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
        Schema::connection('supcompta')->table('files', function (Blueprint $table) {
            // Ajouter la colonne 'societe_id' en tant qu'unsignedBigInteger
            $table->unsignedBigInteger('folders')->nullable(); // Permet à la colonne d'accepter des valeurs nulles

            // Ajouter un index sur 'societe_id' pour de meilleures performances
            $table->index('folders_id');

            // Ajouter la contrainte de clé étrangère
            $table->foreign('folders_id')
                  ->references('id')
                  ->on('folders')  // Assurez-vous que le nom de la table est correct (societes au lieu de societe si nécessaire)
                  ->onDelete('set null'); // Comportement lors de la suppression (set null, cascade, etc.)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('supcompta')->table('files', function (Blueprint $table) {
            // Supprimer d'abord la clé étrangère
            $table->dropForeign(['folders_id']);

            // Ensuite, supprimer la colonne 'societe_id'
            $table->dropColumn('folders_id');
        });
    }
};


