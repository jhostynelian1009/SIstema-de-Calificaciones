<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class TeachingAssignmentController extends Controller
{
    /**
     * Display a listing of active teaching assignments assigned to the current teacher.
     */
    public function index(Request $request)
    {
        $teacher = $request->user();

        $assignments = TeachingAssignment::with(['course', 'subject', 'academicPeriod'])
            ->assignedTo($teacher)
            ->active()
            ->latest()
            ->paginate(15);

        return view('teacher.assignments.index', compact('assignments'));
    }

    /**
     * Display details of a specific teaching assignment and active enrolled students.
     */
    public function show(TeachingAssignment $assignment)
    {
        $this->authorize('view', $assignment);

        $assignment->load(['course', 'subject', 'academicPeriod']);

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

        return view('teacher.assignments.show', compact('assignment', 'students'));
    }
}
