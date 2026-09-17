<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_learning_objectives', function (Blueprint $table) {
            $table->dropColumn(['chapter_number', 'chapter_name']);
        });
    }

    public function down(): void
    {
        Schema::table('grade_learning_objectives', function (Blueprint $table) {
            $table->string('chapter_number', 20)->nullable();
            $table->string('chapter_name', 255)->nullable();
        });
    }
};