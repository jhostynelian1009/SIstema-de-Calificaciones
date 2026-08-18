<?php

namespace App\Services\Grades;

use App\Enums\PublicationStatus;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PartialPublicationStateService
{
    /**
     * Validate that a partial belongs to the same academic period as a teaching assignment.
     *
     * @throws InvalidArgumentException
     */
    public function validateCoherence(TeachingAssignment $assignment, Partial $partial): bool
    {
        if ((int) $assignment->academic_period_id !== (int) $partial->academic_period_id) {
            throw new InvalidArgumentException("El parcial #{$partial->id} no pertenece al período académico de la asignación docente #{$assignment->id}.");
        }

        return true;
    }

    /**
     * Ensure P1 and P2 partial publication draft rows exist for a given teaching assignment.
     *
     * @throws InvalidArgumentException
     */
    public function ensureForAssignment(TeachingAssignment $assignment): Collection
    {
        return DB::transaction(function () use ($assignment) {
            $periodId = $assignment->academic_period_id;

            $partials = Partial::where('academic_period_id', $periodId)
                ->orderBy('number', 'asc')
                ->get();

            if ($partials->count() !== 2) {
                throw new InvalidArgumentException("El período académico debe poseer exactamente 2 parciales configurados.");
            }

            $numbers = $partials->pluck('number')->toArray();
            if ($numbers !== [1, 2]) {
                throw new InvalidArgumentException("Los parciales del período académico deben corresponder exactamente a los números 1 y 2.");
            }

            foreach ($partials as $partial) {
                if ((float) $partial->weight !== 50.00) {
                    throw new InvalidArgumentException("Cada parcial del período académico debe poseer un peso de 50.00%.");
                }

                // Strict Period Coherence check
                $this->validateCoherence($assignment, $partial);

                PartialPublication::firstOrCreate(
                    [
                        'teaching_assignment_id' => $assignment->id,
                        'partial_id' => $partial->id,
                    ],
                    [
                        'status' => PublicationStatus::Draft,
                    ]
                );
            }

            return PartialPublication::where('teaching_assignment_id', $assignment->id)
                ->with('partial')
                ->get()
                ->sortBy(fn ($pub) => $pub->partial->number)
                ->values();
        });
    }

    /**
     * Ensure partial publication states exist for all teaching assignments idempotently.
     */
    public function ensureForAllAssignments(): int
    {
        $assignments = TeachingAssignment::with(['academicPeriod.partials'])->get();
        $processedCount = 0;

        foreach ($assignments as $assignment) {
            $this->ensureForAssignment($assignment);
            $processedCount++;
        }

        return $processedCount;
    }

    /**
     * Retrieve partial publication states for a specific teaching assignment.
     */
    public function getPublicationsForAssignment(TeachingAssignment $assignment): Collection
    {
        return PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->with(['partial', 'publishedBy', 'reopenedBy'])
            ->get()
            ->sortBy(fn ($pub) => $pub->partial->number)
            ->values();
    }

    /**
     * Retrieve partial publication states for a specific academic period.
     */
    public function getPublicationsForPeriod($periodId): Collection
    {
        return PartialPublication::whereHas('teachingAssignment', function ($q) use ($periodId) {
            $q->where('academic_period_id', $periodId);
        })
            ->with(['teachingAssignment.teacher', 'teachingAssignment.course', 'teachingAssignment.subject', 'partial'])
            ->get();
    }

    /**
     * Retrieve partial publication states for a specific teacher.
     */
    public function getPublicationsForTeacher($teacherId): Collection
    {
        return PartialPublication::whereHas('teachingAssignment', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId)->where('active', true);
        })
            ->with(['teachingAssignment.course', 'teachingAssignment.subject', 'partial'])
            ->get();
    }
}
