@extends('layouts.user_type.auth')

@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tabulateur Exemple</title>

    <!-- Importer Tabulator CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">

    <!-- Importer Tabulator JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/tabulator-tables/dist/js/tabulator.min.js"></script>
</head>
<body>


<div class="mb-3 d-flex align-items-center gap-2 flex-wrap-nowrap">
    <!-- Bouton Créer -->
    <button type="button" id="create-button" class="btn btn-outline-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modal-saisie-manuel">
        Créer
    </button>

</div>


<!-- Modal pour Ajouter un Nouveau utilisateur -->
<div class="modal fade" id="modal-saisie-manuel" tabindex="-1" aria-labelledby="modal-saisie-manuelLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-saisie-manuelLabel">Ajouter un nouveau utilisateur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <form id="form-client">
    <div class="mb-3">
        <label for="name" class="form-label">Nom</label>
        <input type="text" class="form-control" id="name" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Mot de passe</label>
        <input type="password" class="form-control" id="password" required>
    </div>
    <div class="mb-3">
        <label for="phone" class="form-label">Téléphone</label>
        <input type="text" class="form-control" id="phone" required>
    </div>
    <div class="mb-3">
        <label for="location" class="form-label">Localisation</label>
        <input type="text" class="form-control" id="location" required>
    </div>
    <div class="mb-3">
        <label for="about_me" class="form-label">À propos de moi</label>
        <textarea class="form-control" id="about_me" rows="3" required></textarea>
    </div>
    <input type="hidden" class="form-control" id="baseName" value="{{ session('database') }}" required>
    <div class="mb-3">
        <label for="type" class="form-label">Type</label>
        <div>
            <input type="radio" class="form-check-input" id="typeAdmin" name="type" value="admin" required>
            <label for="typeAdmin" class="form-check-label">Admin</label>
        </div>
        <div>
            <input type="radio" class="form-check-input" id="typeUtilisateur" name="type" value="utilisateur" required>
            <label for="typeUtilisateur" class="form-check-label">Utilisateur</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Ajouter</button>
</form>

      </div>
    </div>
  </div>
</div>
    <div id="example-table"></div>

    <script>
       

        // Initialiser Tabulator pour afficher le tableau
        const table = new Tabulator("#example-table", {
          
            layout: "fitColumns", // Ajuster automatiquement la largeur des colonnes
            columns: [
                {title: "Nom", field: "name"},
                {title: "Email", field: "email"},
                {title: "Mot de passe", field: "password"},
                {title: "Téléphone", field: "phone"},
                {title: "Localisation", field: "location"},
                {title: "À propos de moi", field: "about_me"},
                {title: "BaseName", field: "baseName"},
                {title: "type", field: "type"},
                {
            title: "Actions", 
            field: "id", 
            formatter: function(cell, formatterParams, onRendered) {
                var id = cell.getValue();
                return `
                   <span class="text-warning edit-client" title="Modifier" style="cursor: pointer;">
                        <i class="fas fa-edit" style="color:#82d616;"></i>
                    </span>
                    <span class="text-danger" title="Supprimer" style="cursor: pointer;">
                        <i class="fas fa-trash" style="color:#82d616;"></i>
                    </span>
                `;
            }
        }
            ]
        });

     
     

</script>



</body>
</html>


@endsection