@extends('layouts.user_type.auth')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>

<div class="container mt-4">
<a href="{{ route('exercices.show', ['societe_id' => session()->get('societeId')]) }}">Tableau De Board</a>
➢Dossier Permanant</h6>
    <div class="row"  style="margin-left:400px">
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
                <form id="form-vente" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="Dossier_permanant">
                    <input type="file" name="file" id="file-vente" style="display: none;" onchange="handleFileSelect(event, 'vente')">
                    <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                    
                    <input type="hidden" name="folders_id" value="0">

                    <!-- Charger Button -->
                    <button type="button" class="btn btn-primary btn-sm" style="height: 38px; margin-right: 10px;" onclick="document.getElementById('file-vente').click()">Charger</button>

                    <!-- Submit Button (hidden initially) -->
                    <button type="submit" style="display: none;" id="submit-vente">Envoyer</button>
                </form>
            </div>
        </div>
    </div>
</div>
        <!-- Ajouter un Dossier -->
        <div class="col">
            <div class="card shadow-sm" style="width: 10rem; height: 100px; cursor: pointer;" onclick="openCreateFolderForm()">
                <div class="card-body text-center d-flex flex-column justify-content-center align-items-center" style="height: 100%; background-color: #f8f9fa;">
                    <i class="fas fa-plus fa-2x text-primary"></i>
                    <p class="mt-1" style="font-size: 0.8rem;">Ajouter un Dossier</p>
                </div>
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
                        <label for="name" class="form-label">Nom du Dossier</label>
                        <input type="text" class="form-control form-control-sm" id="fname" name="name" required>
                    </div>
                    <input type="hidden" name="type_folder" value="Dossier_permanant">

                    <input type="hidden" name="societe_id" id="societe_id" value="{{ $societe->id }}">
                    <button type="submit" class="btn btn-primary btn-sm">Créer le Dossier</button>
                </form>
            </div>
        </div>
    </div>
</div>

 
@if ($folders->isEmpty())
            <p>Aucun dossier trouvé pour cette société.</p>
        @else
            @foreach ($folders as $folder)
                <div class="col" ondblclick="openFile({{ $folder->id }})">
                    <div class="card shadow-sm" style="width: 10rem; height: 100px; cursor: pointer;">
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
                                    <button type="submit" class="btn btn-link p-0" title="Supprimer le dossier" style="margin-top:-115px;margin-left:130px;">
                                        <i class="fas fa-times" style="color:rgb(227, 231, 235);"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
 

   <!-- Gestion des fichiers de type Vente -->
   <div class="container mt-4">
@if ($files->isEmpty())
    <p>Aucun fichier trouvé pour cette société.</p>
@else
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
    @foreach ($files as $file)
        <div class="col" ondblclick="viewFile({{ $file->id }})">
            <div class="card shadow-sm" style="width: 13rem; height: 250px;">
                <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;">
                    <!-- Vérifiez si le fichier est un PDF -->
                    @if(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'pdf')
                        <canvas id="pdf-preview-{{ $file->id }}" class="img-fluid mb-2" style="overflow-clip-margin: content-box;overflow: clip; height: 200px"></canvas>
                    @elseif(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'xlsx' || strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'xls')
                        <div id="excel-preview-{{ $file->id }}" class="excel-preview" style="overflow-clip-margin: content-box;overflow: clip; height: 200px"></div>
                    @else
                        <img src="{{ $file->preview }}" alt="{{ $file->name }}" class="img-fluid mb-2" style="overflow-clip-margin: content-box;overflow: clip; height: 200px">
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
                        <form action="{{ route('file.delete', $file->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link p-0" title="Supprimer le fichier" style="margin-top:-450px;margin-left:180px;">
                                <i class="fas fa-times" style="color:#33333333;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
 
        <script>
            // S'assurer que chaque PDF est traité indépendamment
            document.addEventListener("DOMContentLoaded", function() {
                @if(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'pdf')
                    var url = '{{ asset('storage/' . $file->path) }}'; // L'URL du fichier PDF
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
                @elseif(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'xlsx' || strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'xls')
                    var url = '{{ asset('storage/' . $file->path) }}'; // L'URL du fichier Excel
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
}
function openFile(folderId) {
    setFolderId(folderId);  
    window.location.href = '/folderDossier_permanant/' + folderId;
}

function setFolderId(folderId) {
    document.querySelector('input[name="folders_id"]').value = folderId;
}
</script>
@endsection