<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Partial;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Display a paginated listing of all registered activities for administrators (Read-only).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Activity::class);

        $query = Activity::with([
            'teachingAssignment.course',
            'teachingAssignment.subject',
            'teachingAssignment.teacher',
            'teachingAssignment.academicPeriod',
            'partial',
        ]);

        // Filter by Academic Period
        if ($request->filled('academic_period_id')) {
            $query->whereHas('teachingAssignment', function ($q) use ($request) {
                $q->where('academic_period_id', $request->academic_period_id);
            });
        }

        // Filter by Course
        if ($request->filled('course_id')) {
            $query->whereHas('teachingAssignment', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        // Filter by Subject
        if ($request->filled('subject_id')) {
            $query->whereHas('teachingAssignment', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        // Filter by Teacher
        if ($request->filled('teacher_id')) {
            $query->whereHas('teachingAssignment', function ($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id);
            });
        }

        // Filter by Partial
        if ($request->filled('partial_id')) {
            $query->where('partial_id', $request->partial_id);
        }

        // Filter by Active status
        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $activities = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        $periods = AcademicPeriod::orderBy('name')->get();
        $courses = Course::where('active', true)->orderBy('name')->get();
        $subjects = Subject::where('active', true)->orderBy('name')->get();
        $teachers = User::where('role', UserRole::Teacher)->where('active', true)->orderBy('name')->get();
        $partials = Partial::orderBy('number')->get();

        return view('admin.activities.index', compact(
            'activities',
            'periods',
            'courses',
            'subjects',
            'teachers',
            'partials'
        ));
    }
}
