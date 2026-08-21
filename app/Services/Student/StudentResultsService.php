<?php

namespace App\Services\Student;

use App\Enums\PublicationStatus;
use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Grades\GradeCalculationService;
use App\Services\Grades\PublishedResultsService;
use Illuminate\Support\Collection;

class StudentResultsService
{
    protected PublishedResultsService $publishedResultsService;

    protected GradeCalculationService $gradeCalculationService;

    public function __construct(
        PublishedResultsService $publishedResultsService,
        GradeCalculationService $gradeCalculationService
    ) {
        $this->publishedResultsService = $publishedResultsService;
        $this->gradeCalculationService = $gradeCalculationService;
    }

    /**
     * Get all academic periods where the student has an enrollment (active or historical).
     */
    public function getAvailablePeriods(User $student): Collection
    {
        $periodIds = Enrollment::where('student_id', $student->id)
            ->pluck('academic_period_id')
            ->unique();

        return AcademicPeriod::whereIn('id', $periodIds)
            ->orderBy('starts_at', 'desc')
            ->get();
    }

    /**
     * Get the student's enrollment for a given period.
     */
    public function getEnrollmentForPeriod(User $student, AcademicPeriod|int $period): ?Enrollment
    {
        $periodId = $period instanceof AcademicPeriod ? $period->id : (int) $period;

        return Enrollment::with(['course', 'academicPeriod'])
            ->where('student_id', $student->id)
            ->where('academic_period_id', $periodId)
            ->first();
    }

    /**
     * Retrieve complete published results summary for a student in a specific period.
     */
    public function getSubjectsSummaryForPeriod(User $student, AcademicPeriod|int $period): ?array
    {
        $periodId = $period instanceof AcademicPeriod ? $period->id : (int) $period;

        $enrollment = $this->getEnrollmentForPeriod($student, $periodId);
        if (! $enrollment) {
            return null;
        }

        $assignments = TeachingAssignment::where('course_id', $enrollment->course_id)
            ->where('academic_period_id', $periodId)
            ->where('active', true)
            ->with(['subject', 'teacher', 'partialPublications.partial'])
            ->get();

        $p1 = Partial::where('academic_period_id', $periodId)->where('number', 1)->first();
        $p2 = Partial::where('academic_period_id', $periodId)->where('number', 2)->first();

        $subjectsSummary = [];

        foreach ($assignments as $assignment) {
            $p1Pub = $assignment->partialPublications->first(fn ($p) => $p->partial_id === $p1?->id);
            $p2Pub = $assignment->partialPublications->first(fn ($p) => $p->partial_id === $p2?->id);

            $p1StatusLabel = $this->resolveStatusLabel($p1Pub?->status);
            $p2StatusLabel = $this->resolveStatusLabel($p2Pub?->status);

            $p1Result = ($p1 && $p1Pub?->status === PublicationStatus::Published)
                ? $this->publishedResultsService->getPublishedPartialResult($assignment, $p1, $student)
                : null;

            $p2Result = ($p2 && $p2Pub?->status === PublicationStatus::Published)
                ? $this->publishedResultsService->getPublishedPartialResult($assignment, $p2, $student)
                : null;

            $finalResult = ($p1Pub?->status === PublicationStatus::Published && $p2Pub?->status === PublicationStatus::Published)
                ? $this->publishedResultsService->getPublishedSubjectResult($assignment, $student)
                : null;

            $subjectsSummary[] = [
                'assignment' => $assignment,
                'subject' => $assignment->subject,
                'teacher' => $assignment->teacher,
                'p1_status_label' => $p1StatusLabel,
                'p2_status_label' => $p2StatusLabel,
                'p1_published' => $p1Pub?->status === PublicationStatus::Published,
                'p2_published' => $p2Pub?->status === PublicationStatus::Published,
                'p1_result' => $p1Result,
                'p2_result' => $p2Result,
                'final_result' => $finalResult,
            ];
        }

        $generalResult = $enrollment->course
            ? $this->publishedResultsService->getPublishedGeneralResult($enrollment->course, $periodId, $student)
            : null;

        return [
            'enrollment' => $enrollment,
            'course' => $enrollment->course,
            'academic_period' => $enrollment->academicPeriod,
            'subjects' => $subjectsSummary,
            'general_result' => $generalResult,
            'is_general_official' => $generalResult !== null,
        ];
    }

    /**
     * Get detailed activities and published grades for a specific teaching assignment.
     */
    public function getSubjectDetail(User $student, AcademicPeriod|int $period, TeachingAssignment $assignment): ?array
    {
        $periodId = $period instanceof AcademicPeriod ? $period->id : (int) $period;

        $enrollment = $this->getEnrollmentForPeriod($student, $periodId);

        // Security validation: Assignment must belong to student's enrolled course and period
        if (! $enrollment || (int) $assignment->course_id !== (int) $enrollment->course_id || (int) $assignment->academic_period_id !== (int) $periodId) {
            return null;
        }

        $assignment->load(['subject', 'teacher', 'course', 'academicPeriod', 'partialPublications.partial']);

        $p1 = Partial::where('academic_period_id', $periodId)->where('number', 1)->first();
        $p2 = Partial::where('academic_period_id', $periodId)->where('number', 2)->first();

        $p1Detail = $p1 ? $this->getPartialDetailForStudent($assignment, $p1, $student) : null;
        $p2Detail = $p2 ? $this->getPartialDetailForStudent($assignment, $p2, $student) : null;

        $finalResult = ($p1Detail['is_published'] ?? false) && ($p2Detail['is_published'] ?? false)
            ? $this->publishedResultsService->getPublishedSubjectResult($assignment, $student)
            : null;

        return [
            'enrollment' => $enrollment,
            'assignment' => $assignment,
            'p1_detail' => $p1Detail,
            'p2_detail' => $p2Detail,
            'final_result' => $finalResult,
        ];
    }

    /**
     * Internal helper to build partial detail if published.
     */
    protected function getPartialDetailForStudent(TeachingAssignment $assignment, Partial $partial, User $student): array
    {
        $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->first();

        $status = $publication ? $publication->status : PublicationStatus::Draft;
        $statusLabel = $this->resolveStatusLabel($status);

        if ($status !== PublicationStatus::Published) {
            return [
                'partial' => $partial,
                'status' => $status,
                'status_label' => $statusLabel,
                'is_published' => false,
                'activities' => [],
                'partial_result' => null,
                'published_at' => null,
            ];
        }

        $result = $this->publishedResultsService->getPublishedPartialResult($assignment, $partial, $student);

        // Fetch published activity details and grades
        $activities = Activity::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->where('active', true)
            ->orderBy('created_at', 'asc')
            ->get();

        $activityIds = $activities->pluck('id');
        $grades = Grade::whereIn('activity_id', $activityIds)
            ->where('student_id', $student->id)
            ->get()
            ->keyBy('activity_id');

        $activityDetails = [];
        foreach ($activities as $activity) {
            $grade = $grades->get($activity->id);
            $activityDetails[] = [
                'activity' => $activity,
                'name' => $activity->name,
                'description' => $activity->description,
                'percentage' => $activity->percentage,
                'score' => $grade?->score,
                'score_formatted' => $grade?->score !== null ? number_format((float) $grade->score, 2) : '0.00',
                'observation' => $grade?->observation,
                'date' => $activity->created_at,
            ];
        }

        return [
            'partial' => $partial,
            'status' => $status,
            'status_label' => $statusLabel,
            'is_published' => true,
            'activities' => $activityDetails,
            'partial_result' => $result,
            'published_at' => $publication->published_at,
        ];
    }

    /**
     * Map publication status enum to public student label.
     */
    public function resolveStatusLabel(?PublicationStatus $status): string
    {
        if (! $status) {
            return 'Resultados aún no publicados';
        }

        return match ($status) {
            PublicationStatus::Published => 'Resultado disponible',
            PublicationStatus::Reopened => 'Resultado temporalmente no disponible',
            default => 'Resultados aún no publicados',
        };
    }
}
