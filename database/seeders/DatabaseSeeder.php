<?php

namespace Database\Seeders;

use App\Models\Api\V1\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(30)->create();

        User::factory()->create([
            'name' => 'Derex',
            'surname' => 'Script',
            'user' => 'derexscript',
            'email' => 'derex@outlook.com.br',
            'password' => '$2y$10$kQxIX2a5rNZV8xXDfU8KyOOGd8INIpKfyyJdrTHt68HhddlLKl4Cy',
            'phone' => '81986571208',
            'email_verified_at' => now(),
            'is_admin' => true,
            'permissions' => ["*"]
        ]);
    }
}
