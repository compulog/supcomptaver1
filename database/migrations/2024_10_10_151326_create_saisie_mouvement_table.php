<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaisieMouvementTable extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        // Créer la table 'saisie_mouvement'
        Schema::connection('supcompta')->create('saisie_mouvement', function (Blueprint $table) {
            $table->id();                              // ID auto-incrémenté
            $table->date('date');                      // Date de la saisie
            $table->string('numero_dossier');           // N° dossier
            $table->string('numero_facture');           // N° facture
            $table->unsignedBigInteger('compte');       // Compte
            $table->string('libelle');                  // Libellé
            $table->decimal('debit', 15, 2)->default(0); // Débit
            $table->decimal('credit', 15, 2)->default(0); // Crédit
            $table->string('contre_partie');            // Contre-Partie
            $table->string('nature_operation');         // Nature de l'opération
            $table->string('rubrique_tva');             // Rubrique TVA
            $table->decimal('taux_tva', 5, 2);          // Taux TVA
            $table->unsignedBigInteger('compte_tva');   // Compte TVA
            $table->string('type');                     // Type de saisie
            $table->timestamps();                      // created_at, updated_at

            // Définir la clé étrangère vers la table 'plan_comptable'
            $table->foreign('compte')->references('id')->on('plan_comptable')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');

            // Définir la clé étrangère vers la table 'fournisseurs'
            $table->foreign('contre_partie')->references('id')->on('fournisseurs')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');

            // Définir la clé étrangère vers la table 'clients'
            $table->foreign('contre_partie')->references('id')->on('clients')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');
            
        });
    
    }

    /**
     * Annuler la migration.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer la table 'saisie_mouvement'
        Schema::connection('supcompta')->dropIfExists('saisie_mouvement');
    }
}
