@extends('layouts.user_type.auth')

@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">
    <div class="container mt-5">
        <h3>Ajouter un Client</h3>

        <!-- Affichage du message de succès ou d'erreur -->
        <div id="message" class="alert d-none" role="alert"></div>

<!-- Boutons pour ouvrir les modals -->
<div class="mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-saisie-manuel">
        Ajouter
    </button>
    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modal-import-excel">
                Importer
            </button>
</div>

<!-- Modal pour le formulaire d'ajout manuel -->
<div class="modal fade" id="modal-saisie-manuel" tabindex="-1" aria-labelledby="modalSaisieManuelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSaisieManuelLabel">Nouveau Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> 
            <div class="modal-body">
                <form action="{{ route('client.store') }}" method="POST" id="form-saisie-manuel">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="compte" class="form-label">Compte</label>
                            <input type="text" class="form-control" name="compte" placeholder="Compte" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="intitule" class="form-label">Intitulé</label>
                            <input type="text" class="form-control" name="intitule" placeholder="Intitulé" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" id="identifiant_fiscal" name="identifiant_fiscal" class="form-control" maxlength="8" pattern="\d*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        
                        </div>
                        <div class="col-md-6">
                            <label for="ICE" class="form-label">ICE</label>
                            <input type="text" id="ICE" name="ICE" class="form-control" maxlength="15" pattern="\d*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                           
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type_client" class="form-label">Type client</label>
                           
                            <select class="form-control" name="type_client" required>
                            <option value="1.Entreprise de droit privé">1.Entreprise de droit privé</option>
                            <option value="2.État">2.État</option>
                            <option value="3.Collectivités territoriales">3.Collectivités territoriales</option>
                            <option value="4.Entreprise publique">4.Entreprise publique</option>
                            <option value="5.Autre organisme public">5.Autre organisme public</option>
    
</select>

</select>

                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>

  <!-- Modal pour le formulaire d'importation Excel -->
  <div class="modal fade" id="modal-import-excel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalImportExcelLabel">Importer des Clients</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('import.clients') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label">Fichier Excel :</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <h4>Mapping des champs :</h4>
                            <div class="mb-3">
                                <label for="compte">Colonne Compte :</label>
                                <input type="number" name="mapping[compte]" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="intitule">Colonne Intitulé :</label>
                                <input type="number" name="mapping[intitule]" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="identifiant_fiscal">Colonne Identifiant Fiscal :</label>
                                <input type="number" name="mapping[identifiant_fiscal]" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="ICE">Colonne ICE :</label>
                                <input type="number" name="mapping[ICE]" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="type_client">Colonne Type Client :</label>
                                <input type="number" name="mapping[type_client]" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">Importer Clients</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<script>
    protected function validateRow(array $row)
{
    $requiredFields = ['compte', 'intitule', 'identifiant_fiscal', 'ice', 'type_client'];

    foreach ($requiredFields as $field) {
        if (empty($row[$field])) {
            $this->failedRows[] = $row; // Add the failed row for later review
            throw new \InvalidArgumentException("Le champ '$field' est requis.");
        }
    }

    // Validation pour l'ICE
    if (!preg_match('/^[0-9]{15}$/', $row['ice'])) {
        $this->failedRows[] = $row; // Add the failed row for later review
        throw new \InvalidArgumentException("Le champ 'ICE' doit contenir 15 chiffres.");
    }
}

</script>

<!-- @foreach($clients as $client)
   
@endforeach -->
<!-- Modal pour la modification d'un client -->
<div class="modal fade" id="editClientModal" tabindex="-1" role="dialog" aria-labelledby="editClientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="clientForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel">Modifier le Client</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="compte">Compte</label>
                        <input type="text" class="form-control" name="compte" required>
                    </div>
                    <div class="form-group">
                        <label for="intitule">Intitulé</label>
                        <input type="text" class="form-control" name="intitule" required>
                    </div>
                    <div class="form-group">
                        <label for="identifiant_fiscal">Identifiant Fiscal</label>
                        <input type="text" class="form-control" name="identifiant_fiscal" required>
                    </div>
                    <div class="form-group">
                        <label for="ICE">ICE</label>
                        <input type="text" class="form-control" name="ICE" required>
                    </div>
                    <div class="form-group">
                        <label for="type_client">Type Client</label>
                        <select class="form-control" name="type_client" required>
                            <option value="1.Entreprise de droit privé">1.Entreprise de droit privé</option>
                            <option value="2.État">2.État</option>
                            <option value="3.Collectivités territoriales">3.Collectivités territoriales</option>
                            <option value="4.Entreprise publique">4.Entreprise publique</option>
                            <option value="5.Autre organisme public">5.Autre organisme public</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Sauvegarder les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
$(document).ready(function() {
    // Événement pour le clic sur le bouton d'édition
    $(document).on('click', '.edit-client', function() {
        var clientId = $(this).data('id');

        // Appel AJAX pour récupérer les données du client
        $.ajax({
            url: '/clients/' + clientId + '/edit', // Vérifiez que cette route existe
            method: 'GET',
            success: function(data) {
                // Remplir le formulaire dans le pop-up avec les données
                $('#clientForm [name="compte"]').val(data.compte);
                $('#clientForm [name="intitule"]').val(data.intitule);
                $('#clientForm [name="identifiant_fiscal"]').val(data.identifiant_fiscal);
                $('#clientForm [name="ICE"]').val(data.ICE);
                // Remplir d'autres champs si nécessaire
                
                // Mettre à jour l'URL d'action du formulaire pour la modification
                $('#clientForm').attr('action', '/clients/' + clientId); // Assurez-vous que cette route est correcte

                // Afficher le pop-up
                $('#editClientModal').modal('show');
            },
            error: function(xhr) {
                console.error('Erreur lors de la récupération des données :', xhr);
            }
        });
    });

    // Événement pour la soumission du formulaire de modification
    $('#clientForm').on('submit', function(event) {
        event.preventDefault(); // Empêche le comportement par défaut du formulaire

        // Appel AJAX pour modifier le client
        $.ajax({
            url: $(this).attr('action'), // Utiliser l'URL définie précédemment
            method: 'PUT', // Assurez-vous que votre méthode est correcte (PUT pour modification)
            data: $(this).serialize(), // Sérialiser les données du formulaire
            success: function(data) {
                // Afficher un message de succès
                alert("Client modifié avec succès !");

                // Mettre à jour la ligne correspondante dans le tableau Tabulator
                var updatedClient = {
                    id: data.client.id, // ID du client
                    compte: $('#clientForm [name="compte"]').val(), // Nouveau compte
                    intitule: $('#clientForm [name="intitule"]').val(), // Nouveau intitulé
                    identifiant_fiscal: $('#clientForm [name="identifiant_fiscal"]').val(), // Nouvel identifiant fiscal
                    ICE: $('#clientForm [name="ICE"]').val(), // Nouvel ICE
                    type_client: data.client.type_client // Garder le type client de la réponse
                };

                // Supposons que votre tableau Tabulator est stocké dans une variable appelée "table"
                table.updateOrAddData([updatedClient]); // Mettre à jour la ligne correspondante

                // Fermer le modal
                $('#editClientModal').modal('hide');
            },
            error: function(xhr) {
                console.error('Erreur lors de la modification du client :', xhr);
                alert("Erreur lors de la modification du client !");
            }
        });
    });
});



</script>



<!-- CSS de Tabulator -->
<link href="https://unpkg.com/tabulator-tables@5.4.3/dist/css/tabulator.min.css" rel="stylesheet">

<!-- JavaScript de Tabulator -->
<script src="https://unpkg.com/tabulator-tables@5.4.3/dist/js/tabulator.min.js"></script>

    <!-- Conteneur Tabulator avec recherche -->
<div class="container mt-4">
    <h3>Liste des clients</h3>
      
    <!-- Conteneur pour Tabulator -->
    <div id="table-list"></div>
</div>

<script>
    
    //inicialise le tab ulator
    var table = new Tabulator("#table-list", {
    layout: "fitColumns",
    data: @json($clients), // Chargement initial des données
    columns: [
        {title: "Compte", field: "compte", headerFilter: true},
        {title: "Intitulé", field: "intitule", headerFilter: true},
        {title: "Identifiant fiscal", field: "identifiant_fiscal", headerFilter: true},
        {title: "ICE", field: "ICE", headerFilter: true},
        {title: "Type client", field: "type_client", headerFilter: true},
        {
    title: "Actions", field: "id", formatter: function(cell, formatterParams, onRendered){
        var id = cell.getValue();
        const rowData = cell.getRow().getData(); // Obtenez les données de la ligne

        return `
          <span class="text-warning edit-client" title="Modifier" style="cursor: pointer;" data-id="${id}">
    <i class="fas fa-edit"></i>
</span>

            <span class="text-danger" title="Supprimer" style="cursor: pointer;" onclick="deleteclients(${id})">
                <i class="fas fa-trash"></i>
            </span>
        `;
    }
}

    ]
});


</script>





<script>
    document.getElementById('form-import-excel').addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        fetch("{{ route('import.excel') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Fichier importé avec succès!');
                // Actions supplémentaires si nécessaire
            } else {
                alert('Erreur lors de l\'importation.');
            }
        })
        .catch(error => console.log(error));
    });
    $.ajax({
    url: '/clients/get', // Assure-toi que cette route est correcte
    method: 'GET',
    success: function(data) {
        // Met à jour Tabulator avec les nouvelles données
        table.setData(data);
    },
    error: function(err) {
        console.error('Erreur lors de la récupération des données:', err);
    }
});

</script>




<script>
    // Fonction pour soumettre le formulaire d'ajout de client
    document.getElementById('form-saisie-manuel').onsubmit = function(event) {
    event.preventDefault(); // Empêche le rechargement de la page

    const data = new FormData(this); // Récupère les données du formulaire

    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: data
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('message');
        if (data.success) {
            // Afficher un message de succès
            messageDiv.className = 'alert alert-success';
            messageDiv.textContent = 'Client ajouté avec succès !';
            messageDiv.classList.remove('d-none');

            // Ajouter le nouveau client dans Tabulator
            table.addRow(data.client);

        } else {
            // Afficher un message d'erreur
            messageDiv.className = 'alert alert-danger';
            // messageDiv.textContent = 'Erreur lors de l\'ajout du client : ' + data.error;
            messageDiv.classList.remove('d-none');
        }

        // Réinitialiser le formulaire
        this.reset();
    })
    .catch(error => {
        const messageDiv = document.getElementById('message');
        messageDiv.className = 'alert alert-danger';
        messageDiv.textContent = 'Erreur de connexion : ' + error.message;
        messageDiv.classList.remove('d-none');
        console.error('Erreur:', error);
    });
    }       
</script>


<script>
    // Fonction pour supprimer un client
    function deleteclients(id) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce client ?")) {
        fetch(`{{ url('clients') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            const messageDiv = document.getElementById('message');
            if (data.success) {
                // Afficher un message de succès
                messageDiv.className = 'alert alert-success';
                messageDiv.textContent = 'Client supprimé avec succès !';
                messageDiv.classList.remove('d-none');

                // Supprimer le client du tableau Tabulator
                table.deleteRow(id);
            } else {
                // Afficher un message d'erreur
                messageDiv.className = 'alert alert-danger';
                messageDiv.textContent = 'Erreur lors de la suppression.';
                messageDiv.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    }
}



</script>
@endsection
