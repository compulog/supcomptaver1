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
#cloturer-button:disabled {
    background-color: red;  
    color: white; 
    cursor: not-allowed;  
}

</style>

<!-- Navigation -->
<nav>
    <a href="{{ route('exercices.show', ['societe_id' => session()->get('societeId')]) }}">Tableau De Board</a>
    ➢
    <!-- <a href="{{ route('caisse.view') }}">Caisse</a>
    ➢ -->
    <a href="">Etat de caisse</a>
</nav>
<center><h5>ETAT DE CAISSE MENSUELLE</h5></center>
<!-- Conteneur pour les sélecteurs -->
<div class="form-group" style="display: flex; align-items: center; margin-left: -22%;">
    <!-- Sélecteur de code journal -->
    <label for="journal-select" style="margin-right: 10px;">Code Journal :</label>
    <select id="journal-select" style="width: 150px; height: 31px; margin-right: 20px;">
        @foreach ($journauxCaisse as $journal)
            <option value="{{ $journal->code_journal }}" data-intitule="{{ $journal->intitule }}">{{ $journal->code_journal }}</option>
        @endforeach
        <!-- Vous pouvez également ajouter d'autres options ici si nécessaire -->
    </select>

    <!-- Champ de texte pour l'intitulé -->
    <label for="intitule-input" style="margin-right: 10px;">Intitulé :</label>
    <input type="text" id="intitule-input" style="width: 150px; height: 31px;" readonly>


<script>
    // Récupérer les éléments du DOM
    const journalSelect = document.getElementById('journal-select');
    const intituleInput = document.getElementById('intitule-input');

    // Ajouter un écouteur d'événements pour détecter le changement de sélection du code journal
    journalSelect.addEventListener('change', function() {
        // Récupérer l'option sélectionnée
        const selectedOption = journalSelect.options[journalSelect.selectedIndex];
        // Récupérer l'intitulé associé à cette option
        const intitule = selectedOption.getAttribute('data-intitule');
        
        // Mettre à jour l'intitulé dans l'input correspondant
        intituleInput.value = intitule;
    });

    // Initialiser l'intitulé au chargement de la page avec la première sélection
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser avec la première option sélectionnée
        journalSelect.dispatchEvent(new Event('change'));
    });
</script>




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
    <i id="export-excel-icon" class="fas fa-file-excel" title="Exporter en Excel" style="cursor: pointer; font-size: 17px; color: green;margin-left:10px;"></i>
    <i class="fas fa-trash-alt" id="deleteAllIcon" title="Supprimer toutes les lignes sélectionnées" style="cursor: pointer;" onclick="deleteSelectedRows()"></i>

<!-- Bouton de transfert conditionnel -->
@if(auth()->user()->type !== 'interlocuteurs')
    <i class="fa fa-share" aria-hidden="true" title="transférer" style="margin-left:3px;"></i>
@endif
</div>

<!-- Solde initial à afficher en fonction du mois et de l'année choisis -->
<div class="form-group" style="margin-left:70%;margin-top:-20px;">
    <label for="initial-balance">Solde initial :</label>
    <!-- <input type="number" id="initial-balance" readonly> -->
    <input type="number" id="initial-balance" >
</div>
 
<div id="example-table"></div>
<input type="hidden" id="total-revenue" placeholder="Total recette">
     <input type="hidden" id="total-expense" placeholder="Total dépense"> 
<!-- Total recette, dépense et solde final -->
<div style="margin-left:70%;">
     
    <label for="final-balance">Solde final :</label>
    <input type="number" id="final-balance" placeholder="Solde final" style="border-radius:4px;border:green;">
</div>
 
<button id="cloturer-button" class="btn btn-primary" style="margin-left:85%;height:31px;border-radius:4px;font-size:10px;">Clôturer</button>

<!-- <i class="fa fa-trash" id="delete-selected"></i> -->

<script>
// Passer les soldes mensuels récupérés depuis Laravel à JavaScript
var soldesMensuels = @json($soldesMensuels);
var transactions = @json($transactions);

</script>
<script src="{{ asset('js/etat_de_caisse.js') }}"></script>

@endsection