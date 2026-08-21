<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $period = AcademicPeriod::where('active', true)->first();
        if (! $period) {
            return;
        }

        $course8 = Course::where('code', '8VO-A')->first();
        $course9 = Course::where('code', '9NO-A')->first();

        if (! $course8 || ! $course9) {
            return;
        }

        // Students 1..3 in Octavo A
        for ($i = 1; $i <= 3; $i++) {
            $student = User::where('email', "estudiante{$i}@calificaciones.local")->first();
            if ($student) {
                Enrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_period_id' => $period->id,
                    ],
                    [
                        'course_id' => $course8->id,
                        'active' => true,
                    ]
                );
            }
        }

        // Students 4..6 in Noveno A
        for ($i = 4; $i <= 6; $i++) {
            $student = User::where('email', "estudiante{$i}@calificaciones.local")->first();
            if ($student) {
                Enrollment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_period_id' => $period->id,
                    ],
                    [
                        'course_id' => $course9->id,
                        'active' => true,
                    ]
                );
            }
        }
    }
}
