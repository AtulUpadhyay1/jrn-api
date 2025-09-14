<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\LinkedInProfileAi;
use App\Http\Controllers\Controller;
use OpenAI\Client as OpenAIClient;

class LinkedInProfileAiApiController extends Controller
{
    protected OpenAIClient $openai;
    protected string $model;

    public function __construct(OpenAIClient $openai)
    {
        $this->openai = $openai;
        $this->model = config('services.openai.model');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $list = LinkedInProfileAi::where('user_id', auth()->id())->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'LinkedIn Profile AI data retrieved successfully',
                'data' => $list,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving LinkedIn Profile AI data',
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
            'profile' => 'nullable|string',
            'ai_report' => 'nullable|string',
        ]);

        try {
            $data = new LinkedInProfileAi();
            $data->user_id = auth()->id();
            $data->profile = $request->profile;
            $data->ai_report = $request->ai_report;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'LinkedIn Profile AI created successfully',
                'data' => $data,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating LinkedIn Profile AI',
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
            $user = auth()->user();
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
                $linkedInProfileAi = LinkedInProfileAi::where('user_id', $user->id)->first();
                if (!$linkedInProfileAi) {
                    $linkedInProfileAi = new LinkedInProfileAi();
                }
                $linkedInProfileAi->user_id = $user->id;
                $linkedInProfileAi->snapshot_id = $response;
                $linkedInProfileAi->save();

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
        $data->status = 'active';
        $data->save();
        return response()->json([
            'success' => true,
            'message' => 'LinkedIn Profile AI status update successful.',
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'profile' => 'nullable|string',
            'ai_report' => 'nullable|string',
        ]);

        try {
            $data = LinkedInProfileAi::where('user_id', auth()->id())->findOrFail($id);
            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'LinkedIn Profile AI not found',
                ], 404);
            }
            $data->profile = $request->profile;
            $data->ai_report = $request->ai_report;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'LinkedIn Profile AI updated successfully',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating LinkedIn Profile AI',
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
            $data = LinkedInProfileAi::where('user_id', auth()->id())->findOrFail($id);
            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'LinkedIn Profile AI not found',
                ], 404);
            }
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'LinkedIn Profile AI deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting LinkedIn Profile AI',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function snapshot(Request $request)
    {
        $user = auth()->user();
        $linkedInProfileAi = LinkedInProfileAi::where('user_id', $user->id)->first();
        if (!$linkedInProfileAi) {
            return response()->json([
                'success' => false,
                'message' => 'No LinkedIn profile found for the user',
            ], 404);
        }
        if(!$linkedInProfileAi->profile) {
            if($linkedInProfileAi->snapshot_id && $linkedInProfileAi->api_status != 1) {
                $snapshot_id = $linkedInProfileAi->snapshot_id['snapshot_id'];
                try {
                    $snapshotUrl = "https://api.brightdata.com/datasets/v3/snapshot/{$snapshot_id}?format=json";
                    $headers = [
                        'Authorization: Bearer 9ba96b9f77407111cadaf9461b5a96fc84a79b3277e0cb260d3d2e02d16f289b'
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $snapshotUrl);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($response && $httpCode === 200) {
                        $profileData = json_decode($response, true);
                        $linkedInProfileAi->profile = $response;

                        \Log::info('LinkedIn snapshot retrieved successfully', [
                            'user_id' => $linkedInProfileAi->user_id,
                            'snapshot_id' => $snapshot_id,
                            'data_size' => strlen($response)
                        ]);

                        // Automatically trigger AI analysis for the retrieved profile
                        try {
                            $analysisReport = $this->generateProfileAnalysis($response);
                            $linkedInProfileAi->ai_report = json_encode($analysisReport);
                            $linkedInProfileAi->status = 'active';
                            \Log::info('LinkedIn profile analysis completed automatically', [
                                'user_id' => $linkedInProfileAi->user_id,
                                'analysis_size' => strlen(json_encode($analysisReport))
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('Failed to analyze LinkedIn profile automatically', [
                                'user_id' => $linkedInProfileAi->user_id,
                                'error' => $e->getMessage()
                            ]);
                        }

                    } else {
                        \Log::error('Failed to retrieve LinkedIn snapshot', [
                            'user_id' => $linkedInProfileAi->user_id,
                            'snapshot_id' => $snapshot_id,
                            'http_code' => $httpCode,
                            'response' => $response
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Exception while retrieving LinkedIn snapshot', [
                        'user_id' => $linkedInProfileAi->user_id,
                        'snapshot_id' => $snapshot_id,
                        'error' => $e->getMessage()
                    ]);
                }
                $linkedInProfileAi->api_status = 1;
                $linkedInProfileAi->save();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No snapshot data available for the LinkedIn profile',
                ], 404);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'LinkedIn profile snapshot retrieved successfully',
            'data' => $linkedInProfileAi->profile,
        ], 200);

    }

    public function analyzeProfile(Request $request)
    {
        try {
            $request->validate([
                'profile_data' => 'required|string'
            ]);

            $profileData = $request->profile_data;

            // Create the analysis prompt
            $prompt = $this->createAnalysisPrompt($profileData);

            // Call OpenAI API
            $response = $this->openai->chat()->create([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional LinkedIn profile analyst. Analyze the provided LinkedIn profile data and return a comprehensive JSON report with detailed insights, pros, cons, graphical data suggestions, and improvement recommendations.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 4000
            ]);

            $aiReport = $response->choices[0]->message->content;

            // Try to parse the AI response as JSON
            $analysisReport = json_decode($aiReport, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // If not valid JSON, wrap in a structure
                $analysisReport = [
                    'analysis_text' => $aiReport,
                    'timestamp' => now()->toISOString()
                ];
            }

            // Save or update the LinkedIn profile with AI report
            $user = auth()->user();
            $linkedInProfileAi = LinkedInProfileAi::firstOrCreate(
                ['user_id' => $user->id],
                ['profile' => $profileData]
            );

            $linkedInProfileAi->profile = $profileData;
            $linkedInProfileAi->ai_report = json_encode($analysisReport);
            $linkedInProfileAi->save();

            return response()->json([
                'success' => true,
                'message' => 'LinkedIn profile analysis completed successfully',
                'data' => $analysisReport,
            ], 200);

        } catch (\Throwable $th) {
            \Log::error('LinkedIn Profile Analysis Error', [
                'error' => $th->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while analyzing the LinkedIn profile',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    private function createAnalysisPrompt($profileData)
    {
        return "
        Please analyze the following LinkedIn profile data and provide a comprehensive analysis report in JSON format.
        The report should include:

        1. **Profile Summary**: Key highlights and overview
        2. **Strengths (Pros)**: What stands out positively
        3. **Areas for Improvement (Cons)**: What could be enhanced
        4. **Graphical Data Insights**: Suggestions for visual representation of data
        5. **Experience Analysis**: Detailed breakdown of work experience
        6. **Skills Assessment**: Analysis of listed skills and recommendations
        7. **Education Evaluation**: Assessment of educational background
        8. **Network Analysis**: Insights about connections and reach
        9. **Activity Assessment**: Analysis of LinkedIn activity and engagement
        10. **Improvement Recommendations**: Specific actionable suggestions

        LinkedIn Profile Data:
        ```json
        {$profileData}
        ```

        Please return the analysis as a well-structured JSON object with the following format:
        ```json
        {
            \"profile_summary\": {
                \"name\": \"string\",
                \"headline\": \"string\",
                \"location\": \"string\",
                \"summary_score\": \"number (1-10)\",
                \"key_highlights\": [\"string\"]
            },
            \"strengths\": [
                {
                    \"category\": \"string\",
                    \"description\": \"string\",
                    \"impact_score\": \"number (1-10)\"
                }
            ],
            \"areas_for_improvement\": [
                {
                    \"category\": \"string\",
                    \"issue\": \"string\",
                    \"priority\": \"high|medium|low\",
                    \"suggested_action\": \"string\"
                }
            ],
            \"experience_analysis\": {
                \"total_experience_years\": \"number\",
                \"current_role_assessment\": \"string\",
                \"career_progression_score\": \"number (1-10)\",
                \"experience_details\": [
                    {
                        \"company\": \"string\",
                        \"role\": \"string\",
                        \"duration\": \"string\",
                        \"assessment\": \"string\"
                    }
                ]
            },
            \"skills_assessment\": {
                \"technical_skills\": [\"string\"],
                \"soft_skills\": [\"string\"],
                \"missing_skills\": [\"string\"],
                \"skill_relevance_score\": \"number (1-10)\"
            },
            \"education_evaluation\": {
                \"highest_degree\": \"string\",
                \"institution_quality\": \"string\",
                \"relevance_to_career\": \"string\",
                \"additional_certifications_needed\": [\"string\"]
            },
            \"network_analysis\": {
                \"connections_count\": \"number\",
                \"followers_count\": \"number\",
                \"network_quality_score\": \"number (1-10)\",
                \"networking_suggestions\": [\"string\"]
            },
            \"activity_assessment\": {
                \"engagement_level\": \"high|medium|low\",
                \"content_quality\": \"string\",
                \"posting_frequency\": \"string\",
                \"activity_improvement_tips\": [\"string\"]
            },
            \"graphical_insights\": {
                \"suggested_charts\": [
                    {
                        \"chart_type\": \"string\",
                        \"data_points\": [\"string\"],
                        \"purpose\": \"string\"
                    }
                ],
                \"key_metrics_to_visualize\": [\"string\"]
            },
            \"improvement_recommendations\": {
                \"immediate_actions\": [
                    {
                        \"action\": \"string\",
                        \"expected_impact\": \"string\",
                        \"effort_required\": \"low|medium|high\"
                    }
                ],
                \"long_term_goals\": [
                    {
                        \"goal\": \"string\",
                        \"timeline\": \"string\",
                        \"steps\": [\"string\"]
                    }
                ]
            },
            \"overall_score\": {
                \"profile_completeness\": \"number (1-10)\",
                \"professional_appeal\": \"number (1-10)\",
                \"marketability\": \"number (1-10)\",
                \"overall_rating\": \"number (1-10)\"
            },
            \"analysis_metadata\": {
                \"analyzed_at\": \"" . now()->toISOString() . "\",
                \"analysis_version\": \"1.0\",
                \"data_quality\": \"string\"
            }
        }
        ```

        Ensure the response is valid JSON and provides actionable, specific insights based on the profile data.
        ";
    }

    private function generateProfileAnalysis($profileData)
    {
        $prompt = $this->createAnalysisPrompt($profileData);

        $response = $this->openai->chat()->create([
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional LinkedIn profile analyst. Analyze the provided LinkedIn profile data and return a comprehensive JSON report with detailed insights, pros, cons, graphical data suggestions, and improvement recommendations. Respond only with valid JSON.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 4000
        ]);

        $aiReport = $response->choices[0]->message->content;

        // Try to parse the AI response as JSON
        $analysisReport = json_decode($aiReport, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // If not valid JSON, create a structured response
            $analysisReport = [
                'profile_summary' => [
                    'name' => 'Profile Analysis',
                    'summary_score' => 5,
                    'key_highlights' => ['Analysis completed']
                ],
                'analysis_text' => $aiReport,
                'error' => 'AI response was not valid JSON',
                'timestamp' => now()->toISOString()
            ];
        }

        return $analysisReport;
    }
}
