<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
<br>
    <title>Gestion des Journaux</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">
    <link href="https://unpkg.com/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chargement de jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

<!-- Chargement de Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<!-- Chargement de Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Chargement de Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .select2-results__option.category { font-weight:700; color:#6c757d; background:#f8f9fa; cursor:default; }
.select2-results__option.subcategory-option { font-weight:600; padding-left:10px !important; color:#495057; }
.select2-results__option.option-item { padding-left:18px !important; }
.select2-results__option--highlighted.category,
.select2-results__option--highlighted.subcategory-option { background: rgba(0,0,0,0.03) !important; }

/* Style du select personnalisé */
.form-select {
    background-color: #f8f9fa;
    border: 2px solid #007bff;
    border-radius: 0.375rem; /* bords arrondis */
    font-size: 1rem;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}
.select2-container--default .select2-results__options {
  max-height: 100px; /* Ajustez cette valeur si nécessaire */
  overflow-y: auto; /* Affiche toujours la barre de défilement */
}

.form-select:focus {
    border-color: #0056b3;
    box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
}

/* Bouton + aligné avec le select (ajouté) */
.input-group .btn-add-account {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
  border-top-right-radius: .375rem;
  border-bottom-right-radius: .375rem;
  min-width: 44px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .3rem;
  padding: .45rem .6rem;
}

/* s'assurer que select prend l'espace restant */
.input-group .form-select.flex-grow-1 {
  width: auto;
}

/* petits ajustements responsive */
@media (max-width: 576px) {
  .input-group .btn-add-account { min-width: 40px; padding: .35rem .5rem; }
}
/* Bouton + moderne intégré à Select2 */
.select2-container .select2-add-btn {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  height: 30px;
  width: 30px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #0078ff, #00b7ff);
  color: #fff;
  font-weight: 700;
  font-size: 18px;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 5px rgba(0,0,0,0.15);
  transition: all 0.2s ease;
  z-index: 2050;
}

/* Effet hover pro */
.select2-container .select2-add-btn:hover {
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 3px 8px rgba(0,0,0,0.2);
  background: linear-gradient(135deg, #0066dd, #00a6e6);
}

/* Effet d’enfoncement */
.select2-container .select2-add-btn:active {
  transform: translateY(-50%) scale(0.95);
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

/* Ajustement du champ search pour ne pas être recouvert */
.select2-search__field.with-add-btn {
  padding-right: 42px !important;
  box-sizing: border-box;
}

/* Zone de recherche relative pour positionner le bouton correctement */
.select2-search--dropdown {
  position: relative !important;
}


</style>
<meta name="societe-id" content="{{ session('societeId') }}">

</head>

@extends('layouts.user_type.auth')

@section('content')
<body>

    <div class="container my-3">
        <!-- Ligne de titre et action -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h1 class="h3 text-secondary mb-0">Gestion des Journaux</h1>
          <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                  data-bs-toggle="modal" data-bs-target="#ajouterJournalModal"
                  data-bs-toggle="tooltip" data-bs-placement="top" title="Ajouter un Journal">
            <i class="fas fa-plus icon-3d"></i>
            <span>Ajouter un Journal</span>
          </button>
        </div>
      </div>

      <!-- Tableau des journaux -->
      <div id="journal-table" class="border rounded shadow-sm bg-white p-3"></div>

      <!-- Styles pour l'effet 3D sur les icônes -->
      <style>
        .icon-3d {
          font-size: 1.2rem;
          transition: transform 0.2s, box-shadow 0.2s;
          box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }
        .icon-3d:hover {
          transform: translateY(-2px);
          box-shadow: 3px 3px 6px rgba(0, 0, 0, 0.4);
        }
      </style>

      <!-- Initialisation des tooltips Bootstrap -->
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          try {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
              if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
              }
            });
          } catch(e) { console.warn('tooltip init skipped', e); }
        });
      </script>

<small id="error-message" class="text-danger" style="display:none;"></small>
<small id="success-message" class="text-success" style="display:none;"></small>


<!-- Modal Ajout -->
<div class="modal fade" id="ajouterJournalModal" tabindex="-1" role="dialog" aria-labelledby="ajouterJournalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-light">
          <h5 class="modal-title pro-dark" id="ajouterJournalModalLabel">Créer un Journal</h5>
          <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
          @csrf
          <input type="hidden" name="societe_id" value="{{ session('societeId') }}">
        </div>
        <div class="modal-body">
          <form id="ajouterJournalForm">
            <!-- Ligne 1: Code Journal et Intitulé -->
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="code_journal" class="form-label fw-semibold">Code Journal</label>
                  <input type="text" class="form-control form-control-lg shadow-sm" id="code_journal" name="code_journal" required placeholder="Entrez le code journal">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="intitule" class="form-label fw-semibold">Intitulé</label>
                  <input type="text" class="form-control form-control-lg shadow-sm" id="intitule" name="intitule" required placeholder="Entrez l'intitulé">
                </div>
              </div>
            </div>

            <!-- Ligne 2: Type Journal et Contre Partie -->
            <div class="row g-3 mt-2">
              <div class="col-md-6 position-relative">
                <label for="type_journal" class="form-label fw-semibold">Type Journal</label>
                <select class="form-select form-select-lg" id="type_journal" name="type_journal" required>
                  <option value="" selected>Sélectionner un type</option>
                  <option value="Achats">Achats</option>
                  <option value="Ventes">Ventes</option>
                  <option value="Caisse">Caisse</option>
                  <option value="Banque">Banque</option>
                  <option value="Opérations Diverses">Opérations Diverses</option>
                </select>
              </div>

              <div class="col-md-6 position-relative">
                <label for="contre_partie" class="form-label fw-semibold">Contre Partie</label>
                <!-- Wrapping in input-group so + button sits next to select -->
                <div class="input-group">
                  <select class="form-select form-select-lg flex-grow-1"  id="contre_partie" name="contre_partie">
                    <option value="" selected>Sélectionner une contre partie</option>
                    <!-- Ajoutez d'autres options ici -->
                  </select>

                </div>
            </div>
            </div>

            <!-- Ligne 3: IF Banque et ICE Banque (affichée par défaut ou cachée selon vos besoins) -->
            <div class="row g-3 mt-2 d-none" id="if_ice_container">
                <div class="col-md-6">
                  <label for="identifiant_fiscal" class="form-label fw-semibold">IF Banque</label>
                  <input type="text" class="form-control" id="identifiant_fiscal"
                         name="identifiant_fiscal" maxlength="8" pattern="\d{7,8}"
                         placeholder="Entrez votre IF">
                </div>
                <div class="col-md-6">
                  <label for="ice" class="form-label fw-semibold">ICE Banque</label>
                  <input type="text" class="form-control" id="ice"
                         name="ice" maxlength="15" pattern="\d{15}"
                         placeholder="Entrez votre ICE">
                </div>
              </div>
          </form>
        </div>
        <div class="modal-footer mt-4 d-flex justify-content-between">
          <!-- Bouton Réinitialiser -->
          <button type="button" class="btn btn-outline-secondary px-4" id="resetFormBtn">
            <i class="fas fa-sync-alt"></i> Réinitialiser
          </button>
          <div id="alertMessage" class="alert alert-success" role="alert" style="display:none;"></div>

          <!-- Bouton Valider -->
          <button type="submit" form="ajouterJournalForm" class="btn btn-primary px-4">
            <i class="fas fa-check"></i> Valider
          </button>
        </div>
      </div>
    </div>
  </div>

<div id="alertMessage" class="alert" role="alert" style="display:none;"></div>

<!-- Modal d'édition -->
<div class="modal fade" id="journalModalEdit" tabindex="-1" role="dialog" aria-labelledby="journalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-light">
<h5 class="modal-title pro-dark" id="journalModalLabel">Modifier le Journal</h5>
          <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="journalFormEdit">
            <input type="hidden" id="editJournalId" value="">
            <!-- Ligne 1: Code Journal et Intitulé -->
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editCodeJournal" class="form-label fw-semibold">Code Journal</label>
                  <input type="text" class="form-control form-control-lg shadow-sm" id="editCodeJournal" required placeholder="Entrez le code journal" readonly>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editIntituleJournal" class="form-label fw-semibold">Intitulé</label>
                  <input type="text" class="form-control form-control-lg shadow-sm" id="editIntituleJournal" required placeholder="Entrez l'intitulé">
                </div>
              </div>
            </div>

            <!-- Ligne 2: Type Journal et Contre Partie -->
            <div class="row g-3 mt-2">
              <div class="col-md-6 position-relative">
                <label for="editTypeJournal" class="form-label fw-semibold">Type Journal</label>
                <select class="form-select form-select-lg shadow-sm" id="editTypeJournal" name="type_journal_modif">
                  <option value="" selected>Sélectionner un type</option>
                  <option value="Achats">Achats</option>
                  <option value="Ventes">Ventes</option>
                  <option value="Caisse">Caisse</option>
                  <option value="Banque">Banque</option>
                  <option value="Opérations Diverses">Opérations Diverses</option>
                </select>
              </div>
              <div class="col-md-6 position-relative" id="contrePartieContainer">
                <label for="editContrePartie" class="form-label">Contre Partie</label>
                <div class="input-group">
                  <select class="form-select form-select-lg shadow-sm" id="editContrePartie" name="contre_partie">
                    <option value="" selected>Sélectionner une contre partie</option>
                    <!-- Ajoutez d'autres options ici -->
                  </select>

                </div>
              </div>
            </div>

            <!-- Ligne 3: IF Banque et ICE Banque (caché par défaut) -->
            <div class="row g-3 mt-2" id="edit_if_ice_container" style="display: none;">
              <div class="col-md-6">
                <label for="edit_identifiant_fiscal" class="form-label fw-semibold">IF Banque</label>
                <input type="text" class="form-control" id="edit_identifiant_fiscal" name="identifiant_fiscal" maxlength="8" pattern="\d{7,8}" placeholder="Entrez votre IF ">
                <small id="error-identifiant_fiscal" class="text-danger" style="display:none;"></small>
              </div>
              <div class="col-md-6">
                <label for="edit_ice" class="form-label fw-semibold">ICE Banque</label>
                <input type="text" class="form-control" id="edit_ice" name="ice" maxlength="15" pattern="\d{15}" placeholder="Entrez votre ICE ">
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer mt-4 d-flex justify-content-between">
          <!-- Bouton Réinitialiser -->
          <button type="button" class="btn btn-outline-secondary px-4" id="resetFormBtn">
            <i class="fas fa-sync-alt"></i> Réinitialiser
          </button>
          <!-- Bouton Sauvegarder -->
          <button type="submit" form="journalFormEdit" class="btn btn-primary px-4">
            <i class="fas fa-check"></i> Sauvegarder
          </button>
        </div>
      </div>
    </div>
  </div>

</main>

<script>
/*
  Script complet corrigé pour gestion des journaux + rubrique_tva.
  - Toutes les alertes/confirmations remplacées par SweetAlert2.
  - Important : ajouter si besoin dans ton CSS :
    .select2-container--bootstrap-5 .select2-dropdown { z-index: 2100 !important; }
*/

// Fichier JS complet — gestion journaux, select contre_partie, rubrique TVA, etc.

$(function(){
  // Select2 init pour certains selects (ajout/édition) — dropdownParent réglé quand modal d'ajout est présent
  $('#type_journal, #contre_partie').select2({
    allowClear: true,
    width: '100%',
    // theme: 'bootstrap-5',
    placeholder: 'Sélectionnez une option',
    dropdownParent: $('#ajouterJournalModal').length ? $('#ajouterJournalModal') : $(document.body),
  });

  const $ifIce = $('#if_ice_container');
  const $if    = $('#identifiant_fiscal');
  const $ice   = $('#ice');

  function toggleIfIce() {
    var val = $('#type_journal').val();
    if (val && String(val).toLowerCase() === 'banque') {
      $ifIce.removeClass('d-none');
    } else {
      $ifIce.addClass('d-none');
      $if.val(''); $ice.val('');
    }
  }
  $('#type_journal').on('change select2:select select2:unselect', toggleIfIce);
  $('#ajouterJournalModal').on('shown.bs.modal', toggleIfIce);

  // Validations IF/ICE (remplacé alert par SweetAlert)
  $if.on('input', function(){ this.value = this.value.replace(/\D/g, '').slice(0,8); })
     .on('blur', function(){
       if (this.value && (this.value.length < 7 || this.value.length > 8)) {
         Swal.fire({ icon: 'warning', title: 'Format IF invalide', text: 'Le champ IF doit contenir 7 ou 8 chiffres exactement' });
         this.value = '';
       }
     });
  $ice.on('input', function(){ this.value = this.value.replace(/\D/g, '').slice(0,15); });
});

// DOMContentLoaded pour focus et setup
document.addEventListener("DOMContentLoaded", () => {
  // Focus sur champs des modals
  $('#ajouterJournalModal').on('shown.bs.modal', () => $('#code_journal').focus());
  $('#journalModalEdit').on('shown.bs.modal', function(){
    // n'init que si pas encore initialisé (évite double init)
    if (!$('#edit_rubrique_tva').hasClass('select2-hidden-accessible')) {
      toggleRubriqueEdit();
    }
    $('#editCodeJournal').focus();
  });

  // Reset forms
  $('#resetFormBtn').on('click', () => { $('#ajouterJournalForm')[0].reset(); $('#type_journal, #contre_partie').val(null).trigger('change'); });
  $('#journalModalEdit #resetFormBtn').on('click', () => { $('#journalFormEdit')[0].reset(); $('#editJournalId').val(''); });

  // CSRF setup
  var csrfToken = $('meta[name="csrf-token"]').attr('content');
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

  // --- Init select2 for editContrePartie if present (added to preserve behavior) ---
  if ($('#editContrePartie').length && !$('#editContrePartie').hasClass('select2-hidden-accessible')) {
    $('#editContrePartie').select2({
      allowClear: true,
      width: '100%',
      placeholder: 'Sélectionnez un compte',
      dropdownParent: $('#journalModalEdit').length ? $('#journalModalEdit') : $(document.body),
    });
  }
  // -------------------------------------------------------------------------

  // Tabulator initialisation (conserve ton config)
  var table = new Tabulator("#journal-table", {
    ajaxURL: "/journaux/data",
    height: "600px",
    layout: "fitColumns",
    selectable: true,
    rowSelection: true,
    columns: [
      {
        title: `<i class="fas fa-check-square" id="selectAllIcon" title="Sélectionner tout" style="cursor: pointer;"></i>
                <i class="fas fa-trash-alt" id="deleteAllIcon" title="Supprimer les lignes sélectionnées" style="cursor: pointer; margin-left:8px"></i>`,
        field:"select", formatter:"rowSelection", headerSort:false, hozAlign:"center", width:60, cellClick:function(e,cell){ cell.getRow().toggleSelect(); }
      },
      { title:"Code Journal", field:"code_journal", editor:"input", headerFilter:"input" },
      { title:"Intitulé", field:"intitule", editor:"input", headerFilter:"input" },
      { title:"Type Journal", field:"type_journal", editor:"input", headerFilter:"input" },
  // Remplacer l'ancienne colonne Contre Partie par :
  {
    title: "Contre Partie",
    field: "contre_partie_affiche",    // UTILISER le champ d'affichage fourni par le back
    headerFilter: "input",
    headerFilterPlaceholder: "Recherche...",
    editor: false,                     // lecture seule dans le tableau (modification via modal)
    hozAlign: "left",
    cellFormatter: function(cell) {    // fallback propre si valeur nulle
      const v = cell.getValue();
      return v ? v : (cell.getData().contre_partie ? String(cell.getData().contre_partie) : '');
    }
  },
      { title:"identifiant_fiscal", field:"identifiant_fiscal", editor:"input", headerFilter:"input", visible:false },
      { title:"ice", field:"ice", editor:"input", headerFilter:"input", visible:false },
      {
        title:"Actions",
        field:"action-icons",
        formatter:function(){ return `<i class='fas fa-edit edit-icon' style='font-size:0.95em;cursor:pointer;margin-right:8px' title='Modifier'>
            </i><i class='fas fa-trash-alt text-danger delete-icon' style='font-size:0.95em;cursor:pointer' title='Supprimer'></i>`; },

        cellClick:function(e,cell){
          var rowData = cell.getRow().getData();
          if (e.target.classList.contains('edit-icon')) editJournal(rowData);
          else if (e.target.classList.contains('delete-icon')) deleteJournal(rowData.id, rowData.code_journal);
        },
        hozAlign:"center", headerSort:false
      }
    ],
    rowSelected: function(row){ row.getElement().classList.add("bg-light"); },
    rowDeselected: function(row){ row.getElement().classList.remove("bg-light"); }
  });

  // Delete multiple
  function deleteSelectedRows(){
    const societeId = $('meta[name="societe-id"]').attr('content');
    if (!societeId) {
        Swal.fire({ icon: 'error', title: 'Erreur', text: 'Aucune société sélectionnée dans la session.' });
        return;
    }
    var selectedRows = table.getSelectedRows();
    var idsToDelete = selectedRows.map(r => r.getData().id);
    if (!idsToDelete.length) {
      Swal.fire({ icon: 'info', title: 'Aucune sélection', text: 'Aucune ligne sélectionnée à supprimer.' });
      return;
    }

    Swal.fire({
      title: 'Confirmer la suppression',
      text: `Supprimer ${idsToDelete.length} élément(s) ?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText: 'Annuler'
    }).then((result) => {
      if (!result.isConfirmed) return;
      fetch("/journaux/delete-selected", {
        method: "POST",
        headers: { "Content-Type":"application/json", "X-CSRF-TOKEN": csrfToken },
        body: JSON.stringify({ ids: idsToDelete, societeId })
      }).then(r => r.json()).then(data => {
        if (data.error) {
          Swal.fire({ icon: 'error', title: 'Erreur', text: data.error });
        } else {
          Swal.fire({ icon: 'success', title: 'Supprimé', text: data.message });
          table.setData("/journaux/data");
        }
      })
      .catch(e => {
        console.error(e);
        Swal.fire({ icon: 'error', title: 'Erreur', text: 'Erreur lors de la suppression.' });
      });
    });
  }

  $('#journal-table').on("click", function(e){
    if (e.target.id === "selectAllIcon") { if (table.getSelectedRows().length === table.getRows().length) table.deselectRow(); else table.selectRow(); }
    if (e.target.id === "deleteAllIcon") deleteSelectedRows();
  });

  // ---------- ============ MODIFICATIONS ICI ============ ----------
  // getComptesUrl: mapping normalisé des types -> endpoints (kebab-case)
  // mapping normalisé + variantes à essayer
function getComptesUrlVariants(typeJournal) {
  if (!typeJournal) return [];
  const t = String(typeJournal).trim().toLowerCase();

  if (t === 'achats') return ['/comptes-achats', '/comptes-Achats'];
  if (t === 'ventes') return ['/comptes-ventes', '/comptes-Ventes'];
  if (t === 'caisse') return ['/comptes-caisse', '/comptes-Caisse'];
  if (t === 'banque') return ['/comptes-banque', '/comptes-Banque'];
  // opérations diverses -> comptes-op
  if (t.indexOf('opération') !== -1 || t.indexOf('operation') !== -1 || t.indexOf('opérations') !== -1) return ['/comptes-op', '/comptes-OP'];
  // fallback général
  return ['/comptes-op', '/comptes-OP'];
}

// helper: essaie une liste d'URLs jusqu'à obtenir une réponse JSON valide
function tryFetchVariants(urls, cb) {
  let i = 0;
  function next() {
    if (i >= urls.length) return cb(new Error('All endpoints failed'));
    const url = urls[i++];
    fetch(url, { credentials: 'same-origin' })
      .then(res => {
        if (!res.ok) {
          console.warn('endpoint', url, 'returned', res.status);
          return next();
        }
        return res.json().then(data => cb(null, { url, data })).catch(err => {
          console.warn('invalid json from', url, err);
          next();
        });
      })
      .catch(err => {
        console.warn('fetch error', url, err);
        next();
      });
  }
  next();
}

/* ---------- utilitaire pour ajouter l'option "+ Ajouter un compte..." ---------- */
/* on garde la fonction appelée par ton code mais on la transforme en NO-OP
   pour respecter ta demande "ne rien enlever" tout en empêchant l'option dans le select. */
function appendAddAccountOption($select) {
  // NO-OP : on n'ajoute pas l'option dans le select (le + est désormais un bouton à côté)
  if (!$select || !$select.length) return;
  // s'il reste une ancienne option, on la retire (sécurité)
  try { $select.find('option[value="__add__"]').remove(); } catch(e){}
  // ne rien ajouter.
}

/* ---------- remplace loadComptesAdd (version sans option +Ajouter) ---------- */
function loadComptesAdd(typeJournal, preserveValue) {
  const $select = $('#contre_partie');
  const variants = getComptesUrlVariants(typeJournal);

  $select.closest('div').show();
  $select.prop('disabled', true);
  $select.html('<option value="">Chargement...</option>');
  tryFetchVariants(variants, function(err, result){
    if (err) {
      console.error('Aucun endpoint disponible pour', typeJournal, variants);
      $select.html('<option value="">Erreur de chargement</option>');
      $select.prop('disabled', false);
      try { $select.trigger('change.select2'); } catch(e){}
      // mettre à jour visibilité bouton
      updateAddButtonVisibilityBySelectId('#contre_partie');
      return;
    }
    const data = result.data;
    // remplissage
    let options = '<option value="">Sélectionner un compte</option>';
    if (Array.isArray(data) && data.length) {
      data.forEach(c => {
        options += `<option value="${c.compte}">${c.compte} - ${c.intitule}</option>`;
      });
    } else {
      options += '<option value="">Aucun compte disponible</option>';
    }

    $select.html(options);

    // préserve valeur
    if (preserveValue !== undefined && preserveValue !== null && String(preserveValue) !== '') {
      const exists = $select.find(`option[value="${String(preserveValue)}"]`).length > 0;
      if (!exists) {
        $select.append(`<option value="${preserveValue}" selected>${preserveValue} (compte non listé)</option>`);
        try { $select.val(String(preserveValue)).trigger('change.select2'); } catch(e){ $select.val(String(preserveValue)); }
      } else {
        try { $select.val(String(preserveValue)).trigger('change.select2'); } catch(e){ $select.val(String(preserveValue)); }
      }
    } else {
      try { $select.val(null).trigger('change.select2'); } catch(e){ $select.val(null); }
    }

    $select.prop('disabled', false);
    // update add button visibility
    updateAddButtonVisibilityBySelectId('#contre_partie');
  });
}

/* ---------- remplace loadComptesEdit (version sans option +Ajouter) ---------- */
function loadComptesEdit(typeJournal, selectedValue) {
  const $select = $('#editContrePartie');
  const variants = getComptesUrlVariants(typeJournal);

  $select.closest('div').show();
  $select.prop('disabled', true);
  $select.html('<option value="">Chargement...</option>');
  tryFetchVariants(variants, function(err, result){
    if (err) {
      console.error('Aucun endpoint disponible pour', typeJournal, variants);
      $select.html('<option value="">Erreur de chargement</option>');
      $select.prop('disabled', false);
      try { $select.trigger('change.select2'); } catch(e){}
      updateAddButtonVisibilityBySelectId('#editContrePartie');
      return;
    }
    const data = result.data;
    let options = '<option value="">Sélectionner un compte</option>';
    let foundSelected = false;
    if (Array.isArray(data) && data.length) {
      data.forEach(function(compte){
        const compteVal = String(compte.compte);
        const isSelected = (selectedValue !== undefined && selectedValue !== null && String(selectedValue) === compteVal);
        if (isSelected) foundSelected = true;
        options += `<option value="${compteVal}"${isSelected ? ' selected' : ''}>${compteVal} - ${compte.intitule}</option>`;
      });
    } else {
      options += '<option value="">Aucun compte disponible</option>';
    }
    if (selectedValue && !foundSelected) options += `<option value="${selectedValue}" selected>${selectedValue} (compte non listé)</option>`;

    $select.html(options);
    try { $select.trigger('change.select2'); } catch(e){}
    $select.prop('disabled', false);

    // update bouton
    updateAddButtonVisibilityBySelectId('#editContrePartie');
  });
}

/* ---------- Helper : afficher/masquer le bouton + selon le contenu du select / résultats Select2 ---------- */
function updateAddButtonVisibilityBySelectId(selectId) {
  var $sel = $(selectId);
  if (!$sel.length) return;
  var $btn = $sel.closest('.input-group').find('.btn-add-account');
  if (!$btn.length) return;

  // si select est désactivé -> afficher le bouton (car on veut permettre ajout manuel)
  if ($sel.prop('disabled')) {
    $btn.show();
    return;
  }

  // compter options valides (exclure placeholder)
  var $validOptions = $sel.find('option').filter(function(){
    var v = $(this).val();
    return (v !== '' && v !== null && v !== undefined && !$(this).prop('disabled'));
  });
  if ($validOptions.length === 0) $btn.show(); else $btn.hide();
}

/* ---------- Show Add Account via SweetAlert (in-modal safe) ---------- */
function showAddAccountSwal(prefillTerm, $originSelect) {
  // if origin select is inside a modal, target that modal content (prevents focus trap issues)
  var $parentModal = ($originSelect && $originSelect.length) ? $originSelect.closest('.modal') : $();
  var target = undefined;
  if ($parentModal && $parentModal.length) {
    var modalContent = $parentModal.find('.modal-content').get(0);
    if (modalContent) target = modalContent;
  }

  Swal.fire({
    title: 'Ajouter un compte',
    html:
      `<input id="swal_compte" class="swal2-input" placeholder="Numéro de compte" value="${(prefillTerm||'').replace(/"/g,'&quot;')}">
       <input id="swal_intitule" class="swal2-input" placeholder="Intitulé du compte">`,
    showCancelButton: true,
    confirmButtonText: 'Ajouter',
    target: target || undefined,
    focusConfirm: false,
    didOpen: () => {
      const el = document.getElementById('swal_compte');
      if (el) el.focus();
    },
    preConfirm: () => {
      const compte = document.getElementById('swal_compte').value.trim();
      const intitule = document.getElementById('swal_intitule').value.trim();
      if (!compte || !intitule) {
        Swal.showValidationMessage('Veuillez renseigner le numéro de compte et l\'intitulé.');
        return false;
      }
      const payload = {
        compte: compte,
        intitule: intitule,
        societe_id: $('meta[name="societe-id"]').attr('content') || $('#societe_id').val()
      };
      return fetch('/plancomptable', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      })
      .then(res => {
        if (!res.ok) return res.json().then(j => { throw j; });
        return res.json();
      })
      .then(json => {
        if (!(json && (json.success || json.created))) {
          throw (json && json.message) ? json : { message: 'Erreur lors de la création du compte.' };
        }
        return { created: true, data: (json.data ? json.data : { compte: payload.compte, intitule: payload.intitule }) };
      })
      .catch(err => {
        const msg = (err && err.message) ? err.message : 'Erreur lors de la création du compte.';
        Swal.showValidationMessage(msg);
        throw err;
      });
    },
    allowOutsideClick: () => !Swal.isLoading()
  }).then((result) => {
    if (result.isConfirmed && result.value && result.value.data) {
      const newCompte = result.value.data.compte;
      const newIntitule = result.value.data.intitule;

      // ajoute / met à jour l'option dans les deux selects et sélectionne
      ['#contre_partie','#editContrePartie'].forEach(function(selector){
        var $sel = $(selector);
        if (!$sel.length) return;
        var exists = $sel.find('option[value="' + newCompte + '"]').length > 0;
        if (!exists) {
          var optionHtml = '<option value="' + newCompte + '">' + newCompte + ' - ' + newIntitule + '</option>';
          $sel.append(optionHtml);
        } else {
          $sel.find('option[value="' + newCompte + '"]').text(newCompte + ' - ' + newIntitule);
        }
        try { $sel.val(newCompte).trigger('change.select2'); } catch(e){ $sel.val(newCompte); }
      });

      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Compte ajouté', showConfirmButton: false, timer: 1400 });

      // update button visibility after adding
      updateAddButtonVisibilityBySelectId('#contre_partie');
      updateAddButtonVisibilityBySelectId('#editContrePartie');
    }
  });
}

/* ---------- Handlers to show the + button next to select when Select2 has no results ---------- */
$(document).on('select2:open', '#contre_partie, #editContrePartie', function(e){
  var $select = $(this);
  var $inputGroup = $select.closest('.input-group');
  var $btn = $inputGroup.find('.btn-add-account');
  if (!$btn.length) return;

  // hide initially
  $btn.hide();

  // fetch select2 container
  var $container = $('.select2-container--open').last();
  var $results = $container.find('.select2-results__options');
  var $search = $container.find('.select2-search__field');

  function checkNoResultsInDropdown() {
    if (!$results.length) return;
    // message "no results"
    var $msg = $results.find('.select2-results__option.select2-results__message');
    if ($msg.length && $msg.text().trim().length > 0) {
      $btn.show();
      return;
    }
    var $opts = $results.find('.select2-results__option').not('.select2-results__option.select2-results__message').not('[aria-disabled="true"]');
    if ($opts.length === 0) $btn.show(); else $btn.hide();
  }

  // small initial delay to let Select2 render
  setTimeout(checkNoResultsInDropdown, 60);

  if ($search && $search.length) {
    $search.on('input.addBtn', function(){ setTimeout(checkNoResultsInDropdown, 30); });
  }

  // observer for results changes
  var mo = null;
  if ($results && $results.length) {
    mo = new MutationObserver(function(){ setTimeout(checkNoResultsInDropdown, 20); });
    mo.observe($results.get(0), { childList: true, subtree: true, characterData: true });
  }

  $select.data('addBtnMo', mo);
  $select.data('addBtnSearch', $search);
});

// cleanup on close
$(document).on('select2:close', '#contre_partie, #editContrePartie', function(e){
  var $select = $(this);
  var mo = $select.data('addBtnMo');
  var $search = $select.data('addBtnSearch');
  if (mo && mo.disconnect) mo.disconnect();
  if ($search && $search.off) $search.off('.addBtn');
  $select.removeData('addBtnMo addBtnSearch');
  $select.closest('.input-group').find('.btn-add-account').hide();
});

// click on + button (near select)
$(document).on('click', '.btn-add-account', function(e){
  e.preventDefault();
  var $btn = $(this);
  var $select = $btn.closest('.input-group').find('select');
  var term = '';

  // if select2 open, take search term
  var $openSearch = $('.select2-container--open .select2-search__field').last();
  if ($openSearch && $openSearch.length) term = $openSearch.val().trim();

  if (!term) term = ($select.val() && $select.val() !== '') ? $select.val() : '';

  // close Select2 if open
  try { $select.select2('close'); } catch(e){}

  // use SweetAlert to add account (in-modal safe)
  showAddAccountSwal(term, $select);
});


// select2-add-button.js
(function($){
  'use strict';

  function getOpenSelect2ContainerFor($select) {
    var containers = $('.select2-container--open');
    if (containers.length === 0) return null;
    return containers.last();
  }

  // Handler quand Select2 s'ouvre
  $(document).on('select2:open', '#contre_partie, #editContrePartie', function(e){
    var $select = $(this);
    var $container = getOpenSelect2ContainerFor($select);
    if (!$container || !$container.length) return;

    // trouver la zone de recherche du dropdown
    var $searchArea = $container.find('.select2-search--dropdown').first();
    if (!$searchArea || !$searchArea.length) {
      $searchArea = $container.find('.select2-dropdown').first();
    }
    if (!$searchArea || !$searchArea.length) return;

    // nettoyage sécurité
    $container.find('.select2-add-btn').remove();
    $container.find('.select2-search__field.with-add-btn').removeClass('with-add-btn');

    // position relative pour référence absolue du bouton
    $searchArea.css('position', 'relative');

    // créer bouton
    var $btn = $('<button type="button" class="select2-add-btn" aria-label="Ajouter un compte" title="Ajouter un compte">+</button>');

    // rechercher le champ search et l'insérer juste après si présent,
    // sinon prepend (fallback)
    var $searchField = $searchArea.find('.select2-search__field').first();
    if ($searchField && $searchField.length) {
      // si on préfère garder le DOM order, insertAfter; on applique padding-right pour éviter chevauchement
      $btn.insertAfter($searchField);
      $searchField.addClass('with-add-btn');
    } else {
      $searchArea.prepend($btn);
    }

    // empêcher fermeture du dropdown / perte de focus au mousedown
    $btn.on('mousedown.select2add', function(ev){
      ev.preventDefault();
    });

    // clic : ouvrir modal swal ou fonction custom
    $btn.on('click.select2add', function(ev){
      ev.preventDefault();
      ev.stopPropagation();

      // récupérer terme entrant
      var term = '';
      var $openSearch = $container.find('.select2-search__field').first();
      if ($openSearch && $openSearch.length) term = $openSearch.val().trim();

      // fermer dropdown pour éviter focus traps
      try { $select.select2('close'); } catch(e){}

      // si l'utilisateur a défini showAddAccountSwal(term, $select) l'appeler
      if (typeof showAddAccountSwal === 'function') {
        try {
          showAddAccountSwal(term, $select);
        } catch(err) {
          console.error('showAddAccountSwal error', err);
        }
        return;
      }

      // fallback SweetAlert2 simple
      Swal.fire({
        title: 'Ajouter un compte',
        html: `<input id="swal_compte" class="swal2-input" placeholder="Numéro de compte" value="${(term||'').replace(/"/g,'&quot;')}">
               <input id="swal_intitule" class="swal2-input" placeholder="Intitulé du compte">`,
        focusConfirm: false,
        showCancelButton: true,
        preConfirm: () => {
          const compte = document.getElementById('swal_compte').value.trim();
          const intitule = document.getElementById('swal_intitule').value.trim();
          if (!compte || !intitule) {
            Swal.showValidationMessage('Veuillez renseigner le numéro de compte et l\'intitulé.');
            return false;
          }
          return { compte, intitule };
        }
      }).then(res => {
        if (res.isConfirmed && res.value) {
          // ajouter dans les selects
          ['#contre_partie','#editContrePartie'].forEach(function(selector){
            var $sel = $(selector);
            if (!$sel.length) return;
            var safeVal = $('<div>').text(res.value.compte).html();
            var safeTxt = $('<div>').text(res.value.intitule).html();
            var exists = $sel.find('option[value="' + res.value.compte + '"]').length > 0;
            if (!exists) {
              $sel.append(`<option value="${safeVal}">${safeVal} - ${safeTxt}</option>`);
            } else {
              $sel.find('option[value="' + res.value.compte + '"]').text(safeVal + ' - ' + safeTxt);
            }
            try { $sel.val(res.value.compte).trigger('change.select2'); } catch(e){ $sel.val(res.value.compte); }
          });
          Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Compte ajouté', showConfirmButton:false, timer:1200 });
        }
      }).catch(function(err){
        console.error('Swal add account error', err);
      });
    });

  });

  // Handler fermeture : nettoyage complet
  $(document).on('select2:close', '#contre_partie, #editContrePartie', function(e){
    $('.select2-container--open').each(function(){
      $(this).find('.select2-add-btn').remove();
      $(this).find('.select2-search__field.with-add-btn').removeClass('with-add-btn');
    });
    // safety remove any leftover
    $('.select2-add-btn').remove();
    $('.select2-search__field.with-add-btn').removeClass('with-add-btn');
  });

})(jQuery);

/* ---------- soumission du formulaire d'ajout vers POST /plancomptable (gardé si présent ailleurs) ---------- */
$('#planComptableFormAdd').on('submit', function(e){
  e.preventDefault();
  var $form = $(this);
  var compte = $('#compte_add').val().trim();
  var intitule = $('#intitule_add').val().trim();
  var societe_id = $('#societe_id').val() || $('meta[name="societe-id"]').attr('content');
  var nombre_chiffre = parseInt($('#nombre_chiffre_compte').val() || 0, 10);

  if (!compte || !intitule) {
    Swal.fire({ icon: 'warning', title: 'Champs requis', text: 'Veuillez renseigner le numéro de compte et l\'intitulé.' });
    return;
  }
  if (nombre_chiffre > 0 && String(compte).length !== nombre_chiffre) {
    Swal.fire({ icon: 'warning', title: 'Format compte', text: 'Le compte doit contenir ' + nombre_chiffre + ' chiffres.' });
    return;
  }

  var $btn = $form.find('[type="submit"]').prop('disabled', true);

  $.ajax({
    url: '/plancomptable',
    type: 'POST',
    data: {
      compte: compte,
      intitule: intitule,
      societe_id: societe_id,
      _token: $('meta[name="csrf-token"]').attr('content')
    },
    success: function(resp){
      // Attendu: { success: true, data: { compte, intitule } } ou similaire
      if (resp && (resp.success || resp.created)) {
        var newCompte = (resp.data && resp.data.compte) ? resp.data.compte : (resp.compte || compte);
        var newIntitule = (resp.data && resp.data.intitule) ? resp.data.intitule : (resp.intitule || intitule);

        // ajoute / met à jour l'option dans les deux selects et sélectionne
        var addOptionIfMissing = function(selector){
          var $sel = $(selector);
          if (!$sel.length) return;
          var exists = $sel.find('option[value="' + newCompte + '"]').length > 0;
          if (!exists) {
            var optionHtml = '<option value="' + newCompte + '">' + newCompte + ' - ' + newIntitule + '</option>';
            $sel.append(optionHtml);
          } else {
            $sel.find('option[value="' + newCompte + '"]').text(newCompte + ' - ' + newIntitule);
          }
          try { $sel.val(newCompte).trigger('change.select2'); } catch(e){ $sel.val(newCompte); }
        };

        addOptionIfMissing('#contre_partie');
        addOptionIfMissing('#editContrePartie');

        $('#planComptableModalAdd').modal('hide');
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Compte ajouté', showConfirmButton: false, timer: 1400 });
        $form[0].reset();

        // update add button vis
        updateAddButtonVisibilityBySelectId('#contre_partie');
        updateAddButtonVisibilityBySelectId('#editContrePartie');
      } else {
        var errMsg = (resp && resp.message) ? resp.message : 'Erreur lors de la création du compte.';
        Swal.fire({ icon: 'error', title: 'Erreur', text: errMsg });
      }
    },
    error: function(xhr){
      console.error('Erreur création compte', xhr);
      var msg = 'Erreur lors de la création du compte.';
      if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
      Swal.fire({ icon: 'error', title: 'Erreur', text: msg });
    },
    complete: function(){ $btn.prop('disabled', false); }
  });
});

/* ---------- quand modal d'ajout est fermé : focus / open select parent si modal journal est ouvert ---------- */
$('#planComptableModalAdd').on('hidden.bs.modal', function(){
  if ($('#ajouterJournalModal').hasClass('show')) {
    try { $('#contre_partie').select2('open'); } catch(e) { $('#contre_partie').focus(); }
  } else if ($('#journalModalEdit').hasClass('show')) {
    try { $('#editContrePartie').select2('open'); } catch(e) { $('#editContrePartie').focus(); }
  }
});

/* ---------- optionnel : si tu veux forcer l'ajout initial (au cas où les listes ont déjà été chargées) ---------- */
$(document).ready(function(){
  // appendAddAccountOption est no-op mais on l'appelle pour respecter le flux existant
  appendAddAccountOption($('#contre_partie'));
  appendAddAccountOption($('#editContrePartie'));

  // ensure buttons initial visibility
  updateAddButtonVisibilityBySelectId('#contre_partie');
  updateAddButtonVisibilityBySelectId('#editContrePartie');
});

 // ======= Rubrique TVA: containers + remplissage (promise-safe) =======
  function ensureRubriqueAddExists(){
    if ($('#rubrique_container_add').length) return;
    const html = `<div class="row g-3 mt-2" id="rubrique_container_add" style="display:none;">
      <div class="col-md-6">
        <label for="rubrique_tva" class="form-label fw-semibold">Rubrique TVA</label>
        <select id="rubrique_tva" name="rubrique_tva" class="form-select form-select-lg" style="width:100%"></select>
      </div><div class="col-md-6"></div></div>`;
    const cpCol = $('#contre_partie').closest('.row');
    if (cpCol.length) cpCol.after(html); else $('#ajouterJournalModal .modal-body form').append(html);
  }
  function ensureRubriqueEditExists(){
    if ($('#rubrique_container_edit').length) return;
    const html = `<div class="row g-3 mt-2" id="rubrique_container_edit" style="display:none;">
      <div class="col-md-6">
        <label for="edit_rubrique_tva" class="form-label fw-semibold">Rubrique TVA</label>
        <select id="edit_rubrique_tva" name="rubrique_tva" class="form-select form-select-lg" style="width:100%"></select>
      </div><div class="col-md-6"></div></div>`;
    const cpEdit = $('#editContrePartie').closest('.row');
    if (cpEdit.length) cpEdit.after(html); else $('#journalModalEdit .modal-body form').append(html);
  }

  /**
   * fillRubriqueSelect: remplit et initialise select2, retourne une Promise qui résol quand Select2 est prêt
   * selectId : id du select (ex: 'rubrique_tva' ou 'edit_rubrique_tva')
   * selectedValue : valeur à présélectionner (peut être null)
   */
/**
 * fillRubriqueSelect: remplit et initialise select2, retourne une Promise (jQuery Deferred)
 * selectId : id du select (ex: 'rubrique_tva' ou 'edit_rubrique_tva')
 * selectedValue : valeur à présélectionner (peut être null)
 */
function fillRubriqueSelect(selectId, selectedValue){
  const $sel = $('#' + selectId);
  const deferred = $.Deferred();

  if (!$sel.length) { deferred.resolve(); return deferred.promise(); }

  // Guard: si un remplissage est déjà en cours pour ce select, retourne la même promise
  var existing = $sel.data('fillPromise');
  if (existing && existing.then) {
    console.warn('fillRubriqueSelect: remplissage déjà en cours pour', selectId);
    return existing;
  }

  // Marque l'opération en cours : stocke la promise sur l'élément pour éviter doublons
  var fillPromise = $.getJSON('/rubriques-tva-vente')
  .done(function(data){
    try {
      // reset select
      // si Select2 déjà initialisé, le détruire proprement
      if ($sel.hasClass('select2-hidden-accessible')) {
        try { $sel.select2('destroy'); } catch(e){ /* ignore */ }
      }
      $sel.empty();

      // placeholder
      $sel.append(new Option('Sélectionnez une rubrique','',true,false));
      const excludedNumRacines = [147,151,152,148,144];

      // Build a normalized list of entries: { type, category, rubrique }
      const entries = [];

      function pushEntry(typeName, categoryName, rubrique){
        typeName = (typeName || '').toString().trim();
        categoryName = (categoryName || '').toString().trim() || '';
        entries.push({ type: typeName, category: categoryName, rubrique: rubrique });
      }

      // Flexible parsing depending on API shape
      if (Array.isArray(data.rubriques)) {
        // case: data.rubriques is an array of groups or items
        data.rubriques.forEach(item => {
          if (!item) return;
          // group like: { type, categorie, rubriques: [...] }
          if (item.rubriques && Array.isArray(item.rubriques)) {
            const typeN = item.type || item.typeName || '';
            const catN  = item.categorie || item.category || '';
            item.rubriques.forEach(r => pushEntry(typeN, catN, r));
          } else if (item.Num_racines || item.num_racines || item.code) {
            // single rubrique object
            pushEntry('', '', item);
          } else if (typeof item === 'object') {
            // try to iterate object values
            Object.values(item).forEach(v => {
              if (Array.isArray(v)) v.forEach(r => { if (r && (r.Num_racines || r.code)) pushEntry(item.type||'', item.categorie||'', r); });
            });
          }
        });
      } else if (typeof data.rubriques === 'object' && data.rubriques !== null) {
        // case: data.rubriques is an object keyed by category/type
        Object.keys(data.rubriques).forEach(key => {
          const group = data.rubriques[key] || {};
          const parts = String(key).split('/').map(p => p.trim()).filter(Boolean);
          let typeName = '';
          let categoryName = '';

          if (parts.length >= 2) {
            typeName = parts[0];
            categoryName = parts[1];
          } else if (parts.length === 1) {
            categoryName = parts[0];
            typeName = '';
          } else {
            typeName = group.type || '';
            categoryName = group.categorie || '';
          }

          const rubs = Array.isArray(group.rubriques) ? group.rubriques : (Array.isArray(group) ? group : []);
          if (Array.isArray(rubs) && rubs.length) {
            rubs.forEach(r => pushEntry(typeName, categoryName, r));
          } else {
            const maybe = Object.values(group).flat();
            maybe.forEach(m => { if (m && (m.Num_racines || m.code)) pushEntry(typeName, categoryName, m); });
          }
        });
      } else {
        console.warn('fillRubriqueSelect: format inattendu pour /rubriques-tva-vente', data);
      }

      // Group entries by type -> category -> rubriques
      const grouped = {};
      entries.forEach(e => {
        const t = e.type || '';
        const c = e.category || '';
        grouped[t] = grouped[t] || {};
        grouped[t][c] = grouped[t][c] || [];
        grouped[t][c].push(e.rubrique);
      });

      // Flatten into ordered array and sort special categories first (ex: "Ca non imposable")
      const SPECIAL_CAT_RE = /^ca\s*non\s*imposable$/i;
      const flat = [];
      Object.keys(grouped).forEach(t => {
        Object.keys(grouped[t]).forEach(c => {
          flat.push({ type: t, category: c, rubriques: grouped[t][c] });
        });
      });

      flat.sort((a,b) => {
        const aSpecial = SPECIAL_CAT_RE.test(a.category);
        const bSpecial = SPECIAL_CAT_RE.test(b.category);
        if (aSpecial && !bSpecial) return -1;
        if (!aSpecial && bSpecial) return 1;
        const cmpType = (a.type || '').localeCompare(b.type || '');
        if (cmpType !== 0) return cmpType;
        return (a.category || '').localeCompare(b.category || '');
      });

      // Build output order with type headers then category then rubriques
      const outputOrder = [];
      const seenTypes = new Set();
      flat.forEach(item => {
        if (item.type && !seenTypes.has(item.type)) {
          seenTypes.add(item.type);
          outputOrder.push({ kind: 'type', value: item.type });
        }
        outputOrder.push({ kind: 'category', value: item.category, type: item.type, rubriques: item.rubriques });
      });

      // Append options to select
      outputOrder.forEach(entry => {
        if (entry.kind === 'type') {
          const typeName = entry.value;
          const opt = new Option(typeName, '__type__' + typeName, false, false);
          $(opt).addClass('type-header').prop('disabled', true).attr('data-is-type', '1');
          $sel.append(opt);
        } else if (entry.kind === 'category') {
          const catName = entry.value;
          const typeName = entry.type || '';
          const displayCat = catName ? ('  ' + catName) : '  ';
          const catOpt = new Option(displayCat, '__cat__' + typeName + '##' + catName, false, false);
          $(catOpt).addClass('category-header').prop('disabled', true).attr('data-is-category','1').attr('data-type', typeName);
          $sel.append(catOpt);

          const rubs = Array.isArray(entry.rubriques) ? entry.rubriques : [];
          rubs.forEach(r => {
            try {
              const code = String(r.Num_racines || r.num_racines || r.code || r.id || '').trim();
              if (!code) return;
              if (excludedNumRacines.includes(Number(code))) return;
              const labelParts = [
                r.Num_racines || r.num_racines || r.code || code,
                r.Nom_racines || r.Nom || r.nom || '',
                (typeof r.Taux !== 'undefined') ? `(${parseFloat(r.Taux).toFixed(2)}%)` : ''
              ];
              const text = labelParts.filter(Boolean).join(': ').replace(': (',' (');
              const opt = new Option('    ' + text, code, false, false);
              $(opt).addClass('subcategory-item');
              const searchText = `${code} ${r.Nom_racines || r.Nom || ''} ${typeName} ${catName}`;
              $(opt).attr('data-search-text', searchText);
              $(opt).attr('data-type', typeName);
              $(opt).attr('data-category', catName);
              $sel.append(opt);
            } catch(e){ /* ignore malformed rubrique */ }
          });
        }
      });

      // choix du dropdownParent selon modal (sécurise focus)
      const parentModal = (selectId.indexOf('edit') !== -1) ? $('#journalModalEdit') : $('#ajouterJournalModal');
      const dropdownParent = parentModal.length ? parentModal : $(document.body);

      // (Re)initialisation Select2
      $sel.select2({
        width: '100%',
        placeholder: "Rechercher une rubrique TVA...",
        allowClear: true,
        dropdownAutoWidth: true,
        minimumResultsForSearch: 0,
        dropdownParent: dropdownParent,
        templateResult: function(data){
          if (!data || !data.element) return data.text || '';
          const $el = $(data.element);
          if ($el.data('is-type')) return $('<div style="font-weight:700;color:#6c757d;">' + (data.text||'') + '</div>');
          if ($el.data('is-category')) return $('<div style="font-weight:600;padding-left:6px;color:#495057;">' + (data.text||'') + '</div>');
          return $('<div style="padding-left:12px;">' + (data.text||'') + '</div>');
        },
        matcher: function(params, data){
          if ($.trim(params.term) === '') return data;
          const term = params.term.toLowerCase();
          const $el = data.element ? $(data.element) : $();
          const searchText = ($el.data('search-text') || '').toString().toLowerCase();
          const text = (data && data.text) ? data.text.toString().toLowerCase() : '';
          if (searchText.indexOf(term) !== -1) return data;
          if (text.indexOf(term) !== -1) return data;
          return null;
        },
        escapeMarkup: function(m){ return m; }
      });

      // ensure selectedValue exists as option
      if (selectedValue !== undefined && selectedValue !== null && String(selectedValue) !== '') {
        const exists = $sel.find(`option[value="${String(selectedValue)}"]`).length > 0;
        if (!exists) {
          $sel.append(new Option(String(selectedValue) + ' (non listé)', String(selectedValue), true, false));
        }
      }

      // apply value after init (small timeout to ensure select2 mounted)
      setTimeout(function(){
        try {
          if (selectedValue !== undefined && selectedValue !== null && String(selectedValue) !== '') {
            $sel.val(String(selectedValue)).trigger('change.select2');
          } else {
            $sel.val(null).trigger('change.select2');
          }
        } catch(e){
          // fallback silent
        }
        deferred.resolve();
      }, 60);

    } catch (err) {
      console.error('fillRubriqueSelect: erreur lors du traitement', err);
      deferred.resolve();
    }
  })
  .fail(function(){
    console.warn('fillRubriqueSelect: erreur chargement /rubriques-tva-vente');
    deferred.resolve();
  })
  .always(function(){
    // cleanup : retirer le marqueur de remplissage
    $sel.removeData('fillPromise');
  });

  // store the promise for guarding concurrent calls
  $sel.data('fillPromise', fillPromise);

  // Return the promise (jQuery promise)
  // also return our deferred.promise to keep compatibility with callers expecting .then() or .done()
  return fillPromise.then(function(){ return deferred.promise(); });
}

  function toggleRubriqueAdd(selectedValue){
    ensureRubriqueAddExists();
    const type = $('#type_journal').val();
    const $container = $('#rubrique_container_add');
    if (type === 'Ventes') {
      $container.show();
      fillRubriqueSelect('rubrique_tva', selectedValue);
    } else {
      if ($('#rubrique_tva').hasClass('select2-hidden-accessible')) $('#rubrique_tva').select2('destroy');
      $('#rubrique_tva').val(null).empty();
      $container.hide();
    }
  }

  function toggleRubriqueEdit(selectedValue){
    ensureRubriqueEditExists();
    const type = $('#editTypeJournal').val();
    const $container = $('#rubrique_container_edit');
    if (type === 'Ventes') {
      $container.show();
      fillRubriqueSelect('edit_rubrique_tva', selectedValue);
    } else {
      if ($('#edit_rubrique_tva').hasClass('select2-hidden-accessible')) $('#edit_rubrique_tva').select2('destroy');
      $('#edit_rubrique_tva').val(null).empty();
      $container.hide();
    }
  }

  // binds
  $('#type_journal').on('change select2:select select2:unselect', function(){
    // preserve current contre_partie when changing type
    const preserve = $('#contre_partie').val();
    toggleRubriqueAdd();
    loadComptesAdd($(this).val(), preserve);
  });
  if ($('#type_journal').val()) {
    // load comptes at start preserving any existing value
    loadComptesAdd($('#type_journal').val(), $('#contre_partie').val());
    toggleRubriqueAdd();
  }
  $(document).on('change select2:select select2:unselect', '#editTypeJournal', function(){
    const preserve = $('#editContrePartie').val();
    toggleRubriqueEdit();
    loadComptesEdit($(this).val(), preserve);
  });
  $('#ajouterJournalModal').on('shown.bs.modal', function(){
    toggleRubriqueAdd();
    // ensure contre_partie is loaded when modal opens
    loadComptesAdd($('#type_journal').val(), $('#contre_partie').val());
  });
  // journalModalEdit shown handler is set above (prevents double init)

  // === override editJournal to pre-select rubrique correctement (promise-safe) ===
  var _oldEditJournal = window.editJournal;
  window.editJournal = function(rowData){
    // si ancien comportement existe, on l'appelle (il peut remplir champs et ouvrir modal)
    if (typeof _oldEditJournal === 'function') {
      try { _oldEditJournal(rowData); } catch(e) { console.warn('old editJournal error', e); }
    } else {
      // fallback: remplir champs et ouvrir modal
      $("#editCodeJournal").val(rowData.code_journal);
      $("#editIntituleJournal").val(rowData.intitule);
      $("#editTypeJournal").val(rowData.type_journal).trigger('change');
      $("#editContrePartie").val(rowData.contre_partie);
      $("#editJournalId").val(rowData.id);
      $("#edit_identifiant_fiscal").val(rowData.identifiant_fiscal);
      $("#edit_ice").val(rowData.ice || '');
      toggleIfIceFields(rowData.type_journal);
      loadComptesEdit(rowData.type_journal, rowData.contre_partie);
      $('#journalModalEdit').modal('show');
    }

    // Pré-sélection robuste de la rubrique TVA
    const sel = rowData.rubrique_tva ?? rowData.Num_racines ?? '';
    fillRubriqueSelect('edit_rubrique_tva', sel).then(function(){
      if (sel && $('#edit_rubrique_tva').length) {
        $('#edit_rubrique_tva').val(String(sel)).trigger('change.select2');
      }
    });
  };

  // toggleIfIceFields used in edit form
  function toggleIfIceFields(selectedType){
    if (selectedType === "Banque") $("#edit_if_ice_container").show();
    else { $("#edit_if_ice_container").hide(); $("#edit_identifiant_fiscal, #edit_ice").val(''); $("#error-identifiant_fiscal").hide(); $("#edit_identifiant_fiscal").removeClass('is-valid is-invalid'); }
  }

  // edit form submit -> include rubrique_tva
  $('#journalFormEdit').on('submit', function(e){
    e.preventDefault();
    var journalId = $("#editJournalId").val();
    var data = {
      _token: $("meta[name='csrf-token']").attr('content'),
      code_journal: $("#editCodeJournal").val(),
      type_journal: $("#editTypeJournal").val(),
      contre_partie: $("#editContrePartie").val(),
      intitule: $("#editIntituleJournal").val(),
      rubrique_tva: ($("#edit_rubrique_tva").length && $("#edit_rubrique_tva").val()) ? $("#edit_rubrique_tva").val() : null,
      identifiant_fiscal: $("#edit_identifiant_fiscal").val().trim(),
      ice: $("#edit_ice").val().trim()
    };

    // IF validation
    if (data.identifiant_fiscal && !/^\d{7,8}$/.test(data.identifiant_fiscal)) {
      $("#edit_identifiant_fiscal").addClass("is-invalid");
      $("#error-identifiant_fiscal").text("Le champ IF doit contenir exactement 7 ou 8 chiffres.").show();
      return;
    }
    var $btn = $(this).find("[type='submit']").prop("disabled", true);
    $.ajax({
      url: "/journaux/" + journalId,
      type: "PUT",
      data: data,
      success: function(response){
        if (response.success) {
          table.setData("/journaux/data").then(function(){
            // toast succès
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Journal mis à jour avec succès', showConfirmButton: false, timer: 1800 });
            $('#journalModalEdit').modal('hide');
          });
        } else {
          Swal.fire({ icon: 'error', title: 'Erreur', text: (response.message || "Erreur lors de la mise à jour du journal.") });
        }
      },
      error: function(xhr){
        Swal.fire({ icon: 'error', title: 'Erreur', text: (xhr.responseJSON?.message || "Erreur lors de la mise à jour du journal.") });
      },
      complete: function(){ $btn.prop("disabled", false); }
    });
  });

  // Ajout: submit -> inclure rubrique_tva
  $('#ajouterJournalForm').on('submit', function(e){
    e.preventDefault();
    const codeJournal = $("#code_journal").val().trim();
    const typeJournal = $("#type_journal").val();
    let contrePartie = $("#contre_partie").val();
    const intitule = $("#intitule").val();
    const rubrique_tva = $("#rubrique_tva").length ? $("#rubrique_tva").val() : null;
    const ifVal = $("#identifiant_fiscal").val();
    const iceVal = $("#ice").val();

    if (!codeJournal) {
      Swal.fire({ icon: 'warning', title: 'Champ requis', text: 'Le code journal est requis.' });
      return;
    }
    if (typeJournal === "Opérations Diverses") contrePartie = null;

    // check duplication
    $.ajax({
      url: '/check-journal',
      type: 'GET',
      data: { code_journal: codeJournal }
    }).done(function(response){
      if (response.exists) {
        Swal.fire({ icon: 'error', title: 'Doublon', text: 'Ce code journal existe déjà pour cette société' });
        $("#code_journal").addClass('is-invalid');
      } else {
        $("#code_journal").removeClass('is-invalid');
        let formData = {
          _token: $("meta[name='csrf-token']").attr('content'),
          code_journal: codeJournal,
          type_journal: typeJournal,
          contre_partie: contrePartie,
          intitule: intitule,
          rubrique_tva: (typeJournal === 'Ventes') ? (rubrique_tva || null) : null,
          identifiant_fiscal: (typeJournal === 'Banque') ? ifVal : null,
          ice: (typeJournal === 'Banque') ? iceVal : null,
        };

        $.ajax({
          url: '/journaux',
          type: 'POST',
          data: formData,
          success: function(response){
            table.setData("/journaux/data").then(function(){
              Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Journal ajouté avec succès', showConfirmButton: false, timer: 1800 });
              $('#ajouterJournalForm')[0].reset();
              $('#type_journal, #contre_partie').val(null).trigger('change');
              $("#code_journal").focus();
              $('#ajouterJournalModal').modal('hide');
            });
          },
          error: function(xhr){
            Swal.fire({ icon: 'error', title: 'Erreur', text: "Erreur lors de l'ajout du journal." });
            console.error(xhr);
          }
        });
      }
    }).fail(function(xhr){
      console.error('Erreur check-journal', xhr);
      Swal.fire({ icon: 'error', title: 'Erreur', text: "Erreur lors de la vérification du code journal." });
    });
  });

//   // Ajout: when type_journal change, reload comptes
//   $('#type_journal').on('change', function(){
//     // preserve current contre_partie when changing
//     const preserve = $('#contre_partie').val();
//     loadComptesAdd($(this).val(), preserve);
//   });

  // Suppression journal simple (avec SweetAlert confirm)
  window.deleteJournal = function(journalId, codeJournal){
    const societeId = $('meta[name="societe-id"]').attr('content');
    if (!societeId) {
      Swal.fire({ icon: 'error', title: 'Erreur', text: 'Aucune société sélectionnée dans la session.' });
      return;
    }

    const isMouvemented = codeJournal && codeJournal.trim().toLowerCase() === 'mouvementé';

    function proceedDelete() {
      $.ajax({
        url: `/journaux/${journalId}`,
        type: 'DELETE',
        data: { _token: $('meta[name="csrf-token"]').attr('content'), societeId },
        success: function (response) {
          table.setData("/journaux/data");
          Swal.fire({ icon: 'success', title: 'Supprimé', text: response.message });
        },
        error: function (xhr) {
          console.error("Erreur lors de la suppression :", xhr.responseText);
          let errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : "Erreur lors de la suppression.";
          Swal.fire({ icon: 'error', title: 'Erreur', text: errorMsg });
        }
      });
    }

    if (!isMouvemented) {
      Swal.fire({
        title: 'Confirmer la suppression',
        text: `Êtes-vous sûr de vouloir supprimer le journal "${codeJournal}" ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
      }).then((result) => {
        if (result.isConfirmed) {
          proceedDelete();
        }
      });
    } else {
      // suppression sans confirmation pour 'mouvementé'
      proceedDelete();
    }
  };

}); // fin DOMContentLoaded

</script>

</body>

@endsection
