<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_component_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 6, 2);
            $table->decimal('reexam_score', 6, 2)->nullable(); // filled if student sat re-exam
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'grade_component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};