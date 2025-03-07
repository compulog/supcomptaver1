<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategorieToOperationCouranteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('operation_courante', function (Blueprint $table) {
            // Ajoute un champ 'categorie' de type string qui peut être null
            $table->string('categorie')->nullable()->after('type_journal');
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
            // Supprime le champ 'categorie'
            $table->dropColumn('categorie');
        });
    }
}
