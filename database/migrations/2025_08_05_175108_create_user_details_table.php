<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('address')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('current_position')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('industry')->nullable();
            $table->string('desired_position')->nullable();
            $table->string('work_location_preference')->nullable();
            $table->string('expected_salary_range')->nullable();
            $table->string('job_type_preferences')->nullable();
            $table->text('skills_technologies')->nullable();
            $table->text('primary_purpose_career_development')->nullable();
            $table->text('career_goals')->nullable();
            $table->text('motivates_professionally')->nullable();
            $table->text('areas_of_interest')->nullable();
            $table->string('preferred_learning_methods')->nullable();
            $table->string('resume')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('video_introduction')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();
            $table->string('github')->nullable();
            $table->string('personal_website')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('behance')->nullable();
            $table->string('dribbble')->nullable();
            $table->integer('step')->default(1);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
