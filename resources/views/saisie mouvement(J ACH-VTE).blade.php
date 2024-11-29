<head>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisie des opérations</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

    <!-- SweetAlert2 JS (pour des alertes) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap JS (nécessaire pour certains composants Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

@extends('layouts.user_type.auth')

@section('content')
<div class="container">
    <h1>Opérations Courantes</h1>

    <!-- Formulaire de filtrage par année et mois -->
    <form id="filterForm">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="year">Année</label>
                <select name="year" id="year" class="form-control">
                    <option value="">Sélectionner l'année</option>
                    @foreach(range(2020, date('Y')) as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="month">Mois</label>
                <select name="month" id="month" class="form-control">
                    <option value="">Sélectionner le mois</option>
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 10)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary mt-4">Filtrer</button>
            </div>
        </div>
    </form>

    <!-- Table des opérations -->
    <table id="operationsTable" class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>N° Dossier</th>
                <th>N° Facture</th>
                <th>Compte</th>
                <th>Libellé</th>
                <th>Débit</th>
                <th>Crédit</th>
                <th>Contre-Partie</th>
                <th>Rubrique TVA</th>
                <th>Compte TVA</th>
                <th>Prorat de Déduction</th>
                <th>Pièce Justificative</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Les données seront chargées via Ajax -->
        </tbody>
    </table>

    <!-- Cumul et Solde -->
    <div class="row mt-4">
        <div class="col-md-6">
            <p>Cumul Débit: <span id="cumulDebit">0.00</span></p>
            <p>Cumul Crédit: <span id="cumulCredit">0.00</span></p>
        </div>
        <div class="col-md-6">
            <p>Solde Débiteur: <span id="soldeDebiteur">0.00</span></p>
            <p>Solde Créditeur: <span id="soldeCrediteur">0.00</span></p>
        </div>
    </div>

    <!-- Boutons d'actions -->
    <div class="mt-4">
        <button class="btn btn-success" id="addOperationBtn">Ajouter une opération</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const societeId = "{{ session('societe_id') }}";

    // Initialiser DataTable avec configuration de base
    var table = $('#operationsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "destroy": true,
        "ajax": {
            url: '{{ route("operation_courante.index") }}', // L'URL de votre contrôleur
            data: function(d) {
                d.year = $('#year').val();
                d.month = $('#month').val();
                d.societe_id = societeId;
            },
            type: "GET",
        },
        "ordering": false,
        "columns": [
            { data: 'date' },
            { data: 'numero_dossier' },
            { data: 'numero_facture' },
            { data: 'compte' },
            { data: 'libelle' },
            { data: 'debit' },
            { data: 'credit' },
            { data: 'contre_partie' },
            { data: 'rubrique_tva' },
            { data: 'compte_tva' },
            { data: 'prorat_de_deduction' },
            { data: 'piece_justificative' },
            {
                data: 'actions',
                orderable: false,
                searchable: false
            }
        ]
    });

    // Charger les données avec filtrage
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload(); // Recharger la table avec les nouveaux filtres
    });

    // Ajouter une opération
    $('#addOperationBtn').on('click', function() {
        const newOperation = {
            date: '2024-12-01',
            numero_dossier: '1234',
            numero_facture: 'F1234',
            compte: 'A100',
            libelle: 'Test Débit',
            debit: '100.00',
            credit: '0.00',
            contre_partie: 'Contre Partie 1',
            rubrique_tva: 'Taux A',
            compte_tva: 'TVA1',
            prorat_de_deduction: '50%',
            piece_justificative: 'PJ1'
        };

        $.ajax({
            url: '{{ route("operation_courante.store") }}',
            method: 'POST',
            data: newOperation,
            success: function(response) {
                table.ajax.reload();
                alert(response.success);
            }
        });
    });

    // Supprimer une opération
    $(document).on('click', '.deleteBtn', function() {
        let operationId = $(this).closest('tr').data('id');
        if (confirm("Êtes-vous sûr de vouloir supprimer cette opération?")) {
            $.ajax({
                url: `/operation_courante/${operationId}`,
                method: 'DELETE',
                success: function(response) {
                    table.ajax.reload();
                    alert(response.success);
                }
            });
        }
    });
</script>
@endsection
