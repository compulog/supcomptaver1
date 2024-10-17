<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des comptes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS de Tabulator -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/css/tabulator.min.css" rel="stylesheet">

    <!-- Bibliothèque XLSX pour exporter en XLSX -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>

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
              <label for="code" class="form-label">Code</label>
              <input type="text" class="form-control" id="code" name="code" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="intitule" class="form-label">Intitulé</label>
              <input type="text" class="form-control" id="intitule" name="intitule" required>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>

        <div class="row mt-4">
          <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Tableau de comptes</h5>
                </div>
                <div class="card-body">
                    <input class="form-control mb-3" id="searchInput" type="text" placeholder="Rechercher...">
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
        {title: "Code", field: "code", editor: "input", width: 150, headerFilter: "input"},
        {title: "Intitulé", field: "intitule", editor: "input", width: 150, headerFilter: "input"},
        {   
            title: "Actions", 
            field: "actions", 
            formatter: function(cell, formatterParams, onRendered){
                return "<i class='fas fa-trash-alt delete-icon'></i>";
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
    cellEdited: function(cell){
      // Fonction qui peut être utilisée pour envoyer les modifications à une base de données via Ajax
      var field = cell.getField(); // Nom du champ modifié
      var value = cell.getValue(); // Nouvelle valeur
      var row = cell.getRow().getData(); // Données de la ligne entière

      console.log("Modification effectuée sur le champ: " + field + ", Nouvelle valeur: " + value);
      console.log(row);
      
      // Ici, vous pouvez utiliser fetch() ou Ajax pour sauvegarder la modification sur le serveur si nécessaire
    }
  });

  // Fonction de gestion de la soumission du formulaire de saisie manuelle
  document.getElementById('form-saisie-manuel').addEventListener('submit', function(e) {
    e.preventDefault();

    // Récupérer les données du formulaire
    var code = document.getElementById('code').value;
    var intitule = document.getElementById('intitule').value;

    // Ajouter les informations saisies au tableau Tabulator
    table.addData([{
      code: code,
      intitule: intitule
    }]);

    // Réinitialiser le formulaire après soumission
    e.target.reset();
  });

  // Fonction de recherche
  document.getElementById('searchInput').addEventListener('input', function() {
    table.setFilter([ // Appliquer le filtre sur le code ou l'intitulé
        {field: "code", type: "like", value: this.value},
        {field: "intitule", type: "like", value: this.value}
    ]);
  });
</script>

<!-- Bootstrap JS (optionnel) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
@endsection