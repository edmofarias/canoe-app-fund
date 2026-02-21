<?php

namespace Database\Factories;

use App\Models\DuplicateWarning;
use App\Models\Fund;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DuplicateWarning>
 */
class DuplicateWarningFactory extends Factory
{
    protected $model = DuplicateWarning::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fund_id_1' => Fund::factory(),
            'fund_id_2' => Fund::factory(),
            'resolved' => false,
        ];
    }
}
