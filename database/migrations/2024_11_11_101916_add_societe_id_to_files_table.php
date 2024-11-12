<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSocieteIdToFilesTable extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        // Ajouter la colonne 'societe_id' dans la table 'files'
        Schema::connection('supcompta')->table('files', function (Blueprint $table) {
            $table->foreignId('societe_id')->constrained('societe')->onDelete('cascade'); 
            // Crée une colonne 'societe_id' qui fait référence à 'id' dans la table 'societe'
        });
    }

    /**
     * Annuler la migration.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer la colonne 'societe_id'
        Schema::connection('supcompta')->table('files', function (Blueprint $table) {
            $table->dropForeign(['societe_id']); // Supprime la contrainte de clé étrangère
            $table->dropColumn('societe_id');    // Supprime la colonne
        });
    }
}
