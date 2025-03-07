<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodeSocieteToSocieteTable extends Migration
{
    public function up()
    {
        Schema::table('societe', function (Blueprint $table) {
            $table->string('code_societe')->nullable(); // Ajout du champ code_societe
        });
    }

    public function down()
    {
        Schema::table('societe', function (Blueprint $table) {
            $table->dropColumn('code_societe'); // Suppression du champ code_societe
        });
    }
}

