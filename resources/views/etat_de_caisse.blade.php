@extends('layouts.user_type.auth')

@section('content')  

<!-- Tabulator, jQuery, SweetAlert2, FontAwesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.9/css/tabulator.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.9/js/tabulator.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        padding: 20px;
    }
    h5 {
        color: #343a40;
        font-weight: 600;
        margin-bottom: 20px;
    }
    .form-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
    }
    .form-group label {
        font-weight: bold;
        color: #495057;
        margin-right: 5px;
    }
    .form-group select, .form-group input {
        padding: 6px 10px;
        border: 1px solid #ced4da;
        border-radius: 5px;
        transition: border-color 0.3s;
        font-size: 11px;
        width: 130px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }
    .form-group select:focus, .form-group input:focus {
        border-color: #80bdff;
        outline: none;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }
    #example-table {
        border: 1px solid #ced4da;
        border-radius: 5px;
        background-color: #fff;
        margin-top: 15px;
        font-size: 11px;
    }
    .action-icons i {
        margin: 0 5px;
        cursor: pointer;
        color: #6c757d;
    }
    .action-icons i:hover {
        color: #000;
    }
    .nav-arrow {
        margin: 0 5px;
        font-size: 11px;
        color: #495057;
        font-weight: bold;
    }
 .total-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    height: 60px;
    padding: 0 20px;
}

.solde-final {
    position: absolute;
    left: 62%;
    transform: translateX(-62%);
    display: flex;
    align-items: center;
    gap: 10px;
}

.solde-final label {
    font-size: 14px;
}

.solde-final input {
    font-size: 14px;
    padding: 5px 10px;
    width: 150px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

#cloturer-button {
    font-size: 14px;
    padding: 7px 16px;
    background-color: #13922b;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

#cloturer-button:hover {
    background-color: #a20c7f;
}

  /* Ajoute ce style dans ton fichier CSS ou dans un <style> */
#cloturer-button:disabled {
    background-color: #bdbdbd !important; /* gris ou la couleur de ton choix */
    color: #fff;
    cursor: not-allowed;
    border-color: #bdbdbd;
}
    .modal-header {
        background-color: #cb0c9f;
        color: white;
    }
    .modal-footer .btn-primary {
        background-color: #cb0c9f;
    }
    .modal-footer .btn-secondary {
        background-color: #adb5bd;
    }
    .nav-arrow {
    font-size: 11px;
    color: #6c757d;
    margin: 0 6px;
    vertical-align: middle;
}
.form-group {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-bottom: 15px;
    justify-content: space-between;
}

.action-right-buttons {
    display: flex;
    gap: 10px;
}

.action-right-buttons button {
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 11px;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.action-right-buttons button i {
    color: #495057;
}

.action-right-buttons button:hover {
    background-color: #f1f1f1;
}

.action-right-buttons button[title*="Excel"] i { color: #2f9e44; }
.action-right-buttons button[title*="PDF"] i { color: #d6336c; }
.action-right-buttons button[title*="Supprimer"] i { color: #e03131; }
.action-right-buttons button[title*="Transférer"] i { color: #6f42c1; }


@media (max-width: 768px) {
    input[type="text"],
    input[type="number"],
    select {
        font-size: 11px;
        width: 100px;
        padding: 3px 6px;
    }
}

</style>

<nav class="mb-3">
    <a href="{{ route('exercices.show', ['societe_id' => session()->get('societeId')]) }}">Tableau de bord</a>
<i class="fas fa-chevron-right nav-arrow"></i>
    <a href="#">État de caisse Mensuel</a>
</nav>

<div class="form-group">
    <label for="journal-select">Code :</label>
    <select id="journal-select">
        <option value="Null">Choisir une option</option>
        @foreach ($journauxCaisse as $journal)
            <option value="{{ $journal->code_journal }}" data-intitule="{{ $journal->intitule }}">{{ $journal->code_journal }}</option>
        @endforeach
    </select>

    <label for="intitule-input">Intitulé :</label>
    <input type="text" id="intitule-input" readonly>




    <!-- Sélecteur de mois et d'année -->
    <label for="month-select" style="margin-right: 10px;">Période :</label>
    <select id="month-select" style="margin-right: 10px;">
        <option value="Null">Choisir une option</option>
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
    {{-- <label for="month-select">Période :</label>
    <select id="month-select">
        @for ($i = 1; $i <= 12; $i++)
            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                {{ \Carbon\Carbon::create()->month($i)->locale('fr_FR')->isoFormat('MMMM') }} {{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}
            </option>
        @endfor
    </select> --}}
    <input type="text" id="year-select" value="{{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}" readonly  style="display: none;">

    {{-- <input type="hidden" id="year-select" value="{{ \Carbon\Carbon::parse($societe->exercice_social_debut)->year }}"> --}}

    {{-- <i id="export-excel-icon" class="fas fa-file-excel" title="Exporter en Excel" style="color: green;"></i>
    <i class="fas fa-trash-alt" id="deleteAllIcon" title="Supprimer les lignes sélectionnées" onclick="deleteSelectedRows()"></i> --}}

    {{-- @if(auth()->user()->type !== 'interlocuteurs')
        <i class="fa fa-share"  aria-hidden="true" title="Transférer" id="transfer-button"></i>
    @endif  --}}

    <label for="initial-balance">Solde initial :</label>
    
    <input type="number" id="initial-balance">







<!-- Boutons alignés à droite -->
    <div class="action-right-buttons" style="margin-left: auto;">
        <button id="export-excel-icon" title="Exporter en Excel">
            <i class="fas fa-file-excel"></i>
        </button>
        <button id="export-pdf-icon" title="Exporter en PDF">
            <i class="fas fa-file-pdf"></i>
        </button>
        <button id="deleteAllIcon" title="Supprimer les lignes sélectionnées" onclick="deleteSelectedRows()">
            <i class="fas fa-trash-alt"></i>
        </button>
        @if(auth()->user()->type !== 'interlocuteurs')
            <button id="transfer-button" title="Transférer">
                <i class="fas fa-share" aria-hidden="true"></i>
            </button>
        @endif
    </div>
</div>


<div id="example-table"></div>
<input type="hidden" id="total-revenue" placeholder="Total recette">
     <input type="hidden" id="total-expense" placeholder="Total dépense">

<div class="total-container">
    <div class="solde-final">
        <label for="final-balance">Solde final :</label>
        <input type="number" id="final-balance" placeholder="Solde final">
    </div>
        <div style="margin-left: auto;">

    <button id="cloturer-button">Clôturer</button>
        </div>

</div>




<!-- Modale pour visualiser le fichier -->
<div class="modal fade" id="fichierModal" tabindex="-1" aria-labelledby="fichierModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="fichierModalLabel">Fichier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe id="fichierIframe" src="" width="100%" height="500px" frameborder="0"></iframe>
      </div>
    </div>
  </div>
</div>
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
    document.addEventListener('DOMContentLoaded', function () {
        journalSelect.dispatchEvent(new Event('change'));

        const transferButton = document.getElementById('transfer-button');
        if (transferButton) {
            transferButton.addEventListener('click', function () {
                Swal.fire({
                    title: 'Confirmer le transfert',
                    text: 'Êtes-vous sûr de vouloir transférer cet état de caisse ?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, transférer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire('Transféré !', "L'état de caisse a été transféré avec succès.", 'success');
                    }
                });
            });
        }
    });




    var soldesMensuels = @json($soldesMensuels);
    var transactions = @json($transactions);
console.log('transaction:', JSON.stringify(transactions, null, 2));
</script>
<script src="{{ asset('js/etat_de_caisse.js') }}"></script>

@endsection
