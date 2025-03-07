<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSocieteIdToSoldesMensuelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('soldes_mensuels', function (Blueprint $table) {
            // Ajouter la colonne 'societe_id'
            $table->unsignedBigInteger('societe_id');

            // Si vous souhaitez ajouter une contrainte de clé étrangère, décommentez la ligne suivante
            // $table->foreign('societe_id')->references('id')->on('societes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('soldes_mensuels', function (Blueprint $table) {
            // Supprimer la colonne 'societe_id'
            $table->dropColumn('societe_id');
        });
    }
}
