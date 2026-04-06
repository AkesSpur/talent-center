<?php

declare(strict_types=1);

namespace Tests\Feature\Stage5;

use App\Models\Organization;
use App\Models\User;
use Database\Factories\OrganizationFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContestPaidFieldsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organization $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'participant']);
        $this->org  = Organization::factory()->create([
            'created_by' => $this->user->id,
            'status'     => 'verified',
        ]);
        $this->org->representatives()->attach($this->user->id, [
            'can_create'   => true,
            'can_manage'   => true,
            'can_evaluate' => true,
        ]);
    }

    public function test_free_contest_stores_is_paid_false_and_null_entry_fee(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('contests.store'), $this->contestData([
            'organization_id' => $this->org->id,
            'is_paid'         => false,
            'application_limit' => 20,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('contests', [
            'organization_id'   => $this->org->id,
            'is_paid'           => 0,
            'entry_fee'         => null,
            'application_limit' => 20,
        ]);
    }

    public function test_paid_contest_requires_entry_fee(): void
    {
        $this->actingAs($this->user);

        // Give org paid contest eligibility
        $this->org->update([
            'bank_name' => 'Bank', 'bank_bik' => '123456789',
            'bank_account' => '40702810000000000001',
            'correspondent_account' => '30101810400000000225',
            'kpp' => '773601001', 'offer_accepted' => true,
        ]);

        $response = $this->post(route('contests.store'), $this->contestData([
            'organization_id' => $this->org->id,
            'is_paid'         => 1,
            // no entry_fee
        ]));

        $response->assertSessionHasErrors('entry_fee');
    }

    public function test_entry_fee_below_100_is_rejected(): void
    {
        $this->actingAs($this->user);

        $this->org->update([
            'bank_name' => 'Bank', 'bank_bik' => '123456789',
            'bank_account' => '40702810000000000001',
            'correspondent_account' => '30101810400000000225',
            'kpp' => '773601001', 'offer_accepted' => true,
        ]);

        $response = $this->post(route('contests.store'), $this->contestData([
            'organization_id' => $this->org->id,
            'is_paid'         => 1,
            'entry_fee'       => 99,
        ]));

        $response->assertSessionHasErrors('entry_fee');
    }

    public function test_entry_fee_above_100000_is_rejected(): void
    {
        $this->actingAs($this->user);

        $this->org->update([
            'bank_name' => 'Bank', 'bank_bik' => '123456789',
            'bank_account' => '40702810000000000001',
            'correspondent_account' => '30101810400000000225',
            'kpp' => '773601001', 'offer_accepted' => true,
        ]);

        $response = $this->post(route('contests.store'), $this->contestData([
            'organization_id' => $this->org->id,
            'is_paid'         => 1,
            'entry_fee'       => 100001,
        ]));

        $response->assertSessionHasErrors('entry_fee');
    }

    public function test_paid_contest_stores_correctly(): void
    {
        $this->actingAs($this->user);

        $this->org->update([
            'bank_name' => 'Bank', 'bank_bik' => '123456789',
            'bank_account' => '40702810000000000001',
            'correspondent_account' => '30101810400000000225',
            'kpp' => '773601001', 'offer_accepted' => true,
        ]);

        $response = $this->post(route('contests.store'), $this->contestData([
            'organization_id'   => $this->org->id,
            'is_paid'           => 1,
            'entry_fee'         => 500,
            'application_limit' => 100,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('contests', [
            'organization_id'   => $this->org->id,
            'is_paid'           => 1,
            'entry_fee'         => 500,
            'application_limit' => 100,
        ]);
    }

    public function test_application_limit_max_50_for_free_contests(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('contests.store'), $this->contestData([
            'organization_id'   => $this->org->id,
            'is_paid'           => false,
            'application_limit' => 51,
        ]));

        $response->assertSessionHasErrors('application_limit');
    }

    private function contestData(array $overrides = []): array
    {
        return array_merge([
            'organization_id'       => $this->org->id,
            'title'                 => 'Test Contest',
            'description'           => 'Description here',
            'rules'                 => '',
            'applications_start_at' => now()->subDay()->format('Y-m-d'),
            'applications_end_at'   => now()->addDays(10)->format('Y-m-d'),
            'evaluation_end_at'     => now()->addDays(20)->format('Y-m-d'),
            'is_paid'               => false,
            'application_limit'     => 50,
            'categories'            => [],
            'juries'                => [],
            'contest_age_groups'    => [],
        ], $overrides);
    }
}
