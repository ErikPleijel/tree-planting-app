<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreeType extends Model
{
    protected $fillable = [
        'name',
        'latin_name',
        'description',
        'wood_density_kg_m3',
        'carbon_fraction',
        'source_reference',
    ];

    public function treePlantings()
    {
        return $this->hasMany(TreePlanting::class);
    }
}
