<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_learning_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->enum('phase', ['A', 'B', 'C']);
            $table->unsignedTinyInteger('level');
            $table->enum('semester', ['1', '2']);
            $table->string('code', 20);
            $table->text('description');
            $table->timestamps();

            $table->index(['subject_id', 'phase', 'level', 'semester'], 'idx_grade_lo_filter');
        });

        Schema::create('grade_sumative_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->enum('phase', ['A', 'B', 'C']);
            $table->enum('semester', ['1', '2']);
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('grade_formatifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('acad_enrollments')->cascadeOnDelete();
            $table->foreignId('learning_objective_id')->constrained('grade_learning_objectives')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->nullable();
            $table->boolean('is_achieved')->default(true);
            $table->timestamps();

            $table->unique(['enrollment_id', 'learning_objective_id'], 'uk_grade_formatif');
        });

        Schema::create('grade_sumatifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('acad_enrollments')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->foreignId('sumative_scope_id')->nullable()->constrained('grade_sumative_scopes')->cascadeOnDelete();
            $table->enum('type', ['SLM', 'SAS']);
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->index(['enrollment_id', 'subject_id', 'type'], 'idx_grade_sum_lookup');
        });

        Schema::create('grade_final_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('acad_enrollments')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->decimal('formative_avg', 5, 2)->default(0.00);
            $table->decimal('sumative_slm_avg', 5, 2)->default(0.00);
            $table->decimal('sumative_sas', 5, 2)->default(0.00);
            $table->decimal('final_score', 5, 2)->default(0.00);
            $table->text('highest_achieved_description')->nullable();
            $table->text('lowest_achieved_description')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'subject_id'], 'uk_grade_final_score');
        });

        Schema::create('grade_report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->unique('fk_grade_rc_enroll')->constrained('acad_enrollments')->cascadeOnDelete();
            $table->unsignedTinyInteger('sick')->default(0);
            $table->unsignedTinyInteger('permission')->default(0);
            $table->unsignedTinyInteger('unexcused')->default(0);
            $table->text('extracurricular_notes')->nullable();
            $table->text('teacher_notes')->nullable();
            $table->string('height_weight', 100)->nullable();
            $table->text('health_notes')->nullable();
            $table->boolean('is_promoted')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->string('place_date_printed', 150)->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('grade_p5_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('acad_academic_years')->cascadeOnDelete();
            $table->enum('phase', ['A', 'B', 'C']);
            $table->string('theme');
            $table->string('title');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('grade_p5_subelements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p5_project_id')->constrained('grade_p5_projects')->cascadeOnDelete();
            $table->string('dimension');
            $table->string('element');
            $table->text('subelement_name');
            $table->text('target_narrative');
            $table->timestamps();
        });

        Schema::create('grade_p5_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('acad_enrollments')->cascadeOnDelete();
            $table->foreignId('p5_subelement_id')->constrained('grade_p5_subelements')->cascadeOnDelete();
            $table->enum('score', ['BB', 'MB', 'BSH', 'SB']);
            $table->timestamps();

            $table->unique(['enrollment_id', 'p5_subelement_id'], 'uk_grade_p5_score');
        });

        Schema::create('grade_extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('instructor_name')->nullable();
            $table->timestamps();
        });

        Schema::create('grade_extracurricular_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('acad_enrollments')->cascadeOnDelete();
            $table->foreignId('extracurricular_id')->constrained('grade_extracurriculars')->cascadeOnDelete();
            $table->enum('grade', ['A', 'B', 'C', 'D'])->default('B');
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_extracurricular_scores');
        Schema::dropIfExists('grade_extracurriculars');
        Schema::dropIfExists('grade_p5_scores');
        Schema::dropIfExists('grade_p5_subelements');
        Schema::dropIfExists('grade_p5_projects');
        Schema::dropIfExists('grade_report_cards');
        Schema::dropIfExists('grade_final_scores');
        Schema::dropIfExists('grade_sumatifs');
        Schema::dropIfExists('grade_formatifs');
        Schema::dropIfExists('grade_sumative_scopes');
        Schema::dropIfExists('grade_learning_objectives');
    }
};