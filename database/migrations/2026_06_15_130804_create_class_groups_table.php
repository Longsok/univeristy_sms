<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Class groups table: M1, M2, A1, A2...
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('name', 20);          // e.g. M1, M2, A1, A2
            $table->string('description')->nullable(); // e.g. "Morning Group 1"
            $table->unsignedTinyInteger('year_level')->default(1);
            $table->unsignedInteger('capacity')->default(40);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['program_id', 'name', 'year_level']);
        });

        // Add class_group_id to students table
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('class_group_id')
                  ->nullable()
                  ->after('program_id')
                  ->constrained('class_groups')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['class_group_id']);
            $table->dropColumn('class_group_id');
        });
        Schema::dropIfExists('class_groups');
    }
};