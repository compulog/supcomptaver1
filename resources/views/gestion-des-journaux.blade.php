<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Gestion des Journaux</title>
  
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
    background-color: #f8f9fa; /* Couleur de l'en-tête */
}


#tabulator-table .tabulator-header .tabulator-col-title {
    font-size: 0.85em; /* Taille de police des titres des colonnes */
}

/* Ajuste le champ de recherche dans le header */
.tabulator .tabulator-header input[type="search"] {
    height: 20px; /* Diminue la hauteur */
    padding: 1px 3px; /* Ajuste le padding interne */
    font-size: 0.8em; /* Diminue légèrement la police */}
    .btn-custom-gradient {
    background-image: linear-gradient(to right, #344767, #31477a) !important; /* Dégradé de gauche à droite */
    color: white !important; /* Couleur du texte en blanc */
    border: none; /* Pas de bordure */
    transition: background-color!important 0.1s ease; /* Transition douce pour le survol */
}


   

#tabulator-table .tabulator-row {
    transition: all 0.1s ease-in-out; /* Animation pour un effet dynamique */
}

    /* background-color: #e9ecef !important; Fond gris clair au survol */
    .tabulator .tabulator-row:hover {
    background-color: #31477a !important;  /* Couleur de survol */
    color: white;  /* Texte en blanc lors du survol pour plus de contraste */
}


.bg-light {
    background-color: #d1ecf1 !important; /* Fond bleu clair pour la sélection de ligne */
}

.tabulator .tabulator-col, .tabulator .tabulator-header {
    font-weight: bold;
    color: #495057 !important; /* Couleur de texte sombre */
}


.btn-custom-gradient:hover {
    background-image: linear-gradient(to right, #536fb2, #344767)!important; /* Inverser le dégradé au survol */
}

#fournisseur-table {
    overflow: auto; /* Permet le défilement */
    max-height: 800px; /* Hauteur maximale du conteneur */
}
/* Applique un style ajusté tout en gardant le style du bouton btn-secondary */
.input-group .btn-secondary {
    padding: 0.375rem 0.75rem; /* Ajuste le padding horizontal et vertical */
    font-size: 1rem; /* Taille du texte cohérente avec celle de l'input */
    font-weight: 400; /* Poids de police standard */
    color: #6c757d; /* Couleur par défaut du bouton secondaire (qui est la couleur d'origine de btn-secondary) */
    background-color: #e2e6ea; /* Couleur d'arrière-plan du bouton secondaire */
    border-color: #adb5bd; /* Bordure du bouton secondaire */
    border-radius: 0.25rem; /* Coins arrondis pour un look plus moderne */
   
}


</style>

</head>

@extends('layouts.user_type.auth')

@section('content')
<body>


<div class="container mt-5">
        <h1>Gestion des Journaux</h1>
        <button class="btn btn-custom-gradient" data-toggle="modal" data-target="#ajouterJournalModal"> + Journal</button>
       
        <div id="journal-table"></div>
    </div>

 <!-- Modal pour Ajouter Journal -->
 <div class="modal fade" id="ajouterJournalModal" tabindex="-1" role="dialog" aria-labelledby="ajouterJournalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ajouterJournalModalLabel">Créer</h5>
                @csrf
                <!-- Champ caché pour l'ID de la société -->
        <input type="hidden" name="societe_id" value="{{ session('societeId') }}">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="ajouterJournalForm">
                    <div class="row">
                        <!-- Type Journal en premier -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type_journal">Type Journal</label>
                                <select class="form-control" id="type_journal" name="type_journal" required>
                                    <option value="" disabled selected>Sélectionner un type</option>
                                    <option value="Achats">Achats</option>
                                    <option value="Ventes">Ventes</option>
                                    <option value="Trésoreries">Trésoreries</option>
                                    <option value="Opérations Diverses">Opérations Diverses</option>
                                </select>
                            </div>
                        </div>
                        <!-- Contre Partie en deuxième position -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contre_partie">Contre Partie</label>
                                <select class="form-control" id="contre_partie" name="contre_partie">
                                    <option value="" selected>Sélectionner une contre partie</option>
                                    <option value="Option1">Option 1</option>
                                    <option value="Option2">Option 2</option>
                                    <!-- Ajoutez d'autres options ici -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Code Journal en troisième position -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code_journal">Code Journal</label>
                                <input type="text" class="form-control" id="code_journal" name="code_journal" required>
                            </div>
                        </div>
                        <!-- Intitulé en quatrième position -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="intitule">Intitulé</label>
                                <input type="text" class="form-control" id="intitule" name="intitule" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <!-- Bouton Réinitialiser aligné à gauche avec icône -->
                <button type="button" class="btn btn-secondary" id="resetFormBtn">
                    <i class="fas fa-sync-alt"></i> Réinitialiser
                </button>
                <!-- Bouton Valider aligné à droite avec icône de coche -->
                <button type="submit" form="ajouterJournalForm" class="btn btn-custom-gradient">
                    <i class="fas fa-check"></i> Valider
                </button>
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
                <form id="modifierJournalForm" class="d-flex flex-wrap">
                    <input type="hidden" id="journal_id_modif">
                    
                    <!-- Type Journal -->
                    <div class="form-group flex-grow-1 mr-2">
                        <label for="type_journal_modif">Type Journal</label>
                        <select class="form-control" id="type_journal_modif" name="type_journal_modif">
                            <option value="" selected>Sélectionner un type</option>
                            <option value="Achats">Achats</option>
                            <option value="Ventes">Ventes</option>
                            <option value="Trésoreries">Trésoreries</option>
                            <option value="Opérations Diverses">Opérations Diverses</option>
                        </select>
                    </div>
                    
                    <!-- Contre Partie -->
                    <div class="form-group flex-grow-1 mr-2">
                        <label for="contre_partie_modif">Contre Partie</label>
                        <select class="form-control" id="contre_partie_modif" name="contre_partie_modif">
                            <option value="" selected>Sélectionner une contre partie</option>
                            
                        </select>
                    </div>
                    
                    <!-- Code Journal -->
                    <div class="form-group flex-grow-1 mr-2">
                        <label for="code_journal_modif">Code Journal</label>
                        <input type="text" class="form-control" id="code_journal_modif" required>
                    </div>
                    
                    <!-- Intitulé -->
                    <div class="form-group flex-grow-1">
                        <label for="intitule_modif">Intitulé</label>
                        <input type="text" class="form-control" id="intitule_modif">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <!-- Bouton Sauvegarder avec icône de coche -->
                <button type="submit" form="modifierJournalForm" class="btn btn-custom-gradient">
                    <i class="fas fa-check"></i> Sauvegarder
                </button>
            </div>
        </div>
    </div>
</div>

       
</main>


<script>

// Fonction pour passer automatiquement au champ suivant lors de la pression sur "Entrée"
document.addEventListener("DOMContentLoaded", function() {
        // Rendre le bouton "Réinitialiser" fonctionnel
        document.getElementById("resetFormBtn").addEventListener("click", function() {
            document.getElementById("ajouterJournalForm").reset();
        });

        // Empêcher la touche Entrée de soumettre le formulaire, sauf si elle est utilisée dans le champ "Intitulé"
        const form = document.getElementById("ajouterJournalForm");
        form.addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                const formElements = Array.from(form.elements);
                const currentIndex = formElements.indexOf(event.target);
                if (currentIndex > -1 && currentIndex < formElements.length - 1) {
                    formElements[currentIndex + 1].focus();
                    event.preventDefault();
                }
            }
        });
    });


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
    $('#ajouterJournalModal, #modifierJournalModal').on('shown.bs.modal', function () 
    {
        $(this).find('select[name="type_journal"], select[name="type_journal_modif"]').focus();
    });
});






// Initialisation de Tabulator pour afficher les journaux
var table = new Tabulator("#journal-table", {
    ajaxURL: "/journaux/data", // URL pour récupérer les données
    height: "600px", // Hauteur du tableau
    layout: "fitColumns",
    rowSelection: true,
    selectable: true, // Permet la sélection des lignes
    columns: [
        {
            title: `
                <i class="fas fa-check-square" id="selectAllIcon" title="Sélectionner tout" style="cursor: pointer;"></i> 
                <i class="fas fa-trash-alt" id="deleteAllIcon" title="Supprimer toutes les lignes sélectionnées" style="cursor: pointer;"></i>
            `,
            field: "select",
            formatter: "rowSelection", // Active la sélection de ligne
            headerSort: false,
            hozAlign: "center",
            width: 60, // Fixe la largeur de la colonne de sélection
            cellClick: function(e, cell) {
                cell.getRow().toggleSelect();  // Basculer la sélection de ligne
            }
        },
        { 
            title: "Code Journal", 
            field: "code_journal", 
            editor: "input", 
            headerFilter: "input" 
        },
        { 
            title: "Type Journal", 
            field: "type_journal", 
            editor: "input", 
            headerFilter: "input" 
        },
        { 
            title: "Intitulé", 
            field: "intitule", 
            editor: "input", 
            headerFilter: "input" 
        },
        { 
            title: "Contre Partie", 
            field: "contre_partie", 
            editor: "input", 
            headerFilter: "input" 
        },
        {
            title: "Actions",
            field: "action-icons",
            formatter: function() {
                return `
                    <i class='fas fa-edit text-primary edit-icon' style='font-size: 0.9em; cursor: pointer;'></i>
                    <i class='fas fa-trash-alt text-danger delete-icon' style='font-size: 0.9em; cursor: pointer;'></i>
                `;
            },
            cellClick: function(e, cell) {
                var row = cell.getRow();
                
                if (e.target.classList.contains('row-select-checkbox')) {
                    // Synchronise la sélection de la ligne avec l'état de la checkbox
                    if (e.target.checked) {
                        row.select();
                    } else {
                        row.deselect();
                    }
                } else if (e.target.classList.contains('edit-icon')) {
                    var rowData = cell.getRow().getData();
                    editFournisseur(rowData);
                } else if (e.target.classList.contains('delete-icon')) {
                    var rowData = cell.getRow().getData();
                    deleteFournisseur(rowData.id);
                }
            },
            hozAlign: "center",
            headerSort: false,
        }
    ],
    rowSelected: function(row) {
        row.getElement().classList.add("bg-light"); // Ajoute un fond à la ligne sélectionnée
    },
    rowDeselected: function(row) {
        row.getElement().classList.remove("bg-light"); // Supprime le fond de la ligne désélectionnée
    }
});

// Fonction pour supprimer les lignes sélectionnées côté serveur
function deleteSelectedRows() {
    var selectedRows = table.getSelectedRows(); // Récupère les lignes sélectionnées
    var idsToDelete = selectedRows.map(function(row) {
        return row.getData().id; // Récupère les IDs des lignes sélectionnées
    });

    // Envoie les IDs au serveur pour suppression
    if (idsToDelete.length > 0) {
        fetch("/journaux/delete-selected", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids: idsToDelete })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Affiche un message de succès
            table.deleteRow(selectedRows); // Supprime les lignes du tableau côté client
        })
        .catch(error => {
            console.error('Erreur de suppression:', error);
            alert('Erreur lors de la suppression des lignes.');
        });
    }
}

// Gestionnaire d'événements pour sélectionner/désélectionner toutes les lignes et supprimer les lignes sélectionnées
document.getElementById("journal-table").addEventListener("click", function(e) {
    if (e.target.id === "selectAllIcon") {
        if (table.getSelectedRows().length === table.getRows().length) {
            table.deselectRow(); // Désélectionner toutes les lignes
        } else {
            table.selectRow(); // Sélectionner toutes les lignes
        }
    }
    if (e.target.id === "deleteAllIcon") {
        deleteSelectedRows(); // Appelle la fonction de suppression pour les lignes sélectionnées
    }
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

  

    $('#modifierJournalForm').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault(); // Empêche le comportement par défaut
            $(this).submit(); // Soumettre le formulaire
        }
    });
});


$(document).ready(function() {
    // Écoute le clic sur les boutons de suppression
    $('.delete-icon').on('click', function() {
        // Récupère l'ID du journal à partir de l'attribut data-id
        var journalId = $(this).data('id');

        // Confirmer l'action de suppression
        if (confirm('Êtes-vous sûr de vouloir supprimer ce journal ?')) {
            // Envoie une requête AJAX pour supprimer le journal
            $.ajax({
                url: '/journaux/' + journalId,  // URL vers la route de suppression
                type: 'DELETE',  // Méthode HTTP DELETE
                data: {
                    _token: '{{ csrf_token() }}'  // CSRF token pour la sécurité
                },
                success: function(response) {
                    // Retirer la ligne du tableau si la suppression réussie
                    $('tr[data-id="' + journalId + '"]').remove();
                    alert(response.message);  // Afficher un message de succès
                },
                error: function(xhr) {
                    alert('Erreur lors de la suppression du journal.');
                }
            });
        }
    });
});


</script>


  

 


@endsection