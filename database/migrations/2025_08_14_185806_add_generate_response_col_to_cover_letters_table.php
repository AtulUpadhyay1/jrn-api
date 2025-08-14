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
            $table->longText('generated_response')->nullable()->after('temperature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cover_letters', function (Blueprint $table) {
            $table->dropColumn('generated_response');
        });
    }
};
