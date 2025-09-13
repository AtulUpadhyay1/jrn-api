<?php

namespace App\Http\Controllers\Api;

use App\Models\JobEngine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JobEngineApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $list = JobEngine::where('user_id', auth()->id())->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'Job Engine data retrieved successfully',
                'data' => $list,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving Job Engine data',
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
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contract_type' => 'nullable|string|max:255',
            'experience_level' => 'nullable|string|max:255',
            'work_type' => 'nullable|string|max:255',
            'published_at' => 'nullable|string|max:255',
            'jobs_count' => 'nullable|string|max:255',
        ]);

        try {
            $data = new JobEngine();
            $data->user_id = auth()->id();
            $data->title = $request->title;
            $data->location = $request->location;
            $data->contract_type = $request->contract_type;
            $data->experience_level = $request->experience_level;
            $data->work_type = $request->work_type;
            $data->published_at = $request->published_at;
            $data->jobs_count = $request->jobs_count;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Job Engine created successfully',
                'data' => $data,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating experience',
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
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contract_type' => 'nullable|string|max:255',
            'experience_level' => 'nullable|string|max:255',
            'work_type' => 'nullable|string|max:255',
            'published_at' => 'nullable|string|max:255',
            'jobs_count' => 'nullable|string|max:255',
        ]);

        try {
            $data = JobEngine::where('user_id', auth()->id())->findOrFail($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job Engine entry not found',
                ], 404);
            }
            $data->title = $request->title;
            $data->location = $request->location;
            $data->contract_type = $request->contract_type;
            $data->experience_level = $request->experience_level;
            $data->work_type = $request->work_type;
            $data->published_at = $request->published_at;
            $data->jobs_count = $request->jobs_count;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Job Engine updated successfully',
                'data' => $data,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating experience',
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
            $data = JobEngine::where('user_id', auth()->id())->findOrFail($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job Engine entry not found',
                ], 404);
            }
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job Engine entry deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting experience',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
