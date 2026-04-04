<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'email'                => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password'             => ['required', 'confirmed', Rules\Password::defaults()],
            'invitation_code'      => ['required', 'in:'.env('INVITATION_CODE')],
            'telephone'            => ['nullable', 'string', 'max:30'],
            'country'              => ['nullable', 'string', 'max:100'],
            'profile_picture_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ]);

        $profilePicturePath = null;

        if ($request->hasFile('profile_picture_file')) {
            $profilePicturePath = $request->file('profile_picture_file')->store('profile-pictures', 'public');
        } elseif ($request->filled('profile_picture_data')) {
            $imageData   = $request->input('profile_picture_data');
            $image       = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $image       = str_replace(' ', '+', $image);
            $imageBinary = base64_decode($image);
            $filename    = uniqid('selfie_') . '.jpg';
            $path        = 'profile-pictures/' . $filename;
            Storage::disk('public')->put($path, $imageBinary);
            $profilePicturePath = $path;
        }

        $user = User::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'password'             => Hash::make($request->password),
            'telephone'            => $request->telephone,
            'country'              => $request->country,
            'profile_picture_path' => $profilePicturePath,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}