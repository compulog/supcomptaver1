<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByUserIdToSocieteTable extends Migration
{
    public function up()
    {
        Schema::table('societe', function (Blueprint $table) {
            // Ajout du champ 'created_by_user_id' qui fait référence à l'utilisateur qui a créé la société
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('societe', function (Blueprint $table) {
            // Suppression de la colonne 'created_by_user_id'
            $table->dropColumn('created_by_user_id');
        });
    }
}
