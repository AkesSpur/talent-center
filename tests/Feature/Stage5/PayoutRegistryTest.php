<?php

declare(strict_types=1);

namespace Tests\Feature\Stage5;

use App\Enums\PaymentStatus;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PayoutRegistry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayoutRegistryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role'               => 'admin',
            'email_verified_at'  => now(),
        ]);
    }

    private function makePaidContest(int $feeCount = 3, int $feeAmount = 1000): array
    {
        $org  = Organization::factory()->create();
        $user = User::factory()->create(['role' => 'participant']);
        $contest = Contest::factory()->create([
            'organization_id' => $org->id,
            'created_by'      => $user->id,
            'is_paid'         => true,
            'entry_fee'       => $feeAmount,
            'status'          => 'archive',
        ]);

        for ($i = 0; $i < $feeCount; $i++) {
            $app = Application::factory()->create([
                'contest_id' => $contest->id,
                'status'     => 'new',
            ]);
            Payment::factory()->create([
                'application_id' => $app->id,
                'contest_id'     => $contest->id,
                'user_id'        => $app->user_id,
                'amount'         => $feeAmount,
                'status'         => 'accepted',
            ]);
        }

        return [$org, $contest];
    }

    public function test_admin_can_access_payout_registry_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.payout-registries.index'))
            ->assertOk();
    }

    public function test_participant_cannot_access_payout_registry(): void
    {
        $user = User::factory()->create(['role' => 'participant', 'email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('admin.payout-registries.index'))
            ->assertForbidden();
    }

    public function test_payout_amount_calculated_correctly(): void
    {
        // 3 payments × 1000 = 3000 total → floor(3000 * 0.6) - 2000 = 1800 - 2000 = max(0, -200) = 0
        // Let's use 5 × 1000 = 5000 → floor(5000 * 0.6) - 2000 = 3000 - 2000 = 1000
        [, $contest] = $this->makePaidContest(feeCount: 5, feeAmount: 1000);

        $this->actingAs($this->admin)
            ->post(route('admin.payout-registries.store'), [
                'contest_id' => $contest->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payout_registries', [
            'contest_id'   => $contest->id,
            'payout_amount' => 1000,
        ]);
    }

    public function test_creating_registry_marks_payments_as_distributed(): void
    {
        [, $contest] = $this->makePaidContest(feeCount: 2, feeAmount: 500);

        $this->actingAs($this->admin)
            ->post(route('admin.payout-registries.store'), [
                'contest_id' => $contest->id,
            ]);

        $undistributed = Payment::where('contest_id', $contest->id)
            ->where('status', PaymentStatus::Accepted->value)
            ->count();

        $this->assertSame(0, $undistributed);

        $distributed = Payment::where('contest_id', $contest->id)
            ->where('status', PaymentStatus::Distributed->value)
            ->count();

        $this->assertSame(2, $distributed);
    }

    public function test_duplicate_registry_for_same_contest_is_rejected(): void
    {
        [, $contest] = $this->makePaidContest();

        $this->actingAs($this->admin)
            ->post(route('admin.payout-registries.store'), ['contest_id' => $contest->id]);

        // Second attempt
        $response = $this->actingAs($this->admin)
            ->post(route('admin.payout-registries.store'), ['contest_id' => $contest->id]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame(1, PayoutRegistry::where('contest_id', $contest->id)->count());
    }

    public function test_admin_can_update_transfer_details(): void
    {
        [, $contest] = $this->makePaidContest();

        $this->actingAs($this->admin)
            ->post(route('admin.payout-registries.store'), ['contest_id' => $contest->id]);

        $registry = PayoutRegistry::where('contest_id', $contest->id)->first();

        $this->actingAs($this->admin)
            ->put(route('admin.payout-registries.update', $registry), [
                'transfer_amount'          => 800,
                'transfer_document_number' => 'П-001',
                'transfer_document_date'   => '2026-04-10',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payout_registries', [
            'id'                       => $registry->id,
            'transfer_amount'          => 800,
            'transfer_document_number' => 'П-001',
        ]);
    }
}
