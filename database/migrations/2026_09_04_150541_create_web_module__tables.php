<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        Schema::create('web_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('image_path')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('web_post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->timestamps();
        });

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
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at', 'slug'], 'idx_web_post_search');
        });

        Schema::create('web_galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('web_gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('web_galleries')->cascadeOnDelete();
            $table->enum('type', ['image', 'video_url'])->default('image');
            $table->string('file_or_url');
            $table->string('caption')->nullable();
            $table->timestamps();
        });

        Schema::create('web_hero_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->string('image_path');
            $table->string('button_text', 50)->nullable();
            $table->string('button_url')->nullable();
            $table->unsignedTinyInteger('order_number')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('web_school_facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

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

        Schema::create('web_contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('sender_name', 100);
            $table->string('email_or_phone', 100);
            $table->string('subject', 200);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('web_downloads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path');
            $table->string('file_size', 20)->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();
        });

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
        Schema::dropIfExists('web_downloads');
        Schema::dropIfExists('web_contact_messages');
        Schema::dropIfExists('web_school_achievements');
        Schema::dropIfExists('web_school_facilities');
        Schema::dropIfExists('web_hero_sliders');
        Schema::dropIfExists('web_gallery_items');
        Schema::dropIfExists('web_galleries');
        Schema::dropIfExists('web_posts');
        Schema::dropIfExists('web_post_categories');
        Schema::dropIfExists('web_pages');
        Schema::dropIfExists('web_school_profiles');
    }
};