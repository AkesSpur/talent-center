<?php

declare(strict_types=1);

namespace Tests\Feature\Stage5;

use App\Models\Contest;
use App\Models\Organization;
use App\Models\PayoutRegistry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgPayoutsPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrgWithManager(): array
    {
        $manager = User::factory()->create([
            'role'              => 'participant',
            'email_verified_at' => now(),
        ]);
        $org = Organization::factory()->create(['created_by' => $manager->id]);
        $org->representatives()->attach($manager->id, [
            'can_create' => true, 'can_manage' => true, 'can_evaluate' => false,
        ]);

        return [$manager, $org];
    }

    public function test_org_rep_with_can_manage_can_view_payouts_page(): void
    {
        [$manager, $org] = $this->makeOrgWithManager();

        $this->actingAs($manager)
            ->get(route('organizations.payouts.index', $org))
            ->assertOk();
    }

    public function test_participant_without_can_manage_gets_403(): void
    {
        [, $org] = $this->makeOrgWithManager();
        $other = User::factory()->create(['role' => 'participant', 'email_verified_at' => now()]);

        $this->actingAs($other)
            ->get(route('organizations.payouts.index', $org))
            ->assertForbidden();
    }

    public function test_org_rep_can_confirm_payout_receipt(): void
    {
        [$manager, $org] = $this->makeOrgWithManager();

        $contest  = Contest::factory()->create([
            'organization_id' => $org->id,
            'created_by'      => $manager->id,
        ]);
        $registry = PayoutRegistry::factory()->create([
            'organization_id'      => $org->id,
            'contest_id'           => $contest->id,
            'payout_amount'        => 1000,
            'transfer_document_path' => 'payout-docs/fake.pdf',
            'payment_received'     => false,
        ]);

        $this->actingAs($manager)
            ->post(route('organizations.payouts.confirm', [$org, $registry]))
            ->assertRedirect();

        $registry->refresh();
        $this->assertTrue($registry->payment_received);
        $this->assertNotNull($registry->payment_received_at);
        $this->assertSame($manager->id, $registry->payment_received_by);
    }

    public function test_double_confirmation_is_blocked(): void
    {
        [$manager, $org] = $this->makeOrgWithManager();

        $contest  = Contest::factory()->create([
            'organization_id' => $org->id,
            'created_by'      => $manager->id,
        ]);
        $registry = PayoutRegistry::factory()->create([
            'organization_id'  => $org->id,
            'contest_id'       => $contest->id,
            'payout_amount'    => 1000,
            'payment_received' => true,
        ]);

        $response = $this->actingAs($manager)
            ->post(route('organizations.payouts.confirm', [$org, $registry]));

        $response->assertRedirect();
        $response->assertSessionHas('warning');
    }
}
