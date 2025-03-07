@extends('layouts.user_type.auth')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>

<div class="container mt-4">
<a href="{{ route('exercices.show', ['societe_id' => session()->get('societeId')]) }}">Tableau De Board</a>
➢Impot</h6>
    <div class="row"  style="margin-left:50%">
        <div class="d-flex align-items-center mb-3">
 
        <!-- Formulaire de téléchargement -->
            <div class="p-0" style="background-color: transparent; border-radius: 15px; font-size: 0.75rem; display: inline-flex; justify-content: left; align-items: center; height: auto;">
                <form id="form-vente" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="impot">
                    <input type="file" name="file" id="file-vente" style="display: none;" onchange="handleFileSelect(event, 'vente')">
                    <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                    <input type="hidden" name="folders" value="0">

                  

                    <!-- Charger Button -->
                    <button type="button" class="btn btn-primary btn-sm" style="height: 38px; margin-right: 10px;" onclick="document.getElementById('file-vente').click()">Charger</button>

                    <!-- Submit Button (hidden initially) -->
                    <button type="submit" style="display: none;" id="submit-vente">Envoyer</button>
                </form>
            </div>
                   <!-- Formulaire de filtrage -->
                   <form method="GET" action="" class="d-flex me-3">
    <div class="input-group">
        <button class="btn btn-primary btn-sm" type="submit" style="height: 38px; order: -1;">Triée par</button>
        
        <select name="filter_by" class="form-select" style="height: 38px; width: auto; max-width: 200px; font-size: 0.875rem;">
            <option value="name" {{ request()->get('filter_by') == 'name' ? 'selected' : '' }}>Nom</option>
            <option value="date" {{ request()->get('filter_by') == 'date' ? 'selected' : '' }}>Date</option>
        </select>
        
        <select name="order_by" class="form-select" style="height: 38px; width: auto; max-width: 200px; font-size: 0.875rem;">
                        <option value="asc" {{ request()->get('order_by') == 'asc' ? 'selected' : '' }}>↑  </option>
                        <option value="desc" {{ request()->get('order_by') == 'desc' ? 'selected' : '' }}>↓  </option>
                    </select>
    </div>
</form>


        </div>
    </div>
</div>


<input type="hidden" name="folders_id" value="0">
<style>
    .form-select {
        -webkit-appearance: none; /* Pour Safari */
        -moz-appearance: none; /* Pour Firefox */
        appearance: none; /* Pour les autres navigateurs */
        background: none; /* Supprime le fond par défaut */
        border: 1px solid #ccc; /* Ajoute une bordure personnalisée */
        padding: 8px; /* Ajoute un peu de padding */
        font-size: 0.875rem; /* Taille de police */
    }
    
    /* Optionnel : ajouter une image personnalisée pour la flèche */
    .form-select::after {
        content: ''; /* Ajoute un contenu vide */
        background: url('path/to/your/icon.png') no-repeat; /* Remplacez par le chemin de votre icône */
        width: 10px; /* Largeur de l'icône */
        height: 10px; /* Hauteur de l'icône */
        position: absolute; /* Position absolue */
        right: 10px; /* Position à droite */
        top: 50%; /* Centrer verticalement */
        transform: translateY(-50%); /* Ajuste la position */
        pointer-events: none; /* Ignore les événements de souris */
    }
</style>
        

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
                        <label for="name" class="form-label">Nom du Dossier</label>
                        <input type="text" class="form-control form-control-sm" id="fname" name="name" required>
                    </div>
                    <input type="hidden" name="type_folder" value="impot">

                    <input type="hidden" name="societe_id" id="societe_id" value="{{ $societe->id }}">
                    <button type="submit" class="btn btn-primary btn-sm">Créer </button>
                    <button type="reset" class="btn btn-secondary" style="margin-left: 10px;">Réinitialiser</button>

                </form>
            </div>
        </div>
    </div>
</div>
<div class="container mt-5">
<h5>Dossiers</h5>
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-6 g-3">

<!-- Ajouter un Dossier -->
<div class="col">
    <div class="card shadow-sm" style="width: 10rem; height: 100px; cursor: pointer;" onclick="openCreateFolderForm()">
        <div class="card-body text-center d-flex flex-column justify-content-center align-items-center" style="height: 100%; background-color: #f8f9fa;">
            <i class="fas fa-plus fa-2x text-primary"></i>
            <p class="mt-1" style="font-size: 0.8rem;">Ajouter un Dossier</p>
        </div>
    </div>
</div>


@if ($folders->isEmpty())
            <p>Aucun dossier trouvé pour cette société.</p>
        @else
            @foreach ($folders as $folder)
                <div class="col" ondblclick="openFile({{ $folder->id }})">
                    <div class="card shadow-sm" style="width: 10rem; height: 100px; cursor: pointer;">
                        <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;background-color:#6f42c1;border-radius:17px;">
                            <!-- Icône du Dossier -->
                            <i class="fas fa-folder fa-2x mb-1" style="color:rgb(227, 231, 235);"></i>
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;color:rgb(227, 231, 235);">
                                {{ $folder->name }}
                            </h5>
                            <div class="d-flex justify-content-between" style="font-size: 0.8rem;">

<!-- Menu contextuel -->
<div class="dropdown" style="margin-top:-80px;margin-left:145px;">

                    <button class="btn btn-link p-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v" style="color:rgb(227, 231, 235);"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="#" onclick="openRenameModal({{ $folder->id }}, '{{ $folder->name }}')">Renommer</a></li>
                        <li>
                            <form action="{{ route('folder.delete', $folder->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce dossier ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item">Supprimer</button>
                            </form>
                        </li>
                    </ul>
                </div>
                </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
 
</div>
</div>


<!-- Modal pour renommer le dossier -->
<div class="modal fade" id="renameFolderModal" tabindex="-1" aria-labelledby="renameFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renameFolderModalLabel">Renommer le Dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="renameFolderForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="newFolderName" class="form-label">Nouveau Nom du Dossier</label>
                        <input type="text" class="form-control" id="newFolderName" name="name" required>
                    </div>
                    <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                    <button type="submit" class="btn btn-primary">Renommer</button>
                    <button type="reset" class="btn btn-secondary" style="margin-left: 10px;">Réinitialiser</button>

                </form>
            </div>
        </div>
    </div>
</div>
<script>
function openRenameModal(folderId, folderName) {
    document.getElementById('newFolderName').value = folderName;
    document.getElementById('renameFolderForm').action = '/folder/' + folderId; // Met à jour l'action du formulaire avec l'ID du dossier
    var myModal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
    myModal.show();
}
</script>
<!-- Gestion des fichiers de type Vente -->
<div class="container mt-4">
    <h5>Fichiers</h5>
    @if ($files->isEmpty())
        <p>Aucun fichier trouvé pour cette société.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach ($files as $file)
                <div class="col" ondblclick="viewFile({{ $file->id }})">
                    <div class="card shadow-sm" style="width: 13rem; height: 250px;">
                        <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;">
                            <!-- Vérifiez si le fichier est une image -->
                            @if(in_array(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ asset($file->path) }}" alt="{{ $file->name }}" class="img-fluid mb-2" style="overflow-clip-margin: content-box;overflow: clip; height: 200px">
                            <!-- Vérifiez si c'est un PDF -->
                            @elseif(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'pdf')
                                <canvas id="pdf-preview-{{ $file->id }}" class="img-fluid mb-2" style="overflow-clip-margin: content-box;overflow: clip; height: 200px"></canvas>
                            <!-- Vérifiez si c'est un fichier Excel -->
                            @elseif(in_array(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)), ['xls', 'xlsx']))
                                <div id="excel-preview-{{ $file->id }}" class="excel-preview" style="overflow-clip-margin: content-box;overflow: clip; height: 200px"></div>
                            <!-- Vérifiez si c'est un fichier Word -->
                            @elseif(in_array(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)), ['doc', 'docx']))
                                <img src="https://via.placeholder.com/80x100.png?text=Word" class="img-fluid mb-2" style="overflow-clip-margin: content-box;overflow: clip; height: 200px">
                            <!-- Sinon, fichier générique -->
                            @else
                                <img src="https://via.placeholder.com/80x100.png?text=Fichier" class="img-fluid mb-2" style="overflow-clip-margin: content-box;overflow: clip; height: 200px">
                            @endif

                            <!-- Affichage du nom du fichier -->
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;">
                                {{ $file->name }}
                                <!-- Vérifier si des messages non lus existent pour ce fichier -->
                                @if(isset($notifications[$file->id]) && $notifications[$file->id] > 0)
                                    <span class="badge bg-danger" style="font-size: 0.5rem; position: absolute; left: 10px;top:232px;">
                                        {{ $notifications[$file->id] }}
                                    </span>
                                @endif
                            </h5>

                            <div class="d-flex justify-content-between" style="font-size: 0.8rem;">
                <div class="dropdown" style="margin-top:-230px;margin-left:190px;">

                        <button class="btn btn-link p-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="#" onclick="openRenameFileModal({{ $file->id }}, '{{ $file->name }}')">Renommer</a></li>
                            <li>
                                <form action="{{ route('file.delete', $file->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item">Supprimer</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                        </div>
                    </div>
                </div>

                <script>
                    // S'assurer que chaque PDF est traité indépendamment
                    document.addEventListener("DOMContentLoaded", function() {
                        @if(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'pdf')
                            var url = '{{ asset($file->path) }}'; // L'URL du fichier PDF
                            var canvas = document.getElementById('pdf-preview-{{ $file->id }}');
                            var ctx = canvas.getContext('2d');

                            // Utilisation de PDF.js pour afficher la première page du PDF
                            pdfjsLib.getDocument(url).promise.then(function (pdf) {
                                pdf.getPage(1).then(function (page) {
                                    var scale = 0.5; // Réduit l'échelle pour l'aperçu
                                    var viewport = page.getViewport({ scale: scale });
                                    canvas.height = viewport.height;
                                    canvas.width = viewport.width;

                                    // Dessiner le PDF sur le canvas
                                    page.render({
                                        canvasContext: ctx,
                                        viewport: viewport
                                    });
                                });
                            });
                        @elseif(in_array(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)), ['xls', 'xlsx']))
                            var url = '{{ asset($file->path) }}'; // L'URL du fichier Excel
                            var previewElement = document.getElementById('excel-preview-{{ $file->id }}');

                            // Utilisation de SheetJS pour lire et afficher un aperçu du fichier Excel
                            fetch(url)
                                .then(response => response.arrayBuffer())
                                .then(data => {
                                    var workbook = XLSX.read(data, { type: 'array' });

                                    // Prendre la première feuille de calcul
                                    var sheet = workbook.Sheets[workbook.SheetNames[0]];

                                    // Convertir la feuille en un tableau HTML
                                    var html = XLSX.utils.sheet_to_html(sheet, { id: 'excel-preview', editable: false });

                                    // Afficher l'aperçu
                                    previewElement.innerHTML = html;
                                });
                        @endif
                    });
                </script>
            @endforeach
        </div>
    @endif
</div>


<!-- Modal pour renommer le fichier -->
<div class="modal fade" id="renameFileModal" tabindex="-1" aria-labelledby="renameFileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renameFileModalLabel">Renommer le Fichier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="renameFileForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="newFileName" class="form-label">Nouveau Nom du Fichier</label>
                        <input type="text" class="form-control" id="newFileName" name="name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Renommer</button>
                    <button type="reset" class="btn btn-secondary" style="margin-left: 10px;">Réinitialiser</button>

                </form>
            </div>
        </div>
    </div>
</div>
<script>
function openRenameFileModal(fileId, fileName) {
    document.getElementById('newFileName').value = fileName;
    document.getElementById('renameFileForm').action = '/file/' + fileId; // Met à jour l'action du formulaire avec l'ID du fichier
    var myModal = new bootstrap.Modal(document.getElementById('renameFileModal'));
    myModal.show();
}
</script>
<script>
    function downloadFile(fileId) {
 
 window.location.href = '/file/download/' + fileId;
}


function handleFileSelect(event, type) {
    const fileInput = document.getElementById('file-' + type.toLowerCase());
    const formId = 'form-' + type.toLowerCase();
    
    if (!fileInput.files.length) {
        alert("Veuillez sélectionner un fichier.");
        return;
    }

    document.getElementById(formId).submit();
}

function viewFile(fileId) {
        window.location.href = '/achat/view/' + fileId;
    }
    function openCreateFolderForm() {
    var myModal = new bootstrap.Modal(document.getElementById('createFolderModal'));
    myModal.show();
    document.getElementById('createFolderModal').addEventListener('shown.bs.modal', function () {
        document.getElementById('fname').focus(); // Met le focus sur le champ "Nom du Dossier"
    });
}
function openFile(folderId) {
    setFolderId(folderId);  
    window.location.href = '/folderImpot/' + folderId;
}

function setFolderId(folderId) {
    document.querySelector('input[name="folders_id"]').value = folderId;
}
</script>
@if(session('alert'))
    <script>
        alert("{{ session('alert') }}");
    </script>
@endif
@endsection