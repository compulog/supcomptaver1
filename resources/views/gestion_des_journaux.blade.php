@extends('layouts.user_type.auth')

@section('content')

<main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">

  <!-- Formulaire de saisie -->
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h3>Ajouter un journal</h3>

        <!-- Tabs pour choisir entre saisie manuelle et importation Excel -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link active" id="manual-tab" data-bs-toggle="tab" href="#manual" role="tab" aria-controls="manual" aria-selected="true">Saisie Manuelle</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link" id="import-tab" data-bs-toggle="tab" href="#import" role="tab" aria-controls="import" aria-selected="false">Importer un fichier Excel</a>
          </li>
        </ul>

        <!-- Contenu des Tabs -->
        <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="manual" role="tabpanel" aria-labelledby="manual-tab">
            <form id="form-saisie-manuel" class="mt-3">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="type_journal" class="form-label">Type Journal</label>
                  <select class="form-control" id="type_journal" name="type_journal" required>
                    <option value="" disabled selected>Sélectionner un type</option>
                    <option value="Achat">Achat</option>
                    <option value="Vente">Vente</option>
                    <option value="Trésorerie">Trésorerie</option>
                    <option value="OD">OD</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="code_journal" class="form-label">Code Journal</label>
                  <input type="text" class="form-control" id="code_journal" name="code_journal" required>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="intitule" class="form-label">Intitulé</label>
                  <input type="text" class="form-control" id="intitule" name="intitule" required>
                </div>
                <div class="col-md-6">
                  <label for="contre_partie" class="form-label">Contre-Partie</label>
                  <input type="text" class="form-control" id="contre_partie" name="contre_partie" required>
                </div>
              </div>

              <button type="submit" class="btn btn-primary">Ajouter</button>
            </form>
          </div>

          <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
            <form id="form-import-excel" class="mt-3">
              <!-- Formulaire d'importation Excel ici -->
              <button type="submit" class="btn btn-primary">Importer</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tableau avec recherche -->
  <div class="container mt-4">
    <div class="row">
      <div class="col-md-12">
        <h3>Liste des journaux</h3>
        <input class="form-control mb-3" id="searchInput" type="text" placeholder="Rechercher...">

        <div id="table-list"></div> <!-- Ceci est l'élément où Tabulator sera rendu -->

        <!-- Boutons d'exportation -->
        <div class="mt-3">
          <button id="export-xlsx" class="btn btn-success btn-sm">Exporter en XLSX</button>
          <button id="export-pdf" class="btn btn-danger btn-sm">Exporter en PDF</button>
        </div>
      </div>
    </div>
  </div>

</main>

@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.1.2/css/tabulator.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.1.2/js/tabulator.min.js"></script>
<script>
  // Fonction d'initialisation de Tabulator
  let table = new Tabulator("#table-list", {
    height: "300px",
    layout: "fitColumns",
    placeholder: "Aucune donnée disponible",
    columns: [
      {title: "Code Journal", field: "code_journal", width: 150},
      {title: "Intitulé", field: "intitule", width: 200},
      {title: "Type Journal", field: "type_journal", width: 150},
      {title: "Contre-Partie", field: "contre_partie", width: 150},
      {title: "Actions", field: "actions", width: 150, hozAlign: "center", formatter: function(cell, formatterParams) {
          return `
            <button class="btn btn-warning btn-sm me-2 edit-btn">Modifier</button>
            <button class="btn btn-danger btn-sm delete-btn">Supprimer</button>
          `;
        }, cellClick: function(e, cell) {
          const row = cell.getRow();
          const data = row.getData();
          if (e.target.classList.contains('edit-btn')) {
            editRow(data, row);
          } else if (e.target.classList.contains('delete-btn')) {
            row.delete();
          }
        }},
    ],
    data: [],
    pagination: true,
    paginationSize: 5,
  });

  // Fonction pour ajouter une ligne dans Tabulator (saisie manuelle)
  document.getElementById('form-saisie-manuel').addEventListener('submit', function(event) {
    event.preventDefault();

    const codeJournal = document.getElementById('code_journal').value;
    const intitule = document.getElementById('intitule').value;
    const typeJournal = document.getElementById('type_journal').value;
    const contrePartie = document.getElementById('contre_partie').value;

    if (codeJournal && intitule && typeJournal && contrePartie) {
      table.addRow({
        code_journal: codeJournal,
        intitule: intitule,
        type_journal: typeJournal,
        contre_partie: contrePartie,
        actions: ""
      });

      document.getElementById('form-saisie-manuel').reset();
    }
  });

  // Fonction pour gérer la modification d'une ligne
  function editRow(data, row) {
    document.getElementById('code_journal').value = data.code_journal;
    document.getElementById('intitule').value = data.intitule;
    document.getElementById('type_journal').value = data.type_journal;
    document.getElementById('contre_partie').value = data.contre_partie;

    row.delete();
  }

  // Fonction de recherche
  document.getElementById('searchInput').addEventListener('input', function(event) {
    const filter = event.target.value.toLowerCase();
    table.setFilter(function(data) {
      return data.code_journal.toLowerCase().includes(filter) || 
             data.intitule.toLowerCase().includes(filter) || 
             data.type_journal.toLowerCase().includes(filter) || 
             data.contre_partie.toLowerCase().includes(filter);
    });
  });

  // Exporter les données en XLSX
  document.getElementById('export-xlsx').addEventListener('click', function() {
    table.download("xlsx", "data.xlsx", {});
  });

  // Exporter les données en PDF
  document.getElementById('export-pdf').addEventListener('click', function() {
    table.downloadToTab("pdf"); // Ouvrir le PDF dans un nouvel onglet
  });
</script>
@endpush
