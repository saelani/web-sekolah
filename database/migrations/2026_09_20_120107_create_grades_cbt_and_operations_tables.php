<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ==========================================
        // 1. MODUL GRADE / PENILAIAN & KURIKULUM
        // ==========================================

        // grade_sumative_scopes
        Schema::create('grade_sumative_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->enum('phase', ['A', 'B', 'C']);
            $table->enum('semester', ['1', '2']);
            $table->string('name');
            $table->timestamps();
        });

        // grade_learning_objectives
        Schema::create('grade_learning_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->foreignId('sumative_scope_id')->nullable()->constrained('grade_sumative_scopes')->cascadeOnDelete();
            $table->enum('phase', ['A', 'B', 'C']);
            $table->unsignedTinyInteger('level');
            $table->enum('semester', ['1', '2']);
            $table->string('code', 20);
            $table->text('description');
            $table->timestamps();

            $table->index(['subject_id', 'phase', 'level', 'semester'], 'idx_grade_lo_filter');
        });

        // grade_formatifs
        Schema::create('grade_formatifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('acad_enrollments')->cascadeOnDelete();
            $table->foreignId('learning_objective_id')->constrained('grade_learning_objectives')->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->boolean('is_achieved')->default(1);
            $table->timestamps();

            $table->unique(['enrollment_id', 'learning_objective_id'], 'uk_grade_formatif');
        });

        // grade_sumatifs
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

        // grade_final_scores
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

        // grade_extracurricular_scores
        Schema::create('grade_extracurricular_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('acad_enrollments')->cascadeOnDelete();
            $table->foreignId('extracurricular_id')->constrained('grade_extracurriculars')->cascadeOnDelete();
            $table->enum('grade', ['A', 'B', 'C', 'D'])->default('B');
            $table->text('description');
            $table->timestamps();
        });

        // grade_p5_projects
        Schema::create('grade_p5_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('acad_academic_years')->cascadeOnDelete();
            $table->enum('phase', ['A', 'B', 'C']);
            $table->string('theme');
            $table->string('title');
            $table->text('description');
            $table->timestamps();
        });

        // grade_p5_subelements
        Schema::create('grade_p5_subelements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p5_project_id')->constrained('grade_p5_projects')->cascadeOnDelete();
            $table->string('dimension');
            $table->string('element');
            $table->text('subelement_name');
            $table->text('target_narrative');
            $table->timestamps();
        });

        // grade_p5_scores
        Schema::create('grade_p5_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('acad_enrollments')->cascadeOnDelete();
            $table->foreignId('p5_subelement_id')->constrained('grade_p5_subelements')->cascadeOnDelete();
            $table->enum('score', ['BB', 'MB', 'BSH', 'SB']);
            $table->timestamps();

            $table->unique(['enrollment_id', 'p5_subelement_id'], 'uk_grade_p5_score');
        });

        // grade_report_cards
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
            $table->boolean('is_locked')->default(0);
            $table->string('place_date_printed', 150)->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });

        // ==========================================
        // 2. MODUL CBT (UJIAN ONLINE)
        // ==========================================

        // cbt_exams
        Schema::create('cbt_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('acad_teachers')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('duration_minutes');
            $table->string('token', 10)->nullable();
            $table->boolean('randomize_questions')->default(1);
            $table->boolean('randomize_options')->default(1);
            $table->boolean('show_result')->default(0);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->boolean('is_active')->default(0);
            $table->timestamps();
        });

        // cbt_questions
        Schema::create('cbt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->enum('type', ['single_choice', 'multiple_choice', 'short_answer', 'essay'])->default('single_choice');
            $table->longText('question_text');
            $table->string('media_path')->nullable();
            $table->unsignedTinyInteger('score_weight')->default(1);
            $table->timestamps();
        });

        // cbt_options
        Schema::create('cbt_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_question_id')->constrained('cbt_questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->string('media_path')->nullable();
            $table->boolean('is_correct')->default(0);
            $table->timestamps();
        });

        // cbt_exam_sessions
        Schema::create('cbt_exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('acad_students')->cascadeOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('max_end_time');
            $table->dateTime('submitted_at')->nullable();
            $table->string('status', 20)->default('ongoing');
            $table->decimal('total_score', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['cbt_exam_id', 'student_id'], 'uk_cbt_session');
        });

        // cbt_student_answers
        Schema::create('cbt_student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_exam_session_id')->constrained('cbt_exam_sessions')->cascadeOnDelete();
            $table->foreignId('cbt_question_id')->constrained('cbt_questions')->cascadeOnDelete();
            $table->foreignId('cbt_option_id')->nullable()->constrained('cbt_options')->nullOnDelete();
            $table->text('answer_text')->nullable();
            $table->text('essay_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('score_given', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['cbt_exam_session_id', 'cbt_question_id'], 'uk_cbt_answer');
        });

        // ==========================================
        // 3. MODUL OPERASIONAL & KEUANGAN SISWA
        // ==========================================

        // ops_student_attendances
        Schema::create('ops_student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('acad_classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('acad_students')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alpa'])->default('Hadir');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date'], 'uk_ops_attendance');
            $table->index(['class_id', 'date', 'status'], 'idx_ops_att_lookup');
        });

        // ops_student_savings
        Schema::create('ops_student_savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('acad_students')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('sys_users')->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['in', 'out']);
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'date', 'type'], 'idx_ops_sav_lookup');
        });

        // ops_cash_flows
        Schema::create('ops_cash_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('acad_academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('acad_classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('sys_users')->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['in', 'out']);
            $table->decimal('amount', 12, 2);
            $table->text('description');
            $table->timestamps();

            $table->index(['class_id', 'academic_year_id', 'type'], 'idx_ops_cash_lookup');
        });

        // ==========================================
        // 4. MODUL WEBSITE & LAINNYA (Tabel Pendukung)
        // ==========================================

        // acad_academic_calendars
        Schema::create('acad_academic_calendars', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('subject_id')->unsigned()->nullable();
            $table->bigInteger('class_room_id')->unsigned()->nullable();
            $table->bigInteger('learning_objective_id')->unsigned()->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('activity_type')->default('kbm');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // acad_assignments
        Schema::create('acad_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('acad_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date');
            $table->timestamps();
        });

        // materials
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subject');
            $table->string('class_level')->default('Kelas 5');
            $table->string('type', 50)->nullable();
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        // web_contact_messages
        Schema::create('web_contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('sender_name', 100);
            $table->string('email_or_phone', 100);
            $table->string('subject', 200);
            $table->text('message');
            $table->boolean('is_read')->default(0);
            $table->timestamps();
        });

        // web_downloads
        Schema::create('web_downloads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path');
            $table->string('file_size', 20)->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();
        });

        // web_galleries
        Schema::create('web_galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // web_gallery_items
        Schema::create('web_gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('web_galleries')->cascadeOnDelete();
            $table->enum('type', ['image', 'video_url'])->default('image');
            $table->string('file_or_url');
            $table->string('caption')->nullable();
            $table->timestamps();
        });

        // web_hero_sliders
        Schema::create('web_hero_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->string('image_path');
            $table->string('button_text', 50)->nullable();
            $table->string('button_url')->nullable();
            $table->unsignedTinyInteger('order_number')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        // web_pages
        Schema::create('web_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('image_path')->nullable();
            $table->boolean('is_published')->default(1);
            $table->timestamps();
        });

        // web_posts
        Schema::create('web_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('web_post_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('sys_users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('thumbnail_path')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->boolean('is_published')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at', 'slug'], 'idx_web_post_search');
        });

        // web_school_achievements
        Schema::create('web_school_achievements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('category', ['Akademik', 'Non-Akademik']);
            $table->enum('level', ['Kecamatan', 'Kota/Kab', 'Provinsi', 'Nasional', 'Internasional']);
            $table->string('winner_name');
            $table->date('achievement_date');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        // web_school_facilities
        Schema::create('web_school_facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        // web_subject_materials
        Schema::create('web_subject_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('acad_teachers')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_subject_materials');
        Schema::dropIfExists('web_school_facilities');
        Schema::dropIfExists('web_school_achievements');
        Schema::dropIfExists('web_posts');
        Schema::dropIfExists('web_pages');
        Schema::dropIfExists('web_hero_sliders');
        Schema::dropIfExists('web_gallery_items');
        Schema::dropIfExists('web_galleries');
        Schema::dropIfExists('web_downloads');
        Schema::dropIfExists('web_contact_messages');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('acad_assignments');
        Schema::dropIfExists('acad_academic_calendars');
        Schema::dropIfExists('ops_cash_flows');
        Schema::dropIfExists('ops_student_savings');
        Schema::dropIfExists('ops_student_attendances');
        Schema::dropIfExists('cbt_student_answers');
        Schema::dropIfExists('cbt_exam_sessions');
        Schema::dropIfExists('cbt_options');
        Schema::dropIfExists('cbt_questions');
        Schema::dropIfExists('cbt_exams');
        Schema::dropIfExists('grade_report_cards');
        Schema::dropIfExists('grade_p5_scores');
        Schema::dropIfExists('grade_p5_subelements');
        Schema::dropIfExists('grade_p5_projects');
        Schema::dropIfExists('grade_extracurricular_scores');
        Schema::dropIfExists('grade_final_scores');
        Schema::dropIfExists('grade_sumatifs');
        Schema::dropIfExists('grade_formatifs');
        Schema::dropIfExists('grade_learning_objectives');
        Schema::dropIfExists('grade_sumative_scopes');
    }
};
