<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModerationController extends Controller
{
    // Listar usuarios
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                                             ->orWhere('email', 'like', "%{$request->search}%"))
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Moderator/Users/Index', [
            'users' => $users,
            'filters' => $request->only('search'),
        ]);
    }

    // Suspender usuario
    public function suspend(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!$request->user()->can('suspend_users')) {
            abort(403, 'No tienes permiso para suspender usuarios');
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'No puedes suspender a un administrador o super admin');
        }

        $user->suspend($request->reason);

        return redirect()->back()->with('success', "Usuario {$user->name} suspendido");
    }
}
