<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LidarScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'tree_planting_measurement_id',
        'user_id',
        'scan_source',
        'scan_file_path',
        'lidar_height_cm',
        'lidar_dbh_estimate_cm',
        'lidar_canopy_area_m2',
        'scan_confidence',
        'method_note',
    ];

    public function treePlantingMeasurement()
    {
        return $this->belongsTo(TreePlantingMeasurement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
