<?php

declare(strict_types=1);

namespace Tests\Feature\Stage5;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationBankDetailsTest extends TestCase
{
    use RefreshDatabase;

    private function userWithOrg(): array
    {
        $user = User::factory()->create([
            'role'                => 'participant',
            'email_verified_at'  => now(),
        ]);
        $org = Organization::factory()->create([
            'created_by' => $user->id,
            'status'     => 'verified',
        ]);
        $org->representatives()->attach($user->id, [
            'can_create' => true, 'can_manage' => true, 'can_evaluate' => true,
        ]);

        return [$user, $org];
    }

    public function test_bank_details_are_saved_on_org_update(): void
    {
        [$user, $org] = $this->userWithOrg();

        $this->actingAs($user)
            ->put(route('organizations.update', $org), [
                'name'                  => $org->name,
                'inn'                   => $org->inn,
                'contact_email'         => $org->contact_email,
                'bank_name'             => 'Сбербанк',
                'bank_bik'              => '044525225',
                'bank_account'          => '40702810000000000001',
                'correspondent_account' => '30101810400000000225',
                'kpp'                   => '773601001',
                'offer_accepted'        => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('organizations', [
            'id'             => $org->id,
            'bank_name'      => 'Сбербанк',
            'bank_bik'       => '044525225',
            'offer_accepted' => 1,
        ]);
    }

    public function test_offer_accepted_can_be_cleared(): void
    {
        [$user, $org] = $this->userWithOrg();

        $org->update(['offer_accepted' => true]);

        $this->actingAs($user)
            ->put(route('organizations.update', $org), [
                'name'          => $org->name,
                'inn'           => $org->inn,
                'contact_email' => $org->contact_email,
                // offer_accepted not sent (unchecked checkbox)
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('organizations', [
            'id'             => $org->id,
            'offer_accepted' => 0,
        ]);
    }

    public function test_org_edit_page_loads_for_representative(): void
    {
        [$user, $org] = $this->userWithOrg();

        $this->actingAs($user)
            ->get(route('organizations.edit', $org))
            ->assertOk();
    }

    public function test_org_edit_page_denied_for_non_representative(): void
    {
        [, $org] = $this->userWithOrg();
        $other = User::factory()->create(['role' => 'participant', 'email_verified_at' => now()]);

        $this->actingAs($other)
            ->get(route('organizations.edit', $org))
            ->assertForbidden();
    }
}
