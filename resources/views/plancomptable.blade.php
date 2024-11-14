<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des comptes</title>

    <!-- Liens CSS et JS externes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
    <script src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- Styles personnalisés -->
    <style>
        #tabulator-table {
    overflow-y: auto; /* Activer le défilement vertical */
    border: 1px solid #ddd; /* Ajouter une bordure si nécessaire */
}

/* Optionnel : Style pour le tableau */
.tabulator {
    border-collapse: collapse; /* Pour un meilleur rendu visuel */
}


    #tabulator-table .tabulator-header {
    height: 15px; /* Ajustez la hauteur du header */
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
       
#plan-comptable-table .tabulator-row {
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
        
        .btn-custom-gradient {
            background-image: linear-gradient(to right, #344767, #31477a);
            color: white !important;
            border: none;
            transition: background-color 0.3s ease;
        }
        .btn-custom-gradient:hover {
            background-image: linear-gradient(to right, #536fb2, #344767);
        }
    </style>
</head>
<body>
@extends('layouts.user_type.auth')

@section('content')
   

    <div class="container mt-5">
        <h3>Liste des Plans Comptables</h3>
        
        <button class="btn btn-custom-gradient" id="addPlanComptableBtn" data-toggle="modal" data-target="#planComptableModalAdd">
            <i class="fas fa-plus"></i> Ajouter
        </button>
        
        <button class="btn btn-custom-gradient" id="importPlanComptableBtn" data-toggle="modal" data-target="#importModal">
            <i class="fas fa-file-import"></i> Importer
        </button>
        
        <a href="{{ route('plan.comptable.excel') }}" class="btn btn-custom-gradient">
            <i class="fas fa-file-export"></i> Exporter en Excel
        </a>
        
        <!-- Formulaire pour exporter en PDF -->
        <form action="{{ route('export.plan_comptable') }}" method="GET" style="display: inline;">
            <input type="hidden" name="societe_id" value="{{ session('societe_id') }}">
            <button type="submit" class="btn btn-custom-gradient">
                <i class="fas fa-file-pdf"></i> Exporter en PDF
            </button>
        </form>
        
        <span id="select-stats"></span>
        
       
        <div id="plan-comptable-table" class="mt-3"></div>
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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Importation du Plan Comptable</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importForm" action="{{ route('plancomptable.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- Champ caché pour le societe_id -->
                    <input type="hidden" name="societe_id" id="societe_id" value="{{ session('societeId') }}"><!-- Exemple pour la société 3 -->

                    <div class="form-group">
                        <label for="file">Fichier Excel</label>
                        <input type="file" class="form-control" name="file" id="file" required>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label for="colonne_compte">Colonne Compte</label>
                            <input type="number" class="form-control" name="colonne_compte" id="colonne_compte" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="colonne_intitule">Colonne Intitulé</label>
                            <input type="number" class="form-control" name="colonne_intitule" id="colonne_intitule" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </form>
            </div>
        </div>
    </div>
</div>




<!-- Modal add-->
<div class="modal fade" id="planComptableModalAdd" tabindex="-1" role="dialog" aria-labelledby="planComptableModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planComptableModalLabel">Ajouter Plan Comptable</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="planComptableFormAdd">
                    @csrf <!-- CSRF token pour la sécurité -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="compte">Compte</label>
                                <input type="text" class="form-control" id="compte" name="compte" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="intitule">Intitulé</label>
                                <input type="text" class="form-control" id="intitule" name="intitule" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>





<!-- Modal edit-->
<div class="modal fade" id="planComptableModalEdit" tabindex="-1" role="dialog" aria-labelledby="planComptableModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planComptableModalLabel">Modifier Plan Comptable</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="planComptableFormEdit">
                    @csrf
                    <input type="hidden" id="editPlanComptableId" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editCompte">Compte</label>
                                <input type="text" class="form-control" id="editCompte" name="compte" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editIntitule">Intitulé</label>
                                <input type="text" class="form-control" id="editIntitule" name="intitule" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
 $("#planComptableFormAdd").on("submit", function(e) {
    e.preventDefault(); // Empêche le rechargement de la page

    // Récupérer les valeurs des champs
    const compte = $("#compte").val();
    const intitule = $("#intitule").val();

    // Affichage de la validation des données dans la console pour déboguer
    console.log("Compte:", compte);
    console.log("Intitulé:", intitule);

    $.ajax({
        url: "/plancomptable",
        type: "POST",
        data: {
            compte: compte,
            intitule: intitule,
            _token: '{{ csrf_token() }}' // Ajout du token CSRF pour sécurité
        },
        success: function(response) {
            console.log("Réponse:", response);  // Log de la réponse serveur pour voir ce qui arrive
            if (response.success) {
                alert(response.success); // Affiche un message de succès
                table.setData("/plancomptable/data"); // Actualise le tableau
                $("#planComptableModalAdd").modal("hide"); // Ferme le modal
                $("#planComptableFormAdd")[0].reset(); // Réinitialise le formulaire
            } else {
                alert("Échec de l'ajout du plan comptable.");
            }
        },
        error: function(xhr, status, error) {
            console.log("Erreur:", xhr.responseText);  // Affiche la réponse du serveur
            alert("Erreur lors de l'enregistrement des données !");
        }
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
    height: "800px",
    layout: "fitColumns",
    selectable: true,
    rowSelection: true, // Activer la sélection des lignes

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

