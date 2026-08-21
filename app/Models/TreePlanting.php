<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreePlanting extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_date',
        'number_of_trees',
        'biochar',
        'tree_type_id',
        'planting_location_id',
        'status',
        'status_updated_by',
        'user_id',
    ];

    protected $casts = [
        'planting_date' => 'date'
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

    public function statusUpdatedBy()
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public function measurements()
    {
        return $this->hasMany(TreePlantingMeasurement::class);
    }

    public function contributors()
    {
        return $this->belongsToMany(Contributor::class)
            ->withPivot('note')
            ->withTimestamps();
    }

public function division()
{
    return $this->belongsTo(Division::class);
}
}
