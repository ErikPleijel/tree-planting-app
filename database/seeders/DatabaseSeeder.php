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
        // Seed 20 users and assign TreePlanter role
        User::factory(20)->create()->each(function ($user) {
            $user->assignRole('TreePlanter');
        });

        $admin = User::firstOrCreate(
            ['email' => 'superadmin@ex.com'],
            [
                'name' => 'Mr SuperAdmin Man',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('SuperAdmin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@ex.com'],
            [
                'name' => 'Mr Admin User Man',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Admin');

        $inspector = User::firstOrCreate(
            ['email' => 'inspector@ex.com'],
            [
                'name' => 'Inspector User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $inspector->assignRole('Inspector');


    }
}
