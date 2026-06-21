<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->enum('scholarship_type', ['paid', 'partial', 'full'])
                  ->default('paid')
                  ->after('status')
                  ->comment('paid = self-funded, partial = partial scholarship, full = full scholarship');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('scholarship_type');
        });
    }
};