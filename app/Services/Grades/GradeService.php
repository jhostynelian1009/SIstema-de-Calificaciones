<?php

namespace App\Services\Grades;

use App\Enums\PublicationStatus;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\PartialPublication;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GradeService
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Save or update an individual grade for a student and activity.
     */
    public function saveGrade(Activity $activity, int $studentId, float|string $score, string $observation, User $teacher): Grade
    {
        return DB::transaction(function () use ($activity, $studentId, $score, $observation, $teacher) {
            $assignment = $activity->teachingAssignment;
            $partial = $activity->partial;

            // Lock partial publication state for update
            $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
                ->where('partial_id', $partial->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($publication->status === PublicationStatus::Published) {
                throw new InvalidArgumentException('No se pueden registrar o modificar calificaciones en un parcial publicado.');
            }

            if (! $activity->active) {
                throw new InvalidArgumentException('No se pueden registrar calificaciones en una actividad inactiva.');
            }

            if (! $assignment->active) {
                throw new InvalidArgumentException('No se pueden registrar calificaciones en una asignación inactiva.');
            }

            if ((int) $assignment->teacher_id !== (int) $teacher->id) {
                throw new InvalidArgumentException('El docente autenticado no es el responsable actual de la asignación.');
            }

            // Check student active status and enrollment
            $student = User::where('id', $studentId)->where('active', true)->first();
            if (! $student || ! $student->isStudent()) {
                throw new InvalidArgumentException('El usuario calificado no es un estudiante activo.');
            }

            $hasActiveEnrollment = Enrollment::where('student_id', $studentId)
                ->where('course_id', $assignment->course_id)
                ->where('academic_period_id', $assignment->academic_period_id)
                ->where('active', true)
                ->exists();

            if (! $hasActiveEnrollment) {
                throw new InvalidArgumentException('El estudiante no posee una matrícula activa en el curso y período correspondiente.');
            }

            // Score & observation validation
            $scoreFloat = (float) $score;
            if ($scoreFloat < 0.00 || $scoreFloat > 10.00) {
                throw new InvalidArgumentException('La calificación debe estar comprendida entre 0.00 y 10.00.');
            }

            $trimmedObs = trim($observation);
            $obsLength = mb_strlen($trimmedObs);
            if ($obsLength < 3 || $obsLength > 500) {
                throw new InvalidArgumentException('La observación es requerida y debe contener entre 3 y 500 caracteres.');
            }

            $existingGrade = Grade::where('activity_id', $activity->id)
                ->where('student_id', $studentId)
                ->first();

            $grade = Grade::updateOrCreate(
                [
                    'activity_id' => $activity->id,
                    'student_id' => $studentId,
                ],
                [
                    'score' => $scoreFloat,
                    'observation' => $trimmedObs,
                    'graded_by' => $teacher->id,
                    'graded_at' => now(),
                ]
            );

            // Audit logging if partial is Reopened
            if ($publication->status === PublicationStatus::Reopened) {
                if ($existingGrade) {
                    $this->auditService->log(
                        'grade.updated_after_reopen',
                        $grade,
                        [
                            'score' => (float) $existingGrade->score,
                            'observation' => $existingGrade->observation,
                        ],
                        [
                            'score' => $scoreFloat,
                            'observation' => $trimmedObs,
                        ]
                    );
                } else {
                    $this->auditService->log(
                        'grade.created_after_reopen',
                        $grade,
                        null,
                        [
                            'student_id' => $studentId,
                            'score' => $scoreFloat,
                            'observation' => $trimmedObs,
                        ]
                    );
                }
            }

            return $grade;
        });
    }

    /**
     * Bulk upsert grades for an activity.
     */
    public function bulkUpsertGrades(Activity $activity, array $gradesData, User $teacher): array
    {
        return DB::transaction(function () use ($activity, $gradesData, $teacher) {
            $assignment = $activity->teachingAssignment;
            $partial = $activity->partial;

            // Lock publication state
            $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
                ->where('partial_id', $partial->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($publication->status === PublicationStatus::Published) {
                throw new InvalidArgumentException('No se pueden registrar o modificar calificaciones en un parcial publicado.');
            }

            if (! $activity->active) {
                throw new InvalidArgumentException('No se pueden registrar calificaciones en una actividad inactiva.');
            }

            if (! $assignment->active) {
                throw new InvalidArgumentException('No se pueden registrar calificaciones en una asignación inactiva.');
            }

            if ((int) $assignment->teacher_id !== (int) $teacher->id) {
                throw new InvalidArgumentException('El docente autenticado no es el responsable actual de la asignación.');
            }

            // Get valid enrolled student IDs
            $validStudentIds = Enrollment::where('course_id', $assignment->course_id)
                ->where('academic_period_id', $assignment->academic_period_id)
                ->where('active', true)
                ->whereHas('student', fn ($q) => $q->where('active', true))
                ->pluck('student_id')
                ->map(fn ($id) => (int) $id)
                ->toArray();

            $processedGrades = [];

            foreach ($gradesData as $item) {
                $studentId = isset($item['student_id']) ? (int) $item['student_id'] : null;
                $rawScore = $item['score'] ?? null;
                $rawObservation = $item['observation'] ?? null;

                $isScoreEmpty = $rawScore === null || $rawScore === '';
                $isObsEmpty = $rawObservation === null || trim((string) $rawObservation) === '';

                // Both fields empty -> skip row completely
                if ($isScoreEmpty && $isObsEmpty) {
                    continue;
                }

                if ($studentId === null || ! in_array($studentId, $validStudentIds, true)) {
                    throw new InvalidArgumentException("El estudiante ID {$studentId} no posee una matrícula activa en esta asignatura.");
                }

                // Score empty but observation provided -> error
                if ($isScoreEmpty && ! $isObsEmpty) {
                    throw new InvalidArgumentException("La calificación es obligatoria cuando se incluye una observación para el estudiante ID {$studentId}.");
                }

                // Observation empty or invalid when score is provided -> error
                if (! $isScoreEmpty && $isObsEmpty) {
                    throw new InvalidArgumentException("La observación es obligatoria (entre 3 y 500 caracteres) al registrar una nota para el estudiante ID {$studentId}.");
                }

                $scoreFloat = (float) $rawScore;
                if ($scoreFloat < 0.00 || $scoreFloat > 10.00) {
                    throw new InvalidArgumentException("La calificación para el estudiante ID {$studentId} debe estar comprendida entre 0.00 y 10.00.");
                }

                $trimmedObs = trim((string) $rawObservation);
                $obsLength = mb_strlen($trimmedObs);
                if ($obsLength < 3 || $obsLength > 500) {
                    throw new InvalidArgumentException("La observación para el estudiante ID {$studentId} debe contener entre 3 y 500 caracteres.");
                }

                $existingGrade = Grade::where('activity_id', $activity->id)
                    ->where('student_id', $studentId)
                    ->first();

                $grade = Grade::updateOrCreate(
                    [
                        'activity_id' => $activity->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'score' => $scoreFloat,
                        'observation' => $trimmedObs,
                        'graded_by' => $teacher->id,
                        'graded_at' => now(),
                    ]
                );

                if ($publication->status === PublicationStatus::Reopened) {
                    if ($existingGrade) {
                        $this->auditService->log(
                            'grade.updated_after_reopen',
                            $grade,
                            [
                                'score' => (float) $existingGrade->score,
                                'observation' => $existingGrade->observation,
                            ],
                            [
                                'score' => $scoreFloat,
                                'observation' => $trimmedObs,
                            ]
                        );
                    } else {
                        $this->auditService->log(
                            'grade.created_after_reopen',
                            $grade,
                            null,
                            [
                                'student_id' => $studentId,
                                'score' => $scoreFloat,
                                'observation' => $trimmedObs,
                            ]
                        );
                    }
                }

                $processedGrades[] = $grade;
            }

            return $processedGrades;
        });
    }
}
