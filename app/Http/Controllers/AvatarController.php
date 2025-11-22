<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvatarController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'avatar' => 'required|string'
        ]);

        $user = Auth::user();
        $user->avatar = $request->avatar;
        $user->save();

        return back();
    }
}
