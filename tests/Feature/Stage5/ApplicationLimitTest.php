<?php

declare(strict_types=1);

namespace Tests\Feature\Stage5;

use App\Models\Application;
use App\Models\Contest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_contest_is_application_limit_reached_returns_false_when_zero(): void
    {
        $contest = Contest::factory()->make(['application_limit' => 0]);
        $this->assertFalse($contest->isApplicationLimitReached());
    }

    public function test_contest_is_application_limit_reached_counts_non_pending(): void
    {
        $org  = Organization::factory()->create();
        $user = User::factory()->create(['role' => 'participant']);
        $org->representatives()->attach($user->id, [
            'can_create' => true, 'can_manage' => true, 'can_evaluate' => true,
        ]);

        $contest = Contest::factory()->create([
            'organization_id'   => $org->id,
            'created_by'        => $user->id,
            'status'            => 'accepting',
            'application_limit' => 2,
        ]);

        Application::factory()->count(2)->create([
            'contest_id' => $contest->id,
            'status'     => 'new',
        ]);

        $this->assertTrue($contest->isApplicationLimitReached());
    }

    public function test_payment_pending_applications_not_counted_toward_limit(): void
    {
        $org  = Organization::factory()->create();
        $user = User::factory()->create(['role' => 'participant']);
        $org->representatives()->attach($user->id, [
            'can_create' => true, 'can_manage' => true, 'can_evaluate' => true,
        ]);

        $contest = Contest::factory()->create([
            'organization_id'   => $org->id,
            'created_by'        => $user->id,
            'status'            => 'accepting',
            'application_limit' => 2,
            'is_paid'           => true,
            'entry_fee'         => 500,
        ]);

        // 2 payment_pending — should NOT count
        Application::factory()->count(2)->create([
            'contest_id' => $contest->id,
            'status'     => 'payment_pending',
        ]);

        $this->assertFalse($contest->isApplicationLimitReached());
    }

    public function test_application_store_blocked_when_limit_reached(): void
    {
        $org  = Organization::factory()->create(['status' => 'verified']);
        $user = User::factory()->create(['role' => 'participant', 'email_verified_at' => now()]);

        $contest = Contest::factory()->create([
            'organization_id'   => $org->id,
            'created_by'        => $user->id,
            'status'            => 'accepting',
            'application_limit' => 1,
            'is_paid'           => false,
        ]);

        // Fill the 1 slot with another user's application
        Application::factory()->create([
            'contest_id' => $contest->id,
            'status'     => 'new',
        ]);

        $response = $this->actingAs($user)
            ->post(route('applications.store', $contest), [
                'contest_id'    => $contest->id,
                'external_link' => 'https://drive.google.com/test',
            ]);

        $response->assertRedirect(route('contests.show', $contest));
        $response->assertSessionHas('error');
    }
}
