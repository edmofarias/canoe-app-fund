<?php

namespace Database\Factories;

use App\Models\Alias;
use App\Models\Fund;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alias>
 */
class AliasFactory extends Factory
{
    protected $model = Alias::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company() . ' ' . fake()->word(),
            'fund_id' => Fund::factory(),
        ];
    }
}
