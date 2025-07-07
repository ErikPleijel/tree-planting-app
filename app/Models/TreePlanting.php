<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreePlanting extends Model
{
    public function treeType()
    {
        return $this->belongsTo(TreeType::class);
    }

    public function plantingLocation()
    {
        return $this->belongsTo(PlantingLocation::class);
    }

    public function status()
    {
        return $this->belongsTo(TreePlantingStatus::class, 'status');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
