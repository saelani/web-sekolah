<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acad_academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year', 9);
            $table->enum('semester', ['1', '2']);
            $table->boolean('is_active')->default(false)->index('idx_acad_ay_active');
            $table->timestamps();
        });

        Schema::create('acad_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('sys_users')->nullOnDelete();
            $table->string('nip', 18)->unique()->nullable();
            $table->string('nuptk', 16)->unique()->nullable();
            $table->string('name');
            $table->string('front_title', 50)->nullable();
            $table->string('back_title', 50)->nullable();
            $table->enum('gender', ['L', 'P']);
            $table->string('photo_path')->nullable();
            $table->enum('role_type', ['headmaster', 'class_teacher', 'subject_teacher'])->default('class_teacher');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('acad_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('acad_academic_years')->cascadeOnDelete();
            $table->string('name', 50);
            $table->enum('phase', ['A', 'B', 'C']);
            $table->unsignedTinyInteger('level');
            $table->foreignId('teacher_id')->nullable()->constrained('acad_teachers')->nullOnDelete();
            $table->timestamps();

            $table->index(['academic_year_id', 'level', 'phase'], 'idx_acad_classes_search');
        });

        Schema::create('acad_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->enum('category', ['agama', 'umum', 'muatan_lokal']);
            $table->unsignedTinyInteger('order_number')->default(0);
            $table->timestamps();
        });

        Schema::create('acad_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('sys_users')->nullOnDelete();
            $table->string('nis', 20)->unique();
            $table->string('nisn', 10)->unique();
            $table->string('name');
            $table->enum('gender', ['L', 'P']);
            $table->enum('religion', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'])->default('Islam');
            $table->string('pob', 100);
            $table->date('dob');
            $table->text('address')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('photo_path')->nullable();
            $table->foreignId('class_id')->nullable()->constrained('acad_classes')->nullOnDelete();
            $table->timestamps();

            $table->index(['religion', 'gender', 'name'], 'idx_acad_students_filter');
        });

        Schema::create('acad_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('acad_academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('acad_classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('acad_students')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['academic_year_id', 'student_id'], 'uk_acad_enrollment');
        });

        Schema::create('acad_teacher_subject_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('acad_teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('acad_classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('acad_academic_years')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'subject_id', 'class_id', 'academic_year_id'], 'uk_acad_tsc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acad_teacher_subject_classes');
        Schema::dropIfExists('acad_enrollments');
        Schema::dropIfExists('acad_students');
        Schema::dropIfExists('acad_subjects');
        Schema::dropIfExists('acad_classes');
        Schema::dropIfExists('acad_teachers');
        Schema::dropIfExists('acad_academic_years');
    }
};