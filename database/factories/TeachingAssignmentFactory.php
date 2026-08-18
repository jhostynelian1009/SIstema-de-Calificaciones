<?php

namespace Database\Factories;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeachingAssignment>
 */
class TeachingAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory()->teacher(),
            'course_id' => Course::factory(),
            'subject_id' => Subject::factory(),
            'academic_period_id' => AcademicPeriod::factory(),
            'active' => true,
        ];
    }
}
