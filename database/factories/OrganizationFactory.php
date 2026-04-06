<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'          => fake()->company(),
            'inn'           => fake()->numerify('##########'),
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'status'        => 'verified',
            'created_by'    => User::factory(),
            'is_blocked'    => false,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function withBankDetails(): static
    {
        return $this->state(fn () => [
            'bank_name'             => 'ПАО Сбербанк',
            'bank_bik'              => '044525225',
            'bank_account'          => '40702810000000000001',
            'correspondent_account' => '30101810400000000225',
            'kpp'                   => '773601001',
            'offer_accepted'        => true,
        ]);
    }
}
