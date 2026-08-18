<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Partial;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teaching_assignment_id' => TeachingAssignment::factory(),
            'partial_id' => Partial::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'due_date' => fake()->optional()->dateTimeBetween('+1 week', '+1 month'),
            'percentage' => fake()->randomElement([10.00, 20.00, 25.00, 30.00, 50.00]),
            'active' => true,
        ];
    }
}
