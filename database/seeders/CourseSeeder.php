<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'name' => 'Octavo A',
                'code' => '8VO-A',
                'description' => 'Paralelo A de octavo año de educación general básica',
                'active' => true,
            ],
            [
                'name' => 'Noveno A',
                'code' => '9NO-A',
                'description' => 'Paralelo A de noveno año de educación general básica',
                'active' => true,
            ],
        ];

        foreach ($courses as $courseData) {
            Course::updateOrCreate(
                ['code' => $courseData['code']],
                [
                    'name' => $courseData['name'],
                    'description' => $courseData['description'],
                    'active' => $courseData['active'],
                ]
            );
        }
    }
}
