<?php

namespace App\Http\Controllers\Api;

use App\Models\AtsScores;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AtsScoresApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $list = AtsScores::where('user_id', auth()->id())->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'ATS scores retrieved successfully',
                'data' => $list,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving ATS scores',
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
            'job_description' => 'nullable|string',
            'template' => 'nullable|string',
            'keyword_match' => 'required|numeric',
            'skill_match' => 'required|numeric',
            'formatting_issues' => 'required|numeric',
            'length_issues' => 'required|numeric',
            'score' => 'required|numeric',
        ]);

        try {
            $data = new AtsScores();
            $data->user_id = auth()->id();
            $data->job_description = $request->job_description;
            $data->template = $request->template;
            $data->keyword_match = $request->keyword_match;
            $data->skill_match = $request->skill_match;
            $data->formatting_issues = $request->formatting_issues;
            $data->length_issues = $request->length_issues;
            $data->score = $request->score;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'ATS score created successfully',
                'data' => $data,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the ATS score',
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
            'job_description' => 'nullable|string',
            'template' => 'nullable|string',
            'keyword_match' => 'required|numeric',
            'skill_match' => 'required|numeric',
            'formatting_issues' => 'required|numeric',
            'length_issues' => 'required|numeric',
            'score' => 'required|numeric',
        ]);

        try {
            $data = AtsScores::where('user_id', auth()->id())->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'ATS score not found',
                ], 404);
            }
            $data->job_description = $request->job_description;
            $data->template = $request->template;
            $data->keyword_match = $request->keyword_match;
            $data->skill_match = $request->skill_match;
            $data->formatting_issues = $request->formatting_issues;
            $data->length_issues = $request->length_issues;
            $data->score = $request->score;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'ATS score updated successfully',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the ATS score',
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
            $data = AtsScores::where('user_id', auth()->id())->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'ATS score not found',
                ], 404);
            }
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'ATS score deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the ATS score',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
