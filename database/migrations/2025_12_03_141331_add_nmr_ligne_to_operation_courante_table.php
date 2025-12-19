<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('operation_courante', function (Blueprint $table) {
            $table->integer('nmr_ligne')->after('type_journal'); // ou un autre type selon ton besoin
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
            $table->dropColumn('nmr_ligne');
        });
    }
};
