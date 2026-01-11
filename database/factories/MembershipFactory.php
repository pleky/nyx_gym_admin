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

        $plan = MembershipPlan::factory()->create();
        $member = Member::factory()->create();

        $start = $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d');
        $end = date('Y-m-d', strtotime($start . ' + ' . $plan->duration_days . ' days'));


        return [
            'membership_plan_id' => $plan->id,
            'member_id' => $member->id,
            'start_date' => $start,
            'end_date' => $end,
            'status' => $this->faker->randomElement(['ACTIVE', 'EXPIRED', 'CANCELLED']),
            'price_paid' => $plan->price,
        ];
    }
}
