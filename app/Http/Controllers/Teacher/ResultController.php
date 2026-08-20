<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Teacher\TeacherResultService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    protected TeacherResultService $resultService;

    public function __construct(TeacherResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    /**
     * Display general overview listing of teacher's assignments with quick result status.
     */
    public function index(Request $request)
    {
        $teacher = $request->user();

        $assignments = TeachingAssignment::with(['course', 'subject', 'academicPeriod', 'partialPublications.partial'])
            ->assignedTo($teacher)
            ->active()
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('teacher.results.index', compact('assignments'));
    }

    /**
     * Display consolidated results matrix for a specific teaching assignment.
     */
    public function assignment(TeachingAssignment $assignment, Request $request)
    {
        $this->authorize('view', $assignment);

        $search = $request->input('search');
        $data = $this->resultService->getAssignmentMatrix($assignment, $search);

        return view('teacher.results.assignment', $data);
    }

    /**
     * Display detailed subject transcript for a student inside a specific teaching assignment.
     */
    public function student(TeachingAssignment $assignment, User $student)
    {
        $this->authorize('view', $assignment);

        $data = $this->resultService->getStudentSubjectResult($assignment, $student);

        return view('teacher.results.student', $data);
    }

    /**
     * Display HTML printable view of assignment grade matrix.
     */
    public function print(TeachingAssignment $assignment)
    {
        $this->authorize('view', $assignment);

        $data = $this->resultService->getPrintableAssignmentMatrix($assignment);

        return view('teacher.results.print', $data);
    }
}
