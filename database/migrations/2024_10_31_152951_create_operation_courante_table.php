<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationCouranteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operation_courante', function (Blueprint $table) {
            $table->id(); // Clé primaire auto-incrémentée
            $table->date('date'); // Date de l'opération
            $table->string('numero_dossier'); // N° dossier
            $table->string('numero_facture'); // N° facture
            $table->string('compte'); // Compte
            $table->string('libelle'); // Libellé
            $table->decimal('debit', 15, 2)->default(0); // Débit
            $table->decimal('credit', 15, 2)->default(0); // Crédit
            $table->string('contre_partie'); // Contre-Partie
            $table->string('rubrique_tva'); // Rubrique TVA
            $table->string('compte_tva'); // Compte TVA
            $table->boolean('prorat_de_deduction')->default(false); // Prorat de déduction
            $table->string('piece_justificative'); // Pièce justificative
            $table->string('type_journal'); // Type de journal
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
        Schema::dropIfExists('operation_courante');
    }
}
