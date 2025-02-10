<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToSocieteTable extends Migration
{
    public function up()
    {
        Schema::table('societe', function (Blueprint $table) {
            // Ajouter la colonne 'user_id' comme clé étrangère
            $table->bigInteger('user_id')->unsigned()->nullable();

            // Définir la contrainte de clé étrangère
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('societe', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère et la colonne 'user_id'
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}
