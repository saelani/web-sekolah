<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acad_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('acad_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->string('day_name'); // Contoh: Senin, Selasa, dll.
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acad_schedules');
    }
};