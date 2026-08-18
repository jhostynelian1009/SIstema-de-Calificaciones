<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeachingAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $period = AcademicPeriod::where('active', true)->first();
        if (!$period) {
            return;
        }

        $teacher1 = User::where('email', 'docente@calificaciones.local')->first();
        $teacher2 = User::where('email', 'docente2@calificaciones.local')->first();

        if (!$teacher1 || !$teacher2) {
            return;
        }

        $course8 = Course::where('code', '8VO-A')->first();
        $course9 = Course::where('code', '9NO-A')->first();

        $mat = Subject::where('code', 'MAT')->first();
        $lyl = Subject::where('code', 'LYL')->first();
        $ccnn = Subject::where('code', 'CCNN')->first();

        if (!$course8 || !$course9 || !$mat || !$lyl || !$ccnn) {
            return;
        }

        $assignments = [
            // Octavo A
            ['course_id' => $course8->id, 'subject_id' => $mat->id, 'teacher_id' => $teacher1->id],
            ['course_id' => $course8->id, 'subject_id' => $lyl->id, 'teacher_id' => $teacher2->id],
            ['course_id' => $course8->id, 'subject_id' => $ccnn->id, 'teacher_id' => $teacher1->id],
            // Noveno A
            ['course_id' => $course9->id, 'subject_id' => $mat->id, 'teacher_id' => $teacher2->id],
            ['course_id' => $course9->id, 'subject_id' => $lyl->id, 'teacher_id' => $teacher1->id],
            ['course_id' => $course9->id, 'subject_id' => $ccnn->id, 'teacher_id' => $teacher2->id],
        ];

        foreach ($assignments as $assignmentData) {
            TeachingAssignment::updateOrCreate(
                [
                    'course_id' => $assignmentData['course_id'],
                    'subject_id' => $assignmentData['subject_id'],
                    'academic_period_id' => $period->id,
                ],
                [
                    'teacher_id' => $assignmentData['teacher_id'],
                    'active' => true,
                ]
            );
        }
    }
}
