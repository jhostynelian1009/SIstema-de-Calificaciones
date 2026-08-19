<?php

namespace App\Services\Grades;

use App\Enums\PublicationStatus;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use App\Models\User;

class PublishedResultsService
{
    protected GradeCalculationService $calculationService;

    public function __construct(GradeCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Get official published result for a single partial.
     * Returns null if partial is not published.
     */
    public function getPublishedPartialResult(TeachingAssignment $assignment, Partial $partial, User|int $student): ?array
    {
        $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->first();

        if (! $publication || $publication->status !== PublicationStatus::Published) {
            return null;
        }

        $res = $this->calculationService->calculatePartialAverage($assignment, $partial, $student, requirePublished: true);

        if (! $res['calculable']) {
            return null;
        }

        return [
            'official' => true,
            'assignment' => $assignment,
            'partial' => $partial,
            'publication' => $publication,
            'score_hundredths' => $res['score_hundredths'],
            'score_formatted' => $res['score_formatted'],
            'components' => $res['components'],
        ];
    }

    /**
     * Get official published final subject result.
     * Returns null if either P1 or P2 is not published.
     */
    public function getPublishedSubjectResult(TeachingAssignment $assignment, User|int $student): ?array
    {
        $res = $this->calculationService->calculateFinalSubjectAverage($assignment, $student, officialOnly: true);

        if (! $res['calculable']) {
            return null;
        }

        return [
            'official' => true,
            'assignment' => $assignment,
            'score_hundredths' => $res['score_hundredths'],
            'score_formatted' => $res['score_formatted'],
            'p1' => $res['p1'],
            'p2' => $res['p2'],
        ];
    }

    /**
     * Get official published general period result across all active subjects in a course.
     * Returns null if any applicable subject is not published/calculable.
     */
    public function getPublishedGeneralResult(Course $course, AcademicPeriod|int $period, User|int $student): ?array
    {
        $periodId = $period instanceof AcademicPeriod ? $period->id : (int) $period;

        $res = $this->calculationService->calculateGeneralAverage($course, $periodId, $student, officialOnly: true);

        if (! $res['calculable']) {
            return null;
        }

        return [
            'official' => true,
            'course' => $course,
            'academic_period_id' => $periodId,
            'score_hundredths' => $res['score_hundredths'],
            'score_formatted' => $res['score_formatted'],
            'subject_results' => $res['subject_results'],
        ];
    }

    /**
     * Get all published results for a historical enrollment (even if enrollment is inactive).
     */
    public function getHistoricalPublishedResultsForEnrollment(Enrollment $enrollment): array
    {
        $studentId = $enrollment->student_id;
        $courseId = $enrollment->course_id;
        $periodId = $enrollment->academic_period_id;

        $assignments = TeachingAssignment::where('course_id', $courseId)
            ->where('academic_period_id', $periodId)
            ->where('active', true)
            ->with(['subject', 'teacher'])
            ->get();

        $subjectsData = [];

        foreach ($assignments as $assignment) {
            $p1 = Partial::where('academic_period_id', $periodId)->where('number', 1)->first();
            $p2 = Partial::where('academic_period_id', $periodId)->where('number', 2)->first();

            $p1Result = $p1 ? $this->getPublishedPartialResult($assignment, $p1, $studentId) : null;
            $p2Result = $p2 ? $this->getPublishedPartialResult($assignment, $p2, $studentId) : null;

            $subjectResult = $this->getPublishedSubjectResult($assignment, $studentId);

            $subjectsData[] = [
                'assignment' => $assignment,
                'p1_result' => $p1Result,
                'p2_result' => $p2Result,
                'subject_result' => $subjectResult,
            ];
        }

        $course = Course::find($courseId);
        $generalResult = $course ? $this->getPublishedGeneralResult($course, $periodId, $studentId) : null;

        return [
            'enrollment' => $enrollment,
            'student' => $enrollment->student,
            'subjects_results' => $subjectsData,
            'general_result' => $generalResult,
        ];
    }
}
