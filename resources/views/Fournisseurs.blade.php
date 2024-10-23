<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Fournisseurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/tabulator-tables@5.0.7/dist/css/tabulator.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.0.7/dist/js/tabulator.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
 
</head>




@extends('layouts.user_type.auth')

@section('content')
<body>


<div class="container mt-5">
    <h2>Liste des Fournisseurs</h2>
    <button class="btn btn-primary" id="addFournisseurBtn" data-toggle="modal" data-target="#fournisseurModal">Ajouter Fournisseur</button>
    <button class="btn btn-primary" id="addFournisseurBtn" data-toggle="modal" data-target="#importModal">Importer Fournisseur</button>
    <div id="fournisseur-table"></div>

</div>


<!-- Formulaire d'importation Excel -->
<!-- Modal d'importation -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Importation des Fournisseurs</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importForm" action="{{ route('fournisseurs.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="file">Fichier Excel</label>
                        <input type="file" class="form-control" name="file" id="file" required>
                    </div>
                    <div class="form-group">
                        <label for="colonne_compte">Colonne Compte</label>
                        <input type="number" class="form-control" name="colonne_compte" id="colonne_compte" required>
                    </div>
                    <div class="form-group">
                        <label for="colonne_intitule">Colonne Intitulé</label>
                        <input type="number" class="form-control" name="colonne_intitule" id="colonne_intitule" required>
                    </div>
                    <div class="form-group">
                        <label for="colonne_identifiant_fiscal">Colonne Identifiant Fiscal</label>
                        <input type="number" class="form-control" name="colonne_identifiant_fiscal" id="colonne_identifiant_fiscal" required>
                    </div>
                    <div class="form-group">
                        <label for="colonne_ICE">Colonne ICE</label>
                        <input type="number" class="form-control" name="colonne_ICE" id="colonne_ICE" required>
                    </div>
                    <div class="form-group">
                        <label for="colonne_nature_operation">Colonne Nature d'Opération</label>
                        <input type="number" class="form-control" name="colonne_nature_operation" id="colonne_nature_operation" required>
                    </div>
                    <div class="form-group">
                        <label for="colonne_rubrique_tva">Colonne Rubrique TVA</label>
                        <input type="number" class="form-control" name="colonne_rubrique_tva" id="colonne_rubrique_tva" required>
                    </div>
                    <div class="form-group">
                        <label for="colonne_designation">Colonne Désignation</label>
                        <input type="number" class="form-control" name="colonne_designation" id="colonne_designation" required>
                    </div>
                    <div class="form-group">
                        <label for="colonne_contre_partie">Colonne Contre Partie</label>
                        <input type="number" class="form-control" name="colonne_contre_partie" id="colonne_contre_partie" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </form>
            </div>
        </div>
    </div>
</div>





<!-- Modal -->
<div class="modal fade" id="fournisseurModal" tabindex="-1" role="dialog" aria-labelledby="fournisseurModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fournisseurModalLabel">Ajouter/Modifier Fournisseur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="fournisseurForm">
                    <input type="hidden" id="fournisseurId" value="">
                    <div class="form-group">
                        <label for="compte">Compte</label>
                        <input type="text" class="form-control" id="compte" required>
                    </div>
                    <div class="form-group">
                        <label for="intitule">Intitulé</label>
                        <input type="text" class="form-control" id="intitule" required>
                    </div>
                    <div class="form-group">
                        <label for="identifiant_fiscal">Identifiant Fiscal</label>
                        <input type="text" class="form-control" id="identifiant_fiscal" required>
                    </div>
                    <div class="form-group">
                        <label for="ICE">ICE</label>
                        <input type="text" class="form-control" id="ICE" required>
                    </div>
                    <div class="form-group">
                    <label for="nature_operation">Nature de l'opération</label>
    <select id="nature_operation" name="nature_operation" class="form-control">
        <option value="Achat de biens d'équipement">Achat de biens d'équipement</option>
        <option value="Achat de travaux">Achat de travaux</option>
        <option value="Achat de services">Achat de services</option>
    </select>
                    </div>
                    <div class="form-group">
    <label for="rubrique_tva">Rubrique TVA</label>
    <select class="form-control" id="rubrique_tva" required>
        <!-- Les options seront ajoutées par JavaScript -->
    </select>
</div>

                    <div class="form-group">
                        <label for="designation">Désignation</label>
                        <input type="text" class="form-control" id="designation" required>
                    </div>
                    <div class="form-group">
                        <label for="contre_partie">Contre Partie</label>
                        <input type="text" class="form-control" id="contre_partie" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
    
</div>



<script>
  var table = new Tabulator("#fournisseur-table", {
    ajaxURL: "/fournisseurs/data", // URL pour récupérer les données
    layout: "fitColumns",
    minHeight: 80, // Hauteur minimale du tableau
    columns: [
        {title: "Compte", field: "compte", editor: "input", headerFilter: "input", minWidth: 80},
        {title: "Intitulé", field: "intitule", editor: "input", headerFilter: "input", minWidth: 120},
        {title: "Identifiant Fiscal", field: "identifiant_fiscal", editor: "input", headerFilter: "input", minWidth: 50},
        {title: "ICE", field: "ICE", editor: "input", headerFilter: "input", minWidth: 80},
        {title: "Nature de l'opération", field: "nature_operation", editor: "select", headerFilter: "select", headerFilterParams: {
            values: [
                {value: "Achat de biens d'équipement", label: "Achat de biens d'équipement"},
                {value: "Achat de travaux", label: "Achat de travaux"},
                {value: "Achat de services", label: "Achat de services"},
            ],
        }, minWidth: 50},
        {
            title: "Rubrique TVA",
            field: "rubrique_tva",
            editor: "select",
            headerFilter: "select",
            headerFilterParams: function() {
                var options = {};
                $.ajax({
                    url: '/rubriques-tva', // Remplacez par votre URL pour récupérer les rubriques
                    async: false,
                    success: function(data) {
                        data.rubriques.forEach(function(rubrique) {
                            options[rubrique.Nom_racines] = rubrique.Nom_racines;
                        });
                    }
                });
                return options;
            },
            minWidth: 50,
        },
        {title: "Désignation", field: "designation", editor: "input", headerFilter: "input", minWidth: 80},
        {title: "Contre Partie", field: "contre_partie", editor: "input", headerFilter: "input", minWidth: 80},
        {
            title: "Actions", 
            field: "action-icons", 
            formatter: function() {
                return `
                    <button class='btn btn-warning btn-edit' title='Modifier'>
                        <i class='fas fa-edit' style='font-size: 0.8em; line-height:0.5;'></i>
                    </button>
                    <button class='btn btn-danger btn-delete' title='Supprimer'>
                        <i class='fas fa-trash-alt' style='font-size: 0.8em; line-height:0.5;'></i>
                    </button>
                `;
            },
            cellClick: function(e, cell) {
                if (e.target.classList.contains('btn-edit')) {
                    var rowData = cell.getRow().getData();
                    editFournisseur(rowData);
                } else if (e.target.classList.contains('btn-delete')) {
                    var rowData = cell.getRow().getData();
                    deleteFournisseur(rowData.id);
                }
            },
            minWidth: 50,
        }
    ],
});



    // Fonction pour gérer l'ajout et la modification des fournisseurs
    $("#fournisseurForm").on("submit", function(e) {
    e.preventDefault();

    var fournisseurId = $("#fournisseurId").val();
    var url = fournisseurId ? "/fournisseurs/" + fournisseurId : "/fournisseurs";

    $.ajax({
        url: url,
        type: fournisseurId ? "PUT" : "POST",
        data: {
            compte: $("#compte").val(),
            intitule: $("#intitule").val(),
            identifiant_fiscal: $("#identifiant_fiscal").val(),
            ICE: $("#ICE").val(),
            nature_operation: $("#nature_operation").val(),
            rubrique_tva: $("#rubrique_tva").val(), // Récupérer la valeur du select
            designation: $("#designation").val(),
            contre_partie: $("#contre_partie").val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            table.setData("/fournisseurs/data"); // Recharger les données
            $("#fournisseurModal").modal("hide");
            $("#importModal").modal("hide");
            $("#fournisseurForm")[0].reset(); // Réinitialiser le formulaire
            $("#fournisseurId").val(""); // Réinitialiser l'ID
        },
        error: function(xhr) {
            alert("Erreur lors de l'enregistrement des données !");
        }
    });
});


    // Fonction pour remplir le formulaire pour la modification
    function editFournisseur(data) {
        $("#fournisseurId").val(data.id);
        $("#compte").val(data.compte);
        $("#intitule").val(data.intitule);
        $("#identifiant_fiscal").val(data.identifiant_fiscal);
        $("#ICE").val(data.ICE);
        $("#nature_operation").val(data.nature_operation);
        $("#rubrique_tva").val(data.rubrique_tva);
        $("#designation").val(data.designation);
        $("#contre_partie").val(data.contre_partie);
        $("#fournisseurModal").modal("show");
    }


    function remplirRubriquesTva() {
    $.ajax({
        url: '/rubriques-tva?type=Achat', // URL modifiée pour inclure le paramètre type
        type: 'GET',
        success: function(data) {
            var select = $("#rubrique_tva");
            select.empty(); // Vider le select avant d'ajouter les options
            data.rubriques.forEach(function(rubrique) {
                // Ajouter chaque option avec le format souhaité
                select.append(new Option(`${rubrique.Nom_racines} (${rubrique.Num_racines}) - Taux: ${rubrique.Taux}`, rubrique.Num_racines));
            });
        }
    });
}

// Appeler la fonction lorsque le modal s'ouvre
$('#fournisseurModal').on('show.bs.modal', function () {
    remplirRubriquesTva(); // Remplir les rubriques TVA à chaque ouverture du modal
});


// excel

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







    // Fonction pour supprimer un fournisseur
    function deleteFournisseur(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce fournisseur ?")) {
            $.ajax({
                url: "/fournisseurs/" + id,
                type: "DELETE",
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    table.setData("/fournisseurs/data"); // Recharger les données
                },
                error: function(xhr) {
                    alert("Erreur lors de la suppression des données !");
                }
            });
        }
    }
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>

</body>
</html>

@endsection
