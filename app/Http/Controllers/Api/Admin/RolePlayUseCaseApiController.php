<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Models\RolePlayUseCase;
use App\Http\Controllers\Controller;

class RolePlayUseCaseApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $useCases = RolePlayUseCase::with('category')->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'Role play use cases retrieved successfully',
                'data' => $useCases,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role play use cases',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:role_play_categories,id',
            'prompt' => 'required|string',
            'time' => 'required|string|max:255',
        ]);

        try {
            $data = new RolePlayUseCase();
            $data->name = $request->name;
            $data->category_id = $request->category_id;
            $data->use_case_type = 'video';
            $data->prompt = $request->prompt;
            $data->time = $request->time;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Role play use case created successfully',
                'data' => $data,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role play use case',
                'error' => $th->getMessage()
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
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:role_play_categories,id',
            'prompt' => 'required|string',
            'time' => 'required|string|max:255',
        ]);

        try {
            $data = RolePlayUseCase::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role play use case not found',
                ], 404);
            }
            $data->name = $request->name;
            $data->category_id = $request->category_id;
            $data->use_case_type = 'video';
            $data->prompt = $request->prompt;
            $data->time = $request->time;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Role play use case updated successfully',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role play use case',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = RolePlayUseCase::find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role play use case not found',
                ], 404);
            }
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role play use case deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role play use case',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
