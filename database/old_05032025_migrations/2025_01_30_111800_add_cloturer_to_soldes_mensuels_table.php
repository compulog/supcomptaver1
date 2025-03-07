<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCloturerToSoldesMensuelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('soldes_mensuels', function (Blueprint $table) {
            $table->boolean('cloturer')->default(false); // Ajout du champ cloturer
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
            $table->dropColumn('cloturer'); // Suppression du champ cloturer
        });
    }
}

