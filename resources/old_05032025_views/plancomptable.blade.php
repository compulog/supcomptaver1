<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des comptes</title>

    <!-- Liens CSS et JS externes -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
    <script src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Styles personnalisés -->
    <style>

    </style>
</head>
<body>
@extends('layouts.user_type.auth')

@section('content')


<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary">Liste des Plans Comptables</h3>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="addPlanComptableBtn" data-bs-toggle="modal" data-bs-target="#planComptableModalAdd">
                <i class="fas fa-plus me-2"></i> Ajouter
            </button>
            <button class="btn btn-outline-secondary" id="importPlanComptableBtn" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-2"></i> Importer
            </button>
            <a href="{{ route('plan.comptable.excel') }}" class="btn btn-outline-success">
                <i class="fas fa-file-export me-2"></i> Exporter en Excel
            </a>
            <form action="{{ route('export.plan_comptable') }}" method="GET" style="display: inline;">
                <input type="hidden" name="societe_id" value="{{ session('societe_id') }}">
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-file-pdf me-2"></i> Exporter en PDF
                </button>
            </form>
        </div>
    </div>

    <!-- Statistiques -->
    <span id="select-stats" class="text-muted"></span>

    <!-- Tableau des plans comptables -->
    <div id="plan-comptable-table" class="border rounded shadow-sm bg-white p-3"></div>
</div>



@if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger" role="alert">
        {{ session('error') }}
    </div>
@endif
<!-- Modal d'importation du plan comptable -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog shadow-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="importModalLabel">Importation du Plan Comptable</h5>
                <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importForm" action="{{ route('plancomptable.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}">

                    <!-- Chargement du fichier -->
                    <div class="mb-3">
                        <label for="file" class="form-label">Fichier Excel</label>
                        <input type="file" class="form-control shadow-sm" name="file" id="file" accept=".xls,.xlsx" required>
                    </div>

                    <!-- Sélection des colonnes -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="colonne_compte" class="form-label">Colonne Compte</label>
                            <select class="form-control shadow-sm" name="colonne_compte" id="colonne_compte" required>
                                <option value="">-- Sélectionnez une colonne --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="colonne_intitule" class="form-label">Colonne Intitulé</label>
                            <select class="form-control shadow-sm" name="colonne_intitule" id="colonne_intitule" required>
                                <option value="">-- Sélectionnez une colonne --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tableau de prévisualisation -->
                    <div class="mt-3">
                        <h6>Prévisualisation des données</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="previewTable">
                                <thead class="table-dark">
                                    <tr id="previewHeader">
                                        <!-- Les en-têtes seront insérés ici -->
                                    </tr>
                                </thead>
                                <tbody id="previewBody">
                                    <!-- Les lignes seront insérées ici -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-between mt-3">
                        <button type="reset" class="btn btn-light d-flex align-items-center">
                            <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-primary d-flex align-items-center ms-2">
                            <i class="bi bi-upload me-1"></i> Importer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('file').addEventListener('change', function (e) {
    const file = e.target.files[0];
    const reader = new FileReader();

    reader.onload = function (event) {
        const data = new Uint8Array(event.target.result);
        const workbook = XLSX.read(data, { type: 'array' });

        const sheetName = workbook.SheetNames[0]; // Prendre la première feuille
        const worksheet = workbook.Sheets[sheetName];
        const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1 }); // Lire toutes les lignes

        const previewHeader = document.getElementById('previewHeader');
        const previewBody = document.getElementById('previewBody');
        const compteSelect = document.getElementById('colonne_compte');
        const intituleSelect = document.getElementById('colonne_intitule');

        // Réinitialiser les options et la prévisualisation
        compteSelect.innerHTML = '<option value="">-- Sélectionnez une colonne --</option>';
        intituleSelect.innerHTML = '<option value="">-- Sélectionnez une colonne --</option>';
        previewHeader.innerHTML = '';
        previewBody.innerHTML = '';

        if (rows.length > 0) {
            const headers = rows[0]; // Première ligne pour les en-têtes

            // Ajouter les en-têtes au tableau
            headers.forEach((header, index) => {
                const th = document.createElement('th');
                th.textContent = header;
                previewHeader.appendChild(th);

                // Ajouter des options pour les colonnes
                const option = new Option(header, index + 1);
                compteSelect.add(option);
                intituleSelect.add(option.cloneNode(true));
            });

            // Ajouter les données (5 premières lignes) au tableau
            const previewLimit = Math.min(5, rows.length - 1); // Limiter à 5 lignes
            for (let i = 1; i <= previewLimit; i++) {
                const row = rows[i];
                if (row) {
                    const tr = document.createElement('tr');
                    row.forEach((cell) => {
                        const td = document.createElement('td');
                        td.textContent = cell !== undefined ? cell : '';
                        tr.appendChild(td);
                    });
                    previewBody.appendChild(tr);
                }
            }
        } else {
            alert('Le fichier est vide ou ne contient pas de données.');
        }
    };

    reader.readAsArrayBuffer(file);
});

</script>

<!-- Modal Ajouter -->
<div class="modal fade" id="planComptableModalAdd" tabindex="-1" role="dialog" aria-labelledby="planComptableModalLabel" aria-hidden="true">
    <div class="modal-dialog shadow-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="planComptableModalLabel">Ajouter Plan Comptable</h5>
                <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="planComptableFormAdd">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="compte" class="form-label">Compte</label>
                            <input type="text" class="form-control shadow-sm" id="compte" name="compte" required>
                        </div>
                        <div class="col-md-6">
                            <label for="intitule" class="form-label">Intitulé</label>
                            <input type="text" class="form-control shadow-sm" id="intitule" name="intitule" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="reset" class="btn btn-light d-flex align-items-center">
                            <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-primary d-flex align-items-center ms-2">
                            <i class="bi bi-plus-circle me-1"></i> Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifier -->
<div class="modal fade" id="planComptableModalEdit" tabindex="-1" role="dialog" aria-labelledby="planComptableModalLabel" aria-hidden="true">
    <div class="modal-dialog shadow-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="planComptableModalLabel">Modifier Plan Comptable</h5>
                <button type="button" class="btn-close text-white bg-dark shadow" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="planComptableFormEdit">
                    @csrf
                    <input type="hidden" id="editPlanComptableId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editCompte" class="form-label">Compte</label>
                            <input type="text" class="form-control shadow-sm" id="editCompte" name="compte" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editIntitule" class="form-label">Intitulé</label>
                            <input type="text" class="form-control shadow-sm" id="editIntitule" name="intitule" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="reset" class="btn btn-light d-flex align-items-center">
                            <i class="bi bi-arrow-clockwise me-1"></i> Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-primary d-flex align-items-center ms-2">
                            <i class="bi bi-check-circle me-1"></i> Modifier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>

$(document).ready(function () {
    var nombreChiffresCompte = {{ $societe->nombre_chiffre_compte }}; // Longueur exacte du compte
    var societeId = $('#societe_id').val(); // ID de la société

    // Limiter la longueur du champ "compte" pour qu'il respecte le nombre de chiffres
    $('#compte').attr('maxlength', nombreChiffresCompte);

    // Variable pour éviter la répétition d'alertes bloquantes
    let validationEnCours = false;

    // Mettre le focus sur le champ "compte" à l'ouverture du modal
    $('#planComptableModalAdd').on('shown.bs.modal', function () {
        $('#compte').focus();
    });

    // Validation du champ "compte" uniquement lors de la soumission du formulaire
    $("#planComptableFormAdd").on("submit", function (e) {
        e.preventDefault();

        var compte = $("#compte").val().trim();
        var intitule = $("#intitule").val().trim();

        // Vérification du compte : doit avoir la bonne longueur
        if (compte.length !== nombreChiffresCompte) {
            alert(`Le compte doit comporter exactement ${nombreChiffresCompte} chiffres.`);
            $("#compte").focus(); // Retourner le focus sur le champ "compte"
            return; // Empêcher l'envoi du formulaire tant que le compte n'est pas correct
        }

        // Vérifier si le compte existe déjà
        var comptesExistants = table.getData().map(row => row.compte);
        if (comptesExistants.includes(compte)) {
            alert("Ce compte existe déjà !");
            $("#compte").focus();
            return; // Empêcher l'envoi du formulaire si le compte existe déjà
        }

        // Vérifier que le champ "intitule" est rempli
        if (!intitule) {
            alert("Le champ Intitulé est obligatoire.");
            $("#intitule").focus();
            return; // Empêcher l'envoi si l'intitulé est vide
        }

        // Soumettre les données au serveur via AJAX
        $.ajax({
            url: "/plancomptable",
            type: "POST",
            data: {
                compte: compte,
                intitule: intitule,
                societe_id: societeId,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function () {
                $("#planComptableFormAdd button").text("En cours...").prop("disabled", true);
            },
            success: function (response) {
                if (response.success) {
                    alert("Plan comptable ajouté avec succès !");
                    table.setData("/plancomptable/data"); // Rafraîchir le tableau
                    $("#planComptableFormAdd")[0].reset(); // Réinitialiser le formulaire
                    $("#planComptableModalAdd").modal("hide"); // Fermer le modal
                } else {
                    alert(response.error || "Une erreur s'est produite.");
                }
            },
            error: function (xhr) {
                console.error("Erreur:", xhr.responseText);
                alert("Erreur lors de l'ajout du plan comptable.");
            },
            complete: function () {
                $("#planComptableFormAdd button").text("Ajouter").prop("disabled", false);
            }
        });
    });

    // Vérification lorsque l'utilisateur quitte le champ "compte"
    $('#compte').on('blur', function () {
        var compteValue = $(this).val().trim();

        // Si la longueur est incorrecte, on l'informe seulement une fois
        if (compteValue.length > 0 && compteValue.length !== nombreChiffresCompte) {
            alert(`Le compte doit comporter exactement ${nombreChiffresCompte} chiffres.`);
            $(this).val(''); // Réinitialiser le champ si incorrect
            $(this).focus(); // Mettre le focus de nouveau sur le champ
        }
    });

    // Nettoyer les classes résiduelles après fermeture du modal
    $("#planComptableModalAdd").on("hidden.bs.modal", function () {
        $(".modal-backdrop").remove();
        $("body").removeClass("modal-open").css("padding-right", "");
    });
});



 // Fonction pour gérer la modification des plans comptables
   // Configuration AJAX pour inclure automatiquement le token CSRF dans chaque requête
   $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
});

// Soumission du formulaire de modification
$("#planComptableFormEdit").on("submit", function(e) {
    e.preventDefault();

    var planComptableId = $("#editPlanComptableId").val();

    // Envoi de la requête AJAX pour mettre à jour le plan comptable
    $.ajax({
        url: "/plancomptable/" + planComptableId,
        type: "PUT",
        data: {
            compte: $("#editCompte").val(),
            intitule: $("#editIntitule").val(),
        },
        beforeSend: function() {
            // Indiquer le début de la requête, par exemple en affichant un spinner
            $("#planComptableModalEdit .btn-primary").text("En cours...").prop("disabled", true);
        },
        success: function(response) {
            // Mise à jour de la table Tabulator
            table.setData("/plancomptable/data");

            // Masquer le modal et réinitialiser le formulaire
            $("#planComptableModalEdit").modal("hide");
            $("#planComptableFormEdit")[0].reset();
            $("#editPlanComptableId").val("");

            // Message de succès (optionnel)
            alert("Plan comptable mis à jour avec succès !");
        },
        error: function(xhr) {
            // Gestion des erreurs
            var errorMessage = "Erreur lors de l'enregistrement des données.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            alert(errorMessage);
        },
        complete: function() {
            // Rétablir le bouton après la requête
            $("#planComptableModalEdit .btn-primary").text("Modifier").prop("disabled", false);
        }
    });
});

// Fonction pour ouvrir le formulaire de modification avec les données existantes
function editPlanComptable(data) {
    $("#editPlanComptableId").val(data.id);
    $("#editCompte").val(data.compte);
    $("#editIntitule").val(data.intitule);
    $("#planComptableModalEdit").modal("show");
}



    // Fonction pour supprimer un plan comptable
    function deletePlanComptable(id) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce plan comptable ?")) {
        $.ajax({
            url: "/plancomptable/" + id,
            type: "DELETE",
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Recharger les données pour refléter la suppression
                table.setData("/plancomptable/data");
                alert("Plan comptable supprimé avec succès !");
            },
            error: function(xhr) {
                alert("Erreur lors de la suppression du plan comptable. Veuillez réessayer.");
            }
        });
    }
}

// Initialiser le tableau avec Tabulator


var table = new Tabulator("#plan-comptable-table", {
    ajaxURL: "/plancomptable/data", // Votre route pour récupérer les données
    height: "600px",
    layout: "fitColumns",
    selectable: true,
    // rowSelection: true, // Activer la sélection des lignes

    columns: [
        {
            title: `
                <input type='checkbox' id='select-all' />
                <i class="fas fa-trash-alt" id="delete-all-icon" style="cursor: pointer;" title="Supprimer les lignes sélectionnées"></i>
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
        { title: "Compte", field: "compte", editor: "input", headerFilter: "input" },
        { title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input" },
        {
            title: "Actions",
            field: "action-icons",
            formatter: function() {
                return `
                    <i class='fas fa-edit text-primary edit-icon' style='cursor: pointer;'></i>
                    <i class='fas fa-trash-alt text-danger delete-icon' style='cursor: pointer;'></i>
                `;
            },
            cellClick: function(e, cell) {
                var row = cell.getRow();
                if (e.target.classList.contains('edit-icon')) {
                    var rowData = row.getData();
                    editPlanComptable(rowData);  // Fonction d'édition (à définir)
                } else if (e.target.classList.contains('delete-icon')) {
                    var rowData = row.getData();
                    deletePlanComptable(rowData.id);  // Fonction de suppression (à définir)
                }
            },
            hozAlign: "center",
            headerSort: false,
        }
    ],

    rowSelected: function(row) {
        row.getElement().classList.add("bg-light"); // Style de ligne sélectionnée
    },
    rowDeselected: function(row) {
        row.getElement().classList.remove("bg-light"); // Retirer le style de ligne désélectionnée
    }
});

document.addEventListener("DOMContentLoaded", function() {
    // Sélectionner/Désélectionner toutes les lignes
    document.getElementById("select-all").addEventListener("change", function() {
        if (this.checked) {
            // Sélectionner toutes les lignes
            table.getRows().forEach(row => row.select());
        } else {
            // Désélectionner toutes les lignes
            table.getRows().forEach(row => row.deselect());
        }
    });

    // Fonction pour supprimer les lignes sélectionnées
    document.getElementById("delete-all-icon").addEventListener("click", function() {
        var selectedRows = table.getSelectedRows(); // Récupérer les lignes sélectionnées

        if (selectedRows.length === 0) {
            alert("Aucune ligne sélectionnée.");
            return;
        }

        // Récupérer les IDs des lignes sélectionnées
        var idsToDelete = selectedRows.map(function(row) {
            return row.getData().id;
        });

        if (confirm("Voulez-vous vraiment supprimer les lignes sélectionnées ?")) {
            fetch('/plancomptable/deleteSelected', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // CSRF token pour Laravel
                },
                body: JSON.stringify({ ids: idsToDelete })  // Envoi des IDs à supprimer
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    // Supprimer les lignes du tableau après la suppression côté serveur
                    selectedRows.forEach(row => row.delete()); // Supprimer les lignes du tableau
                    alert("Les lignes sélectionnées ont été supprimées.");

                    // Recharger les données pour mettre à jour l'affichage
                    table.replaceData("/plancomptable/data");  // Charger les données mises à jour
                } else {
                    alert("Erreur lors de la suppression.");
                }
            })
            .catch(error => console.error('Erreur:', error));
        }
    });
});

table.on("rowSelectionChanged", function(data, rows) {
    document.getElementById("select-stats").innerHTML = rows.length; // Afficher le nombre de lignes sélectionnées
});


</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js">
</script>
<script>
    document.getElementById('file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const reader = new FileReader();

        reader.onload = function(event) {
            const data = new Uint8Array(event.target.result);
            const workbook = XLSX.read(data, { type: 'array' });

            const sheetName = workbook.SheetNames[0]; // Prendre la première feuille
            const worksheet = workbook.Sheets[sheetName];
            const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1 }); // Lire toutes les lignes

            if (rows.length > 1) {
                // Remplir les options avec les en-têtes de colonnes
                const headers = rows[0]; // Utiliser la première ligne comme en-têtes
                const compteSelect = document.querySelector('input[name="colonne_compte"]');
                const intituleSelect = document.querySelector('input[name="colonne_intitule"]');

                compteSelect.value = ''; // Réinitialiser
                intituleSelect.value = ''; // Réinitialiser

                // Afficher les colonnes disponibles dans les champs de sélection
                // Mettre les indices de colonnes en options
                for (let i = 0; i < headers.length; i++) {
                    const option = new Option(headers[i], i + 1); // Les indices de colonnes sont à partir de 1
                    compteSelect.add(option);
                    intituleSelect.add(option.cloneNode(true));
                }
            }
        };

        reader.readAsArrayBuffer(file);
    });
</script>







<!-- Bootstrap JS (optionnel) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
@endsection

