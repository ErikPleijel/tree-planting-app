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
            ['tree_planting_status' => 'Planted'],
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



        User::factory()->count(20)->create();
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
            ['LGA_name' => 'Agaie'],
            ['LGA_name' => 'Agwara'],
            ['LGA_name' => 'Bida'],
            ['LGA_name' => 'Borgu'],
            ['LGA_name' => 'Bosso'],
            ['LGA_name' => 'Chanchaga'],
            ['LGA_name' => 'Edati'],
            ['LGA_name' => 'Gbako'],
            ['LGA_name' => 'Gurara'],
            ['LGA_name' => 'Katcha'],
            ['LGA_name' => 'Kontagora'],
            ['LGA_name' => 'Lapai'],
            ['LGA_name' => 'Lavun'],
            ['LGA_name' => 'Magama'],
            ['LGA_name' => 'Mariga'],
            ['LGA_name' => 'Mashegu'],
            ['LGA_name' => 'Mokwa'],
            ['LGA_name' => 'Munya'],
            ['LGA_name' => 'Paikoro'],
            ['LGA_name' => 'Rafi'],
            ['LGA_name' => 'Rijau'],
            ['LGA_name' => 'Shiroro'],
            ['LGA_name' => 'Suleja'],
            ['LGA_name' => 'Tafa'],
            ['LGA_name' => 'Wushishi'],
        ]);

    }
}
