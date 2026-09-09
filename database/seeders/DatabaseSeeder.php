<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
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

        User::create([
            'name' => 'Super Admin',
            'no_hp' => '081234567890',
            'email' => 'superadmin@example.com',
            'role' => 'SUPERADMIN',
            'password' => Hash::make('password'),
        ]);

        // Call sample data seeder
        $this->call(SampleDataSeeder::class);
    }
}
