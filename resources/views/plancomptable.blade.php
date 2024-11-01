<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
   

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des comptes</title>
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
  </style>

</head>

@extends('layouts.user_type.auth')

@section('content')

<div class="container mt-5">
    <h3>Liste des Plans Comptables</h3>
    <button class="btn btn-primary" id="addPlanComptableBtn" data-toggle="modal" data-target="#planComptableModalAdd">Ajouter</button>
    <button class="btn btn-primary" id="importPlanComptableBtn" data-toggle="modal" data-target="#importModal">Importer</button>
    <button id="vider-plan-comptable" class="btn btn-danger">Vider</button>
    <div id="plan-comptable-table"></div>
</div>

<!-- Formulaire d'importation Excel -->
<!-- Modal d'importation -->
<!-- Modal d'importation -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Importation des Plans Comptables</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importForm" action="{{ route('plancomptable.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="file">Fichier Excel</label>
                        <input type="file" class="form-control" name="file" id="file" required>
                    </div>
                    <div class="form-group">
                        <label for="colonne_compte">Colonne Compte (ex: 1 pour la première colonne)</label>
                        <input type="number" class="form-control" name="colonne_compte" id="colonne_compte" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="colonne_intitule">Colonne Intitulé (ex: 2 pour la deuxième colonne)</label>
                        <input type="number" class="form-control" name="colonne_intitule" id="colonne_intitule" required min="1">
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
                    <input type="hidden" id="planComptableId" value="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="compte">Compte</label>
                                <input type="text" class="form-control" id="compte" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="intitule">Intitulé</label>
                                <input type="text" class="form-control" id="intitule" required>
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
                <h5 class="modal-title" id="planComptableModalLabel">Modifier</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="planComptableFormEdit">
                    <input type="hidden" id="editPlanComptableId" value="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editCompte">Compte</label>
                                <input type="text" class="form-control" id="editCompte" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editIntitule">Intitulé</label>
                                <input type="text" class="form-control" id="editIntitule" required>
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

// Fonction pour gérer la navigation entre les champs avec la touche Entrée
function setupEnterNavigation(formId) {
    document.getElementById(formId).addEventListener('keypress', function(event) {
        // Vérifie si la touche appuyée est "Entrée"
        if (event.key === 'Enter') {
            event.preventDefault(); // Empêche le comportement par défaut du bouton Entrée
            
            // Récupère tous les éléments INPUT et SELECT du formulaire
            const inputs = Array.from(this.elements).filter(el => el.tagName === 'INPUT' || el.tagName === 'SELECT');
            
            // Trouve l'index de l'élément actuellement actif
            const currentIndex = inputs.indexOf(document.activeElement);
            
            // Si l'élément actuel n'est pas le dernier, passe au champ suivant
            if (currentIndex < inputs.length - 1) {
                inputs[currentIndex + 1].focus(); // Passe au champ suivant
            } else {
                // Si c'est le dernier champ, vous pouvez éventuellement soumettre le formulaire ici
                // this.submit(); // Décommentez cette ligne si vous voulez soumettre le formulaire après le dernier champ
            }
        }
    });
}


    // Initialiser le tableau
    var table = new Tabulator("#plan-comptable-table", {
        ajaxURL: "/plancomptable/data", // URL pour récupérer les données
        height: "600px", // Hauteur du tableau pour activer le défilement vertical
        layout: "fitColumns",
        columns: [
            {title: "Compte", field: "compte", editor: "input", headerFilter: "input"},
            {title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input"},
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
                    if (e.target.classList.contains('edit-icon')) {
                        var rowData = cell.getRow().getData();
                        editPlanComptable(rowData);
                    } else if (e.target.classList.contains('delete-icon')) {
                        var rowData = cell.getRow().getData();
                        deletePlanComptable(rowData.id);
                    }
                },
            }
        ],
    });


// Fonction pour vider le tableau et les données de la base de données
$('#vider-plan-comptable').click(function() {
        if (confirm('Êtes-vous sûr de vouloir vider le plan comptable ?')) {
            $.ajax({
                url: '/plancomptable/vider', // L'URL de la route
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}', // Ajoutez le token CSRF pour la sécurité
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message); // Affiche le message de succès
                        table.setData(); // Recharge le tableau pour refléter les modifications
                    }
                },
                error: function(xhr) {
                    alert('Erreur lors de la suppression des données : ' + xhr.responseJSON.message);
                }
            });
        }
    });


    // Fonction pour gérer l'ajout des plans comptables
    $("#planComptableFormAdd").on("submit", function(e) {
        e.preventDefault();

        $.ajax({
            url: "/plancomptable",
            type: "POST",
            data: {
                compte: $("#compte").val(),
                intitule: $("#intitule").val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                table.setData("/plancomptable/data");
                $("#planComptableModalAdd").modal("hide");
                $("#planComptableFormAdd")[0].reset();
              
            },
            error: function(xhr) {
                alert("Erreur lors de l'enregistrement des données !");
            }
        });
        
    });

    // Fonction pour gérer la modification des plans comptables
    $("#planComptableFormEdit").on("submit", function(e) {
        e.preventDefault();

        var planComptableId = $("#editPlanComptableId").val();
        $.ajax({
            url: "/plancomptable/" + planComptableId,
            type: "PUT",
            data: {
                compte: $("#editCompte").val(),
                intitule: $("#editIntitule").val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                table.setData("/plancomptable/data");
                $("#planComptableModalEdit").modal("hide");
                
                $("#planComptableFormEdit")[0].reset();
                $("#editPlanComptableId").val("");
            },
            error: function(xhr) {
                alert("Erreur lors de l'enregistrement des données !");
            }
        });
    });

    // Fonction pour remplir le formulaire pour la modification
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

$("#importForm").on("submit", function(e) {
    e.preventDefault();

    $.ajax({
        url: "{{ route('plancomptable.import') }}",
        type: "POST",
        data: new FormData(this), // Envoi du formulaire avec le fichier
        processData: false,
        contentType: false,
        success: function(response) {
            table.setData("/plancomptable/data"); // Recharger les données du tableau
            $("#importModal").modal("hide");
            alert(response.message); // Affiche le message de succès
        },
        error: function(xhr) {
            let errorMessage = 'Erreur lors de l\'importation des données !';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message; // Message d'erreur du backend
            }
            alert(errorMessage);
        }
    });
});


//excel
// Ouvrir le modal d'importation
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
        
        compteSelect.innerHTML = ''; // Vider les options existantes
        intituleSelect.innerHTML = ''; // Vider les options existantes

        headers.forEach((header, index) => {
            // Ajouter l'option pour le champ "compte"
            if (header.toLowerCase().includes('compte')) { // Vérifier si le header contient "compte"
                const optionCompte = new Option(header, index);
                compteSelect.add(optionCompte);
            }

            // Ajouter l'option pour le champ "intitule"
            if (header.toLowerCase().includes('intitule')) { // Vérifier si le header contient "intitule"
                const optionIntitule = new Option(header, index);
                intituleSelect.add(optionIntitule);
            }
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

