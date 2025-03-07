<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanComptableTable extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        // Créer la table 'plan_comptable' avec les champs spécifiés
        Schema::connection('supcompta')->create('plan_comptable', function (Blueprint $table) {
            $table->id();                              // Champ auto-incrémenté pour l'identifiant
            $table->string('compte',191)->unique();       // Compte (doit être unique)
            $table->string('intitule');                // Intitulé du compte (string)
            $table->timestamps();                      // Champs pour created_at et updated_at
        });
    }

    /**
     * Annuler la migration.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer la table 'plan_comptable'
        Schema::connection('supcompta')->dropIfExists('plan_comptable');
    }
}
