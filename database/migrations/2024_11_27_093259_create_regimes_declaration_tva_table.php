<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegimesDeclarationTvaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regimes_declaration_tva', function (Blueprint $table) {
            $table->id(); // Ajoute une clé primaire auto-incrémentée
            $table->string('numero', 191)->unique();
            $table->text('description')->nullable(); // Description du régime de déclaration (facultatif)
            $table->timestamps(); // Créera les champs created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regimes_declaration_tva');
    }
}
