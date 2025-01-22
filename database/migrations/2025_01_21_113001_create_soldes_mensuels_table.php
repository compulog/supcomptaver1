<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoldesMensuelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soldes_mensuels', function (Blueprint $table) {
            $table->id();
            $table->date('mois');  // Le premier jour du mois
            $table->decimal('solde_initial', 10, 2)->default(0); // Solde initial
            $table->decimal('total_recette', 10, 2)->default(0); // Total des recettes
            $table->decimal('total_depense', 10, 2)->default(0); // Total des dépenses
            $table->decimal('solde_final', 10, 2)->default(0); // Solde final
            $table->timestamps(); // Colonnes created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soldes_mensuels');
    }
}
