<?php

namespace App\Services\Grades;

use App\Enums\PublicationStatus;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;

class PartialReadinessService
{
    /**
     * Consolidate and determine the dynamic readiness status of a partial publication.
     *
     * @return array{
     *     calculated_status: string,
     *     persisted_status: string,
     *     total_percentage: float,
     *     remaining_percentage: float,
     *     active_activities_count: int,
     *     active_students_count: int,
     *     expected_grades_count: int,
     *     completed_grades_count: int,
     *     pending_grades_count: int,
     *     pending_issues: array<string>,
     *     is_ready: bool
     * }
     */
    public function checkReadiness(TeachingAssignment $assignment, Partial $partial): array
    {
        $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->first();

        $persistedStatus = $publication ? $publication->status->value : PublicationStatus::Draft->value;

        $issues = [];

        // Structure & Entities validation
        if (! $assignment->active) {
            $issues[] = 'La asignación docente se encuentra inactiva.';
        }

        if (! $assignment->teacher || ! $assignment->teacher->active) {
            $issues[] = 'El docente asignado se encuentra inactivo.';
        }

        if ((int) $partial->academic_period_id !== (int) $assignment->academic_period_id) {
            $issues[] = 'Incoherencia entre el parcial y el período académico de la asignación.';
        }

        // Active Activities
        $activeActivities = Activity::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->where('active', true)
            ->get();

        $activeActivitiesCount = $activeActivities->count();

        $totalWeightHundredths = 0;
        foreach ($activeActivities as $act) {
            $totalWeightHundredths += (int) round((float) $act->percentage * 100);
        }

        $totalPercentage = round($totalWeightHundredths / 100, 2);
        $remainingPercentage = round((10000 - $totalWeightHundredths) / 100, 2);

        if ($totalWeightHundredths !== 10000) {
            if ($totalWeightHundredths < 10000) {
                $issues[] = "La suma de ponderaciones activas es {$totalPercentage}%. Falta {$remainingPercentage}% para alcanzar el 100.00%.";
            } else {
                $over = round(($totalWeightHundredths - 10000) / 100, 2);
                $issues[] = "La suma de ponderaciones activas ({$totalPercentage}%) excede el 100.00% por {$over}%.";
            }
        }

        if ($activeActivitiesCount === 0) {
            $issues[] = 'No existen actividades evaluativas activas registradas para este parcial.';
        }

        // Active Enrolled Students
        $activeEnrollments = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $assignment->academic_period_id)
            ->where('active', true)
            ->whereHas('student', fn ($q) => $q->where('active', true))
            ->with('student')
            ->get();

        $activeStudents = $activeEnrollments->pluck('student')->sortBy('name');
        $activeStudentsCount = $activeStudents->count();

        if ($activeStudentsCount === 0) {
            $issues[] = 'No existen estudiantes con matrícula activa en el curso y período.';
        }

        $expectedGradesCount = $activeActivitiesCount * $activeStudentsCount;
        $completedGradesCount = 0;

        if ($activeActivitiesCount > 0 && $activeStudentsCount > 0) {
            $activityIds = $activeActivities->pluck('id')->toArray();
            $studentIds = $activeStudents->pluck('id')->toArray();

            $existingGrades = Grade::whereIn('activity_id', $activityIds)
                ->whereIn('student_id', $studentIds)
                ->get()
                ->keyBy(fn ($g) => "{$g->activity_id}_{$g->student_id}");

            foreach ($activeStudents as $student) {
                foreach ($activeActivities as $act) {
                    $key = "{$act->id}_{$student->id}";
                    $grade = $existingGrades->get($key);

                    if ($grade && $grade->score !== null && mb_strlen(trim($grade->observation ?? '')) >= 3) {
                        $completedGradesCount++;
                    } else {
                        if (! $grade) {
                            $issues[] = "Falta calificación para el estudiante '{$student->name}' en la actividad '{$act->name}'.";
                        } elseif (mb_strlen(trim($grade->observation ?? '')) < 3) {
                            $issues[] = "Observación inválida o demasiado corta para '{$student->name}' en '{$act->name}'.";
                        }
                    }
                }
            }
        }

        $pendingGradesCount = max(0, $expectedGradesCount - $completedGradesCount);

        // Determine Calculated Status
        $calculatedStatus = PublicationStatus::Draft->value;

        if ($persistedStatus === PublicationStatus::Published->value) {
            $calculatedStatus = 'published';
        } elseif ($persistedStatus === PublicationStatus::Reopened->value) {
            $calculatedStatus = count($issues) === 0 ? 'reopened_ready' : 'reopened_incomplete';
        } else {
            // Persisted status is draft
            if ($activeActivitiesCount === 0 || $activeStudentsCount === 0) {
                $calculatedStatus = 'empty';
            } elseif (count($issues) === 0) {
                $calculatedStatus = 'ready';
            } else {
                $calculatedStatus = 'incomplete';
            }
        }

        $isReady = in_array($calculatedStatus, ['ready', 'reopened_ready'], true);

        return [
            'calculated_status' => $calculatedStatus,
            'persisted_status' => $persistedStatus,
            'total_percentage' => $totalPercentage,
            'remaining_percentage' => $remainingPercentage,
            'active_activities_count' => $activeActivitiesCount,
            'active_students_count' => $activeStudentsCount,
            'expected_grades_count' => $expectedGradesCount,
            'completed_grades_count' => $completedGradesCount,
            'pending_grades_count' => $pendingGradesCount,
            'pending_issues' => $issues,
            'is_ready' => $isReady,
        ];
    }
}
