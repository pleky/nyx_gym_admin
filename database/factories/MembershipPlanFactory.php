<?php

namespace Database\Factories;

use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MembershipPlan>
 */
class MembershipPlanFactory extends Factory
{
    protected $model = MembershipPlan::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Monthly', 'Quarterly', 'Yearly']),
            'duration_days' => $this->faker->randomElement([30, 90, 365]),
            'price' => $this->faker->randomFloat(2, 20, 500),
            'is_active' => true,
        ];
    }
}
