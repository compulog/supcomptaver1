@extends('layouts.user_type.authss')



@section('content')

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">



    <title>Tabulateur Exemple</title>



    <!-- Inclure Tabulator CSS -->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/css/tabulator.min.css" rel="stylesheet">



    <!-- Inclure Tabulator JS -->

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/js/tabulator.min.js"></script>



    <!-- Importer Tabulator CSS -->

    <link href="https://cdn.jsdelivr.net/npm/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">



    <!-- Importer Tabulator JS -->

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/tabulator-tables/dist/js/tabulator.min.js"></script>



    <style>

        /* Style pour le modal de modification */

        .modal-content {

            width: 100%;

            max-width: 600px;

            margin: auto;

            padding: 20px;

            border-radius: 10px;

            background-color: #f8f9fa;

        }



        .modal-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            border-bottom: 2px solid #ccc;

            padding-bottom: 10px;

        }



        .modal-title {

            font-size: 1.25rem;

            font-weight: bold;

            color: #333;

        }



        .close {

            font-size: 1.5rem;

            color: #999;

            cursor: pointer;

        }



        .form-control {

            border-radius: 5px;

            box-shadow: none;

        }



        .form-label {

            font-weight: bold;

        }



        .form-check-input {

            margin-right: 10px;

        }



        .modal-body {

            padding: 20px;

        }



        .btn-primary {

            background-color: #007bff;

            border: none;

            border-radius: 5px;

            padding: 10px 15px;

            font-weight: bold;

            color: white;

            cursor: pointer;

        }



        .btn-primary:hover {

            background-color: #0056b3;

        }



        .modal-footer {

            display: flex;

            justify-content: space-between;

            padding-top: 10px;

        }

    </style>

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

        <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>

      </div>

      <div class="modal-body">

        <form id="form-client" action="{{ route('utilisateurs.store') }}" method="POST">

          @csrf

          <div class="row mb-3">

            <div class="col-md-6">

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

          </div>

          <div class="row mb-3">

            <div class="col-md-6">

                <label for="name" class="form-label">Nom</label>

                <input type="text" class="form-control" id="name" name="name" required>

            </div>

            <!-- <div class="col-md-6" style="margin-top:-110px;">

                <label for="droits">Droits d'accès</label>

                @foreach ($droits as $droit)

                    <div>

                        <input type="checkbox" name="droits[]" value="{{ $droit->id }}">

                        <label for="droit">{{ $droit->name }}</label>

                    </div>

                @endforeach

            </div> -->

          </div>



            <div class="col-md-6" style="margin-top:-20px;">

                <label for="password" class="form-label">Mot de passe</label>

                <input type="password" class="form-control" id="password" name="password" required>

            </div>

            <div class="col-md-6">

                <label for="phone" class="form-label">Téléphone</label>

                <input type="text" class="form-control" id="phone" name="phone" required>

            </div>



          <div class="row mb-3">

            <div class="col-md-6">

                <label for="email" class="form-label">Email</label>

                <input type="email" class="form-control" id="email" name="email" required>

            </div>

            <input type="hidden" class="form-control" id="baseName" name="baseName" value="{{ session('database') }}" required>

          </div>



          <div class="modal-footer">

            <button type="submit" class="btn btn-primary">Ajouter</button>

            <button type="reset" class="btn btn-secondary me-8">

                            <i class="fas fa-undo"></i> 

            </button>

          </div>

        </form>

      </div>

    </div>

  </div>

</div>

@foreach($users as $user)

<!-- Modal de modification pour chaque utilisateur -->

<div id="modifier-modal-{{ $user->id }}" class="modal fade" tabindex="-1" aria-labelledby="modifierModalLabel" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="modifierModalLabel">Modifier l'utilisateur</h5>

                <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>

            </div>

            <div class="modal-body">

                <form method="POST" action="{{ route('utilisateurs.update', $user->id) }}">

                    @csrf

                    @method('PUT')



                    <!-- Nom -->

                    <div class="form-group">

                        <label for="name">Nom</label>

                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>

                        @error('name')

                            <div class="alert alert-danger">{{ $message }}</div>

                        @enderror

                    </div>



                    <!-- Email -->

                    <div class="form-group">

                        <label for="email">Email</label>

                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>

                        @error('email')

                            <div class="alert alert-danger">{{ $message }}</div>

                        @enderror

                    </div>



                    <!-- Mot de passe -->

                    <div class="form-group">

                        <label for="password">Mot de passe</label>

                        <input type="password" class="form-control" id="password" name="password">

                        <small>Si vous ne voulez pas changer le mot de passe, laissez ce champ vide.</small>

                        @error('password')

                            <div class="alert alert-danger">{{ $message }}</div>

                        @enderror

                    </div>



                    <!-- Téléphone -->

                    <div class="form-group">

                        <label for="phone">Téléphone</label>

                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>

                        @error('phone')

                            <div class="alert alert-danger">{{ $message }}</div>

                        @enderror

                    </div>



                    <!-- Type -->

                    <div class="form-group">

                        <label for="type">Type</label>

                        <div>

                            <input type="radio" class="form-check-input" id="typeAdmin-modifier" name="type" value="admin" {{ old('type', $user->type) == 'admin' ? 'checked' : '' }} required>

                            <label for="typeAdmin-modifier" class="form-check-label">Admin</label>

                        </div>

                        <div>

                            <input type="radio" class="form-check-input" id="typeUtilisateur-modifier" name="type" value="utilisateur" {{ old('type', $user->type) == 'utilisateur' ? 'checked' : '' }} required>

                            <label for="typeUtilisateur-modifier" class="form-check-label">Utilisateur</label>

                        </div>

                    </div>



                 



                    <!-- BaseName (champ caché) -->

                    <input type="hidden" class="form-control" id="baseName" name="baseName" value="{{ session('database') }}">



                    <!-- Boutons -->

                    <div class="modal-footer">

                        <button type="submit" class="btn btn-primary">Mettre à jour</button>

                        <button type="reset" class="btn btn-secondary me-8">

                            <i class="fas fa-undo"></i> 

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endforeach







<!-- Div for Tabulator Table -->

<div id="example-table"></div>



<script>

// Initialiser Tabulator avec les données des utilisateurs

const table = new Tabulator("#example-table", {

    layout: "fitColumns", // Ajuster automatiquement la largeur des colonnes

    data: @json($users), // Chargement initial des données



    columns: [

        {title: "Nom", field: "name", headerFilter: "input"},

        {title: "Mot de passe", field: "raw_password", headerFilter: "input"},

        {title: "Téléphone", field: "phone", headerFilter: "input"},

        {title: "Email", field: "email", headerFilter: "input"},

        {title: "BaseName", field: "BaseName", headerFilter: "input"},

        {title: "Type", field: "type", headerFilter: "input"},

        {

    title: "Actions", 

    field: "id", 

    formatter: function(cell, formatterParams, onRendered) {

        var id = cell.getValue(); // Récupère l'ID de l'utilisateur



        // Générer l'icône Modifier uniquement pour cet utilisateur

        return `

            <span class="text-warning" title="Modifier" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modifier-modal-${id}">

                <i class="fas fa-edit" style="color:#82d616;"></i>

            </span>

            <span class="text-danger" title="Supprimer" style="cursor: pointer;" onclick="deleteUtilisateur(${id})">

                <i class="fas fa-trash" style="color:#82d616;"></i>

            </span>

        `;

    }

}





    ]

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

                alert('utilisateur supprimé avec succès.');

            } else {

                alert('Erreur lors de la suppression du utilisateur.');

            }

        })

        .catch(error => {

            console.error('Erreur:', error);

        });

    }

}

// Fonction pour afficher le modal de modification

// Fonction pour afficher le modal de modification avec les informations de l'utilisateur

function editUtilisateur(id) {

    // Envoyer une requête AJAX pour récupérer les informations de l'utilisateur

    fetch(`/utilisateurs/${id}/edit`)

        .then(response => response.json())

        .then(data => {

            // Remplir les champs du formulaire avec les données de l'utilisateur

            document.getElementById('name-modifier').value = data.name;

            document.getElementById('email-modifier').value = data.email;

            document.getElementById('phone-modifier').value = data.phone;

            document.getElementById('password-modifier').value = '';

            document.getElementById('typeAdmin-modifier').checked = data.type === 'admin';

            document.getElementById('typeUtilisateur-modifier').checked = data.type === 'utilisateur';

            

            // Ouvrir le modal

            var modal = new bootstrap.Modal(document.getElementById('modifier-modal'));

            modal.show();

        })

        .catch(error => {

            console.error('Erreur:', error);

        });

}



</script>

@section('scripts')

<script>

    document.querySelector('form').addEventListener('submit', function(e) {

        const password = document.getElementById('password').value;

        const passwordConfirmation = document.getElementById('password_confirmation').value;



        if (password && password !== passwordConfirmation) {

            e.preventDefault(); // Empêche la soumission du formulaire

            alert('Les mots de passe ne correspondent pas.');

        }

    });

</script>

@endsection



</body>

</html>

@endsection

