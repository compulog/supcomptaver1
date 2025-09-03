<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdatedByToDossiersTable extends Migration
{
    public function up()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->nullable()->after('societe_id');

            // Clé étrangère vers la table users
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');
        });
    }
}
