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

        // Onboarding routes
        Route::get('/onboarding', 'OnboardingApiController@index');
        Route::post('/onboarding', 'OnboardingApiController@save');

        // Profile routes
        Route::get('profile', 'ProfileApiController@profile');
        Route::post('profile', 'ProfileApiController@update');

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
    });
});
