<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\RolePlayUseCase;
use App\Models\RolePlayCategory;
use App\Http\Controllers\Controller;

class RolePlayApiController extends Controller
{
    /**
     * Display a listing of the role play categories.
     */
    public function category(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);
        try {
            $categories = RolePlayCategory::latest()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->get();
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
     * Display a listing of the role play use cases.
     */
    public function useCases(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:role_play_categories,id',
        ]);
        try {
            $useCases = RolePlayUseCase::with('category')
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->when($request->category_id, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->latest()
            ->get();
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

}
