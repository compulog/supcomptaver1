<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToOperationCouranteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('operation_courante', function (Blueprint $table) {
            $table->boolean('fact_lettrer')->default(false); // Facture lettrée (booléen)
            $table->decimal('taux_ras_tva', 5, 2)->nullable(); // Taux RAS TVA (nullable si non obligatoire)
            $table->string('nature_op')->nullable(); // Nature de l'opération
            $table->date('date_lettrage')->nullable(); // Date de lettrage
            $table->string('mode_pay')->nullable(); // Mode de paiement
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('operation_courante', function (Blueprint $table) {
            $table->dropColumn(['fact_lettrer', 'taux_ras_tva', 'nature_op', 'date_lettrage', 'mode_pay']);
        });
    }
}
