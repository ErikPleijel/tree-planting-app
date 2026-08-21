<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contributor extends Model
{
    protected $fillable = [
        'name',
        'website',
        'contact_email',
    ];

    public function treePlantings()
    {
        return $this->belongsToMany(TreePlanting::class)
            ->withPivot('note')
            ->withTimestamps();
    }
}
