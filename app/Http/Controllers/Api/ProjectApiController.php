<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $list = Project::where('user_id', auth()->id())->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'Project data retrieved successfully',
                'data' => $list,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving project data',
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
            'description' => 'required|string',
            'role' => 'required|string|max:255',
            'technologies' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'team_size' => 'required|integer|min:1',
        ]);

        try {
            $data = new Project();
            $data->user_id = auth()->id();
            $data->title = $request->title;
            $data->description = $request->description;
            $data->role = $request->role;
            $data->technologies = $request->technologies;
            $data->link = $request->link;
            $data->duration = $request->duration;
            $data->team_size = $request->team_size;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $data,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the project',
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'role' => 'required|string|max:255',
            'technologies' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'team_size' => 'required|integer|min:1',
        ]);

        try {
            $data = Project::where('user_id', auth()->id())->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found',
                ], 404);
            }
            $data->title = $request->title;
            $data->description = $request->description;
            $data->role = $request->role;
            $data->technologies = $request->technologies;
            $data->link = $request->link;
            $data->duration = $request->duration;
            $data->team_size = $request->team_size;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the project',
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
            $data = Project::where('user_id', auth()->id())->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found',
                ], 404);
            }
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the project',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
