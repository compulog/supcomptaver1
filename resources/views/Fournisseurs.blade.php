<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
 <!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<!-- Tabulator CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/css/tabulator.min.css" rel="stylesheet" />

<!-- FontAwesome (pour les icônes) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />

<!-- jQuery (nécessaire pour Select2 et Tabulator) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap 5 JS (chargé après jQuery et Select2) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Select2 JS (après Bootstrap) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<!-- Tabulator JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/js/tabulator.min.js"></script>
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



</head>

    <style>



</style>


</head>


<body>

@extends('layouts.user_type.auth')

@section('content')


@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif


<!-- Conteneur principal -->
<!-- Section principale -->
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary">Liste des Fournisseurs</h3>
    </div>

    <!-- Boutons d'actions -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <!-- Bouton Créer -->
        <button class="btn btn-outline-primary d-flex align-items-center gap-2" id="addFournisseurBtn" data-bs-toggle="modal" data-bs-target="#fournisseurModaladd">
            <i class="bi bi-plus-circle"></i> Créer
        </button>

        <!-- Bouton Importer -->
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2" id="importFournisseurBtn" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-arrow-up"></i> Importer
        </button>

        <!-- Bouton Exporter en Excel -->
        <a href="{{ url('/export-fournisseurs-excel') }}" class="btn btn-outline-success d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-excel"></i> Exporter en Excel
        </a>

        <!-- Bouton Exporter en PDF -->
        <a href="{{ url('/export-fournisseurs-pdf') }}" class="btn btn-outline-danger d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-pdf"></i> Exporter en PDF
        </a>
    </div>

    <!-- Tableau des Fournisseurs -->
    <div id="fournisseur-table" class="border rounded shadow-sm bg-white p-3"></div>
</div>




<!-- Formulaire d'importation Excel -->
<!-- Exemple d'un modal avec des améliorations visuelles et des commentaires -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true" data-bs-animation="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="importModalLabel"><i class="fas fa-upload"></i> Importation des Fournisseurs</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="importForm" action="{{ route('fournisseurs.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">
                    <input type="hidden" name="nombre_chiffre_compte" value="{{ $societe->nombre_chiffre_compte }}">

                    <div class="form-group mb-3">
                        <label for="file" class="form-label"><strong>Fichier Excel</strong></label>
                        <input type="file" class="form-control form-control-lg shadow-sm" name="file" id="file" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_chiffre_compte_display" class="form-label">Nombre de Chiffres du Compte</label>
                            <input type="text" class="form-control form-control-lg shadow-sm" id="nombre_chiffre_compte_display" value="{{ $societe->nombre_chiffre_compte }}" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="colonne_compte" class="form-label">Colonne Compte</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_compte" id="colonne_compte"  value="1"  required>


                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="colonne_intitule" class="form-label">Colonne Intitulé</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_intitule" id="colonne_intitule" value="2"  required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="colonne_identifiant_fiscal" class="form-label">Colonne Identifiant Fiscal</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_identifiant_fiscal" id="colonne_identifiant_fiscal"  value="3"  required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="colonne_ICE" class="form-label">Colonne ICE</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_ICE" id="colonne_ICE"  value="4"  required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="colonne_nature_operation" class="form-label">Colonne Nature d'Opération</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_nature_operation" id="colonne_nature_operation"  value="5"  required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="colonne_rubrique_tva" class="form-label">Colonne Rubrique TVA</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_rubrique_tva" id="colonne_rubrique_tva"  value="6"  required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="colonne_designation" class="form-label">Colonne Désignation</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_designation" id="colonne_designation"  value="7"  required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="colonne_contre_partie" class="form-label">Colonne Contre Partie</label>
                            <input type="number" class="form-control form-control-lg shadow-sm" name="colonne_contre_partie" id="colonne_contre_partie" value="8"  required>
                        </div>
                    </div>


                        <div class="d-flex justify-content-between">
                            <!-- Bouton de réinitialisation -->
                            <button type="button" class="btn btn-secondary mr-2" id="resetModal">
                                <i class="bi bi-arrow-clockwise fs-6"></i> Réinitialiser
                            </button>
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-check"></i> Importer
                        </button>
                        <div id="loadingSpinner" class="d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="mt-4">
    <h5 class="text-danger">Comptes ignorés</h5>
    <table class="table table-striped table-bordered" id="skippedTable" style="display: none;">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Compte</th>
                <th>Raison</th>
            </tr>
        </thead>
        <tbody id="skippedTableBody">
            <!-- Les lignes ignorées seront ajoutées dynamiquement ici -->
        </tbody>
    </table>
</div>


<div id="errorMessages" class="alert alert-danger d-none">
    <ul id="errorList"></ul>
</div>


<!-- Modal add-->
<!-- Modal Ajouter Fournisseur -->
<div class="modal fade" id="fournisseurModaladd" tabindex="-1" aria-labelledby="fournisseurModalLabel" aria-hidden="true" data-bs-animation="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light text-white">
                <h5 class="modal-title" id="fournisseurModalLabel"><i class="fas fa-plus-circle"></i> Créer Fournisseur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="fournisseurFormAdd">
                    @csrf
                    <input type="hidden" id="nombre_chiffre_compte" value="{{ $societe->nombre_chiffre_compte }}">
                    <input type="hidden" id="societe_id" name="societe_id" value="{{ session('societeId') }}">
                    <div class="row">
                        <!-- Champ Compte -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="compte">Compte</label>
                                <div class="d-flex">
                                    <input type="text" class="form-control form-control-sm shadow-sm " id="compte" name="compte" placeholder="4411XXXX" required>
                                    <button type="button" class="btn btn-secondary btn-sm" id="autoIncrementBtn" >
                                        Auto
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Champ Intitulé -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="intitule">Intitulé</label>
                                <input type="text" class="form-control form-control-sm shadow-sm" id="intitule" name="intitule">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Champ Identifiant Fiscal -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="identifiant_fiscal">Identifiant Fiscal</label>
                                <input type="text" class="form-control form-control-sm shadow-sm" id="identifiant_fiscal" name="identifiant_fiscal" maxlength="8" pattern="\d*">
                            </div>
                        </div>
                        <!-- Champ ICE -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ICE">ICE</label>
                                <input type="text" class="form-control form-control-sm shadow-sm" id="ICE" name="ICE" maxlength="15" pattern="\d*">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Nature de l'opération -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nature_operation">Nature de l'opération</label>
                                <select class="form-select form-select-sm shadow-sm" id="nature_operation" name="nature_operation">
                                    <option value="">Sélectionner une option</option>
                                    <option value="1-Achat de biens d'équipement">Achat de biens d'équipement</option>
                                    <option value="2-Achat de travaux">Achat de travaux</option>
                                    <option value="3-Achat de services">Achat de services</option>
                                </select>
                            </div>
                        </div>
                        <!-- Contre Partie -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contre_partie">Contre Partie</label>
                                <select class="form-select2" id="contre_partie" name="contre_partie" required>
                                    <option value="">Sélectionner une contre partie</option>
                                    <option value="ajouter_compte">Ajouter un compte</option>
                                    <!-- Les autres options peuvent être ajoutées ici -->
                                </select>
                                <p class="text-muted mt-1" style="font-size: 0.875rem;">Si vous ne trouvez pas la contrepartie souhaitée, <a href="#" id="ajouterCompteLink">ajoutez une nouvelle contrepartie</a>.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Rubrique TVA -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rubrique_tva">Rubrique TVA</label>
                                <select class="form-select2 form-select2-sm shadow-sm" id="rubrique_tva" name="rubrique_tva">
                                    <option value="" selected>Sélectionner une rubrique</option>
                                </select>
                            </div>
                        </div>
                        <!-- Désignation -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="designation">Désignation</label>
                                <input type="text" class="form-control form-control-sm shadow-sm" id="designation" name="designation" placeholder="Désignation">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary btn-sm" id="resetModal"><i class="bi bi-arrow-clockwise fs-6"></i> Réinitialiser</button>
                        <button type="submit" class="btn btn-primary btn-sm">Valider <i class="bi bi-check-lg"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal Edit -->
<div class="modal fade" id="fournisseurModaledit" tabindex="-1" role="dialog" aria-labelledby="fournisseurModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="fournisseurModalLabel">Modifier un compte</h5>
                <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="fournisseurFormEdit">
                    <input type="hidden" id="editFournisseurId" value="">

                    <!-- Formulaire -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editCompte">Compte</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editCompte" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editIntitule">Intitulé</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editIntitule" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editIdentifiantFiscal">Identifiant Fiscal</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editIdentifiantFiscal" maxlength="8" pattern="\d*">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editICE">ICE</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editICE" maxlength="15" pattern="\d*">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editNatureOperation">Nature de l'opération</label>
                                <select class="form-select form-select-lg shadow-sm" id="editNatureOperation">
                                    <option value="">Sélectionner une option</option>
                                    <option value="1-Achat de biens d'équipement">Achat de biens d'équipement</option>
                                    <option value="2-Achat de travaux">Achat de travaux</option>
                                    <option value="3-Achat de services">Achat de services</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editContrePartie">Contre Partie</label>
                                <select class="form-select form-select-lg shadow-sm select2" id="editContrePartie">
                                    <option value="">Sélectionnez une contre partie</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editRubriqueTVA">Rubrique TVA</label>
                                <select class="form-select form-select-lg shadow-sm" id="editRubriqueTVA">
                                    <option value="">Sélectionnez une Rubrique</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editDesignation">Désignation</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editDesignation" placeholder="Designation">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <!-- Bouton de réinitialisation -->
                        <button type="button" class="btn btn-secondary mr-2" id="resetModal">
                            <i class="bi bi-arrow-clockwise fs-6"></i> Réinitialiser
                        </button>
                        <!-- Bouton de validation -->
                        <button type="submit" class="btn btn-primary ml-2">Valider
                            <i class="bi bi-check-lg bi-2x"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Plan Comptable -->
<div class="modal fade" id="planComptableModalAdd" tabindex="-1" aria-labelledby="planComptableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="planComptableModalLabel">Ajouter un compte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="planComptableFormAdd">
                    <!-- Champs du formulaire -->
                    <div class="form-group ">
                        <input type="hidden" id="nombre_chiffre_compte" value="{{ $societe->nombre_chiffre_compte }}">
                        <input type="hidden" id="societe_id" name="societe_id" value="{{ session('societeId') }}">
                        <label for="compte_add" class="form-label">Compte</label>
                        <input type="text" class="form-control form-control-lg shadow-sm" id="compte_add" name="compte" placeholder="Entrer le numéro du compte">

                    </div>
                    <div class="form-group ">
                        <label for="intitule_add" class="form-label">Intitulé</label>
                        <input type="text" class="form-control form-control-lg shadow-sm" id="intitule_add" name="intitule" placeholder="Entrer l'intitulé">
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
$(document).ready(function () {
    var initialValue = '4411'; // Le préfixe des fournisseurs (ex: '4411')
    var societeId = $('#societe_id').val(); // ID de la société sélectionnée
    var nombreChiffresCompte = parseInt($('#nombre_chiffre_compte').val()); // Nombre de chiffres total du compte, à partir de la société sélectionnée

    // Désactiver la saisie après le nombre de chiffres spécifié
    $('#compte').on('input', function () {
        var currentValue = $(this).val();

        // Si la longueur du champ dépasse la limite définie par nombre_chiffre_compte, couper l'excédent
        if (currentValue.length > nombreChiffresCompte) {
            $(this).val(currentValue.substring(0, nombreChiffresCompte)); // Limite la longueur à nombreChiffresCompte
        }

        // Si l'utilisateur tente de modifier le préfixe (par exemple '4411'), le restaurer
        if (currentValue.substring(0, initialValue.length) !== initialValue) {
            $(this).val(initialValue + currentValue.substring(initialValue.length)); // Remet le préfixe "4411" s'il est modifié
        }
    });

    // Lors de l'ouverture du modal de saisie manuelle, se positionner après le préfixe
    $('#fournisseurModaladd').on('shown.bs.modal', function () {
        var input = $('#compte')[0];
        input.setSelectionRange(initialValue.length, initialValue.length); // Positionner le curseur juste après le préfixe "4411"
        $('#compte').focus(); // Focus sur le champ "compte"
    });

    // Bouton pour générer le prochain compte automatiquement
    $('#autoIncrementBtn').on('click', async function () {
        try {
            const response = await $.ajax({
                url: `/get-next-compte/${societeId}`, // Appel API pour récupérer le prochain compte
                method: 'GET',
                dataType: 'json',
            });

            if (response.success) {
                $('#compte').val(response.nextCompte); // Remplir le champ avec le compte généré
                console.log('Compte généré:', response.nextCompte);
            } else {
                alert(response.message || 'Erreur inconnue lors de la génération du compte.');
            }
        } catch (error) {
            console.error('Erreur lors de l\'appel API:', error);
            alert('Une erreur est survenue lors de la génération du compte.');
        }
    });

    // Générer automatiquement un compte lorsque la société est changée
    $('#societe_id').on('change', async function () {
        societeId = $(this).val(); // Mettre à jour l'ID de la société sélectionnée
        nombreChiffresCompte = parseInt($('#nombre_chiffre_compte').val()); // Mettre à jour la configuration

        // Mettre à jour la longueur maximale du champ "compte"
        $('#compte').attr('maxlength', nombreChiffresCompte);

        try {
            const response = await $.ajax({
                url: `/get-next-compte/${societeId}`, // Appel API pour récupérer le prochain compte
                method: 'GET',
                dataType: 'json',
            });

            if (response.success) {
                $('#compte').val(response.nextCompte); // Remplir le champ avec le compte généré
                console.log('Compte généré pour la nouvelle société:', response.nextCompte);
            } else {
                alert(response.message || 'Erreur lors de la génération du compte pour la nouvelle société.');
            }
        } catch (error) {
            console.error('Erreur lors de l\'appel API:', error);
            alert('Une erreur est survenue lors de la mise à jour du compte.');
        }
    });





// Validation pour le champ ICE
$("#ICE").on("input", function() {
  // Remplacer le contenu du champ par uniquement les chiffres
  this.value = this.value.replace(/[^0-9]/g, '');

  // Limiter la longueur à 15 caractères
  if (this.value.length > 15) {
      this.value = this.value.slice(0, 15);
  }
});

// Validation pour le champ identifiant_fiscal
$("#identifiant_fiscal").on("input", function() {
  // Remplacer le contenu du champ par uniquement les chiffres
  this.value = this.value.replace(/[^0-9]/g, '');

  // Limiter la longueur à 15 caractères
  if (this.value.length > 15) {
      this.value = this.value.slice(0, 15);
  }
});



var table = new Tabulator("#fournisseur-table", {
    ajaxURL: "/fournisseurs/data", // URL pour récupérer les données
    layout: "fitColumns",
    height: "600px", // Hauteur du tableau
    selectable: true, // Permet de sélectionner les lignes
    rowSelection: true,
    initialSort: [ // Tri initial par colonne 'Compte'
        { column: "compte", dir: "asc" }
    ],
    columns: [
        {
            title: `
                <i class="fas fa-check-square" id="selectAllIcon" title="Sélectionner tout" style="cursor: pointer;"></i>
                <i class="fas fa-trash-alt " id="deleteAllIcon" title="Supprimer toutes les lignes sélectionnées" style="cursor: pointer;"></i>
            `,
            field: "select",
            formatter: "rowSelection", // Active la sélection de ligne
            headerSort: false,
            hozAlign: "center",
            width: 60, // Fixe la largeur de la colonne de sélection
            cellClick: function(e, cell) {
                cell.getRow().toggleSelect();  // Basculer la sélection de ligne
            }
        },
        {title: "Compte", field: "compte", editor: "input", headerFilter: "input"},
        {title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input"},
        {title: "Identifiant Fiscal", field: "identifiant_fiscal", editor: "input", headerFilter: "input"},
        {title: "ICE", field: "ICE", editor: "input", headerFilter: "input"},
        {title: "Nature de l'opération", field: "nature_operation", editor: "input", headerFilter: "input"},
        {title: "Rubrique TVA", field: "rubrique_tva", editor: "input", headerFilter: "input"},
        {title: "Désignation", field: "designation", editor: "input", headerFilter: "input"},
        {title: "Contre Partie", field: "contre_partie", editor: "input", headerFilter: "input"},
        {
            title: "Actions",
            field: "action-icons",
            formatter: function() {
                return `
                    <i class='fas fa-edit text-primary edit-icon' style='font-size: 0.9em; cursor: pointer;'></i>
                    <i class='fas fa-trash-alt text-danger delete-icon' style='font-size: 0.9em; cursor: pointer;'></i>
                `;
            },
            cellClick: function(e, cell) {
    var row = cell.getRow();

    // Vérifier quel élément a été cliqué
    if (e.target.classList.contains('row-select-checkbox')) {
        // Synchronise la sélection de la ligne avec l'état de la checkbox
        if (e.target.checked) {
            row.select();
        } else {
            row.deselect();
        }
    } else if (e.target.classList.contains('edit-icon')) {
        var rowData = cell.getRow().getData();

        // Ouvrir directement le modal de modification
        editFournisseur(rowData); // Appel à votre fonction de modification
    } else if (e.target.classList.contains('delete-icon')) {
        var rowData = cell.getRow().getData();
        deleteFournisseur(rowData.id);
    }
},
hozAlign: "center",
headerSort: false

        }
    ],

});

// Fonction pour supprimer les lignes sélectionnées côté serveur
function deleteSelectedRows() {
    var selectedRows = table.getSelectedRows(); // Récupère les lignes sélectionnées
    var idsToDelete = selectedRows.map(function(row) {
        return row.getData().id; // Récupère les IDs des lignes sélectionnées
    });

    // Envoie les IDs au serveur pour suppression
    if (idsToDelete.length > 0) {
        fetch("/fournisseurs/delete-selected", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids: idsToDelete })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Affiche un message de succès
            table.deleteRow(selectedRows); // Supprime les lignes du tableau côté client
        })
        .catch(error => {
            console.error('Erreur de suppression:', error);
            alert('Erreur lors de la suppression des lignes.');
        });
    }
}

// Gestionnaire d'événements pour sélectionner/désélectionner toutes les lignes et supprimer les lignes sélectionnées
document.getElementById("fournisseur-table").addEventListener("click", function(e) {
    if (e.target.id === "selectAllIcon") {
        if (table.getSelectedRows().length === table.getRows().length) {
            table.deselectRow(); // Désélectionner toutes les lignes
        } else {
            table.selectRow(); // Sélectionner toutes les lignes
        }
    }
    if (e.target.id === "deleteAllIcon") {
        deleteSelectedRows(); // Appelle la fonction de suppression pour les lignes sélectionnées
    }
});


// Initialisation globale
var designationValue = ''; // Variable globale pour stocker l'intitulé

// Fonction pour remplir les rubriques TVA
function remplirRubriquesTva(selectId, selectedValue = null) {
    $.ajax({
        url: '/rubriques-tva?type=Achat',
        type: 'GET',
        success: function (data) {
            var select = $("#" + selectId);

            // Réinitialisation de Select2
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2("destroy");
            }
            select.empty();
            select.append(new Option("Sélectionnez une Rubrique", ""));

            // Traitement des catégories et rubriques
            let categoriesArray = [];
            $.each(data.rubriques, function (categorie, rubriques) {
                let categories = categorie.split('/').map(cat => cat.trim());
                let mainCategory = categories[0];
                let subCategory = categories[1] ? categories[1].trim() : '';
                categoriesArray.push({
                    mainCategory: mainCategory,
                    subCategory: subCategory,
                    rubriques: rubriques.rubriques
                });
            });

            // Tri des catégories
            categoriesArray.sort((a, b) => a.mainCategory.localeCompare(b.mainCategory));
            let categoryCounter = 1;
            const excludedNumRacines = [147, 151, 152, 148, 144];

            // Remplissage des options
            $.each(categoriesArray, function (index, categoryObj) {
                let mainCategoryOption = new Option(`${categoryCounter}. ${categoryObj.mainCategory}`, '', true, true);
                mainCategoryOption.className = 'category';
                mainCategoryOption.disabled = true;
                select.append(mainCategoryOption);
                categoryCounter++;

                if (categoryObj.subCategory) {
                    let subCategoryOption = new Option(` ${categoryObj.subCategory}`, '', true, true);
                    subCategoryOption.className = 'subcategory';
                    subCategoryOption.disabled = true;
                    select.append(subCategoryOption);
                }

                categoryObj.rubriques.forEach(function (rubrique) {
                    if (!excludedNumRacines.includes(rubrique.Num_racines)) {
                        let option = new Option(`${rubrique.Num_racines}: ${rubrique.Nom_racines} : ${Math.round(rubrique.Taux)}%`, rubrique.Num_racines);
                        option.setAttribute('data-search-text', `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.mainCategory}`);
                        select.append(option);
                    }
                });
            });

            // Initialisation de Select2
            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true,
                templateResult: function (data) {
                    if (!data.id) return data.text;
                    if ($(data.element).hasClass('category')) {
                        return $('<span style="font-weight: bold;">' + data.text + '</span>');
                    } else if ($(data.element).hasClass('subcategory')) {
                        return $('<span style="font-weight: bold; padding-left: 10px;">' + data.text + '</span>');
                    }
                    return $('<span>' + data.text + '</span>');
                },
                matcher: function (params, data) {
                    if ($.trim(params.term) === '') return data;
                    var searchText = $(data.element).data('search-text');
                    return searchText && searchText.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
                }
            });

            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des rubriques :', textStatus, errorThrown);
        }
    });
}

// Fonction pour remplir les options de contrepartie
function remplirContrePartie(selectId, selectedValue = null, callback = null) {
    $.ajax({
        url: '/comptes',
        type: 'GET',
        success: function (data) {
            var select = $("#" + selectId);

            // Réinitialisation de Select2
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2("destroy");
            }
            select.empty();
            select.append(new Option("Sélectionnez une contre partie", ""));

            // Tri et ajout des comptes
            data.sort((a, b) => a.compte.localeCompare(b.compte));
            data.forEach(function (compte) {
                let option = new Option(`${compte.compte} - ${compte.intitule}`, compte.compte);
                select.append(option);
            });

            // Initialisation de Select2
            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true
            });

            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }

            if (callback) callback();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des comptes :', textStatus, errorThrown);
        }
    });
}

// Gestion de la soumission du formulaire
$("#fournisseurFormAdd").on("submit", function (e) {
    e.preventDefault();
    var designationValue = $('#designation').val();

    if (designationValue === '') {
        var contrePartieIntitule = $('#contre_partie').find('option:selected').text();
        var intitule = contrePartieIntitule.split('-')[1]?.trim();
        if (intitule) {
            $('#designation').val(intitule);
        }
    }

    var compteValue = $("#compte").val();
    var societeId = $("#societe_id").val();

    verifierCompteExistence(compteValue, societeId);
});

// Vérification de l'existence du compte
function verifierCompteExistence(compte, societeId) {
    $.ajax({
        url: "/fournisseurs/verifier-compte",
        type: "POST",
        data: {
            compte: compte,
            societe_id: societeId,
            _token: '{{ csrf_token() }}'
        },
        success: function (response) {
            if (response.exists) {
                alert('Le compte ' + compte + ' existe déjà pour cette société.');
            } else {
                envoyerDonnees();
            }
        },
        error: function (xhr) {
            console.error("Erreur lors de la vérification du compte :", xhr.responseText);
        }
    });
}

// Envoi des données via AJAX
function envoyerDonnees() {
    $.ajax({
        url: "/fournisseurs",
        type: "POST",
        data: {
            compte: $("#compte").val(),
            intitule: $("#intitule").val(),
            identifiant_fiscal: $("#identifiant_fiscal").val(),
            ICE: $("#ICE").val(),
            nature_operation: $("#nature_operation").val(),
            rubrique_tva: $("#rubrique_tva").val(),
            designation: $("#designation").val(),
            contre_partie: $("#contre_partie").val(),
            societe_id: $("#societe_id").val(),
            _token: '{{ csrf_token() }}'
        },
        success: function (response) {
            if (response.success) {
                table.setData("/fournisseurs/data");
                $("#fournisseurModaladd").modal("hide");
                $("#fournisseurFormAdd")[0].reset();
                $('#fournisseurFormAdd select').val('').trigger('change');
            } else {
                alert("Erreur lors de l'ajout du fournisseur.");
            }
        },
        error: function (xhr) {
            console.error("Erreur lors de l'envoi des données:", xhr.responseText);
        }
    });
}

// Initialisation à l'ouverture du modal
$('#fournisseurModaladd').on('shown.bs.modal', function () {
    // Ajout du backdrop
    $('body').append('<div class="modal-backdrop fade show"></div>');

    // Initialisation des champs
    remplirRubriquesTva('rubrique_tva');
    remplirContrePartie('contre_partie');
    $('#compte').focus();
    $('#rubrique_tva').val('').trigger('change');
    $('#designation').val('');
});

// Suppression du backdrop à la fermeture du modal
$('#fournisseurModaladd').on('hidden.bs.modal', function () {
    $('.modal-backdrop').remove();
});

 // Fonction pour obtenir le prochain compte
 function getNextCompte() {
        $.ajax({
            url: '/get-next-compte', // Route vers votre contrôleur
            type: 'GET',
            success: function(data) {
                $('#compte').val(data.compte); // Remplir le champ compte
                $('#intitule').focus(); // Mettre le focus sur le champ intitule
            },
            error: function() {
                alert('Erreur lors de la récupération du compte.');
            }
        });
    }

    $("#fournisseurFormEdit").on("submit", function(e) {
    e.preventDefault(); // Empêche la soumission par défaut du formulaire

    var fournisseurId = $("#editFournisseurId").val();
    var url = "/fournisseurs/" + fournisseurId; // URL pour la modification


    // Vérifier si le champ editDesignation est vide
    if ($("#editDesignation").val().trim() === '') {
        // Remplir editDesignation avec le designationValue
        $("#editDesignation").val(designationValue.trim());
    }

    $.ajax({
        url: url,
        type: "PUT",
        data: {
            compte: $("#editCompte").val(),
            intitule: $("#editIntitule").val(),
            identifiant_fiscal: $("#editIdentifiantFiscal").val(),
            ICE: $("#editICE").val(),
            nature_operation: $("#editNatureOperation").val(),
            rubrique_tva: $("#editRubriqueTVA").val(), // Inclure la valeur sélectionnée dans les données
            designation: $("#editDesignation").val(), // Utiliser la designation remplie
            contre_partie: $("#editContrePartie").val(),
           // societe_id: $("#societe_id").val(), // Ajout de l'ID de la société ici
            _token: '{{ csrf_token() }}' // Assurez-vous que le token CSRF est inclus
        },
        success: function(response) {
            table.setData("/fournisseurs/data"); // Recharger les données
            $("#fournisseurModaledit").modal("hide");
            $("#fournisseurFormEdit")[0].reset(); // Réinitialiser le formulaire de modification
            $("#editFournisseurId").val(""); // Réinitialiser l'ID
            // Remplir de nouveau les rubriques TVA pour le prochain affichage
            remplirRubriquesTva('rubrique_tva');
            remplirContrePartie('contre_partie');
        },
        error: function(xhr) {
            alert("Erreur lors de l'enregistrement des données !");
        }
    });
});



// Fonction pour remplir le formulaire pour la modification
function editFournisseur(data) {
    $("#editFournisseurId").val(data.id);
    $("#editCompte").val(data.compte);
    $("#editIntitule").val(data.intitule);
    $("#editIdentifiantFiscal").val(data.identifiant_fiscal);
    $("#editICE").val(data.ICE);
    $("#editNatureOperation").val(data.nature_operation);

    remplirRubriquesTva('rubrique_tva');
    remplirContrePartie('contre_partie');

    // Remplir la liste déroulante de rubrique TVA avec la valeur actuelle
    remplirRubriquesTva("editRubriqueTVA", data.rubrique_tva);

    $("#editDesignation").val(data.designation);
    $("#editContrePartie").val(data.contre_partie);
    remplirContrePartie("editContrePartie", data.contre_partie);

    $("#fournisseurModaledit").modal("show");
}




$(document).ready(function () {
    const societeId = $("#societe_id").val();

    // Initialiser Select2
    $("#contre_partie").select2({
        width: "100%",
        placeholder: "Sélectionner ou ajouter un compte",
        allowClear: true,
        minimumResultsForSearch: 0,  // Toujours afficher la barre de recherche
        escapeMarkup: function (markup) {
            return markup;  // Pour gérer les caractères HTML
        },
        matcher: function (params, data) {
            // Permet à la recherche de fonctionner même si l'utilisateur ne tape qu'une partie du texte
            return data.text.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
        }
    });

    // Ouvrir le modal lorsque l'utilisateur clique sur le lien "Ajouter un compte"
    $("#ajouterCompteLink").on("click", function (e) {
        e.preventDefault(); // Empêche le comportement par défaut du lien
        // const modal = new bootstrap.Modal(document.getElementById("planComptableModalAdd"));
        // modal.show();
        $('#planComptableModalAdd').modal('show'); // Appel direct de la méthode show


        // Différer légèrement l'ajout du focus avec setTimeout pour s'assurer que le modal est bien ouvert
        // setTimeout(function () {
        //     // Focus sur l'input du compte
        //     $("#compte_add").focus();
        // }, 500);
    });

    // Soumission du formulaire pour ajouter un compte
    $("#planComptableFormAdd").on("submit", function (e) {
        e.preventDefault();
        const compte = $("#compte_add").val();
        const intitule = $("#intitule_add").val();

        if (compte.length !== 10) {
            alert("Le compte doit contenir exactement 10 chiffres.");
            return;
        }
        if (!intitule) {
            alert("Veuillez saisir un intitulé.");
            return;
        }

        $.ajax({
            url: "{{ route('ajouterContrePartie') }}",
            type: "POST",
            data: {
                compte,
                intitule,
                societe_id: societeId,
                _token: "{{ csrf_token() }}",
            },
            success: function (response) {
                if (response.success) {
                    const newOption = new Option(
                        `${response.data.compte} - ${response.data.intitule}`,
                        response.data.compte,
                        true,
                        true
                    );
                    $("#contre_partie").append(newOption).trigger("change");
                    const modal = bootstrap.Modal.getInstance(document.getElementById("planComptableModalAdd"));
                    modal.hide();
                    $("#planComptableFormAdd")[0].reset();
                    alert("Compte ajouté avec succès !");
                } else {
                    alert(response.message || "Erreur lors de l'ajout du compte.");
                }
            },
            error: function (xhr) {
                console.error("Erreur AJAX:", xhr.responseText);
                alert("Une erreur est survenue. Veuillez réessayer.");
            },
        });
    });
});





// excel
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const reader = new FileReader();

    reader.onload = function(event) {
        const data = new Uint8Array(event.target.result);
        const workbook = XLSX.read(data, { type: 'array' });

        const sheetName = workbook.SheetNames[0]; // Sélection de la première feuille
        const worksheet = workbook.Sheets[sheetName];
        const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1 }); // Lecture ligne par ligne

        if (rows.length > 1) {
            const headers = rows[0]; // La première ligne comme en-têtes de colonnes

            // Vérifier si le fichier contient des colonnes suffisantes
            if (headers.length < 8) {
                alert("Le fichier Excel doit contenir au moins 8 colonnes.");
                return;
            }

            // Références vers les sélecteurs pour lier les colonnes avec des champs
            const selectors = {
                compte: document.querySelector('input[name="colonne_compte"]'),
                intitule: document.querySelector('input[name="colonne_intitule"]'),
                identifiantFiscal: document.querySelector('input[name="colonne_identifiant_fiscal"]'),
                ICE: document.querySelector('input[name="colonne_ICE"]'),
                natureOperation: document.querySelector('input[name="colonne_nature_operation"]'),
                rubriqueTva: document.querySelector('input[name="colonne_rubrique_tva"]'),
                designation: document.querySelector('input[name="colonne_designation"]'),
                contrePartie: document.querySelector('input[name="colonne_contre_partie"]'),
            };

            // Envoie du fichier au backend pour récupérer la longueur du compte et les lignes
            const societeId = document.getElementById('societe_id').value; // ID de la société sélectionnée
            fetch('/fournisseurs/import', {
                method: 'POST',
                body: new FormData(document.getElementById('importform')), // Formulaire avec le fichier
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Si nécessaire
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Vérifier la réponse du serveur

                const nombreChiffreCompte = data.nombreChiffreCompte; // Récupérer le nombre de chiffres du compte
                const table = document.getElementById("fournisseur-table").tabulator;

                let importSuccess = false; // Variable pour vérifier si l'importation a réussi

                // Parcourir les lignes du fichier Excel
                rows.slice(1).forEach(row => {
                    let compte = row[selectors.compte.value - 1]; // La colonne du compte fournisseur
                    compte = compte ? compte.trim() : ""; // Enlever les espaces avant et après

                    const compteLength = compte.length;

                    // Vérifier si la longueur du compte est correcte
                    if (compteLength === nombreChiffreCompte) {
                        // Vérifier si le compte existe déjà dans le tableau Tabulator
                        const tableData = table.getData(); // Récupérer les données du tableau Tabulator
                        const compteExists = tableData.some(item => item.compte === compte); // Vérifier si le compte existe déjà dans Tabulator

                        if (compteExists) {
                            // Si le compte existe, mettre à jour
                            table.updateData([row]); // Exemple de mise à jour
                        } else {
                            // Si le compte n'existe pas, l'importer
                            table.addData([row]); // Exemple d'ajout de nouvelles données
                        }

                        importSuccess = true; // L'importation a réussi pour cette ligne
                    }
                });

                // Afficher un message de succès si l'importation a eu lieu
                if (importSuccess) {
                    Swal.fire({
                        title: 'Succès!',
                        text: 'Les comptes ont été importés avec succès.',
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    });
                } else {
                    Swal.fire({
                        title: 'Erreur!',
                        text: 'Aucun compte valide n\'a été importé.',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                }
            })
            .catch(error => {
                console.error('Erreur lors de l\'importation:', error);
            });
        } else {
            alert("Le fichier Excel semble être vide !");
        }
    };

    reader.readAsArrayBuffer(file);
});






document.getElementById('resetModal').addEventListener('click', function () {
    const form = document.getElementById('importForm');
    form.reset(); // Réinitialise tous les champs du formulaire
});




  // Fonction pour supprimer un fournisseur
  function deleteFournisseur(id) {
    // Demande de confirmation
    if (confirm("Êtes-vous sûr de vouloir supprimer ce fournisseur ?")) {
        // Appel à la route de suppression
        $.ajax({
            url: "/fournisseurs/" + id,
            type: "DELETE",
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                table.setData("/fournisseurs/data"); // Recharger les données
            },
            error: function(xhr) {
                alert("Erreur lors de la suppression des données !");
            }
        });
    }
}




});

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>





</body>

</html>

@endsection
