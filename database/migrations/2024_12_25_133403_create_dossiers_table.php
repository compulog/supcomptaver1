<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDossiersTable extends Migration
{
    public function up()
    {
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du dossier
            $table->unsignedBigInteger('societe_id'); // ID de la société
            $table->timestamps();

            // Relation avec la table sociétés
            $table->foreign('societe_id')->references('id')->on('societes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dossiers');
    }
}
