<?php

namespace Database\Factories;

use App\Models\FundManager;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FundManager>
 */
class FundManagerFactory extends Factory
{
    protected $model = FundManager::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Management',
        ];
    }

    /**
     * Indicate that the fund manager is soft-deleted.
     *
     * @return static
     */
    public function deleted(): static
    {
        return $this->state(fn(array $attributes) => [
        'deleted_at' => now(),
        ]);
    }
}
