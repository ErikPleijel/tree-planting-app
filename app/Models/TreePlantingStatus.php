<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreePlantingStatus extends Model
{
    protected $table = 'tree_planting_status';

    // Add this to use the correct column name
    protected $fillable = ['tree_planting_status'];
}
