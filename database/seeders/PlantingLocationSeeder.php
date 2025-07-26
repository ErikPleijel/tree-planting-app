<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PlantingLocation;
use App\Models\TreePlanting;

class PlantingLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PlantingLocation::factory()->count(50)->create();
        TreePlanting::factory()->count(200)->create();
    }
}
