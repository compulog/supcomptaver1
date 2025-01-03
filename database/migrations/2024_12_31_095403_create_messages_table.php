<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); // Crée une colonne 'id' auto-incrémentée
            $table->text('text_message'); // Le message en texte
            $table->unsignedBigInteger('user_id'); // Clé étrangère vers la table users
            $table->unsignedBigInteger('societe_id'); // Clé étrangère vers la table societe
            $table->unsignedBigInteger('folder_id')->nullable(); // Clé étrangère vers la table folders
            $table->unsignedBigInteger('file_id')->nullable(); // Clé étrangère vers la table files
            $table->timestamps(); // Timestamps pour la création et mise à jour
            $table->softDeletes(); // Permet les suppressions douces (soft deletes)

            // Définir les clés étrangères
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('societe_id')->references('id')->on('societe')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('set null');
            $table->foreign('file_id')->references('id')->on('files')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
