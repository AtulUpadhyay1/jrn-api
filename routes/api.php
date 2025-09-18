<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['namespace' => 'App\Http\Controllers\Api'], function () {

    // Auth API routes
    Route::post('login', 'AuthApiController@login');
    Route::post('register', 'AuthApiController@register');
    Route::post('forgot-password', 'AuthApiController@forgotPassword');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        // Dashboard routes
        Route::get('dashboard', 'DashboardApiController@dashboard');

        // Onboarding routes
        Route::get('/onboarding', 'OnboardingApiController@index');
        Route::post('/onboarding', 'OnboardingApiController@save');

        // Profile routes
        Route::get('profile', 'ProfileApiController@profile');
        Route::post('profile', 'ProfileApiController@update');

        // QR Code routes
        Route::get('profile/qr-code', 'ProfileApiController@getQrCode');
        Route::post('profile/generate-qr-code', 'ProfileApiController@generateQrCode');

        // Education routes
        Route::apiResource('education', 'EducationApiController');

        // Skills routes
        Route::apiResource('skills', 'SkillsApiController');

        // Communications routes
        Route::apiResource('communications', 'CommunicationApiController');

        // Projects routes
        Route::apiResource('projects', 'ProjectApiController');

        // Experience routes
        Route::apiResource('experience', 'ExperienceApiController');

        // Ats Scores routes
        Route::apiResource('ats-scores', 'AtsScoresApiController');

        // Curriculum routes
        Route::apiResource('curriculum', 'CurriculumApiController');

        // Cover Letter routes
        Route::apiResource('cover-letters', 'CoverLetterApiController');

        // Resume Parsing routes
        Route::post('resume-parse', 'ResumeParseApiController@parse');
        Route::post('resume-generate-report', 'ResumeParseApiController@generateReport');

        // Cover Letter Generation routes
        Route::post('cover-letters-generate', 'CoverLetterApiController@generate');

        // Job Engine routes
        Route::apiResource('job-engine', 'JobEngineApiController');
        Route::post('job-engine/search', 'JobEngineApiController@searchJobs');

        // LinkedIn Profile AI routes
        Route::apiResource('linkedin-profile-ai', 'LinkedInProfileAiApiController');
        Route::post('linkedin-profile-ai/snapshot', 'LinkedInProfileAiApiController@snapshot');
        Route::post('linkedin-profile-ai/analyze', 'LinkedInProfileAiApiController@analyzeProfile');

        // Role Play Category and Use Case routes
        Route::get('role-play-categories', 'RolePlayApiController@category');
        Route::get('role-play-use-cases', 'RolePlayApiController@useCases');
    });
});

// Admin Route
Route::group(['prefix' => 'admin', 'namespace' => 'App\Http\Controllers\Api\Admin'], function () {
    Route::post('login', 'AuthApiController@login');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        // Profile routes
        Route::get('profile', 'ProfileApiController@profile');
        Route::post('profile', 'ProfileApiController@update');

        // Role Play Category routes
        Route::apiResource('role-play-categories', 'RolePlayCategoryApiController');

        // Role Play Use Case routes
        Route::apiResource('role-play-use-cases', 'RolePlayUseCaseApiController');

    });
});
