<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Gestion des Journaux</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">
    <link href="https://unpkg.com/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chargement de jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

<!-- Chargement de Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<!-- Chargement de Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Chargement de Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<link href="/assets/vendor/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">



    <style>
/* Style du select personnalisé */
.form-select {
    background-color: #f8f9fa;
    border: 2px solid #007bff;
    border-radius: 0.375rem; /* bords arrondis */
    font-size: 1rem;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.form-select:focus {
    border-color: #0056b3;
    box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
}

</style>

</head>

@extends('layouts.user_type.auth')

@section('content')
<body>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-primary">Gestion des Journaux</h1>
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ajouterJournalModal">
                <i class="fas fa-plus me-2"></i> Ajouter un Journal
            </button>
        </div>
    </div>


        <!-- Tableau des journaux -->
        <div id="journal-table" class="border rounded shadow-sm bg-white p-3"></div>
    </div>


<!-- Modal Ajout -->
<div class="modal fade" id="ajouterJournalModal" tabindex="-1" role="dialog" aria-labelledby="ajouterJournalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary" id="ajouterJournalModalLabel">Créer un Journal</h5>
                <i class="fas fa-times btn-close" data-bs-dismiss="modal" aria-label="Close"></i>
                @csrf
                <input type="hidden" name="societe_id" value="{{ session('societeId') }}">
            </div>
            <div class="modal-body">
                <form id="ajouterJournalForm">
                    <div class="row g-3">
                        <!-- Code Journal -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code_journal" class="form-label fw-semibold">Code Journal</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="code_journal" name="code_journal" required placeholder="Entrez le code journal">
                            </div>
                        </div>

                        <!-- Intitulé -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="intitule" class="form-label fw-semibold">Intitulé</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="intitule" name="intitule" required placeholder="Entrez l'intitulé">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Type Journal -->
                        <div class="col-md-6 position-relative">
                            <label for="type_journal" class="form-label fw-semibold">Type Journal</label>
                            <select class="form-select form-select-lg" id="type_journal" name="type_journal" required>
                                <option value="" selected>Sélectionner un type</option>
                                <option value="Achats">Achats</option>
                                <option value="Ventes">Ventes</option>
                                <option value="Trésoreries">Trésoreries</option>
                                <option value="Opérations Diverses">Opérations Diverses</option>
                            </select>
                            <!-- Flèche FontAwesome -->
                            <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);"></i>
                        </div>

                        <!-- Contre Partie -->
                        <div class="col-md-6 position-relative">
                            <label for="contre_partie" class="form-label fw-semibold">Contre Partie</label>
                            <select class="form-select form-select-lg shadow-sm" id="contre_partie" name="contre_partie">
                                <option value="" selected>Sélectionner une contre partie</option>
                                <!-- Ajoutez d'autres options ici -->
                            </select>
                            <!-- Flèche FontAwesome -->
                            <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);"></i>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer mt-4 d-flex justify-content-between">
                <!-- Bouton Réinitialiser -->
                <button type="button" class="btn btn-outline-secondary px-4" id="resetFormBtn">
                    <i class="fas fa-sync-alt"></i> Réinitialiser
                </button>

                <!-- Bouton Valider -->
                <button type="submit" form="ajouterJournalForm" class="btn btn-primary px-4">
                    <i class="fas fa-check"></i> Valider
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'édition -->
<div class="modal fade" id="journalModalEdit" tabindex="-1" role="dialog" aria-labelledby="journalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary" id="journalModalLabel">Modifier le Journal</h5>
                <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>
            </div>
            <div class="modal-body">
                <form id="journalFormEdit">
                    <input type="hidden" id="editJournalId" value=""> <!-- ID caché du journal -->
                    <div class="row g-3">
                        <!-- Code Journal -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editCodeJournal" class="form-label fw-semibold">Code Journal</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editCodeJournal" required placeholder="Entrez le code journal">
                            </div>
                        </div>

                        <!-- Intitulé -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editIntituleJournal" class="form-label fw-semibold">Intitulé</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editIntituleJournal" required placeholder="Entrez l'intitulé">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Type Journal -->
                        <div class="col-md-6 position-relative">
                            <label for="editTypeJournal" class="form-label fw-semibold">Type Journal</label>
                            <select class="form-select form-select-lg shadow-sm" id="editTypeJournal" name="type_journal_modif">
                                <option value="" selected>Sélectionner un type</option>
                                <option value="Achats">Achats</option>
                                <option value="Ventes">Ventes</option>
                                <option value="Trésoreries">Trésoreries</option>
                                <option value="Opérations Diverses">Opérations Diverses</option>
                            </select>
                            <!-- Flèche FontAwesome -->
                            <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);"></i>
                        </div>

                        <!-- Contre Partie -->
                        <div class="col-md-6 position-relative" id="contrePartieContainer">
                            <label for="editContrePartie" class="form-label fw-semibold">Contre Partie</label>
                            <select class="form-select form-select-lg shadow-sm" id="editContrePartie" name="contre_partie">
                                <option value="" selected>Sélectionner une contre partie</option>
                                <!-- Ajoutez d'autres options ici -->
                            </select>
                            <!-- Flèche FontAwesome -->
                            <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);"></i>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer mt-4 d-flex justify-content-between">
                <!-- Bouton Réinitialiser -->
                <button type="button" class="btn btn-outline-secondary px-4" id="resetFormBtn">
                    <i class="fas fa-sync-alt"></i> Réinitialiser
                </button>

                <!-- Bouton Sauvegarder -->
                <button type="submit" form="journalFormEdit" class="btn btn-primary px-4">
                    <i class="fas fa-check"></i> Sauvegarder
                </button>
            </div>
        </div>
    </div>
</div>



</main>


<script>

$(document).ready(function () {
    // Initialisation de Select2
    $('#editTypeJournal, #editContrePartie, #type_journal, #contre_partie').select2({
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        // placeholder: 'Sélectionnez une option', // Ajoute un placeholder
    });

    document.addEventListener("DOMContentLoaded", () => {
    // Curseur sur le premier champ lors de l'ouverture de la modal d'ajout
    const ajouterJournalModal = document.getElementById('ajouterJournalModal');
    ajouterJournalModal.addEventListener('shown.bs.modal', () => {
        const firstInput = document.getElementById('code_journal');
        if (firstInput) {
            firstInput.focus();
        }
    });

    // Curseur sur le premier champ lors de l'ouverture de la modal d'édition
    const journalModalEdit = document.getElementById('journalModalEdit');
    journalModalEdit.addEventListener('shown.bs.modal', () => {
        const firstInput = document.getElementById('editCodeJournal');
        if (firstInput) {
            firstInput.focus();
        }
    });
});

   // Réinitialisation du formulaire de création
document.getElementById('resetFormBtn').addEventListener('click', function () {
    // Sélectionnez le formulaire et réinitialisez-le
    const form = document.getElementById('ajouterJournalForm');
    form.reset();
});

// Réinitialisation du formulaire d'édition
document.querySelector('#journalModalEdit #resetFormBtn').addEventListener('click', function () {
    // Sélectionnez le formulaire et réinitialisez-le
    const form = document.getElementById('journalFormEdit');
    form.reset();

    // Si des champs doivent être pré-remplis ou si des valeurs doivent être réinitialisées manuellement
    document.getElementById('editJournalId').value = '';
});



    // Récupérer le CSRF token
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Configurer AJAX pour inclure automatiquement le CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });



    var table = new Tabulator("#journal-table", {
    ajaxURL: "/journaux/data", // URL pour récupérer les données
    height: "600px", // Hauteur pour activer le scroll vertical
    layout: "fitColumns", // Ajuster les colonnes à la largeur disponible
    selectable: true,
    rowSelection: true,
    columns: [
        {
            title: `<i class="fas fa-check-square" id="selectAllIcon" title="Sélectionner tout" style="cursor: pointer;"></i>
                    <i class="fas fa-trash-alt" id="deleteAllIcon" title="Supprimer toutes les lignes sélectionnées" style="cursor: pointer;"></i>`,
            field: "select",
            formatter: "rowSelection",
            headerSort: false,
            hozAlign: "center",
            width: 60,
            cellClick: function(e, cell) {
                cell.getRow().toggleSelect();
            }
        },
        { title: "Code Journal", field: "code_journal", editor: "input", headerFilter: "input" },
        { title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input" },
        { title: "Type Journal", field: "type_journal", editor: "input", headerFilter: "input" },
        { title: "Contre Partie", field: "contre_partie", editor: "input", headerFilter: "input" },
        {
            title: "Actions",
            field: "action-icons",
            formatter: function() {
                return `
                    <i class='fas fa-edit text-primary edit-icon' style='font-size: 0.9em; cursor: pointer;' title='Modifier'></i>
                    <i class='fas fa-trash-alt text-danger delete-icon' style='font-size: 0.9em; cursor: pointer;' title='Supprimer'></i>
                `;
            },
            cellClick: function(e, cell) {
                var row = cell.getRow();
                if (e.target.classList.contains('edit-icon')) {
                    var rowData = cell.getRow().getData();
                    editJournal(rowData);
                } else if (e.target.classList.contains('delete-icon')) {
                    var rowData = cell.getRow().getData();
                    deleteJournal(rowData.id);
                }
            },
            hozAlign: "center",
            headerSort: false,
        }
    ],
    rowSelected: function(row) {
        row.getElement().classList.add("bg-light");
    },
    rowDeselected: function(row) {
        row.getElement().classList.remove("bg-light");
    }
});


    // Fonction pour supprimer les lignes sélectionnées côté serveur
    function deleteSelectedRows() {
        var selectedRows = table.getSelectedRows();
        var idsToDelete = selectedRows.map(function(row) {
            return row.getData().id;
        });

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
                alert(data.message);
                table.deleteRow(selectedRows);
            })
            .catch(error => {
                console.error('Erreur de suppression:', error);
                alert('Erreur lors de la suppression des lignes.');
            });
        }
    }

    // Gestionnaire d'événements pour sélectionner/désélectionner toutes les lignes et supprimer les lignes sélectionnées
    $('#journal-table').on("click", function(e) {
        if (e.target.id === "selectAllIcon") {
            if (table.getSelectedRows().length === table.getRows().length) {
                table.deselectRow();
            } else {
                table.selectRow();
            }
        }
        if (e.target.id === "deleteAllIcon") {
            deleteSelectedRows();
        }
    });
// Fonction pour charger les comptes dans le modal d'ajout
function loadComptesAdd(typeJournal) {
    var url = getComptesUrl(typeJournal); // Obtenir l'URL des comptes en fonction du type de journal

    // Masquer ou afficher le champ "contre_partie" selon le type de journal
    if (typeJournal === 'Opérations Diverses') {
        $('#contre_partie').closest('div').hide(); // Masquer le champ contre_partie
    } else {
        $('#contre_partie').closest('div').show(); // Afficher le champ contre_partie
        if (url) {
            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    var options = '<option value="">Sélectionner un compte</option>';
                    data.forEach(function(compte) {
                        options += `<option value="${compte.compte}">${compte.compte} - ${compte.intitule}</option>`;
                    });
                    // Mettre à jour le select du modal d'ajout
                    $('#contre_partie').html(options);
                },
                error: function(xhr, status, error) {
                    alert('Erreur lors du chargement des comptes.');
                }
            });
        }
    }
}

// Fonction d'initialisation pour le modal d'ajout
$(document).ready(function() {
    // Réagir au changement de type de journal dans le modal d'ajout
    $('#type_journal').on('change', function () {
        var selectedType = $(this).val();
        loadComptesAdd(selectedType); // Charger les comptes selon le type sélectionné et masquer/afficher le champ
    });

    // Charger les comptes au démarrage si un type est déjà sélectionné
    if ($('#type_journal').val()) {
        loadComptesAdd($('#type_journal').val()); // Charger les comptes en fonction du type initial
    }
});

// Fonction pour obtenir l'URL des comptes selon le type de journal
function getComptesUrl(typeJournal) {
    if (typeJournal === 'Achats') return '/comptes-achats';
    if (typeJournal === 'Ventes') return '/comptes-ventes';
    if (typeJournal === 'Trésoreries') return '/comptes-tresorerie';
    return null;
}

// Fonction pour charger les comptes dans le modal d'édition
function loadComptesEdit(typeJournal, selectedValue) {
    var url = getComptesUrl(typeJournal); // Obtenir l'URL des comptes en fonction du type de journal

    // Masquer ou afficher le champ "contre_partie" selon le type de journal
    if (typeJournal === 'Opérations Diverses') {
        $('#editContrePartie').closest('div').hide(); // Masquer le champ contre_partie
    } else {
        $('#editContrePartie').closest('div').show(); // Afficher le champ contre_partie
        if (url) {
            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    var options = '<option value="">Sélectionner un compte</option>';
                    data.forEach(function(compte) {
                        options += `<option value="${compte.compte}" ${compte.compte === selectedValue ? 'selected' : ''}>${compte.compte} - ${compte.intitule}</option>`;
                    });
                    // Mettre à jour le select du modal d'édition
                    $('#editContrePartie').html(options);
                },
                error: function(xhr, status, error) {
                    alert('Erreur lors du chargement des comptes.');
                }
            });
        }
    }
}

// Fonction d'édition d'un journal
function editJournal(rowData) {
    $("#editCodeJournal").val(rowData.code_journal);
    $("#editIntituleJournal").val(rowData.intitule);
    $("#editTypeJournal").val(rowData.type_journal);
    $("#editContrePartie").val(rowData.contre_partie); // Mettre la valeur existante de contrepartie
    $("#editJournalId").val(rowData.id);

    // Charger les comptes en fonction du type de journal pour l'édition et masquer/afficher le champ
    loadComptesEdit(rowData.type_journal, rowData.contre_partie); // Charger les comptes et pré-sélectionner celui existant

    $('#journalModalEdit').modal('show');
}

// Réagir au changement de type de journal dans le modal d'édition
$("#editTypeJournal").on('change', function () {
    var selectedType = $(this).val();
    loadComptesEdit(selectedType, null); // Recharger les comptes pour le champ "contre_partie" et masquer/afficher le champ
});

// Soumission du formulaire d'édition
$('#journalFormEdit').on('submit', function (e) {
    e.preventDefault();
    let journalId = $("#editJournalId").val();

    $.ajax({
        url: "/journaux/" + journalId,
        type: "PUT",
        data: {
            _token: $("meta[name='csrf-token']").attr('content'),
            code_journal: $("#editCodeJournal").val(),
            type_journal: $("#editTypeJournal").val(),
            contre_partie: $("#editContrePartie").val(),
            intitule: $("#editIntituleJournal").val(),
        },
        success: function (response) {
            if (response.success) {
                table.setData("/journaux/data").then(function () {
                    alert('Journal mis à jour avec succès');
                    $('#journalModalEdit').modal('hide');
                }).catch(function (error) {
                    alert("Erreur lors du rechargement des données.");
                });
            } else {
                alert(response.message || 'Erreur lors de la mise à jour du journal.');
            }
        },
        error: function (xhr, status, error) {
            alert('Erreur lors de la mise à jour du journal.');
        }
    });
});

// Gestionnaire d'événement pour la soumission du formulaire d'ajout
$('#ajouterJournalForm').on('submit', function (e) {
    e.preventDefault();

    let formData = {
        _token: $("meta[name='csrf-token']").attr('content'),
        code_journal: $("#code_journal").val(),
        type_journal: $("#type_journal").val(),
        contre_partie: $("#contre_partie").val(),  // Assurez-vous que la valeur de "contre_partie" est capturée
        intitule: $("#intitule").val(),
    };

    $.ajax({
        url: '/journaux',
        type: 'POST',
        data: formData,
        success: function(response) {
            table.setData("/journaux/data").then(function() {
                alert('Journal ajouté avec succès');
                $('#journalModal').modal('hide');
            });
        },
        error: function(xhr, status, error) {
            alert('Erreur lors de l\'ajout du journal.');
        }
    });
});

// Fonction d'initialisation pour le modal d'ajout
$(document).ready(function() {
    // Réagir au changement de type de journal dans le modal d'ajout
    $('#type_journal').on('change', function () {
        var selectedType = $(this).val();
        loadComptesAdd(selectedType); // Charger les comptes selon le type sélectionné et masquer/afficher le champ
    });

    // Charger les comptes au démarrage si un type est déjà sélectionné
    if ($('#type_journal').val()) {
        loadComptesAdd($('#type_journal').val()); // Charger les comptes en fonction du type initial
    }
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

 });



</script>







@endsection
