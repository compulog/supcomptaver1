@extends('layouts.user_type.auth')

@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="societeId" content="{{ session('societeId') }}">
  <title>Gestion de Rubrique TVA </title>
  <!-- jQuery UI CSS -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<!-- jQuery + jQuery UI JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

  <link href="https://unpkg.com/tabulator-tables@5.5.1/dist/css/tabulator.min.css" rel="stylesheet" />
  <!-- XLSX pour Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- jsPDF & autoTable pour PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
/* Ajuste la hauteur et la police de tous les header-filters */
.tabulator .tabulator-header .tabulator-col .tabulator-header-filter input {
  height: 24px !important;       /* Hauteur souhaitée */
  font-size: 12px !important;     /* Taille de la police */
  padding: 2px 4px !important;    /* Espacements internes */
  box-sizing: border-box;         /* Pour gérer padding + border dans la hauteur */
}



    #controls.glass-controls {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  border-radius: 16px;
  padding: 12px 20px;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 12px;
  margin: 20px auto;
  max-width: 1000px;
  transition: 0.3s ease;
}

/* Boutons élégants */
.btn-glass {
  padding: 10px 16px;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.9);
  color: #2c3e50;
  box-shadow: 0 4px 10px rgba(0,0,0,0.08);
  transition: 0.3s;
  cursor: pointer;
  font-size: 0.92rem;
}

.btn-glass:hover {
  background: rgba(255, 255, 255, 1);
  transform: translateY(-1px);
  box-shadow: 0 6px 14px rgba(0,0,0,0.1);
}

.btn-glass.green {
  background: #e8f5e9;
  color: #1b5e20;
}

.btn-glass.red {
  background: #ffebee;
  color: #b71c1c;
}




    .ui-autocomplete {
  z-index: 99999 !important;
}
/* correspond à rowHeight: 28px */
.tabulator .tabulator-cell .confirm-btn,
.tabulator .tabulator-cell .cancel-btn,
.tabulator .tabulator-cell .hide-row-btn,
.tabulator .tabulator-cell .delete-btn {
  font-size: 14px;      /* icônes légèrement plus petites */
  width: 20px;          /* bouton carré */
  height: 20px;         /* hauteur du bouton */
  line-height: 20px;    /* centrage vertical du contenu */
  padding: 0;           /* plus de padding inutile */
  margin: 0 2px;        /* petit espacement horizontal */
  border: none;         /* sans bordure */
  background: transparent;
  cursor: pointer;
}


    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f7fa;
      margin: 0;
      padding: 10px;
      display: flex;
      flex-direction: column;
      height: 100vh;
      box-sizing: border-box;
    }
    h1 {
      text-align: center;
      color: #34495e;
      margin-bottom: 10px;
      font-weight: 700;
    }
    #controls {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-bottom: 10px;
      flex-wrap: wrap;
    }
    button {
      background: #2c3e50;
      color: white;
      border: none;
      padding: 8px 16px;
      font-size: 0.9rem;
      font-weight: 600;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background: #34495e;
    }
    #table-container {
      flex-grow: 1;
      overflow: hidden;
    }
    .tabulator {
      height: 100%;
    }
    .confirm-btn {
      background-color: #27ae60;
    }
    .confirm-btn:hover {
      background-color: #2ecc71;
    }
  </style>
</head>
<body>
  <h5>Gestion de Rubrique TVA</h5>
  <!-- Ajout bouton Import -->
{{-- <div id="controls">
  <button id="add-row-btn">➕ Ajouter une rubrique</button>
  <button id="show-hidden-btn">👁️ Afficher les rubriques masquées</button>
  <button id="export-xlsx-btn">📤 Exporter Excel</button>
  <button id="export-pdf-btn">📄 Exporter PDF</button>
</div> --}}

<div id="controls">
  <button id="add-row-btn" class="btn-glass">
    ➕ Ajouter une rubrique
  </button>

  <button id="show-hidden-btn" class="btn-glass">
    👁️ Réaficher Rubriques
  </button>

  <button id="export-excel-btn" class="btn-glass green">
    📊 Exporter Excel
  </button>

  <button id="export-pdf-btn" class="btn-glass red">
    📄 Exporter PDF
  </button>
</div>



  <!-- Modal Ajouter Compte -->
<div class="modal fade" id="planComptableModalAdd" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog shadow-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter Compte</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <form id="planComptableFormAdd">
          @csrf
          <input type="hidden" id="compte_tva_length" value="{{ $societe->nombre_chiffre_compte }}">
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
          <div class="d-flex justify-content-end mt-3">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-1"></i> Annuler
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-plus-circle me-1"></i> Ajouter
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

  <input type="hidden" id="compte_tva_length" value="{{ $societe->nombre_chiffre_compte }}">

  <div id="table-container">
    <div id="racines-table"></div>
  </div>

  <script src="https://unpkg.com/tabulator-tables@5.5.1/dist/js/tabulator.min.js"></script>
  <script>
    let hiddenRows = new Set();
    const csrfToken = document.querySelector("meta[name='csrf-token']").content;
    const societeId = document.querySelector("meta[name='societeId']").content;


   // 1) cellEditable : si confirmé, seul compte_tva reste éditable
// function cellEditable(cell) {
//   const d = cell.getRow().getData();
//   if (!d.persisted) return true;                             // nouvelle ligne : tout éditable
//   if (d.isConfirmed) return cell.getField() === "compte_tva"; // après confirm : seul compte_tva
//   return true;                                                // persistée mais pas confirmée : tout éditable
// }
function cellEditable(cell) {
  const field = cell.getField();
  const data = cell.getRow().getData();

  // Le champ compte_tva est toujours éditable
  if (field === 'compte_tva') return true;

  // Pour les autres champs, vérifier si mouvementé
  const isMouvementee = data.mouvementee === true; // Assure-toi que ce flag est présent côté serveur

  if (isMouvementee) {
    // Affiche un message d'info
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'info',
      title: 'Modification impossible : rubrique utilisée dans fournisseurs',
      showConfirmButton: false,
      timer: 2000
    });
    return false; // interdiction d'éditer
  }

  return true; // autorise l'édition
}


function actionCellClick(e, cell) {
  const row = cell.getRow();
  const data = row.getData();
  const isNew = !data.persisted;

  // Masquer la ligne si déjà confirmée
  if (data.isConfirmed && e.target.classList.contains("hide-row-btn")) {
    hiddenRows.add(data.id);
    row.getElement().style.display = "none";

    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'info',
      title: 'Rubrique masquée avec succès',
      showConfirmButton: false,
      timer: 1500
    });
    return;
  }

  // Lors du clic sur bouton de confirmation
  if (e.target.classList.contains("confirm-btn")) {
    const required = ["Num_racines", "Nom_racines", "Taux", "type"];
    for (let f of required) {
      if (!data[f]) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'warning',
          title: `Le champ "${f}" est obligatoire`,
          showConfirmButton: false,
          timer: 2000
        });
        return;
      }
    }

    // Vérifie si quelque chose a changé
    const hasChanges = (
      isNew ||
      data.Num_racines !== data.original_Num_racines ||
      data.Nom_racines !== data.original_Nom_racines ||
      data.Taux !== data.original_Taux ||
      data.type !== data.original_type ||
      data.categorie !== data.original_categorie ||
      data.compte_tva !== data.original_compte_tva
    );

    if (!hasChanges) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'Aucune modification détectée',
        showConfirmButton: false,
        timer: 1500
      });
      return;
    }

    const url = isNew ? "/racines" : `/racines/${data.id}`;
    const method = isNew ? "POST" : "PUT";

    const payload = {
      Num_racines: data.Num_racines,
      Nom_racines: data.Nom_racines,
      Taux: parseFloat(data.Taux),
      type: data.type,
      compte_tva: data.compte_tva || null,
      categorie: data.categorie || null,
      societe_id: societeId
    };

    fetch(url, {
      method,
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
        "X-Societe-Id": societeId,
        "X-Requested-With": "XMLHttpRequest"
      },
      body: JSON.stringify(payload)
    })
      .then(async res => {
        const text = await res.text();
        try {
          const body = JSON.parse(text);

          if (!res.ok) {
            throw new Error(body.message || body.error || "Erreur serveur");
          }

          // Met à jour la ligne comme confirmée/persistée
          row.update({
            id: body.id || data.id,
            isConfirmed: true,
            persisted: true,
            original_Num_racines: data.Num_racines,
            original_Nom_racines: data.Nom_racines,
            original_Taux: data.Taux,
            original_type: data.type,
            original_categorie: data.categorie,
            original_compte_tva: data.compte_tva
          });

          row.getTable().redraw(true);

          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: isNew ? '✅ Nouvelle rubrique enregistrée' : '✏️ Modifications enregistrées',
            showConfirmButton: false,
            timer: 1800
          });

        } catch (e) {
          if (text.startsWith("<")) {
            throw new Error("Erreur serveur : réponse HTML reçue au lieu de JSON");
          } else {
            throw new Error(e.message);
          }
        }
      })
      .catch(err => {
        console.error(err);

        const msg = err.message.includes("Num_racines")
          ? "⚠️ Ce code existe déjà pour cette société."
          : `❌ Erreur : ${err.message}`;

        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: msg,
          showConfirmButton: false,
          timer: 2500
        });
      });
  }
}




  const catMap    = {};
  const compteMap = {};

  // Fetch categories selon type ("Achat" ou "Vente")
   // Récupère les catégories distinctes pour un type donné
  async function fetchCategories(type) {
    const res = await fetch(`/get-categories?type=${encodeURIComponent(type)}`, {
      headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Societe-Id': societeId , "X-Requested-With": "XMLHttpRequest"  // ← ajoutez cette tête
}
    });
    console.log("resultat" + res);
    if (!res.ok) return [];
    return res.json();
  }

  // Fetch comptes TVA depuis plan_comptable via backend
 async function fetchCompteTVA(type) {
      const res = await fetch(`/get-compte-tva-type?type=${encodeURIComponent(type)}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Societe-Id': societeId ,  "X-Requested-With": "XMLHttpRequest"  // ← ajoutez cette tête
}
      });
      if (!res.ok) return {};
      const data = await res.json();
      return data.reduce((map, item) => {
        map[item.compte] = `${item.compte} - ${item.intitule}`;
        return map;
      }, {});
    }


  (async function init() {


    const table = new Tabulator('#racines-table', {
      ajaxURL: '/racines',
      ajaxConfig: { method:'GET', credentials:'same-origin',
        headers:{ 'X-CSRF-TOKEN': csrfToken, 'X-Societe-Id': societeId }
      },
ajaxResponse: (_, __, resp) => {
    return resp.map(r => {
      // Si pas encore de compte_tva, on applique nos règles
      if (!r.compte_tva) {
        const t = (r.type || "").toLowerCase().trim();
        const c = (r.categorie || "").toLowerCase().trim();

        if (t === "les déductions") {
          r.compte_tva = (c === "immobilisations")
            ? "34510000"  // déductions + immobilisations
            : "34520000"; // déductions + autre catégorie
        }
        else if (t === "ca imposable") {
          r.compte_tva = "44550000";
        }
      }

      // Marque comme persisté / confirmé pour l’affichage du bouton Masquer
      r.isConfirmed = true;
      r.persisted   = true;
      return r;
    });
  },
    // === layout & dimensionnement automatique ===
  layout: 'fitColumns',       // cale chaque colonne à la largeur max de son contenu
  autoColumns: false,         // on ne génère pas de colonnes supplémentaires
  resizableColumns: false,    // on désactive le redimensionnement manuel
  variableRowHeight: false,   // lignes d’une hauteur fixe
  rowHeight: 29,              // hauteur de ligne réduite (en px)

    //    layout: 'fitData',
      height: '500px',
      placeholder: 'Aucune donnée disponible',
      movableColumns: true,
      tooltips: true,

      columns: [
{
  title: 'Code',
  field: 'Num_racines',
  headerFilter: 'input',
  headerFilterParams: {
      elementAttributes: {
        style: 'height:24px; font-size:12px; padding:2px 4px; box-sizing:border-box;'
      }
    },
  editor: 'input',
  editable: cellEditable,
  validator: [
    { type: 'required', parameters: { element: 'Num_racines' } },
    { type: 'unique' }
  ],
  cellEdited: async function (cell) {
    const row = cell.getRow();
    const data = row.getData();
    const newVal = cell.getValue();
    const oldVal = cell.getOldValue();

    if (!cell.isValid()) {
      const errs = cell.getValidationErrors();
      let message = 'Champ invalide';

      if (errs.some(e => e.toLowerCase().includes('unique'))) {
        message = '⚠️ Ce code existe déjà';
      } else if (errs.some(e => e.toLowerCase().includes('required'))) {
        message = '⚠️ Le code est requis';
      }

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'warning',
        title: message,
        showConfirmButton: false,
        timer: 2000
      });

      setTimeout(() => {
        cell.edit();
        const input = cell.getElement().querySelector('input');
        if (input) input.focus();
      }, 300);
      return;
    }

    if (!data.persisted) return;

    try {
      const res = await fetch(`/racines/${data.id}`, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Societe-Id': societeId,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          Num_racines: newVal,
          Nom_racines: data.Nom_racines,
          Taux: data.Taux,
          type: data.type
        })
      });
      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || JSON.stringify(payload));

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: '✏️ Modifications enregistrées',
        showConfirmButton: false,
        timer: 1500
      });
    } catch (err) {
      console.error(err);
      cell.setValue(oldVal);
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: '❌ Erreur : ' + err.message,
        showConfirmButton: false,
        timer: 2000
      });
    }
  }
},
{ title:'Intitulé', field:'Nom_racines', headerFilter:'input',headerFilterParams: {
      elementAttributes: {
        style: 'height:24px; font-size:12px; padding:2px 4px; box-sizing:border-box;'
      }
    }, editor:'input', editable: cellEditable,
  cellEdited: async function (cell) {
    const row = cell.getRow();
    const data = row.getData();
    const newVal = cell.getValue();
    const oldVal = cell.getOldValue();

    if (!data.persisted) return;

    try {
      const res = await fetch(`/racines/${data.id}`, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Societe-Id': societeId,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          Nom_racines: newVal,
          Num_racines: data.Num_racines,
          Taux: data.Taux,
          type: data.type
        })
      });
      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || JSON.stringify(payload));

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: '✏️ Modifications enregistrées',
        showConfirmButton: false,
        timer: 1500
      });
    } catch (err) {
      console.error(err);
      cell.setValue(oldVal);
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: '❌ Erreur : ' + err.message,
        showConfirmButton: false,
        timer: 2000
      });
    }
  }
},
{ title:'Taux (%)', field:'Taux', headerFilter:'input',headerFilterParams: {
      elementAttributes: {
        style: 'height:24px; font-size:12px; padding:2px 4px; box-sizing:border-box;'
      }
    }, formatter:'money', formatterParams:{precision:2}, editor:'input', editable: cellEditable,
  cellEdited: async function (cell) {
    const row = cell.getRow();
    const data = row.getData();
    const newVal = cell.getValue();
    const oldVal = cell.getOldValue();

    if (!data.persisted) return;

    try {
      const res = await fetch(`/racines/${data.id}`, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Societe-Id': societeId,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          Taux: newVal,
          Num_racines: data.Num_racines,
          Nom_racines: data.Nom_racines,
          type: data.type
        })
      });
      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || JSON.stringify(payload));

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: '✏️ Modifications enregistrées',
        showConfirmButton: false,
        timer: 1500
      });
    } catch (err) {
      console.error(err);
      cell.setValue(oldVal);
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: '❌ Erreur : ' + err.message,
        showConfirmButton: false,
        timer: 2000
      });
    }
  }
},
        // ==== COLONNE TYPE ====
 {
  title: 'Type',
  field: 'type',
  headerFilter: 'select',
  headerFilterParams: {
    elementAttributes: {
      style: 'height:24px; font-size:12px; padding:2px 4px; box-sizing:border-box;'
    }
  },
  editor: 'select',
  editable: cellEditable,
  editorParams: {
    values: {
      'CA non imposable': 'CA non imposable',
      'CA imposable': 'CA imposable',
      'Les déductions': 'Les déductions'
    }
  },
  cellEdited: async function (cell) {
    const row = cell.getRow();
    const data = row.getData();
    const newVal = cell.getValue();
    const oldVal = cell.getOldValue();

    if (!data.persisted) return;

    try {
      const res = await fetch(`/racines/${data.id}`, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Societe-Id': societeId,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ type: newVal })
      });

      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || JSON.stringify(payload));

      // Réinitialiser les dépendants côté UI
      row.update({ categorie: '', compte_tva: '' });

      // Recharger les maps locales
      catMap[newVal] = catMap[newVal] || await fetchCategories(newVal);
      compteMap[newVal] = compteMap[newVal] || await fetchCompteTVA(newVal);

      // Redessine + active l’édition directe sur la catégorie
      cell.getTable().redraw(true);
      row.getCell('categorie').edit();

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: '✏️ Type mis à jour',
        showConfirmButton: false,
        timer: 1500
      });

    } catch (err) {
      console.error(err);
      cell.setValue(oldVal);
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: '❌ Erreur : ' + err.message,
        showConfirmButton: false,
        timer: 2000
      });
    }
  }
},

        // CATEGORIE selon type
{
  title: 'Catégorie',
  field: 'categorie',
  headerFilter: 'input',
  headerFilterParams: {
    elementAttributes: {
      style: 'height:24px; font-size:12px; padding:2px 4px; box-sizing:border-box;'
    }
  },
  editor: function (cell, onRendered, success, cancel) {
    const rowData = cell.getRow().getData();
    const type = rowData.type || '';
    const list = catMap[type] || [];

    const values = list.reduce((acc, item) => {
      acc[item] = item;
      return acc;
    }, {});

    const input = document.createElement('input');
    input.type = 'text';
    input.placeholder = '-- Sélectionner une catégorie --';
    input.classList.add('tabulator-autocomplete');
    input.style.width = '100%';
    input.style.padding = '4px';
    input.value = cell.getValue() || (type === 'CA non imposable' ? 'CA non imposable' : '');

    const entries = Object.entries(values).map(([val, label]) => ({
      label: label,
      value: val
    }));

    onRendered(() => {
      input.focus();
      input.select();
      setTimeout(() => {
        $(input).autocomplete("search", input.value);
        const menu = $(".ui-autocomplete");
        if (menu.length) {
          menu.position({
            my: "left top",
            at: "left bottom",
            of: input
          });
        }
      }, 10);
    });

    input.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        success(input.value);
      } else if (e.key === 'Escape') {
        cancel();
      }
    });

    input.addEventListener('blur', () => {
      if (!$('.ui-menu-item-wrapper.ui-state-focus').length) {
        success(input.value);
      }
    });

    $(input).autocomplete({
      source: entries,
      minLength: 0,
      autoFocus: true,
      select: function (event, ui) {
        event.preventDefault();
        input.value = ui.item.value;
        success(ui.item.value);
      }
    });

    return input;
  },
  editable: cellEditable,
  cellEdited: async function(cell) {
    const row = cell.getRow();
    const data = row.getData();
    const newVal = cell.getValue();
    const oldVal = cell.getOldValue();

    if (!data.persisted) return;

    try {
      const res = await fetch(`/racines/${data.id}`, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Societe-Id': societeId,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ categorie: newVal })
      });
      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || JSON.stringify(payload));

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: '✏️ Catégorie mise à jour',
        showConfirmButton: false,
        timer: 1500
      });
    } catch (err) {
      console.error(err);
      cell.setValue(oldVal);
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: '❌ Erreur : ' + err.message,
        showConfirmButton: false,
        timer: 2000
      });
    }
  }
},


        // ==== COLONNE COMPTE TVA ====
{
  title: 'Compte TVA',
  field: 'compte_tva',
  headerFilter: 'input',
  headerFilterParams: {
      elementAttributes: {
        style: 'height:24px; font-size:12px; padding:2px 4px; box-sizing:border-box;'
      }
    },
  editor: function(cell, onRendered, success, cancel) {
    const rowData = cell.getRow().getData();
    const rawType = rowData.type || '';
    const type = rawType.trim().toLowerCase();
    const prefix = type.includes('déduction') ? '345' : (type.includes('imposable') && !type.includes('non')) ? '445' : null;
    const base = compteMap[rawType] || {};
    const values = Object.entries(base).filter(([code]) => !prefix || code.startsWith(prefix));

    const input = document.createElement('input');
    input.type = 'text';
    input.classList.add('form-control');
    input.style.width = '100%';
    input.placeholder = 'Rechercher ou ajouter un compte';
    input.value = cell.getValue() || '';

    onRendered(() => {
      input.focus();
      input.select();
    });

    $(input).autocomplete({
      source: function(request, response) {
        const term = request.term.trim().toLowerCase();
        const resultList = values
          .filter(([code, label]) => code.toLowerCase().includes(term) || label.toLowerCase().includes(term))
          .map(([code, label]) => ({ label, value: code }));

        const alreadyExists = values.some(([code]) => code.toLowerCase() === term);

        if (!alreadyExists && term !== '') {
          resultList.push({
            label: `➕ Ajouter le compte "${request.term}"`,
            value: '__ADD__:' + request.term
          });
        }

        response(resultList);
      },
      minLength: 0,
      select: function(event, ui) {
        event.preventDefault();
        const selectedVal = ui.item.value;

        if (selectedVal.startsWith('__ADD__:')) {
          const newCode = selectedVal.replace('__ADD__:', '').trim();
          document.getElementById('compte').value = newCode;
          document.getElementById('intitule').value = '';
          const modal = new bootstrap.Modal(document.getElementById('planComptableModalAdd'));
          modal.show();

          const form = document.getElementById('planComptableFormAdd');
          const submitHandler = async (e) => {
            e.preventDefault();
            const compte = document.getElementById('compte').value.trim();
            const intitule = document.getElementById('intitule').value.trim();
            const expectedLength = parseInt(document.getElementById('compte_tva_length').value);
            const existingCodes = Object.keys(compteMap[rawType] || {});

            if (compte.length !== expectedLength) {
              Swal.fire('Erreur', `Le compte doit comporter exactement ${expectedLength} chiffres.`, 'warning');
              return;
            }
            if (existingCodes.includes(compte)) {
              Swal.fire('Erreur', 'Ce compte existe déjà !', 'error');
              return;
            }
            if (!intitule) {
              Swal.fire('Erreur', 'Intitulé obligatoire !', 'error');
              return;
            }

            try {
              const resp = await fetch('/plan-comptable', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
                  'X-Societe-Id': societeId,
                      "X-Requested-With": "XMLHttpRequest"  // ← ajoutez cette tête

                },
                body: JSON.stringify({ compte, intitule })
              });
              if (!resp.ok) throw new Error("Erreur lors de l'ajout");
              const saved = await resp.json();
              compteMap[rawType] = {
                ...compteMap[rawType],
                [saved.compte]: `${saved.compte} - ${saved.intitule}`
              };
              modal.hide();
              cell.setValue(saved.compte);
              success(saved.compte);
              Swal.fire({ toast: true, icon: 'success', title: 'Compte ajouté', position: 'top-end', timer: 1500 });
            } catch (err) {
              Swal.fire('Erreur', err.message, 'error');
            } finally {
              form.removeEventListener('submit', submitHandler);
            }
          };
          form.addEventListener('submit', submitHandler, { once: true });
        } else {
          input.value = selectedVal;
          success(selectedVal);
        }
      },
      focus: function(event, ui) {
        event.preventDefault();
        input.value = ui.item.label;
      }
    });

    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') success(input.value);
      else if (e.key === 'Escape') cancel();
    });

    input.addEventListener('blur', () => {
      success(input.value);
    });

    return input;
  },
  editable: cellEditable,

  // ←— AJOUT de cellEdited pour la modification automatique
  cellEdited: async function(cell) {
    const row    = cell.getRow();
    const data   = row.getData();
    const newVal = cell.getValue();
    const oldVal = cell.getOldValue();

    // ✅ IGNORER si la ligne n’est pas encore persistée
    if (!data.persisted) return;

    try {
      const res = await fetch(`/racines/${data.id}`, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Societe-Id': societeId,    "X-Requested-With": "XMLHttpRequest"  // ← ajoutez cette tête

        },
        body: JSON.stringify({
          compte_tva: newVal,
          categorie: data.categorie
        })
      });
      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || JSON.stringify(payload));

      // Mise à jour interne (isConfirmed/persisted)
      row.update({ isConfirmed: true, persisted: true });

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: '✏️ Modifications enregistrées',
        showConfirmButton: false,
        timer: 1500
      });
    } catch (err) {
      console.error(err);
      cell.setValue(oldVal);
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: '❌ Erreur : ' + err.message,
        showConfirmButton: false,
        timer: 2000
      });
    }
  },
},



        {
  title: "Actions",
  hozAlign: "center",
  headerSort: false,
  formatter: cell => {
    const data = cell.getData();

    if (!data.persisted) {
      // nouvelle ligne : confirmer (✔️) ou annuler (✖️)
      return `
        <button class='confirm-btn'>✔️</button>
        <button class='cancel-btn'>✖️</button>
      `;
    } else {
      // ligne existante : masquer (👁️) + supprimer (🗑️)
      return `
        <button class='hide-row-btn'>👁️</button>
        <button class='delete-btn'>🗑️</button>
      `;
    }
  },
  cellClick: async function(e, cell) {
    const row  = cell.getRow();
    const data = row.getData();

    // ❌ Annuler une nouvelle ligne
    if (!data.persisted && e.target.classList.contains("cancel-btn")) {
      row.delete();
      return;
    }

    // ✔️ Confirmer nouvelle ligne ou modification
    if (e.target.classList.contains("confirm-btn")) {
      actionCellClick(e, cell);
      return;
    }

    // 👁️ Masquer ligne existante
    if (data.isConfirmed && e.target.classList.contains("hide-row-btn")) {
      hiddenRows.add(data.id);
      row.getElement().style.display = "none";
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'Rubrique masquée',
        showConfirmButton: false,
        timer: 1500
      });
      return;
    }

    // 🗑️ Supprimer ligne existante
    if (e.target.classList.contains("delete-btn")) {
      // 1) Vérification préalable
      try {
        const checkRes = await fetch(`/racines/${data.id}/check-fournisseurs`, {
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Societe-Id': societeId,    "X-Requested-With": "XMLHttpRequest"  // ← ajoutez cette tête

          }
        });
        const { used } = await checkRes.json(); // { used: true|false }

       // 🗑️ Suppression bloquée si utilisé
if (used) {
  await Swal.fire({
    title: 'Impossible de supprimer',
    text:  'Cette rubrique est utilisée dans des fournisseurs.',
    icon:  'warning',
    confirmButtonText: 'OK',       // bouton OK
    allowOutsideClick: false,      // empêche la fermeture en cliquant à l’extérieur
  });
  return;
}

} catch (err) {
  console.error(err);
  await Swal.fire({
    title: 'Erreur de vérification',
    text:  'Impossible de vérifier l’utilisation de cette rubrique.',
    icon:  'error',
    confirmButtonText: 'OK',       // bouton OK
    allowOutsideClick: false,
  });
  return;
}


      // 2) Confirmation auprès de l’utilisateur
      const result = await Swal.fire({
        title: 'Confirmer la suppression ?',
        text: `La rubrique (${data.Num_racines}) sera définitivement supprimée.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Supprimer',
        cancelButtonText: 'Annuler',
      });

      if (!result.isConfirmed) return;

      // 3) Suppression sur le serveur
      try {
        const delRes = await fetch(`/racines/${data.id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Societe-Id': societeId,    "X-Requested-With": "XMLHttpRequest"  // ← ajoutez cette tête

          }
        });
        if (!delRes.ok) throw new Error('Échec suppression');

        row.delete();
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Rubrique supprimée',
          showConfirmButton: false,
          timer: 1500
        });
      } catch (err) {
        console.error(err);
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: 'Erreur suppression',
          showConfirmButton: false,
          timer: 2000
        });
      }
    }
  },
},

        ],
      });



      // ← Collez cet écouteur juste ici
 // Préchargement des maps sur le premier chargement
     table.on("dataLoaded", async rows=>{
        const types = Array.from(new Set(rows.map(r=>r.type).filter(Boolean)));
        await Promise.all(types.map(async t=>{
          if(!catMap[t])    catMap[t]    = await fetchCategories(t);
          if(!compteMap[t]) compteMap[t] = await fetchCompteTVA(t);
        }));
        table.redraw(true);
      });


      document.getElementById("add-row-btn").addEventListener("click", () => {
        const data = table.getData();
        const nextId = data.length ? Math.max(...data.map(r => r.id)) + 1 : 1;
        table.addRow({
          id: nextId,
          Num_racines: '',
          Nom_racines: '',
          Taux: '',
          type: '',
          categorie: '',
          compte_tva: '',
          isConfirmed: false,
          persisted: false
        }, true).then(row => row.getCell('Num_racines').edit());
      });

    document.getElementById("show-hidden-btn").addEventListener("click", () => {
  if (!hiddenRows.size) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'info',
      title: 'Aucune rubrique masquée',
      showConfirmButton: false,
      timer: 1500
    });
    return;
  }

  hiddenRows.forEach(id => {
    const row = table.getRow(id);
    if (row) row.getElement().style.display = '';
  });
  hiddenRows.clear();

  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'success',
    title: 'Rubriques réaffichées',
    showConfirmButton: false,
    timer: 1500
  });

});


  window.addEventListener("DOMContentLoaded", () => {
    document.getElementById("export-excel-btn").addEventListener("click", () => {
      table.download("xlsx", "rubriques_tva.xlsx", {
        sheetName: "Rubriques TVA"
      });
    });

    document.getElementById("export-pdf-btn").addEventListener("click", () => {
      table.download("pdf", "rubriques_tva.pdf", {
        orientation: "landscape",
        title: "Liste des Rubriques TVA",
        autoTable: {
          styles: { fontSize: 9 },
          headStyles: { fillColor: [52, 73, 94] },
          margin: { top: 33 }
        }
      });
    });
  });


    })();

  </script>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
@endsection
