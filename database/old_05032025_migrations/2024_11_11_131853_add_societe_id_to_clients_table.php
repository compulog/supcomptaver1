<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('supcompta')->table('clients', function (Blueprint $table) {
            // Ajouter la colonne 'societe_id' en tant qu'unsignedBigInteger
            $table->unsignedBigInteger('societe_id')->nullable(); // Permet à la colonne d'accepter des valeurs nulles

            // Ajouter un index sur 'societe_id' pour de meilleures performances
            $table->index('societe_id');

            // Ajouter la contrainte de clé étrangère
            $table->foreign('societe_id')
                  ->references('id')
                  ->on('societe')  // Assurez-vous que le nom de la table est correct (societes au lieu de societe si nécessaire)
                  ->onDelete('set null'); // Comportement lors de la suppression (set null, cascade, etc.)
        });
    }

    /**
     * Annuler la migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('supcompta')->table('clients', function (Blueprint $table) {
            // Supprimer d'abord la clé étrangère
            $table->dropForeign(['societe_id']);

            // Ensuite, supprimer la colonne 'societe_id'
            $table->dropColumn('societe_id');
        });
    }
};
