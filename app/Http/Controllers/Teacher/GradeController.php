<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkUpsertGradesRequest;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use App\Services\Grades\ActivityService;
use App\Services\Grades\GradeCompletionService;
use App\Services\Grades\GradeService;
use Exception;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    protected GradeService $gradeService;
    protected GradeCompletionService $completionService;
    protected ActivityService $activityService;

    public function __construct(
        GradeService $gradeService,
        GradeCompletionService $completionService,
        ActivityService $activityService
    ) {
        $this->gradeService = $gradeService;
        $this->completionService = $completionService;
        $this->activityService = $activityService;
    }

    /**
     * Display grading matrix view for a teaching assignment and partial.
     */
    public function index(TeachingAssignment $assignment, Partial $partial, Request $request)
    {
        $this->authorize('create', [Grade::class, $assignment]);

        $assignment->load(['course', 'subject', 'academicPeriod']);
        $this->activityService->validateCoherence($assignment, $partial);

        $activities = Activity::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->where('active', true)
            ->orderBy('created_at', 'asc')
            ->get();

        $selectedActivityId = $request->query('activity_id');
        $selectedActivity = $activities->firstWhere('id', (int) $selectedActivityId) ?? $activities->first();

        $enrolledStudents = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $assignment->academic_period_id)
            ->where('active', true)
            ->whereHas('student', fn ($q) => $q->where('active', true))
            ->with('student')
            ->get()
            ->pluck('student')
            ->sortBy('name');

        $gradesMap = collect();
        $activityMetrics = null;

        if ($selectedActivity) {
            $grades = Grade::where('activity_id', $selectedActivity->id)->get();
            $gradesMap = $grades->keyBy('student_id');
            $activityMetrics = $this->completionService->calculateForActivity($selectedActivity);
        }

        $partialMetrics = $this->completionService->calculateForPartial($assignment, $partial);
        $activitySummary = $this->activityService->getSummary($assignment, $partial);

        $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->first();

        $isPublished = $publication && $publication->status->value === 'published';

        return view('teacher.grades.index', compact(
            'assignment',
            'partial',
            'activities',
            'selectedActivity',
            'enrolledStudents',
            'gradesMap',
            'partialMetrics',
            'activityMetrics',
            'activitySummary',
            'publication',
            'isPublished'
        ));
    }

    /**
     * Shortcut to view grading matrix for a specific activity.
     */
    public function activityGrades(Activity $activity)
    {
        $assignment = $activity->teachingAssignment;
        $partial = $activity->partial;

        $this->authorize('create', [Grade::class, $assignment]);

        return redirect()->route('teacher.assignments.partials.grades.index', [
            'assignment' => $assignment->id,
            'partial' => $partial->id,
            'activity_id' => $activity->id,
        ]);
    }

    /**
     * Store an individual grade for an activity.
     */
    public function store(StoreGradeRequest $request, Activity $activity)
    {
        $this->authorize('create', [Grade::class, $activity]);

        try {
            $this->gradeService->saveGrade(
                $activity,
                (int) $request->student_id,
                $request->score,
                $request->observation,
                $request->user()
            );

            return redirect()->back()->with('success', 'Calificación registrada correctamente.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk upsert grades for an activity.
     */
    public function bulkUpsert(BulkUpsertGradesRequest $request, TeachingAssignment $assignment, Partial $partial)
    {
        $this->authorize('create', [Grade::class, $assignment]);

        $activityId = $request->input('activity_id');
        $activity = Activity::where('id', $activityId)
            ->where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->firstOrFail();

        try {
            $processed = $this->gradeService->bulkUpsertGrades(
                $activity,
                $request->input('grades', []),
                $request->user()
            );

            $count = count($processed);
            $msg = $count > 0
                ? "Se procesaron {$count} calificaciones correctamente."
                : 'No se realizaron cambios en las calificaciones.';

            return redirect()->route('teacher.assignments.partials.grades.index', [
                'assignment' => $assignment->id,
                'partial' => $partial->id,
                'activity_id' => $activity->id,
            ])->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an individual grade.
     */
    public function update(UpdateGradeRequest $request, Grade $grade)
    {
        $this->authorize('update', $grade);

        try {
            $this->gradeService->saveGrade(
                $grade->activity,
                $grade->student_id,
                $request->score,
                $request->observation,
                $request->user()
            );

            return redirect()->back()->with('success', 'Calificación actualizada correctamente.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
