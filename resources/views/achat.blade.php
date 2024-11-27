@extends('layouts.user_type.auth')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="container mt-4" >
    <div class="row">
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

<!-- Achat -->
<div class="col-md-4 mb-3" id="achat-div" style="margin-top:-70px;margin-left:320px">
    <div class="p-0" style="background-color: transparent; border-radius: 15px; font-size: 0.75rem; display: inline-flex; justify-content: left; align-items: center; height: auto; width: auto;">
        <form id="form-achat" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="Achat">
            <input type="file" name="file" id="file-achat" style="display: none;" onchange="handleFileSelect(event, 'Achat')">
            <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
            <input type="hidden" name="folders_id" value="{{ session()->get('foldersId') }}">

            <!-- Charger Button -->
            <button type="button" class="btn btn-primary btn-sm" style="height: 38px; margin-right: 10px;" onclick="document.getElementById('file-achat').click()">Charger</button>

            <!-- Submit Button (hidden initially) -->
            <button type="submit" style="display: none;" id="submit-achat">Envoyer</button>
        </form>
    </div>
</div>      
<div class="container mt-4">
    <h3>Fichiers Dossiers de la société</h3>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        <!-- Ajouter un Dossier -->
        <div class="col">
            <div class="card shadow-sm" style="cursor: pointer; height: 130px; width: 10rem;" onclick="openCreateFolderForm()">
                <div class="card-body text-center d-flex flex-column justify-content-center align-items-center" style="height: 100%; background-color: #f8f9fa;">
                    <i class="fas fa-plus fa-2x text-primary"></i>
                    <p class="mt-1" style="font-size: 0.8rem;">Ajouter un Dossier</p>
                </div>
            </div>
        </div>

        <!-- Affichage des Dossiers -->
        @if ($folders->isEmpty())
            <p>Aucun dossier trouvé pour cette société.</p>
        @else
            @foreach ($folders as $folder)
                <div class="col" ondblclick="openFile({{ $folder->id }})">
                    <div class="card shadow-sm" style="width: 10rem; height: 130px; cursor: pointer;">
                        <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;">
                            <!-- Icône du Dossier -->
                            <i class="fas fa-folder fa-2x mb-1" style="color: #007bff;"></i>
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">
                                {{ $folder->name }}
                            </h5>
                            <div class="d-flex justify-content-between" style="font-size: 0.8rem;">
                                <!-- Formulaire de suppression -->
                                <form action="{{ route('folder.delete', $folder->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link p-0" title="Supprimer le dossier">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                                <!-- Action de double-clic sur le dossier -->
                                <button class="btn btn-link p-0" onclick="openFile({{ $folder->id }})">
                                    <i class="fas fa-arrow-right text-primary"></i>
                                </button>
                            </div>
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
                        <input type="text" class="form-control form-control-sm" id="folder_name" name="folder_name" required>
                    </div>
                    <input type="hidden" name="societe_id" id="societe_id" value="{{ $societe->id }}">
                    <button type="submit" class="btn btn-primary btn-sm">Créer le Dossier</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Affichage des fichiers -->
<div class="container mt-4">
    <h3>Fichiers Achat</h3>

    @if ($achatFiles->isEmpty())
        <p>Aucun fichier trouvé pour cette société.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach ($achatFiles as $file)
                <div class="col">
                    <div class="card shadow-sm" style="width: 10rem; height: 130px;">
                        <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;">
                            <!-- Icône de fichier -->
                            <i class="fas fa-file-alt fa-2x mb-1" style="color: #28a745;"></i>
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">
                                {{ $file->name }}
                            </h5>
                            <div class="d-flex justify-content-between" style="font-size: 0.8rem;">
                                <!-- Formulaire de suppression -->
                                <form action="{{ route('file.delete', $file->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link p-0" title="Supprimer le fichier">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                                <!-- Bouton de téléchargement -->
                                <a href="{{ route('file.download', $file->id) }}" class="btn btn-link p-0" title="Télécharger">
                                    <i class="fas fa-download text-primary"></i>
                                </a>
                            </div>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Ajout des événements de double-clic pour toutes les sections
    document.getElementById('achat-div').addEventListener('dblclick', function () {
        window.location.href = '{{ route("achat.view") }}';
    });
});

function openCreateFolderForm() {
    var myModal = new bootstrap.Modal(document.getElementById('createFolderModal'));
    myModal.show();
}

function openFile(folderId) {
    window.location.href = '/folder/' + folderId;
}
</script>

@endsection
