<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\LinkedInProfileAi;
use App\Http\Controllers\Controller;

class OnboardingApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user()->load('userDetail');
            if ($user->userDetail && $user->userDetail->resume) {
                $user->userDetail->resume = asset('storage/' . $user->userDetail->resume);
            }
            return response()->json([
                'success' => true,
                'message' => 'Onboarding data retrieved successfully test',
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
        $request->validate([
            'resume' => 'nullable|file|mimes:pdf,doc,docx',
        ]);
        try {
            $user = auth()->user();
            $user->first_name = $request->first_name ?? $user->first_name;
            $user->last_name = $request->last_name ?? $user->last_name;
            $user->email = $request->email ?? $user->email;
            $user->phone = $request->phone ?? $user->phone;
            $user->save();

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
            if($request->hasFile('resume')) {
                $extension = $request->file('resume')->getClientOriginalExtension();
                $name = time() . '.' . $extension;
                $user_detail->resume = $request->file('resume')->storeAs('resumes', $name, 'public');

            }
            $user_detail->additional_notes = $request->additional_notes ?? $user_detail->additional_notes;
            $user_detail->video_introduction = $request->video_introduction ?? $user_detail->video_introduction;
            $old_linkedin_url = $user_detail->linkedin;
            $user_detail->linkedin = $request->linkedin ?? $user_detail->linkedin;
            $user_detail->twitter = $request->twitter ?? $user_detail->twitter;
            $user_detail->github = $request->github ?? $user_detail->github;
            $user_detail->personal_website = $request->personal_website ?? $user_detail->personal_website;
            $user_detail->instagram = $request->instagram ?? $user_detail->instagram;
            $user_detail->youtube = $request->youtube ?? $user_detail->youtube;
            $user_detail->behance = $request->behance ?? $user_detail->behance;
            $user_detail->dribbble = $request->dribbble ?? $user_detail->dribbble;
            if($request->step >= $user_detail->step) {
                $user_detail->status = 'in_progress';
            }
            if($request->step >= $user_detail->step) {
                $user_detail->step = $request->step ?? 1;
            }
            if($request->step == 7) {
                $user_detail->status = 'uploaded';
            }
            $user_detail->save();

            if($old_linkedin_url != $request->linkedin) {
                try {
                    $brightDataUrl = 'https://api.brightdata.com/datasets/v3/trigger?dataset_id=gd_l1viktl72bvl7bjuj0&include_errors=true';
                    $headers = [
                        'Authorization: Bearer 9ba96b9f77407111cadaf9461b5a96fc84a79b3277e0cb260d3d2e02d16f289b',
                        'Content-Type: application/json'
                    ];
                    $data = [
                        [
                            'url' => $user_detail->linkedin
                        ]
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $brightDataUrl);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($response && $httpCode === 200) {
                        // Log successful LinkedIn profile processing
                        \Log::info('LinkedIn profile processed successfully', [
                            'user_id' => $user->id,
                            'linkedin_url' => $request->linkedin,
                            'response' => $response
                        ]);
                        $responseData = json_decode($response, true);
                        if (isset($responseData['snapshot_id'])) {
                            $linkedInProfileAi = new LinkedInProfileAi();
                            $linkedInProfileAi->user_id = $user->id;
                            $linkedInProfileAi->snapshot_id = $responseData['snapshot_id'];
                            $linkedInProfileAi->save();
                        }
                    } else {
                        // Log error but don't fail the main request
                        \Log::error('Failed to process LinkedIn profile', [
                            'user_id' => $user->id,
                            'linkedin_url' => $request->linkedin,
                            'http_code' => $httpCode,
                            'response' => $response
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail the main request
                    \Log::error('Exception while processing LinkedIn profile', [
                        'user_id' => $user->id,
                        'linkedin_url' => $request->linkedin,
                        'error' => $e->getMessage()
                    ]);
                }
            }

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
