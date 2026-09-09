<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('ops_cash_flows');
        Schema::dropIfExists('ops_student_savings');
        Schema::dropIfExists('ops_student_attendances');
    }
};