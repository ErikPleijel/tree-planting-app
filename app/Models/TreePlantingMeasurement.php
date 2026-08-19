<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreePlantingMeasurement extends Model
{
    use HasFactory;

    // verified_by_user_id and verified_at are deliberately excluded —
    // both are set only via direct attribute assignment + save() in
    // TreePlantingMeasurementController::verify()/update(), never
    // mass-assigned, so no future ->update($request->all())-style call
    // can set them and bypass the self-verification check.
    protected $fillable = [
        'tree_planting_id',
        'measurement_date',
        'trees_surviving',
        'height_avg_cm',
        'dbh_avg_cm',
        'canopy_cover_pct',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'verified_at'       => 'datetime',
    ];

    public function treePlanting()
    {
        return $this->belongsTo(TreePlanting::class);
    }

    // Two separate belongsTo(User::class) relations since there are two
    // FKs to the same table — user() would be ambiguous.
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
