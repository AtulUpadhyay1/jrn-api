<?php

namespace App\Http\Controllers\Api;

use App\Models\Experience;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExperienceApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $list = Experience::where('user_id', auth()->id())->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'Experience data retrieved successfully',
                'data' => $list,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving experience data',
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
            'job_title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'employment_type' => 'nullable|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'responsibilities' => 'nullable|string',
            'technologies' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        try {

            $data = new Experience();
            $data->user_id = auth()->id();
            $data->job_title = $request->job_title;
            $data->company = $request->company;
            $data->employment_type = $request->employment_type;
            $data->start_date = $request->start_date;
            $data->end_date = $request->end_date;
            $data->responsibilities = $request->responsibilities;
            $data->technologies = $request->technologies;
            $data->location = $request->location;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Experience created successfully',
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
        $request->validate([
            'job_title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'employment_type' => 'nullable|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'responsibilities' => 'nullable|string',
            'technologies' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            $data = Experience::findOrFail($id);
            if ($data->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this experience',
                ], 403);
            }

            $data->job_title = $request->job_title;
            $data->company = $request->company;
            $data->employment_type = $request->employment_type;
            $data->start_date = $request->start_date;
            $data->end_date = $request->end_date;
            $data->responsibilities = $request->responsibilities;
            $data->technologies = $request->technologies;
            $data->location = $request->location;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Experience updated successfully',
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
            $data = Experience::findOrFail($id);
            if ($data->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this experience',
                ], 403);
            }

            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Experience deleted successfully',
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
