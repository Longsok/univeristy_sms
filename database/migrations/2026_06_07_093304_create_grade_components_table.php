<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('name');                     // e.g. "Midterm Exam", "Assignment 1"
            $table->decimal('max_score', 6, 2)->default(100);
            $table->decimal('weight_percent', 5, 2);   // must sum to 100 per section
            $table->boolean('is_reexam_component')->default(false); // flag re-exam scores
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_components');
    }
};