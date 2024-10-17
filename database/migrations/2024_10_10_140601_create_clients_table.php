<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        // Créer la table 'clients' avec les champs spécifiés
        Schema::connection('supcompta')->create('clients', function (Blueprint $table) {
            $table->id();                              // Champ auto-incrémenté pour l'identifiant
            $table->string('compte');                  // Compte du client (string)
            $table->string('intitule');                // Intitulé du client (string)
            $table->string('identifiant_fiscal');      // Identifiant fiscal (string)
            $table->string('ICE');                     // ICE du client (string)
            $table->string('type_client');             // Type de client (string)
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
        // Supprimer la table 'clients'
        Schema::connection('supcompta')->dropIfExists('clients');
    }
}
