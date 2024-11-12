<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSocieteIdToClientsTable extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('supcompta')->table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('societe_id')->nullable()->after('id'); // Ajout de la colonne societe_id
            $table->foreign('societe_id')->references('id')->on('societe')->onDelete('cascade'); // Définir la clé étrangère
        });
    }

    /**
     * Annuler la migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('supcompta')->table('clients', function (Blueprint $table) {
            $table->dropForeign(['societe_id']); // Supprimer la contrainte de clé étrangère
            $table->dropColumn('societe_id');    // Supprimer la colonne societe_id
        });
    }
}
