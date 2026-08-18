<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class PartialPublicationController extends Controller
{
    /**
     * Display a paginated list of partial publication states for administrators.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PartialPublication::class);

        $query = PartialPublication::with([
            'teachingAssignment.course',
            'teachingAssignment.subject',
            'teachingAssignment.teacher',
            'teachingAssignment.academicPeriod',
            'partial',
            'publishedBy',
            'reopenedBy',
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

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $publications = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        $periods = AcademicPeriod::orderBy('name')->get();
        $courses = Course::where('active', true)->orderBy('name')->get();
        $subjects = Subject::where('active', true)->orderBy('name')->get();
        $teachers = User::where('role', UserRole::Teacher)->where('active', true)->orderBy('name')->get();
        $partials = Partial::orderBy('number')->get();

        return view('admin.partial-publications.index', compact(
            'publications',
            'periods',
            'courses',
            'subjects',
            'teachers',
            'partials'
        ));
    }
}
