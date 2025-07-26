<?php

namespace Database\Factories;

use App\Models\TreePlanting;
use App\Models\User;
use App\Models\TreeType;
use App\Models\PlantingLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TreePlantingFactory extends Factory
{
    protected $model = TreePlanting::class;

    public function definition()
    {
        return [
            'planting_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'number_of_trees' => $this->faker->numberBetween(1, 100),

            // Picks a random existing TreeType ID
            'tree_type_id' => TreeType::inRandomOrder()->first()->id,

            // Random existing user (or create one if no users seeded)
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),

            // Random existing planting location (or create one if none exist)
            'planting_location_id' => PlantingLocation::inRandomOrder()->first()->id ?? PlantingLocation::factory(),

            // Status is always 1 or 2
            'status' => $this->faker->randomElement([1, 2]),
        ];
    }
}
