<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateLivrToOperationCouranteTable extends Migration
{
    public function up()
    {
        Schema::table('operation_courante', function (Blueprint $table) {
            // ajout d'une colonne datetime nullable pour la date de livraison
            $table->dateTime('date_livr')->nullable()->after('date');
        });
    }

    public function down()
    {
        Schema::table('operation_courante', function (Blueprint $table) {
            $table->dropColumn('date_livr');
        });
    }
}
