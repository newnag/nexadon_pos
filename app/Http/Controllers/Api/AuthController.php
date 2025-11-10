<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // For SPA authentication, use session-based authentication
        Auth::login($user, $request->boolean('remember'));

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load('role'),
        ]);
    }

    /**
     * Handle user logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        // Only invalidate session if it exists (important for testing)
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('role'),
        ]);
    }
}
