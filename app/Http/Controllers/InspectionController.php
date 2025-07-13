<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inspections = \App\Models\Inspection::with('user')->get();

        return view('inspections.index', compact('inspections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inspections.create');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'comment' => 'nullable|string',
            'verified' => 'required|boolean',
        ]);

        $validated['user_id'] = auth()->id();

        \App\Models\Inspection::create($validated);

        return redirect()->route('inspections.index')
            ->with('success', 'Inspection recorded successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Inspection $inspection)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Inspection $inspection)
    {
        return view('inspections.edit', [
            'inspection' => $inspection,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\Illuminate\Http\Request $request, \App\Models\Inspection $inspection)
    {
        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'comment' => 'nullable|string',
            'verified' => 'required|boolean',
        ]);

        $inspection->update($validated);

        return redirect()->route('inspections.index')
            ->with('success', 'Inspection updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Inspection $inspection)
    {

        if (!in_array(auth()->user()->role->name, ['Admin'])) {
            abort(403, 'Unauthorized.');
        }
        $inspection->delete();

        return redirect()->route('inspections.index')
            ->with('success', 'Inspection deleted.');
    }

}
