<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! User::query()->where('email', 'admin@placeboshop.test')->exists()) {
            User::factory()->admin()->create([
                'name' => 'Placebo Admin',
                'email' => 'admin@placeboshop.test',
            ]);
        }

        if (! User::query()->where('email', 'demo@placeboshop.test')->exists()) {
            User::factory()->create([
                'name' => 'Demo Shopper',
                'email' => 'demo@placeboshop.test',
            ]);
        }
    }
}
