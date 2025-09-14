<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\LinkedInProfileAi;
use App\Http\Controllers\Controller;

class LinkedInProfileAiApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $list = LinkedInProfileAi::where('user_id', auth()->id())->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'LinkedIn Profile AI data retrieved successfully',
                'data' => $list,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving LinkedIn Profile AI data',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'profile' => 'nullable|string',
            'ai_report' => 'nullable|string',
        ]);

        try {
            $data = new LinkedInProfileAi();
            $data->user_id = auth()->id();
            $data->profile = $request->profile;
            $data->ai_report = $request->ai_report;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'LinkedIn Profile AI created successfully',
                'data' => $data,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating LinkedIn Profile AI',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = LinkedInProfileAi::where('user_id', auth()->id())->findOrFail($id);
            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'LinkedIn Profile AI not found',
                ], 404);
            }
            $data->status = 'active';
            $data->save();
            return response()->json([
                'success' => true,
                'message' => 'LinkedIn Profile AI status update successful.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving LinkedIn Profile AI',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'profile' => 'nullable|string',
            'ai_report' => 'nullable|string',
        ]);

        try {
            $data = LinkedInProfileAi::where('user_id', auth()->id())->findOrFail($id);
            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'LinkedIn Profile AI not found',
                ], 404);
            }
            $data->profile = $request->profile;
            $data->ai_report = $request->ai_report;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'LinkedIn Profile AI updated successfully',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating LinkedIn Profile AI',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = LinkedInProfileAi::where('user_id', auth()->id())->findOrFail($id);
            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'LinkedIn Profile AI not found',
                ], 404);
            }
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'LinkedIn Profile AI deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting LinkedIn Profile AI',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function snapshot(Request $request)
    {
        $user = auth()->user();
        $linkedInProfileAi = LinkedInProfileAi::where('user_id', $user->id)->first();
        if (!$linkedInProfileAi) {
            return response()->json([
                'success' => false,
                'message' => 'No LinkedIn profile found for the user',
            ], 404);
        }
        if(!$linkedInProfileAi->profile) {
            if($linkedInProfileAi->snapshot_id) {
                $snapshot_id = $linkedInProfileAi->snapshot_id['snapshot_id'];

                try {
                    $snapshotUrl = "https://api.brightdata.com/datasets/v3/snapshot/{$snapshot_id}?format=json";
                    $headers = [
                        'Authorization: Bearer 9ba96b9f77407111cadaf9461b5a96fc84a79b3277e0cb260d3d2e02d16f289b'
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $snapshotUrl);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($response && $httpCode === 200) {
                        $profileData = json_decode($response, true);
                        $linkedInProfileAi->profile = $response;

                        \Log::info('LinkedIn snapshot retrieved successfully', [
                            'user_id' => $linkedInProfileAi->user_id,
                            'snapshot_id' => $snapshot_id,
                            'data_size' => strlen($response)
                        ]);
                        
                    } else {
                        \Log::error('Failed to retrieve LinkedIn snapshot', [
                            'user_id' => $linkedInProfileAi->user_id,
                            'snapshot_id' => $snapshot_id,
                            'http_code' => $httpCode,
                            'response' => $response
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Exception while retrieving LinkedIn snapshot', [
                        'user_id' => $linkedInProfileAi->user_id,
                        'snapshot_id' => $snapshot_id,
                        'error' => $e->getMessage()
                    ]);
                }

                $linkedInProfileAi->save();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No snapshot data available for the LinkedIn profile',
                ], 404);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'LinkedIn profile snapshot retrieved successfully',
            'data' => $linkedInProfileAi->profile,
        ], 200);

    }
}
