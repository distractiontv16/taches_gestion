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
        Schema::table('routines', function (Blueprint $table) {
            // Heure d'échéance spécifique pour les tâches générées
            $table->time('due_time')->nullable()->after('end_time');
            
            // Statut actif/inactif pour contrôler la génération
            $table->boolean('is_active')->default(true)->after('workdays_only');
            
            // Priorité des tâches générées
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('is_active');
            
            // Dernière date de génération pour éviter les doublons
            $table->date('last_generated_date')->nullable()->after('priority');
            
            // Métadonnées pour le suivi
            $table->integer('total_tasks_generated')->default(0)->after('last_generated_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->dropColumn([
                'due_time',
                'is_active',
                'priority',
                'last_generated_date',
                'total_tasks_generated'
            ]);
        });
    }
};
