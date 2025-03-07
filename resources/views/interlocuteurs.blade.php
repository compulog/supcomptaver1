@extends('layouts.user_type.authss')

@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin</title>
    <!-- Inclure Tabulator CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/css/tabulator.min.css" rel="stylesheet">

    <!-- Inclure Tabulator JS -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/js/tabulator.min.js"></script>

    <!-- Importer Tabulator CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">

    <!-- Importer Tabulator JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/tabulator-tables/dist/js/tabulator.min.js"></script>

    <!-- Inclure Bootstrap CSS pour le modal -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

    <!-- Inclure Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
<h4>Interlocuteurs </h4>
<br />

<!-- Div for Tabulator Table -->
<div id="Admin_table"></div>

<!-- Modal de modification d'utilisateur -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Modifier l'utilisateur</h5>
        <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>
      </div>
      <div class="modal-body">
        <form id="editForm" method="POST" action="">
          @csrf
          @method('PUT')

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="name">Nom</label>
              <input type="text" class="form-control" id="name" name="name">
            </div>

            <div class="form-group col-md-6">
              <label for="password">Mot De Passe</label>
              <input type="text" class="form-control" id="password" name="password" placeholder="Entrez le nouveau mot de passe">
              <input type="hidden" id="current_password" name="current_password"> <!-- Stocker le mot de passe actuel -->
            </div>

            <div class="form-group col-md-6">
              <label for="email">Email</label>
              <input type="email" class="form-control" id="email" name="email">
            </div>

            <div class="form-group col-md-6">
              <label for="phone">Téléphone</label>
              <input type="text" class="form-control" id="phone" name="phone">
            </div>

          </div>
          <input type="hidden" id="userId">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
    console.log(@json($users));

    // Initialiser Tabulator avec les données des utilisateurs
    const table = new Tabulator("#Admin_table", {
        layout: "fitColumns", // Ajuster automatiquement la largeur des colonnes
        data: @json($users), // Chargement initial des données

        columns: [
            {title: "Nom", field: "name", headerFilter: "input"},
            {title: "Email", field: "email", headerFilter: "input"},
            {title: "Mot De Passe", field: "raw_password", headerFilter: "input"},
            {title: "Téléphone", field: "phone", headerFilter: "input"},
            {
                title: "Actions", 
                field: "id", 
                formatter: function(cell, formatterParams, onRendered) {
                    var id = cell.getValue();
                    return `
                       <span class="text-warning edit-client" title="Modifier" style="cursor: pointer;" onclick="openEditModal(${id})">
                    <i class="fas fa-edit" style="color:#82d616;"></i>
                </span>
                    `;
                }
            }
        ]
    });

    function openEditModal(id) {
        fetch(`/user/${id}`)
        .then(response => response.json())
        .then(user => {
            console.log(user); // Vérifiez les données de l'utilisateur dans la console

            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('phone').value = user.phone;
            document.getElementById('userId').value = user.id;

            // Vérifiez si raw_password existe dans les données renvoyées
            if (user.raw_password) {
                document.getElementById('password').value = user.raw_password; // Afficher le mot de passe actuel dans le champ
                document.getElementById('current_password').value = user.raw_password; // Garder le mot de passe actuel dans un champ caché
            } else {
                document.getElementById('password').value = ""; // S'assurer qu'il n'y a rien s'il n'y a pas de mot de passe
                document.getElementById('current_password').value = ""; // Ne pas envoyer de mot de passe
            }

            // Ouvrir le modal
            $('#editModal').modal('show');
        })
        .catch(error => console.error('Erreur:', error));
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('editForm').addEventListener('submit', function(event) {
            event.preventDefault();

            // Récupérer les éléments du formulaire
            var name = document.getElementById('name');
            var email = document.getElementById('email');
            var phone = document.getElementById('phone');
            var password = document.getElementById('password').value; // Récupérer le mot de passe
            var current_password = document.getElementById('current_password').value; // Récupérer le mot de passe actuel
            var userId = document.getElementById('userId').value; // Récupérer l'ID de l'utilisateur

            // Vérifier si les éléments existent avant d'y accéder
            if (name || email || phone || password) {
                // Si le mot de passe est vide, on ne l'envoie pas
                var dataToSend = {
                    name: name.value,
                    email: email.value,
                    phone: phone.value,
                };

                // Envoyer le mot de passe seulement s'il est modifié
                if (password !== current_password) {
                    dataToSend.raw_password = password; // Envoyer le mot de passe seulement s'il est modifié
                } else {
                    dataToSend.raw_password = current_password; // Si pas de nouveau mot de passe, envoyer l'actuel
                }

                fetch('/interlocuteurs/' + userId, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(dataToSend) // Envoyer les données avec ou sans mot de passe
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Success:', data);
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            } else {
                console.error("Un ou plusieurs champs du formulaire sont manquants.");
            }
        });
    });

</script>
</body>
@endsection
