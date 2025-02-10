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
        Schema::table('soldes_mensuels', function (Blueprint $table) {
            $table->string('code_journal', 50); // Ajout du champ code_journal
        });
    }
    
    public function down()
    {
        Schema::table('soldes_mensuels', function (Blueprint $table) {
            $table->dropColumn('code_journal'); // Suppression du champ
        });
    }
    
};
