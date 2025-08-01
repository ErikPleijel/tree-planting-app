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
        'status',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

   public function status()
    {
        return $this->belongsTo(PlantingLocationStatus::class, 'status_id');
    }

   public function statusRelation()
    {
        return $this->belongsTo(PlantingLocationStatus::class, 'status_id');
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
}
