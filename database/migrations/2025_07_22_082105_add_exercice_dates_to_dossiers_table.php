<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExerciceDatesToDossiersTable extends Migration
{
    public function up()
    {
        Schema::connection('supcompta')->table('dossiers', function (Blueprint $table) {
            $table->date('exercice_debut')->nullable();
            $table->date('exercice_fin')->nullable();
        });
    }

    public function down()
    {
        Schema::connection('supcompta')->table('dossiers', function (Blueprint $table) {
            $table->dropColumn(['exercice_debut', 'exercice_fin']);
        });
    }
}
