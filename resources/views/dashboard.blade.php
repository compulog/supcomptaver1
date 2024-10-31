<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau des Sociétés</title>

    <!-- Tabulator CSS -->
    <link href="https://unpkg.com/tabulator-tables@5.3.2/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://unpkg.com/tabulator-tables@5.3.2/dist/js/tabulator.min.js"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<link href="https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">
<script src="https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js"></script>

    <style>
        body {
            background-color: #f9f9f9; 
            color: #333;
        }
    </style>
</head>
<body>

@extends('layouts.user_type.auth')

@section('content')

<h2>Liste des Sociétés</h2>
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <div>
                        <h5 class="mb-0">Sociétés</h5>
                    </div>
                    <button type="button" class="btn bg-gradient-primary btn-sm mb-0" id="open-modal-btn">+&nbsp; Nouvelle société</button>
                    <button id="import-societes" class="btn bg-gradient-primary btn-sm mb-0" >Importer Sociétés</button>
                </div><br>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <div id="societes-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une nouvelle société -->
<div class="modal fade" id="nouvelleSocieteModal" tabindex="-1" aria-labelledby="nouvelleSocieteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nouvelleSocieteModalLabel">Nouvelle Société</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="societe-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="raison_sociale" class="form-label">Raison sociale</label>
                            <input type="text" class="form-control" name="raison_sociale" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="forme_juridique" class="form-label">Forme Juridique</label>
                            <input type="text" class="form-control" name="forme_juridique" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="siege_social" class="form-label">Siège Social</label>
                            <input type="text" class="form-control" name="siege_social" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="patente" class="form-label">Patente</label>
                            <input type="text" class="form-control" name="patente" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rc" class="form-label">RC</label>
                            <input type="text" class="form-control" name="rc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="centre_rc" class="form-label">Centre RC</label>
                            <input type="text" class="form-control" name="centre_rc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" class="form-control" name="identifiant_fiscal" required id="identifiant_fiscal" maxlength="8" title="Veuillez entrer uniquement des chiffres (max 8 chiffres)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ice" class="form-label">ICE</label>
                            <input type="text" id="ice" class="form-control" name="ice" required maxlength="15" title="Veuillez entrer uniquement des chiffres (max 15 chiffres)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_creation" class="form-label">Date de Création</label>
                            <input type="date" class="form-control" name="date_creation" required>
                        </div>
                        <div class="col-md-6 mb-3 d-flex">
                            <div class="me-2" style="flex: 1;">
                                <label for="exercice_social_debut" class="form-label">Exercice Social Début</label>
                                <input type="date" name="exercice_social_debut" class="form-control" required>
                            </div>
                            <div style="flex: 1;">
                                <label for="exercice_social_fin" class="form-label">Exercice Social Fin</label>
                                <input type="date" name="exercice_social_fin" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nature_activite" class="form-label">Nature de l'Activité</label>
                            <select class="form-control" name="nature_activite">
                                <option value="">Choisir une activité</option>
                                <option value="Vente de biens d'équipement">Vente de biens d'équipement</option>
                                <option value="Vente de travaux">Vente de travaux</option>
                                <option value="Vente de services">Vente de services</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="activite" class="form-label">Activité</label>
                            <input type="text" class="form-control" name="activite" required>
                        </div>

                     



                        <div class="col-md-6 mb-3">
                            <label for="assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <select class="form-control" name="assujettie_partielle_tva" id="assujettie_partielle_tva" required>
                                <option value="" disabled selected>Choisir une option</option>
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="text" class="form-control" name="prorata_de_deduction" id="prorata_de_deduction" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="regime_declaration" class="form-label">Régime de Déclaration</label>
                            <input type="text" class="form-control" name="regime_declaration" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fait_generateur" class="form-label">Fait Générateur</label>
                            <input type="date" class="form-control" name="fait_generateur" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="rubrique_tva" class="form-label">Rubrique TVA</label>
                            <input type="text" class="form-control" name="rubrique_tva" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="designation" class="form-label">Désignation</label>
                            <input type="text" class="form-control" name="designation" required>
                        </div>

                     
                        <div class="col-md-6 mb-3">
                            <label for="nombre_chiffre_compte" class="form-label">Nombre caractères  Compte</label>
                            <input type="number" class="form-control" name="nombre_chiffre_compte" required>
                        </div>
                        
                        
                       
                   
                        <div class="col-md-6 mb-3">
                            <label for="modele_comptable" class="form-label">Modèle Comptable</label>
                            <input type="text" class="form-control" name="modele_comptable" required>
                        </div>
                    </div>
                    <button type="reset" class="btn btn-secondary me-2">Réinitialiser</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour importer des sociétés -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Importer Sociétés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulaire d'Importation -->
                <form id="import-societe-form" action="{{ route('societes.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="import_file" class="form-label">Choisir le fichier d'importation</label>
                        <input type="file" class="form-control" id="import_file" name="file" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="import_raison_sociale" class="form-label">Raison Sociale</label>
                            <input type="number" class="form-control" id="import_raison_sociale" name="raison_sociale" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_siège_social" class="form-label">Siège Social</label>
                            <input type="number" class="form-control" id="import_siège_social" name="siege_social">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_ice" class="form-label">ICE</label>
                            <input type="number" class="form-control" id="import_ice" name="ice" required maxlength="15" title="Veuillez entrer uniquement des chiffres (max 15 chiffres)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_rc" class="form-label">RC</label>
                            <input type="number" class="form-control" id="import_rc" name="rc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="number" class="form-control" id="import_identifiant_fiscal" name="identifiant_fiscal" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_patente" class="form-label">Patente</label>
                            <input type="number" class="form-control" id="import_patente" name="patente">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_centre_rc" class="form-label">Centre RC</label>
                            <input type="number" class="form-control" id="import_centre_rc" name="centre_rc">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_forme_juridique" class="form-label">Forme Juridique</label>
                            <input type="number" class="form-control" id="import_forme_juridique" name="forme_juridique">
                        </div>
                        <div class="col-md-6 mb-3 d-flex">
                            <div class="me-2" style="flex: 1;">
                                <label for="import_exercice_social_debut" class="form-label">Exercice Social Début</label>
                                <input type="number" class="form-control" id="import_exercice_social_debut" name="exercice_social_debut">
                            </div>
                            <div style="flex: 1;">
                                <label for="import_exercice_social_fin" class="form-label">Exercice Social Fin</label>
                                <input type="number" class="form-control" id="import_exercice_social_fin" name="exercice_social_fin">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_date_creation" class="form-label">Date de Création</label>
                            <input type="number" class="form-control" id="import_date_creation" name="date_creation">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <input type="number" class="form-control" id="import_assujettie_partielle_tva" name="assujettie_partielle_tva">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="number" class="form-control" id="import_prorata_de_deduction" name="prorata_de_deduction">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_nature_activite" class="form-label">Nature d'Activité</label>
                            <input type="number" class="form-control" id="import_nature_activite" name="nature_activite">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_activite" class="form-label">Activité</label>
                            <input type="number" class="form-control" id="import_activite" name="activite">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_regime_declaration" class="form-label">Régime de Déclaration</label>
                            <input type="number" class="form-control" id="import_regime_declaration" name="regime_declaration">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_fait_generateur" class="form-label">Fait Générateur</label>
                            <input type="number" class="form-control" id="import_fait_generateur" name="fait_generateur">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_rubrique_tva" class="form-label">Rubrique TVA</label>
                            <input type="number" class="form-control" id="import_rubrique_tva" name="rubrique_tva">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_designation" class="form-label">Désignation</label>
                            <input type="number" class="form-control" id="import_designation" name="designation">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_nombre_chiffre_compte" class="form-label">Nombre caractères Compte</label>
                            <input type="number" class="form-control" id="import_nombre_chiffre_compte" name="nombre_chiffre_compte">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_model_comptable" class="form-label">Modèle Comptable</label>
                            <input type="number" class="form-control" id="import_model_comptable" name="modele_comptable" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal Modifier Société -->
<div class="modal fade" id="modifierSocieteModal" tabindex="-1" aria-labelledby="modifierSocieteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifierSocieteModalLabel">Modifier Société</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form id="societe-modification-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="modification_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mod_raison_sociale" class="form-label">Raison Sociale</label>
                            <input type="text" class="form-control" id="mod_raison_sociale" name="raison_sociale" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_siège_social" class="form-label">Siège Social</label>
                            <input type="text" class="form-control" id="mod_siège_social" name="siege_social">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_ice" class="form-label">ICE</label>
                            <input type="text" class="form-control" id="mod_ice" name="ice" required maxlength="15" title="Veuillez entrer uniquement des chiffres (max 15 chiffres)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_rc" class="form-label">RC</label>
                            <input type="text" class="form-control" id="mod_rc" name="rc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" class="form-control" id="mod_identifiant_fiscal" name="identifiant_fiscal" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_patente" class="form-label">Patente</label>
                            <input type="text" class="form-control" id="mod_patente" name="patente">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_centre_rc" class="form-label">Centre RC</label>
                            <input type="text" class="form-control" id="mod_centre_rc" name="centre_rc">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_forme_juridique" class="form-label">Forme Juridique</label>
                            <input type="text" class="form-control" id="mod_forme_juridique" name="forme_juridique">
                        </div>
                        <div class="col-md-6 mb-3 d-flex">
                            <div class="me-2" style="flex: 1;">
                                <label for="mod_exercice_social_debut" class="form-label">Exercice Social Début</label>
                                <input type="date" class="form-control" id="mod_exercice_social_debut" name="exercice_social_debut">
                            </div>
                            <div style="flex: 1;">
                                <label for="mod_exercice_social_fin" class="form-label">Exercice Social Fin</label>
                                <input type="date" class="form-control" id="mod_exercice_social_fin" name="exercice_social_fin">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_date_creation" class="form-label">Date de Création</label>
                            <input type="date" class="form-control" id="mod_date_creation" name="date_creation">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <input type="text" class="form-control" id="mod_assujettie_partielle_tva" name="assujettie_partielle_tva">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="text" class="form-control" id="mod_prorata_de_deduction" name="prorata_de_deduction">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_nature_activite" class="form-label">Nature d'Activité</label>
                            <input type="text" class="form-control" id="mod_nature_activite" name="nature_activite">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_activite" class="form-label">Activité</label>
                            <input type="text" class="form-control" id="mod_activite" name="activite">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_regime_declaration" class="form-label">Régime de Déclaration</label>
                            <input type="text" class="form-control" id="mod_regime_declaration" name="regime_declaration">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_fait_generateur" class="form-label">Fait Générateur</label>
                            <input type="text" class="form-control" id="mod_fait_generateur" name="fait_generateur">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_rubrique_tva" class="form-label">Rubrique TVA</label>
                            <input type="text" class="form-control" id="mod_rubrique_tva" name="rubrique_tva">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_designation" class="form-label">Désignation</label>
                            <input type="text" class="form-control" id="mod_designation" name="designation">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_nombre_chiffre_compte" class="form-label">Nombre caractères Compte</label>
                            <input type="text" class="form-control" id="mod_nombre_chiffre_compte" name="nombre_chiffre_compte">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_model_comptable" class="form-label">Modèle Comptable</label>
                            <input type="text" class="form-control" id="mod_model_comptable" name="modele_comptable" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
 $(document).ready(function() {
    // Événement lors de l'ouverture du modal de modification
    $('#modifierSocieteModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // bouton qui a déclenché le modal
        var societeId = button.data('id'); // récupère l'ID de la société
        var url = '/societes/' + societeId; // URL pour récupérer les données de la société

        // Requête AJAX pour obtenir les données de la société
        $.get(url, function(data) {
            // Remplir le formulaire avec les données de la société
            $('#modification_id').val(data.id);
            $('#mod_raison_sociale').val(data.raison_sociale);
            $('#mod_siège_social').val(data.siege_social);
            $('#mod_ice').val(data.ice);
            $('#mod_rc').val(data.rc);
            $('#mod_identifiant_fiscal').val(data.identifiant_fiscal);
            $('#mod_patente').val(data.patente);
            $('#mod_centre_rc').val(data.centre_rc);
            $('#mod_forme_juridique').val(data.forme_juridique);
            $('#mod_exercice_social_debut').val(data.exercice_social_debut);
            $('#mod_exercice_social_fin').val(data.exercice_social_fin);
            $('#mod_date_creation').val(data.date_creation);
            $('#mod_assujettie_partielle_tva').val(data.assujettie_partielle_tva);
            $('#mod_prorata_de_deduction').val(data.prorata_de_deduction);
            $('#mod_nature_activite').val(data.nature_activite);
            $('#mod_activite').val(data.activite);
            $('#mod_regime_declaration').val(data.regime_declaration);
            $('#mod_fait_generateur').val(data.fait_generateur);
            $('#mod_rubrique_tva').val(data.rubrique_tva);
            $('#mod_designation').val(data.designation);
            $('#mod_nombre_chiffre_compte').val(data.nombre_chiffre_compte);
            $('#mod_model_comptable').val(data.modele_comptable);
        });
    });

    // Événement lors de la soumission du formulaire
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
                // Recharger la page
                location.reload(); // Recharger la page après la mise à jour
            },
            error: function(xhr) {
                // Gérer les erreurs
                alert('Une erreur s\'est produite lors de la mise à jour de la société.');
            }
        });
    });
});


</script>
<!-- Table Tabulator -->

<div id="societes-table"></div>

<!-- Tabulator JS -->
<script>
    // Assigner les données des sociétés à une variable JS depuis PHP
    var societes = {!! $societes !!};

    // Initialiser Tabulator avec les données
    var table = new Tabulator("#societes-table", {
        data: societes, // Charger les données passées depuis le contrôleur
        layout: "fitColumns", // Ajuster les colonnes à la largeur du tableau
        columns: [
            {title: "Raison Sociale", field: "raison_sociale", formatter: function(cell, formatterParams, onRendered){
                var nomEntreprise = cell.getData()["raison_sociale"];
                var formeJuridique = cell.getData().forme_juridique;
                return nomEntreprise + " " + formeJuridique;
            }, headerFilter: true},
            {title: "ICE", field: "ice", headerFilter: true},
            {title: "RC", field: "rc", headerFilter: true},
            {title: "Identifiant Fiscal", field: "identifiant_fiscal", headerFilter: true},
            {
                title: "Exercice Social",
                field: "exercice_social",
                headerFilter: true,
                formatter: function(cell) {
                    const rowData = cell.getRow().getData(); // Obtenir les données de la ligne
                    return `Du ${rowData.exercice_social_debut} au ${rowData.exercice_social_fin}`; // Formater les dates
                },
            },
            {
                title: "Actions",
                formatter: function(cell, formatterParams) {
                    return "<div class='action-icons'>" +
                        "<a href='#' class='text-primary mx-1' data-bs-toggle='modal' data-bs-target='#modifierSocieteModal' " +
                        "data-id='" + cell.getRow().getData().id + "' " +
                        "data-nom-entreprise='" + cell.getRow().getData().raison_sociale + "' " +
                        "data-ice='" + cell.getRow().getData().ice + "' " +
                        "data-rc='" + cell.getRow().getData().rc + "' " +
                        "data-identifiant-fiscal='" + cell.getRow().getData().identifiant_fiscal + "'>" +
                        "<i class='fas fa-edit'></i></a>" +
                        "<a href='#' class='text-danger mx-1 delete-icon' data-id='" + cell.getRow().getData().id + "'>" +
                        "<i class='fas fa-trash'></i></a>" +
                        "</div>";
                },
                width: 150,
                hozAlign: "center"
            }
        ],
    });

   // Écouteur d'événement pour le double clic sur une ligne du tableau
   table.on("rowDblClick", function(row) {
        var societeId = row.getData().id; // Récupérer l'ID de la société
        window.location.href = `/exercice/${societeId}`; // Rediriger vers la vue "exercice"
    });



    // Ouvrir le modal au clic sur le bouton
    document.getElementById('open-modal-btn').addEventListener('click', function() {
        var myModal = new bootstrap.Modal(document.getElementById('nouvelleSocieteModal'));
        myModal.show();
    });

    // Ajouter une société via Ajax sans rafraîchir la page
    document.getElementById('societe-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Empêcher l'envoi classique du formulaire

        let formData = new FormData(this);

        // Envoi Ajax
        fetch("{{ route('societes.store') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Ajouter la nouvelle société à la table Tabulator
                table.addData([data.societe]);
                // Réinitialiser le formulaire après l'ajout
                document.getElementById('societe-form').reset();
                // Fermer le modal
                var myModal = bootstrap.Modal.getInstance(document.getElementById('nouvelleSocieteModal'));
                myModal.hide();
            } else {
                alert("Erreur lors de l'ajout de la société : " + data.message);
            }
        })
        .catch(error => console.error("Erreur :", error));
    });

    // Gestion de la suppression d'une société
    document.getElementById('societes-table').addEventListener('click', function(e) {
        if (e.target.closest('.delete-icon')) {
            const id = e.target.closest('.delete-icon').getAttribute('data-id');

            if (confirm("Êtes-vous sûr de vouloir supprimer cette société ?")) {
                fetch("{{ url('societes') }}/" + id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                    }
                    
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        table.deleteRow(id); // Supprimer la ligne du tableau
                        
                        alert("Société supprimée avec succès.");
                       
                    } else {
                        alert("Erreur lors de la suppression : " + data.message);
                    }
                })
                .catch(error => console.error("Erreur :", error));
                
            }
        }
    });

// Gestion du changement de la valeur "Assujettie partielle à la TVA"
document.getElementById('assujettie_partielle_tva').addEventListener('change', function() {
    var prorataField = document.getElementById('prorata_de_deduction');
    
    if (this.value === "0") {
        prorataField.value = "100"; // Mettre la valeur à 10
        prorataField.setAttribute("readonly", true); // Rendre le champ non modifiable
    } else {
        prorataField.removeAttribute("readonly"); // Rendre le champ modifiable
        prorataField.value = ""; // Réinitialiser le champ si nécessaire
    }
});
 
</script>


<script>
    document.getElementById("identifiant_fiscal").addEventListener("input", function() {
    // Remplace tous les caractères non numériques par une chaîne vide
    this.value = this.value.replace(/\D/g, '');

    // Limite la longueur à 8 chiffres
    if (this.value.length > 8) {
        this.value = this.value.slice(0, 8);
    }
});

</script>

<script>
    document.getElementById("ice").addEventListener("input", function() {
    // Remplace tous les caractères non numériques par une chaîne vide
    this.value = this.value.replace(/\D/g, '');

    // Limite la longueur à 15 chiffres
    if (this.value.length > 15) {
        this.value = this.value.slice(0, 15);
    }
});

</script>

<script>
$(function() {
  $('#exercice_social').daterangepicker({
    opens: 'left',
    startDate: moment('2018-01-01'), // Date de début par défaut
    endDate: moment('2019-01-15'), // Date de fin par défaut
    locale: {
      format: 'YYYY-MM-DD'
    },
    // Permet de choisir une plage de dates
    singleDatePicker: false,
    showDropdowns: true,
    autoUpdateInput: true
  }, function(start, end) {
    // Met à jour le champ d'entrée avec les dates sélectionnées
    $('#exercice_social').val(start.format('YYYY-MM-DD') + ' au ' + end.format('YYYY-MM-DD'));
  });
});
</script>
<script>
    document.getElementById('import-societes').addEventListener('click', function() {
    // Logique d'importation, par exemple, ouvrir un modal
    openImportModal();
});

function openImportModal() {
    // Code pour afficher le modal d'importation 
    $('#importModal').modal('show'); // Utiliser Bootstrap modal si vous l'avez
}

</script>


<script>

//     // Écouter l'événement de clic sur les icônes de suppression
// document.addEventListener("click", function(e) {
//     if (e.target && e.target.closest(".delete-icon")) {
//         // Récupérer l'ID de la société à partir de l'attribut data-id
//         var societeId = e.target.closest(".delete-icon").getAttribute("data-id");

//         // Demander confirmation avant de supprimer
//         if (confirm("Êtes-vous sûr de vouloir supprimer cette société ?")) {
//             // Appeler la fonction pour supprimer la société
//             deleteSociete(societeId);
            
//         }
//     }
    
// });

</script>

<script>
function ouvrirModalModifierSociete(societeId) {
    $.ajax({
        url: `/societes/${societeId}/edit`,
        type: 'GET',
        success: function(societe) {
            // Remplir les champs du formulaire avec les données de la société
            $('#mod_societe_id').val(societe.id);
            $('#mod_raison_sociale').val(societe.raison_sociale);
            $('#mod_siège_social').val(societe.siege_social); // Ajout du champ Siège Social
            $('#mod_ice').val(societe.ice);
            $('#mod_rc').val(societe.rc);
            $('#mod_identifiant_fiscal').val(societe.identifiant_fiscal);
            $('#mod_patente').val(societe.patente);
            $('#mod_centre_rc').val(societe.centre_rc);
            $('#mod_forme_juridique').val(societe.forme_juridique);
            $('#mod_exercice_social_debut').val(societe.exercice_social_debut);
            $('#mod_exercice_social_fin').val(societe.exercice_social_fin);
            $('#mod_date_creation').val(societe.date_creation);
            $('#mod_assujettie_partielle_tva').val(societe.assujettie_partielle_tva);
            $('#mod_prorata_de_deduction').val(societe.prorata_de_deduction);
            $('#mod_nature_activite').val(societe.nature_activite);
            $('#mod_activite').val(societe.activite);
            $('#mod_regime_declaration').val(societe.regime_declaration);
            $('#mod_fait_generateur').val(societe.fait_generateur);
            $('#mod_rubrique_tva').val(societe.rubrique_tva);
            $('#mod_designation').val(societe.designation);
            $('#mod_nombre_chiffre_compte').val(societe.nombre_chiffre_compte);
            $('#mod_model_comptable').val(societe.modele_comptable);

            // Affiche le modal
            $('#modifierSocieteModal').modal('show');
        },
        error: function(error) {
            console.log("Erreur lors de la récupération des données de la société :", error);
        }
    });
}

// Écouteur d'événement pour le modal de modification
$(document).on('click', '.text-primary', function() {
    var societeId = $(this).data('id'); // Récupère l'ID de la société
    ouvrirModalModifierSociete(societeId);
});

// Soumettre le formulaire
$('#modifierSocieteForm').on('submit', function(e) {
    e.preventDefault(); // Empêche le rechargement de la page

    var societeId = $('#mod_societe_id').val();
    var formData = $(this).serialize(); // Récupère les données du formulaire

    $.ajax({
        url: `/societes/${societeId}`,
        type: 'PUT',
        data: formData,
        success: function(response) {
            // Mettre à jour les données dans le tableau Tabulator
            table.updateOrAddData([response]);

            // Fermer le modal
            $('#modifierSocieteModal').modal('hide');
        },
        error: function(error) {
            console.log("Erreur lors de la modification de la société :", error);
        }
    });
});






 
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('import-societe-form');

    form.addEventListener('submit', function (e) {
        e.preventDefault(); // Empêche le rechargement de la page

        const formData = new FormData(form);

        fetch('{{ route("societes.import") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}', // Pour Laravel
            }
        })
        .then(response => response.json())
        .then(data => {
            // Affiche le message de succès ou d'erreur
            if (data.success) {
                alert(data.message);
                // Ferme le modal
                $('#importModal').modal('hide');
                // Réinitialiser le formulaire si nécessaire
                form.reset();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de l\'importation.');
        });
    });
});



form.addEventListener('submit', function (e) {
    const fileInput = document.getElementById('import_file');
    const filePath = fileInput.value;

    // Vérifier le format du fichier
    const allowedExtensions = /(\.xlsx|\.xls|\.csv)$/i;
    if (!allowedExtensions.exec(filePath)) {
        alert('Veuillez télécharger un fichier avec une extension .xls, .xlsx ou .csv');
        fileInput.value = ''; // Réinitialiser le champ de fichier
        e.preventDefault();
        return false;
    }
});







</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

@endsection

</body>
</html>
