<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Partial;
use App\Models\Subject;
use App\Models\User;

use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * Display read-only grades list for administration.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Grade::class);

        $query = Grade::with([
            'student',
            'gradedBy',
            'activity.teachingAssignment.course',
            'activity.teachingAssignment.subject',
            'activity.teachingAssignment.teacher',
            'activity.teachingAssignment.academicPeriod',
            'activity.partial',
        ]);

        if ($request->filled('academic_period_id')) {
            $query->whereHas('activity.teachingAssignment', function ($q) use ($request) {
                $q->where('academic_period_id', $request->academic_period_id);
            });
        }

        if ($request->filled('course_id')) {
            $query->whereHas('activity.teachingAssignment', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('activity.teachingAssignment', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        if ($request->filled('teacher_id')) {
            $query->whereHas('activity.teachingAssignment', function ($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id);
            });
        }

        if ($request->filled('partial_id')) {
            $query->whereHas('activity', function ($q) use ($request) {
                $q->where('partial_id', $request->partial_id);
            });
        }

        if ($request->filled('activity_id')) {
            $query->where('activity_id', $request->activity_id);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $grades = $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString();

        $periods = AcademicPeriod::orderBy('starts_at', 'desc')->get();
        $courses = Course::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $partials = Partial::orderBy('number')->get();

        return view('admin.grades.index', compact('grades', 'periods', 'courses', 'subjects', 'teachers', 'partials'));
    }
}
