<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReopenPartialRequest;
use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PartialPublication;
use App\Models\Subject;
use App\Models\User;
use App\Services\Grades\GradeCalculationService;
use App\Services\Grades\PartialPublicationService;
use App\Services\Grades\PartialReadinessService;
use Exception;
use Illuminate\Http\Request;

class PartialPublicationController extends Controller
{
    protected PartialPublicationService $publicationService;

    protected PartialReadinessService $readinessService;

    protected GradeCalculationService $calculationService;

    public function __construct(
        PartialPublicationService $publicationService,
        PartialReadinessService $readinessService,
        GradeCalculationService $calculationService
    ) {
        $this->publicationService = $publicationService;
        $this->readinessService = $readinessService;
        $this->calculationService = $calculationService;
    }

    /**
     * Display a paginated listing of partial publication states for admin monitoring.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PartialPublication::class);

        $query = PartialPublication::with([
            'teachingAssignment.course',
            'teachingAssignment.subject',
            'teachingAssignment.academicPeriod',
            'teachingAssignment.teacher',
            'partial',
            'publishedBy',
            'reopenedBy',
        ]);

        if ($request->filled('academic_period_id')) {
            $query->whereHas('teachingAssignment', function ($q) use ($request) {
                $q->where('academic_period_id', $request->input('academic_period_id'));
            });
        }

        if ($request->filled('course_id')) {
            $query->whereHas('teachingAssignment', function ($q) use ($request) {
                $q->where('course_id', $request->input('course_id'));
            });
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('teachingAssignment', function ($q) use ($request) {
                $q->where('subject_id', $request->input('subject_id'));
            });
        }

        if ($request->filled('teacher_id')) {
            $query->whereHas('teachingAssignment', function ($q) use ($request) {
                $q->where('teacher_id', $request->input('teacher_id'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('partial_id')) {
            $query->where('partial_id', $request->input('partial_id'));
        }

        $publications = $query->latest()->paginate(15)->withQueryString();

        $periods = AcademicPeriod::orderBy('name')->get();
        $courses = Course::where('active', true)->orderBy('code')->get();
        $subjects = Subject::where('active', true)->orderBy('name')->get();
        $teachers = User::where('role', UserRole::Teacher)->where('active', true)->orderBy('name')->get();

        return view('admin.partial-publications.index', compact(
            'publications',
            'periods',
            'courses',
            'subjects',
            'teachers'
        ));
    }

    /**
     * Display details, readiness metrics, and audit log for a partial publication.
     */
    public function show(PartialPublication $publication)
    {
        $this->authorize('view', $publication);

        $publication->load([
            'teachingAssignment.course',
            'teachingAssignment.subject',
            'teachingAssignment.academicPeriod',
            'teachingAssignment.teacher',
            'partial',
            'publishedBy',
            'reopenedBy',
        ]);

        $assignment = $publication->teachingAssignment;
        $partial = $publication->partial;

        $readiness = $this->readinessService->checkReadiness($assignment, $partial);

        $enrolledStudents = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $assignment->academic_period_id)
            ->where('active', true)
            ->whereHas('student', fn ($q) => $q->where('active', true))
            ->with('student')
            ->get()
            ->pluck('student')
            ->sortBy('name');

        $studentResults = [];
        foreach ($enrolledStudents as $student) {
            $studentResults[] = [
                'student' => $student,
                'calculation' => $this->calculationService->calculatePartialAverage($assignment, $partial, $student, requirePublished: false),
            ];
        }

        $auditLogs = AuditLog::where('auditable_type', PartialPublication::class)
            ->where('auditable_id', $publication->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.partial-publications.show', compact(
            'publication',
            'assignment',
            'partial',
            'readiness',
            'studentResults',
            'auditLogs'
        ));
    }

    /**
     * Reopen a published partial.
     */
    public function reopen(ReopenPartialRequest $request, PartialPublication $publication)
    {
        $this->authorize('reopen', $publication);

        try {
            $this->publicationService->reopen(
                $publication->teachingAssignment,
                $publication->partial,
                $request->reason,
                $request->user()
            );

            return redirect()->back()->with('success', 'El parcial ha sido reabierto exitosamente. El docente responsable podrá corregir las calificaciones.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
