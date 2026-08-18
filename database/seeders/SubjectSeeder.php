<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                'name' => 'Matemáticas',
                'code' => 'MAT',
                'description' => 'Área de razonamiento lógico y matemático',
                'active' => true,
            ],
            [
                'name' => 'Lengua y Literatura',
                'code' => 'LYL',
                'description' => 'Área de lenguaje, comunicación y literatura',
                'active' => true,
            ],
            [
                'name' => 'Ciencias Naturales',
                'code' => 'CCNN',
                'description' => 'Área de ciencias naturales y del entorno',
                'active' => true,
            ],
        ];

        foreach ($subjects as $subjectData) {
            Subject::updateOrCreate(
                ['code' => $subjectData['code']],
                [
                    'name' => $subjectData['name'],
                    'description' => $subjectData['description'],
                    'active' => $subjectData['active'],
                ]
            );
        }
    }
}
