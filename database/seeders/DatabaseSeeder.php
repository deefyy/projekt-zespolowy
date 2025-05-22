<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => 'adminadmin',
        ]);

        User::factory()->create([
            'name' => 'Test',
            'last_name' => 'Test',
            'email' => 'test@test.test',
            'role' => 'user',
            'password' => 'testtest',
        ]);

        User::factory()->create([
            'name' => 'Organizator',
            'last_name' => 'Organizator',
            'email' => 'org@example.com',
            'role' => 'organizator',
            'password' => 'org',
        ]);
    }
}
