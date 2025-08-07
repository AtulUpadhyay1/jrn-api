<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Communication;
use App\Http\Controllers\Controller;

class CommunicationApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $list = Communication::where('user_id', auth()->id())->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'Communication data retrieved successfully',
                'data' => $list,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving communication data',
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
            'language' => 'required|string|max:255',
            'proficiency' => 'required|string|max:255',
        ]);

        try {
            $data = new Communication();
            $data->user_id = auth()->id();
            $data->language = $request->language;
            $data->proficiency = $request->proficiency;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Communication created successfully',
                'data' => $data,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the communication',
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
            'language' => 'required|string|max:255',
            'proficiency' => 'required|string|max:255',
        ]);

        try {
            $data = Communication::where('user_id', auth()->id())->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Communication not found',
                ], 404);
            }
            $data->language = $request->language;
            $data->proficiency = $request->proficiency;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Communication updated successfully',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the communication',
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
            $data = Communication::where('user_id', auth()->id())->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Communication not found',
                ], 404);
            }
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Communication deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the communication',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
