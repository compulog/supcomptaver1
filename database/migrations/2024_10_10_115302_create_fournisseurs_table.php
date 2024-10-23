<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFournisseursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id(); // Crée une colonne id auto-incrémentée
            $table->string('compte',191)->unique(); // Compte unique
            $table->string('intitule'); // Intitulé
            $table->string('identifiant_fiscal'); // Identifiant fiscal
            $table->string('ICE'); // ICE
            $table->string('nature_operation'); // Nature de l'opération
            $table->string('rubrique_tva'); // Rubrique TVA
            $table->string('designation'); // Désignation
            $table->string('contre_partie'); // Contre partie
            $table->timestamps(); // Créée les colonnes created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fournisseurs'); // Supprime la table si elle existe
    }
}
