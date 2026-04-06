<?php

namespace Database\Factories;

use App\Models\Contest;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayoutRegistryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'contest_id'      => Contest::factory(),
            'payout_amount'   => fake()->numberBetween(0, 10000),
            'payment_received' => false,
        ];
    }
}
