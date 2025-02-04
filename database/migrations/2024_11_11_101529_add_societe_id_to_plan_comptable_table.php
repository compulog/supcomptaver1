<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSocieteIdToPlanComptableTable extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        // Ajouter la colonne 'societe_id' dans la table 'plan_comptable'
        Schema::connection('supcompta')->table('plan_comptable', function (Blueprint $table) {
            $table->foreignId('societe_id')->constrained('societe')->onDelete('cascade'); 
            // Création d'une clé étrangère vers la table 'societe'
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
        Schema::connection('supcompta')->table('plan_comptable', function (Blueprint $table) {
            $table->dropForeign(['societe_id']); // Supprimer la contrainte de clé étrangère
            $table->dropColumn('societe_id');    // Supprimer la colonne
        });
    }
}
