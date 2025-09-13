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
        //
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
}
