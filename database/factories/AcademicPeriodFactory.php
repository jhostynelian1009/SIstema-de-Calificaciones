<?php

namespace Database\Factories;

use App\Models\AcademicPeriod;
use App\Models\Partial;
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
        $startYear = fake()->unique()->numberBetween(2025, 2099);

        return [
            'name' => "Período académico {$startYear}–".($startYear + 1),
            'starts_at' => "{$startYear}-09-01",
            'ends_at' => ($startYear + 1).'-07-31',
            'active' => false,
        ];
    }

    /**
     * Configure the model factory to generate P1 and P2 after creation if they don't exist.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (AcademicPeriod $period) {
            if ($period->partials()->count() === 0) {
                Partial::create([
                    'academic_period_id' => $period->id,
                    'number' => 1,
                    'name' => 'Primer parcial',
                    'weight' => 50.00,
                ]);

                Partial::create([
                    'academic_period_id' => $period->id,
                    'number' => 2,
                    'name' => 'Segundo parcial',
                    'weight' => 50.00,
                ]);
            }
        });
    }
}
