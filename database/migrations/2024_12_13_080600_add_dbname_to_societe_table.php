<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDbnameToSocieteTable extends Migration
{
    public function up()
    {
        Schema::table('societe', function (Blueprint $table) {
            $table->string('dbName')->nullable(); 
        });
    }

    public function down()
    {
        Schema::table('societe', function (Blueprint $table) {
            $table->dropColumn('dbName');  
        });
    }
}
