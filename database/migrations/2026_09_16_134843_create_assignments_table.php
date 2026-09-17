<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acad_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('acad_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('acad_subjects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acad_assignments');
    }
};