<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class StatusTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Planting Location Status
        DB::table('planting_location_status')->insert([
            ['planting_location_status' => 'Planned'],
            ['planting_location_status' => 'Active'],
        ]);

        // Tree Planting Status
        DB::table('tree_planting_status')->insert([
            ['tree_planting_status' => 'Unverified'],
            ['tree_planting_status' => 'Verified'],
        ]);

        /*DB::table('users')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Mr Erik Admin',
                'telephone' => '123456789',
                'password' => Hash::make('password'),
                'role_id' => 1,
                'gender' => 'Male',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );*/

        // Create all users as Monitors (role_id = 2)
       /*$users = User::factory()->count(20)->create([
            'role_id' => 2  // Monitor role
        ]);*/

        // Set one random user as Verifier (role_id = 3)
       // $users->random(1)->first()->update(['role_id' => 3]);

        // Create admin user
       /* User::factory()->create([
            'name' => 'Mr Erik Bengtsson',
            'email' => 'admin@example.com',
            'telephone' => '123456789',
     //       'role_id' => 1,  // Admin role
            'gender' => 'Male',
        ]);*/


        // Tree Types
        DB::table('tree_types')->insert([
            ['name' => 'Flame Tree', 'description' => 'Delonix regia'],
            ['name' => 'Mahogany', 'description' => 'Swietenia macrophylla'],
            ['name' => 'Black Plum', 'description' => 'Syzygium cumini'],
            ['name' => 'Ficus', 'description' => 'Ficus spp.'],
            ['name' => 'Satelite', 'description' => 'Terminalia mantaly'],
            ['name' => 'Eucalyptus', 'description' => 'Eucalyptus camaldulensis'],
            ['name' => 'Almond', 'description' => 'Prunus amygdalus'],
            ['name' => 'Albizia', 'description' => 'Albizia lebbeck'],
            ['name' => 'Teak', 'description' => 'Tectona grandis'],
            ['name' => 'Bamboo', 'description' => 'Bambusa vulgaris'],
            ['name' => 'Baobab', 'description' => 'Adansonia digitata'],
            ['name' => 'Cashew', 'description' => 'Anacardium occidentale'],
            ['name' => 'Guava', 'description' => 'Psidium guajava'],
            ['name' => 'Moringa', 'description' => 'Moringa oleifera'],
            ['name' => 'Neem', 'description' => 'Azadirachta indica'],
            ['name' => 'Leucaena', 'description' => 'Leucaena leucocephala'],
            ['name' => 'Orange', 'description' => 'Citrus sinensis'],
            ['name' => 'Mango', 'description' => 'Mangifera indica'],
            ['name' => 'Shea Butter', 'description' => 'Vitellaria paradoxa'],
        ]);

        DB::table('division')->insert([
            ['LGA_name' => 'Kenya, Baringo'],
            ['LGA_name' => 'Kenya, Bomet'],
            ['LGA_name' => 'Kenya, Bungoma'],
            ['LGA_name' => 'Kenya, Busia'],
            ['LGA_name' => 'Kenya, Elgeyo/Marakwet'],
            ['LGA_name' => 'Kenya, Embu'],
            ['LGA_name' => 'Kenya, Garissa'],
            ['LGA_name' => 'Kenya, Homa Bay'],
            ['LGA_name' => 'Kenya, Isiolo'],
            ['LGA_name' => 'Kenya, Kajiado'],
            ['LGA_name' => 'Kenya, Kakamega'],
            ['LGA_name' => 'Kenya, Kericho'],
            ['LGA_name' => 'Kenya, Kiambu'],
            ['LGA_name' => 'Kenya, Kilifi'],
            ['LGA_name' => 'Kenya, Kirinyaga'],
            ['LGA_name' => 'Kenya, Kisii'],
            ['LGA_name' => 'Kenya, Kisumu'],
            ['LGA_name' => 'Kenya, Kitui'],
            ['LGA_name' => 'Kenya, Kwale'],
            ['LGA_name' => 'Kenya, Laikipia'],
            ['LGA_name' => 'Kenya, Lamu'],
            ['LGA_name' => 'Kenya, Machakos'],
            ['LGA_name' => 'Kenya, Makueni'],
            ['LGA_name' => 'Kenya, Mandera'],
            ['LGA_name' => 'Kenya, Marsabit'],
            ['LGA_name' => 'Kenya, Meru'],
            ['LGA_name' => 'Kenya, Migori'],
            ['LGA_name' => 'Kenya, Mombasa'],
            ['LGA_name' => 'Kenya, Murang\'a'],
            ['LGA_name' => 'Kenya, Nairobi City'],
            ['LGA_name' => 'Kenya, Nakuru'],
            ['LGA_name' => 'Kenya, Nandi'],
            ['LGA_name' => 'Kenya, Narok'],
            ['LGA_name' => 'Kenya, Nyamira'],
            ['LGA_name' => 'Kenya, Nyandarua'],
            ['LGA_name' => 'Kenya, Nyeri'],
            ['LGA_name' => 'Kenya, Samburu'],
            ['LGA_name' => 'Kenya, Siaya'],
            ['LGA_name' => 'Kenya, Taita/Taveta'],
            ['LGA_name' => 'Kenya, Tana River'],
            ['LGA_name' => 'Kenya, Tharaka-Nithi'],
            ['LGA_name' => 'Kenya, Trans Nzoia'],
            ['LGA_name' => 'Kenya, Turkana'],
            ['LGA_name' => 'Kenya, Uasin Gishu'],
            ['LGA_name' => 'Kenya, Vihiga'],
            ['LGA_name' => 'Kenya, Wajir'],
            ['LGA_name' => 'Kenya, West Pokot'],
        ]);

    }
}
