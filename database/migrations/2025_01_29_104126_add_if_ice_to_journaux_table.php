<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('journaux', function (Blueprint $table) {
            $table->string('if', 8)->nullable()->after('type_journal'); // Ajoute la colonne IF
            $table->string('ice', 15)->nullable()->after('if'); // Ajoute la colonne ICE
            $table->softDeletes()->after('ice'); // Ajoute Soft Delete (deleted_at)
        });
    }

    public function down()
    {
        Schema::table('journaux', function (Blueprint $table) {
            $table->dropColumn(['if', 'ice']); // Supprime IF et ICE en cas de rollback
            $table->dropSoftDeletes(); // Supprime deleted_at si on rollback
        });
    }
};
