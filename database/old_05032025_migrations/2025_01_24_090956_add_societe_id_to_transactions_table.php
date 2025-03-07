<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSocieteIdToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Ajouter la colonne societe_id
            $table->unsignedBigInteger('societe_id')->nullable();

            // Ajouter une clé étrangère vers la table societes (en supposant que la table 'societes' existe)
            $table->foreign('societe_id')->references('id')->on('societes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Supprimer la clé étrangère et la colonne
            $table->dropForeign(['societe_id']);
            $table->dropColumn('societe_id');
        });
    }
}

