<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelevesBancairesTable extends Migration
{
    public function up()
    {
        Schema::create('releves_bancaires', function (Blueprint $table) {
            $table->id(); // id auto-incrémenté
            $table->unsignedBigInteger('idfile'); // clé étrangère vers la table 'files'
            $table->string('code_journal', 50); // code journal, longueur ajustable
            $table->unsignedTinyInteger('mois'); // mois entre 1 et 12
            $table->unsignedSmallInteger('annee'); // année (ex: 2025)
            $table->timestamps();

            // Définition de la clé étrangère
            $table->foreign('idfile')->references('id')->on('files')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('releves_bancaires');
    }
}
