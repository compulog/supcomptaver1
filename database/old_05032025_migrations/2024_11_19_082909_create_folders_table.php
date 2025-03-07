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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Le nom du dossier
            $table->unsignedBigInteger('societe_id'); // Colonne pour lier le dossier à une société
            $table->unsignedBigInteger('folder_id')->nullable(); // Colonne pour lier un dossier parent (clé étrangère)
            $table->timestamps();
            $table->softDeletes(); // Ajouter la colonne deleted_at pour les suppressions douces (soft deletes)

            // Ajouter une clé étrangère pour la société
            $table->foreign('societe_id')->references('id')->on('societe')->onDelete('cascade');
            
            // Ajouter une clé étrangère pour le dossier parent (auto-référence)
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
