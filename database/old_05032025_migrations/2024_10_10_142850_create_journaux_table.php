<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournauxTable extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        // Créer la table 'journaux' avec les champs spécifiés
        Schema::connection('supcompta')->create('journaux', function (Blueprint $table) {
            $table->id();                              // Champ auto-incrémenté pour l'identifiant
            $table->string('code_journal', 100); // Code journal (doit être unique)
            $table->string('intitule')->nullable();                // Intitulé du journal
            $table->string('type_journal')->nullable();             // Type du journal
            $table->string('contre_partie')->nullable();
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
        // Supprimer la table 'journaux'
        Schema::connection('supcompta')->dropIfExists('journaux');
    }
}
