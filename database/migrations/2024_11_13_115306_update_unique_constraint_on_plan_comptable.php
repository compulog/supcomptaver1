<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('plan_comptable', function (Blueprint $table) {
        // Supprimer la contrainte unique existante sur 'compte'
        $table->dropUnique(['compte']);

        // Ajouter une contrainte unique combinée sur 'compte' et 'societe_id'
        $table->unique(['compte', 'societe_id']);
    });
}

public function down()
{
    Schema::table('plan_comptable', function (Blueprint $table) {
        // Revenir à la contrainte unique sur 'compte'
        $table->dropUnique(['compte', 'societe_id']);
        $table->unique('compte');
    });
}

};
