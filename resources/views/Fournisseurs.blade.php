<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="societe-id" content="{{ session('societeId') }}">

  <!-- Liens CSS et JS externes -->
   <!-- Bootstrap CSS -->
   <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

   <!-- Font Awesome -->
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

   <!-- Select2 CSS -->
   {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
   <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" /> --}}
   <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


   <!-- Tabulator CSS -->
   <link href="https://unpkg.com/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">

   <!-- Icônes Bootstrap -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* FIX align bouton +/- à côté du select (inline) */
#rubriqueTvaRowsContainer,
#editRubriqueTvaRowsContainer {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

/* Chaque groupe occupe la moitié de la zone (deux par ligne) */
.rubrique-tva-group {
  display: flex;
  align-items: center;
  gap: 8px;
  width: calc(50% - 6px); /* deux par ligne */
  box-sizing: border-box;
  margin-bottom: 6px;
  min-width: 0; /* important pour flex shrink */
}

/* Le select prend l'espace restant, sans forcer width:100% (permet de rester à côté du bouton) */
.rubrique-tva-select,
.rubrique-tva-group .form-select,
.rubrique-tva-group .select2-container {
  flex: 1 1 auto;
  min-width: 0;         /* autorise le shrink dans le flex container */
  width: auto !important; /* override select2 inline width */
}

/* container actions (boutons) prend sa taille naturelle, n'arrive pas à la ligne */
.rubrique-tva-actions {
  flex: 0 0 auto;
  display: flex;
  gap: 6px;
  align-items: center;
  white-space: nowrap;
}

/* boutons petits et centrés verticalement */
.rubrique-tva-actions .btn {
  padding: .25rem .45rem;
  font-size: .85rem;
  line-height: 1;
}

/* responsive : une colonne sur petit écran */
@media (max-width: 576px) {
  .rubrique-tva-group { width: 100%; }
}

</style>

    <style>
.invalid-row {
            background-color: rgba(228, 20, 20, 0.453)!important; /* Rouge clair */
            color: black!important; /* Texte rouge foncé */
        }
      /* Custom styling for small action buttons */
      .action-btn {
            font-weight: bold;
            font-size: 1.25rem;
            line-height: 1;
            padding: 0 8px;
            margin-left: 8px;
            height: 38px; /* align with select height */
            flex-shrink: 0;
        }
        .rubrique-tva-group {
            position: relative;
        }
        .action-btn-remove {
            color: #dc3545;
            border-color: #dc3545;
        }
           @keyframes blink {
            0%, 100% { opacity: 1; }
            50%      { opacity: 0; }
        }
        .blink {
            animation: blink 0.5s step-start infinite;
        }
  </style>


</head>


<body>
 <!-- jQuery -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
 <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
 <!-- Chargement de Select2 JS -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Select2 JS
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}-->

    <!-- Tabulator JS -->
    <script src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>


@extends('layouts.user_type.auth')

@section('content')

{{-- Affichage du flash message --}}
@if(session('success'))
    <div id="flash-message" class="alert alert-success">
        {{ session('success') }}
    </div>
 <script>
        document.addEventListener('DOMContentLoaded', function() {
            const flash = document.getElementById('flash-message');
            if (!flash) return;

            // Ajouter la classe de clignotement
            flash.classList.add('blink');

            // Après 2 s, arrêter le clignotement et masquer l’alerte
            setTimeout(() => {
                flash.classList.remove('blink');
                // Option 1 : masquer complètement
                flash.style.display = 'none';
                // Option 2 : si tu préfères un fondu, décommente :
                // flash.style.transition = 'opacity 0.5s';
                // flash.style.opacity = '0';
            }, 2000);
        });
    </script>
    {{-- CSS pour l’animation de clignotement --}}


    {{-- JS pour lancer le clignotement et cacher après 2 s --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const flash = document.getElementById('flash-message');
            if (!flash) return;

            // Ajouter la classe de clignotement
            flash.classList.add('blink');

            // Après 2 s, arrêter le clignotement et masquer l’alerte
            setTimeout(() => {
                flash.classList.remove('blink');
                // Option 1 : masquer complètement
                flash.style.display = 'none';
                // Option 2 : si tu préfères un fondu, décommente :
                // flash.style.transition = 'opacity 0.5s';
                // flash.style.opacity = '0';
            }, 2000);
        });
    </script>

     <!-- Initialisation des tooltips Bootstrap -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>
@endif





{{-- @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif --}}

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<br>
<div class="container my-3">
    <!-- Ligne de titre et actions -->
    <div class="row align-items-center mb-2">
      <div class="col-md-6">
        <h4 class="text-secondary mb-0">Liste des Fournisseurs</h4>
      </div>
      <div class="col-md-6 text-end">
        <div class="btn-group" role="group" aria-label="Actions">
          <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                  id="addFournisseurBtn"
                  data-bs-toggle="modal"
                  data-bs-target="#fournisseurModaladd"
                  data-bs-toggle="tooltip"
                  data-bs-placement="top"
                  title="Créer">
            <i class="bi bi-plus-circle icon-3d"></i>
            <span>Créer</span>
          </button>
          <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
                  id="importFournisseurBtn"
                  data-bs-toggle="modal"
                  data-bs-target="#importModal"
                  data-bs-toggle="tooltip"
                  data-bs-placement="top"
                  title="Importer">
            <i class="bi bi-file-earmark-arrow-up icon-3d"></i>
            <span>Importer</span>
          </button>
          <a href="{{ url('/export-fournisseurs-excel') }}"
             class="btn btn-outline-success btn-sm d-flex align-items-center gap-1"
             data-bs-toggle="tooltip"
             data-bs-placement="top"
             title="Exporter en Excel">
            <i class="bi bi-file-earmark-excel icon-3d"></i>
            <span>Excel</span>
          </a>
          <a href="{{ url('/export-fournisseurs-pdf') }}"
             class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1"
             data-bs-toggle="tooltip"
             data-bs-placement="top"
             title="Exporter en PDF">
            <i class="bi bi-file-earmark-pdf icon-3d"></i>
            <span>PDF</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Liste des fournisseurs dans une carte avec taille réduite -->
    <div class="card shadow-sm">
      <div class="card-body p-2" style="font-size: 0.8rem;">
        <div id="fournisseur-table" class="border rounded bg-white p-2"></div>
      </div>
    </div>
  </div>

  <!-- Styles personnalisés pour l'effet 3D -->
  <style>
    .icon-3d {
      font-size: 1.2rem;
      transition: transform 0.2s, box-shadow 0.2s;
      /* Effet d'ombre initial */
      box-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }
    .icon-3d:hover {
      transform: translateY(-2px);
      box-shadow: 3px 3px 6px rgba(0,0,0,0.4);
    }
  </style>

  <!-- Initialisation des tooltips Bootstrap -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>




<div>
    <span style="background-color: rgba(233,233,13,0.838); display:inline-block; width:20px; height:20px; border:1px solid black; border-radius:4px;"></span>
    Informations Obligatoires Manquantes
</div>
<div>
    <span style="background-color: rgba(228,20,20,0.453); display:inline-block; width:20px; height:20px; border:1px solid black; border-radius:4px;"></span>
    Informations Erronées
</div>


<!-- Formulaire d'importation Excel -->
<!-- Exemple d'un modal avec des améliorations visuelles et des commentaires -->
<!-- Modal with selects for column mapping -->
<!-- Modal Import Fournisseurs -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true" data-bs-animation="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="importModalLabel">
          <i class="fas fa-upload"></i> Importation des Fournisseurs
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>

      <div class="modal-body bg-light">
        <form id="importForm" action="{{ route('fournisseurs.import') }}" method="POST" enctype="multipart/form-data" novalidate>
          @csrf
          <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">
          <input type="hidden" name="nombre_chiffre_compte" id="nombre_chiffre_compte" value="{{ $societe->nombre_chiffre_compte }}">

          <!-- Fichier -->
          <div class="mb-3">
            <label for="file" class="form-label"><strong>Fichier Excel</strong></label>
            <input type="file" class="form-control form-control-lg shadow-sm" name="file" id="file" accept=".xlsx, .xls, .csv" required>
          </div>

          <!-- Ligne 1 : Compte | Intitulé -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="colonne_compte" class="form-label">Colonne Compte</label>
              <select class="form-select form-select-lg shadow-sm" name="colonne_compte" id="colonne_compte" required>
                <option value="" disabled selected>Sélectionnez une colonne</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label for="colonne_intitule" class="form-label">Colonne Intitulé</label>
              <select class="form-select form-select-lg shadow-sm" name="colonne_intitule" id="colonne_intitule" required>
                <option value="" disabled selected>Sélectionnez une colonne</option>
              </select>
            </div>
          </div>

          <!-- Ligne 2 : Identifiant fiscal | ICE -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="colonne_identifiant_fiscal" class="form-label">Colonne Identifiant Fiscal</label>
              <select class="form-select form-select-lg shadow-sm" name="colonne_identifiant_fiscal" id="colonne_identifiant_fiscal">
                <option value="" disabled selected>Sélectionnez une colonne</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label for="colonne_ICE" class="form-label">Colonne ICE</label>
              <select class="form-select form-select-lg shadow-sm" name="colonne_ICE" id="colonne_ICE">
                <option value="" disabled selected>Sélectionnez une colonne</option>
              </select>
            </div>
          </div>

          <!-- Ligne 3 : Rubrique TVA | Contre partie -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="colonne_rubrique_tva" class="form-label">Colonne Rubrique TVA</label>
              <select class="form-select form-select-lg shadow-sm" name="colonne_rubrique_tva" id="colonne_rubrique_tva">
                <option value="" disabled selected>Sélectionnez une colonne</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label for="colonne_contre_partie" class="form-label">Colonne Contre Partie</label>
              <select class="form-select form-select-lg shadow-sm" name="colonne_contre_partie" id="colonne_contre_partie">
                <option value="" disabled selected>Sélectionnez une colonne</option>
              </select>
            </div>
          </div>

          <!-- Ligne 4 : RC | Ville -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="colonne_RC" class="form-label">Colonne RC</label>
              <select class="form-select form-select-lg shadow-sm" name="colonne_RC" id="colonne_RC">
                <option value="" disabled selected>Sélectionnez une colonne</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label for="colonne_ville" class="form-label">Colonne Ville</label>
              <select class="form-select form-select-lg shadow-sm" name="colonne_ville" id="colonne_ville">
                <option value="" disabled selected>Sélectionnez une colonne</option>
              </select>
            </div>
          </div>

          <!-- Ligne 5 : Adresse | Délai de paiement -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="colonne_adresse" class="form-label">Colonne Adresse</label>
              <select class="form-select form-select-lg shadow-sm" name="colonne_adresse" id="colonne_adresse">
                <option value="" disabled selected>Sélectionnez une colonne</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label for="colonne_delai_p" class="form-label">Colonne Délai de paiement</label>
              <select class="form-select form-select-lg shadow-sm" name="colonne_delai_p" id="colonne_delai_p">
                <option value="" disabled selected>Sélectionnez une colonne</option>
              </select>
            </div>
          </div>

          <!-- Actions -->
          <div class="d-flex justify-content-between align-items-center mt-3">
            <button type="button" class="btn btn-secondary" id="resetModal">
              <i class="bi bi-arrow-clockwise fs-6"></i> Réinitialiser
            </button>

            <div>
              <div id="loadingSpinner" class="d-none me-3 d-inline-block align-middle">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div>
              </div>

              <button type="submit" class="btn btn-primary btn-lg px-4" id="submitBtn" disabled>
                <i class="fas fa-check"></i> Importer
              </button>
            </div>
          </div>
        </form>

        <hr class="my-3">

        <h5>Aperçu des données importées</h5>
        <div id="previewContainer" style="overflow-x:auto; max-height: 300px; overflow-y:auto;">
          <table class="table table-bordered mb-0" id="previewTable">
            <thead class="table-light">
              <tr id="previewHeader"><!-- colonnes générées en JS --></tr>
            </thead>
            <tbody id="previewBody"><!-- lignes générées en JS --></tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>


<div id="errorMessages" class="alert alert-danger d-none">
    <ul id="errorList"></ul>
</div>

<!-- Modal Ajouter Fournisseur (contre_partie sur la même ligne que rubrique_tva) -->
<div class="modal fade" id="fournisseurModaladd" tabindex="-1" aria-labelledby="fournisseurModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="fournisseurModalLabel">
          <i class="fas fa-plus-circle"></i> Créer Fournisseur
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>

      <div class="modal-body bg-light">
        <form id="fournisseurFormAdd" autocomplete="off">
          @csrf
          <input type="hidden" id="nombre_chiffre_compte" value="{{ $societe->nombre_chiffre_compte }}">
          <input type="hidden" id="societe_id" name="societe_id" value="{{ session('societeId') }}">

          <!-- Ligne 1 : Compte | Intitulé -->
          <div class="row mb-2">
            <div class="col-md-6">
              <label for="compte" class="form-label">Compte</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="compte" name="compte" required>
              <small id="compte-error" class="text-danger" style="display: none;"></small>
            </div>
            <div class="col-md-6">
              <label for="intitule" class="form-label">Intitulé</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="intitule" name="intitule" required>
            </div>
          </div>

          <!-- Ligne 2 : Identifiant Fiscal | ICE -->
          <div class="row mb-2">
            <div class="col-md-6">
              <label for="identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="identifiant_fiscal" name="identifiant_fiscal" maxlength="8" pattern="\d*">
            </div>
            <div class="col-md-6">
              <label for="ICE" class="form-label">ICE</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="ICE" name="ICE" maxlength="15" pattern="\d*">
            </div>
          </div>

          <!-- Ligne 3 : RC | Ville -->
          <div class="row mb-2">
            <div class="col-md-6">
              <label for="RC" class="form-label">RC</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="RC" name="RC">
            </div>
            <div class="col-md-6">
              <label for="ville" class="form-label">Ville RC</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="ville" name="ville">
            </div>
          </div>

          <!-- Ligne 4 : Adresse | Délai de paiement -->
          <div class="row mb-2">
            <div class="col-md-6">
              <label for="adresse" class="form-label">Adresse</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="adresse" name="adresse" placeholder="Rue, quartier, etc.">
            </div>
            <div class="col-md-6">
              <label for="delai_p" class="form-label">Délai de paiement</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="delai_p" name="delai_p" value="60 jours">
            </div>
          </div>

          <!-- Ligne 5 : Rubrique TVA (+) | Contre Partie -->
          <div class="row mb-2">
            <div class="col-md-6">
              <label class="form-label d-block">Rubrique TVA</label>
              <div id="rubriqueTvaRowsContainer">
                <div class="d-flex align-items-center mb-2 rubrique-tva-group" data-index="1">
                  <select class="form-select form-select-sm shadow-sm" id="rubrique_tva" name="rubrique_tva[]">
                    <option value="">Sélectionnez une Rubrique</option>
                  </select>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addRubriqueTvaBtn" title="Ajouter une rubrique">
  <i class="fas fa-plus"></i>
  </button>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <label for="contre_partie" class="form-label">Contre Partie</label>
              <select class="form-select form-select-sm shadow-sm" id="contre_partie" name="contre_partie" required>
                <option value="">Sélectionner une contre partie</option>
                <option value="add_new">+ Ajouter un nouveau compte</option>
              </select>
              <p class="text-muted mt-1 small mb-0">
                <a href="#" id="ajouterCompteLink">+Ajouter</a>
              </p>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-outline-secondary px-4" id="resetFormBtn">
              <i class="fas fa-sync-alt"></i> Réinitialiser
            </button>
            <button type="submit" class="btn btn-primary btn-sm">
              Valider <i class="bi bi-check-lg"></i>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Modifier Fournisseur (contre_partie sur la même ligne que rubrique_tva) -->
<div class="modal fade" id="fournisseurModaledit" tabindex="-1" role="dialog" aria-labelledby="fournisseurModalLabel" aria-hidden="true" data-bs-animation="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-secondary text-white">
        <h5 class="modal-title" id="fournisseurModalLabel">Modifier un compte</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body bg-light">
        <form id="fournisseurFormEdit" autocomplete="off">
          <input type="hidden" id="editFournisseurId" value="">

          <!-- Ligne 1 : Compte | Intitulé -->
          <div class="row mb-2">
            <div class="col-md-6">
              <label for="editCompte" class="form-label">Compte</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="editCompte" name="compte" required>
            </div>
            <div class="col-md-6">
              <label for="editIntitule" class="form-label">Intitulé</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="editIntitule" name="intitule" required>
            </div>
          </div>

          <!-- Ligne 2 : Identifiant Fiscal | ICE -->
          <div class="row mb-2">
            <div class="col-md-6">
              <label for="editIdentifiantFiscal" class="form-label">Identifiant Fiscal</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="editIdentifiantFiscal" name="identifiant_fiscal" maxlength="8" pattern="\d*">
            </div>
            <div class="col-md-6">
              <label for="editICE" class="form-label">ICE</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="editICE" name="ICE" maxlength="15" pattern="\d*">
            </div>
          </div>

          <!-- Ligne 3 : RC | Ville -->
          <div class="row mb-2">
            <div class="col-md-6">
              <label for="editRC" class="form-label">RC</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="editRC" name="RC">
            </div>
            <div class="col-md-6">
              <label for="editVille" class="form-label">Ville</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="editVille" name="ville">
            </div>
          </div>

          <!-- Ligne 4 : Adresse | Délai de paiement -->
          <div class="row mb-2">
            <div class="col-md-6">
              <label for="editAdresse" class="form-label">Adresse</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="editAdresse" name="adresse" placeholder="Rue, quartier, etc.">
            </div>
            <div class="col-md-6">
              <label for="editDelaiP" class="form-label">Délai de paiement</label>
              <input type="text" class="form-control form-control-sm shadow-sm" id="editDelaiP" name="delai_p" value="60 jours">
            </div>
          </div>

          <!-- Ligne 5 : Rubrique TVA (+) | Contre Partie -->
          <div class="row mb-2">
            <div class="col-md-6">
              <label class="form-label d-block">Rubrique TVA</label>
              <div id="editRubriqueTvaRowsContainer">
                <div class="d-flex align-items-center mb-2 rubrique-tva-group" data-index="1">
                  <select class="form-select form-select-sm shadow-sm" id="editRubriqueTVA" name="edit_rubrique_tva[]">
                    <option value="">Sélectionnez une Rubrique</option>
                  </select>
                 <button type="button" class="btn btn-outline-primary btn-sm" id="addEditRubriqueTvaBtn" title="Ajouter une rubrique (édition)">
  <i class="fas fa-plus"></i>
</button>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <label for="editContrePartie" class="form-label">Contre Partie</label>
              <select class="form-select form-select-sm shadow-sm" id="editContrePartie" name="contre_partie">
                <option value="">Sélectionnez une contre partie</option>
              </select>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-outline-secondary px-4" id="resetFormBtnEdit">
              <i class="fas fa-sync-alt"></i> Réinitialiser
            </button>
            <button type="submit" class="btn btn-primary">
              Valider <i class="bi bi-check-lg"></i>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


{{--
<script>
document.addEventListener('DOMContentLoaded', function () {
  // CONFIG
  const TOTAL_MAX = 3;   // total max items in modal
  const ADD_LIMIT  = 2;  // max times user can press '+'
  const GET_RUBRIQUES_URL = '/get-rubriques-tva';

  function uid(prefix='id'){ return prefix + '_' + Math.random().toString(36).slice(2,8); }

  // cache fetch
  let cachedRubriques = null;
  function fetchRubriquesIfNeeded() {
    if (cachedRubriques) return Promise.resolve(cachedRubriques);
    return fetch(GET_RUBRIQUES_URL, { credentials: 'same-origin' })
      .then(res => { if (!res.ok) throw new Error('fetch error'); return res.json(); })
      .then(json => { cachedRubriques = json; return cachedRubriques; })
      .catch(err => { console.error('get-rubriques-tva', err); cachedRubriques = { categories: [] }; return cachedRubriques; });
  }

  // build options
  function remplirRubriquesTvaNative(selectEl, data) {
    selectEl.innerHTML = '';
    selectEl.appendChild(new Option('Sélectionnez une Rubrique', ''));
    const excluded = [147,151,152,148,144];
    if (!data || !Array.isArray(data.categories)) return;
    data.categories.forEach(cat => {
      const catOpt = new Option(cat.categoryName || '---', '');
      catOpt.className = 'category'; catOpt.disabled = true;
      selectEl.appendChild(catOpt);
      if (Array.isArray(cat.subCategories)) {
        cat.subCategories.forEach(sub => {
          const subOpt = new Option('  ' + sub, '');
          subOpt.className = 'subcategory'; subOpt.disabled = true;
          selectEl.appendChild(subOpt);
        });
      }
      if (Array.isArray(cat.rubriques)) {
        cat.rubriques.forEach(r => {
          if (excluded.includes(r.Num_racines)) return;
          const txt = ` ${r.Num_racines}: ${r.Nom_racines} — ${Math.round(r.Taux)}%`;
          const opt = new Option(txt, r.Num_racines);
          opt.setAttribute('data-search-text', `${r.Num_racines} ${r.Nom_racines} ${cat.categoryName||''}`);
          selectEl.appendChild(opt);
        });
      }
    });
  }

  // init select2
  function initSelect2On(selectEl, dropdownParentSelector) {
    const $sel = $(selectEl);
    if ($sel.hasClass('select2-hidden-accessible')) {
      try { $sel.select2('destroy'); } catch(e){}
    }
    $sel.select2({
      width: '100%',
      dropdownAutoWidth: true,
      minimumResultsForSearch: 0,
      dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $(document.body),
      templateResult: function(data) {
        if (!data.id) return data.text;
        const $el = $(data.element);
        if ($el.hasClass('category')) return $('<span style="font-weight:700;">' + data.text + '</span>');
        if ($el.hasClass('subcategory')) return $('<span style="font-style:italic; padding-left:8px;">' + data.text + '</span>');
        return $('<span>' + data.text + '</span>');
      },
      matcher: function(params, data) {
        if (!params.term || params.term.trim() === '') return data;
        const searchText = $(data.element).data('search-text') || '';
        if (searchText.toLowerCase().includes(params.term.toLowerCase())) return data;
        if ((data.text||'').toLowerCase().includes(params.term.toLowerCase())) return data;
        return null;
      }
    });
  }

  // swal fallback
  function showWarning(title, text) {
    if (typeof Swal !== 'undefined' && Swal.fire) {
      Swal.fire({ icon: 'warning', title: title || 'Attention', text: text || '' });
    } else {
      alert((title ? title + '\n' : '') + (text || ''));
    }
  }

  // create item: addedViaPlus true => increments add counter on append
  function createRubriqueItem(modalSelector, addedViaPlus = false) {
    const wrapper = document.createElement('div');
    wrapper.className = 'rubrique-tva-item';
    wrapper.setAttribute('data-added', addedViaPlus ? '1' : '0');
    wrapper.setAttribute('data-deleted', '0');
    const selId = uid('rub');
    wrapper.innerHTML = `
      <select id="${selId}" name="rubrique_tva[]" class="form-select form-select-sm"></select>
      <button type="button" class="btn btn-danger btn-sm btn-remove-rubrique" title="Supprimer">
        <i class="fas fa-minus"></i>
      </button>
    `;

    const selectEl = wrapper.querySelector('select');
    const removeBtn = wrapper.querySelector('.btn-remove-rubrique');

    fetchRubriquesIfNeeded().then(data => {
      remplirRubriquesTvaNative(selectEl, data);
      initSelect2On(selectEl, modalSelector);
    });

    // remove/hide behavior will be handled by container logic (different for add/edit)
    return wrapper;
  }

  // setup container
  // options: { silentInit: bool, editMode: bool }
  function setupRubriqueContainer(containerId, addBtnId, modalSelector, options = {}) {
    const container = document.getElementById(containerId);
    const addBtn = document.getElementById(addBtnId);
    if (!container || !addBtn) return;

    container.classList.add('rubrique-tva-flex');
    if (!container.dataset.addCount) container.dataset.addCount = '0';

    // init existing selects (but for editMode with silentInit true we DON'T create default)
    fetchRubriquesIfNeeded().then(data => {
      const existingSelects = container.querySelectorAll('select[name="rubrique_tva[]"]');
      if (existingSelects.length) {
        existingSelects.forEach(sel => {
          remplirRubriquesTvaNative(sel, data);
          initSelect2On(sel, modalSelector);
          const p = sel.closest('.rubrique-tva-item');
          if (p) {
            p.setAttribute('data-added', '0');
            p.setAttribute('data-deleted', '0');
          }
        });
      } else if (!options.silentInit) {
        // only create a default item if NOT silentInit (i.e. add modal)
        container.appendChild(createRubriqueItem(modalSelector, false));
      }
    });

    // click '+'
    addBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const totalCount = container.querySelectorAll('.rubrique-tva-item:not(.hidden)').length;
      const addCount   = parseInt(container.dataset.addCount || '0', 10);

      if (addCount >= ADD_LIMIT) {
        showWarning('Limite d\'ajout', `Vous ne pouvez pas ajouter plus de ${ADD_LIMIT} rubriques via le bouton +.`);
        return;
      }
      if (totalCount >= TOTAL_MAX) {
        showWarning('Limite atteinte', `Le nombre total de rubriques est limité à ${TOTAL_MAX}.`);
        return;
      }

      const newItem = createRubriqueItem(modalSelector, true);
      container.appendChild(newItem);
      container.dataset.addCount = (parseInt(container.dataset.addCount || '0', 10) + 1).toString();
    });

    // delegated handler for remove button:
    // - if editMode: hide item (add data-deleted=1) and mark hidden class; decrement addCount if addedViaPlus
    // - if not editMode: remove from DOM (behavior add)
    container.addEventListener('click', function(ev){
      const btn = ev.target.closest('.btn-remove-rubrique');
      if (!btn) return;
      const item = btn.closest('.rubrique-tva-item');
      if (!item) return;

      const wasAdded = item.getAttribute('data-added') === '1';

      if (options.editMode) {
        // hide and mark deleted instead of removing
        item.classList.add('hidden');
        item.setAttribute('data-deleted', '1');
        // If it was an added-via-plus item, decrement counter
        if (wasAdded) {
          container.dataset.addCount = Math.max(0, parseInt(container.dataset.addCount || '0', 10) - 1).toString();
        }
      } else {
        // normal add modal behavior: remove element entirely
        if (wasAdded) {
          container.dataset.addCount = Math.max(0, parseInt(container.dataset.addCount || '0', 10) - 1).toString();
        }
        item.remove();
        // if empty and not silentInit, create a default item to keep UI usable
        if (container.querySelectorAll('.rubrique-tva-item').length === 0 && !options.silentInit) {
          container.appendChild(createRubriqueItem(modalSelector, false));
        }
      }
    });
  }

  // initialize containers:
  // add modal: silentInit false, editMode false (normal add behavior)
  setupRubriqueContainer('rubriqueTvaRowsContainer', 'addRubriqueTvaBtn', '#fournisseurModaladd', { silentInit: false, editMode: false });

  // edit modal: silentInit true (no default created), editMode true (minus hides & marks deleted)
  setupRubriqueContainer('editRubriqueTvaRowsContainer', 'addEditRubriqueTvaBtn', '#fournisseurModaledit', { silentInit: true, editMode: true });

  // ------------------- Navigation Enter / Shift+Enter dans les deux forms -------------------
  (function($){
    function getFocusable($form) {
      return $form.find('input:not([type=hidden]):not([disabled]), textarea:not([disabled]), select:not([disabled]), button[type=submit]').filter(':visible');
    }
    function focusElement($el) {
      if (!$el || !$el.length) return;
      if ($el.is('select') && $el.hasClass('select2-hidden-accessible')) {
        const selId = $el.attr('id');
        if (selId) {
          const $container = $(`#${selId}`).parent().find('.select2-selection').first();
          if ($container && $container.length) {
            try { $container.focus(); } catch(e){ $el.focus(); }
            return;
          }
        }
      }
      try { $el.focus(); } catch(e){}
    }

    function initEnterNav(formSelector) {
      const $form = $(formSelector);
      if (!$form.length) return;
      $form.off('keydown.enterNav').on('keydown.enterNav', function(e) {
        if (e.key !== 'Enter') return;
        const active = document.activeElement;
        if (active && $(active).closest('.select2-search__field').length) return;
        if (active && active.tagName && active.tagName.toLowerCase() === 'textarea' && (e.ctrlKey || e.metaKey)) return;
        e.preventDefault();

        const $focusables = getFocusable($form);
        if ($focusables.length === 0) return;

        let idx = -1;
        for (let i = 0; i < $focusables.length; i++) {
          if ($focusables.get(i) === active) { idx = i; break; }
        }

        if (e.shiftKey) {
          let found = false;
          for (let i = (idx === -1 ? $focusables.length-1 : idx-1); i >= 0; i--) {
            const $next = $($focusables.get(i));
            if ($next.is(':visible') && !$next.is(':disabled')) { focusElement($next); found = true; break; }
          }
          if (!found) focusElement($($focusables.get(0)));
        } else {
          let found = false;
          for (let i = (idx === -1 ? 0 : idx+1); i < $focusables.length; i++) {
            const $next = $($focusables.get(i));
            if ($next.is(':visible') && !$next.is(':disabled')) { focusElement($next); found = true; break; }
          }
          if (!found) {
            try { $form.trigger('submit'); } catch(e){}
          }
        }
      });
    }

    initEnterNav('#fournisseurFormAdd');
    initEnterNav('#fournisseurFormEdit');
  })(jQuery);

}); // DOMContentLoaded
</script> --}}




<!-- Modal Plan Comptable -->
<div class="modal fade" id="planComptableModalAdd" tabindex="-1" role="dialog" aria-labelledby="planComptableModalLabel" aria-hidden="true">
    <div class="modal-dialog shadow-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="planComptableModalLabel">Ajouter un compte</h5>
                <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="planComptableFormAdd">
                    @csrf
                    <!-- Champs cachés -->
                    <input type="hidden" id="nombre_chiffre_compte" value="{{ $societe->nombre_chiffre_compte }}">
                    <input type="hidden" id="societe_id" name="societe_id" value="{{ session('societeId') }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="compte_add" class="form-label">Compte</label>
                            <input type="text" class="form-control shadow-sm" id="compte_add" name="compte" placeholder="Entrer le numéro du compte" required>
                        </div>
                        <div class="col-md-6">
                            <label for="intitule_add" class="form-label">Intitulé</label>
                            <input type="text" class="form-control shadow-sm" id="intitule_add" name="intitule" placeholder="Entrer l'intitulé" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="reset" class="btn btn-light d-flex align-items-center">
                            <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-primary d-flex align-items-center ms-2">
                            <i class="bi bi-plus-circle me-1"></i> Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

 <!-- Statistiques -->
 <span id="select-stats" class="text-muted"></span>

 <script type="text/javascript">
    // Initialisation du tableau Tabulator
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
// map globale des comptes plan (clé = numéro de compte => label)




// Tabulator init
var table = new Tabulator("#fournisseur-table", {
ajaxURL: "/fournisseurs/data",
  ajaxResponse: function (url, params, response) {
    console.log("ajaxResponse raw:", response);

    if (!response) return [];

    const fournisseurs = response.fournisseurs || [];
    const comptesPlan = response.comptes_plan || [];

    // Ajouter un tag pour savoir d’où vient chaque ligne
    const fournisseursTag = fournisseurs.map(f => ({
      ...f,
      source: "fournisseur"
    }));

    const comptesPlanTag = comptesPlan.map(c => ({
      id: c.id,
      compte: c.compte,
      intitule: c.intitule,
      source: "plan_comptable",
      identifiant_fiscal: "",
      ICE: "",
      rubrique_tva: "",
      designation: "",
      contre_partie: ""
    }));

    // Fusionner les deux listes
    const merged = [...fournisseursTag, ...comptesPlanTag];

    console.log("Fusionné :", merged);
    return merged;
  },



    layout: "fitColumns",
    height: "600px", // Hauteur du tableau
    selectable: true, // Permet de sélectionner les lignes
     // Formatter de ligne pour le surlignage
     rowFormatter: function(row) {
      var data = row.getData();

      var missingCompte    = data.missing_compte === 1 || !data.compte;
      var missingIntitule  = data.missing_intitule === 1 || !data.intitule;
      var invalidPrefix    = data.invalid_compte_format === 1 || (data.compte && !data.compte.startsWith("4411"));
      var invalidLength    = data.invalid_length_compte === 1;

      // Rouge pour chaque erreur de format ou de longueur
      if (invalidPrefix) {
        row.getElement().style.backgroundColor = 'rgba(228,20,20,0.45)';
        return;
      }
      if (invalidLength) {
        row.getElement().style.backgroundColor = 'rgba(228,20,20,0.45)';
        return;
      }

      // Jaune pour compte ou intitulé vide
      if (missingCompte || missingIntitule) {
        row.getElement().style.backgroundColor = 'rgba(233,233,13,0.5)';
      }
    },



    initialSort: [
        { column: "compte", dir: "asc" } // Tri initial

    ],
    columns: [
        {
            title: `
                <i class="fas fa-check-square" id="selectAllIcon" title="Sélectionner tout" style="cursor: pointer;"></i>
                <i class="fas fa-trash-alt" id="deleteAllIcon" title="Supprimer toutes les lignes sélectionnées" style="cursor: pointer;"></i>
            `,
            field: "select",
            formatter: "rowSelection", // Active la sélection de ligne
            headerSort: false,
            hozAlign: "center",
            headerHozAlign: "center", // Centrer le titre de cette colonne
            width: 60,
            cellClick: function (e, cell) {
                cell.getRow().toggleSelect(); // Basculer la sélection de ligne
            },
        },
        {
  title: "Compte",
  field: "compte",

  // 1) désactive totalement l’édition
  editable: false,

  // 2) affiche la valeur sans <input>
  formatter: "plaintext",

  // 3) garde le filtre en en-tête
  headerFilter: "input",
  headerHozAlign: "center",
  headerFilterParams: {
    elementAttributes: {
      style: "width: 90px; height: 22px;"
    }

  },

  // 4) force la possibilité de sélectionner du texte dans la cellule
  cellStyled: function(cell){
    let el = cell.getElement();
    el.style.userSelect        = "text";
    el.style.webkitUserSelect  = "text";
    el.style.MozUserSelect     = "text";
  },

  // 5) comportement « click-to-select » : dès qu’on clique sur la cellule, on sélectionne tout son contenu
  cellClick: function(e, cell){
    let el = cell.getElement();
    let selection = window.getSelection();
    let range = document.createRange();
    selection.removeAllRanges();
    range.selectNodeContents(el);
    selection.addRange(range);
  },
  },



        {
            title: "Intitulé",
            field: "intitule",
            editor: "input",
          headerFilter: "input",
            headerHozAlign: "center", // Centrer le titre
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 90px; height: 22px;"
                }
            },
        },
        {
            title: "Identifiant Fiscal",
            field: "identifiant_fiscal",
            editor: "input",

            headerFilter: "input",
            headerHozAlign: "center", // Centrer le titre
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 90px; height: 22px;"
                }
            },
        },
        {
                    title: 'ICE',
                    field: 'ICE',

            headerFilter: "input",
            headerHozAlign: "center", // Centrer le titre
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 90px; height: 22px;"
                }
            },
                    formatter: function(cell) {
                        var value = cell.getValue();
                        var status = cell.getData().highlight_ice;
                        var el = cell.getElement();
                        if (status === 'missing') {
                            el.style.backgroundColor = 'rgba(233,233,13,0.838)';
                        } else if (status === 'invalid') {
                            el.style.backgroundColor = 'rgba(228,20,20,0.453)';
                        }
                        return value;
                    }
                },
        {
            title: "Nature de l'opération",
            field: "nature_operation",
            visible:false,
            headerFilter: "input",
            headerHozAlign: "center", // Centrer le titre
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 90px; height: 22px;"
                }
            },
        },
        {
            title: "Rubrique TVA",
            field: "rubrique_tva",
            headerFilter: "input",
            headerHozAlign: "center", // Centrer le titre
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 90px; height: 22px;"
                }
            },
        },
        {
            title: "Désignation",
            field: "designation",
            visible:false,
            headerFilter: "input",
            headerHozAlign: "center", // Centrer le titre
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 90px; height: 22px;"
                }
            },
        },
        {
            title: "Contre Partie",
            field: "contre_partie",
            headerFilter: "input",
            headerHozAlign: "center", // Centrer le titre
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 90px; height: 22px;"
                }
            },
        },
        {
            title: "Invalid",
            field: "invalid",
            visible: false // Champ caché mais utile pour les validations
        },
        {
            title: "Actions",
            field: "action-icons",
            formatter: function () {
                return `
                    <i class='fas fa-edit text-primary edit-icon' style='font-size: 0.9em; cursor: pointer;'></i>
                    <i class='fas fa-trash-alt text-danger delete-icon' style='font-size: 0.9em; cursor: pointer;'></i>
                `;
            },
            cellClick: function (e, cell) {
                var row = cell.getRow();
                if (e.target.classList.contains("edit-icon")) {
                    var rowData = cell.getRow().getData();
                    editFournisseur(rowData); // Fonction de modification
                } else if (e.target.classList.contains("delete-icon")) {
                    var rowData = cell.getRow().getData();
                    deleteFournisseur(rowData.id);
                }
            },
            hozAlign: "center",
            headerSort: false,
            headerHozAlign: "center", // Centrer le titre
        },
    ],
    rowFormatter: function (row) {
        let data = row.getData();
        let rowElement = row.getElement();
        // Réinitialiser les styles au début
        rowElement.style.backgroundColor = "";
        rowElement.classList.remove("invalid-row");
        // Vérification pour compte et intitulé vides ou nuls
        if ((!data.compte && !data.intitule) || (!data.compte && data.intitule) || (!data.intitule && data.compte)) {
            rowElement.style.backgroundColor = "rgba(233, 233, 13, 0.838)"; // Jaune orangé
        } else if (data.invalid === 1) {
            rowElement.classList.add("invalid-row");
        }
    },
});


// Définir un événement pour l'édition des cellules
table.on("cellEdited", function (cell) {
    let rowData = cell.getRow().getData();
    if (cell.getField() === "compte") {
        fetch(`/fournisseurs/update/${rowData.id}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({ compte: rowData.compte }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert("Succès: " + data.message);
                    cell.getRow().update({ invalid: 0 }); // Supprimer le surlignement rouge
                } else {
                    alert("Erreur: " + data.message);
                }
            })
            .catch((error) => {
                alert("Erreur: Impossible de mettre à jour le fournisseur.");
                console.error(error);
            });
    }
});


table.on("dataLoaded", function (data) {
  console.log("✅ Données fusionnées :", data.length, "lignes chargées");
});
// Fonction pour supprimer les lignes sélectionnées côté serveur
function deleteSelectedRows() {
    // Récupérer l'ID de la société depuis la balise meta
    const societeId = document
        .querySelector('meta[name="societe-id"]')
        .getAttribute("content");
    if (!societeId) {
        alert("Aucune société sélectionnée dans la session.");
        return;
    }

    var selectedRows = table.getSelectedRows();
    var idsToDelete = selectedRows.map(function (row) {
        return row.getData().id;
    });

    if (idsToDelete.length > 0) {
        if (confirm("Voulez-vous vraiment supprimer les lignes sélectionnées ?")) {
            fetch("/fournisseurs/delete-selected", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content")
                },
                // Ajout de societeId dans le corps de la requête
                body: JSON.stringify({
                    ids: idsToDelete,
                    societeId: societeId
                })
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert("Succès: " + data.message);
                    selectedRows.forEach((row) => row.delete());
                } else {
                    alert("Erreur: " + data.error);
                }
            })
            .catch((error) => {
                alert("Erreur: Impossible de supprimer les fournisseurs sélectionnés.");
                console.error(error);
            });
        }
    } else {
        alert("Aucune ligne sélectionnée.");
    }
}

// Gestionnaire pour sélectionner/désélectionner toutes les lignes
document.getElementById("fournisseur-table").addEventListener("click", function (e) {
    if (e.target.id === "selectAllIcon") {
        if (table.getSelectedRows().length === table.getRows().length) {
            table.deselectRow(); // Désélectionner tout
        } else {
            table.selectRow(); // Sélectionner tout
        }
    } else if (e.target.id === "deleteAllIcon") {
        deleteSelectedRows();
    }
});




// Initialisation globale
var designationValue = ''; // Variable globale pour stocker l'intitulé

// Fonction pour remplir les rubriques TVA

// Fonction pour remplir les options de contrepartie
// Fonction pour remplir un champ de sélection avec des données provenant d'une API 2
function remplirContrePartie(selectId, selectedValue = null, callback = null) {
    $.ajax({
        url: '/comptes',
        type: 'GET',
        success: function (data) {
            console.log("Données reçues de l'API :", data); // Log des données pour débogage

            // Sélectionner l'élément avec l'ID fourni
            var select = $("#" + selectId);

            // Vérifier si l'élément existe
            if (select.length === 0) {
                console.error("Élément avec l'ID", selectId, "non trouvé dans le DOM.");
                return;
            }

            // Si Select2 est initialisé, le détruire pour éviter des conflits
            if (select.hasClass("select2-hidden-accessible")) {
                select.select2("destroy");
            }

            // Réinitialiser le champ de sélection et ajouter une option par défaut
            select.empty();
            select.append(new Option("Sélectionnez une contre partie", ""));

            // Trier les données par ordre alphabétique (par le champ compte)
            data.sort((a, b) => a.compte.localeCompare(b.compte));

            // Ajouter les options au champ de sélection
            data.forEach(function (compte) {
                let option = new Option(`${compte.compte} - ${compte.intitule}`, compte.compte);
                select.append(option);
            });

            // Réinitialiser et appliquer Select2
            select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true
            });

            // Si une valeur sélectionnée est fournie, la définir
            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }

            // Exécuter le callback si défini
            if (callback && typeof callback === 'function') {
                callback();
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des comptes :', textStatus, errorThrown);
        }
    });
}

 $(document).ready(function() {
  // Fonction utilitaire pour récupérer toutes les rubriques sélectionnées
  function getConcatenatedRubriquesTva() {
    const valeurs = $('select[name="rubrique_tva[]"]').map(function() {
      return $(this).val().trim();
    }).get().filter(v => v !== '');
    return valeurs.join('/');
  }

  const $form      = $("#fournisseurFormAdd");
  const $compte    = $form.find("#compte");
  const socId      = $form.find("#societe_id").val();
  const $submitBtn = $form.find('button[type="submit"]');

  // 1) Vérification d’unicité au blur (alert bloquante)
  function checkCompteUnique() {
    const val = $compte.val().trim();
    if (!val || !socId) return;

    $.ajax({
      url: "/verifier-compte",   // <-- ajustez à l'URI exact de votre route
      method: "GET",
      data: { compte: val, societe_id: socId },
      dataType: "json",
      async: false,              // rend l'appel bloquant pour le blur
      success(data) {
        if (data.exists) {
          alert(data.message || "Ce compte existe déjà !");

          $compte.val("").focus();
          genererCompteAuto(socId, "#compte");

          $submitBtn.prop("disabled", true);

        } else {
          $submitBtn.prop("disabled", false);
        }
      },
      error(xhr, status, err) {
        console.error("Erreur check-compte:", status, err);
      }
    });
  }

  $compte.on("blur", checkCompteUnique);

  // 2) Réactivation du bouton si l'utilisateur corrige
  $compte.on("focus", function() {
    $("#compte-error").hide();
    $submitBtn.prop("disabled", false);
  });

  // 3) Interception de la soumission
  $form.on("submit", function(e) {
    e.preventDefault();

    // Empêche si compte vide (vide suite à l'alerte)
    if (!$compte.val().trim()) {
      $compte.focus();
      return;
    }

    // Remplissage automatique de 'designation' si vide
    var designationValue = $('#designation').val();
    if (!designationValue) {
      var cpText   = $('#contre_partie option:selected').text();
      var intitule = cpText.split('-')[1]?.trim();
      if (intitule) $('#designation').val(intitule);
    }

    // Envoi AJAX
    envoyerDonnees();
  });

  // 4) Fonction d’envoi des données
  function envoyerDonnees() {
    $.ajax({
      url: "/fournisseurs",
      type: "POST",
      data: {
        compte:               $compte.val(),
        intitule:             $("#intitule").val(),
        identifiant_fiscal:   $("#identifiant_fiscal").val(),
        ICE:                  $("#ICE").val(),
        nature_operation:     $("#nature_operation").val(),
        rubrique_tva:         getConcatenatedRubriquesTva(),
        designation:          $("#designation").val(),
        contre_partie:        $("#contre_partie").val(),
        societe_id:           socId,
          // nouveaux champs ajoutés
     RC: $('#RC').val(),
      rc: $('#RC').val(),
      ville: $('#ville').val(),
      adresse: $('#adresse').val(),
      delai_p: $('#delai_p').val(),

        _token:               '{{ csrf_token() }}'
      },
      success(response) {
        table.setData("/fournisseurs/data");
        $("#fournisseurModaladd").modal("hide");
        $form[0].reset();
        $submitBtn.prop("disabled", false);

      },
      error(xhr) {
        if (xhr.status === 422) {
          var resp = xhr.responseJSON;
          if (resp && resp.error) {
            alert(resp.error);

            $compte.focus();

          }
        } else {
          console.error("Erreur lors de l'envoi :", xhr.responseText);
        }
      }
    });
  }
});


// Nouvelle version de remplirRubriquesTva : accepte un paramètre selectedValue
function remplirRubriquesTva(selectElement, selectedValue = null) {
    $.ajax({
        url: '/get-rubriques-tva',
        type: 'GET',
        success: function (data) {
            // Vider l’existant
            $(selectElement).empty();
            selectElement.appendChild(new Option('Sélectionnez une Rubrique', ''));

            const excludedNumRacines = [147, 151, 152, 148, 144];

            data.categories.forEach(categoryObj => {
                const catOption = new Option(categoryObj.categoryName, '', false, false);
                $(catOption).addClass('category').prop('disabled', true);
                selectElement.appendChild(catOption);

                categoryObj.subCategories.forEach(sub => {
                    const subOption = new Option('  ' + sub, '', false, false);
                    $(subOption).addClass('subcategory').prop('disabled', true);
                    selectElement.appendChild(subOption);
                });

                categoryObj.rubriques.forEach(rubrique => {
                    if (!excludedNumRacines.includes(rubrique.Num_racines)) {
                        const text = `    ${rubrique.Num_racines}: ${rubrique.Nom_racines} : ${Math.round(rubrique.Taux)}%`;
                        const opt = new Option(text, rubrique.Num_racines);
                        $(opt).attr('data-search-text', `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.categoryName}`);
                        selectElement.appendChild(opt);
                    }
                });
            });

            // (Re)initialiser Select2 si nécessaire
            if ($(selectElement).hasClass('select2-hidden-accessible')) {
                $(selectElement).select2('destroy');
            }
            $(selectElement).select2({
                dropdownParent: $('#fournisseurModaledit'),
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true,
                templateResult: function(data) {
                    if (!data.id) return data.text;
                    var el = $(data.element);
                    if (el.hasClass('category')) {
                        return $('<span style="font-weight:bold; padding-left:0;">' + data.text + '</span>');
                    }
                    if (el.hasClass('subcategory')) {
                        return $('<span style="font-weight:bold; padding-left:20px;">' + data.text + '</span>');
                    }
                    return $('<span>' + data.text + '</span>');
                },
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') return data;
                    var searchText = $(data.element).data('search-text');
                    return searchText && searchText.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
                }
            });

            // Si une valeur est passée, la sélectionner
            if (selectedValue !== null) {
                $(selectElement).val(selectedValue).trigger('change');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des rubriques TVA :', textStatus, errorThrown);
        }
    });
}

// ==== Gestion du formulaire de modification ====
$("#fournisseurFormEdit").on("submit", function(e) {
    e.preventDefault();
    var fournisseurId = $("#editFournisseurId").val();
    var url = "/fournisseurs/" + fournisseurId;

    // Si désignation vide, on génère depuis la contre-partie
    var designationValue = $('#editDesignation').val();
    if (designationValue === '') {
        var contrePartieIntitule = $('#editContrePartie').find('option:selected').text();
        var intitule = contrePartieIntitule.split('-')[1]?.trim();
        if (intitule) {
            $('#editDesignation').val(intitule);
        }
    }

    // Concaténer toutes les rubriques sélectionnées avec "/"
    var rubriquesArray = $('select[name="edit_rubrique_tva[]"]').map(function() {
        return (($.trim($(this).val() || '')) + '');
    }).get().filter(v => v !== '');
    var rubriquesConcat = rubriquesArray.join('/');

    // Build payload (POST + _method=PUT pour compatibilité Laravel)
    var payload = {
        _method: 'PUT',
        compte: $.trim($("#editCompte").val() || ''),
        intitule: $.trim($("#editIntitule").val() || ''),
        identifiant_fiscal: $.trim($("#editIdentifiantFiscal").val() || ''),
        ICE: $.trim($("#editICE").val() || ''),
        nature_operation: $.trim($("#editNatureOperation").val() || ''),
        rubrique_tva: rubriquesConcat,
        designation: $.trim($("#editDesignation").val() || ''),
        contre_partie: $.trim($("#editContrePartie").val() || ''),
        // envoyer RC en maj + en min pour couvrir les deux versions côté back
        RC: $.trim($('#editRC').val() || ''),
        rc: $.trim($('#editRC').val() || ''),
        ville: $.trim($('#editVille').val() || ''),
        adresse: $.trim($('#editAdresse').val() || ''),
        delai_p: $.trim($('#editDelaiP').val() || ''),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: url,
        type: "POST", // POST + _method=PUT
        data: payload,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            // actualise le tableau global si défini
            if (typeof table !== 'undefined' && table !== null) {
                table.setData("/fournisseurs/data");
            }
            $("#fournisseurModaledit").modal("hide");
            $("#fournisseurFormEdit")[0].reset();
            $("#editCompte").prop('disabled', false);
        },
        error: function(xhr) {
            console.error('Erreur edit fournisseur:', xhr.responseText);
            alert("Erreur lors de l'enregistrement des données !");
        }
    });
});

// ==== Remplissage du formulaire pour modification ====
function editFournisseur(data) {
    $("#editFournisseurId").val(data.id || '');
    $("#editIntitule").val(data.intitule || '');
    $("#editIdentifiantFiscal").val(data.identifiant_fiscal || '');
    $("#editICE").val(data.ICE || '');

    // --- Nouveaux champs ---
    // on essaie plusieurs variantes de clés renvoyées par le back
    $("#editRC").val(data.RC ?? data.rc ?? '');
    $("#editVille").val(data.ville ?? data.Ville ?? '');
    $("#editDelaiP").val(data.delai_p ?? data.delaiP ?? '');
    $("#editAdresse").val(data.adresse ?? data.Adresse ?? '');

    // Split des rubriques (ex : "5/12/20")
    var rubriques = ((data.rubrique_tva || '') + '').split('/').map(function(v){ return v.trim(); }).filter(function(v) { return v !== ''; });

    // Container des selects TVA
    var $container = $("#editRubriqueTvaRowsContainer");
    var $firstGroup = $container.find('.rubrique-tva-group').first();

    // Si pas de groupe par défaut présent, créer un groupe minimal (sécurisé)
    if ($firstGroup.length === 0) {
        // tu devrais avoir une fonction de création; sinon on force une structure minimale
        var defaultHtml = '<div class="rubrique-tva-group" data-index="1"><select name="edit_rubrique_tva[]" class="form-select form-select-sm"></select><button type="button" class="btn btn-danger btn-sm ms-2 remove-edit-rubrique" style="display:none;"><i class="fas fa-minus"></i></button></div>';
        $container.append(defaultHtml);
        $firstGroup = $container.find('.rubrique-tva-group').first();
    }

    // Supprimer tous les groupes sauf le premier (on reconstruira)
    $container.find('.rubrique-tva-group').not($firstGroup).remove();

    var $firstSelect = $firstGroup.find('select[name="edit_rubrique_tva[]"]');

    if (rubriques.length > 0) {
        rubriques.forEach(function(val, index) {
            if (index === 0) {
                // Remplir le premier select et sélectionner (remplirRubriquesTva doit accepter selectedValue param)
                if (typeof remplirRubriquesTva === 'function') {
                    try { remplirRubriquesTva($firstSelect[0], val, '#fournisseurModaledit'); } catch(e){ console.warn(e); }
                } else {
                    // fallback si ta fonction a un autre nom
                    console.warn('remplirRubriquesTva() non définie');
                }
            } else {
                // Cliquer sur "+" pour ajouter un nouveau select (ton bouton doit créer un .rubrique-tva-group)
                $("#addEditRubriqueTvaBtn").click();
                var $newGroup = $container.find('.rubrique-tva-group').last();
                var $newSelect = $newGroup.find('select[name="edit_rubrique_tva[]"]');
                if (typeof remplirRubriquesTva === 'function') {
                    try { remplirRubriquesTva($newSelect[0], val, '#fournisseurModaledit'); } catch(e){ console.warn(e); }
                }
            }
        });
    } else {
        // Pas de rubrique existante : remplir le premier select vide
        if (typeof remplirRubriquesTva === 'function') {
            try { remplirRubriquesTva($firstSelect[0], null, '#fournisseurModaledit'); } catch(e){ console.warn(e); }
        }
    }

    // Remplir le select contre_partie (Select2)
    if (typeof remplirContrePartie === 'function') {
        try { remplirContrePartie("editContrePartie", data.contre_partie); } catch(e){ console.warn(e); }
    }

    $("#editDesignation").val(data.designation || '');

    // Configuration dynamique du champ Compte
    var societeId = $("#societe_id").val();
    var nombreChiffres = parseInt($('#nombre_chiffre_compte').val() || 0, 10);
    $("#editCompte").attr('maxlength', nombreChiffres);

    var $compte = $("#editCompte");
    if (data.compte) {
        $compte.val(data.compte).prop('disabled', true);
    } else {
        // génère un compte si manquant (si ta fonction existe)
        if (typeof genererCompteAuto === 'function') {
            genererCompteAuto(societeId, '#editCompte');
        }
        $compte.prop('disabled', false);
    }

    // Vérification existence de compte au blur
    $compte.off('blur').on('blur', function() {
        var val = $(this).val();
        if (!val) return;
        $.ajax({
            url: '/fournisseurs/check-compte',
            type: 'GET',
            data: { compte: val },
            success: function(res) {
                if (res.exists) {
                    alert('Ce compte existe déjà. Un nouveau compte sera généré.');
                    if (typeof genererCompteAuto === 'function') genererCompteAuto(societeId, '#editCompte');
                }
            },
            error: function() {
                console.warn('Vérification du compte impossible');
            }
        });
    });

    $("#fournisseurModaledit").modal("show");
}


//gestion add
$(document).ready(function () {
    // Variables initiales
    var initialValue = '4411'; // Préfixe des fournisseurs
    var societeId = $('#societe_id').val(); // ID de la société
    var nombreChiffresCompte = parseInt($('#nombre_chiffre_compte').val()); // Nombre de chiffres du compte

    // Fonction pour envoyer les données via AJAX
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
                rubrique_tva: $("#rubrique_tva option:selected").text(),
                designation: $("#designation").val(),
                contre_partie: $("#contre_partie").val(),
                societe_id: $("#societe_id").val(),
                nombre_chiffre_compte: nombreChiffresCompte,
                    // nouveaux champs ajoutés
    RC: $('#RC').val(),
      rc: $('#RC').val(),
      ville: $('#ville').val(),
      adresse: $('#adresse').val(),
      delai_p: $('#delai_p').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                console.log(response);  // Pour vérifier la réponse complète
                if (response.success) {
                    // Mise à jour de la table Tabulator sans recharger la page
                    table.addData([{
                        compte: $("#compte").val(),
                        intitule: $("#intitule").val(),
                        identifiant_fiscal: $("#identifiant_fiscal").val(),
                        ICE: $("#ICE").val(),
                        rubrique_tva: $("#rubrique_tva option:selected").text(),
                        contre_partie: $("#contre_partie").val(),
                        societe_id: $("#societe_id").val()

                    }]);

                    // Mise à jour de la table Plan Comptable
                    $.ajax({
                        url: '/plancomptable',
                        type: 'POST',
                        data: {
                            compte: $("#compte").val(),
                            intitule: $("#intitule").val(),
                            societe_id: $("#societe_id").val(),
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (planComptableResponse) {
                            if (planComptableResponse.success) {
                                alert("Fournisseur et compte ajoutés avec succès.");
                            } else {
                                alert("Erreur lors de l'ajout du compte dans le plan comptable.");
                            }
                        },
                        error: function (xhr) {
                            console.error("Erreur lors de l'ajout du compte dans le plan comptable:", xhr.responseText);
                            alert("Erreur lors de l'ajout du compte dans le plan comptable.");
                        }
                    });

                    // Réinitialisation du modal
                    $("#fournisseurModaladd").modal("hide");
                    $("#fournisseurFormAdd")[0].reset();
                    $('#fournisseurFormAdd select').val('').trigger('change');
                } else {
                    alert("Erreur lors de l'ajout du fournisseur : " + response.error);
                }
            },
            error: function (xhr) {
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    alert("Erreur de validation : " + JSON.stringify(errors));
                } else {
                    alert("Erreur lors de l'envoi des données.");
                }
            }
        });
    }

    // Lors de l'ouverture du modal
    $('#fournisseurModaladd').on('shown.bs.modal', function () {
        // Ajouter le backdrop
        $('body').append('<div class="modal-backdrop fade show"></div>');

        // Initialisation des champs
        remplirRubriquesTva('rubrique_tva');
        remplirContrePartie('contre_partie');
        $('#compte').focus();
        $('#rubrique_tva').val('').trigger('change');
        $('#designation').val('');
        // Générer un compte automatiquement au chargement
        genererCompteAuto();
    });

    // Suppression du backdrop à la fermeture du modal
    $('#fournisseurModaladd').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
    });

     $('#rubrique_tva').select2({
        dropdownParent: $('#fournisseurModaladd'),
        width: '100%',
        minimumResultsForSearch: 0,
        dropdownAutoWidth: true,
        // vos autres options...
    });
    // Remplir les rubriques TVA
    function remplirRubriquesTva(selectId, selectedValue = null) {
    $.ajax({
        url: '/get-rubriques-tva',
        type: 'GET',
        success: function (data) {
            const select = $('#' + selectId);

            // Réinitialisation de Select2 si déjà initialisé
            if (select.hasClass('select2-hidden-accessible')) {
                select.select2('destroy');
            }
            select.empty();
            select.append(new Option('Sélectionnez une Rubrique', ''));

            const excludedNumRacines = [147, 151, 152, 148, 144];

            // Parcours des catégories reçues
            data.categories.forEach(categoryObj => {
                // Afficher le nom de la catégorie (numérotée) une seule fois
                const catOption = new Option(categoryObj.categoryName, '', false, false);
                $(catOption).addClass('category').prop('disabled', true);
                select.append(catOption);

                // Sous-catégories (indentées)
                categoryObj.subCategories.forEach(sub => {
                    const subOption = new Option(`  ${sub}`, '', false, false);
                    $(subOption).addClass('subcategory').prop('disabled', true);
                    select.append(subOption);
                });

                // Rubriques associées
                categoryObj.rubriques.forEach(rubrique => {
                    if (!excludedNumRacines.includes(rubrique.Num_racines)) {
                        const text = `    ${rubrique.Num_racines}: ${rubrique.Nom_racines} : ${Math.round(rubrique.Taux)}%`;
                        const opt = new Option(text, rubrique.Num_racines);
                        $(opt).attr('data-search-text', `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.categoryName}`);
                        select.append(opt);
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
                    const el = $(data.element);
                    if (el.hasClass('category')) {
                        return $('<span style="font-weight:bold; padding-left:0;">' + data.text + '</span>');
                    }
                    if (el.hasClass('subcategory')) {
                        return $('<span style="font-weight:bold; padding-left:20px;">' + data.text + '</span>');
                    }
                    return $('<span>' + data.text + '</span>');
                },
                matcher: function (params, data) {
                    if ($.trim(params.term) === '') return data;
                    const searchText = $(data.element).data('search-text');
                    return searchText && searchText.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
                }

            });

            // Sélection initiale
            if (selectedValue) {
                select.val(selectedValue).trigger('change');
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Erreur lors de la récupération des rubriques TVA :', textStatus, errorThrown);
        }
    });
}

    // Remplir les options de contrepartie 1
    function remplirContrePartie(selectId, selectedValue = null) {
        $.ajax({
            url: '/comptes',
            type: 'GET',
            success: function (data) {
                var select = $("#" + selectId);
                if (select.hasClass("select2-hidden-accessible")) {
                    select.select2("destroy");
                }
                select.empty();
                select.append(new Option("Sélectionnez une contre partie", ""));
                data.sort((a, b) => a.compte.localeCompare(b.compte));
                data.forEach(function (compte) {
                    let option = new Option(`${compte.compte} - ${compte.intitule}`, compte.compte);
                    select.append(option);
                });
                select.select2({
                    width: '100%',
                    minimumResultsForSearch: 0,
                    dropdownAutoWidth: true
                });
                if (selectedValue) {
                    select.val(selectedValue).trigger('change');
                }
            }
        });
    }

    /* =========================================================
       AJOUT : Gestion dynamique des selects Rubrique TVA (+ / -)
       - Permet d'ajouter/supprimer plusieurs selects côte-à-côte
       - Concatène les valeurs pour sauvegarde (ex : 153/142)
       - Supporte le modal d'ajout (#fournisseurModaladd) et d'édition (#fournisseurModaledit)
       ========================================================= */

    // CSS minimal pour aligner correctement les groupes (injecté par JS pour ne rien toucher au HTML)
    if ($('#rubrique-tva-dynamic-styles').length === 0) {
        $('head').append(`
            <style id="rubrique-tva-dynamic-styles">
                .rubrique-tva-group { display: flex; gap: 6px; align-items: center; margin-bottom: 6px; }
                .rubrique-tva-actions { display: flex; gap: 4px; align-items: center; }
                .rubrique-tva-select { flex: 1; min-width: 200px; }
                .rubrique-tva-actions .btn { padding: .25rem .45rem; font-size: .85rem; }
            </style>
        `);
    }

    // Fonction utilitaire : initialise et remplit un <select> de rubrique (utilisable pour add/edit)
    function populateRubriqueSelect($select, selectedValue = null, $dropdownParent = $(document.body)) {
        $.ajax({
            url: '/get-rubriques-tva',
            type: 'GET',
            success: function (data) {
                $select.empty();
                $select.append(new Option('Sélectionnez une Rubrique', ''));

                const excludedNumRacines = [147, 151, 152, 148, 144];

                data.categories.forEach(function (categoryObj) {
                    const $catOpt = $('<option>').text(categoryObj.categoryName).prop('disabled', true).addClass('category');
                    $select.append($catOpt);

                    categoryObj.subCategories.forEach(function (sub) {
                        const $subOpt = $('<option>').text('  ' + sub).prop('disabled', true).addClass('subcategory');
                        $select.append($subOpt);
                    });

                    categoryObj.rubriques.forEach(function (rubrique) {
                        if (!excludedNumRacines.includes(rubrique.Num_racines)) {
                            const text = `    ${rubrique.Num_racines}: ${rubrique.Nom_racines} : ${Math.round(rubrique.Taux)}%`;
                            const $opt = $('<option>').val(rubrique.Num_racines).text(text).attr('data-search-text', `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.categoryName}`);
                            $select.append($opt);
                        }
                    });
                });

                // (Re)initialiser Select2
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    width: '100%',
                    minimumResultsForSearch: 0,
                    dropdownAutoWidth: true,
                    dropdownParent: $dropdownParent.length ? $dropdownParent : $(document.body),
                    templateResult: function (d) {
                        if (!d.id) return d.text;
                        const el = $(d.element);
                        if (el.hasClass('category')) return $('<span style="font-weight:bold;">' + d.text + '</span>');
                        if (el.hasClass('subcategory')) return $('<span style="padding-left:20px; font-weight:bold;">' + d.text + '</span>');
                        return $('<span>' + d.text + '</span>');
                    },
                    matcher: function (params, data) {
                        if ($.trim(params.term) === '') return data;
                        const searchText = $(data.element).data('search-text');
                        return searchText && searchText.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
                    }
                });

                if (selectedValue) {
                    $select.val(selectedValue).trigger('change');
                }
            },
            error: function () {
                console.error('Erreur lors de la récupération des rubriques TVA (populateRubriqueSelect)');
            }
        });
    }

    // Mettre à jour l'affichage des boutons de suppression (masquer si 1 seul groupe)
    function updateRemoveButtons($containerSelector) {
        var $container = $($containerSelector);
        var groups = $container.find('.rubrique-tva-group');
        if (groups.length <= 1) {
            groups.find('.remove-rubrique').hide();
        } else {
            groups.find('.remove-rubrique').show();
        }
    }

    // ----- ADD modal dynamic groups -----
    function ensureAddModalRubriqueContainer() {
        // Si container manquant, on le crée et on place le select existant (#rubrique_tva) dedans
        if ($('#rubriqueTvaRowsContainer').length === 0) {
            var $existing = $('#rubrique_tva');
            if ($existing.length === 0) return; // rien à faire

            // créer container et groupe
            var $container = $('<div id="rubriqueTvaRowsContainer"></div>');
            var $group = $('<div class="rubrique-tva-group"></div>');
            // assurer que le select conserve son id et son name (important pour le code existant)
            $existing.addClass('rubrique-tva-select').attr('name', 'rubrique_tva[]');
            $group.append($existing);
            var $actions = $('<div class="rubrique-tva-actions"></div>');
            // bouton + principal (unique)
            $actions.append('<button type="button" id="addRubriqueTvaBtn" class="btn btn-sm btn-success" title="Ajouter une rubrique"><i class="fas fa-plus"></i></button>');
            // bouton - (caché si unique)
            $actions.append('<button type="button" class="btn btn-sm btn-danger remove-rubrique" title="Supprimer cette rubrique" style="display:none;"><i class="fas fa-minus"></i></button>');
            $group.append($actions);
            // insérer avant le select initial dans le DOM si souhaité (on remplace visuellement)
            $existing.before($container);
            $container.append($group);
            updateRemoveButtons('#rubriqueTvaRowsContainer');
        }
    }

    // ajouter un groupe dans le add modal
    function createAddRubriqueGroup(selectedValue = null) {
        var $container = $('#rubriqueTvaRowsContainer');
        if ($container.length === 0) {
            // si container absent, on s'assure de sa présence puis on re-rappelle
            ensureAddModalRubriqueContainer();
            $container = $('#rubriqueTvaRowsContainer');
        }

        var uniqueId = 'rubrique_tva_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        var $select = $('<select class="form-select form-select-sm rubrique-tva-select" name="rubrique_tva[]" id="' + uniqueId + '"></select>');
        var $group = $('<div class="rubrique-tva-group"></div>');
        var $actions = $('<div class="rubrique-tva-actions"></div>');
        $actions.append('<button type="button" class="btn btn-sm btn-success add-rubrique" title="Ajouter"><i class="fas fa-plus"></i></button>');
        $actions.append('<button type="button" class="btn btn-sm btn-danger remove-rubrique" title="Supprimer"><i class="fas fa-minus"></i></button>');
        $group.append($select).append($actions);
        $container.append($group);

        // remplir le nouveau select
        populateRubriqueSelect($select, selectedValue, $('#fournisseurModaladd'));
        updateRemoveButtons('#rubriqueTvaRowsContainer');
        // focus sur le nouveau select (après initialisation)
        setTimeout(function() { $select.select2('open'); }, 250);
    }

    // ----- EDIT modal dynamic groups -----
    function ensureEditModalControls() {
        // Si bouton d'ajout edit absent, le créer à côté du container (non destructif)
        if ($('#addEditRubriqueTvaBtn').length === 0) {
            var $container = $('#editRubriqueTvaRowsContainer');
            if ($container.length === 0) {
                // si le container n'existe pas, créer un container minimal pour être sûr
                $container = $('<div id="editRubriqueTvaRowsContainer"></div>');
                // essayer de l'insérer dans le modal edit si présent
                $('#fournisseurModaledit .modal-body').first().append($container);
            }
            // créer bouton global d'ajout (utilisé par editFournisseur via click())
            var $btn = $('<button type="button" id="addEditRubriqueTvaBtn" class="btn btn-sm btn-success mb-2"><i class="fas fa-plus"></i> Ajouter Rubrique</button>');
            $container.before($btn);
        }
    }

    function createEditRubriqueGroup(selectedValue = null) {
        var $container = $('#editRubriqueTvaRowsContainer');
        if ($container.length === 0) {
            ensureEditModalControls();
            $container = $('#editRubriqueTvaRowsContainer');
        }

        var uniqueId = 'edit_rubrique_tva_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        var $select = $('<select class="form-select form-select-sm" name="edit_rubrique_tva[]" id="' + uniqueId + '"></select>');
        var $group = $('<div class="rubrique-tva-group"></div>');
        var $actions = $('<div class="rubrique-tva-actions"></div>');
        $actions.append('<button type="button" class="btn btn-sm btn-success add-edit-rubrique" title="Ajouter"><i class="fas fa-plus"></i></button>');
        $actions.append('<button type="button" class="btn btn-sm btn-danger remove-edit-rubrique" title="Supprimer"><i class="fas fa-minus"></i></button>');
        $group.append($select).append($actions);
        $container.append($group);

        populateRubriqueSelect($select, selectedValue, $('#fournisseurModaledit'));
        updateRemoveButtons('#editRubriqueTvaRowsContainer');
        setTimeout(function() { $select.select2('open'); }, 250);
    }

    // evenements délégués pour add modal (ajout/suppression)
    $(document).on('click', '#addRubriqueTvaBtn', function (e) {
        e.preventDefault();
        createAddRubriqueGroup(null);
    });

    // delegation for dynamically created '+' buttons in add modal
    $(document).on('click', '.add-rubrique', function (e) {
        e.preventDefault();
        createAddRubriqueGroup(null);
    });

    // suppression groupe add modal
    $(document).on('click', '#rubriqueTvaRowsContainer .remove-rubrique, .rubrique-tva-group .remove-rubrique', function (e) {
        e.preventDefault();
        var $grp = $(this).closest('.rubrique-tva-group');
        var $container = $('#rubriqueTvaRowsContainer');
        if ($grp.length && $container.length) {
            $grp.remove();
            updateRemoveButtons('#rubriqueTvaRowsContainer');
        }
    });

    // evenements pour edit modal
    $(document).on('click', '#addEditRubriqueTvaBtn', function (e) {
        e.preventDefault();
        createEditRubriqueGroup(null);
    });

    $(document).on('click', '.add-edit-rubrique', function (e) {
        e.preventDefault();
        createEditRubriqueGroup(null);
    });

    // suppression groupe edit modal
    $(document).on('click', '#editRubriqueTvaRowsContainer .remove-edit-rubrique', function (e) {
        e.preventDefault();
        var $grp = $(this).closest('.rubrique-tva-group');
        var $container = $('#editRubriqueTvaRowsContainer');
        if ($grp.length && $container.length) {
            $grp.remove();
            updateRemoveButtons('#editRubriqueTvaRowsContainer');
        }
    });

    // Lors de l'ouverture du modal d'ajout : s'assurer du container dynamique (on ne remplace rien du code existant)
    $('#fournisseurModaladd').on('shown.bs.modal', function () {
        ensureAddModalRubriqueContainer();
        updateRemoveButtons('#rubriqueTvaRowsContainer');
    });

    // Lors de l'ouverture du modal d'édition : s'assurer des contrôles (si editFournisseur click() programmatique se produira, notre bouton existe)
    $('#fournisseurModaledit').on('shown.bs.modal', function () {
        ensureEditModalControls();
        updateRemoveButtons('#editRubriqueTvaRowsContainer');
    });

    // Au chargement initial, s'assurer que si l'édition est possible, les boutons existent
    ensureEditModalControls();

    // Si l'utilisateur soumet le formulaire d'ajout, la fonction getConcatenatedRubriquesTva() (définie plus haut) concaténera correctement les selects créés dynamiquement car
    // nous avons conservé le name="rubrique_tva[]" sur chaque select ajouté.
    //
    // De même, pour l'édition, le code existant sur #fournisseurFormEdit collecte select[name="edit_rubrique_tva[]"], donc nos selects d'édition utilisent ce name.

    /* Fin de la partie ajout/suppression dynamique */
/*
  fichier: fournisseurs-rubriques-managed.js
  - Version complète corrigée pour :
    * rubriques TVA dynamiques (2x2)
    * limite 3 selects
    * Select2 init & open safe
    * ajout auto plan_comptable (best-effort)
    * prevention double submit
    * gestion réponses serveur JSON/HTML
    * focus fix pour modals (aria-hidden warning)
    * pas de backdrop injecté manuellement
  - Dépendances: jQuery, Select2, SweetAlert2 (optionnel, fallback alert)
*/

(function($){
  'use strict';

  /* ===========================
     CONFIG / CSS injection
     =========================== */
  if ($('#rubrique-tva-2col-styles').length === 0) {
    $('head').append(`
      <style id="rubrique-tva-2col-styles">
        /* container flex wrap -> deux groupes par ligne, mobile 1 colonne */
        #rubriqueTvaRowsContainer, #editRubriqueTvaRowsContainer {
          display:flex;
          flex-wrap:wrap;
          gap:12px;
        }
        .rubrique-tva-group {
          width: calc(50% - 6px);
          display:flex;
          gap:8px;
          align-items:center;
          box-sizing:border-box;
          min-width:0;
          margin-bottom:6px;
        }
        .rubrique-tva-select,
        .rubrique-tva-group .form-select,
        .rubrique-tva-group .select2-container {
          flex: 1 1 auto;
          min-width: 0;
          width: auto !important; /* override select2 inline width */
        }
        .rubrique-tva-actions {
          flex: 0 0 auto;
          display:flex;
          gap:6px;
          align-items:center;
          white-space:nowrap;
        }
        .rubrique-tva-actions .btn {
          padding: .25rem .45rem;
          font-size: .85rem;
          line-height:1;
        }
        @media (max-width:576px) {
          .rubrique-tva-group { width:100%; }
        }
      </style>
    `);
  }

  /* ===========================
     Helper: SweetAlert fallback
     =========================== */
  function swalAlert(opts){
    if (typeof Swal !== 'undefined' && Swal.fire) {
      return Swal.fire(opts);
    }
    // fallback minimal synchronous
    if (opts.icon === 'warning') alert(opts.text || opts.title || 'Attention');
    else if (opts.icon === 'success') alert(opts.title || 'Succès');
    else if (opts.icon === 'error') alert(opts.title || 'Erreur');
    return Promise.resolve();
  }

  /* ===========================
     Helper: count/selects & UI updates
     =========================== */
  function countSelects(containerSelector){
    var $c = $(containerSelector);
    if (!$c.length) return 0;
    return $c.find('.rubrique-tva-group').length;
  }

  function updateRemoveButtons(containerSelector){
    var $cont = $(containerSelector);
    var groups = $cont.find('.rubrique-tva-group');
    if (groups.length <= 1) groups.find('.remove-rubrique, .remove-edit-rubrique').hide();
    else groups.find('.remove-rubrique, .remove-edit-rubrique').show();
  }

  function disableAddIfLimit(containerSelector){
    var cnt = countSelects(containerSelector);
    var disabled = cnt >= 3;
    if (containerSelector === '#rubriqueTvaRowsContainer') {
      $('#addRubriqueTvaBtn, .add-rubrique').prop('disabled', disabled);
    } else {
      $('#addEditRubriqueTvaBtn, .add-edit-rubrique').prop('disabled', disabled);
    }
  }

  /* ===========================
     populateRubriqueSelect
     - Remplit un <select> avec rubriques de /get-rubriques-tva
     - afterInit callback optionnel (ex: ouvrir la dropdown)
     =========================== */
  function populateRubriqueSelect($select, selectedValue = null, $dropdownParent = $(document.body), afterInit){
    $.ajax({
      url: '/get-rubriques-tva',
      type: 'GET',
      success: function(data){
        $select.empty().append(new Option('Sélectionnez une Rubrique', ''));

        const excluded = [147,151,152,148,144];
        (data.categories || []).forEach(function(cat){
          var $cat = $('<option>').text(cat.categoryName).prop('disabled', true).addClass('category');
          $select.append($cat);
          (cat.subCategories || []).forEach(function(sub){
            var $sub = $('<option>').text('  ' + sub).prop('disabled', true).addClass('subcategory');
            $select.append($sub);
          });
          (cat.rubriques || []).forEach(function(r){
            if (!excluded.includes(r.Num_racines)) {
              var text = `    ${r.Num_racines}: ${r.Nom_racines} : ${Math.round(r.Taux)}%`;
              var $opt = $('<option>').val(r.Num_racines).text(text).attr('data-search-text', `${r.Num_racines} ${r.Nom_racines} ${cat.categoryName}`);
              $select.append($opt);
            }
          });
        });

        // (Re)destroy si existant
        try { if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy'); } catch(e){}

        // Init select2
        var parent = ($dropdownParent && $dropdownParent.length) ? $dropdownParent : $(document.body);
        $select.select2({
          width: '100%',
          minimumResultsForSearch: 0,
          dropdownAutoWidth: true,
          dropdownParent: parent,
          templateResult: function(d){
            if (!d.id) return d.text;
            var el = $(d.element);
            if (el.hasClass('category')) return $('<span style="font-weight:bold;">' + d.text + '</span>');
            if (el.hasClass('subcategory')) return $('<span style="padding-left:20px; font-weight:bold;">' + d.text + '</span>');
            return $('<span>' + d.text + '</span>');
          },
          matcher: function(params, data){
            if ($.trim(params.term) === '') return data;
            var s = $(data.element).data('search-text') || '';
            return s.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
          }
        });

        if (selectedValue) $select.val(selectedValue).trigger('change');

        // callback after init
        if (afterInit && typeof afterInit === 'function') {
          try { afterInit($select); } catch(e){ console.warn('afterInit callback error', e); }
        }
      },
      error: function(jqXHR, textStatus, errorThrown){
        console.error('Erreur get-rubriques-tva:', textStatus, errorThrown);
      }
    });
  }

  /* ===========================
     Utility: waitAndOpen (safe select2('open'))
     =========================== */
  function waitAndOpen($sel){
    (function tryOpen(){
      var tries = 0;
      var interval = setInterval(function(){
        tries++;
        if ($sel.hasClass('select2-hidden-accessible')) {
          try { $sel.select2('open'); } catch(e){ /* ignore */ }
          clearInterval(interval);
        } else if (tries > 20) { // ~2s timeout
          clearInterval(interval);
        }
      }, 100);
    })();
  }

  /* ===========================
     Ensure containers (add/edit)
     =========================== */
  function ensureAddContainer(){
    if ($('#rubriqueTvaRowsContainer').length === 0) {
      var $container = $('<div id="rubriqueTvaRowsContainer"></div>');
      // Try to place smartly: just before the contre_partie column or in modal body
      var $targetRow = $('#fournisseurModaladd .modal-body').find('.row').filter(function(){
        return $(this).find('#rubrique_tva').length || $(this).find('#contre_partie').length;
      }).first();
      if ($targetRow.length) $targetRow.before($container);
      else $('#fournisseurModaladd .modal-body').prepend($container);

      var $group = $('<div class="rubrique-tva-group"></div>');
      var $sel = $('<select class="form-select form-select-sm rubrique-tva-select" id="rubrique_tva" name="rubrique_tva[]"><option value="">Sélectionnez une Rubrique</option></select>');
      var $actions = $('<div class="rubrique-tva-actions"></div>');
      $actions.append('<button type="button" id="addRubriqueTvaBtn" class="btn btn-outline-primary btn-sm"><i class="fas fa-plus"></i></button>');
      $actions.append('<button type="button" class="btn btn-outline-danger btn-sm remove-rubrique" style="display:none;"><i class="fas fa-minus"></i></button>');
      $group.append($sel).append($actions);
      $container.append($group);
      populateRubriqueSelect($sel, null, $('#fournisseurModaladd'));
    }
  }

  function ensureEditContainer(){
    if ($('#editRubriqueTvaRowsContainer').length === 0) {
      var $container = $('<div id="editRubriqueTvaRowsContainer"></div>');
      var $targetRow = $('#fournisseurModaledit .modal-body').find('.row').filter(function(){
        return $(this).find('#editRubriqueTVA').length || $(this).find('#editContrePartie').length;
      }).first();
      if ($targetRow.length) $targetRow.before($container);
      else $('#fournisseurModaledit .modal-body').prepend($container);

      var $group = $('<div class="rubrique-tva-group"></div>');
      var $sel = $('<select class="form-select form-select-sm rubrique-tva-select" id="editRubriqueTVA" name="edit_rubrique_tva[]"><option value="">Sélectionnez une Rubrique</option></select>');
      var $actions = $('<div class="rubrique-tva-actions"></div>');
      $actions.append('<button type="button" id="addEditRubriqueTvaBtn" class="btn btn-outline-primary btn-sm"><i class="fas fa-plus"></i></button>');
      $actions.append('<button type="button" class="btn btn-outline-danger btn-sm remove-edit-rubrique" style="display:none;"><i class="fas fa-minus"></i></button>');
      $group.append($sel).append($actions);
      $container.append($group);
      populateRubriqueSelect($sel, null, $('#fournisseurModaledit'));
    }
  }

  /* ===========================
     createAddRubriqueGroup / createEditRubriqueGroup
     - limit 3
     - after populate -> open select2 safely
     =========================== */
  function createAddRubriqueGroup(selectedValue){
    ensureAddContainer();
    if (countSelects('#rubriqueTvaRowsContainer') >= 3) {
      swalAlert({ icon:'warning', title:'Limite atteinte', text:'Vous ne pouvez ajouter que 3 rubriques maximum.' });
      return;
    }
    var uid = 'rubrique_tva_' + Date.now() + Math.floor(Math.random()*1000);
    var $select = $('<select class="form-select form-select-sm rubrique-tva-select" name="rubrique_tva[]" id="'+uid+'"></select>');
    var $group = $('<div class="rubrique-tva-group"></div>');
    var $actions = $('<div class="rubrique-tva-actions"></div>');
    $actions.append('<button type="button" class="btn btn-outline-primary btn-sm add-rubrique"><i class="fas fa-plus"></i></button>');
    $actions.append('<button type="button" class="btn btn-outline-danger btn-sm remove-rubrique"><i class="fas fa-minus"></i></button>');
    $group.append($select).append($actions);
    $('#rubriqueTvaRowsContainer').append($group);

    populateRubriqueSelect($select, selectedValue, $('#fournisseurModaladd'), function($s){
      // open safely
      try { $s.select2('open'); } catch(e){ waitAndOpen($s); }
      updateRemoveButtons('#rubriqueTvaRowsContainer');
      disableAddIfLimit('#rubriqueTvaRowsContainer');
    });
  }

  function createEditRubriqueGroup(selectedValue){
    ensureEditContainer();
    if (countSelects('#editRubriqueTvaRowsContainer') >= 3) {
      swalAlert({ icon:'warning', title:'Limite atteinte', text:'Vous ne pouvez ajouter que 3 rubriques maximum.' });
      return;
    }
    var uid = 'edit_rubrique_tva_' + Date.now() + Math.floor(Math.random()*1000);
    var $select = $('<select class="form-select form-select-sm rubrique-tva-select" name="edit_rubrique_tva[]" id="'+uid+'"></select>');
    var $group = $('<div class="rubrique-tva-group"></div>');
    var $actions = $('<div class="rubrique-tva-actions"></div>');
    $actions.append('<button type="button" class="btn btn-outline-primary btn-sm add-edit-rubrique"><i class="fas fa-plus"></i></button>');
    $actions.append('<button type="button" class="btn btn-outline-danger btn-sm remove-edit-rubrique"><i class="fas fa-minus"></i></button>');
    $group.append($select).append($actions);
    $('#editRubriqueTvaRowsContainer').append($group);

    populateRubriqueSelect($select, selectedValue, $('#fournisseurModaledit'), function($s){
      try { $s.select2('open'); } catch(e){ waitAndOpen($s); }
      updateRemoveButtons('#editRubriqueTvaRowsContainer');
      disableAddIfLimit('#editRubriqueTvaRowsContainer');
    });
  }

  /* ===========================
     Delegated events for add/edit groups
     =========================== */
  $(document).off('click', '#addRubriqueTvaBtn').on('click', '#addRubriqueTvaBtn', function(e){ e.preventDefault(); createAddRubriqueGroup(null); });
  $(document).off('click', '.add-rubrique').on('click', '.add-rubrique', function(e){ e.preventDefault(); createAddRubriqueGroup(null); });
  $(document).off('click', '.remove-rubrique').on('click', '.remove-rubrique', function(e){
    e.preventDefault();
    var $g = $(this).closest('.rubrique-tva-group');
    if ($g.length) { $g.remove(); updateRemoveButtons('#rubriqueTvaRowsContainer'); disableAddIfLimit('#rubriqueTvaRowsContainer'); }
  });

  $(document).off('click', '#addEditRubriqueTvaBtn').on('click', '#addEditRubriqueTvaBtn', function(e){ e.preventDefault(); createEditRubriqueGroup(null); });
  $(document).off('click', '.add-edit-rubrique').on('click', '.add-edit-rubrique', function(e){ e.preventDefault(); createEditRubriqueGroup(null); });
  $(document).off('click', '.remove-edit-rubrique').on('click', '.remove-edit-rubrique', function(e){
    e.preventDefault();
    var $g = $(this).closest('.rubrique-tva-group');
    if ($g.length) { $g.remove(); updateRemoveButtons('#editRubriqueTvaRowsContainer'); disableAddIfLimit('#editRubriqueTvaRowsContainer'); }
  });

  /* ===========================
     Helpers: concatenation rubriques (add & edit)
     =========================== */
  function getConcatenatedRubriquesTva(){
    return $('select[name="rubrique_tva[]"]').map(function(){ return $(this).val()||''; }).get().filter(v=>v!=='').join('/');
  }
  function getEditConcatenatedRubriquesTva(){
    return $('select[name="edit_rubrique_tva[]"]').map(function(){ return $(this).val()||''; }).get().filter(v=>v!=='').join('/');
  }

  /* ===========================
     Init on modal shown: ensure containers, select2 contre_partie, focus safe
     =========================== */
  $('#fournisseurModaladd').on('shown.bs.modal', function(){
    ensureAddContainer();
    // init contre_partie select2 with modal as parent
    try {
      if ($('#contre_partie').length) {
        if ($('#contre_partie').hasClass('select2-hidden-accessible')) $('#contre_partie').select2('destroy');
        $('#contre_partie').select2({ width:'100%', dropdownParent: $('#fournisseurModaladd'), minimumResultsForSearch:0, dropdownAutoWidth:true });
      }
    } catch(e){ console.warn('contre_partie select2 init err', e); }
    updateRemoveButtons('#rubriqueTvaRowsContainer');
    disableAddIfLimit('#rubriqueTvaRowsContainer');

    // focus safe
    setTimeout(function(){
      var $first = $('#fournisseurModaladd').find('input:visible, select:visible, button:visible').not('.btn-close').first();
      try { if ($first && $first.length) $first.focus(); } catch(e){}
    }, 80);
  });

  $('#fournisseurModaledit').on('shown.bs.modal', function(){
    ensureEditContainer();
    try {
      if ($('#editContrePartie').length) {
        if ($('#editContrePartie').hasClass('select2-hidden-accessible')) $('#editContrePartie').select2('destroy');
        $('#editContrePartie').select2({ width:'100%', dropdownParent: $('#fournisseurModaledit'), minimumResultsForSearch:0, dropdownAutoWidth:true });
      }
    } catch(e){ console.warn('editContrePartie select2 init err', e); }
    updateRemoveButtons('#editRubriqueTvaRowsContainer');
    disableAddIfLimit('#editRubriqueTvaRowsContainer');

    // focus safe
    setTimeout(function(){
      var $first = $('#fournisseurModaledit').find('input:visible, select:visible, button:visible').not('.btn-close').first();
      try { if ($first && $first.length) $first.focus(); } catch(e){}
    }, 80);
  });

  /* ===========================
     ADD form submit (prevent double, plan_comptable auto)
     =========================== */
  (function(){
    var isSubmittingAdd = false;
    $('#fournisseurFormAdd').off('submit').on('submit', function(e){
      e.preventDefault();
      if (isSubmittingAdd) return;
      var $form = $(this);
      var compte = $.trim($('#compte').val()||'');
      if (!compte) { $('#compte').focus(); return; }

      // fill designation if empty
      if ($('#designation').length && !$('#designation').val()) {
        var cpText = $('#contre_partie option:selected').text() || '';
        var intitule = cpText.split('-')[1]?.trim();
        if (intitule) $('#designation').val(intitule);
      }

      var rubriques = getConcatenatedRubriquesTva();
      var payload = {
        compte: compte,
        intitule: $.trim($('#intitule').val()||''),
        identifiant_fiscal: $.trim($('#identifiant_fiscal').val()||''),
        ICE: $.trim($('#ICE').val()||''),
        nature_operation: $.trim($('#nature_operation').val()||''),
        rubrique_tva: rubriques,
        designation: $.trim($('#designation').val()||''),
        contre_partie: $.trim($('#contre_partie').val()||''),
        societe_id: $('#societe_id').val(),
        RC: $('#RC').val(),
        rc: $('#RC').val(),
        ville: $('#ville').val(),
        adresse: $('#adresse').val(),
        delai_p: $('#delai_p').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
      };

      isSubmittingAdd = true;
      $form.find('button[type="submit"]').prop('disabled', true);

      $.ajax({
        url: '/fournisseurs',
        type: 'POST',
        data: payload,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        dataType: 'json',
        timeout: 15000,
        success: function(resp){
          isSubmittingAdd = false;
          $form.find('button[type="submit"]').prop('disabled', false);

          if (resp && (resp.success === true || resp.message || resp.fournisseur)) {
            if (typeof table !== 'undefined' && table !== null) table.setData('/fournisseurs/data');

            // ajout plan_comptable (best-effort)
            $.ajax({
              url: '/plancomptable',
              type: 'POST',
              data: {
                compte: payload.compte,
                intitule: payload.intitule,
                societe_id: payload.societe_id,
                _token: $('meta[name="csrf-token"]').attr('content')
              },
              headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              success: function(planResp){ /* optional */ },
              error: function(xhr){ console.error('Erreur plancomptable add', xhr.status, xhr.responseText); }
            });

            $('#fournisseurModaladd').modal('hide');
            $form[0].reset();
            $form.find('select').val('').trigger('change');
            swalAlert({ icon:'success', title:'Fournisseur ajouté', text: (resp.message) ? resp.message : 'Le fournisseur a été créé.' });
          } else {
            swalAlert({ icon:'error', title:'Erreur', text: (resp && resp.error) ? resp.error : 'Erreur lors de l\'ajout.' });
          }
        },
        error: function(xhr, textStatus){
          isSubmittingAdd = false;
          $form.find('button[type="submit"]').prop('disabled', false);
          console.error('Erreur add fournisseur:', xhr.status, textStatus, xhr.responseText);
          if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            swalAlert({ icon:'warning', title:'Validation', text: JSON.stringify(xhr.responseJSON.errors) });
          } else if (xhr.status === 419 || xhr.status === 401) {
            swalAlert({ icon:'error', title:'Session', text:'Session expirée. Recharge la page.' });
          } else {
            swalAlert({ icon:'error', title:'Erreur', text:'Erreur lors de l\'envoi des données (voir console).' });
          }
        }
      });

    });
  })();

  /* ===========================
     EDIT form submit (improved)
     =========================== */
  (function(){
    var isSubmittingEdit = false;
    $('#fournisseurFormEdit').off('submit').on('submit', function(e){
      e.preventDefault();
      if (isSubmittingEdit) return;
      var $form = $(this);
      var id = $.trim($('#editFournisseurId').val() || '');
      if (!id) { swalAlert({ icon:'error', title:'Erreur', text:'ID manquant.' }); return; }

      // safe designation fill
      var editDesignationVal = ($('#editDesignation').length) ? $.trim($('#editDesignation').val() || '') : '';
      var editContreVal = ($('#editContrePartie').length) ? $.trim($('#editContrePartie').val() || '') : '';
      if (!editDesignationVal && $('#editContrePartie').length) {
        var cpText = $('#editContrePartie option:selected').text() || '';
        var intitule = (cpText.indexOf('-') !== -1) ? cpText.split('-')[1].trim() : cpText.trim();
        if (intitule) editDesignationVal = intitule;
        if ($('#editDesignation').length) $('#editDesignation').val(editDesignationVal);
      }

      var rubriques = getEditConcatenatedRubriquesTva();
      var payload = {
        _method: 'PUT',
        compte: $.trim($('#editCompte').val() || ''),
        intitule: $.trim($('#editIntitule').val() || ''),
        identifiant_fiscal: $.trim($('#editIdentifiantFiscal').val() || ''),
        ICE: $.trim($('#editICE').val() || ''),
        nature_operation: $.trim($('#editNatureOperation').val() || ''),
        rubrique_tva: rubriques,
        designation: editDesignationVal,
        contre_partie: editContreVal,
        RC: $.trim($('#editRC').val() || ''),
        rc: $.trim($('#editRC').val() || ''),
        ville: $.trim($('#editVille').val() || ''),
        adresse: $.trim($('#editAdresse').val() || ''),
        delai_p: $.trim($('#editDelaiP').val() || ''),
        _token: $('meta[name="csrf-token"]').attr('content')
      };

      console.log('Edit payload ->', payload);

      isSubmittingEdit = true;
      $form.find('button[type="submit"]').prop('disabled', true);

      $.ajax({
        url: '/fournisseurs/' + encodeURIComponent(id),
        type: 'POST',
        data: payload,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        dataType: 'json',
        timeout: 15000,
        success: function(resp){
          isSubmittingEdit = false;
          $form.find('button[type="submit"]').prop('disabled', false);

          var isOk = (resp && (resp.success === true || resp.message || resp.fournisseur));
          if (isOk) {
            if (typeof table !== 'undefined' && table !== null) table.setData('/fournisseurs/data');

            // update plan_comptable best-effort
            $.ajax({
              url: '/plancomptable',
              type: 'POST',
              data: {
                compte: payload.compte,
                intitule: payload.intitule,
                societe_id: $('#societe_id').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
              },
              headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              success: function(){},
              error: function(xhr){ console.error('Erreur plancomptable edit', xhr.status, xhr.responseText); }
            });

            $('#fournisseurModaledit').modal('hide');
            $form[0].reset();
            $('#editCompte').prop('disabled', false);
            var serverMsg = (resp && resp.message) ? resp.message : 'Le fournisseur a été mis à jour.';
            swalAlert({ icon:'success', title:'Modifié', text: serverMsg });
          } else {
            console.warn('Réponse edit inattendue :', resp);
            swalAlert({ icon:'error', title:'Erreur', text: (resp && resp.error) ? resp.error : 'Erreur lors de la modification.' });
          }
        },
        error: function(xhr, textStatus, errorThrown){
          isSubmittingEdit = false;
          $form.find('button[type="submit"]').prop('disabled', false);

          var contentType = xhr.getResponseHeader && xhr.getResponseHeader('Content-Type') || '';
          console.error('Edit request failed:', xhr.status, textStatus, errorThrown);
          console.error('ResponseText (start):', (xhr.responseText || '').slice(0,1000));

          if (xhr.status === 419 || xhr.status === 401) {
            swalAlert({ icon:'error', title:'Session', text:'Session expirée ou non authentifiée. Recharge la page.' });
            return;
          }

          try {
            if (contentType.indexOf('application/json') !== -1 && xhr.responseJSON) {
              if (xhr.status === 422 && xhr.responseJSON.errors) {
                swalAlert({ icon:'warning', title:'Validation', text: JSON.stringify(xhr.responseJSON.errors) });
              } else {
                swalAlert({ icon:'error', title:'Erreur serveur', text: xhr.responseJSON.message || JSON.stringify(xhr.responseJSON) });
              }
              return;
            }
          } catch(e){ console.warn('JSON parse error', e); }

          if (typeof xhr.responseText === 'string' && xhr.responseText.trim().startsWith('<')) {
            swalAlert({ icon:'error', title:'Erreur serveur (HTML)', text: 'Le serveur a renvoyé une page HTML. Voir console (Network).' });
            console.error('Response HTML (début):', xhr.responseText.slice(0,2000));
            return;
          }

          swalAlert({ icon:'error', title:'Erreur', text: 'Erreur lors de l\'enregistrement. Voir console.' });
        }
      });

    });
  })();

  /* ===========================
     Safety init: ensure base selects are populated on first load
     =========================== */
  $(function(){
    try {
      if ($('#rubrique_tva').length && $('#rubrique_tva').find('option').length <= 1) populateRubriqueSelect($('#rubrique_tva'), null, $('#fournisseurModaladd'));
    } catch(e){}
    try {
      if ($('#editRubriqueTVA').length && $('#editRubriqueTVA').find('option').length <= 1) populateRubriqueSelect($('#editRubriqueTVA'), null, $('#fournisseurModaledit'));
    } catch(e){}
    // remplir contre_partie (utilise fonction si déjà existante)
    if (typeof remplirContrePartie === 'function') {
      try { remplirContrePartie('contre_partie', null); remplirContrePartie('editContrePartie', null); } catch(e){}
    } else {
      if ($('#contre_partie').length || $('#editContrePartie').length) {
        $.ajax({ url:'/comptes', type:'GET', success:function(data){
          (data||[]).sort((a,b)=> a.compte.localeCompare(b.compte)).forEach(function(c){
            if ($('#contre_partie').length) $('#contre_partie').append(new Option(`${c.compte} - ${c.intitule}`, c.compte));
            if ($('#editContrePartie').length) $('#editContrePartie').append(new Option(`${c.compte} - ${c.intitule}`, c.compte));
          });
          try{ if ($('#contre_partie').length) $('#contre_partie').select2({ width:'100%', dropdownParent: $('#fournisseurModaladd') }); } catch(e){}
          try{ if ($('#editContrePartie').length) $('#editContrePartie').select2({ width:'100%', dropdownParent: $('#fournisseurModaledit') }); } catch(e){}
        }});
      }
    }
  });

  /* ===========================
     Normalize existing DOM groups (fix buttons under selects)
     - run on ready and modal open
     =========================== */
  function normalizeRubriqueGroups() {
    // for add modal
    $('#rubriqueTvaRowsContainer .rubrique-tva-group, #rubriqueTvaRowsContainer > .d-flex, #rubriqueTvaRowsContainer > div').each(function(){
      var $g = $(this);
      $g.addClass('rubrique-tva-group');
      var $sel = $g.find('select').first();
      if ($sel.length) $sel.addClass('rubrique-tva-select form-select form-select-sm');
      var $btns = $g.find('button').not('.ignore-action');
      if ($btns.length && $g.children('.rubrique-tva-actions').length === 0) {
        var $actions = $('<div class="rubrique-tva-actions"></div>');
        $btns.each(function(){ $actions.append($(this)); });
        $g.append($actions);
      }
    });

    // for edit modal
    $('#editRubriqueTvaRowsContainer .rubrique-tva-group, #editRubriqueTvaRowsContainer > div').each(function(){
      var $g = $(this);
      $g.addClass('rubrique-tva-group');
      var $sel = $g.find('select').first();
      if ($sel.length) $sel.addClass('rubrique-tva-select form-select form-select-sm');
      var $btns = $g.find('button').not('.ignore-action');
      if ($btns.length && $g.children('.rubrique-tva-actions').length === 0) {
        var $actions = $('<div class="rubrique-tva-actions"></div>');
        $btns.each(function(){ $actions.append($(this)); });
        $g.append($actions);
      }
    });

    // re-init select2 widths if needed
    try {
      $('.rubrique-tva-select').each(function(){
        var $s = $(this);
        if ($s.hasClass('select2-hidden-accessible')) {
          $s.select2('destroy');
          $s.select2({ width:'100%', dropdownParent: $s.closest('.modal'), minimumResultsForSearch:0 });
        }
      });
    } catch(e){ /* ignore */ }

    updateRemoveButtons('#rubriqueTvaRowsContainer');
    updateRemoveButtons('#editRubriqueTvaRowsContainer');
    disableAddIfLimit('#rubriqueTvaRowsContainer');
    disableAddIfLimit('#editRubriqueTvaRowsContainer');
  }

  $(document).ready(normalizeRubriqueGroups);
  $('#fournisseurModaladd, #fournisseurModaledit').on('shown.bs.modal', normalizeRubriqueGroups);

  /* ===========================
     IMPORTANT: do not add or remove .modal-backdrop manually anywhere!
     If you did earlier, remove that code.
     =========================== */

})(jQuery);


/* ---------- FIN PATCH ---------- */


    // Générer un compte automatiquement
    function genererCompteAuto() {
        $.ajax({
            url: `/get-next-compte/${societeId}`,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    $('#compte').val(response.nextCompte);
                } else {
                    alert('Erreur lors de la génération du compte.');
                }
            }
        });
    }

    // Evénement pour auto-incrémenter le compte
    $('#autoIncrementBtn').on('click', function () {
        genererCompteAuto();
    });

    // Evénement pour changer la société et mettre à jour les paramètres
    $('#societe_id').on('change', function () {
        societeId = $(this).val(); // Mettre à jour l'ID de la société sélectionnée
        nombreChiffresCompte = parseInt($('#nombre_chiffre_compte').val()); // Mettre à jour la configuration
        // Mettre à jour la longueur maximale du champ "compte"
        $('#compte').attr('maxlength', nombreChiffresCompte);
        genererCompteAuto(); // Régénérer le compte
    });

    // Validation des champs ICE et identifiant_fiscal
    $("#ICE, #identifiant_fiscal").on("input", function () {
        this.value = this.value.replace(/[^0-9]/g, ''); // Supprimer tout sauf les chiffres
        if (this.value.length > 15) {
            this.value = this.value.slice(0, 15); // Limiter à 15 caractères
        }
    });
});


// gestion bouton plan
$(document).ready(function () {
    // Gestion de la soumission du formulaire d'ajout
    $('#planComptableFormAdd').on('submit', function (e) {
        e.preventDefault(); // Empêche la soumission classique

        // Récupération des données
        const compte = $('#compte_add').val().trim();
        const intitule = $('#intitule_add').val().trim();
        const societeId = $('#societe_id').val();

        if (!compte || !intitule) {
            alert("Veuillez remplir tous les champs obligatoires.");
            return;
        }

        // Requête AJAX pour ajouter un nouveau compte
        $.ajax({
            url: '/plancomptable', // Route définie dans votre contrôleur Laravel
            type: 'POST',
            data: {
                compte: compte,
                intitule: intitule,
                societe_id: societeId,
                _token: '{{ csrf_token() }}' // Protection CSRF
            },
            beforeSend: function () {
                // Désactiver le bouton pour éviter les doubles soumissions
                $('#planComptableFormAdd button[type="submit"]').prop('disabled', true).text('Ajout en cours...');
            },
            success: function (response) {
                if (response.success && response.data) {
                    const newOption = new Option(
                        `${response.data.compte} - ${response.data.intitule}`,
                        response.data.compte,
                        true,
                        true
                    );
                    $('#contre_partie').append(newOption).trigger('change'); // Ajouter le nouveau compte au select

                    // Fermer le modal et réinitialiser le formulaire
                    $('#planComptableModalAdd').modal('hide');
                    $('#planComptableFormAdd')[0].reset();

                    alert(response.message || "Compte ajouté avec succès !");
                } else {
                    alert("Erreur : " + (response.message || "Réponse inattendue du serveur."));
                }
            },
            error: function (xhr) {
                console.error("Erreur :", xhr.responseText);
                alert("Une erreur est survenue. Veuillez réessayer.");
            },
            complete: function () {
                $('#planComptableFormAdd button[type="submit"]').prop('disabled', false).text('Ajouter');
            }
        });
    });

    // Réinitialisation du formulaire
    $('#resetModal').on('click', function () {
        $('#planComptableFormAdd')[0].reset();
    });

    // Gestion de l'ouverture du modal depuis le lien "Ajouter un compte"
    $('#ajouterCompteLink').on('click', function (e) {
        e.preventDefault();
        $('#planComptableFormAdd')[0].reset();
        $('#planComptableModalAdd').modal('show');
    });

    // Gestion de l'ouverture du modal depuis le menu déroulant "contre_partie"
    $('#contre_partie').on('change', function () {
        const selectedValue = $(this).val();
        if (selectedValue === 'add_new') {
            $('#planComptableFormAdd')[0].reset();
            $('#planComptableModalAdd').modal('show');
            $(this).val('').trigger('change');
        }
    });
});

$(document).ready(function() {
    $("#resetFormBtn").on("click", function(e) {
        e.preventDefault();
        // Réinitialise le formulaire entier
        document.getElementById("fournisseurFormAdd").reset();
        // Masquer le message d'erreur si présent
        $("#compte-error").hide();
    });

});

$(document).ready(function() {
    $("#resetFormBtn").on("click", function(e) {
        e.preventDefault(); // Empêche toute action par défaut
        document.getElementById("fournisseurFormEdit").reset(); // Réinitialise le formulaire
        // Si vous avez des messages d'erreur ou d'autres éléments à masquer, vous pouvez le faire ici :
        // $("#edit-compte-error").hide();
    });
});


/////////////////////////////verification compte ////////////////////////////////
document.addEventListener('DOMContentLoaded', function() {
  const nombreChiffreCompte = parseInt(document.getElementById('nombre_chiffre_compte').value) || 0;
  const compteInput = document.getElementById('compte');
  const submitBtn = document.getElementById('submitBtn');

  if(nombreChiffreCompte > 0) {
    compteInput.setAttribute('maxlength', nombreChiffreCompte);
  }

  function verifierCompte(compte, societeId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    return fetch('/verifier-compte', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({ compte: compte, societe_id: societeId })
    }).then(response => response.json());
  }

  compteInput.addEventListener('blur', function() {
    const compteValue = this.value.trim();
    const societeId = document.getElementById('societe_id').value;

    // Si le champ n'est pas vide et que la longueur n'est pas correcte
    if (compteValue !== "" && compteValue.length !== nombreChiffreCompte) {
      alert(`Attention, le compte N° "${compteValue}" doit comporter exactement ${nombreChiffreCompte} caractères.`);
      this.value = "";
      this.focus();
      submitBtn.disabled = true;
      return;
    }

    // Si le champ est vide, ne rien faire
    if (compteValue === "") {
      return;
    }

    // Vérifier l'existence du compte
    verifierCompte(compteValue, societeId)
      .then(data => {
        if (data.exists) {
          alert(`Attention, le compte N° "${compteValue}" existe déjà ! Vous ne pouvez pas continuer.`);
          compteInput.value = "";
          compteInput.focus();
          submitBtn.disabled = true;
        } else {
          submitBtn.disabled = false;
        }
      })
      .catch(error => {
        console.error("Erreur lors de la vérification du compte:", error);
      });
  });

  compteInput.addEventListener('input', function() {
    submitBtn.disabled = false;
  });
});

// excel


document.addEventListener('DOMContentLoaded', function () {
  const fileInput = document.getElementById('file');
  const submitBtn = document.getElementById('submitBtn');
  const previewBody = document.getElementById('previewBody');
  const previewHeader = document.getElementById('previewHeader');
  const nombre_chiffre_compte = parseInt(document.getElementById('nombre_chiffre_compte').value || 0, 10);

  // Liste des selects pour les colonnes (assure-toi que ces éléments existent dans ton HTML)
  const selectsMapping = {
    compte: document.getElementById('colonne_compte'),
    intitule: document.getElementById('colonne_intitule'),
    identifiantFiscal: document.getElementById('colonne_identifiant_fiscal'),
    ICE: document.getElementById('colonne_ICE'),
    natureOperation: document.getElementById('colonne_nature_operation'),
    rubriqueTva: document.getElementById('colonne_rubrique_tva'),
    //designation: document.getElementById('colonne_designation'),
    contrePartie: document.getElementById('colonne_contre_partie'),

    // Nouveaux selects pour l'import
    RC: document.getElementById('colonne_RC'),
    ville: document.getElementById('colonne_ville'),
    adresse: document.getElementById('colonne_adresse'),
    delai_p: document.getElementById('colonne_delai_p'),
  };

  function resetSelects() {
    Object.values(selectsMapping).forEach(select => {
      if (!select) return;
      select.innerHTML = '<option value="" disabled selected>Sélectionnez une colonne</option>';
    });
  }

  // Reset bouton
  const resetBtn = document.getElementById('resetModal');
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      fileInput.value = '';
      resetSelects();
      previewBody.innerHTML = '';
      previewHeader.innerHTML = '';
      submitBtn.disabled = true;
    });
  }

  function createTableRow(rowData, colorClass) {
    const tr = document.createElement('tr');
    if (colorClass) {
      tr.classList.add(colorClass);
    }
    rowData.forEach(cell => {
      const td = document.createElement('td');
      // Convertir null/undefined en chaîne vide pour éviter 'undefined'
      td.textContent = (cell === null || typeof cell === 'undefined') ? '' : String(cell);
      tr.appendChild(td);
    });
    return tr;
  }

  /**
   * validateRow : renvoie 'none'|'warning'|'error'
   * mapping attendu : objet qui associe label colonne => index (si tu veux l'utiliser)
   * ici on garde la logique simple basée sur positions attendues (Compte, Intitulé, ICE, Identifiant fiscal)
   */
  function validateRow(row, mapping) {
    // mapping peut être un objet de type { 'Compte': 0, 'Intitulé': 1, ... }
    // si mapping fourni, on utilise les indices, sinon on tente de retrouver par clés
    const getByKey = (key) => {
      if (mapping && mapping[key] !== undefined) {
        return row[mapping[key]];
      }
      return undefined;
    };

    const compte = getByKey('Compte');
    const intitule = getByKey('Intitulé');
    const ice = getByKey('ICE');
    const identFiscal = getByKey('Identifiant fiscal');

    const compteStr = compte !== undefined && compte !== null ? String(compte).trim() : '';
    const intituleStr = intitule !== undefined && intitule !== null ? String(intitule).trim() : '';
    const iceStr = ice !== undefined && ice !== null ? String(ice).trim() : '';
    const identFiscalStr = identFiscal !== undefined && identFiscal !== null ? String(identFiscal).trim() : '';

    if (compteStr === '' && intituleStr === '') {
      return 'warning';
    }
    if (compteStr !== '' && nombre_chiffre_compte && compteStr.length !== nombre_chiffre_compte) {
      return 'error';
    }
    if (iceStr && iceStr.length > 15) {
      return 'error';
    }
    if (identFiscalStr && identFiscalStr.length > 8) {
      return 'error';
    }

    return 'none';
  }

  fileInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) {
      resetSelects();
      previewBody.innerHTML = '';
      previewHeader.innerHTML = '';
      submitBtn.disabled = true;
      return;
    }

    const reader = new FileReader();
    reader.onload = function(event) {
      try {
        const data = new Uint8Array(event.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const sheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[sheetName];
        const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1, raw: false, defval: '' });

        if (!rows || rows.length < 2) {
          alert("Le fichier Excel semble être vide ou ne contient pas assez de données !");
          resetSelects();
          previewBody.innerHTML = '';
          previewHeader.innerHTML = '';
          submitBtn.disabled = true;
          return;
        }

        const headers = rows[0];
        resetSelects();

        // Remplissage des selects avec options nom colonnes + index +1 en value
        Object.values(selectsMapping).forEach(select => {
          if (!select) return;
          headers.forEach((header, idx) => {
            const option = document.createElement('option');
            option.value = idx + 1;
            option.textContent = header || `Colonne ${idx + 1}`;
            select.appendChild(option);
          });
        });

        // Construction preview tableau (en-tête)
        previewHeader.innerHTML = '';
        headers.forEach(cellValue => {
          const th = document.createElement('th');
          th.textContent = cellValue || '';
          previewHeader.appendChild(th);
        });

        // Construction preview body (quelques premières lignes pour aperçu)
        previewBody.innerHTML = '';
        const dataRows = rows.slice(1, Math.min(rows.length, 51)); // affiche jusqu'à 50 lignes pour la preview

        dataRows.forEach(row => {
          // row est un tableau ; on propage longueur à la longueur headers
          const normalized = headers.map((_, i) => row[i] !== undefined ? row[i] : '');
          const tr = createTableRow(normalized, '');
          previewBody.appendChild(tr);
        });

        submitBtn.disabled = false;
      } catch (err) {
        console.error('Erreur lecture fichier Excel :', err);
        alert('Erreur lors de la lecture du fichier Excel. Vérifie qu\'il s\'agit bien d\'un xlsx/xls/csv valide.');
        resetSelects();
        previewBody.innerHTML = '';
        previewHeader.innerHTML = '';
        submitBtn.disabled = true;
      }
    };
    reader.readAsArrayBuffer(file);
  });

  // si le formulaire a besoin d'un mapping objet pour valider les lignes (optionnel),
  // on peut construire ce mapping en fonction des selects choisis au moment du submit.
  // Exemple (non obligatoire) :
  // function buildMappingFromSelects() { ... }

});



  // Fonction pour supprimer un fournisseur
  function deleteFournisseur(id) {
    // Récupérer l'ID de la société depuis la balise meta
    const societeId = $('meta[name="societe-id"]').attr('content');
    if (!societeId) {
        alert("Aucune société sélectionnée dans la session.");
        return;
    }

    if (confirm("Êtes-vous sûr de vouloir supprimer ce fournisseur ?")) {
        $.ajax({
            url: `/fournisseurs/${id}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                societeId: societeId
            },
            success: function(response) {
                // Actualiser le tableau des fournisseurs
                table.setData("/fournisseurs/data");
                alert(response.message);
            },
            error: function(xhr) {
                let errorMsg = "Erreur lors de la suppression.";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                alert(errorMsg);
            }
        });
    }
}



// Convertir une date du format "yyyy-MM-dd" au format "dd/MM/yyyy"
function formatToDDMMYYYY(dateString) {
    const [year, month, day] = dateString.split('-');
    return `${day}/${month}/${year}`;
}


// Require jQuery et SweetAlert2 (Swal) déjà chargés sur la page.
// Intercepte le submit du form d'import pour faire un upload AJAX et afficher Swal.
$(function(){
  const $form = $('#importForm');
  const $submit = $('#submitBtn');
  const $spinner = $('#loadingSpinner');
  const $file = $('#file');
  const $previewBody = $('#previewBody');
  const $previewHeader = $('#previewHeader');

  $form.off('submit.importAjax').on('submit.importAjax', function(e){
    e.preventDefault();

    // Validation basique: fichier présent
    if (!$file.val()) {
      Swal.fire({ icon: 'warning', title: 'Aucun fichier', text: 'Veuillez choisir un fichier à importer.' });
      return;
    }

    // Build FormData (inclut le fichier et tous les selects)
    const fd = new FormData(this);

    // Afficher spinner / désactiver bouton
    $spinner.removeClass('d-none');
    $submit.prop('disabled', true);

    $.ajax({
      url: $form.attr('action'), // route blade: route('fournisseurs.import')
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(resp){
        // masquer spinner / réactiver
        $spinner.addClass('d-none');
        $submit.prop('disabled', false);

        // si le backend renvoie { success: '...' } ou { message: '...' }
        if (resp && (resp.success || resp.message)) {
          Swal.fire({
            icon: 'success',
            title: 'Importation réussie',
            text: resp.success || resp.message || 'Les fournisseurs ont été importés.',
            timer: 1600,
            showConfirmButton: false
          });

          // rafraîchir table Tabulator (assume "table" variable existe)
          if (typeof table !== 'undefined' && table.setData) {
            table.setData('/fournisseurs/data');
          }

          // fermer modal et réinitialiser UI
          $('#importModal').modal('hide');
          $form[0].reset();
          $previewBody.innerHTML = '';
          $previewHeader.innerHTML = '';
          // reset selects (si tu as la fonction resetSelects définie)
          if (typeof resetSelects === 'function') resetSelects();
        } else {
          // backend renvoyé mais sans success
          const err = resp && (resp.error || resp.errors || resp.exception) ? (resp.error || JSON.stringify(resp.errors) || resp.exception) : 'Réponse inattendue du serveur';
          Swal.fire({ icon: 'error', title: 'Erreur', text: err });
        }
      },
      error: function(xhr){
        $spinner.addClass('d-none');
        $submit.prop('disabled', false);

        // essayer d'extraire message d'erreur
        let msg = 'Erreur lors de l\'importation.';
        try {
          const json = xhr.responseJSON || JSON.parse(xhr.responseText || '{}');
          if (json && (json.error || json.message)) msg = json.error || json.message;
          else if (xhr.status === 422 && json.errors) msg = Object.values(json.errors).flat().join('\n');
        } catch(e){}
        Swal.fire({ icon: 'error', title: 'Erreur', text: msg });
      }
    });
  });
});


</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>



</body>

</html>

@endsection
