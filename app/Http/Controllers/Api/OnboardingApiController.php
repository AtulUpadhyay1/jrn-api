<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user()->userDetail;
            return response()->json([
                'success' => true,
                'message' => 'Onboarding data retrieved successfully',
                'data' => $user,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving onboarding data',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function save(Request $request)
    {
        try {
            $user_detail = auth()->user()->userDetail;

            $user_detail->dob = $request->dob ?? $user_detail->dob;
            $user_detail->gender = $request->gender ?? $user_detail->gender;
            $user_detail->address = $request->address ?? $user_detail->address;
            $user_detail->bio = $request->bio ?? $user_detail->bio;
            $user_detail->profile_picture = $request->profile_picture ?? $user_detail->profile_picture;
            $user_detail->current_position = $request->current_position ?? $user_detail->current_position;
            $user_detail->experience_level = $request->experience_level ?? $user_detail->experience_level;
            $user_detail->industry = $request->industry ?? $user_detail->industry;
            $user_detail->desired_position = $request->desired_position ?? $user_detail->desired_position;
            $user_detail->work_location_preference = $request->work_location_preference ?? $user_detail->work_location_preference;
            $user_detail->expected_salary_range = $request->expected_salary_range ?? $user_detail->expected_salary_range;
            $user_detail->job_type_preferences = $request->job_type_preferences ?? $user_detail->job_type_preferences;
            $user_detail->skills_technologies = $request->skills_technologies ?? $user_detail->skills_technologies;
            $user_detail->primary_purpose_career_development = $request->primary_purpose_career_development ?? $user_detail->primary_purpose_career_development;
            $user_detail->career_goals = $request->career_goals ?? $user_detail->career_goals;
            $user_detail->motivates_professionally = $request->motivates_professionally ?? $user_detail->motivates_professionally;
            $user_detail->areas_of_interest = $request->areas_of_interest ?? $user_detail->areas_of_interest;
            $user_detail->preferred_learning_methods = $request->preferred_learning_methods ?? $user_detail->preferred_learning_methods;
            $user_detail->resume = $request->resume ?? $user_detail->resume;
            $user_detail->additional_notes = $request->additional_notes ?? $user_detail->additional_notes;
            $user_detail->video_introduction = $request->video_introduction ?? $user_detail->video_introduction;
            $user_detail->linkedin = $request->linkedin ?? $user_detail->linkedin;
            $user_detail->twitter = $request->twitter ?? $user_detail->twitter;
            $user_detail->github = $request->github ?? $user_detail->github;
            $user_detail->personal_website = $request->personal_website ?? $user_detail->personal_website;
            $user_detail->instagram = $request->instagram ?? $user_detail->instagram;
            $user_detail->youtube = $request->youtube ?? $user_detail->youtube;
            $user_detail->behance = $request->behance ?? $user_detail->behance;
            $user_detail->dribbble = $request->dribbble ?? $user_detail->dribbble;
            if($request->step > $user_detail->step) {
                $user_detail->status = 'in_progress';
            }
            if($request->step > $user_detail->step) {
                $user_detail->step = $request->step ?? 1;
            }
            $user_detail->save();

            return response()->json([
                'success' => true,
                'message' => 'Onboarding data saved successfully',
                'data' => $user_detail,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving onboarding data',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
