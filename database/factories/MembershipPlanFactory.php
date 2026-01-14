<?php

namespace Database\Factories;

use App\Models\Gym;
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
        $gym = Gym::inRandomOrder()->first();
        $plans = [
            ['name' => 'Monthly', 'duration' => 30, 'price' => 250000],
            ['name' => '3 Months', 'duration' => 90, 'price' => 650000],
            ['name' => '6 Months', 'duration' => 180, 'price' => 1200000],
            ['name' => 'Annual', 'duration' => 365, 'price' => 2200000],
        ];
        $plan = $this->faker->randomElement($plans);
    
        return [
            'name' => $plan['name'],
            'duration_days' => $plan['duration'],
            'price' => $plan['price'],
            'is_active' => $this->faker->boolean(90),
            'gym_id' => $gym->id,
            'description' => $this->faker->optional(0.5)->sentence(),
        ];
    }

    public function monthly() {
    return $this->state([
        'name' => 'Monthly',
        'duration_days' => 30,
        'price' => 250000,
        ]);
    }

    public function annual() {
        return $this->state([
            'name' => 'Annual',
            'duration_days' => 365,
            'price' => 2200000,
        ]);
    }

    public function inactive() {
        return $this->state(['is_active' => false]);
    }
}
