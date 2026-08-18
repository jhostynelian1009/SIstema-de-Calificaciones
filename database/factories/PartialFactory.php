<?php

namespace Database\Factories;

use App\Models\AcademicPeriod;
use App\Models\Partial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Partial>
 */
class PartialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_period_id' => AcademicPeriod::factory(),
            'number' => 1,
            'name' => 'Primer parcial',
            'weight' => 50.00,
        ];
    }
}
