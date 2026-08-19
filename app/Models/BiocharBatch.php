<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiocharBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_location_id',
        'tree_planting_id',
        'quantity_kg',
        'source',
        'batch_reference',
        'application_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'application_date' => 'date',
    ];

    public function plantingLocation()
    {
        return $this->belongsTo(PlantingLocation::class);
    }

    public function treePlanting()
    {
        return $this->belongsTo(TreePlanting::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
