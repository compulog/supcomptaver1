@extends('layouts.user_type.auth')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="container mt-4">
    <div class="row">
        <!-- Achat -->
        <div class="col-md-4 mb-3" id="achat-div">
            <div class="p-0" style="background-color: transparent; border-radius: 15px; font-size: 0.75rem; display: inline-flex; justify-content: center; align-items: center; height: auto; width: auto;">
                <form id="form-achat" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="Achat">
                    <input type="file" name="file" id="file-achat" style="display: none;" onchange="handleFileSelect(event, 'Achat')">
                    <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">

                    <!-- Charger Button -->
                    <button type="button" class="btn btn-light btn-sm" style="background-color: #a20a7f; border: 1px solid white; border-radius: 10px; color: white; padding: 0.375rem 0.75rem;" onclick="document.getElementById('file-achat').click()">Charger</button>

                    <!-- Submit Button (hidden initially) -->
                    <button type="submit" style="display: none;" id="submit-achat">Envoyer</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Filtrage des dossiers par Nom ou Date -->
<form method="GET" action="" class="mb-3">
    <div class="input-group">
        <select name="filter_by" class="form-select" style="height: 38px; width: auto; max-width: 200px; font-size: 0.875rem;">
            <option value="name" {{ request()->get('filter_by') == 'name' ? 'selected' : '' }}>Filtrer par Nom</option>
            <option value="date" {{ request()->get('filter_by') == 'date' ? 'selected' : '' }}>Filtrer par Date</option>
        </select>
        <button class="btn btn-primary btn-sm" type="submit" style="height: 38px;">Filtrer</button>
    </div>
</form>

<div class="container mt-4">
    <h3>Fichiers Dossiers de la société</h3>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <!-- Div pour ajouter un nouveau dossier -->
        <div class="col">
            <div class="card shadow-sm" style="width: 12rem; height: 6rem; display: flex; justify-content: center; align-items: center; cursor: pointer;" onclick="openCreateFolderForm()">
                <div class="card-body text-center p-2">
                    <i class="fas fa-plus fa-3x text-primary"></i>
                    <p class="mt-2">Ajouter un Dossier</p>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($folders->isEmpty())
            <p>Aucun dossier trouvé pour cette société.</p>
        @else
            @foreach ($folders as $folder)
                <div class="col" ondblclick="openFile({{ $folder->id }})">
                    <div class="card shadow-sm" style="width: 12rem; height: 6rem;">
                        <div class="card-body text-center p-2 d-flex flex-column justify-content-between">
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">
                                {{ $folder->name }}
                            </h5>
                            <!-- Formulaire de suppression de dossier avec icône uniquement -->
                            <form action="{{ route('folder.delete', $folder->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <i class="fas fa-trash text-danger mt-2" style="cursor: pointer;" title="Supprimer le dossier" onclick="this.closest('form').submit();"></i>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<!-- Modal pour la création de dossier -->
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createFolderModalLabel">Créer un Nouveau Dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('folder.create') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="folder_name" class="form-label">Nom du Dossier</label>
                        <input type="text" class="form-control" id="folder_name" name="folder_name" required>
                    </div>
                    <input type="hidden" name="societe_id" id="societe_id" value="{{ $societe->id }}">
                    <button type="submit" class="btn btn-primary">Créer le Dossier</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">

    @if ($achatFiles->isEmpty())
        <p>Aucun fichier trouvé pour cette société.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach ($achatFiles as $file)
                <div class="col">
                    <div class="card shadow-sm" style="width: 12rem; height: 6rem;">
                        <div class="card-body text-center p-2 d-flex flex-column justify-content-between">
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">
                                {{ $file->name }}
                            </h5>
                          
                            <!-- Formulaire de suppression de fichier avec icône uniquement -->
                            <form action="{{ route('file.delete', $file->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <i class="fas fa-trash text-danger mt-2" style="cursor: pointer;" title="Supprimer le fichier" onclick="this.closest('form').submit();"></i>
                            </form>
                              <!-- Bouton de téléchargement avec icône -->
                              <a href="{{ route('file.download', $file->id) }}" class="btn btn-link mt-1" style="font-size: 1.2rem; color: #007bff;" title="Télécharger">
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Ajout des événements de double-clic pour toutes les sections
    document.getElementById('achat-div').addEventListener('dblclick', function () {
        window.location.href = '{{ route("achat.view") }}';
    });
});

function handleFileSelect(event, type) {
    const fileInput = document.getElementById(`file-${type.toLowerCase()}`);
    const formId = `form-${type.toLowerCase()}`;
    
    if (!fileInput.files.length) {
        alert("Veuillez sélectionner un fichier.");
        return;
    }

    document.getElementById(formId).submit();
}

function openCreateFolderForm() {
    var myModal = new bootstrap.Modal(document.getElementById('createFolderModal'));
    myModal.show();
}

function openFile(folderId) {
    window.location.href = '/folder/' + folderId;
}
</script>

@endsection
