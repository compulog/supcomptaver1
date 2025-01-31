@extends('layouts.user_type.auth')

@section('content')

<!-- Import des fichiers CSS et JS de Tabulator -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.9/css/tabulator.min.css" rel="stylesheet">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.9/js/tabulator.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<!-- Styles personnalisés -->
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

<!-- Sélecteur de mois et d'année -->
<div class="form-group" style="margin-left:-700px;">
    <label for="month-select">Période :</label>
    <select id="month-select">
        <option value="01">Janvier</option>
        <option value="02">Février</option>
        <option value="03">Mars</option>
        <option value="04">Avril</option>
        <option value="05">Mai</option>
        <option value="06">Juin</option>
        <option value="07">Juillet</option>
        <option value="08">Août</option>
        <option value="09">Septembre</option>
        <option value="10">Octobre</option>
        <option value="11">Novembre</option>
        <option value="12">Décembre</option>
    </select>

    <input type="text" id="year-select" value="{{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}" readonly style="margin-left:-30px;border-radius:  0 4px 4px 0 ;border-left:none;width:70px;height:31px;">
</div>

<!-- Solde initial à afficher en fonction du mois et de l'année choisis -->
<div class="form-group" style="margin-left:850px;">
    <label for="initial-balance">Solde initial :</label>
    <input type="number" id="initial-balance" readonly>
</div>

<!-- Modal pour la modification d'état de caisse -->
<div class="modal fade" id="editcaisseModal" tabindex="-1" role="dialog" aria-labelledby="editClientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="etat_de_caisse" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editEtatCaisseModalLabel">Modifier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="day">Jour</label>
                        <input type="number" class="form-control" name="day" required min="1" max="31">
                    </div>
                    <div class="form-group">
                        <label for="Nreference">N° Référence</label>
                        <input type="number" class="form-control" name="Nreference" required>
                    </div>
                    <div class="form-group">
                        <label for="Libellé">Libellé</label>
                        <input type="text" class="form-control" name="Libellé" required>
                    </div>
                    <div class="form-group">
                        <label for="Recette">Recette</label>
                        <input type="number" class="form-control" name="Recette">
                    </div>
                    <div class="form-group">
                        <label for="Depense">Dépense</label>
                        <input type="number" class="form-control" name="Depense">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn btn-secondary me-8">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

  <i class="fa fa-trash" id="delete-selected"></i>

<script>
// Passer les soldes mensuels récupérés depuis Laravel à JavaScript
var soldesMensuels = @json($soldesMensuels);
console.log(soldesMensuels);  // Vérifiez les données reçues

// Fonction pour filtrer le solde initial en fonction du mois et de l'année
function filterSoldeInitial(month, year) {
    if (month === "01") {
        var soldeJanvier = soldesMensuels.find(function(soldeMensuel) {
            var moisComparaison = parseInt(soldeMensuel.mois).toString().padStart(2, '0');
            var anneeComparaison = parseInt(soldeMensuel.annee).toString();
            return moisComparaison === "01" && anneeComparaison === year;
        });

        if (soldeJanvier) {
            document.getElementById('initial-balance').value = soldeJanvier.solde_initial;
        } else {
            document.getElementById('initial-balance').value = 0;
        }

        document.getElementById('initial-balance').readOnly = false;
        return;
    }

    var previousMonth = (parseInt(month) - 1).toString().padStart(2, '0');
    var solde = soldesMensuels.find(function(soldeMensuel) {
        var moisComparaison = parseInt(soldeMensuel.mois).toString().padStart(2, '0');
        var anneeComparaison = parseInt(soldeMensuel.annee).toString();
        return moisComparaison === previousMonth && anneeComparaison === year;
    });

    if (solde) {
        document.getElementById('initial-balance').value = solde.solde_final;
    } else {
        document.getElementById('initial-balance').value = 0;
    }

    document.getElementById('initial-balance').readOnly = false;
}

document.getElementById('month-select').addEventListener('change', function() {
    var selectedMonth = this.value;
    var selectedYear = document.getElementById('year-select').value;
    filterSoldeInitial(selectedMonth, selectedYear);
});

document.addEventListener('DOMContentLoaded', function() {
    var selectedMonth = document.getElementById('month-select').value;
    var selectedYear = document.getElementById('year-select').value;
    filterSoldeInitial(selectedMonth, selectedYear);
});
</script>

<!-- Conteneur pour le tableau -->
<div id="example-table"></div>

<!-- Total recette, dépense et solde final -->
<div class="total-container">
    <label for="total-revenue">Total recette :</label>
    <input type="number" id="total-revenue" placeholder="Total recette">
    <label for="total-expense">Total dépense :</label>
    <input type="number" id="total-expense" placeholder="Total dépense">
    <label for="final-balance">Solde final :</label>
    <input type="number" id="final-balance" placeholder="Solde final">
</div>

<script>
    var transactions = @json($transactions);

    function filterTransactions(month, year) {
        return transactions.filter(function(transaction) {
            var transactionDate = new Date(transaction.date);
            return transactionDate.getMonth() + 1 === parseInt(month) && transactionDate.getFullYear() === parseInt(year);
        });
    }

    function updateTableData(month, year) {
        var filteredTransactions = filterTransactions(month, year);
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

        var emptyRow = {day: "", ref: "", libelle: "", recette: "", depense: ""};
        tableData.push(emptyRow);

        table.replaceData(tableData);
        updateTotals(month, year);
    }

    var table = new Tabulator("#example-table", {
        height: 300,
        layout: "fitColumns",
        columns: [
            {title: "Jour", field: "day", editor: "input", editorPlaceholder: "Entrez le jour", width: 100},
            {title: "N° Référence", field: "ref", editor: "input", editorPlaceholder: "Entrez le N° Référence", width: 200},
            {title: "Libellé", field: "libelle", editor: "input", editorPlaceholder: "Entrez le libellé", width: 382},
            {title: "Recette", field: "recette", editor: "input", editorPlaceholder: "Entrez la recette", width: 200, formatter:"money"},
            {title: "Dépense", field: "depense", editor: "input", editorPlaceholder: "Entrez la dépense", width: 200, formatter:"money"},
            { 
                title: "Actions", 
                field: "actions", 
                width: 100, 
                formatter: function(cell, formatterParams, onRendered) {
                    var rowData = cell.getRow().getData();
                    if (rowData.day === "" && rowData.ref === "" && rowData.libelle === "" && rowData.recette === "" && rowData.depense === "") {
                        return "";
                    }

                    var deleteIcon = document.createElement("i");
                    deleteIcon.classList.add("fas", "fa-trash-alt");
                    deleteIcon.style.cursor = "pointer";

                    deleteIcon.onclick = function() {
                        deleteTransaction(rowData.id);
                    };

                    onRendered(function() {
                        cell.getElement().appendChild(deleteIcon);
                    });

                    return "";
                }
            }
        ],
        
        data: [],
        selectable: true,  // Permet la sélection de lignes
        cellEdited: function(cell) {
            updateTotals($('#month-select').val(), $('input[type="text"]').val());
            saveData();
        }
    });

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
        if (confirm("Êtes-vous sûr de vouloir supprimer cette transaction ?")) {
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
        } else {
            console.log("Suppression annulée.");
        }
        location.reload();
    }

    // Gestionnaire d'événements pour le bouton de suppression
    document.getElementById('delete-selected').addEventListener('click', function() {
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
            location.reload();
    });

    $('#example-table').on('keydown', function(e) {
        if (e.key === "Enter") {
            var selectedRows = table.getSelectedRows();
            if (selectedRows.length > 0) {
                var rowData = selectedRows[0].getData();
                console.log("Données de la ligne sélectionnée :");
                console.log("Jour :", rowData.day);
                console.log("Référence :", rowData.ref);
                console.log("Libellé :", rowData.libelle);
                console.log("Recette :", rowData.recette);
                console.log("Dépense :", rowData.depense);

                var selectedMonth = $('#month-select').val();
                var selectedYear = $('input[type="text"]').val();
                var formattedDate = selectedYear + '-' + selectedMonth + '-' + rowData.day.padStart(2, '0');

                $.ajax({
                    url: '/save-transaction',
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        date: formattedDate,
                        ref: rowData.ref,
                        libelle: rowData.libelle,
                        recette: rowData.recette,
                        depense: rowData.depense
                    },
                    success: function(response) {
                        console.log("Données envoyées avec succès :", response);
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error("Erreur lors de l'envoi des données :", error);
                        console.log(xhr.responseText);
                    }
                });
            } else {
                console.log("Aucune ligne sélectionnée !");
            }
        }
    });

    function saveData() {
        var mois = $('#month-select').val();
        var soldeInitial = parseFloat($('#initial-balance').val() || 0);
        var totalRecette = parseFloat($('#total-revenue').val() || 0);
        var totalDepense = parseFloat($('#total-expense').val() || 0);
        var soldeFinal = parseFloat($('#final-balance').val() || 0);
        var year = $('input[type="text"]').val();
        var date = new Date(year + '-' + mois + '-01');

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
        var filteredTransactions = filterTransactions(month, year);

        filteredTransactions.forEach(function(row) {
            totalRecette += parseFloat(row.recette || 0);
            totalDepense += parseFloat(row.depense || 0);
        });

        $('#total-revenue').val(totalRecette.toFixed(2));
        $('#total-expense').val(totalDepense.toFixed(2));

        var soldeInitial = parseFloat($('#initial-balance').val() || 0);
        var soldeFinal = soldeInitial + totalRecette - totalDepense;
        $('#final-balance').val(soldeFinal.toFixed(2));

        if (soldeFinal < 0) {
            $('#final-balance').css('background-color', 'red');
        } else {
            $('#final-balance').css('background-color', '#52b438');
        }

        if (soldeInitial < 0) {
            $('#initial-balance').css('background-color', 'red');
        } else {
            $('#initial-balance').css('background-color', '#52b438');
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

</script>

@endsection