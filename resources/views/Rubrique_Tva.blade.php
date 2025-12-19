@extends('layouts.user_type.auth')

@section('content')
@php
  $compte_len = $societe->nombre_chiffre_compte ?? '';
@endphp

<br>
<h5 style="margin-top:0.5rem; font-weight:700;">Gestion de Rubrique TVA</h5>

<div id="controls" style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 12px;">
  <button id="add-row-btn" class="btn-glass">➕ Ajouter une rubrique</button>
  <button id="show-hidden-btn" class="btn-glass">👁️ Réafficher Rubriques</button>
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
          <!-- on centralise la longueur attendue dans un seul champ -->
          <input type="hidden" id="compte_tva_length" value="{{ $compte_len }}">
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
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Ajouter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Table container (hauteur explicite) -->
<div id="table-container" style="height:620px; margin-top:8px;">
  <div id="racines-table" style="height:100%"></div>
</div>

<!-- Librairies (si layout les inclut déjà, supprimez les doublons dans le layout) -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link href="https://unpkg.com/tabulator-tables@5.5.1/dist/css/tabulator.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- JS libs -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://unpkg.com/tabulator-tables@5.5.1/dist/js/tabulator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
<!-- Bootstrap bundle pour modal et utilitaires JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 + exports -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<style>
/* styles utiles */
.tabulator .tabulator-header .tabulator-col .tabulator-header-filter input { height:24px!important; font-size:12px!important; padding:2px 4px!important; box-sizing:border-box; }
.ui-autocomplete { z-index: 99999 !important; }
.row-loading { opacity:0.6; position:relative; }
.row-loading::after { content:''; position:absolute; right:8px; top:6px; width:14px; height:14px; border:2px solid rgba(0,0,0,0.12); border-top-color:rgba(0,0,0,0.6); border-radius:50%; animation:spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.select2-plus-wrapper { display:flex; gap:.5rem; align-items:center; width:100%; box-sizing:border-box; }
.select2-plus-select { flex:1; min-width:0; }
.select2-plus-btn { height:34px; min-width:34px; padding:.25rem .45rem; border-radius:.35rem; cursor:pointer; }
.btn-glass { padding:8px 12px; border-radius:8px; border:1px solid rgba(0,0,0,0.08); background:#fff; cursor:pointer; margin-right:8px; }
.actions-btn { background: transparent; border: none; cursor: pointer; margin: 0 2px; font-size: 18px; transition: transform 0.1s ease; }
.actions-btn:hover { transform: scale(1.2); }
.trash-emoji { color: #e53935; text-shadow: 0 0 1px #b71c1c; }
.eye-emoji { color: #424242; text-shadow: 0 0 1px #00000020; }
.compte-cell-display { display:flex; align-items:center; justify-content:space-between; width:100%; box-sizing:border-box; font-size:12px; }
.compte-cell-text { flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.compte-cell-text .placeholder { color:#999; font-style:italic; }
.actions-btn{
  background: transparent;
  border: none;
  padding: 6px;
  margin: 0 4px;
  cursor: pointer;
  font-size: 16px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
}
.actions-btn:hover { background: rgba(0,0,0,0.04); transform: translateY(-1px); }

/* couleur corbeille */
.actions-btn .fa-trash-alt { color: #e53935; }

/* oeil barré gris */
.actions-btn .fa-eye-slash { color: #424242; }

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const csrfToken = document.querySelector("meta[name='csrf-token']")?.content || '{{ csrf_token() }}';
  const societeId = document.querySelector("meta[name='societeId']")?.content || '{{ session('societeId') }}';

  // caches
  const catMap = {};
  const compteMap = {};
  let hiddenRows = new Set();

  // tiny tracker for last interaction to control toasts on blocked edit
  if (!window._tabulatorLastCellInteraction) {
    window._tabulatorLastCellInteraction = { el: null, ts: 0 };
    document.addEventListener('pointerdown', (ev) => {
      try {
        const cellEl = ev.target.closest && ev.target.closest('.tabulator-cell');
        if (cellEl) {
          window._tabulatorLastCellInteraction.el = cellEl;
          window._tabulatorLastCellInteraction.ts = Date.now();
        }
      } catch (e) {}
    }, { capture: true });
    document.addEventListener('keydown', (ev) => {
      try {
        if (ev.key === 'Enter' || ev.key === ' ' || ev.key === 'Spacebar') {
          const cellEl = document.activeElement && document.activeElement.closest && document.activeElement.closest('.tabulator-cell');
          if (cellEl) {
            window._tabulatorLastCellInteraction.el = cellEl;
            window._tabulatorLastCellInteraction.ts = Date.now();
          }
        }
      } catch (e) {}
    }, { capture: true });
  }

  // safe fetch helper
  async function safeFetchJson(url, opts = {}) {
    try {
      const res = await fetch(url, { credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Societe-Id': societeId, 'X-Requested-With': 'XMLHttpRequest', ...(opts.headers||{}) }, ...opts });
      const text = await res.text();
      try {
        return { ok: res.ok, data: text ? JSON.parse(text) : null, status: res.status };
      } catch (e) {
        console.error('Invalid JSON response for', url, text);
        return { ok: res.ok, data: null, status: res.status };
      }
    } catch (e) {
      console.error('Fetch error', url, e);
      return { ok: false, data: null, status: 0 };
    }
  }

  // normalize server resp (array or grouped)
  function normalizeServerResponse(resp) {
    try {
      if (!resp) return [];
      if (Array.isArray(resp)) return resp;
      if (typeof resp === 'object') {
        const desiredOrder = { 'CA non imposable': 1, 'CA imposable': 2, 'Les déductions': 3 };
        let rows = Object.entries(resp).flatMap(([type, arr]) => (Array.isArray(arr) ? arr.map(r => ({ ...r, type: r.type ?? type, type_order: desiredOrder[type] ?? 99 })) : []));
        rows.sort((a, b) => ((a.type_order || 99) - (b.type_order || 99)) || (String(a.Num_racines).localeCompare(String(b.Num_racines))));
        return rows;
      }
      return [];
    } catch (e) {
      console.error('normalizeServerResponse error', e);
      return [];
    }
  }

  // fetch categories & compte TVA from server (lazy cache)
  async function fetchCategories(type) {
    if (!type) return [];
    if (catMap[type]) return catMap[type];
    const { ok, data } = await safeFetchJson(`/get-categories?type=${encodeURIComponent(type)}`);
    catMap[type] = Array.isArray(data) ? data : [];
    return catMap[type];
  }
  async function fetchCompteTVA(type) {
    if (!type) return {};
    if (compteMap[type]) return compteMap[type];
    const { ok, data } = await safeFetchJson(`/get-compte-tva-type?type=${encodeURIComponent(type)}`);
    let items = data;
    if (data && data.items) items = data.items;
    const map = (Array.isArray(items) ? items : []).reduce((m, it) => { m[it.compte] = `${it.compte} - ${it.intitule}`; return m; }, {});
    compteMap[type] = map;
    return map;
  }

  // open add compte modal (calls /plan-comptable then updates cache & table)
  function openAddModalWithPrefill(prefill, type, onSaved) {
    document.getElementById('compte').value = prefill || '';
    document.getElementById('intitule').value = '';

    const modalEl = document.getElementById('planComptableModalAdd');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    const form = document.getElementById('planComptableFormAdd');

    const submitHandler = async function(ev) {
      ev.preventDefault();
      const compte = document.getElementById('compte').value.trim();
      const intitule = document.getElementById('intitule').value.trim();
      const expectedLength = parseInt(document.getElementById('compte_tva_length').value || '0', 10);

      if (expectedLength && compte.length !== expectedLength) {
        Swal.fire('Erreur', `Le compte doit comporter exactement ${expectedLength} chiffres.`, 'warning');
        return;
      }
      if (!intitule) {
        Swal.fire('Erreur', 'Intitulé obligatoire', 'error');
        return;
      }

      try {
        const resp = await fetch('/plan-comptable', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Societe-Id': societeId, 'X-Requested-With': 'XMLHttpRequest' },
          body: JSON.stringify({ compte, intitule })
        });

        const text = await resp.text();
        const body = text ? JSON.parse(text) : null;
        if (!resp.ok) throw new Error(body?.message || 'Erreur ajout compte');

        const saved = body;

        if (!compteMap[type]) compteMap[type] = {};
        compteMap[type][saved.compte] = `${saved.compte} - ${saved.intitule}`;

        try { table.redraw(true); } catch (e) {}
        if (typeof onSaved === 'function') onSaved(saved);

        Swal.fire({ toast: true, icon: 'success', title: 'Compte ajouté', position: 'top-end', timer: 1500 });
        modal.hide();
      } catch (err) {
        console.error(err);
        Swal.fire('Erreur', err.message || 'Erreur serveur', 'error');
      } finally {
        form.removeEventListener('submit', submitHandler);
      }
    };

    form.removeEventListener('submit', submitHandler);
    form.addEventListener('submit', submitHandler);
  }

  // cellEditable logic (blocks editing when mouvementée, allows compte_tva always)
  function cellEditable(cell) {
    const field = cell.getField();
    const data = cell.getRow().getData();

    if (field === 'compte_tva') return true;

    const isMouvementee = !!data.mouvementee;
    if (isMouvementee) {
      const last = window._tabulatorLastCellInteraction || { el: null, ts: 0 };
      const cellEl = cell.getElement ? cell.getElement() : null;
      const timeSince = Date.now() - (last.ts || 0);
      const THRESHOLD_MS = 700;
      const isRecentInteractionOnThisCell = cellEl && last.el === cellEl && timeSince <= THRESHOLD_MS;

      if (isRecentInteractionOnThisCell) {
        try { if (Swal.isVisible()) Swal.close(); } catch (e) {}
        Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Modification impossible : rubrique utilisée dans fournisseurs', showConfirmButton: false, timer: 2000, timerProgressBar: true });
      }

      return false;
    }

    return true;
  }

  // actionCellClick used inside the Actions column (confirm/save etc.)
  async function actionCellClick(e, cell) {
    const row = cell.getRow();
    const data = row.getData();
    const isNew = !data.persisted;

    // Masquer
    if (data.isConfirmed && e.target.classList.contains("hide-row-btn")) {
      hiddenRows.add(data.id);
      row.getElement().style.display = "none";
      Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Rubrique masquée avec succès', showConfirmButton: false, timer: 1500 });
      return;
    }

    // Confirmer (POST /racines ou PUT /racines/{id})
    if (e.target.classList.contains("confirm-btn")) {
      const required = ["Num_racines", "Nom_racines", "Taux", "type"];
      for (let f of required) {
        if (!data[f]) {
          Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: `Le champ "${f}" est obligatoire`, showConfirmButton: false, timer: 2000 });
          return;
        }
      }

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
        Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Aucune modification détectée', showConfirmButton: false, timer: 1500 });
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

      try {
        const resRaw = await fetch(url, {
          method,
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            "X-Societe-Id": societeId,
            "X-Requested-With": "XMLHttpRequest"
          },
          body: JSON.stringify(payload)
        });

        const text = await resRaw.text();
        let body;
        try { body = text ? JSON.parse(text) : {}; } catch (e) { throw new Error("Réponse invalide du serveur"); }

        if (!resRaw.ok) {
          throw new Error(body.message || body.error || "Erreur serveur");
        }

        // update row from response
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

        // merge compte_options si fournis
        if (body.compte_options && typeof body.compte_options === 'object') {
          const t = body.type || data.type;
          compteMap[t] = { ...(compteMap[t] || {}), ...body.compte_options };
          try { table.redraw(true); } catch (e) {}
        }

        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: isNew ? '✅ Nouvelle rubrique enregistrée' : '✏️ Modifications enregistrées', showConfirmButton: false, timer: 1800 });

      } catch (err) {
        console.error(err);
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: `❌ Erreur : ${err.message}`, showConfirmButton: false, timer: 2500 });
      }
    }
  }

  // Tabulator init
  let table = null;
  try {
    table = new Tabulator('#racines-table', {
      ajaxURL: '/racines',
      ajaxConfig: {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'X-Societe-Id': societeId,
          'X-Requested-With': 'XMLHttpRequest'
        }
      },
     ajaxResponse: (url, params, resp) => {
  try {
    const rows = normalizeServerResponse(resp);

    // --- ORDRE PERSONNALISÉ ---
    const typeOrder = [
      "CA non imposable",
      "CA imposable",
      "Les déductions"
    ];

    const categorieOrderMap = {
      "CA non imposable": [
        // si tu veux un ordre particulier pour CA non imposable, ajoute ici
        // sinon laisse vide pour tri alphabétique des catégories inconnues
      ],
      "CA imposable": [
        "Taux normal de 20% avec droit à déduction",
        "Taux normal de 10% avec droit à déduction",
        "Taux normal de 10% sans droit à déduction",
        "Autres impositions"
      ],
      "Les déductions": [
        "Prestations de service",
        "Autres achats non immobilisés",
        "Immobilisations",
        "Autres déductions"
      ]
    };

    function typeIndex(t) {
      const i = typeOrder.indexOf(t);
      return i === -1 ? 999 : i;
    }

    function categorieIndex(t, cat) {
      const list = categorieOrderMap[t] || [];
      if (!cat) return 2000;
      const trimmed = String(cat).trim();
      const i = list.indexOf(trimmed);
      if (i !== -1) return i;
      // catégorie non listée : placer après les connues, tri alphabétique
      return 1000 + trimmed.toLowerCase().charCodeAt(0); // simple bucket pour stable ordering
    }

    // Tri stable : typeOrder -> categorieOrderMap -> Num_racines (numérique si possible)
    const rowsSorted = rows.slice().sort((a, b) => {
      const ta = a.type || '';
      const tb = b.type || '';
      const tcmp = typeIndex(ta) - typeIndex(tb);
      if (tcmp !== 0) return tcmp;

      const ca = a.categorie || '';
      const cb = b.categorie || '';
      const ccmp = categorieIndex(ta, ca) - categorieIndex(tb, cb);
      if (ccmp !== 0) return ccmp;

      const na = Number(a.Num_racines || 0);
      const nb = Number(b.Num_racines || 0);
      if (!isNaN(na) && !isNaN(nb)) return na - nb;

      return String(a.Num_racines || '').localeCompare(String(b.Num_racines || ''));
    });
    // --- FIN ORDRE PERSONNALISÉ ---

    // remplir cache compteMap depuis les compte_options fournis par serveur (sur rowsSorted)
    for (const r of rowsSorted) {
      const t = r.type || '___unknown';
      if (r.compte_options && typeof r.compte_options === 'object') {
        if (!compteMap[t] || Object.keys(compteMap[t]).length === 0) {
          compteMap[t] = r.compte_options;
        } else {
          compteMap[t] = { ...compteMap[t], ...r.compte_options };
        }
      }
    }

    return rowsSorted.map(r => ({
      ...r,
      persisted: true,
      isConfirmed: true,
      compteMapServer: r.compte_options || {},
      compte_tva: r.compte_tva || '',
      original_Num_racines: r.Num_racines,
      original_Nom_racines: r.Nom_racines,
      original_Taux: r.Taux,
      original_type: r.type,
      original_categorie: r.categorie,
      original_compte_tva: (parseFloat(r.Taux || 0) === 0.0) ? '' : (r.compte_tva || '')
    }));
  } catch (e) {
    console.error('ajaxResponse error', e);
    return [];
  }
},

      layout: 'fitColumns',
      height: '100%',
      placeholder: 'Aucune donnée disponible',
      rowHeight: 29,
     groupBy: "type",
groupToggleElement: "header",
groupHeader: function(value, count, data, group){
  // gestion simple du pluriel en français
  const mot = count > 1 ? 'rubriques' : 'rubrique';
  return `${value} (${count} ${mot})`;
},
      columns: [
        {
          title: 'Code',
          field: 'Num_racines',
          width: 70,
          headerFilter: 'input',
          headerFilterParams: { elementAttributes: { style: 'height:24px; font-size:12px; padding:2px 4px; box-sizing:border-box; width:50px; text-align:right;' } },
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
              if (errs.some(e => e.toLowerCase().includes('unique'))) message = '⚠️ Ce code existe déjà';
              else if (errs.some(e => e.toLowerCase().includes('required'))) message = '⚠️ Le code est requis';

              Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: message, showConfirmButton: false, timer: 2000 });
              setTimeout(() => { cell.edit(); const input = cell.getElement().querySelector('input'); if (input) input.focus(); }, 300);
              return;
            }

            if (!data.persisted) return;

            try {
              const resRaw = await fetch(`/racines/${data.id}`, {
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
              const text = await resRaw.text();
              const payload = text ? JSON.parse(text) : null;
              if (!resRaw.ok) throw new Error(payload?.error || JSON.stringify(payload));

              Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: '✏️ Modifications enregistrées', showConfirmButton: false, timer: 1500 });
            } catch (err) {
              console.error(err);
              cell.setValue(oldVal);
              Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: '❌ Erreur : ' + err.message, showConfirmButton: false, timer: 2000 });
            }
          }
        },
        {
          title:'Intitulé',
          field:'Nom_racines',
           width: 380,

          headerFilter:'input',
          headerFilterParams: { elementAttributes: { style: 'height:24px; font-size:12px; padding:2px 4px; box-sizing:border-box;' } },
          editor:'input',
          editable: cellEditable,
          cellEdited: async function (cell) {
            const row = cell.getRow();
            const data = row.getData();
            const newVal = cell.getValue();
            const oldVal = cell.getOldValue();

            if (!data.persisted) return;

            try {
              const resRaw = await fetch(`/racines/${data.id}`, {
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
              const payloadText = await resRaw.text();
              const payload = payloadText ? JSON.parse(payloadText) : null;
              if (!resRaw.ok) throw new Error(payload?.error || JSON.stringify(payload));

              Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: '✏️ Modifications enregistrées', showConfirmButton: false, timer: 1500 });
            } catch (err) {
              console.error(err);
              cell.setValue(oldVal);
              Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: '❌ Erreur : ' + err.message, showConfirmButton: false, timer: 2000 });
            }
          }
        },
        {
          title:'Taux (%)',
          field:'Taux',
          width:80,
          hozAlign:'center',
          headerFilter:'input',
          formatter:'money',
          formatterParams:{precision:2},
          editor:'input',
          editable: cellEditable,
          cellEdited: async function (cell) {
            const row = cell.getRow();
            const data = row.getData();
            const newVal = cell.getValue();
            const oldVal = cell.getOldValue();

            if (!data.persisted) return;

            try {
              const resRaw = await fetch(`/racines/${data.id}`, {
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
              const payloadText = await resRaw.text();
              const payload = payloadText ? JSON.parse(payloadText) : null;
              if (!resRaw.ok) throw new Error(payload?.error || JSON.stringify(payload));

              Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: '✏️ Modifications enregistrées', showConfirmButton: false, timer: 1500 });
            } catch (err) {
              console.error(err);
              cell.setValue(oldVal);
              Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: '❌ Erreur : ' + err.message, showConfirmButton: false, timer: 2000 });
            }
          }
        },
        {
          title: 'Type',
          field: 'type',
           width: 150,

          headerFilter: 'select',
          headerFilterParams: { elementAttributes: { style: 'height:24px; font-size:12px; width:100px; padding:2px 4px; box-sizing:border-box;' } },
          editor: 'list',
          editable: cellEditable,
          editorParams: { values: { 'CA non imposable': 'CA non imposable', 'CA imposable': 'CA imposable', 'Les déductions': 'Les déductions' } },
          cellEdited: async function (cell) {
            const row = cell.getRow();
            const data = row.getData();
            const newVal = cell.getValue();
            const oldVal = cell.getOldValue();

            if (!data.persisted) {
              catMap[newVal] = catMap[newVal] || await fetchCategories(newVal);
              compteMap[newVal] = compteMap[newVal] || await fetchCompteTVA(newVal);
              return;
            }

            try {
              const resRaw = await fetch(`/racines/${data.id}`, {
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
              const payloadText = await resRaw.text();
              const payload = payloadText ? JSON.parse(payloadText) : null;
              if (!resRaw.ok) throw new Error(payload?.error || JSON.stringify(payload));

              // reset dependent fields client-side
              row.update({ categorie: '', compte_tva: '' });

              catMap[newVal] = catMap[newVal] || await fetchCategories(newVal);
              compteMap[newVal] = compteMap[newVal] || await fetchCompteTVA(newVal);

              Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: '✏️ Type mis à jour', showConfirmButton: false, timer: 1500 });
            } catch (err) {
              console.error(err);
              cell.setValue(oldVal);
              Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: '❌ Erreur : ' + err.message, showConfirmButton: false, timer: 2000 });
            }
          }
        },
        {
          title: 'Catégorie',
          field: 'categorie',
                     width: 350,

          headerFilter: 'input',
          headerFilterParams: { elementAttributes: { style: 'height:24px; font-size:12px; padding:2px 4px; width:150px; box-sizing:border-box;' } },
          editor: function (cell, onRendered, success, cancel) {
            const rowData = cell.getRow().getData();
            const type = rowData.type || '';
            const list = catMap[type] || [];

            const input = document.createElement('input');
            input.type = 'text';
            input.placeholder = '-- Sélectionner une catégorie --';
            input.classList.add('tabulator-autocomplete');
            input.style.width = '100%';
            input.style.padding = '4px';
            input.value = cell.getValue() || (type === 'CA non imposable' ? 'CA non imposable' : '');

            const entries = list.map(item => ({ label: item, value: item }));

            onRendered(() => {
              input.focus();
              input.select();
              setTimeout(() => {
                $(input).autocomplete({ source: entries, minLength: 0, autoFocus: true, select: function (event, ui) { event.preventDefault(); input.value = ui.item.value; success(ui.item.value); } });
                $(input).autocomplete('search', input.value);
              }, 10);
            });

            input.addEventListener('keydown', e => {
              if (e.key === 'Enter') { e.preventDefault(); success(input.value); }
              else if (e.key === 'Escape') { cancel(); }
            });

            input.addEventListener('blur', () => {
              if (!$('.ui-menu-item-wrapper.ui-state-focus').length) success(input.value);
            });

            return input;
          },
          editable: cellEditable,
          cellEdited: async function (cell) {
            const row = cell.getRow();
            const data = row.getData();
            const newVal = cell.getValue();
            const oldVal = cell.getOldValue();

            if (!data.persisted) return;

            try {
              const resRaw = await fetch(`/racines/${data.id}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Societe-Id': societeId, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ categorie: newVal })
              });
              const payloadText = await resRaw.text();
              const payload = payloadText ? JSON.parse(payloadText) : null;
              if (!resRaw.ok) throw new Error(payload?.error || JSON.stringify(payload));

              Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: '✏️ Catégorie mise à jour', showConfirmButton: false, timer: 1500 });
            } catch (err) {
              console.error(err);
              cell.setValue(oldVal);
              Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: '❌ Erreur : ' + err.message, showConfirmButton: false, timer: 2000 });
            }
          }
        },
        {
          title: 'Compte TVA',
          field: 'compte_tva',
          width: 290,
          headerFilter: 'input',
          headerFilterParams: { elementAttributes: { style: 'height:24px; font-size:12px; padding:2px 4px; box-sizing:border-box;' } },
          formatter: function (cell) {
            const val = cell.getValue();
            const rowData = cell.getRow().getData();
            const base = compteMap[rowData.type] || {};

            function cleanIntitule(raw) {
              if (!raw) return '';
              return String(raw).replace(/^\s*[\d\-\s]+-?\s*/, '').trim();
            }

            let label = '';
            if (val) {
              const rawIntitule = base[val] || '';
              label = cleanIntitule(rawIntitule);
              label = label ? `${val} - ${label}` : val;
            }

            return `
              <div class="compte-cell-display" title="${label}">
                <div class="compte-cell-text">${label || '<span class="placeholder">Sélectionner…</span>'}</div>
                <div class="compte-cell-chevron" aria-hidden="true">
                  <svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 10l5 5 5-5z"/>
                  </svg>
                </div>
              </div>
            `;
          },
          editor: function (cell, onRendered, success, cancel) {
            const rowData = cell.getRow().getData();
            const rawType = rowData.type || '';
            const type = rawType;
            const base = compteMap[type] || {};

            const wrapper = document.createElement('div');
            wrapper.className = 'select2-plus-wrapper';
            wrapper.style.display = 'flex';
            wrapper.style.gap = '.5rem';
            wrapper.style.alignItems = 'center';
            wrapper.style.width = '100%';
            wrapper.style.boxSizing = 'border-box';

            const selectEl = document.createElement('select');
            selectEl.className = 'select2-plus-select form-control';
            selectEl.style.minWidth = '0';
            selectEl.style.flex = '1';

            selectEl.appendChild(new Option('', ''));

            const values = Object.entries(base);
            for (let i = 0; i < values.length && i < 200; i++) {
              const [code, label] = values[i];
              if (!code) continue;
              const opt = document.createElement('option');
              const cleanLabel = String(label).replace(/^\s*[\d\-\s]+-?\s*/, '').trim();
              opt.value = code;
              opt.text = `${code}-${cleanLabel}`;
              if (String(cell.getValue()) === String(code)) opt.selected = true;
              selectEl.appendChild(opt);
            }

            const currentVal = cell.getValue();
            if (currentVal && !selectEl.querySelector(`option[value="${currentVal}"]`)) {
              const tmp = document.createElement('option');
              tmp.value = currentVal;
              tmp.text = currentVal;
              tmp.selected = true;
              selectEl.appendChild(tmp);
            }

            const plusBtn = document.createElement('button');
            plusBtn.type = 'button';
            plusBtn.className = 'select2-plus-btn';
            plusBtn.title = 'Ajouter un compte';
            plusBtn.setAttribute('aria-label', 'Ajouter un compte');
            plusBtn.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M8 4a.5.5 0 0 1 .5.5V7.5H11.5a.5.5 0 0 1 0 1H8.5V11.5a.5.5 0 0 1-1 0V8.5H4.5a.5.5 0 0 1 0-1H7.5V4.5A.5.5 0 0 1 8 4z" fill="currentColor"/>
              </svg>`;

            wrapper.appendChild(selectEl);
            wrapper.appendChild(plusBtn);

            let $select = null;
            let destroyed = false;
            function destroySelect() {
              if (destroyed) return;
              destroyed = true;
              try {
                if ($select && $select.data('select2')) {
                  $select.off();
                  $select.select2('destroy');
                }
              } catch (e) { /* ignore */ }
            }
            function finalizeAndSuccess(val) { destroySelect(); success(val); }
            function finalizeAndCancel() { destroySelect(); cancel(); }

            onRendered(() => {
              $select = $(selectEl);

              $select.select2({
                tags: true,
                tokenSeparators: [',',';'],
                placeholder: 'Rechercher ou ajouter un compte',
                allowClear: true,
                dropdownAutoWidth: false,
                width: '100%',
                minimumInputLength: 0,
                minimumResultsForSearch: 0,
                dropdownParent: $('body'),
                createTag: function(params) {
                  const term = $.trim(params.term);
                  if (term === '') return null;
                  return { id: '__ADD__:' + term, text: term, newTag: true };
                },
                templateResult: function(item) { return item.text; },
                templateSelection: function(item) { return item.text || item.id; }
              });

              setTimeout(() => {
                try {
                  const $container = $select.next('.select2-container');
                  if ($container.length) {
                    $container.css({ width: '100%', display: 'inline-block', boxSizing: 'border-box' });
                    $container.appendTo($(wrapper)).insertBefore($(plusBtn));
                  }
                  $(wrapper).find('.select2-container').css('width', '100%');
                } catch (e) { /* ignore */ }
              }, 0);

              try { $select.select2('open'); } catch (e) { /* ignore */ }

              $select.on('select2:select', function(e) {
                const data = e.params.data;
                if (String(data.id).startsWith('__ADD__:')) {
                  const newCode = String(data.id).replace('__ADD__:', '').trim();
                  openAddModalWithPrefill(newCode, type, function(saved) {
                    if (!compteMap[type]) compteMap[type] = {};
                    compteMap[type][saved.compte] = `${saved.compte} - ${saved.intitule}`;
                    table.redraw(true);
                    finalizeAndSuccess(saved.compte);
                  });
                  return;
                }
                finalizeAndSuccess(data.id === null ? '' : data.id);
              });

              $select.on('change', function() {
                const val = $select.val();
                if (val && String(val).startsWith('__ADD__:')) return;
                if (val === null || val === '') finalizeAndSuccess('');
                else finalizeAndSuccess(val);
              });
            });

            plusBtn.addEventListener('click', function(ev) {
              ev.preventDefault();
              const searchTerm = ($('.select2-container--open .select2-search__field').val() || '') || '';
              openAddModalWithPrefill(searchTerm.trim(), type, function(saved) {
                if (!compteMap[type]) compteMap[type] = {};
                compteMap[type][saved.compte] = `${saved.compte} - ${saved.intitule}`;
                table.redraw(true);
                try {
                  if ($select.data('select2')) {
                    $select.val(saved.compte).trigger('change');
                  }
                } catch (e) {}
              });
            });

            return wrapper;
          },
          editable: cellEditable,
          cellEdited: async function(cell) {
            const row = cell.getRow();
            const data = row.getData();
            const newVal = cell.getValue();
            const oldVal = cell.getOldValue();

            if (!data.persisted) return;

            const rowEl = row.getElement();
            rowEl.classList.add('row-loading');

            try {
              const resRaw = await fetch(`/racines/${data.id}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
                  'X-Societe-Id': societeId,
                  'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                  compte_tva: (newVal === '' ? null : newVal),
                  categorie: data.categorie
                })
              });
              const text = await resRaw.text();
              const payload = text ? JSON.parse(text) : null;
              if (!resRaw.ok) throw new Error(payload?.error || JSON.stringify(payload));

              if (payload && payload.compte_options) {
                const t = payload.type || data.type;
                compteMap[t] = { ...(compteMap[t] || {}), ...payload.compte_options };
                try { table.redraw(true); } catch (e) {}
              }

              Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: '✏️ Modifications enregistrées', showConfirmButton: false, timer: 1500 });
            } catch (err) {
              console.error(err);
              cell.setValue(oldVal);
              Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: '❌ Erreur : ' + err.message, showConfirmButton: false, timer: 2000 });
            } finally {
              rowEl.classList.remove('row-loading');
            }
          }
        },
       {
  title: "Actions",
 width: 100,

  hozAlign: "center",
  headerSort: false,
  formatter: function(cell){
    const data = cell.getData();
    if (!data.persisted) {
      return `
        <button class="confirm-btn actions-btn" title="Confirmer" aria-label="Confirmer">✔️</button>
        <button class="cancel-btn actions-btn" title="Annuler" aria-label="Annuler">✖️</button>
      `;
    } else {
      return `
        <button class="hide-row-btn actions-btn" title="Masquer" aria-label="Masquer">
          <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
        </button>
        <button class="delete-btn actions-btn" title="Supprimer" aria-label="Supprimer">
          <i class="fas fa-trash-alt" aria-hidden="true"></i>
        </button>
      `;
    }
  },
  cellClick: async function(e, cell) {
    const row  = cell.getRow();
    const data = row.getData();

    // normaliser la cible : bouton cliqué (ou élément à l'intérieur)
    const btn = e.target.closest && e.target.closest('button');
    if (!btn) return;

    // Cancel new row
    if (!data.persisted && btn.classList.contains("cancel-btn")) {
      row.delete();
      return;
    }

    // Confirm (new or update)
    if (btn.classList.contains("confirm-btn")) {
      await actionCellClick(e, cell);
      return;
    }

    // Hide confirmed row
    if (data.isConfirmed && btn.classList.contains("hide-row-btn")) {
      hiddenRows.add(data.id);
      row.getElement().style.display = "none";
      Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Rubrique masquée', showConfirmButton: false, timer: 1500 });
      return;
    }

    // Delete
    if (btn.classList.contains("delete-btn")) {
      // même logique de suppression que tu avais — appel au serveur...
      try {
        const checkRes = await fetch(`/racines/${data.id}/check-fournisseurs`, {
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Societe-Id': societeId,
            "X-Requested-With": "XMLHttpRequest"
          }
        });
        const txt = await checkRes.text();
        let checkBody;
        try { checkBody = txt ? JSON.parse(txt) : {}; } catch (e) { throw new Error('Réponse invalide'); }
        if (checkBody.used) {
          await Swal.fire({ title: 'Impossible de supprimer', text:  'Cette rubrique est utilisée dans des fournisseurs.', icon:  'warning', confirmButtonText: 'OK', allowOutsideClick: false });
          return;
        }
      } catch (err) {
        console.error(err);
        await Swal.fire({ title: 'Erreur de vérification', text:  'Impossible de vérifier l’utilisation de cette rubrique.', icon:  'error', confirmButtonText: 'OK', allowOutsideClick: false });
        return;
      }

      const result = await Swal.fire({ title: 'Confirmer la suppression ?', text: `La rubrique (${data.Num_racines}) sera définitivement supprimée.`, icon: 'question', showCancelButton: true, confirmButtonText: 'Supprimer', cancelButtonText: 'Annuler' });
      if (!result.isConfirmed) return;

      try {
        const delRes = await fetch(`/racines/${data.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Societe-Id': societeId, "X-Requested-With": "XMLHttpRequest" } });
        if (!delRes.ok) throw new Error('Échec suppression');
        row.delete();
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Rubrique supprimée', showConfirmButton: false, timer: 1500 });
      } catch (err) {
        console.error(err);
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Erreur suppression', showConfirmButton: false, timer: 2000 });
      }
    }
  }
},
      ], // end columns
    }); // end Tabulator
  } catch (e) {
    console.error('Tabulator init failed', e);
  }

  // if ajax returns nothing, ensure columns are visible (fallback)
  setTimeout(async () => {
    try {
      if (table && table.getData().length === 0) {
        try { await table.replaceData(); } catch (e) {}
        if (table.getData().length === 0) table.setData([]);
      }
    } catch (e) { console.error(e); }
  }, 1200);

  // preload compte maps after first load
  table?.on("dataLoaded", async rows => {
    const types = Array.from(new Set(rows.map(r=>r.type).filter(Boolean)));
    await Promise.all(types.map(async t => {
      if (!catMap[t]) catMap[t] = await fetchCategories(t);
      if (!compteMap[t]) compteMap[t] = await fetchCompteTVA(t);
    }));
    table.redraw(true);
  });

  // Add row
  document.getElementById("add-row-btn").addEventListener("click", () => {
    const data = table.getData();
    const nextId = data.length ? Math.max(...data.map(r => r.id || 0)) + 1 : 1;
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

  // Show hidden
  document.getElementById("show-hidden-btn").addEventListener("click", () => {
    if (!hiddenRows.size) {
      Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Aucune rubrique masquée', showConfirmButton: false, timer: 1500 });
      return;
    }
    hiddenRows.forEach(id => {
      const row = table.getRow(id);
      if (row) row.getElement().style.display = '';
    });
    hiddenRows.clear();
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Rubriques réaffichées', showConfirmButton: false, timer: 1500 });
  });

  // reload helper
  window.reloadRubriques = () => table.replaceData();
});
</script>

<script>
// Exports: Excel & PDF (boutons ajoutés dynamiquement si non présents)
// (function(){
//   const controls = document.getElementById('controls');
//   if (controls && !document.getElementById('export-excel-btn')) {
//     const xBtn = document.createElement('button');
//     xBtn.id = 'export-excel-btn';
//     xBtn.className = 'btn-glass green';
//     xBtn.textContent = '📤 Exporter Excel';
//     controls.insertBefore(xBtn, controls.firstChild);

//     const pBtn = document.createElement('button');
//     pBtn.id = 'export-pdf-btn';
//     pBtn.className = 'btn-glass red';
//     pBtn.textContent = '📄 Exporter PDF';
//     controls.insertBefore(pBtn, controls.firstChild);

//     xBtn.addEventListener('click', () => {
//       try { table.download('xlsx', 'rubriques_tva.xlsx', { sheetName: 'Rubriques TVA' }); }
//       catch(e){ console.error('Export XLSX failed', e); Swal.fire({icon:'error', title:'Export échoué'}); }
//     });

//     pBtn.addEventListener('click', () => {
//       try { table.download('pdf', 'rubriques_tva.pdf', { orientation: 'landscape', title: 'Liste des Rubriques TVA' }); }
//       catch(e){ console.error('Export PDF failed', e); Swal.fire({icon:'error', title:'Export échoué'}); }
//     });
//   }
// })();

// Keyboard: Ctrl/Cmd+S to save current row (if new)
document.addEventListener('keydown', function(e){
  const isSave = (e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S');
  if (!isSave) return;
  const active = document.activeElement;
  if (!active) return;
  const cellEl = active.closest && active.closest('.tabulator-cell');
  const rowEl = active.closest && active.closest('.tabulator-row');
  if (!rowEl) return; // not inside table
  e.preventDefault();
  try {
    const rowComp = table.getRow(rowEl);
    if (!rowComp) return;
    const confirmBtn = rowComp.getElement().querySelector('.confirm-btn');
    if (confirmBtn) {
      confirmBtn.click();
      Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Enregistrement demandé', showConfirmButton:false, timer:1200 });
    } else {
      // if no confirm button, try to blur to trigger cellEdited saves
      active.blur();
      Swal.fire({ toast:true, position:'top-end', icon:'info', title:"Aucune sauvegarde manuelle nécessaire (édition en place)", showConfirmButton:false, timer:1200 });
    }
  } catch (err) { console.error(err); }
});

// Small accessibility: focus outline for select2 wrapper when editing
document.addEventListener('focusin', (ev)=>{
  if (ev.target && ev.target.closest && ev.target.closest('.select2-plus-wrapper')) {
    ev.target.closest('.select2-plus-wrapper').style.boxShadow = '0 0 0 3px rgba(66,133,244,0.15)';
  }
});
document.addEventListener('focusout', (ev)=>{
  if (ev.target && ev.target.closest && ev.target.closest('.select2-plus-wrapper')) {
    ev.target.closest('.select2-plus-wrapper').style.boxShadow = '';
  }
});
</script>

@endsection
