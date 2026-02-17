<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Seed test data for development.
     */
    public function run(): void
    {
        // ── Support user ────────────────────────────────

        $support = User::firstOrCreate(
            ['email' => 'support@talentcenter.ru'],
            [
                'first_name' => 'Support',
                'last_name' => 'Agent',
                'email' => 'support@talentcenter.ru',
                'password' => Hash::make('password'),
                'role' => 'support',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Support account: support@talentcenter.ru / password");

        // ── Parent participant (with children) ──────────

        $parent = User::firstOrCreate(
            ['email' => 'parent@example.com'],
            [
                'first_name' => 'Maria',
                'last_name' => 'Ivanova',
                'patronymic' => 'Petrovna',
                'email' => 'parent@example.com',
                'password' => Hash::make('password'),
                'role' => 'participant',
                'email_verified_at' => now(),
            ]
        );

        $child1 = User::firstOrCreate(
            ['email' => 'child1@example.com'],
            [
                'first_name' => 'Alexei',
                'last_name' => 'Ivanov',
                'email' => 'child1@example.com',
                'password' => Hash::make('password'),
                'role' => 'participant',
                'parent_id' => $parent->id,
                'email_verified_at' => now(),
            ]
        );

        $child2 = User::firstOrCreate(
            ['email' => 'child2@example.com'],
            [
                'first_name' => 'Elena',
                'last_name' => 'Ivanova',
                'email' => 'child2@example.com',
                'password' => Hash::make('password'),
                'role' => 'participant',
                'parent_id' => $parent->id,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Parent: parent@example.com / password (2 children)");

        // ── Regular participant ──────────────────────────

        $participant = User::firstOrCreate(
            ['email' => 'participant@example.com'],
            [
                'first_name' => 'Dmitry',
                'last_name' => 'Petrov',
                'patronymic' => 'Sergeevich',
                'email' => 'participant@example.com',
                'phone' => '+79001234567',
                'password' => Hash::make('password'),
                'role' => 'participant',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Participant: participant@example.com / password");

        // ── Organization (with representatives) ─────────

        $org = Organization::firstOrCreate(
            ['inn' => '1234567890'],
            [
                'name' => 'Creative Arts Academy',
                'description' => 'Academy for young artists and musicians.',
                'inn' => '1234567890',
                'legal_address' => '123 Culture Street, Moscow',
                'contact_email' => 'info@creative-arts.ru',
                'contact_phone' => '+79009876543',
                'status' => 'verified',
                'created_by' => $participant->id,
                'verified_by' => User::where('role', 'admin')->first()?->id,
                'verified_at' => now(),
            ]
        );

        // Participant becomes org admin with all permissions
        $org->representatives()->syncWithoutDetaching([
            $participant->id => [
                'can_create' => true,
                'can_manage' => true,
                'can_evaluate' => true,
            ],
        ]);

        // Parent gets only evaluate permission (jury)
        $org->representatives()->syncWithoutDetaching([
            $parent->id => [
                'can_create' => false,
                'can_manage' => false,
                'can_evaluate' => true,
            ],
        ]);

        $this->command->info("Organization: '{$org->name}' (verified, 2 representatives)");

        // ── Unverified org ──────────────────────────────

        $pendingOrg = Organization::firstOrCreate(
            ['inn' => '0987654321'],
            [
                'name' => 'Young Talent School',
                'description' => 'School for gifted children.',
                'inn' => '0987654321',
                'status' => 'pending',
                'created_by' => $parent->id,
            ]
        );

        $pendingOrg->representatives()->syncWithoutDetaching([
            $parent->id => [
                'can_create' => true,
                'can_manage' => true,
                'can_evaluate' => true,
            ],
        ]);

        $this->command->info("Pending org: '{$pendingOrg->name}' (not verified)");
    }
}
