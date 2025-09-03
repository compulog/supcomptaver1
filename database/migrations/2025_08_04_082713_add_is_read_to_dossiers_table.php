<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsReadToDossiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the 'is_read' boolean column to the 'dossiers' table.
     */
    public function up()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('societe_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Removes the 'is_read' column from the 'dossiers' table.
     */
    public function down()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
    }
}
