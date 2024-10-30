@extends('layouts.user_type.auth')

@section('content')
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

<!-- Modal de Modification du Client -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Modifier le client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <form action="{{ route('clients.update', 'client_id_placeholder') }}" method="POST" id="edit-form">
    @csrf
    @method('PUT')
    <input type="hidden" id="edit-client-id" name="client_id">
                            <div class="mb-3">
                                <label for="edit-compte" class="form-label">Compte</label>
                                <input type="text" class="form-control" id="edit-compte" name="compte" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-intitule" class="form-label">Intitulé</label>
                                <input type="text" class="form-control" id="edit-intitule" name="intitule" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-identifiant_fiscal" class="form-label">Identifiant fiscal</label>
                                <input type="text" class="form-control" id="edit-identifiant_fiscal" name="identifiant_fiscal" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-ICE" class="form-label">ICE</label>
                                <input type="text" class="form-control" id="edit-ICE" name="ICE" maxlength="15" pattern="\d*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-type_client" class="form-label">Type client</label>
                                <input type="text" class="form-control" id="edit-type_client" name="type_client" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Modifier</button>                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>

    function fillEditModal(client) {
        // Remplissage des champs du modal avec les données du client
        document.getElementById('edit-client-id').value = client.id;
        document.getElementById('edit-compte').value = client.compte;
        document.getElementById('edit-intitule').value = client.intitule;
        document.getElementById('edit-identifiant_fiscal').value = client.identifiant_fiscal;
        document.getElementById('edit-ICE').value = client.ICE;
        document.getElementById('edit-type_client').value = client.type_client;

        // Met à jour l'action du formulaire avec l'ID du client
        document.getElementById('edit-form').action = "{{ route('clients.update', '') }}/" + client.id;
    }


    
    // Exemple d'un clic sur un bouton d'édition
// document.querySelectorAll('.edit-client-button').forEach(button => {
//     button.addEventListener('click', function() {
//         const clientId = this.getAttribute('data-client-id'); // Assurez-vous que l'attribut data-client-id est bien défini
//         const client = clients.find(c => c.id == clientId); // Rechercher le client dans votre tableau de clients
//         fillEditModal(client);
//         $('#editModal').modal('show'); // Affiche le modal
//     });
// });

</script>

    <!-- function fillEditModal(client) {
        document.getElementById('edit-client-id').value = client.id;
        document.getElementById('edit-compte').value = client.compte;
        document.getElementById('edit-intitule').value = client.intitule;
        document.getElementById('edit-identifiant_fiscal').value = client.identifiant_fiscal;
        document.getElementById('edit-ICE').value = client.ICE;
        document.getElementById('edit-type_client').value = client.type_client;

        // Met à jour l'action du formulaire avec l'ID du client
        document.getElementById('edit-form').action = "{{ route('clients.update', '') }}/" + client.id;
    } -->

    


<!-- <script>
    // Script pour remplir le modal de modification avec les informations du client
    function fillEditModal(client) {
        document.getElementById('edit-client-id').value = client.id;
        document.getElementById('edit-compte').value = client.compte;
        document.getElementById('edit-intitule').value = client.intitule;
        document.getElementById('edit-identifiant_fiscal').value = client.identifiant_fiscal;
        document.getElementById('edit-ICE').value = client.ICE;
        document.getElementById('edit-type_client').value = client.type_client;
        
        // Changer l'action du formulaire pour inclure l'ID du client
        document.getElementById('edit-form').action = "{{ route('clients.update', '') }}/" + client.id;
    }
</script> -->



<script>
function setEditClient(clientId) {
    // Code pour remplir les champs du modal avec les données du client
    // Par exemple, vous pouvez faire une requête AJAX pour obtenir les détails du client par ID
    // Ensuite, remplissez les champs du formulaire avec ces détails
}
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
            <span class="text-warning" title="Modifier" style="cursor: pointer;" 
                 data-bs-toggle="modal" data-bs-target="#editModal" onclick="fillEditModal({{ json_encode($client) }})"> <!-- Utilisez rowData.id ici -->
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



// document.getElementById('submitEditButton').addEventListener('click', function() {
//     // Récupérez l'ID du client
//     const id = document.getElementById('edit-client-id').value;

//     // Récupérez les données du formulaire
//     const formData = {
//         compte: document.getElementById('edit-compte').value,
//         intitule: document.getElementById('edit-intitule').value,
//         identifiant_fiscal: document.getElementById('edit-identifiant_fiscal').value,
//         ICE: document.getElementById('edit-ice').value,
//         type_client: document.getElementById('edit-type_client').value,
        
//     };

//     // Appel AJAX pour mettre à jour le client
//     $.ajax({
//         url: "{{ route('clients.update', '') }}/" + id,
//         type: 'PUT',
//         data: formData,
//         success: function(response) {
//             if (response.success) {
//                 alert("Client mis à jour avec succès.");
//                 // Mettez à jour la ligne du tableau
//                 table.updateOrAddData([response.client]);
//                 // Fermer le modal
//                 const editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
//                 editModal.hide();
//             } else {
//                 alert("Erreur lors de la mise à jour : " + response.message);
//             }
//         },
//         error: function(xhr, status, error) {
//             console.error("Erreur :", error);
//             alert("Erreur lors de la mise à jour : " + error);
//         }
//     });
// });

// $('#editModal').on('show.bs.modal', function (event) {
//     var button = $(event.relatedTarget); // Bouton qui a déclenché le modal
//     var clientId = button.data('client-id'); // Récupère l'ID du client
    
//     // Faites une requête AJAX pour obtenir les informations du client par ID
//     $.ajax({
//         url: '/clients/' + clientId,
//         method: 'GET',
//         success: function(data) {
//             // Remplissez le formulaire avec les données du client
//             $('#edit-client-id').val(data.id);
//             $('#edit-compte').val(data.compte);
//             $('#edit-intitule').val(data.intitule);
//             $('#edit-identifiant_fiscal').val(data.identifiant_fiscal);
//         }
//     });
// });


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

axios.put(`/clients/${clientId}`, {
    compte: 'Nouveau Compte',
    intitule: 'Nouvel Intitulé',
    identifiant_fiscal: 'Nouvel Identifiant',
    ICE: 'Nouveau ICE',
    type_client: 'Nouveau Type'
}).then(response => {
    console.log(response.data);
}).catch(error => {
    console.error(error);
});

</script>
@endsection
