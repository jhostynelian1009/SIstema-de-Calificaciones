<?php

namespace App\Services\Grades;

use App\Enums\PublicationStatus;
use App\Models\Activity;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ActivityService
{
    /**
     * Convert percentage decimal string/float to integer hundredths (units).
     * 25.50 % => 2550 units
     * 100.00 % => 10000 units
     */
    public function percentageToUnits(float|string $percentage): int
    {
        return (int) round(((float) $percentage) * 100);
    }

    /**
     * Convert integer hundredths (units) to formatted decimal string.
     * 2550 units => 25.50
     */
    public function unitsToPercentage(int $units): string
    {
        return number_format($units / 100, 2, '.', '');
    }

    /**
     * Validate period coherence and assignment validity.
     *
     * @throws InvalidArgumentException
     */
    public function validateCoherence(TeachingAssignment $assignment, Partial $partial): void
    {
        if ((int) $assignment->academic_period_id !== (int) $partial->academic_period_id) {
            throw new InvalidArgumentException("El parcial (P{$partial->number}) no pertenece al período académico de la asignación docente.");
        }

        if (! in_array((int) $partial->number, [1, 2], true)) {
            throw new InvalidArgumentException('El parcial debe ser P1 o P2.');
        }

        if ((float) $partial->weight !== 50.00) {
            throw new InvalidArgumentException('El parcial debe poseer un peso de 50.00%.');
        }
    }

    /**
     * Lock partial publication row and verify that the partial publication status allows modifications.
     *
     * @throws InvalidArgumentException
     */
    protected function lockAndVerifyPublicationStatus(TeachingAssignment $assignment, Partial $partial): PartialPublication
    {
        $publicationState = PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->lockForUpdate()
            ->first();

        if (! $publicationState) {
            // Fallback: Ensure publication state row exists
            app(PartialPublicationStateService::class)->ensureForAssignment($assignment);
            $publicationState = PartialPublication::where('teaching_assignment_id', $assignment->id)
                ->where('partial_id', $partial->id)
                ->lockForUpdate()
                ->firstOrFail();
        }

        if ($publicationState->status === PublicationStatus::Published) {
            throw new InvalidArgumentException('No se pueden crear ni modificar actividades en un parcial que se encuentra publicado.');
        }

        return $publicationState;
    }

    /**
     * Create a new activity inside a transaction with lockForUpdate on partial_publications.
     *
     * @throws InvalidArgumentException
     */
    public function createActivity(TeachingAssignment $assignment, Partial $partial, array $data, User $teacher): Activity
    {
        return DB::transaction(function () use ($assignment, $partial, $data, $teacher) {
            if (! $teacher->isAdmin() && (int) $assignment->teacher_id !== (int) $teacher->id) {
                throw new InvalidArgumentException('No está autorizado para gestionar actividades en esta asignación docente.');
            }

            if (! $assignment->active) {
                throw new InvalidArgumentException('No se pueden registrar actividades en una asignación docente inactiva.');
            }

            $this->validateCoherence($assignment, $partial);
            $this->lockAndVerifyPublicationStatus($assignment, $partial);

            $percentageUnits = $this->percentageToUnits($data['percentage']);
            if ($percentageUnits <= 0 || $percentageUnits > 10000) {
                throw new InvalidArgumentException('El porcentaje de la actividad debe ser mayor que 0.00% y menor o igual que 100.00%.');
            }

            // Calculate current total active units for this assignment & partial
            $currentActiveUnits = $this->calculateTotalActiveUnits($assignment->id, $partial->id);
            $newTotalUnits = $currentActiveUnits + $percentageUnits;

            if ($newTotalUnits > 10000) {
                $remainingDecimal = $this->unitsToPercentage(10000 - $currentActiveUnits);
                throw new InvalidArgumentException("La suma de porcentajes de las actividades activas no puede superar el 100.00%. Porcentaje disponible: {$remainingDecimal}%.");
            }

            // Validate due date if provided
            if (! empty($data['due_date'])) {
                $this->validateDueDate($data['due_date'], $assignment->academicPeriod);
            }

            return Activity::create([
                'teaching_assignment_id' => $assignment->id,
                'partial_id' => $partial->id,
                'name' => trim($data['name']),
                'description' => isset($data['description']) ? trim($data['description']) : null,
                'due_date' => $data['due_date'] ?? null,
                'percentage' => $this->unitsToPercentage($percentageUnits),
                'active' => true,
            ]);
        });
    }

    /**
     * Update an existing activity inside a transaction with lockForUpdate.
     *
     * @throws InvalidArgumentException
     */
    public function updateActivity(Activity $activity, array $data, User $teacher): Activity
    {
        return DB::transaction(function () use ($activity, $data, $teacher) {
            $assignment = $activity->teachingAssignment;
            $partial = $activity->partial;

            if (! $teacher->isAdmin() && (int) $assignment->teacher_id !== (int) $teacher->id) {
                throw new InvalidArgumentException('No está autorizado para modificar esta actividad.');
            }

            if (! $assignment->active) {
                throw new InvalidArgumentException('No se pueden modificar actividades de una asignación inactiva.');
            }

            $this->lockAndVerifyPublicationStatus($assignment, $partial);

            $newUnits = $this->percentageToUnits($data['percentage']);
            if ($newUnits <= 0 || $newUnits > 10000) {
                throw new InvalidArgumentException('El porcentaje de la actividad debe ser mayor que 0.00% y menor o igual que 100.00%.');
            }

            // Sum all other active activities
            $otherActiveUnits = Activity::where('teaching_assignment_id', $assignment->id)
                ->where('partial_id', $partial->id)
                ->where('id', '!=', $activity->id)
                ->where('active', true)
                ->get()
                ->sum(fn ($act) => $this->percentageToUnits($act->percentage));

            $newTotalUnits = $activity->active ? ($otherActiveUnits + $newUnits) : $otherActiveUnits;

            if ($activity->active && $newTotalUnits > 10000) {
                $remainingDecimal = $this->unitsToPercentage(10000 - $otherActiveUnits);
                throw new InvalidArgumentException("La suma de porcentajes de las actividades activas no puede superar el 100.00%. Porcentaje disponible: {$remainingDecimal}%.");
            }

            if (! empty($data['due_date'])) {
                $this->validateDueDate($data['due_date'], $assignment->academicPeriod);
            }

            $activity->update([
                'name' => trim($data['name']),
                'description' => isset($data['description']) ? trim($data['description']) : null,
                'due_date' => $data['due_date'] ?? null,
                'percentage' => $this->unitsToPercentage($newUnits),
            ]);

            return $activity->fresh();
        });
    }

    /**
     * Toggle the active status of an activity inside a transaction with lockForUpdate.
     *
     * @throws InvalidArgumentException
     */
    public function toggleActivityStatus(Activity $activity, User $teacher): Activity
    {
        return DB::transaction(function () use ($activity, $teacher) {
            $assignment = $activity->teachingAssignment;
            $partial = $activity->partial;

            if (! $teacher->isAdmin() && (int) $assignment->teacher_id !== (int) $teacher->id) {
                throw new InvalidArgumentException('No está autorizado para cambiar el estado de esta actividad.');
            }

            if (! $assignment->active) {
                throw new InvalidArgumentException('No se puede cambiar el estado de actividades en una asignación inactiva.');
            }

            $this->lockAndVerifyPublicationStatus($assignment, $partial);

            if (! $activity->active) {
                // Reactivating activity: check if adding its percentage exceeds 10000 units
                $currentActiveUnits = $this->calculateTotalActiveUnits($assignment->id, $partial->id);
                $activityUnits = $this->percentageToUnits($activity->percentage);
                $newTotalUnits = $currentActiveUnits + $activityUnits;

                if ($newTotalUnits > 10000) {
                    $remainingDecimal = $this->unitsToPercentage(10000 - $currentActiveUnits);
                    throw new InvalidArgumentException("No se puede reactivar la actividad porque superaría el 100.00%. Porcentaje disponible: {$remainingDecimal}%.");
                }
            }

            $activity->update([
                'active' => ! $activity->active,
            ]);

            return $activity->fresh();
        });
    }

    /**
     * Calculate total active percentage units (integer hundredths) for an assignment and partial.
     */
    public function calculateTotalActiveUnits(int $assignmentId, int $partialId): int
    {
        return Activity::where('teaching_assignment_id', $assignmentId)
            ->where('partial_id', $partialId)
            ->where('active', true)
            ->get()
            ->sum(fn ($act) => $this->percentageToUnits($act->percentage));
    }

    /**
     * Get summary metrics for activities of an assignment and partial.
     */
    public function getSummary(TeachingAssignment $assignment, Partial $partial): array
    {
        $totalUnits = $this->calculateTotalActiveUnits($assignment->id, $partial->id);
        $remainingUnits = max(0, 10000 - $totalUnits);

        $activeCount = Activity::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->where('active', true)
            ->count();

        if ($totalUnits === 0) {
            $weightedStatus = 'empty';
            $weightedStatusLabel = 'Sin actividades';
            $badgeClass = 'bg-secondary';
        } elseif ($totalUnits < 10000) {
            $weightedStatus = 'incomplete';
            $weightedStatusLabel = 'Ponderación incompleta';
            $badgeClass = 'bg-warning text-dark';
        } else {
            $weightedStatus = 'weighted_complete';
            $weightedStatusLabel = 'Ponderación completa';
            $badgeClass = 'bg-success';
        }

        $publicationState = PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->first();

        $publicationStatus = $publicationState?->status ?? PublicationStatus::Draft;

        return [
            'total_percentage' => $this->unitsToPercentage($totalUnits),
            'remaining_percentage' => $this->unitsToPercentage($remainingUnits),
            'total_units' => $totalUnits,
            'remaining_units' => $remainingUnits,
            'active_count' => $activeCount,
            'weighted_status' => $weightedStatus,
            'weighted_status_label' => $weightedStatusLabel,
            'badge_class' => $badgeClass,
            'publication_status' => $publicationStatus,
            'publication_status_label' => $publicationStatus->label(),
            'is_published' => $publicationStatus === PublicationStatus::Published,
        ];
    }

    /**
     * Validate that a due date falls within the academic period bounds.
     *
     * @throws InvalidArgumentException
     */
    protected function validateDueDate(string $dueDate, $period): void
    {
        if (! $period) {
            return;
        }

        if ($period->starts_at && $dueDate < $period->starts_at->format('Y-m-d')) {
            throw new InvalidArgumentException("La fecha de entrega no puede ser anterior al inicio del período académico ({$period->starts_at->format('d/m/Y')}).");
        }

        if ($period->ends_at && $dueDate > $period->ends_at->format('Y-m-d')) {
            throw new InvalidArgumentException("La fecha de entrega no puede ser posterior a la finalización del período académico ({$period->ends_at->format('d/m/Y')}).");
        }
    }
}
