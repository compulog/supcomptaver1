<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCnssToSocieteTable extends Migration
{
    public function up()
    {
        Schema::table('societe', function (Blueprint $table) {
            // Ajoute le champ CNSS à la table 'societe'
            $table->string('cnss')->nullable(); // Champ CNSS
        });
    }

    public function down()
    {
        Schema::table('societe', function (Blueprint $table) {
            // Supprime le champ CNSS si la migration est annulée
            $table->dropColumn('cnss');
        });
    }
}
