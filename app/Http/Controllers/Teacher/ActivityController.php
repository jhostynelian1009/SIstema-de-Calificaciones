<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\Partial;
use App\Models\TeachingAssignment;
use App\Services\Grades\ActivityService;
use Exception;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    protected ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Display a listing of activities for a specific teaching assignment and partial.
     */
    public function index(TeachingAssignment $assignment, Partial $partial)
    {
        $this->authorize('create', [Activity::class, $assignment]);

        $assignment->load(['course', 'subject', 'academicPeriod']);
        $this->activityService->validateCoherence($assignment, $partial);

        $activities = Activity::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $summary = $this->activityService->getSummary($assignment, $partial);

        return view('teacher.activities.index', compact('assignment', 'partial', 'activities', 'summary'));
    }

    /**
     * Show the form for creating a new activity.
     */
    public function create(TeachingAssignment $assignment, Partial $partial)
    {
        $this->authorize('create', [Activity::class, $assignment]);

        $assignment->load(['course', 'subject', 'academicPeriod']);
        $this->activityService->validateCoherence($assignment, $partial);

        $summary = $this->activityService->getSummary($assignment, $partial);

        if ($summary['is_published']) {
            return redirect()->route('teacher.assignments.partials.activities.index', [$assignment, $partial])
                ->with('error', 'No se pueden agregar actividades en un parcial que ya ha sido publicado.');
        }

        return view('teacher.activities.create', compact('assignment', 'partial', 'summary'));
    }

    /**
     * Store a newly created activity in storage.
     */
    public function store(StoreActivityRequest $request, TeachingAssignment $assignment, Partial $partial)
    {
        $this->authorize('create', [Activity::class, $assignment]);

        try {
            $this->activityService->createActivity($assignment, $partial, $request->validated(), $request->user());

            return redirect()->route('teacher.assignments.partials.activities.index', [$assignment, $partial])
                ->with('success', 'Actividad registrada correctamente.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified activity.
     */
    public function edit(Activity $activity)
    {
        $this->authorize('update', $activity);

        $activity->load(['teachingAssignment.course', 'teachingAssignment.subject', 'teachingAssignment.academicPeriod', 'partial']);
        $assignment = $activity->teachingAssignment;
        $partial = $activity->partial;

        $summary = $this->activityService->getSummary($assignment, $partial);

        if ($summary['is_published']) {
            return redirect()->route('teacher.assignments.partials.activities.index', [$assignment, $partial])
                ->with('error', 'No se pueden editar actividades en un parcial que ya ha sido publicado.');
        }

        return view('teacher.activities.edit', compact('activity', 'assignment', 'partial', 'summary'));
    }

    /**
     * Update the specified activity in storage.
     */
    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        $assignment = $activity->teachingAssignment;
        $partial = $activity->partial;

        try {
            $this->activityService->updateActivity($activity, $request->validated(), $request->user());

            return redirect()->route('teacher.assignments.partials.activities.index', [$assignment, $partial])
                ->with('success', 'Actividad actualizada correctamente.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle the active status (active/inactive) of the specified activity.
     */
    public function toggleStatus(Request $request, Activity $activity)
    {
        $this->authorize('toggleStatus', $activity);

        $assignment = $activity->teachingAssignment;
        $partial = $activity->partial;

        try {
            $updated = $this->activityService->toggleActivityStatus($activity, $request->user());

            $statusText = $updated->active ? 'reactivada' : 'desactivada';

            return redirect()->route('teacher.assignments.partials.activities.index', [$assignment, $partial])
                ->with('success', "Actividad {$statusText} correctamente.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
