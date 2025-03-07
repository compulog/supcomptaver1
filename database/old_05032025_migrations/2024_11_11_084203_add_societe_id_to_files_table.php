<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('files', function (Blueprint $table) {
        // Ajouter la colonne 'societe_id' en tant qu'unsignedBigInteger
        $table->unsignedBigInteger('societe_id');

        // Ajouter la contrainte de clé étrangère
        $table->foreign('societe_id')
              ->references('id')
              ->on('societe')
              ->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('files', function (Blueprint $table) {
        // Supprimer la clé étrangère d'abord
        $table->dropForeign(['societe_id']);

        // Ensuite, supprimer la colonne 'societe_id'
        $table->dropColumn('societe_id');
    });
}

};
