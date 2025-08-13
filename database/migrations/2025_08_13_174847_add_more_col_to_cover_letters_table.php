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
        Schema::table('cover_letters', function (Blueprint $table) {
            $table->string('company')->after('name')->nullable();
            $table->string('role')->after('company')->nullable();
            $table->json('skills')->after('description')->nullable();
            $table->unsignedInteger('experience_years')->after('skills')->nullable();
            $table->unsignedInteger('max_words')->after('experience_years')->nullable();
            $table->string('tone')->after('max_words')->nullable();
            $table->string('language')->after('tone')->nullable();
            $table->string('style')->after('language')->nullable();
            $table->json('structure')->after('style')->nullable();
            $table->boolean('use_bullets')->default(false)->after('structure');
            $table->text('closing')->nullable()->after('use_bullets');
            $table->string('sign_off_name')->nullable()->after('closing');
            $table->string('model')->nullable()->after('sign_off_name');
            $table->float('temperature', 8, 2)->default(0.6)->after('model');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cover_letters', function (Blueprint $table) {
            $table->dropColumn([
                'company',
                'role',
                'skills',
                'experience_years',
                'max_words',
                'tone',
                'language',
                'style',
                'structure',
                'use_bullets',
                'closing',
                'sign_off_name',
                'model',
                'temperature',
            ]);
        });
    }
};
