<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileApiController extends Controller
{
    /**
     * Admin profile.
     */
    public function profile(Request $request)
    {
        try {
            $user = Auth::user()->load('userDetail');

            return response()->json([
                'success' => true,
                'message' => 'User profile retrieved successfully',
                'user' => $user,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the profile',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Admin profile.
     */

    public function update(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
                'user_detail' => $user->userDetail,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the profile',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
