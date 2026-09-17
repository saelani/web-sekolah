<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_learning_objectives', function (Blueprint $table) {
            // Tambahkan kolom sumative_scope_id (nullable agar data lama tidak error)
            $table->foreignId('sumative_scope_id')
                  ->nullable()
                  ->after('subject_id')
                  ->constrained('grade_sumative_scopes')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('grade_learning_objectives', function (Blueprint $table) {
            $table->dropForeign(['sumative_scope_id']);
            $table->dropColumn('sumative_scope_id');
        });
    }
};