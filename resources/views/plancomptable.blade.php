@extends('layouts.user_type.auth')

@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des comptes</title>

    <!-- CSS externes (une seule inclusion par lib) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/tabulator-tables@6.1.0/dist/css/tabulator.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <!-- Styles personnalisés (conservés / respectés) -->
    <style>
    /* clignote 3 fois, 0.5s par cycle */
    .blink-short { animation: blinker 0.5s linear 3; }
    @keyframes blinker { 50% { opacity: 0; } }

    .icon-3d {
      font-size: 1.2rem;
      transition: transform 0.2s, box-shadow 0.2s;
      box-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }
    .icon-3d:hover {
      transform: translateY(-2px);
      box-shadow: 3px 3px 6px rgba(0,0,0,0.4);
    }
    </style>
</head>
<body>

@if(session('success'))
    <div class="alert alert-success blink-short">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger blink-short">
        {{ session('error') }}
    </div>
@endif

<br>
<div class="container my-3">
    <!-- Ligne de titre et actions -->
    <div class="row align-items-center mb-2">
      <div class="col-md-6">
        <h4 class="text-secondary mb-0">Liste du Plan Comptable</h4>
      </div>
      <div class="col-md-6 text-end">
        <div class="btn-group" role="group" aria-label="Actions">
          <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                  id="addPlanComptableBtn"
                  data-bs-toggle="modal"
                  data-bs-target="#planComptableModalAdd"
                  title="Ajouter">
            <i class="fas fa-plus icon-3d"></i>
            <span>Ajouter</span>
          </button>

          <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
                  id="importPlanComptableBtn"
                  data-bs-toggle="modal"
                  data-bs-target="#importModal"
                  title="Importer">
            <i class="fas fa-file-import icon-3d"></i>
            <span>Importer</span>
          </button>

          <a href="{{ route('plan.comptable.excel') }}"
             class="btn btn-outline-success btn-sm d-flex align-items-center gap-1"
             title="Exporter en Excel">
            <i class="fas fa-file-export icon-3d"></i>
            <span>Excel</span>
          </a>

          <form action="{{ route('export.plan_comptable') }}" method="GET" class="d-inline">
            <input type="hidden" id="societe_id" value="{{ session('societeId') }}">
            <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" title="Exporter en PDF">
              <i class="fas fa-file-pdf icon-3d"></i>
              <span>PDF</span>
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Statistiques -->
    <span id="select-stats" class="text-muted"></span>
<select id="classeFilter">
    <option value="">Toutes</option>
    <option value="1">Classe 1</option>
    <option value="2">Classe 2</option>
    <option value="3">Classe 3</option>
    <option value="4">Classe 4</option>
    <option value="5">Classe 5</option>
    <option value="6">Classe 6</option>
    <option value="7">Classe 7</option>
    <option value="8">Classe 8</option>
    <option value="clients">Clients</option>
    <option value="fournisseurs">Fournisseurs</option>
</select>
    <!-- Tableau des plans comptables -->
    <div id="plan-comptable-table" class="border rounded shadow-sm bg-white p-2" style="font-size: 0.8rem;"></div>
  </div>

  <div class="mt-3">
    <span style="background-color: rgba(233,233,13,0.838); display:inline-block; width:20px; height:20px; border:1px solid black; border-radius:4px;"></span>
    Informations Obligatoires Manquantes
  </div>
  <div>
    <span style="background-color: rgba(228,20,20,0.453); display:inline-block; width:20px; height:20px; border:1px solid black; border-radius:4px;"></span>
    Informations Erronées
  </div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog shadow-lg">
    <div class="modal-content">
      <div class="modal-header d-flex justify-content-between align-items-center bg-dark text-white">
        <h5 class="modal-title" id="importModalLabel">Importation du Plan Comptable</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
       <form id="importForm"
      action="{{ route('plancomptable.import') }}"
      method="POST"
      enctype="multipart/form-data"
      data-expected-length="{{ $societe ? $societe->nombre_chiffre_compte : '' }}">
  @csrf
          <input type="hidden" name="societe_id" value="{{ session('societeId') }}">

          {{-- Fichier --}}
          <div class="mb-3">
            <label for="file" class="form-label">Fichier Excel</label>
            <input type="file" class="form-control" id="file" name="file" accept=".xls,.xlsx,.csv" required>
          </div>

          {{-- Sélections dynamiques --}}
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="colonne_compte" class="form-label">Colonne Compte</label>
              <select id="colonne_compte" name="colonne_compte" class="form-select" required>
                <option value="">-- Sélectionnez --</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="colonne_intitule" class="form-label">Colonne Intitulé</label>
              <select id="colonne_intitule" name="colonne_intitule" class="form-select" required>
                <option value="">-- Sélectionnez --</option>
              </select>
            </div>
          </div>

          {{-- Aperçu --}}
          <div class="mb-3">
            <h6>Aperçu (5 premières lignes)</h6>
            <div class="table-responsive">
              <table class="table table-sm table-bordered" id="previewTable" style="display:none;">
                <thead class="table-dark">
                  <tr id="previewHeader"></tr>
                </thead>
                <tbody id="previewBody"></tbody>
              </table>
            </div>
          </div>

          {{-- Loader --}}
          <div id="importSpinner" class="text-center my-3 d-none">
            <div class="spinner-border" role="status" style="width:2rem; height:2rem;"></div>
            <span class="ms-2">Importation en cours...</span>
          </div>

          {{-- Boutons --}}
          <div class="d-flex justify-content-between">
            <button type="reset" id="resetBtn" class="btn btn-light">
              <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-upload me-1"></i> Importer
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ajouter -->
<div class="modal fade" id="planComptableModalAdd" tabindex="-1" role="dialog" aria-labelledby="planComptableModalLabel" aria-hidden="true">
    <div class="modal-dialog shadow-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="planComptableModalLabel">Ajouter Compte</h5>
                <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="planComptableFormAdd">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="compte" class="form-label">Compte</label>
                            <input type="text" class="form-control shadow-sm" id="compte" name="compte" required>
                        </div>
                        <div class="col-md-6">
                            <label for="intitule" class="form-label">Intitulé</label>
                            <input type="text" class="form-control shadow-sm" id="intitule" name="intitule" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="reset" class="btn btn-light d-flex align-items-center">
                            <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-primary d-flex align-items-center ms-2" id="addSubmitBtn">
                            <i class="bi bi-plus-circle me-1"></i> Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifier -->
<div class="modal fade" id="planComptableModalEdit" tabindex="-1" role="dialog" aria-labelledby="planComptableModalLabel" aria-hidden="true">
    <div class="modal-dialog shadow-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="planComptableModalLabel">Modifier Plan Comptable</h5>
                <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="planComptableFormEdit">
                    @csrf
                    <input type="hidden" id="editPlanComptableId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editCompte" class="form-label">Compte</label>
                            <input type="text" class="form-control shadow-sm" id="editCompte" name="compte" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editIntitule" class="form-label">Intitulé</label>
                            <input type="text" class="form-control shadow-sm" id="editIntitule" name="intitule" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="reset" class="btn btn-light d-flex align-items-center">
                            <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-primary d-flex align-items-center ms-2" id="editSubmitBtn">
                            <i class="bi bi-check-circle me-1"></i> Modifier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Scripts JS (une inclusion par lib) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/tabulator-tables@6.1.0/dist/js/tabulator.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const societeId = document.getElementById('societe_id') ? document.getElementById('societe_id').value : '{{ session("societeId") }}';
    const nombreChiffresCompteRaw = @json($societe ? $societe->nombre_chiffre_compte : null);
    const nombreChiffresCompte = (nombreChiffresCompteRaw !== null) ? parseInt(nombreChiffresCompteRaw, 10) : null;

    // si config manquante -> alerte et disable controls
    if (! nombreChiffresCompte || isNaN(nombreChiffresCompte) || nombreChiffresCompte <= 0) {
        Swal.fire({
            title: 'Société / configuration manquante',
            html: 'Le paramètre <b>nombre_chiffre_compte</b> est absent ou invalide pour la société en session.<br>Merci de sélectionner une société correctement configurée.',
            icon: 'warning'
        });
        document.querySelectorAll('button, input, select').forEach(el => el.disabled = true);
        return;
    }

    function filterByClasse(data, filterValue) {
    const compte = data.compte.toString();

    if(filterValue === "fournisseurs") {
        return compte.startsWith("4411");
    } else if(filterValue === "clients") {
        return compte.startsWith("3421");
    } else {
        // filterValue est un chiffre de 1 à 8
        return compte.startsWith(filterValue.toString());
    }
}
    // appliquer maxlength côté client (UI)
    $('#compte').attr('maxlength', nombreChiffresCompte);
    $('#editCompte').attr('maxlength', nombreChiffresCompte);

    // importForm expected length
    const importForm = document.getElementById('importForm');
    importForm.dataset.expectedLength = nombreChiffresCompte;

    // --- Tabulator init ---
  // ==================== TABLEAU PLAN COMPTABLE ====================
var table = new Tabulator("#plan-comptable-table", {
   ajaxURL: "/plancomptable/data", // Appelle automatiquement getData()
        ajaxConfig: "GET",
    height: "600px", // ✅ pour afficher toutes les lignes sans pagination
    layout: "fitColumns",
    selectable: true,
    ajaxResponse: function(url, params, response) {
    // Ici 'response' peut être { data: [...], meta: ..., expected_length: ... }
    return response.data; // Tabulator ne prend que le tableau
},

    // ajaxResponse: function(url, params, response) {
    //     console.log('Réponse PlanComptable:', response);

    //     // Si le backend renvoie un objet { data, expected_length, meta }
    //     let rows = Array.isArray(response.data) ? response.data : response;

    //     // Afficher total lignes si meta disponible
    //     if (response.meta) {
    //         document.getElementById('select-stats').textContent =
    //             `Total: ${rows.length} | ins:${response.meta.inserted || 0} upd:${response.meta.updated || 0} conf:${response.meta.conflicts || 0}`;
    //     } else {
    //         document.getElementById('select-stats').textContent = `Total: ${rows.length}`;
    //     }

    //     return rows;
    // },
    columns: [
        {
            title: `<input type='checkbox' id='select-all' />
                    <i class="fas fa-trash-alt" id="delete-all-icon" style="cursor: pointer;" title="Supprimer les lignes sélectionnées"></i>`,
            field: "select",
            formatter: "rowSelection",
            headerSort: false,
            hozAlign: "center",
            width: 60,
            cellClick: function(e, cell) { cell.getRow().toggleSelect(); }
        },
        { title: "Compte", field: "compte", editor: "input", headerFilter: "input", headerHozAlign: "center" },
        { title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input", headerHozAlign: "center" },
        { title: "État", field: "etat", headerHozAlign: "center", hozAlign: "center",visible:false },
        {
            title: "Actions",
            field: "action-icons",
            formatter: function() {
                return `
                    <i class='fas fa-edit text-primary edit-icon' style='cursor: pointer; margin-right:8px;'></i>
                    <i class='fas fa-trash-alt text-danger delete-icon' style='cursor: pointer;'></i>
                `;
            },
            cellClick: function(e, cell) {
                const row = cell.getRow();
                if (e.target.classList.contains('edit-icon')) editPlanComptable(row.getData());
                if (e.target.classList.contains('delete-icon')) deletePlanComptable(row.getData().id);
            },
            hozAlign: "center",
            headerHozAlign: "center",
            headerSort: false,
        }
    ],

    rowFormatter: function(row) {
        const { etat } = row.getData();
        const el = row.getElement();

        if (etat === "manquant") {
            el.style.backgroundColor = "rgba(233,233,13,0.8)";
        } else if (etat === "erreur") {
            el.style.backgroundColor = "rgba(228,20,20,0.45)";
            el.style.color = "#721c24";
        } else {
            el.style.backgroundColor = "";
            el.style.color = "";
        }
    },
    rowSelected: function(row) { row.getElement().classList.add("bg-light"); },
    rowDeselected: function(row) { row.getElement().classList.remove("bg-light"); }
});

    table.setFilter(filterByClasse, "1");

    // sauvegarde automatique quand édition inline (PUT)
    table.on("cellEdited", function(cell){
        const rowData = cell.getRow().getData();
        if (!rowData.id) return;

        const payload = { compte: rowData.compte, intitule: rowData.intitule };

        fetch('/plancomptable/' + rowData.id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.ok ? res.json() : res.json().then(j => Promise.reject(j)))
        .then(data => {
            if (data.success) {
                Toastify({ text: data.message || "Mis à jour ✔", duration: 2200 }).showToast();
                if (data.row) cell.getRow().update(data.row);
            } else {
                Swal.fire('Erreur', data.error || 'Échec mise à jour', 'error');
                table.replaceData("/plancomptable/data");
            }
        })
        .catch(err => {
            console.error('Erreur update:', err);
            Swal.fire('Erreur', (err && err.message) || 'Erreur réseau', 'error');
            table.replaceData("/plancomptable/data");
        });
    });

    // select-all handling
    document.addEventListener("change", function (e) {
      if (e.target && e.target.id === 'select-all') {
        const checked = e.target.checked;
        const allRows = table.getRows();
        allRows.forEach(r => checked ? r.select() : r.deselect());
      }
    });

    // suppression multiple
    document.addEventListener("click", function (e) {
      if (e.target && e.target.id === 'delete-all-icon') {
        const selectedRows = table.getSelectedRows();
        if (selectedRows.length === 0) {
          Toastify({ text: "Aucune ligne sélectionnée.", duration: 3000 }).showToast();
          return;
        }
        const idsToDelete = selectedRows.map(r => r.getData().id);

        Swal.fire({
          title: `Supprimer ${idsToDelete.length} ligne(s) ?`,
          text: "Les lignes sélectionnées seront supprimées définitivement.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Oui, supprimer'
        }).then((result) => {
          if (!result.isConfirmed) return;

          Swal.fire({ title: 'Suppression en cours...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

         // suppression multiple (remplace l'ancien fetch /plancomptable/deleteSelected)
fetch('/plancomptable/deleteSelected', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify({ ids: idsToDelete })
})
.then(res => {
    if (res.status === 204) return { ok: true, body: {} }; // no content -> treat as success
    return res.json().then(body => ({ ok: res.ok, body }));
})
.then(({ ok, body }) => {
    Swal.close();
    // Accept either { success: true } or { status: 'success' } or ok===true
    const isSuccess = (body && body.success === true) || (body && body.status === 'success') || ok && (body && Object.keys(body).length === 0);

    if (isSuccess) {
        const msg = (body && (body.message || body.msg)) || 'Suppression effectuée.';
        Toastify({ text: msg, duration: 3000 }).showToast();
        table.replaceData("/plancomptable/data");
        const selectAll = document.getElementById('select-all'); if (selectAll) selectAll.checked = false;
    } else {
        const errMsg = (body && (body.error || body.message)) ? (body.error || body.message) : 'Échec de la suppression';
        Swal.fire('Erreur', errMsg, 'error');
    }
})
.catch(err => {
    Swal.close();
    console.error('Erreur deleteSelected:', err);
    Swal.fire('Erreur', 'Erreur serveur lors de la suppression', 'error');
});
 });
      }
    });

    table.on("rowSelectionChanged", function(data, rows) {
        document.getElementById("select-stats").innerHTML = rows.length; // Afficher le nombre de lignes sélectionnées
    });

    // ----------------- Ajout (modal) -----------------
    $('#planComptableModalAdd').on('shown.bs.modal', function () {
        $('#compte').focus();
    });

    $("#planComptableFormAdd").on("submit", function (e) {
        e.preventDefault();

        const addSubmitBtn = document.getElementById('addSubmitBtn');
        const compte = $("#compte").val().trim();
        const intitule = $("#intitule").val().trim();

        if (compte.length !== nombreChiffresCompte) {
            Swal.fire('Erreur', `Le compte doit comporter exactement ${nombreChiffresCompte} chiffres.`, 'warning');
            $('#compte').focus();
            return;
        }
        if (!intitule) {
            Swal.fire('Erreur', 'Le champ Intitulé est obligatoire.', 'warning');
            $('#intitule').focus();
            return;
        }

        // vérif doublon local rapide
        const comptesExistants = table.getData().map(row => row.compte);
        if (comptesExistants.includes(compte)) {
            Swal.fire('Erreur', 'Ce compte existe déjà !', 'warning');
            $('#compte').focus();
            return;
        }

        addSubmitBtn.disabled = true;
        addSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> En cours...';

        fetch('/plancomptable', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ compte: compte, intitule: intitule, societe_id: societeId })
        })
        .then(res => res.json().then(j => ({ ok: res.ok, body: j })))
        .then(({ ok, body }) => {
            addSubmitBtn.disabled = false;
            addSubmitBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Ajouter';

            if (!ok) {
                // afficher message détaillé si disponible
                const msg = body && (body.error || body.message) ? (body.error || body.message) : ('Erreur lors de l\'ajout (code ' + (body && body.code ? body.code : '??') + ')');
                Swal.fire('Erreur', msg, 'error');
                return;
            }

            if (body.success) {
                // Fermer le modal proprement
                const modalEl = document.getElementById('planComptableModalAdd');
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();

                // enlever backdrop s'il reste (safety)
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');

                Toastify({ text: body.message || "Plan comptable ajouté ✔", duration: 2500 }).showToast();

                // reset form et rafraîchir table
                $("#planComptableFormAdd")[0].reset();
                table.replaceData("/plancomptable/data");
            } else {
                Swal.fire('Erreur', body.error || 'Une erreur est survenue.', 'error');
            }
        })
        .catch(err => {
            addSubmitBtn.disabled = false;
            addSubmitBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Ajouter';
            console.error('Erreur add:', err);
            Swal.fire('Erreur', 'Erreur serveur lors de l\'ajout. Voir console.', 'error');
        });
    });

    // ----------------- Edit (modal) -----------------
    $("#planComptableFormEdit").on("submit", function(e) {
        e.preventDefault();
        const editSubmitBtn = document.getElementById('editSubmitBtn');
        const id = $("#editPlanComptableId").val();
        const compte = $("#editCompte").val().trim();
        const intitule = $("#editIntitule").val().trim();

        if (!id) return Swal.fire('Erreur', 'ID manquant', 'error');
        if (compte.length !== nombreChiffresCompte) { Swal.fire('Erreur', `Le compte doit comporter exactement ${nombreChiffresCompte} chiffres.`, 'warning'); return; }
        if (!intitule) { Swal.fire('Erreur', 'Le champ Intitulé est obligatoire.', 'warning'); return; }

        editSubmitBtn.disabled = true;
        editSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> En cours...';

        fetch('/plancomptable/' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ compte: compte, intitule: intitule })
        })
        .then(res => res.json().then(j => ({ ok: res.ok, body: j })))
        .then(({ ok, body }) => {
            editSubmitBtn.disabled = false;
            editSubmitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Modifier';

            if (!ok) {
                const msg = body && (body.error || body.message) ? (body.error || body.message) : 'Erreur lors de la mise à jour';
                Swal.fire('Erreur', msg, 'error');
                return;
            }

            if (body.success) {
                const modalEl = document.getElementById('planComptableModalEdit');
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');

                Toastify({ text: body.message || "Mis à jour ✔", duration: 2200 }).showToast();
                $("#planComptableFormEdit")[0].reset();
                $("#editPlanComptableId").val("");
                table.replaceData("/plancomptable/data");
            } else {
                Swal.fire('Erreur', body.error || 'Erreur mise à jour', 'error');
            }
        })
        .catch(err => {
            editSubmitBtn.disabled = false;
            editSubmitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Modifier';
            console.error(err);
            Swal.fire('Erreur', 'Erreur serveur lors de la mise à jour', 'error');
        });
    });

    // ouvrir modal edit
    window.editPlanComptable = function(data) {
        $("#editPlanComptableId").val(data.id);
        $("#editCompte").val(data.compte);
        $("#editIntitule").val(data.intitule);
        const modalEl = document.getElementById('planComptableModalEdit');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    };

    // suppression simple - fetch + messages détaillés
    window.deletePlanComptable = function(id) {
        Swal.fire({
            title: 'Supprimer ce plan comptable ?',
            text: "Cette action est irréversible et supprimera aussi les clients/fournisseurs liés.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer'
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Suppression en cours...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch('/plancomptable/' + id, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({})
            })
            .then(res => res.json().then(j => ({ ok: res.ok, body: j })))
            .then(({ ok, body }) => {
                Swal.close();
                if (!ok) {
                    const msg = body && (body.error || body.message) ? (body.error || body.message) : 'Erreur suppression';
                    Swal.fire('Erreur', msg, 'error');
                    return;
                }
                if (body.success) {
                    Toastify({ text: body.message || "Plan comptable supprimé ✔", duration: 2200 }).showToast();
                    table.replaceData("/plancomptable/data");
                } else {
                    Swal.fire('Erreur', body.error || 'Erreur suppression', 'error');
                }
            })
            .catch(err => {
                Swal.close();
                console.error('Erreur delete:', err);
                Swal.fire('Erreur', 'Une erreur est survenue lors de la suppression. Voir console.', 'error');
            });
        });
    };

    // ----------------- Import XLSX preview and submit -----------------
    const fileInput = document.getElementById('file');
    const selectCompte = document.getElementById('colonne_compte');
    const selectIntitule = document.getElementById('colonne_intitule');
    const previewTable = document.getElementById('previewTable');
    const previewHeader = document.getElementById('previewHeader');
    const previewBody = document.getElementById('previewBody');
    const importSpinner = document.getElementById('importSpinner');
    const resetBtn = document.getElementById('resetBtn');

    function resetPreview() {
        selectCompte.innerHTML = '<option value="">-- Sélectionnez --</option>';
        selectIntitule.innerHTML = '<option value="">-- Sélectionnez --</option>';
        previewHeader.innerHTML = "";
        previewBody.innerHTML = "";
        previewTable.style.display = "none";
    }
    resetBtn.addEventListener("click", resetPreview);

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return resetPreview();

        const reader = new FileReader();
        reader.onload = ({ target }) => {
            const wb = XLSX.read(new Uint8Array(target.result), { type: 'array' });
            const rows = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { header: 1 });

            resetPreview();
            if (!rows.length) return;

            rows[0].forEach((h, i) => {
                const txt = h || `Col ${i+1}`;
                selectCompte.append(new Option(txt, i+1));
                selectIntitule.append(new Option(txt, i+1));
                const th = document.createElement("th");
                th.textContent = txt;
                previewHeader.appendChild(th);
            });

            rows.slice(1, 6).forEach(r => {
                const tr = document.createElement("tr");
                rows[0].forEach((_, i) => {
                    const td = document.createElement("td");
                    td.textContent = r[i] ?? "";
                    tr.appendChild(td);
                });
                previewBody.appendChild(tr);
            });

            previewTable.style.display = "table";
        };
        reader.readAsArrayBuffer(file);
    });

    importForm.addEventListener("submit", function (e) {
        e.preventDefault();
        importSpinner.classList.remove("d-none");

        fetch(importForm.action, {
            method: "POST",
            body: new FormData(importForm),
            headers: { "X-Requested-With": "XMLHttpRequest", "X-CSRF-TOKEN": csrf }
        })
        .then(res => res.json().then(j => ({ ok: res.ok, body: j })))
        .then(({ ok, body }) => {
            importSpinner.classList.add("d-none");
            if (!ok || !body.success) {
                const msg = body && (body.error || body.message) ? (body.error || body.message) : 'Erreur import';
                return Swal.fire("Erreur", msg, "error");
            }

            // rafraîchir depuis backend pour garder cohérence
            table.replaceData("/plancomptable/data");

            Swal.fire({ icon: "success", title: "Importation réussie", html: body.message || '', timer: 2000, showConfirmButton: false });

            // fermer modal proprement
            const modalEl = document.getElementById("importModal");
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.hide();
            document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
            document.body.classList.remove("modal-open");

            importForm.reset();
            resetPreview();
        })
        .catch(err => {
            importSpinner.classList.add("d-none");
            console.error('Erreur import:', err);
            Swal.fire("Erreur serveur", "Impossible de traiter le fichier.", "error");
        });
    });

    // Cleanup backdrop when manual hide
    document.getElementById('importModal').addEventListener('hidden.bs.modal', function () {
        document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
        document.body.classList.remove("modal-open");
    });
    document.getElementById("classeFilter").addEventListener("change", function(){
    var val = this.value;
    if(val === "") {
        table.clearFilter();
    } else {
        table.setFilter(filterByClasse, val);
    }
});

}); // DOMContentLoaded
</script>

</body>
</html>
@endsection
