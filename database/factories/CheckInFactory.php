<?php

namespace Database\Factories;

use App\Models\CheckIn;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CheckIn>
 */
class CheckInFactory extends Factory
{
    protected $model = CheckIn::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $member = Member::factory()->create();
        $user = User::factory()->create();

        return [
            'member_id' => $member->id,
            'created_by' => $user->id,
            'check_in_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
