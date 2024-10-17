<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFournisseursTable extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        // Utilisez la connexion 'mysql_second' qui pointe vers la base 'supcompta'
        Schema::connection('supcompta')->create('fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('compte');                      // Compte fournisseur
            $table->string('intitule');                    // Intitulé
            $table->string('identifiant_fiscal');          // Identifiant fiscal
            $table->bigInteger('ICE');                         // ICE
            $table->string('nature_operation');           // Nature de l'opération
            $table->string('rubrique_tva');                // Rubrique TVA
            $table->string('designation');                 // Désignation
            $table->bigInteger('contre_partie');               // Contre partie
            $table->timestamps();
        });
    }

    /**
     * Annuler la migration.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer la table 'fournisseurs' dans la base 'supcompta'
        Schema::connection('supcompta')->dropIfExists('fournisseurs');
    }
}
