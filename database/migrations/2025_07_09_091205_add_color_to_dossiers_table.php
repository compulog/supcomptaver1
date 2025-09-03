<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColorToDossiersTable extends Migration
{
    public function up()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->string('color')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
}