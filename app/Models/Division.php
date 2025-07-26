<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{
    use HasFactory;

    // Optional: This is actually redundant if the table is named "divisions"
    // protected $table = 'divisions';
    protected $table = 'division';
    public function plantingLocations()
    {
        return $this->hasMany(PlantingLocation::class);
    }
}
