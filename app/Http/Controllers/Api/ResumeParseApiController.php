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
            // ✅ OpenAI client
            $client = new \OpenAI\Client(env('OPENAI_API_KEY'));

            // ✅ Define analysis prompt
            $prompt = "Analyze the following resume data and return structured JSON with:
            - profile (name, position, industry, location)
            - resume_analysis: pros (list), cons (list), score (overall, skills_match, experience_relevance, education_strength, presentation), quality (Excellent/Good/Average/Poor), recommendations (list).
            Score must be on 0-100 scale. Return only valid JSON.\n\n"
            . json_encode($user_report);

            // ✅ Call OpenAI
            $response = $client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert resume reviewer.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
            ]);

            // ✅ Parse OpenAI response
            $content = $response->choices[0]->message->content;
            $summary = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // fallback if parsing fails
                $summary = ['raw_output' => $content];
            }

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

