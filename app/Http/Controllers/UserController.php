<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('roles')
            ->when(request('search'), function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->leftJoin('model_has_roles', function($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', User::class);
            })
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where(function($query) {
                $query->whereNull('roles.name')
                      ->orWhere('roles.name', '!=', 'SuperAdmin');
            })
            ->orderByRaw('CASE WHEN roles.name IS NULL THEN 1 ELSE 0 END')
            ->orderBy('roles.name')
            ->orderBy('users.name')
            ->select('users.*')
            ->distinct()
            ->paginate(10);

        return view('users.report', compact('users'));
    }

    public function edit(User $user): View
    {
        // Prevent editing SuperAdmin users
        if ($user->hasRole('SuperAdmin')) {
            abort(403, 'SuperAdmin users cannot be edited.');
        }

        // Prevent Admin from editing themselves or other Admins
        if (auth()->user()->hasRole('Admin') &&
            ($user->id === auth()->id() || $user->hasRole('Admin'))) {
            abort(403, 'Admins cannot edit themselves or other Admins.');
        }

        $roles = Role::where('name', '!=', 'SuperAdmin')->get();
        $canEditAdmin = auth()->user()->hasRole('SuperAdmin');
        return view('users.edit', compact('user', 'roles', 'canEditAdmin'));
    }

    public function update(Request $request, User $user)
    {
        // Prevent editing SuperAdmin users
        if ($user->hasRole('SuperAdmin')) {
            abort(403, 'SuperAdmin users cannot be edited.');
        }

        // Prevent Admin from editing themselves or other Admins
        if (auth()->user()->hasRole('Admin') &&
            ($user->id === auth()->id() || $user->hasRole('Admin'))) {
            abort(403, 'Admins cannot edit themselves or other Admins.');
        }

        $validated = $request->validate([
            'role' => ['nullable', 'exists:roles,name'],
        ]);

        // Prevent non-SuperAdmin users from assigning Admin role
        if ($validated['role'] === 'Admin' && !auth()->user()->hasRole('SuperAdmin')) {
            abort(403, 'Only SuperAdmin users can assign the Admin role.');
        }

        if (empty($validated['role'])) {
            $user->syncRoles([]);
        } else {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()
            ->route('users.report')
            ->with('success', 'User role updated successfully.');
    }
}
