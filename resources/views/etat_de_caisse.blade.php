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
        padding: 10px;
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
        padding: 10px;
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
<div class="form-group">
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

    <input type="text" id="year-select" value="{{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}" readonly style="margin-left:-15px;border-radius:  0 4px 4px 0 ;border-left:none;width:70px;height:45px;">
</div>

<!-- Solde initial à afficher en fonction du mois et de l'année choisis -->
<div class="form-group">
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
                        <label for="date">Date</label>
                        <input type="date" class="form-control" name="date" required>
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
                        <input type="number" class="form-control" name="Recette" required>
                    </div>
                    <div class="form-group">
                        <label for="Depense">Dépense</label>
                        <input type="number" class="form-control" name="Depense" required>
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

<script>
// Passer les soldes mensuels récupérés depuis Laravel à JavaScript
var soldesMensuels = @json($soldesMensuels);
console.log(soldesMensuels);  // Vérifiez les données reçues

// Fonction pour filtrer le solde initial en fonction du mois et de l'année
function filterSoldeInitial(month, year) {
    // Si le mois est janvier (01), on cherche son solde initial dans les données
    if (month === "01") {
        var soldeJanvier = soldesMensuels.find(function(soldeMensuel) {
            var moisComparaison = parseInt(soldeMensuel.mois).toString().padStart(2, '0');
            var anneeComparaison = parseInt(soldeMensuel.annee).toString();
            return moisComparaison === "01" && anneeComparaison === year;  // Recherche de janvier de l'année spécifiée
        });

        // Si un solde est trouvé pour janvier, l'afficher, sinon mettre à 0
        if (soldeJanvier) {
            document.getElementById('initial-balance').value = soldeJanvier.solde_initial;
        } else {
            document.getElementById('initial-balance').value = 0;  // Si pas trouvé, mettre à 0
        }

        document.getElementById('initial-balance').readOnly = false;  // Rendre modifiable pour janvier
        return;
    }

    // Si ce n'est pas janvier, chercher le solde final du mois précédent
    var previousMonth = (parseInt(month) - 1).toString().padStart(2, '0'); // Mois précédent en format 01, 02, etc.
    var solde = soldesMensuels.find(function(soldeMensuel) {
        var moisComparaison = parseInt(soldeMensuel.mois).toString().padStart(2, '0');
        var anneeComparaison = parseInt(soldeMensuel.annee).toString();

        // Vérification de la correspondance du mois précédent et de l'année
        return moisComparaison === previousMonth && anneeComparaison === year;
    });

    // Si un solde est trouvé pour le mois précédent, afficher le solde final, sinon afficher 0
    if (solde) {
        document.getElementById('initial-balance').value = solde.solde_final;
    } else {
        document.getElementById('initial-balance').value = 0;  // Sinon, 0 si pas trouvé
    }

    document.getElementById('initial-balance').readOnly = false;  // Rendre non modifiable pour les autres mois
}

// Ajouter un événement pour filtrer lorsque le mois ou l'année change
document.getElementById('month-select').addEventListener('change', function() {
    var selectedMonth = this.value;
    var selectedYear = document.getElementById('year-select').value;
    filterSoldeInitial(selectedMonth, selectedYear);
});

// Filtrer par défaut lors du chargement de la page avec le mois et l'année initiale
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
    // Passer les transactions récupérées depuis Laravel à JavaScript
    var transactions = @json($transactions);

    // Fonction de filtrage des transactions selon le mois et l'année sélectionnés
    function filterTransactions(month, year) {
        return transactions.filter(function(transaction) {
            // Format de la date : YYYY-MM-DD
            var transactionDate = new Date(transaction.date);
            return transactionDate.getMonth() + 1 === parseInt(month) && transactionDate.getFullYear() === parseInt(year);
        });
    }

    // Fonction pour mettre à jour les données du tableau, y compris la ligne vide
    function updateTableData(month, year) {
        var filteredTransactions = filterTransactions(month, year);

        // Transformer les transactions filtrées en données au format attendu par Tabulator
        var tableData = filteredTransactions.map(function(transaction) {
            return {
                id: transaction.id,  // Assure-toi que chaque transaction a un identifiant
                date: transaction.date,
                ref: transaction.reference,
                libelle: transaction.libelle,
                recette: transaction.recette,
                depense: transaction.depense
            };
        });

        // Ajouter une ligne vide à la première position
        var emptyRow = {date: "", ref: "", libelle: "", recette: "", depense: ""};
        tableData.push(emptyRow);

        // Mettre à jour les données du tableau
        table.replaceData(tableData);
        updateTotals(month, year);  // Met à jour les totaux après avoir mis à jour le tableau
    }

    var table = new Tabulator("#example-table", {
        height: 300, // Hauteur du tableau
        layout: "fitColumns", // Ajuster les colonnes pour qu'elles s'adaptent à l'écran
        columns: [
            {title: "Date", field: "date", editor: "input", editorPlaceholder: "Entrez la date", width: 200},
            {title: "N° Référence", field: "ref", editor: "input", editorPlaceholder: "Entrez le N° Référence", width: 200},
            {title: "Libellé", field: "libelle", editor: "input", editorPlaceholder: "Entrez le libellé", width: 282},
            {title: "Recette", field: "recette", editor: "input", editorPlaceholder: "Entrez la recette", width: 200, formatter:"money"},
            {title: "Dépense", field: "depense", editor: "input", editorPlaceholder: "Entrez la dépense", width: 200, formatter:"money"},
            { 
                title: "Actions", 
                field: "actions", 
                width: 100, 
                formatter: function(cell, formatterParams, onRendered) {
                    // Récupérer les données de la ligne
                    var rowData = cell.getRow().getData();

                    // Si la ligne est vide, ne pas afficher l'icône de suppression
                    if (rowData.date === "" && rowData.ref === "" && rowData.libelle === "" && rowData.recette === "" && rowData.depense === "") {
                        return "";  // Pas d'icône pour cette ligne vide
                    }

                    // Créer une icône de suppression pour les autres lignes
                    var deleteIcon = document.createElement("i");
                    deleteIcon.classList.add("fas", "fa-trash-alt");  // Ajoute les classes FontAwesome pour l'icône de suppression
                    deleteIcon.style.cursor = "pointer";  // Rendre l'icône cliquable

                    // Créer un élément span pour l'icône de modification
                    var editIcon = document.createElement("span");
                    editIcon.classList.add("text-warning", "edit-etat-caisse");
                    editIcon.setAttribute("title", "Modifier");
                    editIcon.style.cursor = "pointer";
                    editIcon.innerHTML = `<i class="fas fa-edit" style="color:#82d616;"></i>`;
                    editIcon.setAttribute('data-id', rowData.id);  // Associer l'ID de la transaction à l'icône

                    // Ajouter les événements sur les icônes
                    deleteIcon.onclick = function() {
                        deleteTransaction(rowData.id);  // Suppression de la ligne avec l'id
                    };

                    // Attacher un événement au bouton de modification (si nécessaire)
                    editIcon.onclick = function() {
                        // Fonction de modification si nécessaire
                        editTransaction(rowData.id);
                    };

                    // Retourner l'HTML combiné avec les éléments DOM créés
                    onRendered(function() {
                        // On s'assure d'attacher l'événement à la suppression une fois le rendu effectué
                        cell.getElement().appendChild(deleteIcon);
                        cell.getElement().appendChild(editIcon);
                    });

                    return "";
                }
            }
        ],
        
        data: [],  // Initialement vide, sera rempli par updateTableData
        selectable: true,  // Permet la sélection de lignes
        cellEdited: function(cell) {
            // Recalcul des totaux après chaque modification de cellule
            updateTotals($('#month-select').val(), $('input[type="text"]').val());
            saveData();  // Sauvegarde des données à chaque modification
        }
    });

    $(document).on('click', '.edit-etat-caisse', function() {
        var caisseId = $(this).data('id');  // Récupérer l'ID de la transaction
        console.log("ID de la transaction:", caisseId);

        // Vérifiez si caisseId est valide
        if (!caisseId) {
            console.error("L'ID de la transaction est manquant");
            return;
        }

        $.ajax({
            url: '/etat-caisse/' + caisseId + '/edit',  // L'URL de l'édition avec l'ID
            method: 'GET',
            success: function(data) {
                // Remplir le formulaire avec les données de la transaction
                $('#etat_de_caisse [name="date"]').val(data.date);
                $('#etat_de_caisse [name="Nreference"]').val(data.reference);
                $('#etat_de_caisse [name="Libellé"]').val(data.libelle);
                $('#etat_de_caisse [name="Recette"]').val(data.recette);
                $('#etat_de_caisse [name="Depense"]').val(data.depense);

                // Mettre à jour l'URL d'action du formulaire pour la modification avec l'ID
                $('#etat_de_caisse').attr('action', '/update-transaction/' + caisseId);  // Mettre l'ID dans l'URL

                // Afficher la modale
                $('#editcaisseModal').modal('show');
            },
            error: function(xhr) {
                console.error('Erreur lors de la récupération des données :', xhr);
                alert('Erreur lors de la récupération des données de l\'état de caisse.');
            }
        });
    });

    // Fonction pour supprimer une transaction via AJAX
    function deleteTransaction(transactionId) {
        // Affichage de la boîte de confirmation avant de procéder à la suppression
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
                        table.deleteRow(transactionId); // Supprime la ligne du tableau
                        updateTotals($('#month-select').val(), $('input[type="text"]').val()); // Met à jour les totaux
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
    }

    // Logique d'envoi des données lorsque la touche "Enter" est pressée
    $('#example-table').on('keydown', function(e) {
        if (e.key === "Enter") {
            var selectedRows = table.getSelectedRows();
            
            if (selectedRows.length > 0) {
                var rowData = selectedRows[0].getData();
                console.log("Données de la ligne sélectionnée :");
                console.log("Date :", rowData.date);
                console.log("Référence :", rowData.ref);
                console.log("Libellé :", rowData.libelle);
                console.log("Recette :", rowData.recette);
                console.log("Dépense :", rowData.depense);

                // Convertir la date au format YYYY-MM-DD
                var date = new Date(rowData.date);
                var formattedDate = date.toISOString().split('T')[0];  // Format YYYY-MM-DD

                // Envoyer les données via AJAX
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

    // Fonction pour enregistrer les données via AJAX
    function saveData() {
        var mois = $('#month-select').val();  // Mois sélectionné
        var soldeInitial = parseFloat($('#initial-balance').val() || 0);
        var totalRecette = parseFloat($('#total-revenue').val() || 0);
        var totalDepense = parseFloat($('#total-expense').val() || 0);
        var soldeFinal = parseFloat($('#final-balance').val() || 0);

        var year = $('input[type="text"]').val();  // Année
        var date = new Date(year + '-' + mois + '-01'); // Le premier jour du mois

        // Envoyer les données via AJAX
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

    // Fonction pour mettre à jour les totaux et calculer le solde final
    function updateTotals(month, year) {
        var totalRecette = 0;
        var totalDepense = 0;

        // Filtrer les transactions selon le mois et l'année
        var filteredTransactions = filterTransactions(month, year);

        // Calculer les totaux
        filteredTransactions.forEach(function(row) {
            totalRecette += parseFloat(row.recette || 0); // Ajout des recettes
            totalDepense += parseFloat(row.depense || 0); // Ajout des dépenses
        });

        // Mettre à jour les champs Total recette et Total dépense
        $('#total-revenue').val(totalRecette.toFixed(2));
        $('#total-expense').val(totalDepense.toFixed(2));

        // Calcul du solde final en fonction du solde initial
        var soldeInitial = parseFloat($('#initial-balance').val() || 0);
        var soldeFinal = soldeInitial + totalRecette - totalDepense;

        // Mettre à jour le champ du solde final
        $('#final-balance').val(soldeFinal.toFixed(2));

        // Vérifier si le solde final est négatif ou positif et appliquer la couleur correspondante
        if (soldeFinal < 0) {
            $('#final-balance').css('background-color', 'red');  // Rouge si négatif
        } else {
            $('#final-balance').css('background-color', '#52b438');  // Vert si positif ou égal à zéro
        }

        // Vérifier si le solde initial est négatif ou positif et appliquer la couleur correspondante
        if (soldeInitial < 0) {
            $('#initial-balance').css('background-color', 'red');  // Rouge si négatif
        } else {
            $('#initial-balance').css('background-color', '#52b438');  // Vert si positif ou égal à zéro
        }
    }

    // Ajouter un gestionnaire d'événement pour le solde initial
    $('#initial-balance').on('input', function() {
        updateTotals($('#month-select').val(), $('input[type="text"]').val());
        saveData();  // Sauvegarde des données automatiquement
    });

    // Mettre à jour les totaux au chargement de la page si des données sont déjà présentes
    $(document).ready(function() {
        var currentMonth = $('#month-select').val();
        var currentYear = $('input[type="text"]').val();
        updateTableData(currentMonth, currentYear);
    });

    // Filtrage après chaque changement de mois ou d'année
    $('#month-select, #year-select').on('change', function() {
        var currentMonth = $('#month-select').val();
        var currentYear = $('input[type="text"]').val();
        updateTableData(currentMonth, currentYear);
    });
</script>

@endsection