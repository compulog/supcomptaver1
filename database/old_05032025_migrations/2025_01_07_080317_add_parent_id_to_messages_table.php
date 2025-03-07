<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            // Ajout de la colonne 'parent_id' qui permet de répondre à un message
            $table->unsignedBigInteger('parent_id')->nullable()->after('file_id'); // La colonne peut être NULL si ce n'est pas une réponse

            // Définir la clé étrangère pour 'parent_id' pointant vers la table 'messages'
            $table->foreign('parent_id')->references('id')->on('messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            // Supprimer la colonne 'parent_id' en cas de rollback
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
}
