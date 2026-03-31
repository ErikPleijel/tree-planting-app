<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantingLocation extends Model
{
    use HasFactory;

    protected $fillable = [

        'location',
        'division_id',
        'comment',
        'latitude',
        'longitude',
        'user_id',
        'status_id',
        'contributors',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function status()
    {
        return $this->belongsTo(PlantingLocationStatus::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function treePlantings()
    {
        return $this->hasMany(TreePlanting::class);
    }

    public function pictures()
    {
        return $this->hasMany(Picture::class);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    protected static function booted()
    {
        static::creating(function ($location) {
            if (!$location->public_code) {
                do {
                    $location->public_code = self::generatePublicCode();
                } while (self::where('public_code', $location->public_code)->exists());
            }
        });
    }

    /**
     * Generate human-friendly public code (no confusing characters)
     */
    protected static function generatePublicCode(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        return collect(range(1, 8))
            ->map(fn () => $characters[random_int(0, strlen($characters) - 1)])
            ->implode('');
    }
}
