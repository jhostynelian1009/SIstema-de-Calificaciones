<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartialPublication>
 */
class PartialPublicationFactory extends Factory
{
    protected $model = PartialPublication::class;

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
            'status' => PublicationStatus::Draft,
            'published_by' => null,
            'published_at' => null,
            'reopened_by' => null,
            'reopened_at' => null,
            'reopen_reason' => null,
        ];
    }

    /**
     * State for Published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Published,
            'published_at' => now(),
        ]);
    }

    /**
     * State for Reopened.
     */
    public function reopened(string $reason = 'Reapertura de prueba'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Reopened,
            'reopened_at' => now(),
            'reopen_reason' => $reason,
        ]);
    }
}
