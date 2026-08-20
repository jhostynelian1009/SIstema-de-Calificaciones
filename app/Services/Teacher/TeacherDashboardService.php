<?php

namespace App\Services\Teacher;

use App\Enums\PublicationStatus;
use App\Models\Enrollment;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Grades\PartialReadinessService;
use Illuminate\Support\Collection;

class TeacherDashboardService
{
    protected PartialReadinessService $readinessService;

    public function __construct(PartialReadinessService $readinessService)
    {
        $this->readinessService = $readinessService;
    }

    /**
     * Get comprehensive dashboard metrics and prioritized actions for a teacher.
     */
    public function getMetrics(User $teacher): array
    {
        $assignments = TeachingAssignment::with([
            'course',
            'subject',
            'academicPeriod.partials',
            'partialPublications.partial',
            'activities.grades',
        ])
        ->assignedTo($teacher)
        ->active()
        ->get();

        $assignmentIds = $assignments->pluck('id');
        $courseIds = $assignments->pluck('course_id')->unique();
        $subjectIds = $assignments->pluck('subject_id')->unique();

        // Unique active students under this teacher's assignments
        $uniqueStudentsCount = 0;
        if ($assignments->isNotEmpty()) {
            $uniqueStudentsCount = Enrollment::whereIn('course_id', $courseIds)
                ->whereIn('academic_period_id', $assignments->pluck('academic_period_id')->unique())
                ->where('active', true)
                ->distinct('student_id')
                ->count('student_id');
        }

        $publications = PartialPublication::whereIn('teaching_assignment_id', $assignmentIds)->get();

        $draftCount = $publications->where('status', PublicationStatus::Draft)->count();
        $publishedCount = $publications->where('status', PublicationStatus::Published)->count();
        $reopenedCount = $publications->where('status', PublicationStatus::Reopened)->count();

        $priorityActions = [];
        $pendingGradesTotal = 0;

        foreach ($assignments as $assignment) {
            $period = $assignment->academicPeriod;
            if (!$period) {
                continue;
            }

            foreach ($period->partials as $partial) {
                $pub = $publications->first(fn ($p) => $p->teaching_assignment_id === $assignment->id && $p->partial_id === $partial->id);
                $status = $pub ? $pub->status : PublicationStatus::Draft;

                $readiness = $this->readinessService->checkReadiness($assignment, $partial);
                $pendingGradesTotal += $readiness['pending_grades_count'];

                // 1. Reopened partials (Priority 1)
                if ($status === PublicationStatus::Reopened) {
                    $priorityActions[] = [
                        'priority' => 1,
                        'type' => 'reopened',
                        'title' => "Parcial {$partial->number} reabierto en {$assignment->subject?->name} ({$assignment->course?->code})",
                        'description' => "Motivo: " . ($pub->reopen_reason ?? 'Requiere corrección de notas o actividades.'),
                        'assignment' => $assignment,
                        'partial' => $partial,
                        'url' => route('teacher.partial-publications.preview', [$assignment, $partial]),
                        'button_text' => 'Corregir y Republicar',
                        'badge_class' => 'bg-warning text-dark',
                    ];
                }
                // 2. Ready to publish (Priority 2)
                elseif ($readiness['is_ready'] && $status !== PublicationStatus::Published) {
                    $priorityActions[] = [
                        'priority' => 2,
                        'type' => 'ready',
                        'title' => "Parcial {$partial->number} listo para publicar en {$assignment->subject?->name} ({$assignment->course?->code})",
                        'description' => 'Todas las actividades y notas del parcial están completas al 100%.',
                        'assignment' => $assignment,
                        'partial' => $partial,
                        'url' => route('teacher.partial-publications.preview', [$assignment, $partial]),
                        'button_text' => 'Revisar y Publicar',
                        'badge_class' => 'bg-success text-white',
                    ];
                }
                // 3. Pending grades (Priority 3)
                elseif ($readiness['active_activities_count'] > 0 && $readiness['pending_grades_count'] > 0 && $status !== PublicationStatus::Published) {
                    $priorityActions[] = [
                        'priority' => 3,
                        'type' => 'pending_grades',
                        'title' => "Faltan {$readiness['pending_grades_count']} calificaciones en Parcial {$partial->number} de {$assignment->subject?->name}",
                        'description' => "Se registraron {$readiness['completed_grades_count']} de {$readiness['expected_grades_count']} notas esperadas.",
                        'assignment' => $assignment,
                        'partial' => $partial,
                        'url' => route('teacher.assignments.partials.grades.index', [$assignment, $partial]),
                        'button_text' => 'Registrar Calificaciones',
                        'badge_class' => 'bg-primary text-white',
                    ];
                }
                // 4. Incomplete weighting (Priority 4)
                elseif ($readiness['active_activities_count'] > 0 && $readiness['total_percentage'] != 100.0 && $status !== PublicationStatus::Published) {
                    $priorityActions[] = [
                        'priority' => 4,
                        'type' => 'incomplete_weighting',
                        'title' => "Ponderación incompleta en Parcial {$partial->number} de {$assignment->subject?->name}",
                        'description' => "La suma actual de actividades es {$readiness['total_percentage']}%. Debe completar el 100%.",
                        'assignment' => $assignment,
                        'partial' => $partial,
                        'url' => route('teacher.assignments.partials.activities.index', [$assignment, $partial]),
                        'button_text' => 'Ajustar Actividades',
                        'badge_class' => 'bg-info text-dark',
                    ];
                }
                // 5. No activities configured (Priority 5)
                elseif ($readiness['active_activities_count'] === 0 && $status !== PublicationStatus::Published) {
                    $priorityActions[] = [
                        'priority' => 5,
                        'type' => 'no_activities',
                        'title' => "Sin actividades en Parcial {$partial->number} de {$assignment->subject?->name} ({$assignment->course?->code})",
                        'description' => 'Aún no se han configurado actividades evaluativas para este parcial.',
                        'assignment' => $assignment,
                        'partial' => $partial,
                        'url' => route('teacher.assignments.partials.activities.index', [$assignment, $partial]),
                        'button_text' => 'Crear Actividades',
                        'badge_class' => 'bg-secondary text-white',
                    ];
                }
            }
        }

        // Sort priority actions by priority ASC
        usort($priorityActions, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return [
            'assignments' => $assignments,
            'active_assignments_count' => $assignments->count(),
            'courses_count' => $courseIds->count(),
            'subjects_count' => $subjectIds->count(),
            'unique_students_count' => $uniqueStudentsCount,
            'draft_count' => $draftCount,
            'published_count' => $publishedCount,
            'reopened_count' => $reopenedCount,
            'pending_grades_count' => $pendingGradesTotal,
            'priority_actions' => $priorityActions,
        ];
    }
}
