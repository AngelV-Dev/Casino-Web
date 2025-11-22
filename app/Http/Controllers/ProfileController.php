<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Friend;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $userId = $request->user()->id;

        // Obtener amigos aceptados (ya sea que el usuario envió o recibió la solicitud)
        $friends = Friend::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('friend_id', $userId);
            })
            ->where('status', 'accepted')
            ->with(['user', 'friendUser'])
            ->limit(20)
            ->get();

        return Inertia::render('Profile/Show', [
            'friends' => $friends,
            'recent_activity' => [],
        ]);
    }

    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->has('avatar')) {
            $validated['avatar'] = $request->avatar;
        }

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    public function selectAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|string',
        ]);

        $user = $request->user();
        $user->avatar = $request->avatar;
        $user->save();

        return Redirect::back();
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
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
