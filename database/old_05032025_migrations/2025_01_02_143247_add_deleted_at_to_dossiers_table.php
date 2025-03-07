<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToDossiersTable extends Migration
{
    public function up()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            // Ajout du champ deleted_at pour la suppression logique (soft delete)
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            // Suppression du champ deleted_at si la migration est annulée
            $table->dropSoftDeletes();
        });
    }
}
