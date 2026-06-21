<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
 
return new class extends Migration {
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->unsignedTinyInteger('semester_number')
                  ->default(1)
                  ->after('academic_year')
                  ->comment('1 = Semester 1, 2 = Semester 2, 3 = Semester 3');
        });
 
        // Auto-set semester_number from existing name field
        // e.g. "Semester 1" → 1, "Semester 2" → 2
        DB::statement("
            UPDATE semesters
            SET semester_number = CASE
                WHEN name LIKE '%1%' THEN 1
                WHEN name LIKE '%2%' THEN 2
                WHEN name LIKE '%3%' THEN 3
                ELSE 1
            END
        ");
    }
 
    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn('semester_number');
        });
    }
};
 