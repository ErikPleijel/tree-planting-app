<?php

namespace App\Services;

use App\Models\ChangeLog;
use Illuminate\Support\Facades\Auth;

/**
 * Writes ChangeLog rows explicitly. Called directly from controllers at
 * each mutation point rather than via model observers/events, because the
 * "move" feature uses a query-builder mass update
 * (TreePlanting::whereIn(...)->update(...)) which does not fire Eloquent
 * model events — an observer-based approach would silently miss it.
 */
class ChangeLogger
{
    public function record(
        string $loggableType,
        int $loggableId,
        string $action,
        ?array $old = null,
        ?array $new = null,
        ?string $reason = null,
    ): ChangeLog {
        $user = Auth::user();

        return ChangeLog::create([
            'loggable_type'   => $loggableType,
            'loggable_id'     => $loggableId,
            'action'          => $action,
            'old_values'      => $old,
            'new_values'      => $new,
            'reason'          => $reason,
            'changed_by'      => $user?->id,
            'changed_by_name' => $user?->name,
        ]);
    }
}
