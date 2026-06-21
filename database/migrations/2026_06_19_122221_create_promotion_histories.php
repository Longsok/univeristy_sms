<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotion_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('promoted_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('from_year');
            $table->unsignedTinyInteger('to_year');
            $table->string('type')->default('promotion'); 
            $table->string('academic_year')->nullable();  
            $table->decimal('gpa_at_promotion', 4, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_histories');
    }
};