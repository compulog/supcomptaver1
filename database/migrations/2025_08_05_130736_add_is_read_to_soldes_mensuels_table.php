<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsReadToSoldesMensuelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('soldes_mensuels', function (Blueprint $table) {
            $table->boolean('is_read')->default(0)->after('solde_final');
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
            $table->dropColumn('is_read');
        });
    }
}
