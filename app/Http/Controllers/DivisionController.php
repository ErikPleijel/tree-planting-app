<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::orderBy('LGA_name')->paginate(150);

        return view('divisions.index', compact('divisions'));
    }

    public function create()
    {
        return view('divisions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Division::create($validated);

        return redirect()
            ->route('divisions.index')
            ->with('success', 'Division created successfully.');
    }

    public function edit(Division $division)
    {
        return view('divisions.edit', compact('division'));
    }

    public function update(Request $request, Division $division)
    {
        $validated = $request->validate($this->rules());

        $division->update($validated);

        return redirect()
            ->route('divisions.index')
            ->with('success', 'Division updated successfully.');
    }

    /**
     * division_id on planting_locations has an onDelete('cascade') FK, so an
     * unguarded delete() here would silently cascade-delete every
     * PlantingLocation in this division (and everything recorded under
     * them). This guard is not optional.
     */
    public function destroy(Division $division)
    {
        if ($division->plantingLocations()->exists()) {
            return redirect()
                ->route('divisions.index')
                ->with('error', 'This division cannot be deleted because it has planting locations assigned to it. Deleting it would also delete those locations and everything recorded under them.');
        }

        $division->delete();

        return redirect()
            ->route('divisions.index')
            ->with('success', 'Division deleted successfully.');
    }

    private function rules(): array
    {
        return [
            'LGA_name'  => ['required', 'string', 'max:255'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
