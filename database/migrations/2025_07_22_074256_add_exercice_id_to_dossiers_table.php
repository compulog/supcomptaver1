<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExerciceIdToDossiersTable extends Migration
{
    public function up()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->unsignedBigInteger('exercice_id')->after('societe_id');

            // Définir la clé étrangère
            $table->foreign('exercice_id')
                  ->references('id')->on('exercice_comptables')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropForeign(['exercice_id']);
            $table->dropColumn('exercice_id');
        });
    }
}
