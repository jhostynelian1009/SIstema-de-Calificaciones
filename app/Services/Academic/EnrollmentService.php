<?php

namespace App\Services\Academic;

use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    /**
     * Create an Enrollment inside a transaction after validating entities and uniqueness.
     */
    public function createEnrollment(array $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            $student = User::where('id', $data['student_id'])
                ->where('role', UserRole::Student)
                ->where('active', true)
                ->first();

            if (!$student) {
                throw ValidationException::withMessages([
                    'student_id' => ['El usuario seleccionado no es un estudiante activo válido.'],
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

            $period = AcademicPeriod::where('id', $data['academic_period_id'])
                ->where('active', true)
                ->first();

            if (!$period) {
                throw ValidationException::withMessages([
                    'academic_period_id' => ['El período académico seleccionado no está activo o no existe.'],
                ]);
            }

            $existing = Enrollment::where('student_id', $student->id)
                ->where('academic_period_id', $period->id)
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'student_id' => ['El estudiante ya posee una matrícula registrada para este período académico.'],
                ]);
            }

            return Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'academic_period_id' => $period->id,
                'active' => isset($data['active']) ? (bool) $data['active'] : true,
            ]);
        });
    }

    /**
     * Update an Enrollment course inside a transaction.
     */
    public function updateEnrollment(Enrollment $enrollment, array $data): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $data) {
            $courseId = $data['course_id'] ?? $enrollment->course_id;

            $course = Course::where('id', $courseId)
                ->where('active', true)
                ->first();

            if (!$course) {
                throw ValidationException::withMessages([
                    'course_id' => ['El curso seleccionado no está activo o no existe.'],
                ]);
            }

            $enrollment->update([
                'course_id' => $course->id,
                'active' => isset($data['active']) ? (bool) $data['active'] : $enrollment->active,
            ]);

            return $enrollment->fresh();
        });
    }

    /**
     * Toggle active status of an Enrollment, verifying entity validity upon reactivation.
     */
    public function toggleStatus(Enrollment $enrollment): Enrollment
    {
        return DB::transaction(function () use ($enrollment) {
            if (!$enrollment->active) {
                // Check if student, course, and academic period are all active before reactivating
                if (!$enrollment->student?->active || $enrollment->student?->role !== UserRole::Student) {
                    throw ValidationException::withMessages([
                        'active' => ['No se puede activar la matrícula porque el estudiante está inactivo.'],
                    ]);
                }

                if (!$enrollment->course?->active) {
                    throw ValidationException::withMessages([
                        'active' => ['No se puede activar la matrícula porque el curso asignado está inactivo.'],
                    ]);
                }

                if (!$enrollment->academicPeriod?->active) {
                    throw ValidationException::withMessages([
                        'active' => ['No se puede activar la matrícula porque el período académico está inactivo.'],
                    ]);
                }
            }

            $enrollment->update([
                'active' => !$enrollment->active,
            ]);

            return $enrollment->fresh();
        });
    }
}
