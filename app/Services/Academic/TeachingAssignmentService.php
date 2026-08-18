<?php

namespace App\Services\Academic;

use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Grades\PartialPublicationStateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeachingAssignmentService
{
    /**
     * Create a TeachingAssignment inside a transaction after validating entities and course+subject+period uniqueness.
     * Automatically initializes P1 and P2 partial publication draft states.
     */
    public function createAssignment(array $data): TeachingAssignment
    {
        return DB::transaction(function () use ($data) {
            $teacher = User::where('id', $data['teacher_id'])
                ->where('role', UserRole::Teacher)
                ->where('active', true)
                ->first();

            if (!$teacher) {
                throw ValidationException::withMessages([
                    'teacher_id' => ['El usuario seleccionado no es un docente activo válido.'],
                ]);
            }

            $course = Course::where('id', $data['course_id'])
                ->where('active', true)
                ->first();

            if (!$course) {
                throw ValidationException::withMessages([
                    'course_id' => ['El curso seleccionado no está activo o no existe.'],
                ]);
            }

            $subject = Subject::where('id', $data['subject_id'])
                ->where('active', true)
                ->first();

            if (!$subject) {
                throw ValidationException::withMessages([
                    'subject_id' => ['La asignatura seleccionada no está activa o no existe.'],
                ]);
            }

            $period = AcademicPeriod::where('id', $data['academic_period_id'])
                ->where('active', true)
                ->first();

            if (!$period) {
                throw ValidationException::withMessages([
                    'academic_period_id' => ['El período académico seleccionado no está activo o no existe.'],
                ]);
            }

            $existing = TeachingAssignment::where('course_id', $course->id)
                ->where('subject_id', $subject->id)
                ->where('academic_period_id', $period->id)
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'course_id' => ['Ya existe una asignación docente registrada para este curso, asignatura y período académico.'],
                ]);
            }

            $assignment = TeachingAssignment::create([
                'teacher_id' => $teacher->id,
                'course_id' => $course->id,
                'subject_id' => $subject->id,
                'academic_period_id' => $period->id,
                'active' => isset($data['active']) ? (bool) $data['active'] : true,
            ]);

            // Initialize P1 and P2 partial publication draft states in the same transaction
            app(PartialPublicationStateService::class)->ensureForAssignment($assignment);

            return $assignment;
        });
    }

    /**
     * Reassign teacher for an existing TeachingAssignment without touching existing publication states.
     */
    public function reassignTeacher(TeachingAssignment $assignment, int $newTeacherId): TeachingAssignment
    {
        return DB::transaction(function () use ($assignment, $newTeacherId) {
            $teacher = User::where('id', $newTeacherId)
                ->where('role', UserRole::Teacher)
                ->where('active', true)
                ->first();

            if (!$teacher) {
                throw ValidationException::withMessages([
                    'teacher_id' => ['El usuario seleccionado no es un docente activo válido.'],
                ]);
            }

            $assignment->update([
                'teacher_id' => $teacher->id,
            ]);

            return $assignment->fresh();
        });
    }

    /**
     * Update an assignment (reassign teacher or update active status).
     */
    public function updateAssignment(TeachingAssignment $assignment, array $data): TeachingAssignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            if (isset($data['teacher_id']) && (int) $data['teacher_id'] !== (int) $assignment->teacher_id) {
                $this->reassignTeacher($assignment, (int) $data['teacher_id']);
            }

            if (isset($data['active'])) {
                $assignment->update(['active' => (bool) $data['active']]);
            }

            return $assignment->fresh();
        });
    }

    /**
     * Toggle active status of a TeachingAssignment after checking relation validity.
     */
    public function toggleStatus(TeachingAssignment $assignment): TeachingAssignment
    {
        return DB::transaction(function () use ($assignment) {
            if (!$assignment->active) {
                if (!$assignment->teacher?->active || $assignment->teacher?->role !== UserRole::Teacher) {
                    throw ValidationException::withMessages([
                        'active' => ['No se puede activar la asignación porque el docente asignado está inactivo.'],
                    ]);
                }

                if (!$assignment->course?->active) {
                    throw ValidationException::withMessages([
                        'active' => ['No se puede activar la asignación porque el curso está inactivo.'],
                    ]);
                }

                if (!$assignment->subject?->active) {
                    throw ValidationException::withMessages([
                        'active' => ['No se puede activar la asignación porque la asignatura está inactiva.'],
                    ]);
                }

                if (!$assignment->academicPeriod?->active) {
                    throw ValidationException::withMessages([
                        'active' => ['No se puede activar la asignación porque el período académico está inactivo.'],
                    ]);
                }

                // Ensure publication states exist when reactivating
                app(PartialPublicationStateService::class)->ensureForAssignment($assignment);
            }

            $assignment->update([
                'active' => !$assignment->active,
            ]);

            return $assignment->fresh();
        });
    }
}
