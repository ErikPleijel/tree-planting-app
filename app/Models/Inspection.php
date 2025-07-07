<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = [
        'inspection_date',
        'comment',
        'verified',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
