<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. sys_users
        Schema::create('sys_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role', ['admin', 'headmaster', 'teacher', 'student'])->default('teacher');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. users (bawaan Laravel jika masih dipakai)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // 3. acad_academic_years
        Schema::create('acad_academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year', 9);
            $table->enum('semester', ['1', '2']);
            $table->boolean('is_active')->default(0)->index('idx_acad_ay_active');
            $table->timestamps();
        });

        // 4. acad_subjects
        Schema::create('acad_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->enum('category', ['agama', 'umum', 'muatan_lokal']);
            $table->unsignedTinyInteger('order_number')->default(0);
            $table->timestamps();
        });

        // 5. grade_extracurriculars
        Schema::create('grade_extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('instructor_name')->nullable();
            $table->timestamps();
        });

        // 6. web_post_categories
        Schema::create('web_post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->timestamps();
        });

        // 7. Web Profile & Lainnya yang independen
        Schema::create('web_school_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('npsn', 10);
            $table->string('school_name');
            $table->string('headmaster_name')->nullable();
            $table->char('accreditation', 1)->default('A');
            $table->text('address');
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 100)->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('maps_embed')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_school_profiles');
        Schema::dropIfExists('web_post_categories');
        Schema::dropIfExists('grade_extracurriculars');
        Schema::dropIfExists('acad_subjects');
        Schema::dropIfExists('acad_academic_years');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sys_users');
    }
};
