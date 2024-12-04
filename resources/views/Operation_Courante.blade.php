@extends('layouts.user_type.auth')

@section('content')

<!-- jQuery (DataTables dépend de jQuery) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap CSS (pour un style rapide et simple) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

<!-- Bootstrap JS (si vous avez besoin de certains composants interactifs, comme les modals, tooltips, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container">
    <h2>Opérations Courantes</h2>

    <table class="table table-bordered" id="operationTable">
        <thead>
            <tr>
                <th>Date</th>
                <th>Numéro Dossier</th>
                <th>Numéro Facture</th>
                <th>Libellé</th>
                <th>Compte</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Les données seront injectées ici par DataTables -->
        </tbody>
    </table>

    <button id="addNewRow" class="btn btn-success">Ajouter une Opération</button>
</div>

<script>
    $(document).ready(function() {
        var table = $('#operationTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("operation_courante.index") }}',
                type: 'GET'
            },
            columns: [
                { data: 'date' },
                { data: 'numero_dossier' },
                { data: 'numero_facture' },
                { data: 'libelle' },
                { data: 'compte_select', orderable: false, searchable: false },
                { data: 'actions', orderable: false, searchable: false }
            ]
        });

        // Ajout d'une nouvelle ligne
        $('#addNewRow').click(function() {
            var newRow = table.row.add({
                'date': '<input type="date" class="form-control date-input">',
                'numero_dossier': '<input type="text" class="form-control dossier-input">',
                'numero_facture': '<input type="text" class="form-control facture-input">',
                'libelle': '<input type="text" class="form-control libelle-input">',
                'compte_select': generateCompteSelect(),
                'actions': '<button class="btn btn-primary saveBtn">Enregistrer</button> <button class="btn btn-danger cancelBtn">Annuler</button>'
            }).draw().node();

            $(newRow).find('.saveBtn').click(function() {
                var date = $(newRow).find('.date-input').val();
                var numeroDossier = $(newRow).find('.dossier-input').val();
                var numeroFacture = $(newRow).find('.facture-input').val();
                var libelle = $(newRow).find('.libelle-input').val();
                var compte = $(newRow).find('.compte-select').val();

                $.ajax({
                    url: '{{ route("operation_courante.store") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        date: date,
                        numero_dossier: numeroDossier,
                        numero_facture: numeroFacture,
                        libelle: libelle,
                        compte: compte
                    },
                    success: function(response) {
                        alert(response.success);
                        table.ajax.reload();  // Réactualise la DataTable
                    }
                });
            });

            $(newRow).find('.cancelBtn').click(function() {
                table.row(newRow).remove().draw();
            });
        });

        // Fonction pour générer la liste déroulante des comptes
        function generateCompteSelect() {
            var comptesOptions = '<select class="form-control compte-select">';
            $.ajax({
                url: '{{ route("operation_courante.getComptes") }}',
                type: 'GET',
                async: false,
                success: function(data) {
                    data.comptes.forEach(function(compte) {
                        comptesOptions += '<option value="' + compte.id + '">' + compte.intitule + '</option>';
                    });
                }
            });
            comptesOptions += '</select>';
            return comptesOptions;
        }

        // Mise à jour des champs inline
        $(document).on('change', '.date-input, .libelle-input, .dossier-input, .facture-input, .compte-select', function() {
            var field = $(this).attr('class').split('-')[0];
            var value = $(this).val();
            var operationId = $(this).data('id');

            $.ajax({
                url: '/operation_courante/update/' + operationId,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    field: field,
                    value: value
                },
                success: function(response) {
                    alert(response.success);
                }
            });
        });

        // Suppression d'une opération
        $(document).on('click', '.deleteBtn', function() {
            var operationId = $(this).data('id');

            if (confirm('Êtes-vous sûr de vouloir supprimer cette opération ?')) {
                $.ajax({
                    url: '/operation_courante/destroy/' + operationId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        table.ajax.reload();
                        alert(response.success);
                    }
                });
            }
        });
    });
</script>

@endsection
