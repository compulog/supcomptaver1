<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLettrageIdToLettrageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lettrage', function (Blueprint $table) {
            $table->unsignedBigInteger('lettrage_id')->nullable()->after('id'); 
            // ->nullable() si c’est optionnel, sinon enlève-le
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lettrage', function (Blueprint $table) {
            $table->dropColumn('lettrage_id');
        });
    }
}
