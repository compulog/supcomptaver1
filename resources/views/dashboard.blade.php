<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS de Tabulator -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/css/tabulator.min.css" rel="stylesheet">

    <!-- Bibliothèque XLSX pour exporter en XLSX -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>

    <!-- Bibliothèque jsPDF pour exporter en PDF -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        :root {
            --primary-color: #007bff;
        }


        body {
    background-color: #f9f9f9; /* Light background */
    color: #333;               /* Dark text */
}

.card-header {
    background-color: #007bff; /* Primary color */
    color: #ffffff;             /* White text for contrast */
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    color: #ffffff;             /* Ensure button text is white */
}

.btn-primary:hover {
    background-color: #0056b3; /* Darker shade for hover state */
    border-color: #0056b3;
}






        /* .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        } */

        /* .btn-primary:hover {
            background-color: darken(var(--primary-color), 10%);
            border-color: darken(var(--primary-color), 10%);
        } */

        /* .card-header {
            background-color: var(--primary-color);
            color: #fff;
        } */

        .tabulator {
            border: 1px solid var(--primary-color);
        }

        .tabulator .tabulator-header {
            background-color: var(--primary-color);
            color: #fff;
        }

        .tabulator .tabulator-cell {
            border: 1px solid #ddd;
        }

        .tabulator .tabulator-row {
            background-color: #f9f9f9;
        }

        .tabulator .tabulator-row:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .file-input {
            border: 1px solid var(--primary-color);
        }
    </style>
</head>


@extends('layouts.user_type.auth')

@section('content')
<body>
<div class="row">
  <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
    <div class="card">
      <!-- Card content remains the same -->
    </div>
  </div>

  <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
    <div class="card">
      <!-- Card content remains the same -->
    </div>
  </div>

  <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
    <div class="card">
      <!-- Card content remains the same -->
    </div>
  </div>

  <div class="col-xl-3 col-sm-6">
    <div class="card">
      <!-- Card content remains the same -->
    </div>
  </div>
</div>

<br><br><br>
<div class="row">
  <div class="col-12">
    <div class="card mb-4 mx-4">
      <div class="card-header pb-0">
        <div class="d-flex flex-row justify-content-between">
          <div>
            <h5 class="mb-0">Sociétés</h5>
          </div>
          <button type="button" class="btn bg-gradient-primary btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#nouvelleSocieteModal">+&nbsp; Nouvelle société</button>
        </div>
      </div>
      <div class="card-body px-0 pt-0 pb-2">
        <div id="example-table"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modale pour la nouvelle société -->
<div class="modal fade" id="nouvelleSocieteModal" tabindex="-1" aria-labelledby="nouvelleSocieteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="nouvelleSocieteModalLabel">Nouvelle Société</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="newSocieteForm">
          @csrf

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="raison_sociale" class="form-label">Raison sociale</label>
              <input type="text" class="form-control custom-input" id="raison_sociale" name="raison_sociale" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="forme_juridique" class="form-label">Forme juridique</label>
              <input type="text" class="form-control custom-input" id="forme_juridique" name="forme_juridique" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="siege_social" class="form-label">Siège social</label>
              <input type="text" class="form-control custom-input" id="siege_social" name="siege_social" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="patente" class="form-label">Patente</label>
              <input type="text" class="form-control custom-input" id="patente" name="patente" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="rc" class="form-label">RC</label>
              <input type="text" class="form-control custom-input" id="rc" name="rc" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="centre_rc" class="form-label">Centre RC</label>
              <input type="text" class="form-control custom-input" id="centre_rc" name="centre_rc" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
              <input type="text" class="form-control custom-input" id="identifiant_fiscal" name="identifiant_fiscal" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="ice" class="form-label">ICE</label>
              <input type="text" class="form-control custom-input" id="ice" name="ice" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="assujettie_tva" class="form-label">Assujettie partielle à la TVA</label>
              <input type="text" class="form-control custom-input" id="assujettie_tva" name="assujettie_tva" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="prorata" class="form-label">Prorata de déduction %</label>
              <input type="number" class="form-control custom-input" id="prorata" name="prorata" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="exercice_en_cours" class="form-label">Exercice en cours</label>
              <input type="text" class="form-control custom-input" id="exercice_en_cours" name="exercice_en_cours" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="date_creation" class="form-label">Date de création</label>
              <input type="date" class="form-control custom-input" id="date_creation" name="date_creation" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="nature_activite" class="form-label">Nature de l'activité</label>
              <input type="text" class="form-control custom-input" id="nature_activite" name="nature_activite" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="activite" class="form-label">Activité</label>
              <input type="text" class="form-control custom-input" id="activite" name="activite" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="regime_declaration" class="form-label">Régime de déclaration</label>
              <input type="text" class="form-control custom-input" id="regime_declaration" name="regime_declaration" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="fait_generateur" class="form-label">Fait générateur</label>
              <input type="text" class="form-control custom-input" id="fait_generateur" name="fait_generateur" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="rubrique_tva" class="form-label">Rubrique TVA</label>
              <input type="text" class="form-control custom-input" id="rubrique_tva" name="rubrique_tva" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="designation" class="form-label">Désignation</label>
              <input type="text" class="form-control custom-input" id="designation" name="designation" required>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modale de modification de société -->
<div class="modal fade" id="modifierSocieteModal" tabindex="-1" aria-labelledby="modifierSocieteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modifierSocieteModalLabel">Modifier Société</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editSocieteForm">
          @csrf
          <input type="hidden" id="edit_id" name="edit_id">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mod_raison_sociale" class="form-label">Raison sociale</label>
              <input type="text" class="form-control custom-input" id="mod_raison_sociale" name="mod_raison_sociale" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="mod_forme_juridique" class="form-label">Forme juridique</label>
              <input type="text" class="form-control custom-input" id="mod_forme_juridique" name="mod_forme_juridique" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mod_siege_social" class="form-label">Siège social</label>
              <input type="text" class="form-control custom-input" id="mod_siege_social" name="mod_siege_social" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="mod_patente" class="form-label">Patente</label>
              <input type="text" class="form-control custom-input" id="mod_patente" name="mod_patente" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mod_rc" class="form-label">RC</label>
              <input type="text" class="form-control custom-input" id="mod_rc" name="mod_rc" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="mod_centre_rc" class="form-label">Centre RC</label>
              <input type="text" class="form-control custom-input" id="mod_centre_rc" name="mod_centre_rc" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mod_identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
              <input type="text" class="form-control custom-input" id="mod_identifiant_fiscal" name="mod_identifiant_fiscal" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="mod_ice" class="form-label">ICE</label>
              <input type="text" class="form-control custom-input" id="mod_ice" name="mod_ice" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mod_assujettie_tva" class="form-label">Assujettie partielle à la TVA</label>
              <input type="text" class="form-control custom-input" id="mod_assujettie_tva" name="mod_assujettie_tva" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="mod_prorata" class="form-label">Prorata de déduction %</label>
              <input type="number" class="form-control custom-input" id="mod_prorata" name="mod_prorata" required>
            </div>
          </div>
          <div class="row">
  <div class="col-md-6 mb-3">
    <label for="exercice_en_cours" class="form-label">Exercice en cours</label>
    <input type="text" class="form-control custom-input" id="exercice_en_cours" name="exercice_en_cours" required>
  </div>
          <!-- <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mod_exercice_social" class="form-label">Exercice en cours</label>
              <input type="text" class="form-control custom-input" id="mod_exercice_social" name="mod_exercice_social" required>
            </div> -->
            <div class="col-md-6 mb-3">
              <label for="mod_date_creation" class="form-label">Date de création</label>
              <input type="date" class="form-control custom-input" id="mod_date_creation" name="mod_date_creation" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mod_nature_activite" class="form-label">Nature de l'activité</label>
              <input type="text" class="form-control custom-input" id="mod_nature_activite" name="mod_nature_activite" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="mod_activite" class="form-label">Activité</label>
              <input type="text" class="form-control custom-input" id="mod_activite" name="mod_activite" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mod_regime_declaration" class="form-label">Régime de déclaration</label>
              <input type="text" class="form-control custom-input" id="mod_regime_declaration" name="mod_regime_declaration" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="mod_fait_generateur" class="form-label">Fait générateur</label>
              <input type="text" class="form-control custom-input" id="mod_fait_generateur" name="mod_fait_generateur" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mod_rubrique_tva" class="form-label">Rubrique TVA</label>
              <input type="text" class="form-control custom-input" id="mod_rubrique_tva" name="mod_rubrique_tva" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="mod_designation" class="form-label">Désignation</label>
              <input type="text" class="form-control custom-input" id="mod_designation" name="mod_designation" required>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Modifier</button>
        </form>
      </div>
    </div>
  </div>
</div>


<script src="https://unpkg.com/tabulator-tables@5.3.2/dist/js/tabulator.min.js"></script>
<script>
  // Initialisation de Tabulator
  var tabledata = [];

  var table = new Tabulator("#example-table", {
    data: tabledata,
    layout: "fitColumns",
    columns: [
      { title: "Nom d'entreprise", field: "nom_entreprise", editor: "input" },
      { title: "ICE", field: "ice", editor: "input" },
      { title: "RC", field: "rc", editor: "input" },
      { title: "Identifiant Fiscal", field: "identifiant_fiscal", editor: "input" },
      { title: "Exercice en cours", field: "exercice_en_cours", editor: "input" },
      {
        title: "Action",
        field: "id",
        formatter: function(cell) {
          return "<button class='edit-btn'>Modifier</button><button class='delete-btn'>Supprimer</button>";
        },
        width: 150
      }
    ],
    rowFormatter: function(row) {
      var cell = row.getCell("id");
      cell.getElement().querySelector(".edit-btn").onclick = function() {
        openEditModal(row.getData());
      };
      cell.getElement().querySelector(".delete-btn").onclick = function() {
        table.deleteRow(row.getIndex());
      };
    },
  });

  // Fonction pour ouvrir la modale d'édition
  function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('mod_raison_sociale').value = data.nom_entreprise;
    document.getElementById('mod_ice').value = data.ice;
    document.getElementById('mod_rc').value = data.rc;
    document.getElementById('mod_identifiant_fiscal').value = data.identifiant_fiscal;
    document.getElementById('mod_exercice_social').value = data.exercice_en_cours;
    $('#modifierSocieteModal').modal('show');
  }

  // Ajouter une nouvelle société
  document.querySelector('#newSocieteForm').addEventListener('submit', function(event) {
    event.preventDefault();

    var newSociete = {
      id: Date.now(), // Génération d'un ID unique
      nom_entreprise: document.getElementById('raison_sociale').value,
      ice: document.getElementById('ice').value,
      rc: document.getElementById('rc').value,
      identifiant_fiscal: document.getElementById('identifiant_fiscal').value,
      exercice_en_cours: document.getElementById('exercice_en_cours').value,
    };

    // Ajout des données dans Tabulator
    table.addData([newSociete]);

    // Fermer la modale
    $('#nouvelleSocieteModal').modal('hide');
    document.getElementById('newSocieteForm').reset(); // Réinitialiser le formulaire
  });

  // Modifier une société
  document.querySelector('#editSocieteForm').addEventListener('submit', function(event) {
    event.preventDefault();

    var updatedSociete = {
      id: document.getElementById('edit_id').value,
      nom_entreprise: document.getElementById('mod_raison_sociale').value,
      ice: document.getElementById('mod_ice').value,
      rc: document.getElementById('mod_rc').value,
      identifiant_fiscal: document.getElementById('mod_identifiant_fiscal').value,
      exercice_en_cours: document.getElementById('mod_exercice_social').value,
    };

    // Mise à jour de la ligne
    table.updateOrAddData([updatedSociete]);

    // Fermer la modale
    $('#modifierSocieteModal').modal('hide');
    document.getElementById('editSocieteForm').reset(); // Réinitialiser le formulaire
  });

  // Code pour récupérer les données de votre API si nécessaire
  async function loadData() {
    const response = await fetch('/api/societes'); // Remplacez par votre URL d'API
    const data = await response.json();
    table.setData(data);
  }

  loadData();
</script>
</body>
@endsection
