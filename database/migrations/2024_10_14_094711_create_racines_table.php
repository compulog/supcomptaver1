<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRacinesTable extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        // Utilisation de la connexion "supcompta" pour créer la table
        Schema::connection('supcompta')->create('racines', function (Blueprint $table) {
            $table->id(); // Champ auto-incrémenté 'id'
            $table->string('type'); // Champ 'type'
            $table->string('categorie'); // Champ 'categorie'
            $table->string('Num_racines'); // Champ 'Num_racines'
            $table->string('Nom_racines'); // Champ 'Nom_racines'
            $table->decimal('Taux', 8, 2); // Champ 'Taux', avec 8 chiffres au total, dont 2 après la virgule
            $table->timestamps(); // Champs 'created_at' et 'updated_at'
            $table->softDeletes(); // Champ 'deleted_at' pour les suppressions douces
        });
    }

    /**
     * Répéter l'opération de migration en cas d'échec.
     *
     * @return void
     */
    public function down()
    {
        // Utilisation de la connexion "supcompta" pour supprimer la table
        Schema::connection('supcompta')->dropIfExists('racines');
    }
}
