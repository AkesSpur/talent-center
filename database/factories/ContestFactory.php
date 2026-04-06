<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ContestFactory extends Factory
{
    public function definition(): array
    {
        $start = Carbon::now()->subDays(5);
        $end   = Carbon::now()->addDays(10);

        return [
            'organization_id'       => Organization::factory(),
            'created_by'            => User::factory(),
            'title'                 => fake()->sentence(4),
            'description'           => fake()->paragraph(),
            'rules'                 => fake()->paragraph(),
            'status'                => 'accepting',
            'is_permanent'          => false,
            'is_paid'               => false,
            'entry_fee'             => null,
            'application_limit'     => 50,
            'applications_start_at' => $start,
            'applications_end_at'   => $end,
            'evaluation_end_at'     => $end->copy()->addDays(10),
        ];
    }

    public function paid(int $fee = 500): static
    {
        return $this->state(fn () => [
            'is_paid'           => true,
            'entry_fee'         => $fee,
            'application_limit' => 0,
        ]);
    }

    public function withLimit(int $limit): static
    {
        return $this->state(fn () => ['application_limit' => $limit]);
    }

    public function accepting(): static
    {
        return $this->state(fn () => ['status' => 'accepting']);
    }
}
