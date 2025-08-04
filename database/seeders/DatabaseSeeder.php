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
        $this->call(StatusTablesSeeder::class);
      //  $this->call(RolesTableSeeder::class);
        $this->call(PlantingLocationSeeder::class);

        // Existing role seeding
        $this->call(RolesSeeder::class);
        // Seed 20 users and assign Grower role
        User::factory(20)->create()->each(function ($user) {
            $user->assignRole('Grower');
        });

        $admin = User::firstOrCreate(
            ['email' => 'superadmin@ex.com'],
            [
                'name' => 'Hon. Marcus Aurelius',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('SuperAdmin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@ex.com'],
            [
                'name' => 'Erik Jörgen',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Admin');

        $Monitor = User::firstOrCreate(
            ['email' => 'monitor@ex.com'],
            [
                'name' => 'Prada Streep',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $Monitor->assignRole('Monitor');

        $grower = User::firstOrCreate(
            ['email' => 'grower@ex.com'],
            [
                'name' => 'Thomas Greenquist',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $grower->assignRole('grower');


    }
}
