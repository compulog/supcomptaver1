<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDossiersTable extends Migration
{
/**
 * Run the migrations.
 *
 * This method creates the 'dossiers' table with the following columns:
 * - id: Primary key
 * - name: Name of the dossier
 * - societe_id: Foreign key referencing the 'societes' table
 * - timestamps: Created and updated timestamps
 *
 * It also sets up a foreign key constraint on 'societe_id' to ensure
 * referential integrity with the 'societes' table, cascading on delete.
 */

    public function up()
    {
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du dossier
            $table->unsignedBigInteger('societe_id'); // ID de la société
            $table->timestamps();

            // Relation avec la table sociétés
            $table->foreign('societe_id')->references('id')->on('societes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dossiers');
    }
}
