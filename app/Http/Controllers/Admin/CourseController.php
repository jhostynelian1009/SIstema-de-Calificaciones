<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Course::class);

        $query = Course::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }
        }

        $courses = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create(): View
    {
        $this->authorize('create', Course::class);

        return view('admin.courses.create');
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        Course::create($request->validated());

        return redirect()->route('admin.courses.index')
            ->with('success', 'Curso creado exitosamente.');
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course): View
    {
        $this->authorize('update', $course);

        return view('admin.courses.edit', compact('course'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $course->update($request->validated());

        return redirect()->route('admin.courses.index')
            ->with('success', 'Curso actualizado exitosamente.');
    }

    /**
     * Toggle the active status of the specified course.
     */
    public function toggleStatus(Course $course): RedirectResponse
    {
        $this->authorize('toggleStatus', $course);

        $course->update(['active' => !$course->active]);

        $statusText = $course->active ? 'activado' : 'desactivado';

        return redirect()->route('admin.courses.index')
            ->with('success', "Curso '{$course->name}' {$statusText} exitosamente.");
    }
}
