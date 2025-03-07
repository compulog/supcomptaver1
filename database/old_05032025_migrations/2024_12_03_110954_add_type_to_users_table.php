<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
 
    public function up()
    {
        Schema::connection('mysql')->table('users', function (Blueprint $table) {
            // Ajouter la colonne 'societe_id' en tant qu'unsignedBigInteger
            $table->string('type')->nullable(); // Permet à la colonne d'accepter des valeurs nulles

                 });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type');  // Suppression du champ 'type'
        });
    }
}
