<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResteMontantLettreToOperationCouranteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('operation_courante', function (Blueprint $table) {
            $table->decimal('reste_montant_lettre', 15, 2)->default(0)->after('type_journal');
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
            $table->dropColumn('reste_montant_lettre');
        });
    }
}
