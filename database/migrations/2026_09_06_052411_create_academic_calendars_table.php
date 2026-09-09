<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acad_academic_calendars', function (Blueprint $table) {
            $table->id();
            
            // Tanpa constrained() agar tidak mengunci nama tabel di level MySQL
            $table->foreignId('subject_id')->nullable();
            $table->foreignId('class_room_id')->nullable();
            $table->foreignId('learning_objective_id')->nullable();

            // Rentang Tanggal Kegiatan
            $table->date('start_date');
            $table->date('end_date');

            $table->string('activity_type')->default('kbm'); // kbm, slm, sls, libur, event
            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acad_academic_calendars');
    }
};