<?php
// database/migrations/2026_06_15_000001_create_manual_attendance_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop old QR-based tables and recreate clean
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');

        // New simple manual attendance table
        // One row per student per week per section
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week'); // 1–16
            $table->enum('status', ['present', 'absent', 'late', 'permission'])
                  ->default('absent');
            $table->timestamps();

            // One record per student per week per section
            $table->unique(['section_id', 'student_id', 'week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};