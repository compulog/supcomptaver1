<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  // database/migrations/xxxx_xx_xx_create_sections_table.php
public function up()
{
    Schema::create('sections', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->unsignedBigInteger('societe_id');
        $table->timestamps();

        // Ajouter une clé étrangère pour la société
        $table->foreign('societe_id')->references('id')->on('societes')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
