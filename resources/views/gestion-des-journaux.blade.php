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
<meta name="societe-id" content="{{ session('societeId') }}">

</head>

@extends('layouts.user_type.auth')

@section('content')
<body>

    <div class="container my-3">
        <!-- Ligne de titre et action -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h1 class="h3 text-secondary mb-0">Gestion des Journaux</h1>
          <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                  data-bs-toggle="modal" data-bs-target="#ajouterJournalModal"
                  data-bs-toggle="tooltip" data-bs-placement="top" title="Ajouter un Journal">
            <i class="fas fa-plus icon-3d"></i>
            <span>Ajouter un Journal</span>
          </button>
        </div>
      </div>

      <!-- Tableau des journaux -->
      <div id="journal-table" class="border rounded shadow-sm bg-white p-3"></div>

      <!-- Styles pour l'effet 3D sur les icônes -->
      <style>
        .icon-3d {
          font-size: 1.2rem;
          transition: transform 0.2s, box-shadow 0.2s;
          box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }
        .icon-3d:hover {
          transform: translateY(-2px);
          box-shadow: 3px 3px 6px rgba(0, 0, 0, 0.4);
        }
      </style>

      <!-- Initialisation des tooltips Bootstrap -->
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
          });
        });
      </script>

<small id="error-message" class="text-danger" style="display:none;"></small>
<small id="success-message" class="text-success" style="display:none;"></small>


<!-- Modal Ajout -->
<div class="modal fade" id="ajouterJournalModal" tabindex="-1" role="dialog" aria-labelledby="ajouterJournalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary" id="ajouterJournalModalLabel">Créer un Journal</h5>
                <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <option value="Caisse">Caisse</option>
                                <option value="Banque">Banque</option>
                               <option value="Opérations Diverses">Opérations Diverses</option>
                            </select>
                            <!-- Flèche FontAwesome -->
                            <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);"></i>
                        </div>



                        <!-- Contre Partie -->
                        <div class="col-md-6 position-relative">
                            <label for="contre_partie" class="form-label fw-semibold">Contre Partie</label>
                            <select class="form-select2" id="contre_partie" name="contre_partie" >
                                <option value="" selected>Sélectionner une contre partie</option>
                                <!-- Ajoutez d'autres options ici -->
                            </select>
                            <!-- Flèche FontAwesome -->
                            <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);"></i>
                        </div>
                        <!-- Champs IF et ICE cachés par défaut -->
<div class="col-md-6" id="if_ice_container" style="display: none;">
    <label for="if" class="form-label fw-semibold">IF</label>
    <input type="text" class="form-control" id="if" name="if" maxlength="8" pattern="\d{7,8}"
    placeholder="Entrez votre IF (7 ou 8 chiffres)">

    <label for="ice" class="form-label fw-semibold mt-2">ICE</label>
    <input type="text" class="form-control" id="ice" name="ice" maxlength="15" pattern="\d{15}" placeholder="Entrez votre ICE (15 chiffres)">
</div>

                    </div>
                </form>
            </div>
            <div class="modal-footer mt-4 d-flex justify-content-between">
                <!-- Bouton Réinitialiser -->
                <button type="button" class="btn btn-outline-secondary px-4" id="resetFormBtn">
                    <i class="fas fa-sync-alt"></i> Réinitialiser
                </button>
                <div id="alertMessage" class="alert alert-success" role="alert" style="display:none;"></div>

                <!-- Bouton Valider -->
                <button type="submit" form="ajouterJournalForm" class="btn btn-primary px-4">
                    <i class="fas fa-check"></i> Valider
                </button>
            </div>
        </div>

    </div>
</div>

</div>
<div id="alertMessage" class="alert" role="alert" style="display:none;"></div>

<!-- Modal d'édition -->
<div class="modal fade" id="journalModalEdit" tabindex="-1" role="dialog" aria-labelledby="journalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary" id="journalModalLabel">Modifier le Journal</h5>
                <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="journalFormEdit">
                    <input type="hidden" id="editJournalId" value=""> <!-- ID caché du journal -->
                    <div class="row g-3">
                        <!-- Code Journal -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editCodeJournal" class="form-label fw-semibold">Code Journal</label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="editCodeJournal" required placeholder="Entrez le code journal" readonly>
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
                                <option value="Caisse">Caisse</option>
                                <option value="Banque">Banque</option>
                                <option value="Opérations Diverses">Opérations Diverses</option>
                            </select>
                            <!-- Flèche FontAwesome -->
                            {{-- <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);"></i> --}}
                        </div>
<!-- Champs IF et ICE cachés par défaut -->

                        <!-- Contre Partie -->
                        <div class="col-md-6 position-relative" id="contrePartieContainer">
                            <label for="editContrePartie" class="form-label">Contre Partie</label>
                            <select class="form-select form-select-lg shadow-sm"id="editContrePartie" name="contre_partie">
                                <option value="" selected>Sélectionner une contre partie</option>
                                <!-- Ajoutez d'autres options ici -->

                            </select>
                            <!-- Flèche FontAwesome -->
                            {{-- <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);"></i> --}}
                        </div>
                        <div class="col-md-6" id="edit_if_ice_container" style="display: none;">
                            <label for="edit_if" class="form-label fw-semibold">IF</label>
                            <input type="text" class="form-control" id="edit_if" name="edit_if" maxlength="8"  pattern="\d{7,8}" placeholder="Entrez votre IF (7 ou 8 chiffres)">
<small id="error-if" class="text-danger" style="display:none;"></small>

                            <label for="edit_ice" class="form-label fw-semibold mt-2">ICE</label>
                            <input type="text" class="form-control" id="edit_ice" name="edit_ice" maxlength="15" pattern="\d{15}" placeholder="Entrez votre ICE (15 chiffres)">
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
</div>



</main>


<script>




$(document).ready(function () {
    // Initialisation de Select2
    $('#type_journal, #contre_partie').select2({
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        placeholder: 'Sélectionnez une option',
        dropdownAutoWidth: true,
        dropdownParent: $('#ajouterJournalModal')
    });

    const ifIceContainer = $('#if_ice_container');
    const ifInput = $('#if');
    const iceInput = $('#ice');

    // Afficher ou masquer les champs IF et ICE
    $('#type_journal').on('change', function () {
        if ($(this).val() === "Banque") {
            ifIceContainer.show();
        } else {
            ifIceContainer.hide();
            ifInput.val('');
            iceInput.val('');
        }
    });

    // Validation IF : Autoriser uniquement 7 ou 8 chiffres
    ifInput.on('input', function () {
        let value = this.value.replace(/\D/g, ''); // Supprimer tout sauf les chiffres

        // Vérifier si la longueur est entre 7 et 8 chiffres
        if (value.length > 8) {
            value = value.slice(0, 8); // Couper après 8 chiffres
        }

        this.value = value;
    });

    // Vérification à la sortie du champ (blur)
    ifInput.on('blur', function () {
        if (this.value.length !== 7 && this.value.length !== 8) {
            alert('Le champ IF doit contenir exactement 7 ou 8 chiffres.');
            this.value = ''; // Effacer la saisie incorrecte
        }
    });

    // Validation ICE : 15 chiffres uniquement
    iceInput.on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 15);
    });

    // Focus sur le champ de recherche Select2 au chargement du modal
    document.addEventListener("DOMContentLoaded", () => {
        const ajouterJournalModal = document.getElementById('ajouterJournalModal');
        ajouterJournalModal.addEventListener('shown.bs.modal', () => {
            $('#type_journal').select2('open');
        });
    });
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
            headerHozAlign: "center", // Centre le titre de la colonne
            width: 60,
            cellClick: function(e, cell) {
                cell.getRow().toggleSelect();
            }
        },
        {
            title: "Code Journal",
            field: "code_journal",
            editor: "input",
            headerFilter: "input",
            headerHozAlign: "center", // Centre le titre
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 220px; height: 22px;"
                }
            },
        },
        {
            title: "Intitulé",
            field: "intitule",
            editor: "input",
            headerFilter: "input",
            headerHozAlign: "center", // Centre le titre
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 220px; height: 22px;"
                }
            },
        },
        {
            title: "Type Journal",
            field: "type_journal",
            editor: "input",
            headerFilter: "input",
            headerHozAlign: "center", // Centre le titre
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 220px; height: 22px;"
                }
            },
        },
        {
            title: "Contre Partie",
            field: "contre_partie",
            editor: "input",
            headerFilter: "input",
            headerHozAlign: "center", // Centre le titre
            headerFilterParams: {
                elementAttributes: {
                    style: "width: 220px; height: 22px;"
                }
            },
        },
        {
            title: "IF",
            field: "if",
            editor: "input",
            headerFilter: "input",
            visible: false // Masquer la colonne
        },
        {
            title: "ice",
            field: "ice",
            editor: "input",
            headerFilter: "input",
            visible: false // Masquer la colonne
        },
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
                    deleteJournal(rowData.id, rowData.code_journal);
                }
            },
            hozAlign: "center",
            headerSort: false,
            headerHozAlign: "center", // Centre le titre de la colonne actions
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
    // Récupérer l'ID de la société depuis la balise meta
    const societeId = document.querySelector('meta[name="societe-id"]').getAttribute('content');
    if (!societeId) {
        alert("Aucune société sélectionnée dans la session.");
        return;
    }

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
            body: JSON.stringify({
                ids: idsToDelete,
                societeId: societeId  // Ajout de l'ID de la société
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                alert(data.message);
                // Optionnel : rafraîchir ou supprimer les lignes du tableau
                table.deleteRow(selectedRows);
            }
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
    if (typeJournal === 'Caisse') return '/comptes-Caisse';
    if (typeJournal === 'Banque') return '/comptes-Banque';

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
    $("#editContrePartie").val(rowData.contre_partie);
    $("#editJournalId").val(rowData.id);

    // Vérifie si les valeurs if et ice existent avant de les attribuer
    if (rowData.if) {
        $("#edit_if").val(rowData.if);  // Remplir le champ "if"
    } else {
        $("#edit_if").val('');  // Si aucune valeur, vider le champ
    }

    if (rowData.ice) {
        $("#edit_ice").val(rowData.ice);  // Remplir le champ "ice"
    } else {
        $("#edit_ice").val('');  // Si aucune valeur, vider le champ
    }

    // Vérifie si le type de journal est "Banque" pour afficher les champs IF & ICE
    toggleIfIceFields(rowData.type_journal);

    // Charger les comptes en fonction du type de journal
    loadComptesEdit(rowData.type_journal, rowData.contre_partie);

    // Ouvre le modal d'édition
    $('#journalModalEdit').modal('show');
}



// Fonction pour basculer l'affichage des champs IF/ICE selon le type de journal
function toggleIfIceFields(selectedType) {
  if (selectedType === "Banque") {
    $("#edit_if_ice_container").show();
  } else {
    $("#edit_if_ice_container").hide();
    $("#edit_if").val('');
    $("#edit_ice").val('');
  }
}

$(document).ready(function () {

  // Détection du changement de type de journal dans l'édition
  $("#editTypeJournal").on('change', function () {
    var selectedType = $(this).val();
    toggleIfIceFields(selectedType);
    // Charge les comptes selon le type sélectionné (fonction à définir selon votre logique)
    loadComptesEdit(selectedType, null);
  });

  // Validation en temps réel du champ IF : garder uniquement les chiffres et limiter à 8 caractères
  $("#edit_if").on('input', function () {
    let val = this.value.replace(/\D/g, '').slice(0, 8);
    this.value = val;
    if (val.length === 7 || val.length === 8) {
      $(this).removeClass("is-invalid").addClass("is-valid");
    } else {
      $(this).removeClass("is-valid").addClass("is-invalid");
    }
  }).on('blur', function () {
    if (this.value && (this.value.length !== 7 && this.value.length !== 8)) {
      $(this).addClass("is-invalid");
      $("#error-if").text("Le champ IF doit contenir exactement 7 ou 8 chiffres.").show();
    } else {
      $(this).removeClass("is-invalid");
      $("#error-if").hide();
    }
  });

  // Validation en temps réel du champ ICE : autoriser uniquement 15 chiffres
  $("#edit_ice").on('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 15);
  });

  // Soumission du formulaire d'édition
  $('#journalFormEdit').on('submit', function (e) {
    e.preventDefault();

    let journalId = $("#editJournalId").val();
    let ifValue = $("#edit_if").val().trim();
    let iceValue = $("#edit_ice").val().trim();
    let submitButton = $(this).find("[type='submit']");

    console.log("Valeur IF envoyée :", ifValue);

    // Vérification du champ IF : doit contenir exactement 7 ou 8 chiffres
    if (ifValue && (!/^\d{7,8}$/.test(ifValue))) {
      $("#edit_if").addClass("is-invalid");
      $("#error-if").text("Le champ IF doit contenir exactement 7 ou 8 chiffres.").show();
      return;
    } else {
      $("#edit_if").removeClass("is-invalid");
      $("#error-if").hide();
    }

    submitButton.prop("disabled", true);

    $.ajax({
      url: "/journaux/" + journalId,
      type: "PUT",
      data: {
        _token: $("meta[name='csrf-token']").attr('content'),
        code_journal: $("#editCodeJournal").val(),
        type_journal: $("#editTypeJournal").val(),
        contre_partie: $("#editContrePartie").val(),
        intitule: $("#editIntituleJournal").val(),
        if: ifValue,
        ice: iceValue
      },
      success: function (response) {
        if (response.success) {
          table.setData("/journaux/data").then(function () {
            showAlert("Journal mis à jour avec succès", "success");
            $('#journalModalEdit').modal('hide');
          }).catch(function () {
            showAlert("Erreur lors du rechargement des données.", "danger");
          });
        } else {
          showAlert(response.message || "Erreur lors de la mise à jour du journal.", "danger");
        }
      },
      error: function (xhr) {
        let errorMsg = "Erreur lors de la mise à jour du journal.";
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMsg = xhr.responseJSON.message;
        }
        showAlert(errorMsg, "danger");
      },
      complete: function () {
        submitButton.prop("disabled", false);
      }
    });
  });

  // Réinitialiser les champs lors de la fermeture du modal d'édition
  $('#journalModalEdit').on('hidden.bs.modal', function () {
    $("#edit_if_ice_container").hide();
    $("#edit_if").val('');
    $("#edit_ice").val('');
  });
});




$(document).ready(function () {
    // Fonction d'affichage de l'alerte clignotante pendant 2 secondes
    function showAlert(message) {
        const $alert = $("#alertMessage");
        $alert.text(message).show();

        let blinkInterval = 300; // durée entre chaque fadeToggle en ms
        let elapsed = 0;
        const interval = setInterval(function () {
            $alert.fadeToggle(blinkInterval);
            elapsed += blinkInterval;
            if (elapsed >= 2000) {
                clearInterval(interval);
                $alert.fadeOut(300);
            }
        }, blinkInterval);
    }

    // Gestionnaire d'événement pour la soumission du formulaire d'ajout
    $('#ajouterJournalForm').on('submit', function (e) {
        e.preventDefault();

        // Récupération des valeurs du formulaire
        const codeJournal = $("#code_journal").val().trim();
        const typeJournal = $("#type_journal").val();
        let contrePartie = $("#contre_partie").val();
        const intitule = $("#intitule").val();
        const ifVal = $("#if").val();
        const iceVal = $("#ice").val();

        // Vérification initiale pour éviter une requête inutile
        if (!codeJournal) {
            alert("Le code journal est requis.");
            return;
        }

        // Si le type de journal est "Opérations Diverses", forcer contrePartie à null
        if (typeJournal === "Opérations Diverses") {
            contrePartie = null;
        }

        // Vérifier via AJAX si le code journal existe déjà pour cette société
        $.ajax({
            url: '/check-journal',
            type: 'GET',
            data: {
                code_journal: codeJournal
            },
            success: function (response) {
                if (response.exists) {
                    // Afficher le message d'erreur (en rouge) et ajouter la classe is-invalid
                    $("#alertMessage")
                        .removeClass('alert-success')
                        .addClass('alert-danger')
                        .text('Ce code journal existe déjà pour cette société')
                        .fadeIn(300);
                    setTimeout(function () {
                        $("#alertMessage").fadeOut(300);
                    }, 2000);
                    $("#code_journal").addClass('is-invalid');
                } else {
                    $("#code_journal").removeClass('is-invalid');

                    // Préparer les données à envoyer
                    let formData = {
                        _token: $("meta[name='csrf-token']").attr('content'),
                        code_journal: codeJournal,
                        type_journal: typeJournal,
                        contre_partie: contrePartie,
                        intitule: intitule,
                        if: ifVal,
                        ice: iceVal,
                    };

                    // Envoyer le formulaire via AJAX
                    $.ajax({
                        url: '/journaux',
                        type: 'POST',
                        data: formData,
                        success: function (response) {
                            // Mettre à jour le tableau des journaux (ici via Tabulator, par exemple)
                            table.setData("/journaux/data").then(function () {
                                // Afficher le message de succès clignotant (en vert)
                                $("#alertMessage")
                                    .removeClass('alert-danger')
                                    .addClass('alert-success');
                                showAlert('Journal ajouté avec succès');

                                // Réinitialiser le formulaire
                                $('#ajouterJournalForm').trigger("reset");
                                // Si vous utilisez Select2, réinitialisez les sélections
                                $('#type_journal, #contre_partie').val(null).trigger('change');
                                // Replacer le focus sur le premier champ
                                $("#code_journal").focus();
                                // Masquer le modal d'ajout
                                $('#journalModal').modal('hide');
                            });
                        },
                        error: function (xhr, status, error) {
                            $("#alertMessage")
                                .removeClass('alert-success')
                                .addClass('alert-danger')
                                .text("Erreur lors de l'ajout du journal.")
                                .fadeIn(300);
                            setTimeout(function () {
                                $("#alertMessage").fadeOut(300);
                            }, 2000);
                        }
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('Erreur lors de la vérification du code journal:', error);
                $("#alertMessage")
                    .removeClass('alert-success')
                    .addClass('alert-danger')
                    .text("Erreur lors de la vérification du code journal.")
                    .fadeIn(300);
                setTimeout(function () {
                    $("#alertMessage").fadeOut(300);
                }, 2000);
            }
        });
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
// Fonction pour supprimer un journal
// Fonction pour supprimer un journal
function deleteJournal(journalId, codeJournal) {
    // Récupérer l'ID de la société depuis la balise meta
    const societeId = $('meta[name="societe-id"]').attr('content');
    if (!societeId) {
        alert("Aucune société sélectionnée dans la session.");
        return;
    }

    // Déterminer si le journal est mouvementé (en normalisant la chaîne)
    const isMouvemented = codeJournal && codeJournal.trim().toLowerCase() === 'mouvementé';

    // Si le journal n'est pas mouvementé, afficher une confirmation
    if (!isMouvemented) {
        if (!confirm("Êtes-vous sûr de vouloir supprimer ce journal ?")) {
            return;
        }
    }
    // Pour un journal mouvementé, on ne montre pas de confirmation et on passe directement à la suppression

    $.ajax({
        url: `/journaux/${journalId}`,
        type: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            societeId: societeId
        },
        success: function (response) {
            // Recharger les données du tableau (par exemple via Tabulator)
            table.setData("/journaux/data");
            alert(response.message);
        },
        error: function (xhr) {
            console.error("Erreur lors de la suppression :", xhr.responseText);
            let errorMsg = "Erreur lors de la suppression.";
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            alert(errorMsg);
        }
    });
}





 });



</script>







@endsection
