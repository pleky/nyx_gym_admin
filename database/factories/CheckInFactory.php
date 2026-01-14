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
        $member = Member::inRandomOrder()->first();
        $checkedInBy = $this->faker->randomElement([
            // 70% Staff-Assisted (mayoritas gym Indonesia masih manual)
            'Staff: ' . $this->faker->name(),
            'Staff: ' . $this->faker->name(),
            'Staff: ' . $this->faker->name(),
            'Staff: ' . $this->faker->name(),
            'Staff: ' . $this->faker->name(),
            'Staff: ' . $this->faker->name(),
            'Staff: ' . $this->faker->name(),
            
            // 20% Self-Service Kiosk
            'Kiosk #1',
            'Kiosk #2',
            
            // 10% Mobile App
            'Mobile App',
        ]);

        return [
            'member_id' => $member->id,
            'checked_in_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'checked_in_by' => $checkedInBy,
            'gym_id' => $member->gym_id,
        ];
    }

    public function today() {
        return $this->state(['checked_in_at' => now()->setHour(rand(6, 22))]);
    }

    public function recent() {
        return $this->state(['checked_in_at' => now()->subHours(rand(1, 6))]);
    }

    public function staffAssisted() {
        return $this->state(['checked_in_by' => 'Staff: ' . $this->faker->name()]);
    }

    public function kiosk() {
        return $this->state(['checked_in_by' => 'Kiosk #' . rand(1, 3)]);
    }
}
