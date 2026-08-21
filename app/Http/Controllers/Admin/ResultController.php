<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Partial;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Grades\GradeCalculationService;
use App\Services\Grades\PartialReadinessService;
use App\Services\Grades\PublishedResultsService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    protected PublishedResultsService $publishedResultsService;

    protected GradeCalculationService $calculationService;

    protected PartialReadinessService $readinessService;

    public function __construct(
        PublishedResultsService $publishedResultsService,
        GradeCalculationService $calculationService,
        PartialReadinessService $readinessService
    ) {
        $this->publishedResultsService = $publishedResultsService;
        $this->calculationService = $calculationService;
        $this->readinessService = $readinessService;
    }

    /**
     * Display consolidated academic results grid for admin supervision.
     */
    public function index(Request $request)
    {
        $periods = AcademicPeriod::orderBy('name')->get();
        $selectedPeriodId = $request->input('academic_period_id')
            ?? AcademicPeriod::where('active', true)->value('id')
            ?? $periods->first()?->id;

        $selectedPeriod = $periods->firstWhere('id', $selectedPeriodId);

        $courses = Course::where('active', true)->orderBy('code')->get();
        $subjects = Subject::where('active', true)->orderBy('name')->get();

        $selectedCourseId = $request->input('course_id');
        $selectedSubjectId = $request->input('subject_id');
        $selectedStatus = $request->input('status');

        $enrollmentQuery = Enrollment::query();
        if ($selectedPeriodId) {
            $enrollmentQuery->where('academic_period_id', $selectedPeriodId);
        }
        if ($selectedCourseId) {
            $enrollmentQuery->where('course_id', $selectedCourseId);
        }
        if ($request->filled('student_id')) {
            $enrollmentQuery->where('student_id', $request->input('student_id'));
        }

        $enrollments = $enrollmentQuery->where('active', true)
            ->with(['student', 'course', 'academicPeriod'])
            ->get();

        $students = $enrollments->pluck('student')->unique('id')->sortBy('name');

        $p1 = $selectedPeriod ? Partial::where('academic_period_id', $selectedPeriod->id)->where('number', 1)->first() : null;
        $p2 = $selectedPeriod ? Partial::where('academic_period_id', $selectedPeriod->id)->where('number', 2)->first() : null;

        $assignmentsQuery = TeachingAssignment::query();
        if ($selectedPeriodId) {
            $assignmentsQuery->where('academic_period_id', $selectedPeriodId);
        }
        if ($selectedCourseId) {
            $assignmentsQuery->where('course_id', $selectedCourseId);
        }
        if ($selectedSubjectId) {
            $assignmentsQuery->where('subject_id', $selectedSubjectId);
        }

        $assignments = $assignmentsQuery->where('active', true)
            ->with(['course', 'subject', 'teacher', 'partialPublications'])
            ->get();

        $results = [];

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            if (! $student || ! $student->active) {
                continue;
            }

            $courseAssignments = $assignments->where('course_id', $enrollment->course_id);

            foreach ($courseAssignments as $assignment) {
                $pubP1 = $p1 ? $assignment->partialPublications->firstWhere('partial_id', $p1->id) : null;
                $pubP2 = $p2 ? $assignment->partialPublications->firstWhere('partial_id', $p2->id) : null;

                $statusP1 = $pubP1 ? $pubP1->status : PublicationStatus::Draft;
                $statusP2 = $pubP2 ? $pubP2->status : PublicationStatus::Draft;

                if ($selectedStatus && $statusP1->value !== $selectedStatus && $statusP2->value !== $selectedStatus) {
                    continue;
                }

                $p1Calc = $p1 ? $this->calculationService->calculatePartialAverage($assignment, $p1, $student, requirePublished: false) : null;
                $p2Calc = $p2 ? $this->calculationService->calculatePartialAverage($assignment, $p2, $student, requirePublished: false) : null;
                $finalCalc = $this->calculationService->calculateFinalSubjectAverage($assignment, $student, officialOnly: true);

                $results[] = [
                    'student' => $student,
                    'course' => $assignment->course,
                    'subject' => $assignment->subject,
                    'teacher' => $assignment->teacher,
                    'assignment' => $assignment,
                    'p1_status' => $statusP1,
                    'p1_calc' => $p1Calc,
                    'p1_official' => ($statusP1 === PublicationStatus::Published),
                    'p2_status' => $statusP2,
                    'p2_calc' => $p2Calc,
                    'p2_official' => ($statusP2 === PublicationStatus::Published),
                    'final_calc' => $finalCalc,
                    'is_complete_official' => ($statusP1 === PublicationStatus::Published && $statusP2 === PublicationStatus::Published),
                ];
            }
        }

        return view('admin.results.index', compact(
            'periods',
            'courses',
            'subjects',
            'selectedPeriodId',
            'selectedCourseId',
            'selectedSubjectId',
            'selectedStatus',
            'results',
            'students'
        ));
    }

    /**
     * Display detailed transcript for a single student.
     */
    public function student(Request $request, User $student)
    {
        if ($student->role !== UserRole::Student) {
            abort(404, 'Usuario no es un estudiante.');
        }

        $periods = AcademicPeriod::orderBy('name')->get();
        $selectedPeriodId = $request->input('academic_period_id')
            ?? AcademicPeriod::where('active', true)->value('id')
            ?? $periods->first()?->id;

        $selectedPeriod = $periods->firstWhere('id', $selectedPeriodId);

        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('academic_period_id', $selectedPeriodId)
            ->where('active', true)
            ->with('course')
            ->first();

        $subjectResults = [];
        $overallResult = null;

        if ($enrollment && $selectedPeriod) {
            $assignments = TeachingAssignment::where('course_id', $enrollment->course_id)
                ->where('academic_period_id', $selectedPeriod->id)
                ->where('active', true)
                ->with(['subject', 'teacher', 'activities.grades' => function ($q) use ($student) {
                    $q->where('student_id', $student->id);
                }, 'partialPublications.partial'])
                ->get();

            $p1 = Partial::where('academic_period_id', $selectedPeriod->id)->where('number', 1)->first();
            $p2 = Partial::where('academic_period_id', $selectedPeriod->id)->where('number', 2)->first();

            foreach ($assignments as $assignment) {
                $pubP1 = $p1 ? $assignment->partialPublications->firstWhere('partial_id', $p1->id) : null;
                $pubP2 = $p2 ? $assignment->partialPublications->firstWhere('partial_id', $p2->id) : null;

                $statusP1 = $pubP1 ? $pubP1->status : PublicationStatus::Draft;
                $statusP2 = $pubP2 ? $pubP2->status : PublicationStatus::Draft;

                $p1Calc = $p1 ? $this->calculationService->calculatePartialAverage($assignment, $p1, $student, requirePublished: false) : null;
                $p2Calc = $p2 ? $this->calculationService->calculatePartialAverage($assignment, $p2, $student, requirePublished: false) : null;
                $finalCalc = $this->calculationService->calculateFinalSubjectAverage($assignment, $student, officialOnly: true);

                $subjectResults[] = [
                    'assignment' => $assignment,
                    'subject' => $assignment->subject,
                    'teacher' => $assignment->teacher,
                    'p1' => $p1,
                    'p1_status' => $statusP1,
                    'p1_calc' => $p1Calc,
                    'p1_official' => ($statusP1 === PublicationStatus::Published),
                    'p2' => $p2,
                    'p2_status' => $statusP2,
                    'p2_calc' => $p2Calc,
                    'p2_official' => ($statusP2 === PublicationStatus::Published),
                    'final_calc' => $finalCalc,
                ];
            }

            $overallGeneral = $this->publishedResultsService->getPublishedGeneralResult($enrollment->course, $selectedPeriod, $student);

            if ($overallGeneral) {
                $overallResult = [
                    'is_official' => true,
                    'overall_average_formatted' => $overallGeneral['score_formatted'],
                ];
            } else {
                $provCalc = $this->calculationService->calculateGeneralAverage($enrollment->course, $selectedPeriod->id, $student, officialOnly: false);
                $overallResult = [
                    'is_official' => false,
                    'overall_average_formatted' => $provCalc['calculable'] ? $provCalc['score_formatted'] : 'N/A',
                ];
            }
        }

        return view('admin.results.student', compact(
            'student',
            'periods',
            'selectedPeriodId',
            'enrollment',
            'subjectResults',
            'overallResult'
        ));
    }
}
