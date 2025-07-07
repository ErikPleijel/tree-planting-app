<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{
    use HasFactory;

    protected $table = 'division';
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function status()
    {
        return $this->belongsTo(PlantingLocationStatus::class, 'status');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function treePlantings()
    {
        return $this->hasMany(TreePlanting::class);
    }

}
