<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProfileApiController extends Controller
{
    /**
     * User profile.
     */

    public function profile(Request $request)
    {
        try {
            $user = Auth::user()->load('userDetail');
            if ($user->userDetail && $user->userDetail->resume) {
                $user->userDetail->resume = asset('storage/' . $user->userDetail->resume);
            }

            return response()->json([
                'success' => true,
                'message' => 'User profile retrieved successfully',
                'user' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
                'qr_code'     => $user->qr_code ? asset('storage/' . $user->qr_code) : null,
                'user_detail' => $user->userDetail,
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
     * Update user profile.
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

            if ($user->userDetail && $user->userDetail->resume) {
                $user->userDetail->resume = asset('storage/' . $user->userDetail->resume);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
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
                'message' => 'An error occurred while updating the profile.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get QR Code for user.
     */

    public function getQrCode(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user->qr_code) {
                return response()->json([
                    'success' => true,
                    'message' => 'QR Code retrieved successfully.',
                    'qr_code' => asset('storage/' . $user->qr_code),
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No QR Code found for the user.',
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the QR code.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate QR Code for user.
     */

    public function generateQrCode(Request $request)
    {
        $request->validate([
            'profile_url' => 'required|url',
        ]);
        try {
            $user = Auth::user();
            $profileUrl = $request->profile_url;

            $qrImage = QrCode::format('png')->size(300)->generate($profileUrl);

            $fileName = "qrcodes/user_" . $user->id . ".png";
            Storage::disk('public')->put($fileName, $qrImage);

            $user->qr_code = $fileName;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'QR Code generated successfully.',
                'qr_code' => asset('storage/' . $fileName),
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the QR code.',
                'error' => $th->getMessage(),
            ], 500);

        }
    }
}
