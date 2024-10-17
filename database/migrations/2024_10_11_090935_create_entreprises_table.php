<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntreprisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Utiliser la connexion à la base de données 'supcompta'
        Schema::connection('supcompta')->create('entreprises', function (Blueprint $table) {
            $table->id(); // Champ ID auto-incrémenté
            $table->string('nom_entreprise'); // Champ pour le nom de l'entreprise
            $table->string('ice', 255)->unique(); // Limite la longueur de la colonne ice à 255 caractères pour éviter l'erreur d'index
            $table->string('rc')->unique(); // Champ RC unique
            $table->string('identifiant_fiscal')->unique(); // Champ Identifiant Fiscal unique
            $table->year('annee'); // Champ pour l'année
            $table->string('nature_operation'); // Champ pour la nature de l'opération
            $table->string('rubrique_tva'); // Champ pour la rubrique TVA
            $table->string('designation'); // Champ pour la désignation
            $table->timestamps(); // Champs created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Utiliser la connexion à la base de données 'supcompta'
        Schema::connection('supcompta')->dropIfExists('entreprises');
    }
}
