<?php

namespace App\Http\Controllers\Api;

use App\Models\Education;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EducationApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $list = Education::where('user_id', auth()->id())->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'Education data retrieved successfully',
                'data' => $list,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving education data',
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
            'degree' => 'required|string|max:255',
            'specialization' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'start_date' => 'required',
            'end_date' => 'nullable|after_or_equal:start_date',
            'grade' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            $data = new Education();
            $data->user_id = auth()->id();
            $data->degree = $request->degree;
            $data->specialization = $request->specialization;
            $data->institution = $request->institution;
            $data->start_date = $request->start_date;
            $data->end_date = $request->end_date;
            $data->grade = $request->grade;
            $data->location = $request->location;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Education data created successfully',
                'data' => $data,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating education data',
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
            'degree' => 'required|string|max:255',
            'specialization' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'grade' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            $data = Education::where('user_id', auth()->id())->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Education data not found',
                ], 404);
            }
            $data->degree = $request->degree;
            $data->specialization = $request->specialization;
            $data->institution = $request->institution;
            $data->start_date = $request->start_date;
            $data->end_date = $request->end_date;
            $data->grade = $request->grade;
            $data->location = $request->location;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Education data updated successfully',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating education data',
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
            $data = Education::where('user_id', auth()->id())->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Education data not found',
                ], 404);
            }
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Education data deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting education data',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
