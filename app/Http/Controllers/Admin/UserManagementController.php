<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    /**
     * Mostrar lista de usuarios con filtros avanzados
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->with(['wallet', 'profile'])
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('id', $search);
                });
            })
            ->when($request->role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $statistics = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'banned' => User::where('status', 'banned')->count(),
            'super_admins' => User::where('role', 'super_admin')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'moderators' => User::where('role', 'moderator')->count(),
            'supports' => User::where('role', 'support')->count(),
            'users' => User::where('role', 'user')->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
            'statistics' => $statistics,
        ]);
    }

    /**
     * Crear nuevo usuario
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,moderator,support,user',
            'initial_balance' => 'nullable|numeric|min:0|max:1000000',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'balance' => $request->initial_balance ?? 1000.00,
            'currency' => 'USD',
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'avatar' => 'default.png',
            'background_color' => '#1a1a2e',
        ]);

        return redirect()->back()->with('success', "Usuario {$user->name} creado exitosamente");
    }

    /**
     * Actualizar datos de usuario
     */
    public function update(Request $request, User $user)
    {
        // 🔒 SEGURIDAD: Si el usuario objetivo es Super Admin y yo NO soy Super Admin...
        if ($user->role === 'super_admin' && $request->user()->role !== 'super_admin') {
            return redirect()->back()->with('error', 'No tienes permiso para editar al Super Admin.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->back()->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Cambiar rol de usuario
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,moderator,support,user',
        ]);

        if ($user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'No se puede cambiar el rol del Super Admin');
        }

        $oldRole = $user->role;
        $user->update(['role' => $request->role]);

        return redirect()->back()->with('success', "Rol cambiado de {$oldRole} a {$request->role}");
    }

    /**
     * Suspender usuario temporalmente
     */
    public function suspend(Request $request, User $user)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        if ($user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'No se puede suspender al Super Admin');
        }

        if ($user->id === $request->user()->id) {
            return redirect()->back()->with('error', 'No puedes suspenderte a ti mismo');
        }

        $user->suspend($request->reason);

        return redirect()->back()->with('success', "Usuario {$user->name} suspendido");
    }

    /**
     * Banear usuario permanentemente
     */
    public function ban(Request $request, User $user)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        if ($user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'No se puede banear al Super Admin');
        }

        if ($user->id === $request->user()->id) {
            return redirect()->back()->with('error', 'No puedes banearte a ti mismo');
        }

        $user->ban($request->reason);

        return redirect()->back()->with('success', "Usuario {$user->name} baneado permanentemente");
    }

    /**
     * Reactivar usuario
     */
    public function activate(User $user)
    {
        $user->activate();

        return redirect()->back()->with('success', "Usuario {$user->name} reactivado");
    }

    /**
     * Eliminar usuario
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'No se puede eliminar al Super Admin');
        }

        if ($user->id === $request->user()->id) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propia cuenta');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "Usuario {$userName} eliminado exitosamente");
    }
}
