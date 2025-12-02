<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'role' => $request->user()->role ?? 'user',
                        'avatar' => $request->user()->avatar ?? 'avatar_default.png',
                        'banner' => $request->user()->banner ?? null,
                        'bio' => $request->user()->bio ?? '¡Conquistando el casino uno a uno! 🎰',
                        'balance' => (float) ($request->user()->wallet->balance ?? 0),
                        'level' => $request->user()->level ?? 1,
                        'xp' => $request->user()->xp ?? 0,
                        'xp_next' => $request->user()->xp_next ?? 100,
                        'games' => $request->user()->games ?? 0,
                        'wins' => $request->user()->wins ?? 0,
                        'streak' => $request->user()->streak ?? 0,
                        'status' => $request->user()->status ?? 'active',
                    ]
                    : null,
            ],

           'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'ban_info' => fn () => $request->session()->get('ban_info'), 
            ],
        ];
    }
}