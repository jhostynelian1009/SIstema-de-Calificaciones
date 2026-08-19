<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Partial;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $octavoA = Course::where('code', '8VO-A')->orWhere('name', 'Octavo A')->first();
        $matematicas = Subject::where('code', 'MAT8')->orWhere('name', 'Matemáticas')->first();
        $lengua = Subject::where('code', 'LIT8')->orWhere('name', 'Lengua y Literatura')->first();

        if (! $octavoA) {
            return;
        }

        // Case 1: Complete grading for Matemáticas Octavo A (P1 and P2)
        if ($matematicas) {
            $mathAssignment = TeachingAssignment::where('course_id', $octavoA->id)
                ->where('subject_id', $matematicas->id)
                ->first();

            if ($mathAssignment) {
                $teacherId = $mathAssignment->teacher_id;
                $students = Enrollment::where('course_id', $octavoA->id)
                    ->where('academic_period_id', $mathAssignment->academic_period_id)
                    ->where('active', true)
                    ->pluck('student_id')
                    ->toArray();

                $p1 = Partial::where('academic_period_id', $mathAssignment->academic_period_id)->where('number', 1)->first();
                $p2 = Partial::where('academic_period_id', $mathAssignment->academic_period_id)->where('number', 2)->first();

                // P1 Activities grading
                if ($p1) {
                    $p1Activities = Activity::where('teaching_assignment_id', $mathAssignment->id)
                        ->where('partial_id', $p1->id)
                        ->where('active', true)
                        ->get();

                    $sampleData = [
                        ['score' => 8.50, 'obs' => 'Buen desempeño; debe reforzar el procedimiento.'],
                        ['score' => 9.25, 'obs' => 'Resolvió correctamente la actividad y entregó a tiempo.'],
                        ['score' => 7.80, 'obs' => 'Participación satisfactoria; revisión en ejercicios prácticos.'],
                    ];

                    foreach ($p1Activities as $actIndex => $act) {
                        foreach ($students as $stuIndex => $studentId) {
                            $data = $sampleData[($actIndex + $stuIndex) % count($sampleData)];
                            Grade::firstOrCreate(
                                [
                                    'activity_id' => $act->id,
                                    'student_id' => $studentId,
                                ],
                                [
                                    'score' => $data['score'],
                                    'observation' => $data['obs'],
                                    'graded_by' => $teacherId,
                                    'graded_at' => now(),
                                ]
                            );
                        }
                    }
                }

                // P2 Activities grading
                if ($p2) {
                    $p2Activities = Activity::where('teaching_assignment_id', $mathAssignment->id)
                        ->where('partial_id', $p2->id)
                        ->where('active', true)
                        ->get();

                    $sampleDataP2 = [
                        ['score' => 10.00, 'obs' => 'Excelente presentación y dominio del contenido.'],
                        ['score' => 8.00, 'obs' => 'Demuestra comprensión adecuada de los conceptos.'],
                        ['score' => 8.75, 'obs' => 'Trabajo estructurado correctamente con observaciones menores.'],
                    ];

                    foreach ($p2Activities as $actIndex => $act) {
                        foreach ($students as $stuIndex => $studentId) {
                            $data = $sampleDataP2[($actIndex + $stuIndex) % count($sampleDataP2)];
                            Grade::firstOrCreate(
                                [
                                    'activity_id' => $act->id,
                                    'student_id' => $studentId,
                                ],
                                [
                                    'score' => $data['score'],
                                    'observation' => $data['obs'],
                                    'graded_by' => $teacherId,
                                    'graded_at' => now(),
                                ]
                            );
                        }
                    }
                }
            }
        }

        // Case 2: Incomplete grading for Lengua y Literatura Octavo A (P1)
        if ($lengua) {
            $lenguaAssignment = TeachingAssignment::where('course_id', $octavoA->id)
                ->where('subject_id', $lengua->id)
                ->first();

            if ($lenguaAssignment) {
                $teacherId = $lenguaAssignment->teacher_id;
                $students = Enrollment::where('course_id', $octavoA->id)
                    ->where('academic_period_id', $lenguaAssignment->academic_period_id)
                    ->where('active', true)
                    ->pluck('student_id')
                    ->toArray();

                $p1 = Partial::where('academic_period_id', $lenguaAssignment->academic_period_id)->where('number', 1)->first();

                if ($p1) {
                    $firstActivity = Activity::where('teaching_assignment_id', $lenguaAssignment->id)
                        ->where('partial_id', $p1->id)
                        ->where('active', true)
                        ->first();

                    if ($firstActivity) {
                        $sampleDataLengua = [
                            ['score' => 9.00, 'obs' => 'Análisis crítico bien desarrollado en el taller de lectura.'],
                            ['score' => 7.50, 'obs' => 'Cumple con los requisitos mínimos del trabajo escrito.'],
                            ['score' => 8.50, 'obs' => 'Buena capacidad de síntesis y vocabulario adecuado.'],
                        ];

                        foreach ($students as $stuIndex => $studentId) {
                            $data = $sampleDataLengua[$stuIndex % count($sampleDataLengua)];
                            Grade::firstOrCreate(
                                [
                                    'activity_id' => $firstActivity->id,
                                    'student_id' => $studentId,
                                ],
                                [
                                    'score' => $data['score'],
                                    'observation' => $data['obs'],
                                    'graded_by' => $teacherId,
                                    'graded_at' => now(),
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}
