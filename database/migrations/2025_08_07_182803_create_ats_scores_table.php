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
        Schema::create('ats_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('job_description')->nullable();
            $table->string('template')->nullable();
            $table->string('keyword_match', 5, 2)->default(0);
            $table->string('skill_match', 5, 2)->default(0);
            $table->string('formatting_issues', 5, 2)->default(0);
            $table->string('length_issues', 5, 2)->default(0);
            $table->string('score', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ats_scores');
    }
};
