<?php

namespace App\Http\Controllers\Api;

use App\Models\Skill;
use App\Models\Project;
use App\Models\Education;
use App\Models\Curriculum;
use App\Models\Experience;
use Illuminate\Http\Request;
use App\Models\Communication;
use App\Http\Controllers\Controller;
use App\Services\ResumeParseService;
use Illuminate\Support\Facades\Auth;

class ResumeParseApiController extends Controller
{
    public function parse(Request $request, ResumeParseService $service)
    {
        $data = $request->validate([
            'resume_url'      => ['required','url'],
            'job_description' => ['nullable','string'],
        ]);

        try {
            $result = $service->parseFromUrl(
                url: $data['resume_url'],
                jobDescription: $data['job_description'] ?? null
            );

            $user = auth()->user();
            $user->userDetail->resume_parsed = $result;
            $user->userDetail->save();

            return response()->json($result, 200, [
                'Content-Type' => 'application/json; charset=utf-8'
            ]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'status'  => 'error',
                'message' => 'Unable to parse resume.',
                'detail'  => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 422);
        }
    }

    public function generateReport(Request $request)
    {
        $user = Auth::user()->load('userDetail');
        if ($user->userDetail && $user->userDetail->resume) {
            $user->userDetail->resume = asset('storage/' . $user->userDetail->resume);
        }

        $communications = Communication::where('user_id', auth()->id())->latest()->get();
        $curriculums    = Curriculum::where('user_id', auth()->id())->latest()->get();
        $educations     = Education::where('user_id', auth()->id())->latest()->get();
        $experiences    = Experience::where('user_id', auth()->id())->latest()->get();
        $projects       = Project::where('user_id', auth()->id())->latest()->get();
        $skills         = Skill::where('user_id', auth()->id())->latest()->get();

        $user_report = [
            'communications' => $communications,
            'curriculums'    => $curriculums,
            'educations'     => $educations,
            'experiences'    => $experiences,
            'projects'       => $projects,
            'skills'         => $skills,
            'user_info'      => $user
        ];

        try {
            // ✅ Proper OpenAI client
            $client = \OpenAI::client(env('OPENAI_API_KEY'));

            // ✅ Strict JSON prompt
            $prompt = "Analyze the following resume data.
            Return only valid JSON in this structure:
            {
            \"profile\": {\"name\": \"string\", \"position\": \"string\", \"industry\": \"string\", \"location\": \"string\"},
            \"resume_analysis\": {
                \"pros\": [\"string\"],
                \"cons\": [\"string\"],
                \"score\": {\"overall\": number, \"skills_match\": number, \"experience_relevance\": number, \"education_strength\": number, \"presentation\": number},
                \"quality\": \"Excellent|Good|Average|Poor\",
                \"recommendations\": [\"string\"]
            }
            }
            Do not include explanations or extra text.

            Resume data: " . json_encode($user_report);

            $response = $client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert resume reviewer.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
            ]);

            $content = trim($response->choices[0]->message->content);

            // ✅ Ensure only JSON is returned
            $content = preg_replace('/^[^{\[]+|[^}\]]+$/', '', $content);

            $summary = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($summary)) {
                throw new \Exception("Invalid JSON returned from OpenAI");
            }

            $user->userDetail->resume_report = $summary;
            $user->userDetail->save();

            return response()->json([
                'status'  => 'success',
                'analysis' => $summary
            ]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to generate resume analysis.',
                'detail'  => app()->hasDebugModeEnabled() ? $e->getMessage() : null,
            ], 500);
        }
    }


}

