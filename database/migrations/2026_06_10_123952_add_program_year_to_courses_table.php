<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Add program_id so courses belong to a program
            $table->foreignId('program_id')
                  ->nullable()
                  ->after('department_id')
                  ->constrained()
                  ->nullOnDelete();

            // Add year_level so courses are grouped by year
            $table->unsignedTinyInteger('year_level')
                  ->default(1)
                  ->after('semester_id');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn(['program_id', 'year_level']);
        });
    }
};
