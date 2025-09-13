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
        Schema::create('job_engines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->string('location')->nullable();
            $table->string('contract_type')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('work_type')->nullable();
            $table->string('published_at')->nullable();
            $table->string('jobs_count')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->boolean('api_status')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_engines');
    }
};
