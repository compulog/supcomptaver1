@extends('layouts.user_type.auth')

@section('content')


  <style>
    #modifierSocieteModal{
        margin-top:20px;
    }
  </style>
<div class="modal fade" id="modifierSocieteModal" tabindex="-1" aria-labelledby="modifierSocieteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifierSocieteModalLabel">Modifier Société</h5>
                <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>
            </div>
            <div class="modal-body">
                <form id="societe-modification-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="modification_id">
                  

                    <!-- Ligne 1 -->
                    <div class="row">

                    <div class="col-md-4 mb-3">
                        <label for="mod_code-societe" class="form-label">Code Société :</label>
                        <input type="text" id="mod_code-societe" name="mod_code-societe" class="form-control" required readOnly>
                    </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_raison_sociale" class="form-label">Raison Sociale</label>
                            <input type="text" class="form-control" id="mod_raison_sociale" name="raison_sociale" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_forme_juridique" class="form-label">Forme Juridique</label>
                            <select class="form-control" id="mod_forme_juridique" name="forme_juridique" required>
                                <option value="SARL">SARL</option>
                                <option value="SARL-AU">SARL-AU</option>
                                <option value="SA">SA</option>
                                <option value="SAS">SAS</option>
                                <option value="SNC">SNC</option>
                                <option value="SCS">SCS</option>
                                <option value="SCI">SCI</option>
                                <option value="SEP">SEP</option>
                                <option value="GIE">GIE</option>
                            </select>
                        </div>
                      
                    </div>

                    <!-- Ligne 2 -->
                    <div class="row">
    <div class="col-md-8 mb-3"> <!-- 2/3 de la largeur -->
        <label for="mod_siège_social" class="form-label">Siège Social</label>
        <input type="text" class="form-control" id="mod_siège_social" required name="siege_social">
    </div>
    <div class="col-md-4 mb-3"> <!-- 1/3 de la largeur -->
        <label for="mod_patente" class="form-label">Patente</label>
        <input type="text" class="form-control" id="mod_patente" required name="patente">
    </div>
</div>

                    <!-- Ligne 3 -->
                    <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="mod_rc" class="form-label">RC</label>
                            <input type="text" class="form-control" id="mod_rc" name="rc" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" class="form-control" id="mod_identifiant_fiscal" name="identifiant_fiscal" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_ice" class="form-label">ICE</label>
                            <input type="text" class="form-control" id="mod_ice" name="ice" required maxlength="15">
                        </div>
                       
                    </div>

                    <!-- Ligne 4 -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="mod_cnss" class="form-label">CNSS</label>
                            <input type="text" class="form-control" name="mod_cnss" id="mod_cnss" maxlength="15" >
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_modele_comptable" class="form-label">Modèle Comptable</label>
                            <select class="form-control" id="mod_modele_comptable" name="modele_comptable" required>
                                <option value="Normal">Normal</option>
                                <option value="Simplifié">Simplifié</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="mod_nombre_chiffre_compte" class="form-label">Nombre caractères Compte</label>
                            <input type="text" class="form-control" id="mod_nombre_chiffre_compte" name="nombre_chiffre_compte">
                        </div>
                       
                    </div>

                    <!-- Ligne 5 -->
                    <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="mod_date_creation" class="form-label">Date de Création</label>
                            <input type="date" class="form-control" id="mod_date_creation" name="date_creation">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_exercice_social_debut" class="form-label">Exercice comptable début</label>
                            <input type="date" class="form-control" id="mod_exercice_social_debut" required name="exercice_social_debut">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_exercice_social_fin" class="form-label">Exercice comptable fin</label>
                            <input type="date" class="form-control" id="mod_exercice_social_fin" required name="exercice_social_fin">
                        </div>
                        
                    </div>

                    <!-- Ligne 6 -->
                    <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="mod_nature_activite" class="form-label">Nature de l'Activité</label>
                            <select class="form-control" id="mod_nature_activite" name="nature_activite">
                                <option value="4.Vente de biens d'équipement"> 4.Vente de biens d'équipement</option>
                                <option value="5.Vente de travaux">5.Vente de travaux</option>
                                <option value="6.Vente de services">6.Vente de services</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <select class="form-control" id="mod_assujettie_partielle_tva" name="assujettie_partielle_tva">
                                <option value="Null">Choisir une option</option>
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="text" class="form-control" id="mod_prorata_de_deduction" name="prorata_de_deduction">
                        </div>
                       
                    </div>

                    <!-- Ligne 7 -->
                    <div class="row">
                    <div class="col-md-4 mb-3">
                            <label for="mod_regime_declaration" class="form-label">Régime de Déclaration de TVA</label>
                            <select class="form-control" id="mod_regime_declaration" name="regime_declaration">
                                <option value="1.Mensuel">1.Mensuel</option>
                                <option value="2.Trimestriel">2.Trimestriel</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mod_fait_generateur" class="form-label">Fait Générateur</label>
                            <select class="form-control" id="mod_fait_generateur" name="fait_generateur">
                                <option value="Encaissement">1.Encaissement</option>
                                <option value="Débit">2.Débit</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editRubriqueTVA">Rubrique TVA</label>
                            <select class="form-control select2" id="editRubriqueTVA" name="rubrique_tva">
                                <!-- Les options seront ajoutées par JavaScript -->
                            </select>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <button type="reset" class="btn btn-secondary me-8">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="submit" class="btn" style="background-color:#007bff;color:white;">Modifier</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="chemin/vers/jquery.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
     openModal();
  });
function openModal() {
    // Réinitialiser le formulaire
    jQuery('#societe-modification-form')[0].reset();

    // Remplir le formulaire avec les données de la société
    jQuery('#modification_id').val({{ session()->get('societeId') }});
    jQuery('#mod_code-societe').val("{{ $societe->code_societe }}");
    jQuery('#mod_cnss').val("{{ $societe->cnss }}");
    jQuery('#mod_raison_sociale').val("{{ $societe->raison_sociale }}");
    jQuery('#mod_siège_social').val("{{ $societe->siege_social }}");
    jQuery('#mod_ice').val("{{ $societe->ice }}");
    jQuery('#mod_rc').val("{{ $societe->rc }}");
    jQuery('#mod_identifiant_fiscal').val("{{ $societe->identifiant_fiscal }}");
    jQuery('#mod_patente').val("{{ $societe->patente }}");
    jQuery('#mod_centre_rc').val("{{ $societe->centre_rc }}");
    jQuery('#mod_forme_juridique').val("{{ $societe->forme_juridique }}");
    jQuery('#mod_date_creation').val("{{ $societe->date_creation }}");
    jQuery('#mod_exercice_social_debut').val("{{ $societe->exercice_social_debut }}");
    jQuery('#mod_exercice_social_fin').val("{{ $societe->exercice_social_fin }}");
    jQuery('#mod_assujettie_partielle_tva').val("{{ $societe->assujettie_partielle_tva }}");
    jQuery('#mod_prorata_de_deduction').val("{{ $societe->prorata_de_deduction }}");
    jQuery('#mod_nature_activite').val("{{ $societe->nature_activite }}");
    jQuery('#mod_activite').val("{{ $societe->activite }}");
    jQuery('#mod_regime_declaration').val("{{ $societe->regime_declaration }}");
    jQuery('#mod_fait_generateur').val("{{ $societe->fait_generateur }}");
    jQuery('#editRubriqueTVA').val("{{ $societe->rubrique_tva }}");
    jQuery('#mod_designation').val("{{ $societe->designation }}");
    jQuery('#mod_nombre_chiffre_compte').val("{{ $societe->nombre_chiffre_compte }}");
    jQuery('#mod_model_comptable').val("{{ $societe->model_comptable }}");

    // Ouvrir le modal après avoir rempli les données
    jQuery('#modifierSocieteModal').modal('show');
}
     $('#societe-modification-form').on('submit', function(e) {
           e.preventDefault(); // Empêche le rechargement de la page
           var formData = $(this).serialize(); // Sérialiser les données du formulaire
           var societeId = $('#modification_id').val(); // ID de la société
   
           // Requête AJAX pour mettre à jour la société
           $.ajax({
               url: '/societes/' + societeId,
               type: 'PUT',
               data: formData,
               success: function(response) {
                   // Fermer le modal
                   $('#modifierSocieteModal').modal('hide');
                   // Recharger la page après la mise à jour
                   location.reload(); // Recharger la page après la mise à jour
               },
               error: function(xhr) {
                   // Gérer les erreurs
                   alert('Une erreur s\'est produite lors de la mise à jour de la société.');
               }
           });
       });
</script>
 
@endsection