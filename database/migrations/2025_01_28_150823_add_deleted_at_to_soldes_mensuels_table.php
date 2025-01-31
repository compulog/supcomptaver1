<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToSoldesMensuelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('soldes_mensuels', function (Blueprint $table) {
            $table->softDeletes(); // Ajoute le champ deleted_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('soldes_mensuels', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Supprime le champ deleted_at
        });
    }
}