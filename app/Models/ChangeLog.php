<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Append-only audit trail. Rows are never updated or deleted — see
 * booted() below, which blocks both at the application level.
 *
 * loggable_type/loggable_id point at the parent record (e.g.
 * 'PlantingLocation', 'TreePlanting') but are deliberately not wired as a
 * real Eloquent relationship: loggable_id has no FK constraint, so a
 * relation would either silently return null after the parent is hard
 * deleted, or invite someone to add a constraint later and reintroduce the
 * data-loss problem this table exists to solve. Use the forEntity() scope
 * to query instead.
 */
class ChangeLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'action',
        'old_values',
        'new_values',
        'reason',
        'changed_by',
        'changed_by_name',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ChangeLog $log) {
            // Not settable by callers (created_at is deliberately excluded
            // from $fillable) — always the real time of the change.
            $log->created_at = now();
        });

        static::updating(function () {
            throw new RuntimeException('ChangeLog records are append-only and cannot be updated.');
        });

        static::deleting(function () {
            throw new RuntimeException('ChangeLog records are append-only and cannot be deleted.');
        });
    }

    public function scopeForEntity(Builder $query, string $type, int $id): Builder
    {
        return $query->where('loggable_type', $type)->where('loggable_id', $id);
    }
}
