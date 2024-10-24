<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-k6RqeWeci5ZR/Lv4MR0sA0FfDOM9zKq2G98zE3B1T3RCaF1d1BZ8RlA5HyHDh2tbO4FGz7W1HeJY+P6PzW4OQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

@extends('layouts.user_type.auth')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">
    <div class="container mt-5">
        <h3>Ajouter un Client</h3>

        <!-- Affichage du message de succès ou d'erreur -->
        <div id="message" class="alert d-none" role="alert"></div>

        <!-- Tabs pour choisir entre saisie manuelle et importation Excel -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="manual-tab" data-bs-toggle="tab" href="#manual" role="tab" aria-controls="manual" aria-selected="true">Nouveau client</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="import-tab" data-bs-toggle="tab" href="#import" role="tab" aria-controls="import" aria-selected="false">Importer un fichier Excel</a>
            </li>
        </ul>

        <!-- Contenu des Tabs -->
        <div class="tab-content" id="myTabContent">
            <!-- Formulaire de saisie manuelle -->
            <div class="tab-pane fade show active" id="manual" role="tabpanel" aria-labelledby="manual-tab">
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="identifiant_fiscal" class="form-label">Identifiant fiscal</label>
                            <input type="text" class="form-control" name="identifiant_fiscal" placeholder="Identifiant fiscal" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ice" class="form-label">ICE</label>
                            <input type="text" class="form-control" name="ICE" placeholder="ICE" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type_client" class="form-label">Type client</label>
                            <input type="text" class="form-control" name="type_client" placeholder="Type client" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>

            <!-- Formulaire d'importation Excel -->
            <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
                <form id="form-import-excel" class="mt-3" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="compte-import" class="form-label">Compte</label>
                            <input type="text" class="form-control" id="compte-import" name="compte-import" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="intitule-import" class="form-label">Intitulé</label>
                            <input type="text" class="form-control" id="intitule-import" name="intitule-import" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="identifiant_fiscal-import" class="form-label">Identifiant fiscal</label>
                            <input type="text" class="form-control" id="identifiant_fiscal-import" name="identifiant_fiscal-import" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type_client-import" class="form-label">Type client</label>
                            <input type="text" class="form-control" id="type_client-import" name="type_client-import" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="excel-file" class="form-label">Importer un fichier Excel</label>
                        <input type="file" class="form-control" id="excel-file" name="excel-file" accept=".xlsx, .xls" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </form>
            </div>
        </div>

        <!-- Tableau avec recherche -->
        <div class="container mt-4">
            <h3>Liste des clients</h3>
            <input class="form-control mb-3" id="searchInput" type="text" placeholder="Rechercher...">

            <table class="table table-bordered" id="table-list">
                <thead>
                    <tr>
                        <th>Compte</th>
                        <th>Intitulé</th>
                        <th>Identifiant fiscal</th>
                        <th>ICE</th>
                        <th>Type client</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    @foreach($clients as $client)
                    <tr data-id="{{ $client->id }}">
                        <td>{{ $client->compte }}</td>
                        <td>{{ $client->intitule }}</td>
                        <td>{{ $client->identifiant_fiscal }}</td>
                        <td>{{ $client->ICE }}</td>
                        <td>{{ $client->type_client }}</td>
                        <td>
                            <span class="text-warning" title="Modifier" style="cursor: pointer;" onclick="openEditModal({{ json_encode($client) }})">
                                <i class="fas fa-edit"></i>
                            </span>
                            <span class="text-danger" title="Supprimer" style="cursor: pointer;" onclick="deleteclients({{ $client->id }})">
                                <i class="fas fa-trash"></i>
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal pour modifier les informations du client -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Modifier le Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-client">
                    @csrf
                    <input type="hidden" name="id" id="edit-client-id">
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
                        <label for="edit-ice" class="form-label">ICE</label>
                        <input type="text" class="form-control" id="edit-ice" name="ICE" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-type_client" class="form-label">Type client</label>
                        <input type="text" class="form-control" id="edit-type_client" name="type_client" required>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="updateClient()">Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>
</div>
<<!-- Modal pour modifier les informations du client -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Modifier le Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
                    <label for="edit-ice" class="form-label">ICE</label>
                    <input type="text" class="form-control" id="edit-ice" name="ICE" required>
                </div>
                <div class="mb-3">
                    <label for="edit-type_client" class="form-label">Type client</label>
                    <input type="text" class="form-control" id="edit-type_client" name="type_client" required>
                </div>
                <button type="button" class="btn btn-primary" onclick="updateClient()">Mettre à jour</button>
            </div>
        </div>
    </div>
</div>


<script>
    function updateClient() {
    const id = document.getElementById('edit-client-id').value; // Assurez-vous que l'ID est bien récupéré
    const data = {
        compte: document.getElementById('edit-compte').value,
        intitule: document.getElementById('edit-intitule').value,
        identifiant_fiscal: document.getElementById('edit-identifiant_fiscal').value,
        ICE: document.getElementById('edit-ice').value,
        type_client: document.getElementById('edit-type_client').value
    };

    fetch(`/clients/${id}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettez à jour la ligne dans le tableau
            const row = document.querySelector(`#table-body tr[data-id="${id}"]`);
            if (row) {
                row.cells[0].textContent = data.client.compte;
                row.cells[1].textContent = data.client.intitule;
                row.cells[2].textContent = data.client.identifiant_fiscal;
                row.cells[3].textContent = data.client.ICE;
                row.cells[4].textContent = data.client.type_client;
            }
            // Fermez le modal
            var myModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
            myModal.hide();
        } else {
            alert("Erreur lors de la mise à jour : " + data.error);
        }
    })
    .catch(error => console.error('Erreur:', error));
}

   $.ajax({
    url: '/clients/' + clientId, // Remplacez clientId par l'ID du client à mettre à jour
    method: 'PUT',
    data: {
        compte: $('#compte').val(),
        intitule: $('#intitule').val(),
        identifiant_fiscal: $('#identifiant_fiscal').val(),
        ICE: $('#ICE').val(),
        type_client: $('#type_client').val(),
        _token: $('meta[name="csrf-token"]').attr('content') // Assurez-vous que le token CSRF est inclus
    },
    success: function(response) {
        if (response.success) {
            alert('Client mis à jour avec succès');
            // Mettez à jour l'affichage ou redirigez l'utilisateur si nécessaire
        } else {
            alert('Erreur : ' + response.error);
        }
    },
    error: function(xhr) {
        alert('Erreur de mise à jour : ' + xhr.responseText);
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

                // Ajouter le nouveau client à la liste (optionnel)
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-id', data.client.id);
                newRow.innerHTML = `
                    <td>${data.client.compte}</td>
                    <td>${data.client.intitule}</td>
                    <td>${data.client.identifiant_fiscal}</td>
                    <td>${data.client.ICE}</td>
                    <td>${data.client.type_client}</td>
                    <td>
                        <span class="text-warning" title="Modifier" style="cursor: pointer;" onclick="openEditModal(${JSON.stringify(data.client)})">
                            <i class="fas fa-edit"></i>
                        </span>
                        <span class="text-danger" title="Supprimer" style="cursor: pointer;" onclick="deleteclients(${data.client.id})">
                            <i class="fas fa-trash"></i>
                        </span>
                    </td>
                `;
                document.getElementById('table-body').appendChild(newRow);
            } else {
                // Afficher un message d'erreur
                messageDiv.className = 'alert alert-danger';
                messageDiv.textContent = 'Erreur lors de l\'ajout du client : ' + data.error;
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
    };
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

                // Ajouter le nouveau client à la liste (optionnel)
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-id', data.client.id);
                newRow.innerHTML = `
                    <td>${data.client.compte}</td>
                    <td>${data.client.intitule}</td>
                    <td>${data.client.identifiant_fiscal}</td>
                    <td>${data.client.ICE}</td>
                    <td>${data.client.type_client}</td>
                    <td>
                        <span class="text-warning" title="Modifier" style="cursor: pointer;" onclick="openEditModal(${JSON.stringify(data.client)})">
                            <i class="fas fa-edit"></i>
                        </span>
                        <span class="text-danger" title="Supprimer" style="cursor: pointer;" onclick="deleteclients(${data.client.id})">
                            <i class="fas fa-trash"></i>
                        </span>
                    </td>
                `;
                document.getElementById('table-body').appendChild(newRow);
            } else {
                // Afficher un message d'erreur
                messageDiv.className = 'alert alert-danger';
                messageDiv.textContent = 'Erreur lors de l\'ajout du client : ' + data.error;
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
    };
</script>

<script>
    // Fonction pour supprimer un client
    function deleteclients(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce client ?")) {
            fetch(`/clients/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({}) // Le corps peut être vide ici
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Sélectionne la ligne à supprimer en utilisant l'attribut data-id
                    const row = document.querySelector(`#table-body tr[data-id="${id}"]`);
                    if (row) {
                        row.remove(); // Retire la ligne du tableau
                    } else {
                        console.error("Erreur : la ligne à supprimer n'a pas été trouvée.");
                    }
                } else {
                    alert("Erreur lors de la suppression : " + data.error);
                }
            })
            .catch(error => console.error('Erreur:', error));
        }
    }

    // Fonction pour ouvrir le modal d'édition
    function openEditModal(client) {
        document.getElementById('edit-client-id').value = client.id;
        document.getElementById('edit-compte').value = client.compte;
        document.getElementById('edit-intitule').value = client.intitule;
        document.getElementById('edit-identifiant_fiscal').value = client.identifiant_fiscal;
        document.getElementById('edit-ice').value = client.ICE;
        document.getElementById('edit-type_client').value = client.type_client;

        // Affiche le modal
        var myModal = new bootstrap.Modal(document.getElementById('editModal'));
        myModal.show();
    }

    // Fonction pour mettre à jour les informations du client
    function updateClient() {
        const id = document.getElementById('edit-client-id').value;
        const data = {
            compte: document.getElementById('edit-compte').value,
            intitule: document.getElementById('edit-intitule').value,
            identifiant_fiscal: document.getElementById('edit-identifiant_fiscal').value,
            ICE: document.getElementById('edit-ice').value,
            type_client: document.getElementById('edit-type_client').value
        };

        fetch(`/clients/${id}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Met à jour la ligne du tableau avec les nouvelles valeurs
                const row = document.querySelector(`#table-body tr[data-id="${id}"]`);
                if (row) {
                    row.cells[0].textContent = data.client.compte;
                    row.cells[1].textContent = data.client.intitule;
                    row.cells[2].textContent = data.client.identifiant_fiscal;
                    row.cells[3].textContent = data.client.ICE;
                    row.cells[4].textContent = data.client.type_client;
                }
                // Ferme le modal
                var myModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                myModal.hide();
            } else {
                alert("Erreur lors de la mise à jour : " + data.error);
            }
        })
        .catch(error => console.error('Erreur:', error));
    }
</script>
@endsection
