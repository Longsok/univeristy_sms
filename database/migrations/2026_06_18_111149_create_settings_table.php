<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
 
        // Insert default settings
        DB::table('settings')->insert([
            ['key' => 'university_name',      'value' => 'Royal University of Phnom Penh', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'university_name_kh',   'value' => 'សាកលវិទ្យាល័យភូមិន្ទភ្នំពេញ',   'created_at' => now(), 'updated_at' => now()],
            ['key' => 'university_short',     'value' => 'RUPP',                           'created_at' => now(), 'updated_at' => now()],
            ['key' => 'academic_year',        'value' => '2025-2026',                      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pass_threshold',       'value' => '50',                             'created_at' => now(), 'updated_at' => now()],
            ['key' => 'reexam_threshold',     'value' => '45',                             'created_at' => now(), 'updated_at' => now()],
            ['key' => 'attendance_weeks',     'value' => '16',                             'created_at' => now(), 'updated_at' => now()],
            ['key' => 'min_gpa_promotion',    'value' => '1.0',                            'created_at' => now(), 'updated_at' => now()],
            ['key' => 'contact_email',        'value' => 'admin@rupp.edu.kh',              'created_at' => now(), 'updated_at' => now()],
            ['key' => 'contact_phone',        'value' => '+855 23 880 734',                'created_at' => now(), 'updated_at' => now()],
            ['key' => 'address',              'value' => 'Russian Federation Blvd, Phnom Penh', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
 
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};