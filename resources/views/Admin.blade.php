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

    <!-- Inclure Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
</head>
<body>
<h4>Gestion Des Droits D'accès </h4>
<br />

<!-- Div for Tabulator Table -->
<div id="Admin_table"></div>


<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document"> 
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Modifier l'utilisateur</h5>
        <i class="fas fa-times btn-close" data-bs-dismiss="modal" aria-label="Close"></i>
      </div>
      <div class="modal-body">
        <form id="editForm" method="POST" action="">
          @csrf
          @method('PUT')

        
          <div class="form-row">
            <div class="form-group col-md-6" > 
              <label for="name">Nom</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="col-md-6">
        <label for="droits">Droits d'accès</label>
        @foreach ($droits as $droit)
            <div>
                <input type="checkbox" name="droits[]" value="{{ $droit->id }}">
                <label for="droit">{{ $droit->name }}</label>
            </div>
        @endforeach
    </div>
          </div>


          <div class="form-group  col-md-6" style="margin-top:-200px;">
            <label for="phone">Téléphone</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
          </div>

          <div class="form-group col-md-6">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>

          <input type="hidden" id="userId">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
      </div>
    </div>
  </div>
</div>






<script>
   
// Initialiser Tabulator avec les données des utilisateurs
const table = new Tabulator("#Admin_table", {
    layout: "fitColumns", // Ajuster automatiquement la largeur des colonnes
    data: @json($users), // Chargement initial des données

    columns: [
        {title: "Nom", field: "name", headerFilter: "input"},
        {title: "Mot de passe", field: "raw_password", headerFilter: "input"},
        {title: "Téléphone", field: "phone", headerFilter: "input"},
        {title: "Email", field: "email", headerFilter: "input"},

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



// <span class="text-danger" title="Supprimer" style="cursor: pointer;" onclick="deleteUtilisateur(${id})">
//                         <i class="fas fa-trash" style="color:#82d616;"></i>
//                     </span>



// document.getElementById('editBtn').addEventListener('click', function() {
//     let userId = this.getAttribute('data-id');
//     // Vous pouvez maintenant rediriger ou effectuer une requête AJAX pour modifier les données
//     window.location.href = '/users/' + userId + '/edit'; // Exemple pour rediriger vers la page d'édition
// });

// Fonction pour ouvrir le modal de modification
// Fonction pour ouvrir le modal de modification et charger les données de l'utilisateur
// function openEditModal(id) {
//     // Faire une requête AJAX pour récupérer les informations de l'utilisateur
//     fetch(`/admin/${id}`)
//         .then(response => response.json())
//         .then(user => {
//             // Remplir les champs du formulaire dans le modal avec les données de l'utilisateur
//             document.getElementById('name').value = user.name;
//             document.getElementById('email').value = user.email;
//             document.getElementById('phone').value = user.phone;
//             document.getElementById('location').value = user.location;
//             document.getElementById('about_me').value = user.about_me;
//             document.getElementById('autorisation').value = user.autorisation;
//             document.getElementById('userId').value = user.id;

//             // Ouvrir le modal
//             $('#editModal').modal('show');
//         })
//         .catch(error => console.error('Erreur:', error));
// }
// Fonction pour ouvrir le modal de modification et charger les données de l'utilisateur
function openEditModal(id) {
    // Faire une requête AJAX pour récupérer les informations de l'utilisateur
    fetch(`/admin/${id}`)
        .then(response => response.json())
        .then(user => {
            // Remplir les champs du formulaire dans le modal avec les données de l'utilisateur
            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('phone').value = user.phone;
              document.getElementById('userId').value = user.id;

            // Ouvrir le modal
            $('#editModal').modal('show');
        })
        .catch(error => console.error('Erreur:', error));
}
document.getElementById('editForm').addEventListener('submit', function(event) {
    event.preventDefault();  // Empêche le rechargement de la page

    const id = document.getElementById('userId').value;
    const updatedData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
         
     };

    // Effectuer la requête PUT pour mettre à jour l'utilisateur
    fetch(`/admin/${id}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(updatedData)  // Convertir les données du formulaire en JSON
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recharger la page pour refléter les modifications
            setTimeout(function() {
               
            }, 500);  // Délai de 500ms pour permettre le rechargement
            alert('Utilisateur modifié avec succès.');
        } else {
            alert('Erreur lors de la modification de l\'utilisateur.');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
    $('#editModal').modal('hide');  // Fermer le modal après un délai
    
    location.reload();
});

// Fonction pour supprimer un utilisateur
function deleteUtilisateur(id) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce utilisateur ?")) {
        fetch(`/utilisateurs/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                table.deleteRow(id);
                alert('Utilisateur supprimé avec succès.');
            } else {
                alert('Erreur lors de la suppression de l\'utilisateur.');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    }
}
</script>
</body>
@endsection
