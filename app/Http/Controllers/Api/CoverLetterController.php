<?php

namespace App\Http\Controllers\Api;

use App\Models\CoverLetter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CoverLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $list = CoverLetter::where('user_id', auth()->id())->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'Cover letters fetched successfully',
                'data' => $list
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cover letters',
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
            'description' => 'nullable|string',
        ]);

        try {
            $data = new CoverLetter();
            $data->user_id = auth()->id();
            $data->name = $request->name;
            $data->description = $request->description;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Cover letter created successfully',
                'data' => $data
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating cover letter',
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
            'description' => 'nullable|string',
        ]);

        try {
            $data = CoverLetter::where('user_id', auth()->id())->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cover letter not found',
                ], 404);
            }
            $data->name = $request->name;
            $data->description = $request->description;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Cover letter updated successfully',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating cover letter',
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
            $data = CoverLetter::where('user_id', auth()->id())->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cover letter not found',
                ], 404);
            }
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cover letter deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting cover letter',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
