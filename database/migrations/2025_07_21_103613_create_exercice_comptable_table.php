<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExerciceComptableTable extends Migration
{
    public function up()
    {
        Schema::create('exercice_comptable', function (Blueprint $table) {
            $table->id(); // ID principal

            $table->string('nom_exercice'); // Exemple : "2024", "2025"

            // Clé étrangère vers la table societe
            $table->unsignedBigInteger('id_societe');
            $table->foreign('id_societe')->references('id')->on('societe')->onDelete('cascade');

            $table->date('date_debut');
            $table->date('date_fin');

            $table->boolean('cloture')->default(false); // true = clôturé

            $table->timestamps(); // created_at et updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('exercice_comptable');
    }
}
