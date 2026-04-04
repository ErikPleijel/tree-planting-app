<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email', 'telephone', 'country']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Handle profile picture replacement
        if ($request->hasFile('profile_picture_file')) {
            if ($user->profile_picture_path) {
                Storage::disk('public')->delete($user->profile_picture_path);
            }
            $user->profile_picture_path = $request->file('profile_picture_file')->store('profile-pictures', 'public');
        } elseif ($request->filled('profile_picture_data')) {
            if ($user->profile_picture_path) {
                Storage::disk('public')->delete($user->profile_picture_path);
            }
            $imageData   = $request->input('profile_picture_data');
            $image       = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $image       = str_replace(' ', '+', $image);
            $imageBinary = base64_decode($image);
            $filename    = uniqid('selfie_') . '.jpg';
            $path        = 'profile-pictures/' . $filename;
            Storage::disk('public')->put($path, $imageBinary);
            $user->profile_picture_path = $path;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}