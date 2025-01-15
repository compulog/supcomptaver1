<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDroitDaccesUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('droit_dacces_user', function (Blueprint $table) {
            $table->id(); // Clé primaire auto-incrémentée
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Clé étrangère vers la table 'users'
            $table->foreignId('droit_dacces_id')->constrained('droit_dacces')->onDelete('cascade'); // Clé étrangère vers la table 'droit_dacces'
            $table->timestamps(); // Colonnes created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('droit_dacces_user');
    }
}
