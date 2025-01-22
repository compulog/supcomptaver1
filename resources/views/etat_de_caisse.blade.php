@extends('layouts.user_type.auth')

@section('content')

<!-- Import des fichiers CSS et JS de Tabulator -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.9/css/tabulator.min.css" rel="stylesheet">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.9/js/tabulator.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

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
<a href="">etat de caisse</a>
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
    <input type="text" value="{{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}" readonly>
</div>

<!-- Solde initial -->
<div class="form-group">
    <label for="initial-balance">Solde initial :</label>
    <input type="number" id="initial-balance" placeholder="Solde initial">
</div>

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

<!-- Bouton pour enregistrer les données -->
<div class="form-group" style="text-align: center;">
    <button id="save-data" class="btn btn-primary">Enregistrer</button>
</div>

<script>
    // Passer les transactions récupérées depuis Laravel à JavaScript
    var transactions = @json($transactions);

    // Transformer les données pour le format attendu par Tabulator
    var tableData = transactions.map(function(transaction) {
        return {
            date: transaction.date,
            ref: transaction.reference,
            libelle: transaction.libelle,
            recette: transaction.recette,
            depense: transaction.depense
        };
    });

    // Ajouter des lignes vides pour permettre l'ajout de nouvelles données
    var emptyRows = [
        {date: "", ref: "", libelle: "", recette: 0, depense: 0},  // Initialiser avec 0 pour éviter NaN
    ];

    // Combiner les lignes vides et les transactions existantes
    var allRows = emptyRows.concat(tableData);

    // Initialisation du tableau Tabulator
    var table = new Tabulator("#example-table", {
        height: 300, // Hauteur du tableau
        layout: "fitColumns", // Ajuster les colonnes pour qu'elles s'adaptent à l'écran
        columns: [
            {title: "Date", field: "date", editor: "input", editorPlaceholder: "Entrez la date", width: 200},
            {title: "N° Référence", field: "ref", editor: "input", editorPlaceholder: "Entrez le N° Référence", width: 200},
            {title: "Libellé", field: "libelle", editor: "input", editorPlaceholder: "Entrez le libellé", width: 200},
            {title: "Recette", field: "recette", editor: "input", editorPlaceholder: "Entrez la recette", width: 200, formatter:"money"},
            {title: "Dépense", field: "depense", editor: "input", editorPlaceholder: "Entrez la dépense", width: 200, formatter:"money"}
        ],
        data: allRows,  // Afficher les transactions dans le tableau + lignes vides
        selectable: true,  // Permet la sélection de lignes
        cellEdited: function(cell) {
            // Recalcul des totaux après chaque modification de cellule
            updateTotals();
        }
    });

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

    // Fonction pour mettre à jour les totaux et calculer le solde final
    function updateTotals() {
        var totalRecette = 0;
        var totalDepense = 0;

        // Parcours de toutes les lignes du tableau
        table.getData().forEach(function(row) {
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
        updateTotals();
    });

    // Mettre à jour les totaux au chargement de la page si des données sont déjà présentes
    $(document).ready(function() {
        updateTotals();
    });

    

    $('#save-data').on('click', function() {
    // Récupérer les valeurs
    var mois = $('#month-select').val();  // Mois sélectionné
    var soldeInitial = parseFloat($('#initial-balance').val() || 0);
    var totalRecette = parseFloat($('#total-revenue').val() || 0);
    var totalDepense = parseFloat($('#total-expense').val() || 0);
    var soldeFinal = parseFloat($('#final-balance').val() || 0);

    // Calculer la date du premier jour du mois sélectionné (au format YYYY-MM-DD)
    var year = $('input[type="text"]').val();  // Année
    var date = new Date(year + '-' + mois + '-01'); // Le premier jour du mois

    // Envoyer les données via AJAX
    $.ajax({
    url: '/save-solde',
    method: 'POST',
    data: {
        mois: $("#mois").val(),
        solde_initial: $("#solde_initial").val(),
        total_recette: $("#total_recette").val(),
        total_depense: $("#total_depense").val(),
        solde_final: $("#solde_final").val(),
        _token: "{{ csrf_token() }}" // Assurez-vous que le token CSRF est inclus
    },
    success: function(response) {
        console.log(response);
        alert("Enregistrement réussi !");
    },
    error: function(xhr, status, error) {
        console.error(xhr.responseText);
        alert("Une erreur s'est produite.");
    }
});
     
});

</script>




@endsection
