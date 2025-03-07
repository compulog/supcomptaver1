<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_invalid_column_to_fournisseurs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvalidColumnToFournisseursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->boolean('invalid')->default(0); // Ajoute une colonne 'invalid' de type boolean, avec une valeur par défaut de 0
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->dropColumn('invalid'); // Supprime la colonne 'invalid' si la migration est annulée
        });
    }
}
