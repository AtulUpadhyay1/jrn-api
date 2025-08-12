<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        try {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                // Check if user is admin
                if ($user->type !== 'admin') {
                    Auth::logout();
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Admin access required.',
                    ], 403);
                }

                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'type'    => $user->type,
                    'access_token' => $token,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
