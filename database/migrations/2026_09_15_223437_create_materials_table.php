<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul materi
            $table->string('slug')->unique(); // Slug untuk URL front-end
            $table->string('subject'); // Mata pelajaran (misal: Bahasa Indonesia, Matematika)
            $table->string('class_level')->default('Kelas 5'); // Tingkat kelas
            $table->enum('type', ['pdf', 'link', 'summary']); // Jenis materi
            $table->text('content')->nullable(); // Untuk ringkasan teks atau catatan
            $table->string('file_path')->nullable(); // Untuk file PDF (disimpan di storage)
            $table->string('external_url')->nullable(); // Untuk link eksternal (YouTube, Google Drive, dll)
            $table->boolean('is_active')->default(true); // Status tampil/tidak di front-end
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};