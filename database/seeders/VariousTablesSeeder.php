<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class VariousTablesSeeder extends Seeder
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
            ['name' => 'Flame Tree', 'latin_name' => 'Delonix regia', 'description' => 'Ornamental tree known for its bright red flowers', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mahogany', 'latin_name' => 'Swietenia macrophylla', 'description' => 'Valuable hardwood tree used for timber', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Black Plum', 'latin_name' => 'Syzygium cumini', 'description' => 'Fruit-bearing tree with medicinal properties', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ficus', 'latin_name' => 'Ficus spp.', 'description' => 'Large genus of trees often used for shade and landscaping', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Satelite', 'latin_name' => 'Terminalia mantaly', 'description' => 'Fast-growing ornamental tree with layered branches', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Eucalyptus', 'latin_name' => 'Eucalyptus camaldulensis', 'description' => 'Fast-growing tree commonly used for timber and fuel', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Almond', 'latin_name' => 'Prunus amygdalus', 'description' => 'Nut-producing tree grown in warm climates', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Albizia', 'latin_name' => 'Albizia lebbeck', 'description' => 'Nitrogen-fixing tree used for shade and soil improvement', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Teak', 'latin_name' => 'Tectona grandis', 'description' => 'Highly valued hardwood tree for construction and furniture', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bamboo', 'latin_name' => 'Bambusa vulgaris', 'description' => 'Fast-growing grass used for construction and crafts', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Baobab', 'latin_name' => 'Adansonia digitata', 'description' => 'Iconic African tree with a massive trunk and edible fruit', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cashew', 'latin_name' => 'Anacardium occidentale', 'description' => 'Tree that produces cashew nuts and fruit', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Guava', 'latin_name' => 'Psidium guajava', 'description' => 'Fruit tree rich in vitamins and widely cultivated', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Moringa', 'latin_name' => 'Moringa oleifera', 'description' => 'Highly nutritious tree known as the “miracle tree”', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Neem', 'latin_name' => 'Azadirachta indica', 'description' => 'Drought-resistant tree with medicinal and pesticidal uses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Leucaena', 'latin_name' => 'Leucaena leucocephala', 'description' => 'Fast-growing tree used for fodder and soil improvement', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Orange', 'latin_name' => 'Citrus sinensis', 'description' => 'Popular fruit tree producing sweet citrus fruits', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mango', 'latin_name' => 'Mangifera indica', 'description' => 'Tropical fruit tree producing sweet mangoes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shea Butter', 'latin_name' => 'Vitellaria paradoxa', 'description' => 'Tree producing nuts used for shea butter', 'created_at' => now(), 'updated_at' => now()],
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
