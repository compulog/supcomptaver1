<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocieteTable extends Migration
{
    public function up()
    {
        Schema::create('societe', function (Blueprint $table) {
            $table->id(); // Crée une colonne 'id' auto-incrémentée
            $table->string('raison_sociale'); // Raison sociale
            $table->string('forme_juridique')->nullable(); // Forme juridique
            $table->string('siege_social')->nullable(); // Siège social
            $table->string('patente')->nullable(); // Patente
            $table->string('rc')->nullable(); // RC
            $table->string('centre_rc')->nullable(); // Centre RC
            $table->string('identifiant_fiscal')->nullable(); // Identifiant fiscal
            $table->string('ice')->nullable(); // ICE
            $table->boolean('assujettie_partielle_tva')->nullable(); // Assujettie partielle à la TVA
            $table->decimal('prorata_de_deduction', 5, 2)->nullable(); // Prorata de déduction
            $table->date('exercice_social_debut')->nullable(); // Exercice social début
            $table->date('exercice_social_fin')->nullable(); // Exercice social fin
            $table->date('date_creation')->nullable(); // Date de création
            $table->string('nature_activite')->nullable(); // Nature de l'activité
            $table->string('activite')->nullable(); // Activité
            $table->string('regime_declaration')->nullable(); // Régime de déclaration
            $table->string('fait_generateur')->nullable(); // Fait générateur (modifiable en string)
            $table->string('rubrique_tva')->nullable(); // Rubrique TVA
            $table->string('designation')->nullable(); // Désignation
            $table->integer('nombre_chiffre_compte')->nullable(); // Nombre de chiffres du compte
            $table->string('modele_comptable')->nullable(); // Modèle comptable
            $table->timestamps(); // Crée les colonnes 'created_at' et 'updated_at'
        });
    }

    public function down()
    {
        Schema::dropIfExists('societe'); // Supprime la table si elle existe
    }
}
