<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;
    

    public function pending() {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'PENDING',
            ];
        });
    }

    public function refunded() {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'REFUNDED',
            ];
        });
    }

    public function forMembership() {
        return $this->state(function (array $attributes) {
            return [
                'payment_for' => 'MEMBERSHIP', // 'CLASS', 'RETAIL'
            ];
        });
    }
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $member = Member::inRandomOrder()->first();
        $gym = Gym::inRandomOrder()->first();

        return [
            'gym_id' => $gym->id,
            'member_id' => $member->id,
            'amount' => $this->faker->numberBetween(5000, 3000000),
            'payment_for' => $this->faker->randomElement(['MEMBERSHIP', 'CLASS', 'RETAIL']),
            'method' => $this->faker->randomElement(['CASH', 'DEBIT_CARD', 'BANK_TRANSFER', 'E_WALLET']),
            'status' => $this->faker->randomElement(['PAID', 'PENDING', 'REFUNDED', 'CANCELLED']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
