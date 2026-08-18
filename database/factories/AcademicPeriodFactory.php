<?php

namespace Database\Factories;

use App\Models\AcademicPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicPeriod>
 */
class AcademicPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2025, 2030);
        return [
            'name' => "Período académico {$startYear}–" . ($startYear + 1),
            'starts_at' => "{$startYear}-09-01",
            'ends_at' => ($startYear + 1) . "-07-31",
            'active' => false,
        ];
    }
}
