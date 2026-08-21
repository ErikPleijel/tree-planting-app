<?php

namespace App\Http\Controllers;

use App\Models\Contributor;
use App\Services\ChangeLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContributorController extends Controller
{
    public function index()
    {
        $contributors = Contributor::withCount('treePlantings')
            ->orderBy('name')
            ->paginate(20);

        return view('contributors.index', compact('contributors'));
    }

    public function create()
    {
        return view('contributors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Contributor::create($validated);

        return redirect()
            ->route('contributors.index')
            ->with('success', 'Contributor created successfully.');
    }

    public function edit(Contributor $contributor)
    {
        return view('contributors.edit', compact('contributor'));
    }

    public function update(Request $request, ChangeLogger $changeLogger, Contributor $contributor)
    {
        $validated = $request->validate($this->rules());

        $trackedFields = ['name', 'website', 'contact_email'];

        $original = collect($trackedFields)
            ->mapWithKeys(fn ($field) => [$field => $contributor->{$field}])
            ->all();

        DB::transaction(function () use ($contributor, $validated, $trackedFields, $original, $changeLogger) {
            $contributor->update($validated);

            $old = [];
            $new = [];

            foreach ($trackedFields as $field) {
                if ((string) $original[$field] !== (string) $contributor->{$field}) {
                    $old[$field] = $original[$field];
                    $new[$field] = $contributor->{$field};
                }
            }

            if (! empty($old)) {
                $changeLogger->record(
                    loggableType: 'Contributor',
                    loggableId: $contributor->id,
                    action: 'contributor_updated',
                    old: $old,
                    new: $new,
                );
            }
        });

        return redirect()
            ->route('contributors.index')
            ->with('success', 'Contributor updated successfully.');
    }

    /**
     * Mirrors TreeTypeController::destroy's existing guard pattern
     * exactly — block deletion while any attachment references this
     * contributor, rather than relying only on the DB's RESTRICT.
     */
    public function destroy(Contributor $contributor)
    {
        if ($contributor->treePlantings()->exists()) {
            return redirect()
                ->route('contributors.index')
                ->with('error', 'This contributor cannot be deleted because it is attached to at least one planting.');
        }

        $contributor->delete();

        return redirect()
            ->route('contributors.index')
            ->with('success', 'Contributor deleted successfully.');
    }

    /**
     * Lightweight JSON typeahead endpoint for the attach flow — mirrors
     * PlantingLocationController::search's existing pattern.
     */
    public function search(Request $request)
    {
        $q = $request->get('q', '');

        $contributors = Contributor::where('name', 'like', '%'.$q.'%')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn ($contributor) => [
                'id'      => $contributor->id,
                'name'    => $contributor->name,
                'website' => $contributor->website,
            ]);

        return response()->json($contributors);
    }

    private function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'website'       => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
