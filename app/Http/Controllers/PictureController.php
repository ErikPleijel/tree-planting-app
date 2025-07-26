<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Picture;
use App\Models\PlantingLocation;
use Illuminate\Support\Facades\Storage;




class PictureController extends Controller
{
    public function create(PlantingLocation $plantingLocation)
    {
        $pictures = $plantingLocation->pictures()->latest()->get();
        return view('pictures.create', compact('plantingLocation', 'pictures'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image_data' => 'required',
            'planting_location_id' => 'required|exists:planting_locations,id',
        ]);

        // Decode base64 image
        $imageData = $request->input('image_data');
        $image = str_replace('data:image/jpeg;base64,', '', $imageData);
        $image = str_replace(' ', '+', $image);
        $imageBinary = base64_decode($image);

        $filename = uniqid('photo_') . '.jpg';
        $path = 'pictures/' . $filename;

        Storage::disk('public')->put($path, $imageBinary);

        Picture::create([
            'user_id' => auth()->id(),
            'planting_location_id' => $request->planting_location_id,
            'path' => $path,
            'thumbnail' => $path, // for now use same image
        ]);

        return redirect()->route('pictures.create', $request->planting_location_id)
            ->with('success', 'Image captured and uploaded!');
    }

}
