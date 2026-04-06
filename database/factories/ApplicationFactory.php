<?php

namespace Database\Factories;

use App\Models\Contest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contest_id'    => Contest::factory(),
            'user_id'       => User::factory(),
            'status'        => 'new',
            'external_link' => 'https://drive.google.com/example',
            'file_type'     => 'link',
        ];
    }

    public function paymentPending(): static
    {
        return $this->state(fn () => ['status' => 'payment_pending']);
    }
}
