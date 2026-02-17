<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Create the default system administrator account.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@talentcenter.ru'],
            [
                'first_name' => 'System',
                'last_name' => 'Admin',
                'email' => 'admin@talentcenter.ru',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin account created: admin@talentcenter.ru / password');
    }
}
