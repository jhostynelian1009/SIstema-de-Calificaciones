<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeachingAssignmentRequest;
use App\Http\Requests\UpdateTeachingAssignmentRequest;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Academic\TeachingAssignmentService;
use Illuminate\Http\Request;

class TeachingAssignmentController extends Controller
{
    /**
     * Display a listing of teaching assignments.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', TeachingAssignment::class);

        $query = TeachingAssignment::with(['teacher', 'course', 'subject', 'academicPeriod']);

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->input('teacher_id'));
        }

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        // Filter by academic period
        if ($request->filled('period_id')) {
            $query->where('academic_period_id', $request->input('period_id'));
        }

        // Filter by active status
        if ($request->has('active') && $request->input('active') !== '') {
            $query->where('active', filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN));
        }

        $assignments = $query->latest()->paginate(25)->withQueryString();

        $teachers = User::where('role', UserRole::Teacher)->orderBy('name')->get();
        $courses = Course::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $periods = AcademicPeriod::orderBy('name')->get();

        return view('admin.teaching-assignments.index', compact('assignments', 'teachers', 'courses', 'subjects', 'periods'));
    }

    /**
     * Show the form for creating a new teaching assignment.
     */
    public function create()
    {
        $this->authorize('create', TeachingAssignment::class);

        $teachers = User::where('role', UserRole::Teacher)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $courses = Course::where('active', true)
            ->orderBy('name')
            ->get();

        $subjects = Subject::where('active', true)
            ->orderBy('name')
            ->get();

        $periods = AcademicPeriod::where('active', true)
            ->orderBy('name')
            ->get();

        return view('admin.teaching-assignments.create', compact('teachers', 'courses', 'subjects', 'periods'));
    }

    /**
     * Store a newly created teaching assignment in storage.
     */
    public function store(StoreTeachingAssignmentRequest $request, TeachingAssignmentService $service)
    {
        $this->authorize('create', TeachingAssignment::class);

        $service->createAssignment($request->validated());

        return redirect()->route('admin.teaching-assignments.index')
            ->with('success', 'Asignación docente registrada exitosamente.');
    }

    /**
     * Show the form for editing (reassigning) the specified teaching assignment.
     */
    public function edit(TeachingAssignment $teachingAssignment)
    {
        $this->authorize('update', $teachingAssignment);

        $teachingAssignment->load(['teacher', 'course', 'subject', 'academicPeriod']);

        $teachers = User::where('role', UserRole::Teacher)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('admin.teaching-assignments.edit', compact('teachingAssignment', 'teachers'));
    }

    /**
     * Update (reassign teacher) for the specified teaching assignment.
     */
    public function update(UpdateTeachingAssignmentRequest $request, TeachingAssignment $teachingAssignment, TeachingAssignmentService $service)
    {
        $this->authorize('update', $teachingAssignment);

        $service->updateAssignment($teachingAssignment, $request->validated());

        return redirect()->route('admin.teaching-assignments.index')
            ->with('success', 'Asignación docente actualizada exitosamente.');
    }

    /**
     * Toggle active status for the specified teaching assignment.
     */
    public function toggleStatus(TeachingAssignment $teachingAssignment, TeachingAssignmentService $service)
    {
        $this->authorize('toggleStatus', $teachingAssignment);

        $updated = $service->toggleStatus($teachingAssignment);

        $statusMessage = $updated->active ? 'activada' : 'desactivada';

        return redirect()->route('admin.teaching-assignments.index')
            ->with('success', "La asignación docente ha sido {$statusMessage} exitosamente.");
    }
}
