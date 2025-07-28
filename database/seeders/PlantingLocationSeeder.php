<?php

namespace Database\Seeders;

use App\Models\Inspection;
use App\Models\PlantingLocation;
use App\Models\TreePlanting;
use Illuminate\Database\Seeder;

class PlantingLocationSeeder extends Seeder
{
    public function run(): void
    {
        PlantingLocation::factory()->count(50)->create();
        TreePlanting::factory()->count(200)->create();
        Inspection::factory()->count(300)->create();
    }
}
