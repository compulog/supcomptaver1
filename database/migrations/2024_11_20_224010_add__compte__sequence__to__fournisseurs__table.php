<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompteSequenceToFournisseursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ajouter un champ pour le compteur de séquence
        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->unsignedInteger('compte_sequence')->default(1); // Compteur pour chaque société
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer le champ `compte_sequence` si on annule la migration
        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->dropColumn('compte_sequence');
        });
    }
}
