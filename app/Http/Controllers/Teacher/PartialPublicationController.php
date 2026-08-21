<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use App\Services\Grades\GradeCalculationService;
use App\Services\Grades\PartialPublicationService;
use App\Services\Grades\PartialReadinessService;
use Exception;
use Illuminate\Http\Request;

class PartialPublicationController extends Controller
{
    protected PartialReadinessService $readinessService;

    protected PartialPublicationService $publicationService;

    protected GradeCalculationService $calculationService;

    public function __construct(
        PartialReadinessService $readinessService,
        PartialPublicationService $publicationService,
        GradeCalculationService $calculationService
    ) {
        $this->readinessService = $readinessService;
        $this->publicationService = $publicationService;
        $this->calculationService = $calculationService;
    }

    /**
     * Preview publication readiness and provisional student averages.
     */
    public function preview(TeachingAssignment $assignment, Partial $partial)
    {
        $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->firstOrFail();

        $this->authorize('preview', $publication);

        $assignment->load(['course', 'subject', 'academicPeriod', 'teacher']);
        $readiness = $this->readinessService->checkReadiness($assignment, $partial);

        $enrolledStudents = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $assignment->academic_period_id)
            ->where('active', true)
            ->whereHas('student', fn ($q) => $q->where('active', true))
            ->with('student')
            ->get()
            ->pluck('student')
            ->sortBy('name');

        $studentProvisionalResults = [];
        foreach ($enrolledStudents as $student) {
            $studentProvisionalResults[] = [
                'student' => $student,
                'calculation' => $this->calculationService->calculatePartialAverage($assignment, $partial, $student, requirePublished: false),
            ];
        }

        return view('teacher.partial-publications.preview', compact(
            'assignment',
            'partial',
            'publication',
            'readiness',
            'studentProvisionalResults'
        ));
    }

    /**
     * Publish or republish a partial for a teaching assignment.
     */
    public function publish(Request $request, TeachingAssignment $assignment, Partial $partial)
    {
        $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->firstOrFail();

        $this->authorize('publish', $publication);

        try {
            $prevStatus = $publication->status->value;
            $this->publicationService->publish($assignment, $partial, $request->user());

            $msg = $prevStatus === 'reopened'
                ? 'El parcial ha sido republicado exitosamente.'
                : 'El parcial ha sido publicado exitosamente.';

            return redirect()->route('teacher.assignments.show', $assignment)->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
