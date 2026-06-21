<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['enrolled', 'dropped', 'completed'])->default('enrolled');

            // Computed and stored by GradeStatusService after teacher finalises grades
            $table->decimal('final_grade', 5, 2)->nullable();   // 0.00 – 100.00
            $table->string('letter_grade', 3)->nullable();       // A+, A, B+, B … F
            $table->decimal('grade_points', 3, 1)->nullable();   // 4.0, 3.7, 3.3 …
            $table->enum('grade_status', [
                'not_graded',   // default — teacher hasn't finalised yet
                'pass',         // passed normally
                'reexam',       // eligible for re-examination
                'fail',         // failed, must retake course
                'incomplete',   // missing components
            ])->default('not_graded');

            $table->boolean('grades_finalised')->default(false); // teacher locks grades
            $table->timestamp('grades_finalised_at')->nullable();

            $table->unique(['student_id', 'section_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};