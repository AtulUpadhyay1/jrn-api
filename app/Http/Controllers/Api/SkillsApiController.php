<?php

namespace App\Http\Controllers\Api;

use App\Models\Skill;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SkillsApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $list = Skill::where('user_id', auth()->id())->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'Skills data retrieved successfully',
                'data' => $list,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving skills data',
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
            'skill' => 'required|string|max:255',
            'category' => 'required',
            'level' => 'required',
        ]);

        try {
            $data = new Skill();
            $data->user_id = auth()->id();
            $data->skill = $request->skill;
            $data->category = $request->category;
            $data->level = $request->level;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Skill created successfully',
                'data' => $data,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the skill',
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
            'skill' => 'required|string|max:255',
            'category' => 'required',
            'level' => 'required',
        ]);

        try {
            $data = Skill::where('user_id', auth()->id())->find($id);
            $data->skill = $request->skill;
            $data->category = $request->category;
            $data->level = $request->level;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Skill updated successfully',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the skill',
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
            $data = Skill::where('user_id', auth()->id())->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Skill not found',
                ], 404);
            }
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Skill deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the skill',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
