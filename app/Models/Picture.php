<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
