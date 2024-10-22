<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-k6RqeWeci5ZR/Lv4MR0sA0FfDOM9zKq2G98zE3B1T3RCaF1d1BZ8RlA5HyHDh2tbO4FGz7W1HeJY+P6PzW4OQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

@extends('layouts.user_type.auth')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">
    <div class="container mt-5">
        <h3>Ajouter un Client</h3>

        <!-- Affichage du message de succès -->

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
        <tr>
            <td>{{ $client->compte }}</td>
            <td>{{ $client->intitule }}</td>
            <td>{{ $client->identifiant_fiscal }}</td>
            <td>{{ $client->ICE }}</td>
            <td>{{ $client->type_client }}</td>
            <td>
                <!-- Vos actions ici, par exemple, des boutons pour modifier ou supprimer -->
                  <!-- Icône Modifier -->
                  <span class="text-warning" title="Modifier" style="cursor: pointer;">
                    <i class="fas fa-edit"></i>
                </span>
                <!-- Icône Supprimer -->
                <span class="text-danger" title="Supprimer" style="cursor: pointer;" onclick="confirmDelete('{{ $client->id }}')">
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

<script>
    document.getElementById('form-saisie-manuel').addEventListener('submit', function(e) {
        e.preventDefault(); // Empêche la soumission normale du formulaire

        let formData = new FormData(this); // Récupère les données du formulaire

        fetch('{{ route('client.store') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Ajouter le nouveau client au tableau
                const tableBody = document.getElementById('table-body');
                const newRow = `
                    <tr>
                        <td>${data.client.compte}</td>
                        <td>${data.client.intitule}</td>
                        <td>${data.client.identifiant_fiscal}</td>
                        <td>${data.client.ICE}</td>
                        <td>${data.client.type_client}</td>
                        <td>   <!-- Icône Modifier -->
                <span class="text-warning" title="Modifier" style="cursor: pointer;">
                    <i class="fas fa-edit"></i>
                </span>
                <!-- Icône Supprimer -->
                <span class="text-danger" title="Supprimer" style="cursor: pointer;" onclick="confirmDelete('{{ $client->id }}')">
                    <i class="fas fa-trash"></i>
                </span></td> <!-- Vos actions ici -->
                    </tr>
                `;
                tableBody.innerHTML += newRow; // Ajoute la nouvelle ligne au tableau
                document.getElementById('form-saisie-manuel').reset(); // Réinitialiser le formulaire
            } else {
                console.error('Erreur:', data.error); // Affichez l'erreur dans la console
            }
        })
        .catch(error => console.error('Erreur:', error));
    });
</script>

@endsection
