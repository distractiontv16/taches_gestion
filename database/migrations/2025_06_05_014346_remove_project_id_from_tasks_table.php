<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère
            $table->dropForeign(['project_id']);
            // Supprimer la colonne project_id
            $table->dropColumn('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Recréer la colonne project_id
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
        });
    }
};
