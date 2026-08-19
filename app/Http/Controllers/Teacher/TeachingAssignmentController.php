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
     * Display a listing of active teaching assignments assigned to the current teacher.
     */
    public function index(Request $request)
    {
        $teacher = $request->user();

        $assignments = TeachingAssignment::with([
            'course',
            'subject',
            'academicPeriod',
            'partialPublications.partial',
        ])
            ->assignedTo($teacher)
            ->active()
            ->latest()
            ->paginate(15);

        // Ensure publication draft states exist for teacher's active assignments
        $publicationService = app(PartialPublicationStateService::class);
        foreach ($assignments as $assignment) {
            if ($assignment->partialPublications->isEmpty()) {
                $publicationService->ensureForAssignment($assignment);
                $assignment->load('partialPublications.partial');
            }
        }

        return view('teacher.assignments.index', compact('assignments'));
    }

    /**
     * Display details of a specific teaching assignment, partial activity summaries, and active enrolled students.
     */
    public function show(TeachingAssignment $assignment)
    {
        $this->authorize('view', $assignment);

        $assignment->load(['course', 'subject', 'academicPeriod', 'partialPublications.partial']);

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

        // Load active enrolled students for the assignment's course & period
        $students = Enrollment::with('student')
            ->forCourse($assignment->course_id)
            ->forPeriod($assignment->academic_period_id)
            ->active()
            ->whereHas('student', function ($q) {
                $q->where('active', true);
            })
            ->get()
            ->pluck('student')
            ->sortBy('name');

        return view('teacher.assignments.show', compact('assignment', 'students', 'partialSummaries', 'readinessMap'));
    }
}
