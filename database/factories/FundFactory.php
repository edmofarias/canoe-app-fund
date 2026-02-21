<?php

namespace Database\Factories;

use App\Models\Fund;
use App\Models\FundManager;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fund>
 */
class FundFactory extends Factory
{
    protected $model = Fund::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Fund',
            'start_year' => fake()->numberBetween(1990, 2024),
            'fund_manager_id' => FundManager::factory(),
        ];
    }

    /**
     * Create a fund with aliases.
     *
     * @param int $count Number of aliases to create
     * @return static
     */
    public function withAliases(int $count = 3): static
    {
        return $this->has(
            \App\Models\Alias::factory()->count($count),
            'aliases'
        );
    }

    /**
     * Create a fund with company associations.
     *
     * @param int $count Number of companies to associate
     * @return static
     */
    public function withCompanies(int $count = 3): static
    {
        return $this->hasAttached(
            \App\Models\Company::factory()->count($count),
        [],
            'companies'
        );
    }

    /**
     * Indicate that the fund is soft-deleted.
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
