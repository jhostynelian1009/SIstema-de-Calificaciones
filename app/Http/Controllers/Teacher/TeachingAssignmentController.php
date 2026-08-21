<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\TeachingAssignment;
use App\Services\Grades\ActivityService;
use App\Services\Grades\PartialPublicationStateService;
use App\Services\Grades\PartialReadinessService;
use Illuminate\Http\Request;

class TeachingAssignmentController extends Controller
{
    /**
     * Display a listing of active and historical teaching assignments assigned to the current teacher.
     */
    public function index(Request $request)
    {
        $teacher = $request->user();

        $query = TeachingAssignment::with([
            'course',
            'subject',
            'academicPeriod',
            'partialPublications.partial',
        ])
            ->assignedTo($teacher);

        // Search filter (course name/code or subject name/code)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('course', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                })->orWhereHas('subject', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            });
        }

        // Period filter
        if ($request->filled('academic_period_id')) {
            $query->where('academic_period_id', $request->input('academic_period_id'));
        }

        // Course filter
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        // Subject filter
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        // Active status filter (Default to active assignments if filter not provided)
        if ($request->filled('active')) {
            $query->where('active', $request->input('active') === '1');
        } else {
            $query->active();
        }

        // Publication status filter
        if ($request->filled('publication_status')) {
            $status = $request->input('publication_status');
            $query->whereHas('partialPublications', function ($pq) use ($status) {
                $pq->where('status', $status);
            });
        }

        $assignments = $query->latest()->paginate(25)->withQueryString();

        // Ensure publication draft states exist for teacher's assignments
        $publicationService = app(PartialPublicationStateService::class);
        foreach ($assignments as $assignment) {
            if ($assignment->partialPublications->isEmpty()) {
                $publicationService->ensureForAssignment($assignment);
                $assignment->load('partialPublications.partial');
            }
        }

        // Available filter options for teacher (active assignments only)
        $teacherAssignments = TeachingAssignment::assignedTo($teacher)->active()->with(['academicPeriod', 'course', 'subject'])->get();
        $periods = $teacherAssignments->pluck('academicPeriod')->unique('id')->filter();
        $courses = $teacherAssignments->pluck('course')->unique('id')->filter();
        $subjects = $teacherAssignments->pluck('subject')->unique('id')->filter();

        return view('teacher.assignments.index', compact('assignments', 'periods', 'courses', 'subjects'));
    }

    /**
     * Display comprehensive details of a specific teaching assignment, partial readiness, and paginated student list.
     */
    public function show(Request $request, TeachingAssignment $assignment)
    {
        $this->authorize('view', $assignment);

        $assignment->load(['course', 'subject', 'academicPeriod', 'partialPublications.partial', 'teacher']);

        // Ensure publication states exist
        if ($assignment->partialPublications->isEmpty()) {
            app(PartialPublicationStateService::class)->ensureForAssignment($assignment);
            $assignment->load('partialPublications.partial');
        }

        $activityService = app(ActivityService::class);
        $readinessService = app(PartialReadinessService::class);

        $partialSummaries = [];
        $readinessMap = [];

        foreach ($assignment->partialPublications->sortBy(fn ($p) => $p->partial->number) as $pub) {
            if ($pub->partial) {
                $partialSummaries[$pub->partial->id] = $activityService->getSummary($assignment, $pub->partial);
                $readinessMap[$pub->partial->id] = $readinessService->checkReadiness($assignment, $pub->partial);
            }
        }

        // Student list pagination & search (active enrollments & active students only)
        $search = $request->input('search');
        $enrollmentQuery = Enrollment::with('student')
            ->forCourse($assignment->course_id)
            ->forPeriod($assignment->academic_period_id)
            ->active()
            ->whereHas('student', function ($sq) use ($search) {
                $sq->where('active', true);
                if ($search) {
                    $sq->where(function ($s) use ($search) {
                        $s->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            });

        $enrollments = $enrollmentQuery->paginate(25)->withQueryString();

        return view('teacher.assignments.show', compact(
            'assignment',
            'enrollments',
            'partialSummaries',
            'readinessMap'
        ));
    }
}
