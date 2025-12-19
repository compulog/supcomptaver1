<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLettrageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lettrage', function (Blueprint $table) {
            $table->id(); // ID auto-incrémentée

            $table->string('compte');
            $table->decimal('Acompte', 15, 2)->default(0);
            $table->string('NFacture');

            // Clé étrangère vers operation_courante
            $table->unsignedBigInteger('id_operation');
            $table->foreign('id_operation')
                ->references('id')
                ->on('operation_courante')
                ->onDelete('cascade');

            // Clé étrangère vers users
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lettrage');
    }
}
