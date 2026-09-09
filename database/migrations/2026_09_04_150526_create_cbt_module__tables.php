<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('acad_teachers')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('duration_minutes');
            $table->string('token', 10)->nullable();
            $table->boolean('randomize_questions')->default(true);
            $table->boolean('randomize_options')->default(true);
            $table->boolean('show_result')->default(false);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('cbt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->enum('type', ['single_choice', 'multiple_choice', 'short_answer', 'essay'])->default('single_choice');
            $table->longText('question_text');
            $table->string('media_path')->nullable();
            $table->unsignedTinyInteger('score_weight')->default(1);
            $table->timestamps();
        });

        Schema::create('cbt_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_question_id')->constrained('cbt_questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->string('media_path')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        Schema::create('cbt_exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('acad_students')->cascadeOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('max_end_time');
            $table->dateTime('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'blocked'])->default('in_progress');
            $table->decimal('total_score', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['cbt_exam_id', 'student_id'], 'uk_cbt_session');
        });

        Schema::create('cbt_student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_exam_session_id')->constrained('cbt_exam_sessions')->cascadeOnDelete();
            $table->foreignId('cbt_question_id')->constrained('cbt_questions')->cascadeOnDelete();
            $table->foreignId('cbt_option_id')->nullable()->constrained('cbt_options')->nullOnDelete();
            $table->text('essay_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('score_given', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['cbt_exam_session_id', 'cbt_question_id'], 'uk_cbt_answer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_student_answers');
        Schema::dropIfExists('cbt_exam_sessions');
        Schema::dropIfExists('cbt_options');
        Schema::dropIfExists('cbt_questions');
        Schema::dropIfExists('cbt_exams');
    }
};