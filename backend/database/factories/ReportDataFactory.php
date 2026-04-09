<?php

namespace Database\Factories;

use App\Models\ReportData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportData>
 */
class ReportDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_id' => 1, // you can override in tests
            'date' => now()->toDateString(),
            'rate' => $this->faker->randomFloat(4, 0.5, 1.5),
        ];
    }
}
