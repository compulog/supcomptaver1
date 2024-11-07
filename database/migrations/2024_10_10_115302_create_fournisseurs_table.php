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
            $table->unsignedBigInteger('societe_id')->nullable(); // ID de la société
            $table->string('compte', 191)->unique(); // Compte unique
            $table->string('intitule')->nullable(); // Intitulé
            $table->string('identifiant_fiscal')->nullable(); // Identifiant fiscal
            $table->string('ICE')->nullable(); // ICE
            $table->string('nature_operation')->nullable(); // Nature de l'opération
            $table->string('rubrique_tva')->nullable(); // Rubrique TVA
            $table->string('designation')->nullable(); // Désignation
            $table->string('contre_partie')->nullable(); // Contre partie
            $table->timestamps(); // Crée les colonnes created_at et updated_at
            
            // Ajout d'une clé étrangère
            $table->foreign('societe_id')->references('id')->on('societes')->onDelete('cascade');
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
