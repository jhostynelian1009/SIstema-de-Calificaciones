<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Requests\UpdateEnrollmentRequest;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\Academic\EnrollmentService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Enrollment::class);

        $query = Enrollment::with(['student', 'course', 'academicPeriod']);

        // Filter by student search (name or email)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by academic period
        if ($request->filled('period_id')) {
            $query->where('academic_period_id', $request->input('period_id'));
        }

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        // Filter by active status
        if ($request->has('active') && $request->input('active') !== '') {
            $query->where('active', filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN));
        }

        $enrollments = $query->latest()->paginate(25)->withQueryString();

        $periods = AcademicPeriod::orderBy('name')->get();
        $courses = Course::orderBy('name')->get();

        return view('admin.enrollments.index', compact('enrollments', 'periods', 'courses'));
    }

    /**
     * Show the form for creating a new enrollment.
     */
    public function create()
    {
        $this->authorize('create', Enrollment::class);

        $students = User::where('role', UserRole::Student)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $courses = Course::where('active', true)
            ->orderBy('name')
            ->get();

        $periods = AcademicPeriod::where('active', true)
            ->orderBy('name')
            ->get();

        return view('admin.enrollments.create', compact('students', 'courses', 'periods'));
    }

    /**
     * Store a newly created enrollment in storage.
     */
    public function store(StoreEnrollmentRequest $request, EnrollmentService $service)
    {
        $this->authorize('create', Enrollment::class);

        $service->createEnrollment($request->validated());

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Matrícula registrada exitosamente.');
    }

    /**
     * Show the form for editing the specified enrollment.
     */
    public function edit(Enrollment $enrollment)
    {
        $this->authorize('update', $enrollment);

        $enrollment->load(['student', 'course', 'academicPeriod']);

        $courses = Course::where('active', true)
            ->orderBy('name')
            ->get();

        return view('admin.enrollments.edit', compact('enrollment', 'courses'));
    }

    /**
     * Update the specified enrollment in storage.
     */
    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment, EnrollmentService $service)
    {
        $this->authorize('update', $enrollment);

        $service->updateEnrollment($enrollment, $request->validated());

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Matrícula actualizada exitosamente.');
    }

    /**
     * Toggle the active status of the specified enrollment.
     */
    public function toggleStatus(Enrollment $enrollment, EnrollmentService $service)
    {
        $this->authorize('toggleStatus', $enrollment);

        $updated = $service->toggleStatus($enrollment);

        $statusMessage = $updated->active ? 'activada' : 'desactivada';

        return redirect()->route('admin.enrollments.index')
            ->with('success', "La matrícula ha sido {$statusMessage} exitosamente.");
    }
}
