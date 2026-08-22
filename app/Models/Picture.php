<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    protected $fillable = [
        'user_id',
        'planting_location_id',
        'path',
        'thumbnail',
        'show_on_welcome',
        'captured_at',
        'captured_latitude',
        'captured_longitude',
        'capture_source',
        'consent_confirmed_at',
    ];

    protected $casts = [
        'show_on_welcome'      => 'boolean',
        'captured_at'          => 'datetime',
        'consent_confirmed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plantingLocation()
    {
        return $this->belongsTo(PlantingLocation::class);
    }

}
