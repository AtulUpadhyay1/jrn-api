<?php

namespace App\Http\Controllers\Api;

use App\Models\JobEngine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

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
            'location' => 'required|string|max:255',
            'keyword' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'time_range' => 'nullable|string|max:255',
            'job_type' => 'nullable|string|max:255',
            'experience_level' => 'nullable|string|max:255',
            'remote' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'location_radius' => 'nullable|string|max:255',
        ]);

        try {
            $data = new JobEngine();
            $data->user_id = auth()->id();
            $data->location = $request->location;
            $data->keyword = $request->keyword;
            $data->country = $request->country;
            $data->time_range = $request->time_range;
            $data->job_type = $request->job_type;
            $data->experience_level = $request->experience_level;
            $data->remote = $request->remote;
            $data->company = $request->company;
            $data->location_radius = $request->location_radius;

            $data->save();

            // // Trigger job search after saving
            // $searchResult = $this->triggerJobSearch($data);

            // // Save search result in jobs column if successful
            // if ($searchResult['success'] && isset($searchResult['data'])) {
            //     $data->snapshot_id = $searchResult['data'];
            //     $data->save();
            // }

            return response()->json([
                'success' => true,
                'message' => 'Job Engine created successfully',
                'data' => $data,
                'search_result' => $searchResult,
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
        try {
            $data = JobEngine::where('user_id', auth()->id())->findOrFail($id);
            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job Engine entry not found',
                ], 404);
            }

            try {

                if($data->api_status != 1){
                    // Make API call to fetch job data
                    $response = Http::withHeaders([
                        'accept' => 'application/json'
                    ])->get('https://anjanette-administrable-kenyetta.ngrok-free.app/jobs', [
                        'search_term' => $data->keyword,
                        'location' => $data->location,
                        'results_wanted' => 20,
                        'hours_old' => 72,
                        'is_remote' => $data->remote === 'remote' ? 'true' : 'false',
                        'fetch_description' => 'true',
                        'fetch_skills' => 'false'
                    ]);

                    if ($response->successful()) {
                        $data->jobs = $response->json();
                        $data->api_status = 1;
                        $data->save();
                    } else {
                        throw new \Exception('Failed to fetch jobs: ' . $response->body());
                    }
                }

            } catch (\Exception $e) {
                \Log::warning('Webhook call failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Webhook call failed: ' . $e->getMessage(),
                ], 500);
            }

            $data->status = 'active';
            $data->save();
            return response()->json([
                'success' => true,
                'message' => 'Job Engine entry status update successful.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving Job Engine entry',
                'error' => $th->getMessage(),
            ], 500);
        }
    }    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request, [
            'location' => 'required|string|max:255',
            'keyword' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'time_range' => 'nullable|string|max:255',
            'job_type' => 'nullable|string|max:255',
            'experience_level' => 'nullable|string|max:255',
            'remote' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'location_radius' => 'nullable|string|max:255',
        ]);

        try {
            $data = JobEngine::where('user_id', auth()->id())->findOrFail($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job Engine entry not found',
                ], 404);
            }
            $data->location = $request->location;
            $data->keyword = $request->keyword;
            $data->country = $request->country;
            $data->time_range = $request->time_range;
            $data->job_type = $request->job_type;
            $data->experience_level = $request->experience_level;
            $data->remote = $request->remote;
            $data->company = $request->company;
            $data->location_radius = $request->location_radius;
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

    /**
     * Helper method to trigger job search for a single JobEngine record
     */
    private function triggerJobSearch(JobEngine $jobEngine)
    {
        try {
            $brightDataToken = '9ba96b9f77407111cadaf9461b5a96fc84a79b3277e0cb260d3d2e02d16f289b';
            $brightDataUrl = 'https://api.brightdata.com/datasets/v3/trigger';

            $queryParams = [
                'dataset_id' => 'gd_lpfll7v5hcqtkxl6l',
                'include_errors' => 'true',
                'type' => 'discover_new',
                'discover_by' => 'keyword'
            ];

            // Create search array from JobEngine data
            $searches = [[
                'location' => $jobEngine->location,
                'keyword' => $jobEngine->keyword ?? '',
                'country' => $jobEngine->country ?? '',
                'time_range' => $jobEngine->time_range ?? '',
                'job_type' => $jobEngine->job_type ?? '',
                'experience_level' => $jobEngine->experience_level ?? '',
                'remote' => $jobEngine->remote ?? '',
                'company' => $jobEngine->company ?? '',
                'location_radius' => $jobEngine->location_radius ?? '',
            ]];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $brightDataToken,
                'Content-Type' => 'application/json',
            ])->post($brightDataUrl . '?' . http_build_query($queryParams), $searches);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Job search triggered successfully',
                    'data' => $response->json(),
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to trigger job search',
                    'error' => $response->body(),
                ];
            }

        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'An error occurred while searching jobs',
                'error' => $th->getMessage(),
            ];
        }
    }

    /**
     * Search jobs using Bright Data API
     */
    public function searchJobs(Request $request)
    {
        $request->validate([
            'searches' => 'required|array',
            'searches.*.location' => 'required|string',
            'searches.*.keyword' => 'required|string',
            'searches.*.country' => 'nullable|string',
            'searches.*.time_range' => 'nullable|string',
            'searches.*.job_type' => 'nullable|string',
            'searches.*.experience_level' => 'nullable|string',
            'searches.*.remote' => 'nullable|string',
            'searches.*.company' => 'nullable|string',
            'searches.*.location_radius' => 'nullable|string',
        ]);

        try {
            $brightDataToken = '9ba96b9f77407111cadaf9461b5a96fc84a79b3277e0cb260d3d2e02d16f289b';
            $brightDataUrl = 'https://api.brightdata.com/datasets/v3/trigger';

            $queryParams = [
                'dataset_id' => 'gd_lpfll7v5hcqtkxl6l',
                'include_errors' => 'true',
                'type' => 'discover_new',
                'discover_by' => 'keyword'
            ];

            $searches = $request->input('searches');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $brightDataToken,
                'Content-Type' => 'application/json',
            ])->post($brightDataUrl . '?' . http_build_query($queryParams), $searches);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Job search triggered successfully',
                    'data' => $response->json(),
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to trigger job search',
                    'error' => $response->body(),
                ], $response->status());
            }

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching jobs',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
