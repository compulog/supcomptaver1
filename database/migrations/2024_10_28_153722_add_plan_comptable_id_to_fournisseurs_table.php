<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->foreignId('plan_comptable_id')->constrained('plan_comptable')->nullable(); // Liez à la table plan_comptable
        });
    }

    public function down()
    {
        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->dropForeign(['plan_comptable_id']);
            $table->dropColumn('plan_comptable_id');
        });
    }
};
