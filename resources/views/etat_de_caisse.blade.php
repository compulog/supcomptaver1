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
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
    }

    h2 {
        text-align: center;
        color: #333;
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
    }

    .form-group select, .form-group input {
        padding: 10px;
        margin: 0 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    #example-table {
        margin: 20px 0;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .total-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 20px 0;
    }

    .total-container label {
        font-weight: bold;
    }

    .total-container input {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
</style>

<!-- Navigation -->
<a href="{{ route('exercices.show', ['societe_id' => session()->get('societeId')]) }}">Tableau De Board</a>
➢
<a href="{{ route('caisse.view') }}">Caisse</a>
➢
<a href="">Etat de caisse</a>
<center><h5>ETAT DE CAISSE MENSUELLE</h5></center>

<!-- Sélecteur de mois et d'année -->
<div class="form-group">
    <label for="month-select">Choisir le mois :</label>
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

    <label for="year-select">L'année :</label>
    <input type="text" id="year-select" value="{{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}" readonly>
</div>

<!-- Solde initial à afficher en fonction du mois et de l'année choisis -->
<div class="form-group">
    <label for="initial-balance">Solde initial :</label>
    <input type="number" id="initial-balance" readonly>
</div>








<!-- Modal pour la modification detat de caisse -->
<div class="modal fade" id="editcaisseModal" tabindex="-1" role="dialog" aria-labelledby="editClientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="etat_de_caisse" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel">Modifier</h5>
                    <i class="fas fa-times" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></i>

                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="N° Référence">N° Référence</label>
                        <input type="number" class="form-control" name="N° Référence" required>
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
                    <label for="Dépense">Dépense</label>
                    <input type="number" class="form-control" name="Dépense" required>
                    </div>
                </div>
                <div class="modal-footer">
                     <!-- Bouton Réinitialiser avec marge très grande à droite -->
                     <button type="reset" class="btn btn-secondary me-8">
                            <i class="fas fa-undo"></i> 
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
    // Chercher le solde mensuel correspondant au mois et à l'année
    var solde = soldesMensuels.find(function(soldeMensuel) {
        // Convertir mois et année en valeurs comparables (en entier ou chaîne formatée)
        var moisComparaison = parseInt(soldeMensuel.mois).toString().padStart(2, '0'); // Mois comme "01", "02", etc.
        var anneeComparaison = parseInt(soldeMensuel.annee).toString(); // Année comme "2011", "2012", etc.
        
        console.log('Comparaison:', moisComparaison, month, anneeComparaison, year);  // Debugging

        // Vérification de la correspondance du mois et de l'année
        return moisComparaison === month && anneeComparaison === year;
    });

    // Si un solde est trouvé, afficher le solde initial, sinon afficher 0
    if (solde) {
        document.getElementById('initial-balance').value = solde.solde_initial;
    } else {
        document.getElementById('initial-balance').value = 0;
    }

    // Rendre le champ éditable uniquement pour le mois de janvier (01)
    if (month === "01") {
        document.getElementById('initial-balance').readOnly = false; // Rendre le champ modifiable
    } else {
        document.getElementById('initial-balance').readOnly = true; // Le rendre en lecture seule
    }
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
        tableData.unshift(emptyRow);

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
        {title: "Libellé", field: "libelle", editor: "input", editorPlaceholder: "Entrez le libellé", width: 200},
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
        // Nous retournons ici les éléments DOM qui seront ajoutés au tableau.
        onRendered(function() {
            // On s'assure d'attacher l'événement à la suppression une fois le rendu effectué
            cell.getElement().appendChild(deleteIcon);
            cell.getElement().appendChild(editIcon);
        });

        // Ne rien retourner ici directement sous forme de texte.
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


$(document).ready(function() {
    // Événement pour le clic sur le bouton d'édition
    $(document).on('click', '.edit-etat-caisse', function() {
        var caisseId = $(this).data('id');
        $('#editcaisseModal').modal('show');
        // Requête AJAX pour récupérer les données du client
        // $.ajax({
        //     url: '/etat-caisse/' + caisseId + '/edit',
        //     method: 'GET',
        //     success: function(data) {
        //         // Remplir le formulaire dans le pop-up avec les données
        //         $('#clientForm [name="compte"]').val(data.compte);
        //         $('#clientForm [name="intitule"]').val(data.intitule);
        //         $('#clientForm [name="identifiant_fiscal"]').val(data.identifiant_fiscal);
        //         $('#clientForm [name="ICE"]').val(data.ICE);
        //         // Remplir d'autres champs si nécessaire

        //         // Mettre à jour l'URL d'action du formulaire pour la modification
        //         $('#clientForm').attr('action', '/clients/' + caiseId);

        //         // Afficher le pop-up
        //         $('#editcaisseModal').modal('show');
        //     },
        //     error: function(xhr) {
        //         console.error('Erreur lors de la récupération des données :', xhr);
        //         alert('Erreur lors de la récupération des données du client.');
        //     }
        // });
    });
});
// Fonction pour supprimer une transaction via AJAX
function deleteTransaction(transactionId) {
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
            $('#final-balance').css('background-color', 'green');  // Vert si positif ou égal à zéro
        }

        // Vérifier si le solde initial est négatif ou positif et appliquer la couleur correspondante
        if (soldeInitial < 0) {
            $('#initial-balance').css('background-color', 'red');  // Rouge si négatif
        } else {
            $('#initial-balance').css('background-color', 'green');  // Vert si positif ou égal à zéro
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
