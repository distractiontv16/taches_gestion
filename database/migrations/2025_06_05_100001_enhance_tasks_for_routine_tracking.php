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
            // Référence vers la routine source (nullable pour les tâches manuelles)
            $table->foreignId('routine_id')->nullable()->constrained()->onDelete('set null')->after('assigned_to');
            
            // Indique si la tâche est générée automatiquement
            $table->boolean('is_auto_generated')->default(false)->after('routine_id');
            
            // Date de génération de la tâche
            $table->date('generation_date')->nullable()->after('is_auto_generated');
            
            // Date cible pour laquelle la tâche a été générée (peut différer de due_date)
            $table->date('target_date')->nullable()->after('generation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['routine_id']);
            $table->dropColumn([
                'routine_id',
                'is_auto_generated',
                'generation_date',
                'target_date'
            ]);
        });
    }
};
