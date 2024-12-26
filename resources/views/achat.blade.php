@extends('layouts.user_type.auth')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<div class="container mt-4">
    <h6>Achat</h6>
    <div class="row"  style="margin-left:500px">
        <div class="d-flex align-items-center mb-3">
            <!-- Formulaire de filtrage -->
            <form method="GET" action="" class="d-flex me-3">
                <div class="input-group">
                    <select name="filter_by" class="form-select" style="height: 38px; width: auto; max-width: 200px; font-size: 0.875rem;">
                        <option value="name" {{ request()->get('filter_by') == 'name' ? 'selected' : '' }}>Filtrer par Nom</option>
                        <option value="date" {{ request()->get('filter_by') == 'date' ? 'selected' : '' }}>Filtrer par Date</option>
                    </select>
                    <button class="btn btn-primary btn-sm" type="submit" style="height: 38px;">Filtrer</button>
                </div>
            </form>

            <!-- Formulaire de téléchargement -->
            <div class="p-0" style="background-color: transparent; border-radius: 15px; font-size: 0.75rem; display: inline-flex; justify-content: left; align-items: center; height: auto;">
                <form id="form-achat" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="Achat">
                    <input type="file" name="file" id="file-achat" style="display: none;" onchange="handleFileSelect(event, 'Achat')">
                    <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                    <input type="hidden" name="folders_id" value="0">

                    <!-- Charger Button -->
                    <button type="button" class="btn btn-primary btn-sm" style="height: 38px; margin-right: 10px;" onclick="document.getElementById('file-achat').click()">Charger</button>

                    <!-- Submit Button (hidden initially) -->
                    <button type="submit" style="display: none;" id="submit-achat">Envoyer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
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
                        <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;background-color:#007bff;border-radius:17px;">
                            <!-- Icône du Dossier -->
                            <i class="fas fa-folder fa-2x mb-1" style="color:rgb(227, 231, 235);"></i>
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;color:rgb(227, 231, 235);">
                                {{ $folder->name }}
                            </h5>
                            <div class="d-flex justify-content-between" style="font-size: 0.8rem;">
                                <form action="{{ route('folder.delete', $folder->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link p-0" title="Supprimer le dossier" style="margin-top:-180px;margin-left:130px;">
                                        <i class="fas fa-times" style="color:rgb(227, 231, 235);"></i>
                                    </button>
                                </form>
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

<!-- Gestion des fichiers de type achat -->
<div class="container mt-4">
    @if ($achatFiles->isEmpty())
        <p>Aucun fichier trouvé pour cette société.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach ($achatFiles as $file)
                <div class="col" ondblclick="downloadFile({{ $file->id }})">
                    <div class="card shadow-sm" style="width: 10rem; height: 130px;">
                        <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;">
                            <img src="{{ $file->preview }}" alt="{{ $file->name }}" class="img-fluid mb-2" style="max-height: 80px; object-fit: contain;">
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">
                                {{ $file->name }}
                            </h5>
                            <div class="d-flex justify-content-between" style="font-size: 0.8rem;">
                                <form action="{{ route('file.delete', $file->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link p-0" title="Supprimer le fichier" style="margin-top:-230px;margin-left:130px;">
                                        <i class="fas fa-times" style="color:#33333333;"></i>
                                    </button>
                                </form>
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
    const fileInput = document.getElementById('file-' + type.toLowerCase());
    const formId = 'form-' + type.toLowerCase();
    
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
    setFolderId(folderId);  // Mettre à jour l'ID du dossier avant l'ouverture
    window.location.href = '/folder/' + folderId;
}

function setFolderId(folderId) {
    document.querySelector('input[name="folders_id"]').value = folderId;
}

function downloadFile(fileId) {
    window.location.href = '/file/download/' + fileId;
}
</script>

@endsection
