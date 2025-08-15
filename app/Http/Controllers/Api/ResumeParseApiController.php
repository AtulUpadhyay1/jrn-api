<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ResumeParseService;

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
}
