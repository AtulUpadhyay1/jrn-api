<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function dashboard(Request $request)
    {
        try {
            $job_count = 0;
            $resume_score = 0;
            $social_profiles = 0;
            return response()->json([
                'success' => true,
                'message' => 'Dashboard data retrieved successfully',
                'data' => [
                    'job_count' => $job_count,
                    'resume_score' => $resume_score,
                    'social_profiles' => $social_profiles,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
