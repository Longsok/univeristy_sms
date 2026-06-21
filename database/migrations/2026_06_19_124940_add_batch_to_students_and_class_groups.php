<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedSmallInteger('batch')
                  ->nullable()
                  ->after('year_level')
                  ->comment('Batch number e.g. 1, 2, 3');
        });

        Schema::table('class_groups', function (Blueprint $table) {
            $table->unsignedSmallInteger('batch')
                  ->nullable()
                  ->after('year_level')
                  ->comment('Batch number e.g. 1, 2, 3');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('batch');
        });
        Schema::table('class_groups', function (Blueprint $table) {
            $table->dropColumn('batch');
        });
    }
};