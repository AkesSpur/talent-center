<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Contest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id'  => Application::factory(),
            'contest_id'      => Contest::factory(),
            'user_id'         => User::factory(),
            'tbank_order_id'  => 'app-' . fake()->randomNumber(3) . '-' . Str::random(6),
            'tbank_payment_id' => null,
            'amount'          => 500,
            'status'          => 'accepted',
            'receipt_data'    => null,
        ];
    }
}
