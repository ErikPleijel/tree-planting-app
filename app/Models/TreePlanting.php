<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreePlanting extends Model
{
    protected $fillable = [
        'planting_date',
        'number_of_trees',
        'tree_type_id',
        'planting_location_id',
        'status',
        'user_id',
    ];
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

    public function statusRelation()
    {
        return $this->belongsTo(TreePlantingStatus::class, 'status');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
