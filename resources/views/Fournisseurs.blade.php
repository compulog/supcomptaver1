<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de saisie de mouvements</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS de Tabulator -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/css/tabulator.min.css" rel="stylesheet">

    <!-- Bibliothèque XLSX pour exporter en XLSX -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>

    <!-- Bibliothèque jsPDF pour exporter en PDF -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #007bff;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: darken(var(--primary-color), 10%);
            border-color: darken(var(--primary-color), 10%);
        }

        .card-header {
            background-color: var(--primary-color);
            color: #fff;
        }

        .tabulator {
            border: 1px solid var(--primary-color);
        }

        .tabulator .tabulator-header {
            background-color: var(--primary-color);
            color: #334767;
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

        .card-body {
            width: 1100px;
        }

        .delete-icon {
            cursor: pointer;
            color: #dc3545; /* Couleur rouge pour l'icône de suppression */
        }
    </style>
</head>

@extends('layouts.user_type.auth')

@section('content')
<body>
<main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg ">

  <!-- Formulaire de saisie -->
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h3>Ajouter un compte</h3>

        <!-- Formulaire de saisie manuelle -->
        <form id="form-saisie-manuel" class="mt-3">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="compte" class="form-label">Compte</label>
              <input type="text" class="form-control" id="compte" name="compte" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="intitule" class="form-label">Intitulé</label>
              <input type="text" class="form-control" id="intitule" name="intitule" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="identifiant_fiscal" class="form-label">Identifiant fiscal</label>
              <input type="text" class="form-control" id="identifiant_fiscal" name="identifiant_fiscal" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="ice" class="form-label">ICE</label>
              <input type="text" class="form-control" id="ice" name="ice" required>
            </div>
          
            
          </div>

          <div class="row">
          <div class="col-md-6 mb-3">
              <label for="contre_partie" class="form-label">Contre partie</label>
              <input type="text" class="form-control" id="contre_partie" name="contre_partie" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="nature_operation" class="form-label">Nature de l'opération</label>
              <select class="form-control" id="nature_operation" name="nature_operation" required>
                <option value="" disabled selected>Sélectionner une option</option>
                <option value="Achat de biens d'équipement">Achat de biens d'équipement</option>
                <option value="Achat de travaux">Achat de travaux</option>
                <option value="Achat de services">Achat de services</option>
              </select>
            </div>
          </div>

          <div class="row">
           
          <div class="col-md-6 mb-3">
              <label for="rubrique_tva" class="form-label">Rubrique TVA</label>
              <input type="text" class="form-control" id="rubrique_tva" name="rubrique_tva" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="designation" class="form-label">Désignation</label>
              <input type="text" class="form-control" id="designation" name="designation" required>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>
        
        <div class="row">
          <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Tableau de fournisseurs</h5>
                </div>
                <div class="card-body">
                    <div id="example-table"></div>
                </div>
            </div>
        </div>
      </div>

    </div>
  </div>

</main>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/js/tabulator.min.js"></script>

<script>
  // Initialisation de Tabulator
  var table = new Tabulator("#example-table", {
    height: "311px",
    layout: "fitColumns",
    resizableColumnFit: true,
    columns: [
        {title: "Compte", field: "compte", width: 150, headerFilter: "input"},
        {title: "Intitulé", field: "intitule", width: 150, headerFilter: "input"},
        {title: "Identifiant fiscal", field: "identifiant_fiscal", width: 150, headerFilter: "input"}, 
        {title: "ICE", field: "ice", width: 150, headerFilter: "input"},
       {title: "Contre partie", field: "contre_partie", width: 150, headerFilter: "input"},
       {title: "Nature de l'opération", field: "nature_operation", width: 150, headerFilter: "input"},
        {title: "Rubrique TVA", field: "rubrique_tva", width: 150, headerFilter: "input"},
        {title: "Désignation", field: "designation", width: 150, headerFilter: "input"},
        {   title: "Actions", 
            field: "actions", 
            formatter: function(cell, formatterParams, onRendered){
                return "<i class='fas fa-trash-alt'></i>";
            },
            width: 120,
            hozAlign: "center",
            cellClick: function(e, cell){
                // Supprimer la ligne lorsque l'icône est cliquée
                var row = cell.getRow();
                row.delete();
            }
        }
    ],
  });

  // Fonction de gestion de la soumission du formulaire
  document.getElementById('form-saisie-manuel').addEventListener('submit', function(e) {
    e.preventDefault();

    // Récupérer les données du formulaire
    var compte = document.getElementById('compte').value;
    var intitule = document.getElementById('intitule').value;
    var identifiant_fiscal = document.getElementById('identifiant_fiscal').value;
   var ice = document.getElementById('ice').value;
     var contre_partie = document.getElementById('contre_partie').value;
    var nature_operation = document.getElementById('nature_operation').value;
    var rubrique_tva = document.getElementById('rubrique_tva').value;
    var designation = document.getElementById('designation').value;
    // Ajouter les informations saisies au tableau Tabulator
    table.addData([{
      compte: compte,
      intitule: intitule,
      identifiant_fiscal: identifiant_fiscal,
      ice: ice,
      contre_partie: contre_partie,
      nature_operation: nature_operation,
      rubrique_tva: rubrique_tva,
      designation: designation
    }]);

    // Réinitialiser le formulaire après soumission
    e.target.reset();
  });

  // Gérer le changement de sélection dans le champ nature_operation
  document.getElementById('nature_operation').addEventListener('change', function() {
    document.getElementById('designation').focus();
  });

  // Ajouter une nouvelle ligne
  document.getElementById('reactivity-add').addEventListener('click', function() {
    table.addRow({
      compte: "Nouveau Compte",
      intitule: "Nouveau Intitulé",
      identifiant_fiscal: "123456789",
      ice: "1234567890",
      contre_partie: "Nouvelle Contre Partie",
      nature_operation: "Achat de biens d'équipement",
      rubrique_tva: "TVA Exemple",
      designation: "Nouvelle Désignation"
    });
  });
</script>

<!-- Bootstrap JS (optionnel) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
@endsection
