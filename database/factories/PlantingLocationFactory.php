<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PlantingLocation;
use App\Models\User;
use App\Models\Division;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlantingLocation>
 */
class PlantingLocationFactory extends Factory
{
    protected $model = PlantingLocation::class;

    public function definition(): array
    {
        // Define a list of actual Nigerian cities
        $nigerianCities = [
            'Minna', 'Bida', 'Kontagora', 'Suleja', 'New Bussa', 'Zungeru',
            'Mokwa', 'Lapai', 'Agaie', 'Katcha', 'Wushishi', 'Rijau',
            'Agwara', 'Kagara', 'Baro', 'Tegina', 'Kutigi', 'Jebba',
            'Mashegu', 'Abunndace', 'Akwanu', 'Aliko', 'Bawalagi', 'Bini-Edrira',
            'Ebangi', 'Edoman-Woro', 'Edomem Gbako', 'Egunkpa', 'Essui Mutum', 'Eyatsu',
            'Fogbe', 'Gari-Nwake', 'Gbani', 'Gbawugi', 'Gbimigi', 'Gunge',
            'Guregi', 'Gushe', 'Gutsungi', 'Illubo', 'Jiya Dzama', 'Kapagi',
            'Kibban', 'Koroba', 'Kpasa', 'Kujikp', 'Kusogba', 'Kusoti',
            'Kusoyaba', 'Laban', 'Lado', 'Lakan I', 'Lakan II', 'Magaji',
            'Afuwagi', 'Ahashe', 'Alikenci', 'Angunu', 'Ashinu', 'Assayin',
            'Baba-Kasuwa', 'Bantigi', 'Batako II', 'Batako III', 'Boku', 'Bororo',
            'Bororoko', 'Bototi', 'Bugana', 'Chata', 'Chekp-Kinpa', 'Chekpadan',
            'Chemiyan', 'Chirik-Olo', 'Danbole', 'Daniya', 'Daracita', 'Dawaworo',
            'Dekodza', 'Doko-Baba', 'Dokoci', 'Duguyi-Woro', 'Dzukodan', 'Dzungi-Kudiri',
            'Ebami', 'Ebugi', 'Edo', 'Abebugur', 'Akisanri', 'Alhaji-Da Are',
            'Berkete', 'Boro Gandugi', 'Budo', 'Budo Kadinta', 'Budo Saidu', 'Budo Sule',
            'Bukari', 'Dabon Sansani', 'Dada Bio', 'Dauda Bagidi'
        ];

        return [
            // Select a random city from your custom list
            'location' => $this->faker->randomElement($nigerianCities),
            'division_id' => Division::inRandomOrder()->first()?->id ?? Division::factory(),
            'comment' => $this->faker->optional()->sentence(),
            'latitude' => $this->faker->latitude(9.0, 11.0),
            'longitude' => $this->faker->longitude(6.0, 8.0),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'status' => 1,
        ];
    }
}
