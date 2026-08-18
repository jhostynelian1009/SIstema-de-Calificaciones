<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Course;
use App\Models\Partial;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $octavoA = Course::where('code', '8VO-A')->orWhere('name', 'Octavo A')->first();
        $matematicas = Subject::where('code', 'MAT8')->orWhere('name', 'Matemáticas')->first();
        $lengua = Subject::where('code', 'LIT8')->orWhere('name', 'Lengua y Literatura')->first();

        if (!$octavoA) {
            return;
        }

        // Case 1: Complete weighting (100.00%) for Matemáticas Octavo A (P1 & P2)
        if ($matematicas) {
            $mathAssignment = TeachingAssignment::where('course_id', $octavoA->id)
                ->where('subject_id', $matematicas->id)
                ->first();

            if ($mathAssignment) {
                $p1 = Partial::where('academic_period_id', $mathAssignment->academic_period_id)->where('number', 1)->first();
                $p2 = Partial::where('academic_period_id', $mathAssignment->academic_period_id)->where('number', 2)->first();

                // P1 Activities
                if ($p1) {
                    Activity::firstOrCreate(
                        [
                            'teaching_assignment_id' => $mathAssignment->id,
                            'partial_id' => $p1->id,
                            'name' => 'Tarea 1',
                        ],
                        [
                            'description' => 'Ejercicios de álgebra lineal y ecuaciones de primer grado',
                            'percentage' => 20.00,
                            'active' => true,
                        ]
                    );

                    Activity::firstOrCreate(
                        [
                            'teaching_assignment_id' => $mathAssignment->id,
                            'partial_id' => $p1->id,
                            'name' => 'Trabajo práctico',
                        ],
                        [
                            'description' => 'Resolución de problemas aplicados en laboratorio de matemáticas',
                            'percentage' => 30.00,
                            'active' => true,
                        ]
                    );

                    Activity::firstOrCreate(
                        [
                            'teaching_assignment_id' => $mathAssignment->id,
                            'partial_id' => $p1->id,
                            'name' => 'Evaluación parcial',
                        ],
                        [
                            'description' => 'Examen escrito individual correspondiente al primer parcial',
                            'percentage' => 50.00,
                            'active' => true,
                        ]
                    );
                }

                // P2 Activities
                if ($p2) {
                    Activity::firstOrCreate(
                        [
                            'teaching_assignment_id' => $mathAssignment->id,
                            'partial_id' => $p2->id,
                            'name' => 'Tarea 2',
                        ],
                        [
                            'description' => 'Guía de ejercicios sobre geometría plana y ángulos',
                            'percentage' => 20.00,
                            'active' => true,
                        ]
                    );

                    Activity::firstOrCreate(
                        [
                            'teaching_assignment_id' => $mathAssignment->id,
                            'partial_id' => $p2->id,
                            'name' => 'Proyecto',
                        ],
                        [
                            'description' => 'Modelado geométrico y presentación en equipo',
                            'percentage' => 30.00,
                            'active' => true,
                        ]
                    );

                    Activity::firstOrCreate(
                        [
                            'teaching_assignment_id' => $mathAssignment->id,
                            'partial_id' => $p2->id,
                            'name' => 'Evaluación parcial',
                        ],
                        [
                            'description' => 'Examen escrito acumulativo del segundo parcial',
                            'percentage' => 50.00,
                            'active' => true,
                        ]
                    );
                }
            }
        }

        // Case 2: Incomplete weighting (50.00%) for Lengua y Literatura Octavo A (P1)
        if ($lengua) {
            $lenguaAssignment = TeachingAssignment::where('course_id', $octavoA->id)
                ->where('subject_id', $lengua->id)
                ->first();

            if ($lenguaAssignment) {
                $p1 = Partial::where('academic_period_id', $lenguaAssignment->academic_period_id)->where('number', 1)->first();

                if ($p1) {
                    Activity::firstOrCreate(
                        [
                            'teaching_assignment_id' => $lenguaAssignment->id,
                            'partial_id' => $p1->id,
                            'name' => 'Taller de lectura',
                        ],
                        [
                            'description' => 'Análisis crítico de texto literario y ensayo corto',
                            'percentage' => 20.00,
                            'active' => true,
                        ]
                    );

                    Activity::firstOrCreate(
                        [
                            'teaching_assignment_id' => $lenguaAssignment->id,
                            'partial_id' => $p1->id,
                            'name' => 'Exposición',
                        ],
                        [
                            'description' => 'Exposición oral sobre autores del siglo XX',
                            'percentage' => 30.00,
                            'active' => true,
                        ]
                    );
                }
            }
        }
    }
}
