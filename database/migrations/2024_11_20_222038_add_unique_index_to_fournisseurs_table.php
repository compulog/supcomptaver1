<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToFournisseursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ajouter l'index unique combiné sur les colonnes `societe_id` et `compte`
        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->unique(['societe_id', 'compte'], 'unique_societe_compte');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer l'index unique combiné si la migration est annulée
        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->dropUnique('unique_societe_compte');
        });
    }
}
