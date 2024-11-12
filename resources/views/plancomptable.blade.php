<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des comptes</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<style>
    .tabulator {
    border: 1px solid #ccc; /* Ajouter une bordure */
    overflow: auto; /* Activer le défilement si nécessaire */
}

/* Ajuste le champ de recherche dans le header */
.tabulator .tabulator-header input[type="search"] {
    height: 20px; /* Diminue la hauteur */
    padding: 1px 3px; /* Ajuste le padding interne */
    font-size: 0.8em; /* Diminue légèrement la police */}
    .btn-custom-gradient {
    background-image: linear-gradient(to right, #344767, #31477a); /* Dégradé de gauche à droite */
    color: white !important; /* Couleur du texte en blanc */
    border: none; /* Pas de bordure */
    transition: background-color 0.3s ease; /* Transition douce pour le survol */
}

.btn-custom-gradient:hover {
    background-image: linear-gradient(to right, #536fb2, #344767); /* Inverser le dégradé au survol */
}

  </style>

</head>
<body>
@extends('layouts.user_type.auth')

@section('content')
{{-- Vérifiez si une société est sélectionnée --}}
@if(isset($societe))
    <div class="alert alert-info">
        <strong>Société sélectionnée :</strong> {{ $societe->nom }} (ID : {{ $societe->id }})
    </div>
@else
    <div class="alert alert-warning">
        Aucune société sélectionnée. Veuillez en sélectionner une.
    </div>
@endif
<div class="container mt-5">
    <h3>Liste des Plans Comptables</h3>
    <button class="btn btn-custom-gradient" id="addPlanComptableBtn" data-toggle="modal" data-target="#planComptableModalAdd">Ajouter</button>
    <button class="btn btn-custom-gradient" id="importPlanComptableBtn" data-toggle="modal" data-target="#importModal">Importer</button>
 

    <a href="{{ route('plan.comptable.excel') }}" class="btn btn-custom-gradient">Exporter en Excel</a>
    <a href="{{ route('plan.comptable.pdf') }}" class="btn btn-custom-gradient">Exporter en PDF</a>
    
    <div id="plan-comptable-table"></div>
</div>


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
                    <input type="hidden" name="societe_id" id="societe_id" value="3"> <!-- Exemple pour la société 3 -->

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
    ajaxURL: "/plancomptable/data", // Remplacez par votre route pour récupérer les données
    height: "600px",
    layout: "fitColumns",
    columns: [
        { title: "Compte", field: "compte", editor: "input", headerFilter: "input" },
        { title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input" },
        {
            title: `Actions`,
         
            field: "action-icons",
            formatter: function() {
                return `
                    <input type="checkbox" class="row-select-checkbox" title="Sélectionner" />
                    <i class='fas fa-edit text-primary edit-icon' style='cursor: pointer;'></i>
                    <i class='fas fa-trash-alt text-danger delete-icon' style='cursor: pointer;'></i>
                `;
            },
            cellClick: function(e, cell) {
                var row = cell.getRow();
                if (e.target.classList.contains('row-select-checkbox')) {
                    // Si la case à cocher est activée/désactivée
                    if (e.target.checked) {
                        row.select();
                    } else {
                        row.deselect();
                    }
                    updateSelectAllCheckbox();
                } else if (e.target.classList.contains('edit-icon')) {
                    var rowData = row.getData();
                    editPlanComptable(rowData);
                } else if (e.target.classList.contains('delete-icon')) {
                    var rowData = row.getData();
                    deletePlanComptable(rowData.id);
                }
            },
            hozAlign: "center",
            headerSort: false,
        }
    ],
    rowSelected: function(row) {
        row.getElement().classList.add("bg-light");
        updateSelectAllCheckbox();
    },
    rowDeselected: function(row) {
        row.getElement().classList.remove("bg-light");
        updateSelectAllCheckbox();
    }
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
        const headers = XLSX.utils.sheet_to_json(worksheet, { header: 1 })[0]; // Obtenir les en-têtes de colonnes

        // Remplir les sélecteurs de colonnes
        const compteSelect = document.querySelector('select[name="colonne_compte"]');
        const intituleSelect = document.querySelector('select[name="colonne_intitule"]');
        
        compteSelect.innerHTML = '';
        intituleSelect.innerHTML = '';
        
        headers.forEach((header, index) => {
            const optionCompte = new Option(header, index);
            const optionIntitule = new Option(header, index);
            compteSelect.add(optionCompte);
            intituleSelect.add(optionIntitule);
        });
    };

    reader.readAsArrayBuffer(file);
});

</script>






<!-- Bootstrap JS (optionnel) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
@endsection

