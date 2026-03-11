<?php

namespace App\Http\Controllers;

use App\Mail\UserBlockedUnblocked;
use App\Mail\UserCreated;
use App\Mail\UserRoleChanged;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => ['required', 'email', 'unique:users,email'],
            // is_admin is intentionally NOT validated from request input to prevent privilege escalation.
            // Admin status is set explicitly below.
        ]);

        // Generate and assign a random password
        $rawPassword = Str::random(8);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => bcrypt($rawPassword),
            'email_verified_at' => now(),
            'is_admin'          => false, // Never allow user-supplied is_admin value
        ]);

        Mail::to($user)->send(new UserCreated($user, $rawPassword));

        return redirect()->back();
    }

    public function changeRole(User $user)
    {
        $user->update(['is_admin' => !(bool) $user->is_admin]);

        $message = "User role was changed into " . ($user->is_admin ? '"Admin"' : '"Regular User"');

        Mail::to($user)->send(new UserRoleChanged($user));

        return response()->json(['message' => $message]);
    }

    public function blockUnblock(User $user)
    {
        if ($user->blocked_at) {
            $user->blocked_at = null;
            $message = 'User "' . $user->name . '" has been activated';
        } else {
            $user->blocked_at = now();
            $message = 'User "' . $user->name . '" has been blocked';
        }
        $user->save();

        Mail::to($user)->send(new UserBlockedUnblocked($user));

        return response()->json(['message' => $message]);
    }
}
