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
        $curriculums = Curriculum::where('user_id', auth()->id())->latest()->get();
        $educations = Education::where('user_id', auth()->id())->latest()->get();
        $experiences = Experience::where('user_id', auth()->id())->latest()->get();
        $projects = Project::where('user_id', auth()->id())->latest()->get();
        $skills = Skill::where('user_id', auth()->id())->latest()->get();

        return response()->json([
            'status' => 'success',
            'communications' => $communications,
            'curriculums'    => $curriculums,
            'educations'     => $educations,
            'experiences'    => $experiences,
            'projects'       => $projects,
            'skills'        => $skills,
        ]);
    }
}

