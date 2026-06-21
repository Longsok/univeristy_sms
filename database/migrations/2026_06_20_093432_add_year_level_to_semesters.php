<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->unsignedTinyInteger('year_level')
                  ->nullable()
                  ->after('semester_number')
                  ->comment('NULL = all years, or specific year level 1-6');
        });
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn('year_level');
        });
    }
};