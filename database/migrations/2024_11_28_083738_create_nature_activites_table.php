<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNatureActivitesTable extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nature_activites', function (Blueprint $table) {
            $table->id();
            $table->string('numero');  // Champ pour le numéro de la nature de l'activité
            $table->text('description');  // Champ pour la description de la nature de l'activité
            $table->timestamps();  // Les champs created_at et updated_at
        });
    }

    /**
     * Inverser la migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nature_activites');
    }
}
