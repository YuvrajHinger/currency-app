<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'currency' => $this->faker->randomElement(['USD','EUR','INR']),
            'range' => $this->faker->randomElement(['1m','6m','1y']),
            'interval' => $this->faker->randomElement(['daily','weekly','monthly']),
            'status' => 'pending',
        ];
    }
}
