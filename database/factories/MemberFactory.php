<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'gender' => $this->faker->randomElement(['M', 'F', 'O']),
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'status' => 'ACTIVE',
            'gym_id' => Gym::inRandomOrder()->first()->id,
            'created_by' => User::inRandomOrder()->first()->id, 
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }

    public function inactive() {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'INACTIVE',
            ];
        });
    }

    public function withEmail() {
        return $this->state(function (array $attributes) {
            return [
                'email' => $this->faker->unique()->safeEmail(),
            ];
        });
    }
}
