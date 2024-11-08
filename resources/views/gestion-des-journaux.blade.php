<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Gestion des Journaux</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chargement de jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!-- Chargement de Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<!-- Chargement de Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Chargement de Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<link href="/assets/vendor/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <style>
/* Style pour le conteneur du tableau */
#tabulator-table {
    overflow-y: auto; /* Activer le défilement vertical */
    border: 1px solid #ddd; /* Ajouter une bordure si nécessaire */
}

/* Optionnel : Style pour le tableau */
.tabulator {
    border-collapse: collapse; /* Pour un meilleur rendu visuel */
}

    #tabulator-table .tabulator-header {
    height: 30px; /* Ajustez la hauteur du header */
    font-size: 0.9em; /* Réduisez la taille de la police */
    padding: 2px 5px; /* Ajustez le padding pour réduire l'espacement */
}

#tabulator-table .tabulator-header .tabulator-col-title {
    font-size: 0.85em; /* Taille de police des titres des colonnes */
}

/* Ajuste le champ de recherche dans le header */
.tabulator .tabulator-header input[type="search"] {
    height: 20px; /* Diminue la hauteur */
    padding: 1px 3px; /* Ajuste le padding interne */
    font-size: 0.8em; /* Diminue légèrement la police */}

</style>

</head>

@extends('layouts.user_type.auth')

@section('content')
<body>


<div class="container mt-5">
        <h1>Gestion des Journaux</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#ajouterJournalModal">Ajouter Journal</button>
       
        <div id="journal-table"></div>
    </div>

 <!-- Modal pour Ajouter Journal -->
<div class="modal fade" id="ajouterJournalModal" tabindex="-1" role="dialog" aria-labelledby="ajouterJournalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ajouterJournalModalLabel">Créer</h5>
                @csrf 
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="ajouterJournalForm">
                    <div class="form-group">
                        <label for="code_journal">Code Journal</label>
                        <input type="text" class="form-control" id="code_journal" name="code_journal" required>
                    </div>
                    <div class="form-group">
                        <label for="type_journal">Type Journal</label>
                        <select class="form-control" id="type_journal" name="type_journal" required>
                            <option value=""  disabled selected>Sélectionner un type</option>
                            <option value="" ></option>
                            <option value="Achats">Achats</option>
                            <option value="Ventes">Ventes</option>
                            <option value="Trésoreries">Trésoreries</option>
                            <option value="Opérations Diverses">Opérations Diverses</option>
                        </select>
                    </div>
                    <div class="form-group contre-partie-container"> <!-- Conteneur pour le champ Contre Partie -->
                        <label for="contre_partie">Contre Partie</label>
                        <select class="form-control" id="contre_partie" name="contre_partie">
                            <option value=""disabled selected>Sélectionner une contre partie</option>
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="intitule">Intitulé</label>
                        <input type="text" class="form-control" id="intitule" name="intitule" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="submit" form="ajouterJournalForm" class="btn btn-primary">Valider</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'édition -->
<div id="modifierJournalModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modifierJournalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifierJournalModalLabel">Modifier Journal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="modifierJournalForm">
                    <input type="hidden" id="journal_id_modif">
                    <div class="form-group">
                        <label for="code_journal_modif">Code Journal</label>
                        <input type="text" class="form-control" id="code_journal_modif" required>
                    </div>
                    <div class="form-group">
                        <label for="type_journal_modif">Type Journal</label>
                        <select class="form-control" id="type_journal_modif" name="type_journal_modif" >
                            <option value=""disabled selected>Sélectionner un type</option>
                            <option value=""></option>
                            <option value="Achats">Achats</option>
                            <option value="Ventes">Ventes</option>
                            <option value="Trésoreries">Trésoreries</option>
                            <option value="Opérations Diverses">Opérations Diverses</option>
                        </select>
                    </div>
                    <div class="form-group contre-partie-container"> <!-- Conteneur pour le champ Contre Partie -->
                        <label for="contre_partie_modif">Contre Partie</label>
                        <select class="form-control" id="contre_partie_modif" name="contre_partie_modif">
                            <option value=""disabled selected>Sélectionner une contre partie</option>
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="intitule_modif">Intitulé</label>
                        <input type="text" class="form-control" id="intitule_modif" >
                    </div>
                    <button type="submit" class="btn btn-primary">Sauvegarder</button>
                </form>
            </div>
        </div>
    </div>
</div>

 </div>

       
</main>


<script>

$(document).ready(function() {
    // Lorsque le type de journal change
    $('#type_journal, #type_journal_modif').change(function() {
        const typeJournal = $(this).val();
        const contrePartieSelect = $(this).closest('.modal').find('select[name="contre_partie"], select[name="contre_partie_modif"]');
        const contrePartieContainer = $(this).closest('.modal').find('.contre-partie-container');

        // Effacer les anciennes options
        contrePartieSelect.empty();
        contrePartieSelect.append('<option value="">Sélectionner une contre partie</option>');

        // Vérifiez le type de journal et remplissez les comptes correspondants
        if (typeJournal === 'Achats') {
            $.ajax({
                url: '/get-comptes-achats',
                method: 'GET',
                success: function(data) {
                    $.each(data, function(index, compte) {
                        contrePartieSelect.append('<option value="' + compte.compte + '">' + compte.compte + ' - ' + compte.intitule + '</option>');
                    });
                },
                error: function(error) {
                    console.error('Erreur lors de la récupération des comptes:', error);
                }
            });
            contrePartieContainer.show();
        } else if (typeJournal === 'Ventes') {
            $.ajax({
                url: '/get-comptes-ventes',
                method: 'GET',
                success: function(data) {
                    $.each(data, function(index, compte) {
                        contrePartieSelect.append('<option value="' + compte.compte + '">' + compte.compte + ' - ' + compte.intitule + '</option>');
                    });
                },
                error: function(error) {
                    console.error('Erreur lors de la récupération des comptes:', error);
                }
            });
            contrePartieContainer.show();
        } else if (typeJournal === 'Trésoreries') {
            $.ajax({
                url: '/get-comptes-tresoreries',
                method: 'GET',
                success: function(data) {
                    $.each(data, function(index, compte) {
                        contrePartieSelect.append('<option value="' + compte.compte + '">' + compte.compte + ' - ' + compte.intitule + '</option>');
                    });
                },
                error: function(error) {
                    console.error('Erreur lors de la récupération des comptes:', error);
                }
            });
            contrePartieContainer.show();
        } else if (typeJournal === 'Opérations Diverses') {
            contrePartieContainer.hide();
        } else {
            contrePartieSelect.empty().append('<option value="">Sélectionner une contre partie</option>');
            contrePartieContainer.show();
        }
    });

    // Au chargement du modal, mettre le focus sur le champ Type Journal
    $('#ajouterJournalModal, #modifierJournalModal').on('shown.bs.modal', function () {
        $(this).find('select[name="type_journal"], select[name="type_journal_modif"]').focus();
    });
});






// Initialisation de Tabulator pour afficher les journaux
var table = new Tabulator("#journal-table", {
    ajaxURL: "/journaux/data", // URL pour récupérer les données
    height: "600px", // Hauteur du tableau
    layout: "fitColumns",
    columns: [
        { title: "Code Journal", field: "code_journal", editor: "input", headerFilter: "input" },
        { title: "Type Journal", field: "type_journal", editor: "input", headerFilter: "input" },
        { title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input" },
        { title: "Contre Partie", field: "contre_partie", editor: "input", headerFilter: "input" },
        {
            title: "Actions",
            field: "action-icons",
            formatter: function () {
                return `
                    <i class='fas fa-edit text-primary edit-icon' style='cursor: pointer;'></i>
                    <i class='fas fa-trash-alt text-danger delete-icon' style='cursor: pointer;'></i>
                `;
            },
            cellClick: function (e, cell) {
                var rowData = cell.getRow().getData();
                if (e.target.classList.contains('edit-icon')) {
                    editJournal(rowData); // Ouvre le modal d'édition avec les données
                } else if (e.target.classList.contains('delete-icon')) {
                    deleteJournal(rowData.id); // Supprime l'enregistrement
                }
            },
        }
    ],
});

// Fonction pour ajouter un nouveau journal
$('#ajouterJournalForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: "/journaux",
        type: "POST",
        data: {
            code_journal: $("#code_journal").val(),
            type_journal: $("#type_journal").val(),
            intitule: $("#intitule").val(),
            contre_partie: $("#contre_partie").val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            table.setData("/journaux/data"); // Recharger les données dans Tabulator
            $("#ajouterJournalModal").modal("hide"); // Fermer le modal d'ajout
            $("#ajouterJournalForm")[0].reset(); // Réinitialiser le formulaire
            alert(response.message); // Message de succès
        },
        error: function (xhr) {
            console.error("Erreur lors de l'enregistrement des données :", xhr.responseText);
            alert("Erreur lors de l'enregistrement des données : " + xhr.responseJSON.message);
        }
    });
});

// Fonction d'édition de journal
function editJournal(rowData) {
    // Remplir le formulaire avec les données du journal sélectionné
    $('#journal_id_modif').val(rowData.id); // ID du journal
    $('#code_journal_modif').val(rowData.code_journal); // Code Journal
    $('#type_journal_modif').val(rowData.type_journal); // Type Journal
    $('#intitule_modif').val(rowData.intitule); // Intitulé
    $('#contre_partie_modif').val(rowData.contre_partie); // Contre Partie

    // Afficher le modal d'édition
    $('#modifierJournalModal').modal('show');
}

// Fonction de mise à jour de journal
$('#modifierJournalForm').on('submit', function (e) {
    e.preventDefault(); // Empêche le rechargement de la page
    var journalId = $('#journal_id_modif').val(); // Récupère l'ID du journal à mettre à jour

    $.ajax({
        url: `/journaux/${journalId}`, // URL pour la mise à jour du journal
        type: 'PUT', // Méthode PUT pour la mise à jour
        data: {
            code_journal: $("#code_journal_modif").val(), // Récupère la valeur du champ code_journal
            type_journal: $("#type_journal_modif").val(), // Récupère la valeur du champ type_journal
            intitule: $("#intitule_modif").val(), // Récupère la valeur du champ intitule
            contre_partie: $("#contre_partie_modif").val(), // Récupère la valeur du champ contre_partie
            _token: $('meta[name="csrf-token"]').attr('content') // Inclut le token CSRF
        },
        success: function (response) {
            table.setData("/journaux/data"); // Recharger les données dans Tabulator
            $('#modifierJournalModal').modal('hide'); // Fermer le modal d'édition
            alert(response.message); // Afficher le message de succès
        },
        error: function (xhr) {
            console.error("Erreur lors de la modification des données :", xhr.responseText); // Log de l'erreur dans la console
            alert("Erreur lors de la modification des données : " + xhr.responseJSON.message); // Afficher le message d'erreur
        }
    });
});



// Fonction de suppression de journal
function deleteJournal(journalId) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce journal ?")) {
        $.ajax({
            url: `/journaux/${journalId}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                table.setData("/journaux/data"); // Recharger les données
                alert(response.message); // Message de succès
            },
            error: function (xhr) {
                console.error("Erreur lors de la suppression :", xhr.responseText);
                alert("Erreur lors de la suppression : " + xhr.responseJSON.message);
            }
        });
    }
}

$(document).ready(function () {
    // Initialiser Select2
    $('.select2').select2();

    // Fonction d'affichage du modal d'ajout
    $('#ajouterJournalModal').on('shown.bs.modal', function () {
        $('#type_journal').focus(); // Met le focus sur le champ "Type Journal"
    });

    // Fonction d'affichage du modal d'édition
    $('#modifierJournalModal').on('shown.bs.modal', function () {
        $('#type_journal_modif').focus(); // Met le focus sur le champ "Type Journal"
    });

    // Gérer la soumission du formulaire avec la touche Entrée
    $('#ajouterJournalForm').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault(); // Empêche le comportement par défaut
            $(this).submit(); // Soumettre le formulaire
        }
    });

    $('#modifierJournalForm').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault(); // Empêche le comportement par défaut
            $(this).submit(); // Soumettre le formulaire
        }
    });
});


</script>


  

 


@endsection