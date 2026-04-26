<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Picture;
use App\Models\PlantingLocation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PictureController extends Controller
{
    /**
     * Show the camera capture page.
     */
    public function create(PlantingLocation $plantingLocation)
    {
        $pictures = $plantingLocation->pictures()->latest()->get();
        return view('pictures.create', compact('plantingLocation', 'pictures'));
    }

    /**
     * Store a camera-captured (base64) image.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image_data'           => 'required',
            'planting_location_id' => 'required|exists:planting_locations,id',
        ]);

        // Decode base64 image
        $imageData   = $request->input('image_data');
        $image       = str_replace('data:image/jpeg;base64,', '', $imageData);
        $image       = str_replace(' ', '+', $image);
        $imageBinary = base64_decode($image);

        $filename = uniqid('photo_') . '.jpg';
        $path     = 'pictures/' . $filename;

        Storage::disk('public')->put($path, $imageBinary);

        Picture::create([
            'user_id'              => auth()->id(),
            'planting_location_id' => $request->planting_location_id,
            'path'                 => $path,
            'thumbnail'            => $path,
            'show_on_welcome'      => false,
        ]);

        return redirect()
            ->route('pictures.create', $request->planting_location_id)
            ->with('success', 'Image captured and uploaded!');
    }

    /**
     * Show the "upload from device" page.
     */
    public function uploadForm(PlantingLocation $plantingLocation)
    {
        return view('pictures.upload', compact('plantingLocation'));
    }

    /**
     * Store one or more uploaded images from device.
     *
     * Rules:
     *   - 1–10 files per request
     *   - Each file: image, max 8 MB, common web formats only
     */
    public function uploadStore(Request $request, PlantingLocation $plantingLocation)
    {
        $request->validate([
            'photos'          => ['required', 'array', 'min:1', 'max:10'],
            'photos.*'        => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'max:8192',      // 8 MB in KB
            ],
            'show_on_welcome' => ['nullable', 'boolean'],
        ], [
            'photos.required'  => 'Please select at least one photo.',
            'photos.max'       => 'You may upload a maximum of 10 photos at a time.',
            'photos.*.image'   => 'All files must be images.',
            'photos.*.mimes'   => 'Allowed formats: JPG, PNG, WEBP, GIF.',
            'photos.*.max'     => 'Each photo must be under 8 MB.',
        ]);

        $showOnWelcome = $request->boolean('show_on_welcome', false);
        $count = 0;

        foreach ($request->file('photos') as $file) {
            $path = $file->store('pictures', 'public');

            $plantingLocation->pictures()->create([
                'user_id'              => Auth::id(),
                'path'                 => $path,
                'thumbnail'            => $path,
                'show_on_welcome'      => $showOnWelcome,
            ]);

            $count++;
        }

        return redirect()
            ->route('planting-locations.show', $plantingLocation)
            ->with('success', $count . ' photo' . ($count > 1 ? 's' : '') . ' uploaded successfully.');
    }

    /**
     * Delete a picture.
     * Allowed: the uploader themselves, or Admin / SuperAdmin.
     */
    public function destroy(Picture $picture)
    {
        $user = Auth::user();

        $isOwner      = $picture->user_id === $user->id;
        $isPrivileged = $user->hasRole(['Admin', 'SuperAdmin']);

        if (! $isOwner && ! $isPrivileged) {
            abort(403, 'You do not have permission to delete this photo.');
        }

        Storage::disk('public')->delete($picture->path);

        $plantingLocation = $picture->plantingLocation;
        $picture->delete();

        return redirect()
            ->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Photo deleted.');
    }

    /**
     * Toggle the "show on welcome page" flag.
     * Allowed: the uploader themselves, or Admin / SuperAdmin.
     */
    public function toggleWelcome(Picture $picture)
    {
        $user = Auth::user();

        $isOwner      = $picture->user_id === $user->id;
        $isPrivileged = $user->hasRole(['Admin', 'SuperAdmin']);

        if (! $isOwner && ! $isPrivileged) {
            abort(403, 'You do not have permission to change this setting.');
        }

        $picture->update([
            'show_on_welcome' => ! $picture->show_on_welcome,
        ]);

        return redirect()
            ->route('planting-locations.show', $picture->plantingLocation)
            ->with('success', 'Welcome page visibility updated.');
    }
}
