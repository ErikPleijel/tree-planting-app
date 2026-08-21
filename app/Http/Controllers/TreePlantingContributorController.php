<?php

namespace App\Http\Controllers;

use App\Models\Contributor;
use App\Models\TreePlanting;
use App\Services\ChangeLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreePlantingContributorController extends Controller
{
    public function index(TreePlanting $treePlanting)
    {
        $treePlanting->load(['plantingLocation', 'treeType', 'contributors']);

        return view('tree-planting-contributors.index', compact('treePlanting'));
    }

    /**
     * Accepts EITHER an existing contributor_id OR a full set of
     * new-contributor fields. If new fields are given, the Contributor
     * is created first, then attached — all in one transaction.
     */
    public function attach(Request $request, ChangeLogger $changeLogger, TreePlanting $treePlanting)
    {
        $validated = $request->validate([
            'contributor_id' => 'nullable|integer|exists:contributors,id',
            'name'           => 'nullable|string|max:255',
            'website'        => 'nullable|url|max:255',
            'contact_email'  => 'nullable|email|max:255',
            'note'           => 'nullable|string',
        ]);

        if (empty($validated['contributor_id']) && empty($validated['name'])) {
            return back()->withErrors([
                'contributor_id' => 'Select an existing contributor or provide a name for a new one.',
            ]);
        }

        // Pre-check (friendly validation error) backed by the DB's own
        // unique constraint (data-integrity safety net) — a brand-new
        // contributor can never already be attached, so this only
        // applies to the existing-contributor path.
        if (
            ! empty($validated['contributor_id'])
            && $treePlanting->contributors()->where('contributor_id', $validated['contributor_id'])->exists()
        ) {
            return back()->withErrors([
                'contributor_id' => 'This contributor is already attached to this planting.',
            ]);
        }

        try {
            DB::transaction(function () use ($validated, $treePlanting, $changeLogger) {
                $contributor = ! empty($validated['contributor_id'])
                    ? Contributor::findOrFail($validated['contributor_id'])
                    : Contributor::create([
                        'name'          => $validated['name'],
                        'website'       => $validated['website'] ?? null,
                        'contact_email' => $validated['contact_email'] ?? null,
                    ]);

                $treePlanting->contributors()->attach($contributor->id, ['note' => $validated['note'] ?? null]);

                // Name is a snapshot at attachment time — the Contributor's
                // own name could be corrected later, and this log should
                // reflect what was true when this specific event happened.
                $changeLogger->record(
                    loggableType: 'TreePlanting',
                    loggableId: $treePlanting->id,
                    action: 'contributor_attached',
                    old: null,
                    new: ['contributor_id' => $contributor->id, 'contributor_name' => $contributor->name],
                );
            });
        } catch (QueryException $e) {
            // Closes the race the pre-check above can't: two requests for
            // the same contributor+planting pair can both pass the
            // pre-check before either commits (double-click, two open
            // tabs). The second to reach this insert hits the DB's own
            // unique constraint on (contributor_id, tree_planting_id).
            // Detected by driver error code, not message text, since
            // wording differs by driver: MySQL/MariaDB expose duplicate-key
            // as SQLSTATE 23000 with driver code 1062 (errorInfo[1]);
            // SQLite (used locally/in tests) expose it as SQLSTATE 23000
            // with driver code 19 (SQLITE_CONSTRAINT). This insert has
            // exactly one enforceable constraint in practice — the
            // contributor_id/tree_planting_id FKs are already satisfied by
            // validation above, so a 23000 here can only be the unique
            // index — anything else re-throws.
            if ($e->getCode() === '23000' && in_array($e->errorInfo[1] ?? null, [1062, 19], true)) {
                return back()->withErrors([
                    'contributor_id' => 'This contributor is already attached to this planting.',
                ]);
            }

            throw $e;
        }

        return redirect()
            ->route('tree-planting-contributors.index', $treePlanting)
            ->with('success', 'Contributor attached successfully.');
    }

    public function detach(ChangeLogger $changeLogger, TreePlanting $treePlanting, Contributor $contributor)
    {
        DB::transaction(function () use ($treePlanting, $contributor, $changeLogger) {
            $affected = $treePlanting->contributors()->detach($contributor->id);

            // Only log if a pivot row genuinely existed — detach() is
            // silently idempotent, and a log entry for nothing actually
            // happening would be a false audit record.
            if ($affected > 0) {
                $changeLogger->record(
                    loggableType: 'TreePlanting',
                    loggableId: $treePlanting->id,
                    action: 'contributor_detached',
                    old: ['contributor_id' => $contributor->id, 'contributor_name' => $contributor->name],
                    new: null,
                );
            }
        });

        return redirect()
            ->route('tree-planting-contributors.index', $treePlanting)
            ->with('success', 'Contributor detached successfully.');
    }
}
