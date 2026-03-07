<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->role($role);
        }

        $users = $query->paginate(20)->withQueryString();

        return Inertia::render('Users/Index', [
            'users'   => $users,
            'filters' => $request->only(['search', 'role']),
            'roles'   => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'exists:roles,name'],
        ]);

        // Un usuario solo tiene un rol a la vez
        $user->syncRoles([$request->role]);

        return back()->with('success', "Rol '{$request->role}' asignado a {$user->name}.");
    }

    public function removeRole(User $user, string $role)
    {
        $user->removeRole($role);

        return back()->with('success', "Rol '{$role}' removido de {$user->name}.");
    }
}
