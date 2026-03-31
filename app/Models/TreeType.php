<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreeType extends Model
{
    protected $fillable = [
        'name',
        'latin_name',
        'description',
    ];

    public function treePlantings()
    {
        return $this->hasMany(TreePlanting::class);
    }
}
