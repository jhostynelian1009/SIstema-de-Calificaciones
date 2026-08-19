<?php

namespace App\Services\Grades;

use App\Enums\PublicationStatus;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PartialPublicationService
{
    protected PartialReadinessService $readinessService;
    protected AuditService $auditService;

    public function __construct(
        PartialReadinessService $readinessService,
        AuditService $auditService
    ) {
        $this->readinessService = $readinessService;
        $this->auditService = $auditService;
    }

    /**
     * Publish or republish a partial for a teaching assignment.
     *
     * @throws InvalidArgumentException
     */
    public function publish(TeachingAssignment $assignment, Partial $partial, User $actor): PartialPublication
    {
        return DB::transaction(function () use ($assignment, $partial, $actor) {
            $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
                ->where('partial_id', $partial->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($publication->status === PublicationStatus::Published) {
                throw new InvalidArgumentException('El parcial ya se encuentra publicado.');
            }

            if (! $actor->isTeacher() || (int) $assignment->teacher_id !== (int) $actor->id) {
                throw new InvalidArgumentException('Solo el docente responsable asignado puede publicar las calificaciones del parcial.');
            }

            $readiness = $this->readinessService->checkReadiness($assignment, $partial);

            if (! $readiness['is_ready']) {
                $issuesText = implode(' ', $readiness['pending_issues']);
                throw new InvalidArgumentException("No se puede publicar el parcial debido a los siguientes requisitos pendientes: {$issuesText}");
            }

            $previousStatus = $publication->status->value;
            $auditAction = ($publication->status === PublicationStatus::Reopened)
                ? 'partial.republished'
                : 'partial.published';

            $publication->update([
                'status' => PublicationStatus::Published,
                'published_by' => $actor->id,
                'published_at' => now(),
            ]);

            $this->auditService->log(
                $auditAction,
                $publication,
                ['status' => $previousStatus],
                [
                    'status' => PublicationStatus::Published->value,
                    'published_by' => $actor->id,
                    'published_at' => $publication->published_at?->toDateTimeString(),
                ]
            );

            return $publication->fresh();
        });
    }

    /**
     * Reopen a published partial (Admin only).
     *
     * @throws InvalidArgumentException
     */
    public function reopen(TeachingAssignment $assignment, Partial $partial, string $reason, User $admin): PartialPublication
    {
        $trimmedReason = trim($reason);

        if (mb_strlen($trimmedReason) < 10 || mb_strlen($trimmedReason) > 500) {
            throw new InvalidArgumentException('El motivo de reapertura debe contener entre 10 y 500 caracteres.');
        }

        return DB::transaction(function () use ($assignment, $partial, $trimmedReason, $admin) {
            $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
                ->where('partial_id', $partial->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $admin->isAdmin()) {
                throw new InvalidArgumentException('Solo el usuario Administrador está autorizado para reabrir publicaciones.');
            }

            if ($publication->status !== PublicationStatus::Published) {
                throw new InvalidArgumentException('Únicamente se pueden reabrir parciales que se encuentren en estado publicado.');
            }

            $previousStatus = $publication->status->value;

            $publication->update([
                'status' => PublicationStatus::Reopened,
                'reopened_by' => $admin->id,
                'reopened_at' => now(),
                'reopen_reason' => $trimmedReason,
            ]);

            $this->auditService->log(
                'partial.reopened',
                $publication,
                ['status' => $previousStatus],
                [
                    'status' => PublicationStatus::Reopened->value,
                    'reopened_by' => $admin->id,
                    'reopened_at' => $publication->reopened_at?->toDateTimeString(),
                    'reopen_reason' => $trimmedReason,
                ]
            );

            return $publication->fresh();
        });
    }
}
