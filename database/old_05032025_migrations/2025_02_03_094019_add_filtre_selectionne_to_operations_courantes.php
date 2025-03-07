<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('operation_courante', function (Blueprint $table) {
            $table->string('filtre_selectionne')->nullable()->after('societe_id');
        });
    }

    public function down()
    {
        Schema::table('operation_courante', function (Blueprint $table) {
            $table->dropColumn('filtre_selectionne');
        });
    }

};
