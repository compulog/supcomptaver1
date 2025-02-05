@extends('layouts.user_type.auth')

@section('content')

<!-- Import des fichiers CSS et JS de Tabulator -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.9/css/tabulator.min.css" rel="stylesheet">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.9/js/tabulator.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<!-- Ajouter le CDN de SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script><!-- Styles personnalisés -->
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #e9ecef;
        margin: 0;
        padding: 20px;
    }

    h2 {
        text-align: center;
        color: #343a40;
        margin-bottom: 20px;
    }

    .form-group {
        margin: 20px 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .form-group label {
        margin-right: 10px;
        font-weight: bold;
        color: #495057;
    }

    .form-group select, .form-group input {
        padding: 3px;
        margin: 0 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        transition: border-color 0.3s;
    }

    .form-group select:focus, .form-group input:focus {
        border-color: #80bdff;
        outline: none;
    }

    #example-table {
        margin: 20px 0;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background-color: #fff;
    }

    .total-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 20px 0;
    }

    .total-container label {
        font-weight: bold;
        color: #495057;
    }

    .total-container input {
        padding: 3px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        width: 150px;
    }

    .modal-header {
        background-color: #cb0c9f;
        color: white;
    }

    .modal-footer .btn {
        background-color: #cb0c9f;
        color: white;
    }

    .modal-footer .btn-secondary {
        background-color: #ced4da;
    }

    .modal-footer .btn:hover {
        opacity: 0.9;
    }

    .btn-close {
        color: white;
    }

    .btn-close:hover {
        opacity: 0.7;
    }

    .text-warning {
        color: #ffc107;
    }
    .hidden {
    display: none;
}
#cloturer-button{
    background-color: green;
}


</style>

<!-- Navigation -->
<nav>
    <a href="{{ route('exercices.show', ['societe_id' => session()->get('societeId')]) }}">Tableau De Board</a>
    ➢
    <a href="{{ route('caisse.view') }}">Caisse</a>
    ➢
    <a href="">Etat de caisse</a>
</nav>
<center><h5>ETAT DE CAISSE MENSUELLE</h5></center>
<!-- Conteneur pour les sélecteurs -->
<div class="form-group" style="display: flex; align-items: center; margin-left: -500px;">
    <!-- Sélecteur de code journal -->
    <label for="journal-select" style="margin-right: 10px;">Code Journal :</label>
<select id="journal-select" style="width: 150px; height: 31px; margin-right: 20px;">
    @foreach ($journauxCaisse as $journal)
        <option value="{{ $journal->code_journal }}">{{ $journal->code_journal }}</option>
    @endforeach
    <!-- Vous pouvez également ajouter d'autres options ici si nécessaire -->
</select>


    <!-- Sélecteur de mois et d'année -->
    <label for="month-select" style="margin-right: 10px;">Période :</label>
    <select id="month-select" style="margin-right: 10px;">
        <option value="01">Janvier {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
        <option value="02">Février {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
        <option value="03">Mars {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
        <option value="04">Avril {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
        <option value="05">Mai {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
        <option value="06">Juin {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
        <option value="07">Juillet {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
        <option value="08">Août {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
        <option value="09">Septembre {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
        <option value="10">Octobre {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
        <option value="11">Novembre {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
        <option value="12">Décembre {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}</option>
    </select>

    <input type="text" id="year-select" value="{{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}" readonly  style="display: none;">
    <i id="export-excel-icon" class="fas fa-file-excel" title="Exporter en Excel" style="cursor: pointer; font-size: 17px; color: green;margin-right:3px;"></i>
    <i class="fas fa-trash-alt" id="deleteAllIcon" title="Supprimer toutes les lignes sélectionnées" style="cursor: pointer;" onclick="deleteSelectedRows()"></i>
    <i class="fa fa-share" aria-hidden="true" title="transférer" style="margin-left:3px;"></i>

</div>

<button id="cloturer-button" class="btn btn-primary" style="margin-left:89%;margin-top:-81px;height:31px;border-radius:4px;font-size:10px;">Clôturer</button>
<!-- Solde initial à afficher en fonction du mois et de l'année choisis -->
<div class="form-group" style="margin-left:850px;">
    <label for="initial-balance">Solde initial :</label>
    <input type="number" id="initial-balance" readonly>
</div>


<!-- <i class="fa fa-trash" id="delete-selected"></i> -->

<script>
// Passer les soldes mensuels récupérés depuis Laravel à JavaScript
var soldesMensuels = @json($soldesMensuels);
console.log(soldesMensuels);  // Vérifiez les données reçues

function filterSoldeInitial(month, year, journalCode) {
    var soldeMensuel = soldesMensuels.find(function(solde) {
        var moisComparaison = parseInt(solde.mois).toString().padStart(2, '0');
        var anneeComparaison = parseInt(solde.annee).toString();
        return moisComparaison === month && anneeComparaison === year && solde.code_journal === journalCode; // Filtrer par code journal
    });

    if (soldeMensuel) {
        // Vérifiez si le solde est clôturé
        if (soldeMensuel.cloturer === 1) {
            Swal.fire({
                title: 'Alerte',
                text: 'Le solde pour ce mois est déjà clôturé.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            // Désactiver le bouton "Clôturer"
            document.getElementById('cloturer-button').disabled = true;
        } else {
            // Réactiver le bouton "Clôturer" si le mois n'est pas clôturé
            document.getElementById('cloturer-button').disabled = false;
        }

        // Afficher le solde initial
        document.getElementById('initial-balance').value = soldeMensuel.solde_initial;
    } else {
        document.getElementById('initial-balance').value = 0;
        // Réactiver le bouton "Clôturer" si aucun solde n'est trouvé
        document.getElementById('cloturer-button').disabled = false;
    }
    updateShareIconVisibility();

    document.getElementById('initial-balance').readOnly = false;
    saveData();
}
// Écoutez le changement de sélection du mois
 
document.getElementById('month-select').addEventListener('change', function() {
    var selectedMonth = this.value;
    var selectedYear = document.getElementById('year-select').value;
    var selectedJournalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné
    filterSoldeInitial(selectedMonth, selectedYear, selectedJournalCode); // Appeler la fonction avec le code journal
    if (selectedMonth !== "01") { // Si le mois n'est pas janvier
        document.getElementById('initial-balance').disabled = true; // Désactiver l'input
    } else {
        document.getElementById('initial-balance').disabled = false; // Activer l'input
    }
});

 
</script>
<div id="example-table"></div>
<input type="hidden" id="total-revenue" placeholder="Total recette">
     <input type="hidden" id="total-expense" placeholder="Total dépense"> 
<!-- Total recette, dépense et solde final -->
<div style="margin-left:900px;">
     
    <label for="final-balance">Solde final :</label>
    <input type="number" id="final-balance" placeholder="Solde final" style="border-radius:4px;border:green;">
</div>

<script>
document.getElementById('cloturer-button').addEventListener('click', function() {
    var mois = $('#month-select').val();
    var annee = $('input[type="text"]').val();
    var journalCode = document.getElementById('journal-select').value;

    console.log("Clôturer le solde pour :", { mois, annee, journalCode });

    $.ajax({
        url: '/cloturer-solde', 
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            mois: mois,
            annee: annee,
            journal_code: journalCode
        },
        success: function(response) {
            console.log("Réponse du serveur :", response);
            alert('Le solde a été clôturé avec succès !');
        },
        error: function(xhr, status, error) {
            console.error("Erreur lors de la clôture :", error);
            alert('Erreur lors de la clôture du solde.');
        }
    });
});


document.getElementById('export-excel-icon').addEventListener('click', exportToExcel);


    var transactions = @json($transactions);
 
    function filterTransactions(month, year, journalCode) {
    return transactions.filter(function(transaction) {
        var transactionDate = new Date(transaction.date);
        return transactionDate.getMonth() + 1 === parseInt(month) &&
               transactionDate.getFullYear() === parseInt(year) &&
               transaction.code_journal === journalCode; // Filtrer par code journal
    });
}

function updateTableData(month, year) {
    var journalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné
    var filteredTransactions = filterTransactions(month, year, journalCode); // Passer le code journal
    var tableData = filteredTransactions.map(function(transaction) {
        return {
            id: transaction.id,
            day: new Date(transaction.date).getDate(),
            ref: transaction.reference,
            libelle: transaction.libelle,
            recette: transaction.recette,
            depense: transaction.depense
        };
    });

    // Vérifiez si le mois est clôturé
    if (!isMonthClosed(month, year, journalCode)) {
        // Si le mois n'est pas clôturé, ajoutez une ligne vide
        var emptyRow = {day: "", ref: "", libelle: "", recette: "", depense: ""};
        tableData.push(emptyRow);
    }

    table.replaceData(tableData);
    updateTotals(month, year);

    // Vérifiez si le mois est clôturé
    if (isMonthClosed(month, year, journalCode)) {
        
        // Désactiver l'édition des cellules
        table.getColumns().forEach(function(column) {
            column.getDefinition().editor = false; // Désactiver l'éditeur
        });
        table.setEditable(false); // Désactiver l'édition du tableau
        alert('Le mois sélectionné est déjà clôturé. Vous ne pouvez pas modifier les données.');
    } else {
        // Réactiver l'édition si le mois n'est pas clôturé
        table.getColumns().forEach(function(column) {
            column.getDefinition().editor = "input"; // Réactiver l'éditeur
        });
        table.setEditable(true); // Réactiver l'édition du tableau
    }
}

document.getElementById('journal-select').addEventListener('change', function() {
    var selectedMonth = document.getElementById('month-select').value;
    var selectedYear = document.getElementById('year-select').value;
    var selectedJournalCode = this.value; // Récupérer le code journal sélectionné
    filterSoldeInitial(selectedMonth, selectedYear, selectedJournalCode); // Appeler la fonction avec le code journal
    updateTableData(selectedMonth, selectedYear); // Mettre à jour le tableau avec le nouveau code journal
});
document.addEventListener('DOMContentLoaded', function() {
    var selectedMonth = document.getElementById('month-select').value;
    var selectedYear = document.getElementById('year-select').value;
    var selectedJournalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné
    filterSoldeInitial(selectedMonth, selectedYear, selectedJournalCode); // Appeler la fonction avec le code journal
// Mettre le focus sur le sélecteur de code journal au chargement de la page
document.getElementById('journal-select').focus();




});

var table = new Tabulator("#example-table", {
    height: 300,
    layout: "fitColumns",
    columns: [
        {title: "Jour", field: "day", editor: "input", editorPlaceholder: "Entrez le jour", width: 100},
        {title: "N° Référence", field: "ref", editor: "input", editorPlaceholder: "Entrez le N° Référence", width: 200},
        {title: "Libellé", field: "libelle", editor: "input", editorPlaceholder: "Entrez le libellé", width: 382},
        {title: "Recette", field: "recette", editor: "number", editorPlaceholder: "Entrez la recette", width: 200, formatter: "money", bottomCalc: "sum"},
        {title: "Dépense", field: "depense", editor: "number", editorPlaceholder: "Entrez la dépense", width: 200, formatter: "money", bottomCalc: "sum"},
        { 
            title: `
                <i class="fas fa-square" id="selectAllIcon" title="Sélectionner tout" style="cursor: pointer;" onclick="selectAllRows()"></i>
            `,
            field: "actions", 
            width: 100, 
            formatter: function(cell, formatterParams, onRendered) {
                var rowData = cell.getRow().getData();

                // Créer un conteneur pour les icônes et la case à cocher
                var actionContainer = document.createElement("div");
                actionContainer.style.display = "flex";
                actionContainer.style.alignItems = "center";

                // Créer une case à cocher pour la ligne
                var checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.style.cursor = "pointer";

                // Écouter l'événement de clic sur la case à cocher
                checkbox.addEventListener('change', function() {
                    if (checkbox.checked) {
                        // Sélectionner la ligne si la case est cochée
                        cell.getRow().select();
                    } else {
                        // Désélectionner la ligne si la case est décochée
                        cell.getRow().deselect();
                    }
                });

                // Vérifier l'état initial de la ligne (si elle est déjà sélectionnée)
                checkbox.checked = cell.getRow().isSelected();

                actionContainer.appendChild(checkbox);

              
                onRendered(function() {
                    cell.getElement().appendChild(actionContainer);
                });

                return ""; // Retourner une chaîne vide car les icônes ont été ajoutées manuellement
            }
        },
    ],
    data: [],
    selectable: true,  // Permet la sélection de lignes
     cellEdited: function(cell) {
        updateTotals($('#month-select').val(), $('input[type="text"]').val());
        saveData();
    }
    
});

// Fonction pour sélectionner toutes les lignes et cocher toutes les cases
function selectAllRows() {
    // Sélectionner toutes les lignes
    table.selectRow();
    
    // Cocher toutes les cases à cocher
    table.getRows().forEach(function(row) {
        var checkbox = row.getCell("actions").getElement().querySelector("input[type='checkbox']");
        if (checkbox) {
            checkbox.checked = true; // Cocher la case
        }
    });
}



    $(document).on('click', '.edit-etat-caisse', function() {
        var caisseId = $(this).data('id');
        console.log("ID de la transaction:", caisseId);

        if (!caisseId) {
            console.error("L'ID de la transaction est manquant");
            return;
        }

        $.ajax({
            url: '/etat-caisse/' + caisseId + '/edit',
            method: 'GET',
            success: function(data) {
                $('#etat_de_caisse [name="day"]').val(new Date(data.date).getDate());
                $('#etat_de_caisse [name="Nreference"]').val(data.reference);
                $('#etat_de_caisse [name="Libellé"]').val(data.libelle);
                $('#etat_de_caisse [name="Recette"]').val(data.recette);
                $('#etat_de_caisse [name="Depense"]').val(data.depense);
                $('#etat_de_caisse').attr('action', '/update-transaction/' + caisseId);
                $('#editcaisseModal').modal('show');
            },
            error: function(xhr) {
                console.error('Erreur lors de la récupération des données :', xhr);
                alert('Erreur lors de la récupération des données de l\'état de caisse.');
            }
        });
    });


    function deleteTransaction(transactionId) {
    var mois = $('#month-select').val();
    var annee = $('input[type="text"]').val();
    var journalCode = document.getElementById('journal-select').value;

    // Vérifiez si le mois est clôturé
    if (isMonthClosed(mois, annee, journalCode)) {
        Swal.fire({
            title: 'Alerte',
            text: 'Le mois est déjà clôturé. Vous ne pouvez pas supprimer des transactions.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return; // Sortir de la fonction si le mois est clôturé
    }

      $.ajax({
            url: '/delete-transaction',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: transactionId
            },
            success: function(response) {
                if (response.success) {
                    console.log("Transaction supprimée avec succès");
                    table.deleteRow(transactionId);
                    updateTotals($('#month-select').val(), $('input[type="text"]').val());
                } else {
                    console.error("Erreur lors de la suppression : " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Erreur lors de la suppression :", error);
            }
        });
    
}



    // Fonction pour sélectionner ou désélectionner toutes les lignes
    function toggleSelectAll() {
        var allRows = table.getRows();
        var allSelected = allRows.every(row => row.getSelected());

        if (allSelected) {
            table.deselectRow();
        } else {
            table.selectRow();
        }
    }

    // Fonction pour supprimer les lignes sélectionnées
    function deleteSelectedRows() {
        var selectedRows = table.getSelectedRows();
        if (selectedRows.length === 0) {
            alert("Aucune ligne sélectionnée !");
            return;
        }

        if (confirm("Êtes-vous sûr de vouloir supprimer les lignes sélectionnées ?")) {
            selectedRows.forEach(function(row) {
                var rowData = row.getData();
                deleteTransaction(rowData.id);
            });
        }
    }

//     $('#example-table').on('keydown', function(e) {
//     if (e.key === "Enter") {
//         var selectedRows = table.getSelectedRows();
//         if (selectedRows.length > 0) {
//             var rowData = selectedRows[0].getData();
//             console.log("Données de la ligne sélectionnée :");
//             console.log("Jour :", rowData.day);
//             console.log("Référence :", rowData.ref);
//             console.log("Libellé :", rowData.libelle);
//             console.log("Recette :", rowData.recette);
//             console.log("Dépense :", rowData.depense);

//             var selectedMonth = $('#month-select').val();
//             var selectedYear = $('input[type="text"]').val();
//             var formattedDate = selectedYear + '-' + selectedMonth + '-' + rowData.day.padStart(2, '0');
//             var journalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné

//             $.ajax({
//                 url: '/save-transaction',
//                 type: "POST",
//                 data: {
//                     _token: $('meta[name="csrf-token"]').attr('content'),
//                     date: formattedDate,
//                     ref: rowData.ref,
//                     libelle: rowData.libelle,
//                     recette: rowData.recette,
//                     depense: rowData.depense,
//                     journal_code: journalCode 
//                 },
//                 success: function(response) {
//                     console.log("Données envoyées avec succès :", response);
//                     location.reload();
//                 },
//                 error: function(xhr, status, error) {
//                     console.error("Erreur lors de l'envoi des données :", error);
//                     console.log(xhr.responseText);
//                 }
//             });
//         } else {
//             console.log("Aucune ligne sélectionnée !");
//         }
//     }
// });

    function saveData() {
        var mois = $('#month-select').val();
        var soldeInitial = parseFloat($('#initial-balance').val() );
        var totalRecette = parseFloat($('#total-revenue').val() );
        var totalDepense = parseFloat($('#total-expense').val() );
        var soldeFinal = parseFloat($('#final-balance').val() );
        var year = $('input[type="text"]').val();
        var date = new Date(year + '-' + mois + '-01');
        var journalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné
        console.log(journalCode);
        $.ajax({
            url: '/save-solde',
            method: 'POST',
            data: {
                mois: mois,
                annee: year,
                solde_initial: soldeInitial,
                total_recette: totalRecette,
                total_depense: totalDepense,
                solde_final: soldeFinal,
                journal_code: journalCode ,

                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }
    function updateTotals(month, year) {
    var totalRecette = 0;
    var totalDepense = 0;
    var filteredTransactions = filterTransactions(month, year, document.getElementById('journal-select').value);

    filteredTransactions.forEach(function(row) {
        totalRecette += parseFloat(row.recette || 0);
        totalDepense += parseFloat(row.depense || 0);
    });

    console.log("Total Recette:", totalRecette); // Vérifiez le total des recettes
    console.log("Total Dépense:", totalDepense); // Vérifiez le total des dépenses

    // Mettre à jour les éléments du pied de page pour afficher les totaux
    $('#total-revenue-footer').text(totalRecette.toFixed(2));
    $('#total-expense-footer').text(totalDepense.toFixed(2));

    // Mettre à jour les champs d'entrée pour les totaux
    $('#total-revenue').val(totalRecette.toFixed(2));
    $('#total-expense').val(totalDepense.toFixed(2));

    var soldeInitial = parseFloat($('#initial-balance').val() || 0);
    var soldeFinal = soldeInitial + totalRecette - totalDepense;
    $('#final-balance').val(soldeFinal.toFixed(2));

    console.log("Solde Final:", soldeFinal); // Vérifiez le solde final

    // Changer la couleur de fond en fonction du solde final
    if (soldeFinal < 0) {
        $('#final-balance').css('background-color', 'red');
    } else {
        $('#final-balance').css('background-color', '#52b438');
    }
}

    $('#initial-balance').on('input', function() {
        updateTotals($('#month-select').val(), $('input[type="text"]').val());
        saveData();
    });

    $(document).ready(function() {
        var currentMonth = $('#month-select').val();
        var currentYear = $('input[type="text"]').val();
        updateTableData(currentMonth, currentYear);
    });

    $('#month-select, #year-select').on('change', function() {
        var currentMonth = $('#month-select').val();
        var currentYear = $('input[type="text"]').val();
        updateTableData(currentMonth, currentYear);
    });
// Variable pour stocker la réponse de l'utilisateur
let userResponse = null;

// Fonction pour vérifier si la référence existe déjà dans le tableau
function checkReferenceExists(reference, currentRowId) {
    const existingTransaction = transactions.find(function(transaction) {
        return transaction.reference === reference && transaction.id !== currentRowId;
    });
    return existingTransaction ? {
        exists: true,
        month: new Date(existingTransaction.date).getMonth() + 1, // Récupérer le mois (1-12)
        year: new Date(existingTransaction.date).getFullYear() // Récupérer l'année
    } : { exists: false };
}

// Événement cellEdited pour vérifier la référence
table.on("cellEdited", function(cell) {
    var field = cell.getField();
    var newValue = cell.getValue();
    var rowData = cell.getRow().getData();

    // Vérifiez si le champ modifié est la référence
    if (field === "ref") {
        var referenceCheck = checkReferenceExists(newValue, rowData.id);
        if (referenceCheck.exists) {
            // Afficher une alerte avec SweetAlert2
            var period = referenceCheck.month + '/' + referenceCheck.year; // Format de la période
            Swal.fire({
                title: `La référence N° "${newValue}" existe déjà dans la période ${period}`,
                text: "Voulez-vous continuer ou annuler ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui',
                cancelButtonText: 'Non'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si l'utilisateur choisit "Oui", accepter la modification et conserver la nouvelle valeur
                    userResponse = 'continue'; // Stocker la réponse
                } else {
                    // Si l'utilisateur choisit "Non", réinitialiser la valeur de la cellule
                    cell.setValue(rowData.ref); // Réinitialiser à l'ancienne valeur
                    userResponse = 'cancel'; // Stocker la réponse
                }
            });
        }
    }

    // Sélectionner la ligne en cours après modification
    table.deselectRow(); // Désélectionner toutes les lignes
    cell.getRow().select(); // Sélectionner la ligne en cours
    // Mettre à jour les totaux après modification
    updateTotals($('#month-select').val(), $('input[type="text"]').val());
});
// Événement pour enregistrer les données lors de l'appui sur "Entrée"
$('#example-table').on('keydown', function(e) {
    if (e.key === "Enter") {
        var selectedRows = table.getSelectedRows();
        if (selectedRows.length > 0) {
            var rowData = selectedRows[0].getData();
            var selectedMonth = $('#month-select').val();
            var selectedYear = $('input[type="text"]').val();
            var formattedDate = selectedYear + '-' + selectedMonth + '-' + rowData.day.padStart(2, '0');
            var journalCode = document.getElementById('journal-select').value; // Récupérer le code journal sélectionné

            // Vérifier si userResponse est vide et le remplacer par 0
            var userResponseToSend = userResponse ? userResponse : 0;

            // Vérification des valeurs vides
            if (!rowData.day) {
                alert("Le jour ne peut pas être vide.");
                return;
            }
            if (!rowData.depense && !rowData.recette) {
                alert("Vous devez entrer soit une dépense soit une recette.");
                return;
            }
            if (isMonthClosed(selectedMonth, selectedYear, journalCode)) {
                alert("Le mois est déjà clôturé. Vous ne pouvez pas modifier des transactions.");
                return; // Sortir de la fonction si le mois est clôturé
            } else {
                $.ajax({
                    url: '/save-transaction',
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        date: formattedDate,
                        ref: rowData.ref,
                        libelle: rowData.libelle,
                        recette: rowData.recette,
                        depense: rowData.depense,
                        journal_code: journalCode,
                        user_response: userResponseToSend 
                    },
                    success: function(response) {
                        updateTotals($('#month-select').val(), $('input[type="text"]').val());
                        saveData();
                     
                        console.log("Données envoyées avec succès :", response);
                        // location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error("Erreur lors de l'envoi des données :", error);
                        console.log(xhr.responseText);
                    }
                });
            }
        } else {
            console.log("Aucune ligne sélectionnée !");
        }
      
        location.reload();
      
    }
});


function exportToExcel() {
    // Récupérer les données du tableau
    const tableData = table.getData();
    
    // Créer un tableau pour les en-têtes et les données
    const headers = ["Jour", "N° Référence", "Libellé", "Recette", "Dépense"];
    const data = [headers];

    // Ajouter les données du tableau
    tableData.forEach(row => {
        data.push([row.day, row.ref, row.libelle, row.recette, row.depense]);
    });

    // Créer un nouveau classeur
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(data);

    // Ajouter la feuille au classeur
    XLSX.utils.book_append_sheet(wb, ws, "État de Caisse");

    // Exporter le fichier Excel
    XLSX.writeFile(wb, "etat_caisse_mensuelle.xlsx");
}





function isMonthClosed(month, year, journalCode) {
    var soldeMensuel = soldesMensuels.find(function(solde) {
        var moisComparaison = parseInt(solde.mois).toString().padStart(2, '0');
        var anneeComparaison = parseInt(solde.annee).toString();
        return moisComparaison === month && anneeComparaison === year && solde.code_journal === journalCode; // Inclure le code journal
    });
    return soldeMensuel && soldeMensuel.cloturer === 1;
}


// Ajoutez cette fonction pour mettre à jour la visibilité de l'icône
function updateShareIconVisibility() {
    var cloturerButton = document.getElementById('cloturer-button');
    var shareIcon = document.querySelector('.fa-share');  

    if (cloturerButton.disabled) {
        shareIcon.classList.remove('hidden'); 
     } else {
        shareIcon.classList.add('hidden');     }
}

// Appelez cette fonction chaque fois que vous modifiez l'état du bouton "Clôturer"
document.getElementById('cloturer-button').addEventListener('click', function() {
 
     updateShareIconVisibility();
});
 
 
</script>
 
@endsection