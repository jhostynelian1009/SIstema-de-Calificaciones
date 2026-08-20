<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\TeachingAssignment;
use App\Services\Student\StudentResultsService;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    protected StudentResultsService $resultsService;

    public function __construct(StudentResultsService $resultsService)
    {
        $this->resultsService = $resultsService;
    }

    /**
     * Display student grades overview for the current or selected period.
     */
    public function index(Request $request)
    {
        $student = $request->user();
        $periods = $this->resultsService->getAvailablePeriods($student);

        if ($periods->isEmpty()) {
            return response()
                ->view('student.grades.index', [
                    'has_periods' => false,
                    'periods' => collect(),
                    'selected_period' => null,
                    'summary' => null,
                ])
                ->header('Cache-Control', 'private, no-store')
                ->header('Pragma', 'no-cache')
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        $periodId = $request->input('period_id');
        $selectedPeriod = $periodId ? $periods->firstWhere('id', (int) $periodId) : null;

        if (! $selectedPeriod) {
            $activePeriod = AcademicPeriod::where('active', true)->first();
            $selectedPeriod = ($activePeriod && $periods->contains('id', $activePeriod->id))
                ? $activePeriod
                : $periods->first();
        }

        $summary = $this->resultsService->getSubjectsSummaryForPeriod($student, $selectedPeriod);

        return response()
            ->view('student.grades.index', [
                'has_periods' => true,
                'periods' => $periods,
                'selected_period' => $selectedPeriod,
                'summary' => $summary,
            ])
            ->header('Cache-Control', 'private, no-store')
            ->header('Pragma', 'no-cache')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Display student grades for a specific academic period.
     */
    public function period(Request $request, AcademicPeriod $academicPeriod)
    {
        $student = $request->user();
        $periods = $this->resultsService->getAvailablePeriods($student);

        // Security check: Student must have an enrollment in the requested period
        if (! $periods->contains('id', $academicPeriod->id)) {
            abort(403, 'No tiene acceso a la información del período solicitado.');
        }

        $summary = $this->resultsService->getSubjectsSummaryForPeriod($student, $academicPeriod);

        return response()
            ->view('student.grades.index', [
                'has_periods' => true,
                'periods' => $periods,
                'selected_period' => $academicPeriod,
                'summary' => $summary,
            ])
            ->header('Cache-Control', 'private, no-store')
            ->header('Pragma', 'no-cache')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Display detailed activity grades and feedback for a single teaching assignment.
     */
    public function subject(Request $request, AcademicPeriod $academicPeriod, TeachingAssignment $teachingAssignment)
    {
        $student = $request->user();

        $detail = $this->resultsService->getSubjectDetail($student, $academicPeriod, $teachingAssignment);

        if (! $detail) {
            abort(403, 'No tiene permiso para consultar los detalles de esta asignatura.');
        }

        return response()
            ->view('student.grades.subject', compact('detail', 'academicPeriod', 'teachingAssignment'))
            ->header('Cache-Control', 'private, no-store')
            ->header('Pragma', 'no-cache')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Display print-ready official grade transcript for a period.
     */
    public function print(Request $request, AcademicPeriod $academicPeriod)
    {
        $student = $request->user();

        $summary = $this->resultsService->getSubjectsSummaryForPeriod($student, $academicPeriod);

        if (! $summary) {
            abort(403, 'No tiene permiso para imprimir las calificaciones del período.');
        }

        // Gather activity-level detail for all published partials in each subject
        $detailedSubjects = [];
        foreach ($summary['subjects'] as $item) {
            $assignment = $item['assignment'];
            $detail = $this->resultsService->getSubjectDetail($student, $academicPeriod, $assignment);
            $detailedSubjects[] = array_merge($item, [
                'detail' => $detail,
            ]);
        }

        return response()
            ->view('student.grades.print', [
                'summary' => $summary,
                'detailedSubjects' => $detailedSubjects,
                'academicPeriod' => $academicPeriod,
                'student' => $student,
                'generatedAt' => now(),
            ])
            ->header('Cache-Control', 'private, no-store')
            ->header('Pragma', 'no-cache')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
