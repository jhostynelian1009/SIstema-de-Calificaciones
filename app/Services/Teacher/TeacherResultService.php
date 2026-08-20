<?php

namespace App\Services\Teacher;

use App\Enums\PublicationStatus;
use App\Models\Enrollment;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Grades\GradeCalculationService;
use App\Services\Grades\PartialReadinessService;
use App\Services\Grades\PublishedResultsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;

class TeacherResultService
{
    protected GradeCalculationService $calcService;
    protected PartialReadinessService $readinessService;
    protected PublishedResultsService $publishedResultsService;

    public function __construct(
        GradeCalculationService $calcService,
        PartialReadinessService $readinessService,
        PublishedResultsService $publishedResultsService
    ) {
        $this->calcService = $calcService;
        $this->readinessService = $readinessService;
        $this->publishedResultsService = $publishedResultsService;
    }

    /**
     * Get consolidated grade matrix for a teaching assignment with paginated students.
     */
    public function getAssignmentMatrix(TeachingAssignment $assignment, ?string $search = null, int $perPage = 25): array
    {
        $assignment->load(['course', 'subject', 'academicPeriod', 'teacher', 'partialPublications.partial']);

        $p1 = Partial::where('academic_period_id', $assignment->academic_period_id)->where('number', 1)->first();
        $p2 = Partial::where('academic_period_id', $assignment->academic_period_id)->where('number', 2)->first();

        $p1Pub = $assignment->partialPublications->firstWhere('partial_id', $p1?->id);
        $p2Pub = $assignment->partialPublications->firstWhere('partial_id', $p2?->id);

        $p1Official = $p1Pub && $p1Pub->status === PublicationStatus::Published;
        $p2Official = $p2Pub && $p2Pub->status === PublicationStatus::Published;

        $enrollmentQuery = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $assignment->academic_period_id)
            ->with('student');

        if ($search) {
            $enrollmentQuery->whereHas('student', function ($sq) use ($search) {
                $sq->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $enrollments = $enrollmentQuery->paginate($perPage)->withQueryString();

        $rows = [];
        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            if (!$student) {
                continue;
            }

            $p1Calc = $p1 ? $this->calcService->calculatePartialAverage($assignment, $p1, $student, requirePublished: false) : null;
            $p2Calc = $p2 ? $this->calcService->calculatePartialAverage($assignment, $p2, $student, requirePublished: false) : null;
            $finalCalc = $this->calcService->calculateFinalSubjectAverage($assignment, $student, officialOnly: false);

            $rows[] = [
                'enrollment' => $enrollment,
                'student' => $student,
                'p1_calc' => $p1Calc,
                'p1_official' => $p1Official,
                'p2_calc' => $p2Calc,
                'p2_official' => $p2Official,
                'final_calc' => $finalCalc,
                'is_final_official' => ($p1Official && $p2Official && $finalCalc['calculable']),
            ];
        }

        return [
            'assignment' => $assignment,
            'p1' => $p1,
            'p2' => $p2,
            'p1_pub' => $p1Pub,
            'p2_pub' => $p2Pub,
            'p1_official' => $p1Official,
            'p2_official' => $p2Official,
            'enrollments' => $enrollments,
            'rows' => $rows,
        ];
    }

    /**
     * Get detailed transcript for a specific student inside a teaching assignment.
     */
    public function getStudentSubjectResult(TeachingAssignment $assignment, User $student): array
    {
        // Validate student enrollment in assignment course & period
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('course_id', $assignment->course_id)
            ->where('academic_period_id', $assignment->academic_period_id)
            ->first();

        if (! $enrollment) {
            throw new AuthorizationException('El estudiante seleccionado no pertenece a este curso y período.');
        }

        $assignment->load(['course', 'subject', 'academicPeriod', 'teacher', 'partialPublications.partial']);

        $p1 = Partial::where('academic_period_id', $assignment->academic_period_id)->where('number', 1)->first();
        $p2 = Partial::where('academic_period_id', $assignment->academic_period_id)->where('number', 2)->first();

        $p1Pub = $assignment->partialPublications->firstWhere('partial_id', $p1?->id);
        $p2Pub = $assignment->partialPublications->firstWhere('partial_id', $p2?->id);

        $p1Official = $p1Pub && $p1Pub->status === PublicationStatus::Published;
        $p2Official = $p2Pub && $p2Pub->status === PublicationStatus::Published;

        $p1Calc = $p1 ? $this->calcService->calculatePartialAverage($assignment, $p1, $student, requirePublished: false) : null;
        $p2Calc = $p2 ? $this->calcService->calculatePartialAverage($assignment, $p2, $student, requirePublished: false) : null;
        $finalCalc = $this->calcService->calculateFinalSubjectAverage($assignment, $student, officialOnly: false);

        return [
            'assignment' => $assignment,
            'student' => $student,
            'enrollment' => $enrollment,
            'p1' => $p1,
            'p2' => $p2,
            'p1_pub' => $p1Pub,
            'p2_pub' => $p2Pub,
            'p1_official' => $p1Official,
            'p2_official' => $p2Official,
            'p1_calc' => $p1Calc,
            'p2_calc' => $p2Calc,
            'final_calc' => $finalCalc,
            'is_final_official' => ($p1Official && $p2Official && $finalCalc['calculable']),
        ];
    }

    /**
     * Get complete non-paginated assignment matrix for HTML printable view.
     */
    public function getPrintableAssignmentMatrix(TeachingAssignment $assignment): array
    {
        $assignment->load(['course', 'subject', 'academicPeriod', 'teacher', 'partialPublications.partial']);

        $p1 = Partial::where('academic_period_id', $assignment->academic_period_id)->where('number', 1)->first();
        $p2 = Partial::where('academic_period_id', $assignment->academic_period_id)->where('number', 2)->first();

        $p1Pub = $assignment->partialPublications->firstWhere('partial_id', $p1?->id);
        $p2Pub = $assignment->partialPublications->firstWhere('partial_id', $p2?->id);

        $p1Official = $p1Pub && $p1Pub->status === PublicationStatus::Published;
        $p2Official = $p2Pub && $p2Pub->status === PublicationStatus::Published;

        $enrollments = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $assignment->academic_period_id)
            ->with('student')
            ->get()
            ->sortBy(fn ($e) => $e->student?->name);

        $rows = [];
        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            if (!$student) {
                continue;
            }

            $p1Calc = $p1 ? $this->calcService->calculatePartialAverage($assignment, $p1, $student, requirePublished: false) : null;
            $p2Calc = $p2 ? $this->calcService->calculatePartialAverage($assignment, $p2, $student, requirePublished: false) : null;
            $finalCalc = $this->calcService->calculateFinalSubjectAverage($assignment, $student, officialOnly: false);

            $rows[] = [
                'enrollment' => $enrollment,
                'student' => $student,
                'p1_calc' => $p1Calc,
                'p1_official' => $p1Official,
                'p2_calc' => $p2Calc,
                'p2_official' => $p2Official,
                'final_calc' => $finalCalc,
                'is_final_official' => ($p1Official && $p2Official && $finalCalc['calculable']),
            ];
        }

        return [
            'assignment' => $assignment,
            'p1' => $p1,
            'p2' => $p2,
            'p1_official' => $p1Official,
            'p2_official' => $p2Official,
            'rows' => $rows,
            'generated_at' => now(),
        ];
    }
}
