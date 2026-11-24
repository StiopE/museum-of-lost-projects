<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrNew(['email' => 'admin@example.com']);

        $admin->forceFill([
            'name' => 'Museum Administrator',
            'password' => 'password',
            'email_verified_at' => now(),
            'is_admin' => true,
        ])->save();
    }
}
