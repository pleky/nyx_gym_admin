<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership>
 */
class MembershipFactory extends Factory
{
    protected $model = Membership::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $plan = MembershipPlan::inRandomOrder()->first();
        $member = Member::inRandomOrder()->first();

        $start = $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d');
        $end = date('Y-m-d', strtotime($start . ' + ' . $plan->duration_days . ' days'));


        return [
            'membership_plan_id' => $plan->id,
            'member_id' => $member->id,
            'start_date' => $start,
            'end_date' => $end,
            'status' => $this->faker->randomElement(['ACTIVE', 'EXPIRED', 'CANCELLED', 'PENDING_RENEWAL']),
            'auto_renew' => $this->faker->boolean(30),
            'gym_id' => $member->gym_id,
        ];
    }

    public function active() {
        return $this->state(['status' => 'ACTIVE']);
    }

    public function expired() {
        return $this->state(['status' => 'EXPIRED']);
    }

    public function withAutoRenew() {
        return $this->state(['auto_renew' => true]);
    }
}
