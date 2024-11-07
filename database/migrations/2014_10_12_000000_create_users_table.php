<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id'); // Crée une colonne id auto-incrémentée
            $table->string('name'); // Nom de l'utilisateur
            $table->string('email', 191)->unique(); // Email unique
            $table->string('password'); // Mot de passe
            $table->bigInteger('phone')->nullable(); // Numéro de téléphone
            $table->string('location')->nullable(); // Localisation
            $table->string('about_me')->nullable(); // Informations sur l'utilisateur
            $table->unsignedBigInteger('societe_id')->nullable(); // Id de la société
            $table->rememberToken(); // Jeton pour se souvenir de l'utilisateur
            $table->timestamps(); // Crée les colonnes created_at et updated_at

            // Définir la clé étrangère si vous l'avez
            $table->foreign('societe_id')->references('id')->on('societes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users'); // Supprime la table si elle existe
    }
}
