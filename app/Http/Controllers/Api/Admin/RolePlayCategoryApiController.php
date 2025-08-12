<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Models\RolePlayCategory;
use App\Http\Controllers\Controller;

class RolePlayCategoryApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $categories = RolePlayCategory::latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'Role play categories retrieved successfully',
                'data' => $categories,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role play categories',
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
        ]);
        try {
            $category = new RolePlayCategory;
            $category->name = $request->name;
            $category->save();

            return response()->json([
                'success' => true,
                'message' => 'Role play category created successfully',
                'data' => $category
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role play category',
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
        ]);
        try {
            $category = RolePlayCategory::find($id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role play category not found',
                ], 404);
            }
            $category->name = $request->name;
            $category->save();

            return response()->json([
                'success' => true,
                'message' => 'Role play category created successfully',
                'data' => $category
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role play category',
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
            $category = RolePlayCategory::find($id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role play category not found',
                ], 404);
            }
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role play category deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role play category',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
