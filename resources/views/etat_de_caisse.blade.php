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

    <label for="year-select">Choisir l'année :</label>
    <select id="year-select">
        <!-- Les années peuvent être définies dynamiquement ou manuellement -->
        <option value="2025">2025</option>
        <option value="2024">2024</option>
        <option value="2023">2023</option>
        <option value="2022">2022</option>
    </select>
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

<script>
    // Initialisation des données vides pour le tableau
    var emptyRows = [
        {date: "", ref: "", libelle: "", recette: "", depense: ""},
        {date: "", ref: "", libelle: "", recette: "", depense: ""},
        {date: "", ref: "", libelle: "", recette: "", depense: ""},
        {date: "", ref: "", libelle: "", recette: "", depense: ""},
        {date: "", ref: "", libelle: "", recette: "", depense: ""}
    ];

    // Initialisation du tableau Tabulator
    var table = new Tabulator("#example-table", {
    height: 300, // Hauteur du tableau
    layout: "fitColumns", // Ajuster les colonnes pour qu'elles s'adaptent à l'écran
    columns: [
        {title: "Date", field: "date", editor: "input", editorPlaceholder: "Entrez la date", width: 200},
        {title: "N° Référence", field: "ref", editor: "input", editorPlaceholder: "Entrez le N° Référence", width: 200},
        {title: "Libellé", field: "libelle", editor: "input", editorPlaceholder: "Entrez le libellé", width: 200},
        {title: "Recette", field: "recette", editor: "input", editorPlaceholder: "Entrez la recette", width: 200},
        {title: "Dépense", field: "depense", editor: "input", editorPlaceholder: "Entrez la dépense", width: 200}
    ],
    selectable: true, // Active la sélection des lignes
    cellEdited: function(cell) {
        console.log("Cellule modifiée:", cell.getData());
    },
    data: emptyRows // Remplir directement le tableau avec des lignes vides
});


    // Écouter l'événement "keydown" pour détecter la touche "Enter"
    // Écouter l'événement "keydown" pour détecter la touche "Enter"
$('#example-table').on('keydown', function(e) {
    // Vérifier si la touche pressée est "Enter"
    if (e.key === "Enter") {
        // Récupérer les lignes sélectionnées
        var selectedRows = table.getSelectedRows();

        // Vérifier s'il y a une ligne sélectionnée
        if (selectedRows.length > 0) {
            var rowData = selectedRows[0].getData(); // Récupérer les données de la ligne sélectionnée

            // Envoyer les données via AJAX
            $.ajax({
    url: "{{ route('savetransaction') }}", // Assurez-vous que cette URL est correcte.
    type: "POST",
    data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        date: rowData.date,
        ref: rowData.ref,
        libelle: rowData.libelle,
        recette: rowData.recette,
        depense: rowData.depense
    },
    success: function(response) {
        console.log("Données envoyées avec succès :", response);
    },
    error: function(xhr, status, error) {
        console.error("Erreur lors de l'envoi des données :", error);
    }
});


        } else {
            console.log("Aucune ligne sélectionnée !");
        }
    }
});

</script>

@endsection
