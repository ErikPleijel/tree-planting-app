<?php

namespace App\Http\Controllers;

use App\Models\TreeType;
use Illuminate\Http\Request;

class TreeTypeController extends Controller
{


    public function index()
    {
        $treeTypes = TreeType::withSum('treePlantings', 'number_of_trees')
            ->orderBy('name')
            ->paginate(150);

        return view('tree-types.index', compact('treeTypes'));
    }

    public function create()
    {
        return view('tree-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latin_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        TreeType::create($validated);

        return redirect()
            ->route('tree-types.index')
            ->with('success', 'Tree type created successfully.');
    }

    public function show(TreeType $treeType)
    {
        return view('tree-types.show', compact('treeType'));
    }

    public function edit(TreeType $treeType)
    {
        return view('tree-types.edit', compact('treeType'));
    }

    public function update(Request $request, TreeType $treeType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latin_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $treeType->update($validated);

        return redirect()
            ->route('tree-types.index')
            ->with('success', 'Tree type updated successfully.');
    }

    public function destroy(TreeType $treeType)
    {
        if ($treeType->treePlantings()->exists()) {
            return redirect()
                ->route('tree-types.index')
                ->with('error', 'This tree type cannot be deleted because it is already in use.');
        }

        $treeType->delete();

        return redirect()
            ->route('tree-types.index')
            ->with('success', 'Tree type deleted successfully.');
    }
}
