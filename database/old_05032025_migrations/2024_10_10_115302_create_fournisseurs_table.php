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
            $table->id();
            $table->unsignedBigInteger('societe_id'); // FK vers la table des sociétés
            $table->string('compte', 191); // Limite la longueur pour rester dans les limites MySQL
            $table->string('intitule')->nullable();
            $table->string('identifiant_fiscal')->nullable();
            $table->string('ICE')->nullable();
            $table->string('nature_operation')->nullable();
            $table->string('rubrique_tva')->nullable();
            $table->string('designation')->nullable();
            $table->string('contre_partie')->nullable();
            $table->timestamps();

            // Clé étrangère
            $table->foreign('societe_id')->references('id')->on('societes')->onDelete('cascade');

            // Unicité du compte pour une société spécifique avec taille limitée
            $table->unique(['societe_id', 'compte'], 'unique_compte_societe');
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
