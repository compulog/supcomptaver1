
@extends('layouts.user_type.auth')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.12.313/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.12.313/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
 
<!-- Balise meta CSRF -->
<meta name="csrf-token" content="{{ csrf_token() }}">
 
<!-- Liens pour les scripts et styles -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    .modal-content-bg {
        background-color: transparent;
    }

     
    .excel-preview {
    width: 100%; /* Prend toute la largeur du pop-up */
    margin: 20px auto; /* Centre le conteneur */
    border: 1px solid #ddd; /* Bordure autour du conteneur */
    border-radius: 8px; /* Coins arrondis */
    background-color: #f9f9f9; /* Couleur de fond légèrement grise */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); /* Ombre plus prononcée */
    overflow-x: auto; /* Ajoute un défilement horizontal si nécessaire */
    max-height: 70vh; /* Limite la hauteur pour éviter de dépasser le pop-up */
    overflow-y: auto; /* Ajoute un défilement vertical si nécessaire */
}
.excel-preview table {
    width: 100%; /* Le tableau prend toute la largeur du conteneur */
    border-collapse: collapse; /* Supprime les espaces entre les cellules */
}

.excel-preview th, .excel-preview td {
    border: 1px solid #ccc; /* Bordure grise claire */
    padding: 10px; /* Espacement interne */
    text-align: left; /* Alignement à gauche */
    font-size: 14px; /* Taille de la police */
}

.excel-preview th {
    background-color: #007bff; /* Couleur de fond des en-têtes */
    color: white; /* Couleur du texte des en-têtes */
}

.excel-preview tr:nth-child(even) {
    background-color: #f2f2f2; /* Couleur de fond pour les lignes paires */
}

.excel-preview tr:hover {
    background-color: #e0e0e0; /* Couleur de fond au survol */
}
.excel-preview {
    width: 100%; 
    margin: 20px auto; 
    border: 1px solid #ddd; 
    border-radius: 8px; 
    background-color: #f9f9f9; 
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
    overflow-x: auto; 
    max-height: 70vh; 
    overflow-y: auto; 
}

.excel-preview table {
    width: 100%; 
    border-collapse: collapse; 
}

.excel-preview th, .excel-preview td {
    border: 1px solid #ccc; 
    padding: 10px; 
    text-align: left; 
    font-size: 14px; 
}

.excel-preview th {
    background-color: #007bff; 
    color: white; 
}

.excel-preview tr:nth-child(even) {
    background-color: #f2f2f2; 
}

.excel-preview tr:hover {
    background-color: #e0e0e0; 
}
/* Styles pour le modal d'affichage de fichier */
.modal .excel-preview {
    width: 100%; /* Prend toute la largeur du modal */
    margin: 20px auto; /* Centre le conteneur */
    border: 1px solid #ddd; /* Bordure autour du conteneur */
    border-radius: 8px; /* Coins arrondis */
    background-color: #f9f9f9; /* Couleur de fond légèrement grise */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); /* Ombre plus prononcée */
    max-height: 70vh; /* Limite la hauteur pour éviter de dépasser le modal */
    overflow-x: auto; /* Ajoute un défilement horizontal si nécessaire */
    overflow-y: auto; /* Ajoute un défilement vertical si nécessaire */
}

.modal .excel-preview table {
    width: 100%; /* Le tableau prend toute la largeur du conteneur */
    border-collapse: collapse; /* Supprime les espaces entre les cellules */
}

.modal .excel-preview th, .modal .excel-preview td {
    border: 1px solid #ccc; /* Bordure grise claire */
    padding: 10px; /* Espacement interne */
    text-align: left; /* Alignement à gauche */
    font-size: 14px; /* Taille de la police */
}

.modal .excel-preview th {
    background-color: #007bff; /* Couleur de fond des en-têtes */
    color: white; /* Couleur du texte des en-têtes */
}

.modal .excel-preview tr:nth-child(even) {
    background-color: #f2f2f2; /* Couleur de fond pour les lignes paires */
}

.modal .excel-preview tr:hover {
    background-color: #e0e0e0; /* Couleur de fond au survol */
}
#filePreviewContent {
    overflow-y: auto;
 }
 .message {
    margin-bottom: 20px;
    background-color: #e9ecef;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.message p {
    margin: 0;
    font-size: 14px;
    color: #333;
}

.message-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 5px;
}

.message-actions button {
    background: none;
    border: none;
    cursor: pointer;
    color: #1a73e8;
    font-size: 16px;
    transition: color 0.3s;
}

.message-actions button:hover {
    color: #0c59b3;
}
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


    .chat-box {
        position: fixed;
        right: 20px;
        top: 20px; /* Ajustez la position selon vos besoins */
         
        width: 300px;
        height: auto; /* Permet à la boîte de s'ajuster à son contenu */
        background-color: transparent;
        /* padding: 20px; */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        /* overflow-y: auto; */
        z-index: 999;
    }

    .chat-box h5 {
        font-size: 20px;
        color: #fff;
        margin-bottom: 20px;
        text-align: center;
    }

    .chat-box form textarea {
        width: 100%;
        height: 60px;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #ddd;
        font-size: 14px;
        margin-bottom: -5px;
        transition: border-color 0.3s;
    }

    .chat-box form textarea:focus {
        border-color: #1a73e8;
    }

    .chat-box form button {
        width: 100%;
        padding: 12px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .chat-box form button:hover {
        background-color: #218838;
    }
</style>
<div class="container mt-4">
    <h6 style="margin-top:-60px">
    <a href="{{ route('exercices.show', ['societe_id' => session()->get('societeId')]) }}">Tableau De Board</a>
    ➢
    <a href="{{ route('caisse.view') }}">Caisse</a>
    ➢

    @php
        $currentFolder = $folder;
        $breadcrumbs = [];

        while ($currentFolder) {
            $breadcrumbs[] = $currentFolder;
            $currentFolder = $currentFolder->parent;  
        }

        $breadcrumbs = array_reverse($breadcrumbs); 
    @endphp

    <!-- Affichage des trois derniers dossiers -->
    @foreach ($breadcrumbs as $index => $breadcrumb)
        @if ($index >= count($breadcrumbs) - 3)
            <a href="{{ route('folder.show', ['id' => $breadcrumb->id]) }}">{{ $breadcrumb->name }}</a>
            @if (!$loop->last) ➢ @endif
        @endif
    @endforeach

    <!-- Ajouter les trois points après les trois derniers dossiers -->
    @if (count($breadcrumbs) > 3)
        <span id="showMore" style="cursor: pointer; color: blue;" onclick="toggleMenu()">...</span>
    @endif

    <!-- Afficher le menu déroulant pour les dossiers supplémentaires -->
    @if (count($breadcrumbs) > 3)
        <div class="mt-2" id="folderMenuWrapper" style="display: none;">
            <ul id="folderMenu" class="list-unstyled">
                @foreach ($breadcrumbs as $index => $breadcrumb)
                    @if ($index < count($breadcrumbs) - 3)  <!-- Afficher les dossiers plus anciens -->
                        <li>
                            <a href="{{ route('folder.show', ['id' => $breadcrumb->id]) }}" >{{ $breadcrumb->name }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif
</h6>

<!-- Script pour afficher/cacher le menu déroulant et gérer l'affichage -->
<script>
    function toggleMenu() {
        // Basculer la visibilité des éléments
        var menuWrapper = document.getElementById('folderMenuWrapper');
        
        // Si le menu est caché, on l'affiche
        if (menuWrapper.style.display === 'none') {
            menuWrapper.style.display = 'block';
        } else {
            // Sinon, on le cache
            menuWrapper.style.display = 'none';
        }
    }

    // Fermer le menu si l'utilisateur clique en dehors de la zone
    document.addEventListener('click', function(event) {
        var showMore = document.getElementById('showMore');
        var menuWrapper = document.getElementById('folderMenuWrapper');
        
        // Vérifier si le clic est en dehors de la zone des trois points ou du menu
        if (!showMore.contains(event.target) && !menuWrapper.contains(event.target)) {
            // Fermer le menu déroulant
            menuWrapper.style.display = 'none';
        }
    });

    // Ajouter une transition pour l'animation de l'affichage
    document.getElementById('folderMenuWrapper').style.transition = "all 0.3s ease-in-out";
</script>





    <div class="row"   style="margin-left:50%">

        <!-- Conteneur flexible pour aligner les éléments sur la même ligne -->
        <div class="d-flex align-items-center mb-3">
        <div class="p-0" style="background-color: transparent; border-radius: 15px; font-size: 0.75rem; display: inline-flex; justify-content: center; align-items: center; height: auto; width: auto;">
        <form id="form-caisse" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="caisse">
                <input type="file" name="file" id="file-caisse" style="display: none;" onchange="handleFileSelect(event, 'caisse')">
                <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                <input type="hidden" name="folders" value="{{ session()->get('foldersId') }}">

                <!-- Dropdown personnalisé sans flèche -->
                <div class="dropdown">
                    <button 
                        class="btn btn-primary btn-sm" 
                        type="button" 
                        id="dropdownMenuButtonCaisse" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false"
                        style="border: 1px solid white; border-radius: 10px; color: white; width:100px;">
                        Charger
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonCaisse">
                        <li><a class="dropdown-item" href="#" onclick="handleUploadCaisse('importer')">Importer</a></li>
                        <!-- <li><a class="dropdown-item" href="#" onclick="handleUploadCaisse('scanner')">Scanner</a></li> -->
                        <li><a class="dropdown-item" href="#" onclick="handleUploadCaisse('fusionner')">Fusionner</a></li>
                    </ul>
                </div>

                <button type="submit" style="display: none;" id="submit-caisse">Envoyer</button>
            </form>
            </div>

            <!-- Formulaire de téléchargement (Charger) -->
            <div class="p-0" style="background-color: transparent; border-radius: 15px; font-size: 0.75rem; display: inline-flex; justify-content: center; align-items: center; height: auto; width: auto;">
               <!-- Formulaire de filtrage -->
               <form method="GET" action="{{ url()->current() }}" class="d-flex me-3">
    <div class="input-group">
        <button class="btn btn-primary btn-sm" type="submit" style="height: 38px; order: -1;">Triée par</button>
        
        <!-- Le select pour le tri -->
        <select name="filter_by" class="form-select" style="height: 38px; width: auto; max-width: 200px; font-size: 0.875rem;">
            <option value="name" {{ request()->get('filter_by') == 'name' ? 'selected' : '' }}>Nom</option>
            <option value="date" {{ request()->get('filter_by') == 'date' ? 'selected' : '' }}>Date</option>
        </select>
        
        <!-- Le select pour l'ordre (ascendant ou descendant) -->
        <select name="order_by" class="form-select" style="height: 38px; width: auto; max-width: 200px; font-size: 0.875rem;">
                        <option value="asc" {{ request()->get('order_by') == 'asc' ? 'selected' : '' }}>↑  </option>
                        <option value="desc" {{ request()->get('order_by') == 'desc' ? 'selected' : '' }}>↓  </option>
                    </select>
    </div>
</form>
              
            </div>
        </div>  
    </div>
</div>


<!-- Input caché pour fusion Caisse -->
<input type="file" id="filesToMergeCaisseHidden" multiple style="display: none;" onchange="mergeFilesCaisseDirect(event)">
<script>
function handleUploadCaisse(option) {
    if (option === 'importer') {
        document.getElementById('file-caisse').click();
    } else if (option === 'scanner') {
        alert("Fonction de scan non implémentée.");
    } else if (option === 'fusionner') {
        document.getElementById('filesToMergeCaisseHidden').click();
    }
}

async function mergeFilesCaisseDirect(event) {
    try {
        const files = event.target.files;

        if (files.length < 2) {
            alert("Veuillez sélectionner au moins deux fichiers à fusionner.");
            return;
        }

        const mergedPdf = await PDFLib.PDFDocument.create();

        for (const file of files) {
            const arrayBuffer = await file.arrayBuffer();
            const fileType = file.type;

            if (fileType === 'application/pdf') {
                const pdf = await PDFLib.PDFDocument.load(arrayBuffer);
                const copiedPages = await mergedPdf.copyPages(pdf, pdf.getPageIndices());
                copiedPages.forEach((page) => mergedPdf.addPage(page));
            } else if (fileType.startsWith('image/')) {
                const imagePdf = await PDFLib.PDFDocument.create();
                const imageBytes = new Uint8Array(arrayBuffer);
                let image;

                if (fileType === 'image/png') {
                    image = await imagePdf.embedPng(imageBytes);
                } else if (fileType === 'image/jpeg' || fileType === 'image/jpg') {
                    image = await imagePdf.embedJpg(imageBytes);
                } else {
                    console.warn(`Type d'image non supporté: ${fileType}`);
                    continue;
                }

                const pageWidth = 595.28;   // ≈ 210 mm
                const pageHeight = 841.89;  // ≈ 297 mm

                const targetWidth = pageWidth * 0.9;
                const scale = targetWidth / image.width;
                const targetHeight = image.height * scale;

                const x = (pageWidth - targetWidth) / 2;
                const y = (pageHeight - targetHeight) / 2;

                const page = imagePdf.addPage([pageWidth, pageHeight]);
                page.drawImage(image, {
                    x: x,
                    y: y,
                    width: targetWidth,
                    height: targetHeight,
                });

                const copiedPages = await mergedPdf.copyPages(imagePdf, [0]);
                copiedPages.forEach((page) => mergedPdf.addPage(page));
            }
        }

        const pdfBytes = await mergedPdf.save();
        const blob = new Blob([pdfBytes], { type: 'application/pdf' });
        const formData = new FormData();
        const randomFileName = `merged_${Date.now()}.pdf`;

        formData.append('file', blob, randomFileName);
        formData.append('societe_id', '{{ session()->get('societeId') }}');
        formData.append('type', 'caisse');
        formData.append('folders', '{{ session()->get('foldersId') }}'); // ✅ ligne ajoutée ici

        const response = await fetch('/uploadFusionner', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();

        if (data.success) {
            // ✅ Message stylisé de succès
            const messageDiv = document.createElement('div');
            messageDiv.textContent = "Fichiers fusionnés et envoyés avec succès !";
            messageDiv.style.position = 'fixed';
            messageDiv.style.top = '150px';
            messageDiv.style.left = '20%';
            messageDiv.style.transform = 'translateX(-50%)';
            messageDiv.style.backgroundColor = '#4CAF50';
            messageDiv.style.color = 'white';
            messageDiv.style.padding = '10px 20px';
            messageDiv.style.borderRadius = '5px';
            messageDiv.style.zIndex = 9999;
            messageDiv.style.boxShadow = '0px 0px 10px rgba(0, 0, 0, 0.2)';
            document.body.appendChild(messageDiv);

            // ⏳ Rechargement automatique après 2 secondes
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            alert("Erreur lors de l'envoi des fichiers fusionnés.");
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert("Une erreur s'est produite lors de la fusion des fichiers.");
    }
}
</script>

<input type="hidden" name="folders" value="{{ session()->get('foldersId') }}">

<input type="hidden" name="folders_id" value="{{ session()->get('foldersId') }}">
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
<div class="container mt-5">
<h5>Dossiers</h5>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-6 g-3">
        <!-- Ajouter un Dossier -->
        <div class="col">
            <div class="card shadow-sm" style="width: 10rem; height: 50px; cursor: pointer;" onclick="openCreateFolderForm()">
            <div class="card-body text-center d-flex flex-row justify-content-center align-items-center" style="height: 100%; background-color: #f8f9fa;">
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
                    <div class="card shadow-sm" style="width: 10rem; height: 50px; cursor: pointer;">
                        <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;background-color:#dc3545;border-radius:17px;">
                            <!-- Icône du Dossier -->
                            <!-- <i class="fas fa-folder fa-2x mb-1" style="color:rgb(227, 231, 235);"></i> -->
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;color:rgb(227, 231, 235);">
                                {{ $folder->name }} 
                            </h5>
                            <div class="d-flex justify-content-between" style="font-size: 0.8rem;">

<!-- Menu contextuel -->
<div class="dropdown" style="margin-top:-30px;margin-left:135px;">

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
<!-- Script pour trier la liste par nom -->
<script>
    function sortListByName() {
        const folderList = document.getElementById("folderList");
        const folders = Array.from(folderList.getElementsByClassName("col-3"));

        // Trier les dossiers par nom
        folders.sort((a, b) => {
            const nameA = a.querySelector(".card-title").textContent.trim().toLowerCase();
            const nameB = b.querySelector(".card-title").textContent.trim().toLowerCase();
            return nameA.localeCompare(nameB);
        });

        // Re-ajouter les dossiers triés à la liste
        folderList.innerHTML = "";
        folders.forEach(folder => folderList.appendChild(folder));
    }

    // Appliquer le tri si "Filtrer par Nom" est sélectionné
    document.addEventListener('DOMContentLoaded', function () {
        const filterBy = document.querySelector('[name="filter_by"]').value;
        if (filterBy === "name") {
            sortListByName();
        }
    });
</script>


<!-- Modal pour créer un dossier -->
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createFolderModalLabel">Créer un Nouveau Dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('foldersCaisse1.create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="folders_id" value="{{ $foldersId }}">
                    <input type="hidden" name="type_folder" value="caisse">

                    <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">

                    <div class="mb-3">
                        <label for="folderName" class="form-label">Nom du Dossier</label>
                        <input type="text" class="form-control" id="folderName" name="name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Créer</button>
                    <button type="reset" class="btn btn-secondary" style="margin-left: 10px;">Réinitialiser</button>

                </form>
            </div>
        </div>
    </div>
</div>

<!-- Gestion des fichiers de type achat -->
<div class="container mt-4">
    <h5>Fichiers</h5>
    @if ($achatFiles->isEmpty())
        <p>Aucun fichier trouvé pour cette société.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach ($achatFiles as $file)
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


 <!-- Modal pour afficher le fichier et la communication -->
<div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true" style="margin-left:-5px;width: 100%;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-content-bg" style="position: relative;border:none;">
                <!-- Ajout des boutons pour télécharger, imprimer et fermer -->
            @if(isset($file))
            <h6 style="font-size: 15px; font-weight: bold; color: #FFFFFF; margin-top: 4%; margin-left: -26%;">
    {{ $file->name }}
</h6>
                <div class="action-buttons" style="position: absolute; top: 15px; right: 11%; z-index: 1000;">
                <a href="{{ asset($file->path) }}" class="btn btn-primary" download title="Télécharger" style="width:2%;height:2%;">
    <i class="fas fa-download" style="margin-left:-4px;"></i>
</a>
<a href="javascript:void(0);" class="btn btn-secondary" onclick="printFile('{{ asset($file->path) }}')" title="Imprimer" style="width:2%;height:2%;">
    <i class="fas fa-print" style="margin-left:-4px;"></i>
</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" title="Fermer"  style="width:2%;height:2%;">
                        <i class="fas fa-times" style="margin-left:-4px;"></i>
                    </button>
                </div>
            @endif

            <div class="modal-body" style="margin-left:-28%;margin-top:7%;">
                <div id="filePreviewContent" style="margin-top:-7%;">
                    <!-- Contenu du fichier sera chargé ici -->
                </div>

            <!-- Styles -->
<style>
    .nav-arrow {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        font-size: 24px;
        color: #1a73e8;
        z-index: 1000;
        background: none;
        border: none;
        cursor: pointer;
    }

    .nav-left {
        left: 20px;
    }

    .nav-right {
        right: 20px;
    }
</style>

<!-- Boutons de navigation -->
<button class="nav-arrow nav-left" id="prevFileBtn" onclick="navigateFile(-1)">
    &#9664;
</button>
<button class="nav-arrow nav-right" id="nextFileBtn" onclick="navigateFile(1)">
    &#9654;
</button>




<div id="page-num" style="text-align: center; color: white; position: fixed; bottom: 10px; left: 37%; transform: translateX(-50%); z-index: 1000;background-color:rgba(0, 0, 0, .75);border-radius:25px;padding: 10px;width:200px;">
    <span id="current-page-display" style="color:white; cursor: pointer;">Page 1 sur 1</span>
</div>



                <div class="chat-box" style="height:100%;margin-right:0px;">
                    <h5>Communication</h5>
                    <div id="commentsList"></div>
                    <div id="messages-container" style="height: 80%; overflow-y: auto;">
                        <!-- Les messages seront affichés ici -->
                    </div>
                    
                   <form id="messageForm" action="{{ route('messages.store') }}" method="POST">
    @csrf  
    <textarea id="message_text" name="text_message" placeholder="Écrivez ici..." style="width:100%;"></textarea>
    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
    <input type="hidden" name="file_id" value="{{ $file->id ?? '' }}">
    <input type="hidden" name="societe_id" value="{{ session('societeId') }}">
    <button type="submit">Envoyer</button>
</form>
 
                </div>
            </div>
        </div>
    </div>
</div>
 
<script>


$('#fileModal').on('hidden.bs.modal', function () {
        location.reload(); // Recharge la page
    });

    
  document.getElementById('messageForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Empêche le rechargement de la page
    const formData = new FormData(this); // Récupère les données du formulaire
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Ajoutez le nouveau message à la liste des messages
            const messagesContainer = document.getElementById("messages-container");
            const messageDiv = createMessageDiv(data); // Utilisez une fonction pour créer le message
            messagesContainer.appendChild(messageDiv);
            // Réinitialiser le champ de texte
            document.getElementById("message_text").value = '';
        } else {
            alert("Erreur lors de l'envoi du message.");
        }
    })
    .catch(error => console.error("Erreur lors de l'envoi du message:", error));
});

// Fonction pour créer un div de message
function createMessageDiv(data) {
    const messageDiv = document.createElement("div");
    messageDiv.classList.add("message");

    // Extraire le texte après le symbole @
    let messageAfterAt = data.text_message;
    const atIndex = data.text_message.indexOf("@");
    if (atIndex !== -1 && atIndex + 1 < data.text_message.length) {
        messageAfterAt = data.text_message.substring(atIndex + 1).trim();
    }

    messageDiv.innerHTML = `
        <p><strong>${data.user_name}:</strong><br>
        <i style="font-size:10px;"> ${data.created_at}</i><br>
        <p style="font-size:18px;"> ${messageAfterAt} </p></p>
        <div class="message-actions">
            <button class="mark-as-read" title="${data.is_read ? 'Marquer comme non lue' : 'Marquer comme lue'}">
                <i class="fas ${data.is_read ? 'fa-envelope-open' : 'fa-envelope'}"></i>
            </button>
            <button class="edit-message" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <button class="delete-message" title="Supprimer">
                <i class="fas fa-trash"></i>
            </button>
            <button class="reply-message" title="Répondre">
                <i class="fas fa-reply"></i>
            </button>
        </div>
    `;

    // Ajoutez les événements pour les actions
    addEventListenersToMessageActions(messageDiv, data.message_id, data.text_message);
    return messageDiv;
}

// Fonction pour ajouter des événements aux actions du message
function addEventListenersToMessageActions(messageDiv, messageId, originalText) {
    
    // Marquer comme lu
    messageDiv.querySelector('.mark-as-read').addEventListener('click', function() {
        fetch(`/messages/read/${messageId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.querySelector('i').classList.replace("fa-envelope", "fa-envelope-open");
                this.querySelector('i').style.color = "#28a745"; // Change la couleur pour indiquer que le message est lu
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error("Erreur lors de la mise à jour de l'état de lecture:", error));
    });

    
    // Modifier le message
    messageDiv.querySelector('.edit-message').addEventListener('click', function() {
        var newText = prompt("Modifiez votre message:", originalText);
        if (newText) {
            fetch(`/messages/update/${messageId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ text_message: newText })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Recharge la page pour voir les changements
                } else {
                    alert("Erreur lors de la modification du message.");
                }
            })
            .catch(error => console.error("Erreur lors de la modification du message:", error));
        }
    });

    
    // Supprimer le message
    messageDiv.querySelector('.delete-message').addEventListener('click', function() {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce message ?")) {
            fetch(`/messages/delete/${messageId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.remove(); // Supprime le message du DOM
                } else {
                    alert("Erreur lors de la suppression du message.");
                }
            })
            .catch(error => console.error("Erreur lors de la suppression du message:", error));
        }
    });

    
    // Répondre au message
    messageDiv.querySelector('.reply-message').addEventListener('click', function() {
        var replyForm = document.createElement("form");
        replyForm.action = "{{ route('messages.store') }}";
        replyForm.method = "POST";
        replyForm.innerHTML = `@csrf
            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
            <input type="hidden" name="file_id" value="{{ $file->id ?? 'null' }}">
            <input type="hidden" name="reply_to_message_id" value="${messageId}">
            <input type="hidden" name="societe_id" value="{{ session('societeId') }}">
            <textarea name="text_message" placeholder="Répondre..." style="width: 100%; height: 60px;"></textarea>
            <button type="submit">Envoyer</button>
            <input type="button" value="Annuler" class="cancel-reply" style="background: none; border: none; cursor: pointer; color: #ff0000; margin-top: 5px;">`;

        // Ajouter l'événement pour le bouton Annuler
        var cancelButton = replyForm.querySelector(".cancel-reply");
        cancelButton.addEventListener("click", function() {
            replyForm.remove(); // Supprime le formulaire lorsque le bouton Annuler est cliqué
        });

        messageDiv.appendChild(replyForm);
    });
}


</script>

<style>
 
.textLayer {
    font-family: sans-serif;
    line-height: 1;
    white-space: pre;
    color: transparent;
    pointer-events: auto !important;
    user-select: text;
    -webkit-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
}

.textLayer span {
    color: transparent !important; /* Le texte est invisible */

    position: absolute;
     background: transparent;
    transform-origin: 0 0;
    line-height: 1;
    white-space: pre;
    cursor: text;
    user-select: text;
    -webkit-user-select: text;
}

.textLayer span::selection {
    background: rgba(0, 123, 255, 0.3); /* jolis surlignages */
}
 


</style>
<script>
   function printFile(fileUrl) {
        var printWindow = window.open(fileUrl, '_blank');
        printWindow.onload = function() {
            printWindow.print();
        };
    }


function openRenameFileModal(fileId, fileName) {
    document.getElementById('newFileName').value = fileName;
    document.getElementById('renameFileForm').action = '/file/' + fileId; // Met à jour l'action du formulaire avec l'ID du fichier
    var myModal = new bootstrap.Modal(document.getElementById('renameFileModal'));
    myModal.show();
}
</script>

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
    window.location.href = '/foldersCaisse1/' + folderId;
}

function downloadFile(fileId) {
        window.location.href = '/file/download/' + fileId;
    }
 
// function viewFile(fileId,folderId) {
//         window.location.href = '/achat/view/' + fileId ;
// }

// function viewFile(fileId,folderId) {
//     alert(folderId);
//         window.location.href = '/achat/view/' + folderId + fileId;
// }
  let currentFileIndex = 0; 
const files = @json($achatFiles); 

function navigateFile(direction) {
    if (files.length === 0) {
        console.error('Aucun fichier à naviguer.');
        return;
    }

    // Mettre à jour l'index du fichier actuel
    currentFileIndex += direction;

    // Limiter l'index pour ne pas dépasser les bornes
    if (currentFileIndex < 0) {
        currentFileIndex = 0; // Rester sur le premier fichier
    } else if (currentFileIndex >= files.length) {
        currentFileIndex = files.length - 1; // Rester sur le dernier fichier
    }

    // Mettre à jour l'affichage des boutons de navigation
    updateNavigationButtons();

    // Afficher le fichier actuel
    viewFile(files[currentFileIndex].id);
}

// Fonction pour mettre à jour l'affichage des boutons de navigation
function updateNavigationButtons() {
    document.getElementById('prevFileBtn').style.display = currentFileIndex === 0 ? 'none' : 'block';
    document.getElementById('nextFileBtn').style.display = currentFileIndex === files.length - 1 ? 'none' : 'block';
}

// Appeler cette fonction lors du chargement de la page pour initialiser l'affichage
updateNavigationButtons();


let currentPage = 1;
function viewFile(fileId) {
   const file = files.find(f => f.id === fileId); // Trouver le fichier actuel

if (!file) {
    // Afficher un message d'erreur ou exécuter une action
    console.error('Fichier introuvable.');
    
    // Optionnel : afficher un message dans l'interface utilisateur
    const fileTitleElement = document.querySelector('#fileModal h6');
    if (fileTitleElement) {
        fileTitleElement.textContent = 'Fichier non trouvé';
    }

    // Optionnel : désactiver les boutons de téléchargement et impression
    const downloadButton = document.querySelector('.action-buttons a.btn-primary');
    if (downloadButton) {
        downloadButton.href = '#';
        downloadButton.classList.add('disabled');
    }

    const printButton = document.querySelector('.action-buttons a.btn-secondary');
    if (printButton) {
        printButton.removeAttribute('onclick');
        printButton.classList.add('disabled');
    }

    return;
}

    const url = '/file/view/' + fileId;
    const modal = new bootstrap.Modal(document.getElementById('fileModal'));
    modal.show();
// Mettre à jour l'ID du fichier dans le formulaire de message
document.querySelector('input[name="file_id"]').value = fileId;

    const filePreviewContent = document.getElementById('filePreviewContent');
    filePreviewContent.innerHTML = '<p>Chargement...</p>';

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors du chargement du fichier.');
            }
            return response.blob();
        })
        .then(blob => {
            const fileURL = URL.createObjectURL(blob);
            const fileExtension = blob.type.split('/').pop().toLowerCase();

            if (['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(blob.type)) {
                const previewElement = document.createElement('div');
                filePreviewContent.innerHTML = '';
                filePreviewContent.appendChild(previewElement);

                const reader = new FileReader();
                reader.onload = function(e) {
                    const data = new Uint8Array(e.target.result);
                    mammoth.convertToHtml({ arrayBuffer: data })
                        .then(function(result) {
                            previewElement.innerHTML = result.value;
                            previewElement.style.height = '800px';
                        })
                        .catch(function(err) {
                            console.error("Erreur lors de la conversion du fichier Word :", err);
                            previewElement.innerHTML = '<p>Impossible d\'afficher le fichier Word. Vérifiez le format du fichier.</p>';
                        });
                };
                reader.readAsArrayBuffer(blob);
            } else if (blob.type === 'application/pdf') {
    filePreviewContent.innerHTML = '';

    const container = document.createElement('div');
    container.id = 'pdf-container';
    filePreviewContent.appendChild(container);

    // Ajout du bloc d'affichage dynamique
    const pageControl = document.createElement('div');
    // pageControl.id = 'page-num';
    // pageControl.style = 'text-align: center; color: white; position: fixed; bottom: 10px; left: 50%; transform: translateX(-50%); z-index: 1000; background-color: rgba(0, 0, 0, .75); border-radius: 25px; padding: 10px;';
    // pageControl.innerHTML = `
    //     <span id="current-page-display" style="color:white; cursor: pointer;">Page 1 sur 1</span>
    // `;
    document.body.appendChild(pageControl);

    const currentPageDisplay = document.getElementById('current-page-display');

    pdfjsLib.getDocument({ url: fileURL }).promise.then(pdf => {
        const totalPages = pdf.numPages;

        // Met à jour le texte de la page
        currentPageDisplay.innerText = `Page ${currentPage} sur ${totalPages}`;

        // Observer les pages visibles
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const visiblePageNum = parseInt(entry.target.getAttribute('data-page-number'));
                    currentPage = visiblePageNum;
                    currentPageDisplay.innerText = `Page ${currentPage} sur ${totalPages}`;
                }
            });
        }, {
            root: null,
            rootMargin: '0px',
            threshold: 0.6
        });

 // Gérer le clic pour afficher le champ de saisie
currentPageDisplay.addEventListener('click', () => {
    const inputContainer = document.createElement('div'); // Créer un conteneur
    inputContainer.style.display = 'flex'; // Utiliser flex pour aligner le texte et l'input

    const input = document.createElement('input');
    input.type = 'number';
    input.min = 1;
    input.max = totalPages;
    input.value = currentPage;
    input.style.width = '30px';
    input.style.padding = '4px';
    input.style.border = 'none';
    input.style.borderRadius = '0px'; // Supprimer la bordure arrondie
    input.style.background = 'transparent';
    input.style.color = 'white'; // Couleur de texte blanche
    input.style.textAlign = 'center';
    input.style.outline = 'none';

    // Masquer les flèches dans différents navigateurs
    input.style.webkitAppearance = 'none'; // Chrome, Safari, Opera
    input.style.mozAppearance = 'textfield'; // Firefox
    input.style.appearance = 'none'; // Standard pour d'autres navigateurs modernes

    // Cibler spécifiquement les flèches pour les versions récentes de Chrome et Safari
    input.style.msInputMethod = 'none'; // Pour Internet Explorer et Edge (ancien)

    // Supprimer la zone de texte par défaut sous Safari
    input.style.background = 'transparent';
    input.style.overflow = 'hidden'; // Masquer l'overflow de la zone du spinner

    // Créer un élément pour le texte "Page"
    const pageText = document.createElement('span');
    const surText = document.createElement('span');
    const totalText = document.createElement('span');

    pageText.innerText = 'Page ';
    surText.innerText = ' sur ';
    totalText.innerText = ` ${totalPages}`;

    pageText.style.color = 'white'; // Couleur de texte blanche
    pageText.style.marginRight = '5px'; // Espace entre le texte et l'input

    // Ajouter le texte et l'input au conteneur
    inputContainer.appendChild(pageText);
    inputContainer.appendChild(input);
    inputContainer.appendChild(surText);
    inputContainer.appendChild(totalText);

    // Remplacer currentPageDisplay par le conteneur
    currentPageDisplay.replaceWith(inputContainer);
    input.focus();

    const validateInput = () => {
        const pageNum = parseInt(input.value);
        if (!isNaN(pageNum) && pageNum >= 1 && pageNum <= totalPages) {
            const targetPage = document.querySelector(`.pdf-page[data-page-number='${pageNum}']`);
            if (targetPage) {
                targetPage.scrollIntoView({ behavior: 'smooth' });
            }
            currentPage = pageNum; // Mettre à jour la page actuelle
        }
        // Mettre à jour le message avec la valeur de l'input
        currentPageDisplay.innerText = `Page ${currentPage} sur ${totalPages}`;
        inputContainer.replaceWith(currentPageDisplay); // Remplacer le conteneur par le texte
    };

    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            validateInput();
        }
    });

    input.addEventListener('blur', () => {
        validateInput();
    });
});


        // Rendu de chaque page
        for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
            pdf.getPage(pageNum).then(page => {
                const scale = 1.5;
                const viewport = page.getViewport({ scale });

                const pageContainer = document.createElement('div');
                pageContainer.className = 'pdf-page';
                pageContainer.setAttribute('data-page-number', pageNum);
                pageContainer.style.position = 'relative';
                pageContainer.style.marginBottom = '20px';
                // pageContainer.style.border = '1px solid #ccc';
                container.appendChild(pageContainer);

                observer.observe(pageContainer); // 👈 observer chaque page

                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                pageContainer.appendChild(canvas);

                page.render({ canvasContext: context, viewport });

                page.getTextContent().then(textContent => {
                    const textLayerDiv = document.createElement('div');
                    textLayerDiv.className = 'textLayer';
                    textLayerDiv.style.position = 'absolute';
                    textLayerDiv.style.top = '0';
                    textLayerDiv.style.left = '0';
                    textLayerDiv.style.height = `${viewport.height}px`;
                    textLayerDiv.style.width = `${viewport.width}px`;
                    textLayerDiv.style.pointerEvents = 'auto';
                    pageContainer.appendChild(textLayerDiv);

                    pdfjsLib.renderTextLayer({
                        textContent,
                        container: textLayerDiv,
                        viewport,
                        textDivs: []
                    });
                });
            });
        }
    }).catch(err => {
        console.error("Erreur lors de l'affichage du PDF :", err);
        filePreviewContent.innerHTML = '<p>Impossible d\'afficher le fichier PDF.</p>';
    });
}
         
 else if (['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'].includes(blob.type)) {
                const previewElement = document.createElement('div');
                filePreviewContent.innerHTML = ''; // Vider le contenu précédent
                filePreviewContent.appendChild(previewElement);

                // Appliquer les styles directement en JavaScript
                previewElement.style.width = '95%'; // Largeur à 100%
                previewElement.style.left = '-3%';  
                previewElement .style.height = '100%'; // Hauteur à 100px
                previewElement.style.margin = '20px auto'; // Centre le conteneur
                previewElement.style.border = '1px solid #ddd'; // Bordure autour du conteneur
                previewElement.style.borderRadius = '8px'; // Coins arrondis
                previewElement.style.backgroundColor = '#ffffff'; // Couleur de fond blanche
                previewElement.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.2)'; // Ombre plus prononcée
                previewElement.style.overflowX = 'auto'; // Ajoute un défilement horizontal si nécessaire
                previewElement.style.overflowY = 'auto'; // Ajoute un défilement vertical si nécessaire
                previewElement.style.position = 'relative'; // Position relative pour le conteneur

                const reader = new FileReader();
                reader.onload = function(e) {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const sheet = workbook.Sheets[workbook.SheetNames[0]];
                    const htmlString = XLSX.utils.sheet_to_html(sheet, { editable: false });

                    // Appliquer le HTML à l'élément de prévisualisation
                    previewElement.innerHTML = htmlString;

                    // Appliquer des styles aux tables générées
                    const tables = previewElement.getElementsByTagName('table');
                    for (let table of tables) {
                        table.style.width = '100%'; // Le tableau prend toute la largeur du conteneur
                        table.style.borderCollapse = 'collapse'; // Supprime les espaces entre les cellules
                    }

                    const ths = previewElement.getElementsByTagName('th');
                    for (let th of ths) {
                        th.style.border = '1px solid #ccc'; // Bordure grise claire
                        th.style.padding = '10px'; // Espacement interne
                        th.style.textAlign = 'left'; // Alignement à gauche
                        th.style.fontSize = '14px'; // Taille de la police
                        th.style.backgroundColor = '#007bff'; // Couleur de fond des en-têtes
                        th.style.color = 'white'; // Couleur du texte des en-têtes
                    }

                    const tds = previewElement.getElementsByTagName('td');
                    for (let td of tds) {
                        td.style.border = '1px solid #ccc'; // Bordure grise claire
                        td.style.padding = '10px'; // Espacement interne
                        td.style.textAlign = 'left'; // Alignement à gauche
                        td.style.fontSize = '14px'; // Taille de la police
                    }

                    // Appliquer des styles aux lignes
                    const rows = previewElement.getElementsByTagName('tr');
                    for (let i = 0; i < rows.length; i++) {
                        if (i % 2 === 0) {
                            rows[i].style.backgroundColor = '#f2f2f2'; // Couleur de fond pour les lignes paires
                        }
                        rows[i].onmouseover = function() {
                            this.style.backgroundColor = '#e0e0e0'; // Couleur de fond au survol
                        };
                        rows[i].onmouseout = function() {
                            this.style.backgroundColor = ''; // Réinitialiser la couleur de fond
                        };
                    }
                };
                reader.readAsArrayBuffer(blob);
            } else {
                filePreviewContent.innerHTML = `<img src="${fileURL}" alt="Fichier" style="width: 95%;">`;
            }
    
        fetch(`/messages/getMessages/${fileId}`)
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    const messagesContainer = document.getElementById("messages-container");
                    messagesContainer.innerHTML = ''; // Vider le conteneur avant d'ajouter les nouveaux messages

                    data.messages.forEach(function(message) {
    var messageDiv = document.createElement("div");
    messageDiv.classList.add("message");
    
    // Création du message
    var userMessage = document.createElement("p");
    userMessage.innerHTML = `<strong>${message.user_name}:</strong></br/><i style="font-size:10px;">  ${message.created_at}</i> </br/><p style="font-size:18px;"> ${message.text_message} </p>`;
    console.log("Message:", message);

    // Vérifiez si le message a un commentaire
// Dans la boucle où vous créez les messages
if (message.commentaire !== null) {
    var commentIcon = document.createElement("i");
    commentIcon.classList.add("fas", "fa-comment");
    commentIcon.title = "Ce message a un commentaire";
    userMessage.appendChild(commentIcon);

    // Ajoutez l'événement de clic ici
    commentIcon.addEventListener("click", function() {
        handleCommentClick(message.commentaire); // Passer le texte du commentaire à la fonction
    });
}

    // Actions du message
    var actionsDiv = document.createElement("div");
    actionsDiv.classList.add("message-actions");

    
    var markAsReadButton = document.createElement("i");
    markAsReadButton.classList.add("fas", message.is_read ? "fa-envelope-open" : "fa-envelope");
    markAsReadButton.style.cursor = "pointer";
    markAsReadButton.style.fontSize = "15px";
    markAsReadButton.style.color = message.is_read ? "#28a745" : "#e74a3b";
    markAsReadButton.title = message.is_read ? "Marquer comme non lue" : "Marquer comme lue";
                     
    markAsReadButton.addEventListener("click", function() {
                            fetch(`/messages/read/${message.id}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    markAsReadButton.classList.replace("fa-envelope", "fa-envelope-open");
                                    markAsReadButton.style.color = "#28a745"; // Change la couleur pour indiquer que le message est lu
                                } else {
                                    alert(data.message); // Affiche un message d'erreur si le message n'a pas pu être marqué comme lu
                                }
                            })
                            .catch(error => console.error("Erreur lors de la mise à jour de l'état de lecture:", error));
                        });
  

     // Bouton de suppression
     var deleteButton = document.createElement("button");
                        deleteButton.innerHTML = '<i class="fas fa-trash" title="Supprimer"></i>';
                        deleteButton.style = "background: none; border: none; cursor: pointer; color: #ff0000;";
                        deleteButton.addEventListener("click", function() {
                            if (confirm("Êtes-vous sûr de vouloir supprimer ce message ?")) {
                                fetch(`/messages/delete/${message.id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        messageDiv.remove();
                                    } else {
                                        alert("Erreur lors de la suppression du message.");
                                    }
                                })
                                .catch(error => console.error("Erreur lors de la suppression du message:", error));
                            }
                        });
                        //modification button
    var editButton = document.createElement("button");
                        editButton.innerHTML = '<i class="fas fa-edit" title="Modifier"></i>';
                        editButton.style = "background: none; border: none; cursor: pointer; color: #f39c12;";

                        editButton.addEventListener("click", function() {
                            var newText = prompt("Modifiez votre message:", message.text_message);
                            if (newText) {
                                fetch(`/messages/update/${message.id}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({ text_message: newText })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        location.reload();
                                    } else {
                                        alert("Erreur lors de la modification du message.");
                                    }
                                })
                                .catch(error => console.error("Erreur lors de la modification du message:", error));
                            }
                        });
// Bouton de réponse
var replyButton = document.createElement("button");
                        replyButton.innerHTML = '<i class="fas fa-reply" title="Répondre"></i>';
                        replyButton.style = "background: none; border: none; cursor: pointer; color: #1a73e8;";
                        replyButton.addEventListener("click", function() {
                            var replyForm = document.createElement("form");
                            replyForm.action = "{{ route('messages.store') }}";
                            replyForm.method = "POST";
                            replyForm.innerHTML = `@csrf
                                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                                <input type="hidden" name="file_id" value="{{ $file->id ?? 'null' }}">
                                <input type="hidden" name="reply_to_message_id" value="${message.id}">
                                <input type="hidden" name="societe_id" value="{{ session('societeId') }}">
                                <textarea name="text_message" placeholder="Répondre..." style="width: 100%; height: 60px;"></textarea>
                                <button type="submit">Envoyer</button>
                                <input type="button" value="Annuler" class="cancel-reply" style="background: none; border: none; cursor: pointer; color: #ff0000; margin-top: 5px;">`;

                            // Ajouter l'événement pour le bouton Annuler
                            var cancelButton = replyForm.querySelector(".cancel-reply");
                            cancelButton.addEventListener("click", function() {
                                replyForm.remove(); // Supprimer le formulaire lorsque le bouton Annuler est cliqué
                            });

                            messageDiv.appendChild(replyForm);
                        });

    actionsDiv.appendChild(replyButton);                 
    actionsDiv.appendChild(markAsReadButton);
    actionsDiv.appendChild(editButton);  
    actionsDiv.appendChild(deleteButton);
    messageDiv.appendChild(userMessage);
    messageDiv.appendChild(actionsDiv);
    messagesContainer.appendChild(messageDiv);

      // Afficher les réponses
      if (message.replies.length > 0) {
                            var repliesDiv = document.createElement("div");
                            repliesDiv.style.marginLeft = "20px"; // Décalage pour les réponses
                            message.replies.forEach(function(reply) {
                                var replyDiv = document.createElement("div");
                                replyDiv.classList.add("message");

                                // Affichage du message de la réponse
                                replyDiv.innerHTML = `<p><strong>${reply.user_name}:</strong></br><i style="font-size:10px;"> ${reply.created_at}</i><br><p style="font-size:18px;">${reply.text_message}</p></p>`;
                                
                                // Actions de la réponse
                                var replyActionsDiv = document.createElement("div");
                                replyActionsDiv.style.display = "flex"; 
                                replyActionsDiv.style.alignItems = "center"; 
                                replyActionsDiv.style.gap = "10px"; 

                                // Bouton de modification de la réponse
                                var editReplyButton = document.createElement("button");
                                editReplyButton.innerHTML = '<i class="fas fa-edit" title="Modifier"></i>';
                                editReplyButton.style = "background: none; border: none; cursor: pointer; color: #f39c12;";

                                editReplyButton.addEventListener("click", function() {
                                    var newReplyText = prompt("Modifiez votre réponse:", reply.text_message);
                                    if (newReplyText) {
                                        fetch(`/messages/update/${reply.id}`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                            },
                                            body: JSON.stringify({ text_message: newReplyText })
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                location.reload();
                                            } else {
                                                alert("Erreur lors de la modification de la réponse.");
                                            }
                                        })
                                        .catch(error => console.error("Erreur lors de la modification de la réponse:", error));
                                    }
                                });

                                // Bouton de suppression de la réponse
                                var deleteReplyButton = document.createElement("button");
                                deleteReplyButton.innerHTML = '<i class="fas fa-trash" title="Supprimer"></i>';
                                deleteReplyButton.style = "background: none; border: none; cursor: pointer; color: #ff0000;";
                                deleteReplyButton.addEventListener("click", function() {
                                    if (confirm("Êtes-vous sûr de vouloir supprimer cette réponse ?")) {
                                        fetch(`/messages/delete/${reply.id}`, {
                                            method: 'DELETE',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                replyDiv.remove();
                                            } else {
                                                alert("Erreur lors de la suppression de la réponse.");
                                            }
                                        })
                                        .catch(error => console.error("Erreur lors de la suppression de la réponse:", error));
                                    }
                                });

                                
                               // Bouton de marquage comme lu pour la réponse
var markAsReadButton = document.createElement("button");
markAsReadButton.innerHTML = '<i class="fas ' + (reply.is_read ? 'fa-envelope-open' : 'fa-envelope') + '" title="' + (reply.is_read ? 'Marqué comme lu' : 'Marquer comme lue') + '" style="cursor: pointer; font-size: 15px; color: ' + (reply.is_read ? '#28a745' : '#e74a3b') + ';"></i>';
markAsReadButton.style = "background: none; border: none; cursor: pointer; color: #28a745;";


markAsReadButton.addEventListener("click", function() {
    fetch(`/messages/read/${reply.id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mise à jour de l'icône
            reply.is_read = true; // Met à jour l'état local
            markAsReadButton.innerHTML = '<i class="fas fa-envelope-open" title="Marqué comme lu" style="cursor: pointer; font-size: 15px; color: #28a745;"></i>';
        } else {
            alert(data.message); // Affiche un message d'erreur si le marquage échoue
        }
    })
    .catch(error => console.error("Erreur lors du marquage comme lu de la réponse:", error));
});

                                replyActionsDiv.appendChild(markAsReadButton); // Ajouter le bouton "Marquer comme lu"
                                replyActionsDiv.appendChild(editReplyButton);
                                replyActionsDiv.appendChild(deleteReplyButton);

                                replyDiv.appendChild(replyActionsDiv);
                                repliesDiv.appendChild(replyDiv);
                            });
                            messageDiv.appendChild(repliesDiv);
                        }
});
                })
                .catch(error => console.error("Erreur lors du chargement des messages:", error));
        })
        .catch(error => {
            console.error('Erreur lors du chargement du fichier :', error);
            filePreviewContent.innerHTML = `<p>${error.message}</p>`;
        });

        
}

    function openRenameModal(folderId, folderName) {
        document.getElementById('newFolderName').value = folderName;
        document.getElementById('renameFolderForm').action = '/folder/' + folderId; // Met à jour l'action du formulaire avec l'ID du dossier
        var myModal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
        myModal.show();
    }
    function openRenameFileModal(fileId, fileName) {
    document.getElementById('newFileName').value = fileName; // Remplit le champ avec le nom actuel
    document.getElementById('renameFileForm').action = '/file/' + fileId; // Met à jour l'action du formulaire
    var myModal = new bootstrap.Modal(document.getElementById('renameFileModal'));
    myModal.show(); // Affiche le modal
}


 
let selectedText = '';
    let range;

    // Écoutez l'événement de sélection de texte dans le conteneur du PDF
    // document.getElementById("filePreviewContent").addEventListener("mouseup", function () {
    //     const selection = window.getSelection();
    //     if (selection.toString().length > 0) {
    //         selectedText = selection.toString();
    //         range = selection.getRangeAt(0);
            
    //         // Insérer le texte sélectionné dans la boîte de communication
    //         document.getElementById("message_text").value = selectedText + '@ '; // Insérer le texte sélectionné
    //         document.getElementById("message_text").focus(); // Met le focus sur le champ de message
    //     }
    // });

    let commentIcon;

document.getElementById("filePreviewContent").addEventListener("mouseup", function () {
    const selection = window.getSelection();
    if (selection.toString().length > 0) {
        selectedText = selection.toString();  // Stocker le texte sélectionné

        // Créer l'icône de commentaire si elle n'existe pas déjà
        if (!commentIcon) {
            // Créer un div qui contiendra l'icône
            commentIcon = document.createElement("div");
            commentIcon.style.position = "absolute";
            commentIcon.style.cursor = "pointer";
            commentIcon.style.zIndex = "1500";
            commentIcon.style.width = "40px";  // Largeur du carré
            commentIcon.style.height = "40px"; // Hauteur du carré
            commentIcon.style.display = "flex";
            commentIcon.style.alignItems = "center";
            commentIcon.style.justifyContent = "center";
            commentIcon.style.backgroundColor = "#ffffff"; // Couleur de fond du carré (blanc)
            commentIcon.style.border = "2px solid #1a73e8"; // Bordure bleu autour du carré
            commentIcon.style.borderRadius = "8px"; // Coins arrondis
            commentIcon.style.boxShadow = "0 2px 5px rgba(0,0,0,0.1)"; // Ombre légère
            commentIcon.style.transition = "background-color 0.3s"; // Transition de fond au survol

            // Ajouter l'icône "plus"
            const plusIcon = document.createElement("i");
            plusIcon.className = "fas fa-plus"; // Icône plus
            plusIcon.style.fontSize = "18px"; // Taille de l'icône
            plusIcon.style.color = "#1a73e8"; // Couleur de l'icône
            commentIcon.appendChild(plusIcon);

            // Ajouter un effet au survol (lorsque l'utilisateur survole l'icône)
            commentIcon.addEventListener("mouseenter", () => {
                commentIcon.style.backgroundColor = "#e8f0fe"; // Fond bleu clair
            });

            commentIcon.addEventListener("mouseleave", () => {
                commentIcon.style.backgroundColor = "#ffffff"; // Revenir au fond blanc
            });

            document.body.appendChild(commentIcon);
        }

        // Positionner l'icône à droite de la sélection
        const range = selection.getRangeAt(0);
        const rect = range.getBoundingClientRect();
        commentIcon.style.top = `${rect.bottom + window.scrollY + 5}px`; // Positionner juste en dessous de la sélection
        commentIcon.style.left = `${rect.right + window.scrollX + 5}px`; // Positionner à droite de la sélection, avec une marge de 5px
        commentIcon.style.display = "flex"; // Afficher l'icône dans le carré

        // Ajouter un événement de clic à l'icône
        commentIcon.onclick = function() {
            // Insérer le texte sélectionné suivi de "@" dans le champ de message
            const messageText = document.getElementById("message_text");
            messageText.value = selectedText + '@ '; // Ajouter "@" après le texte sélectionné
            messageText.focus(); // Mettre le focus sur le champ de message
            commentIcon.style.display = "none"; // Cacher l'icône après le clic
        };
    } else {
        if (commentIcon) {
            commentIcon.style.display = "none"; // Cacher l'icône si rien n'est sélectionné
        }
    }
});




 
    function saveComment() {
        const comment = document.getElementById("message_text").value;

        if (comment) {
            // Logique pour enregistrer le commentaire ou le message
            const commentsList = document.getElementById("commentsList");
            const div = document.createElement("div");
            div.textContent = `Commentaire: "${comment}" sur "${selectedText}"`;
            commentsList.appendChild(div);

            // Réinitialiser
            document.getElementById("message_text").value =  selectedText + '@ ';
            selectedText = '';
            range = null;
        }
    }


// Fonction pour rechercher et sélectionner le texte dans le PDF
function handleCommentClick(commentText) {
    console.log("Commentaire cliqué:", commentText);
    searchAndHighlightText(commentText);
}

function searchAndHighlightText(text) {
    const textLayer = document.querySelector('.textLayer');

    if (!textLayer) {
        console.error("Aucune couche de texte trouvée.");
        return;
    }

    const textElements = textLayer.getElementsByTagName('span');

    // Nettoyer les anciens surlignages
    for (let el of textElements) {
        el.classList.remove('highlight');
    }

    let found = false;

    // Chercher et surligner le texte
    for (let i = 0; i < textElements.length; i++) {
        // Ajouter .trim() pour enlever les espaces inutiles
        if (textElements[i].textContent.trim().includes(text.trim())) {
            console.log("Texte trouvé à l'index:", i);  // Debug
            // Appliquer le surlignage
            textElements[i].classList.add('highlight');
            found = true;
            break;
        }
    }

    if (!found) {
        console.log("Texte non trouvé");
    }
}


   
    function openRenameModal(folderId, folderName) {
        document.getElementById('newFolderName').value = folderName;
        document.getElementById('renameFolderForm').action = '/folder/' + folderId; // Met à jour l'action du formulaire avec l'ID du dossier
        var myModal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
        myModal.show();
    }

</script>

 <style>
.highlight {
    position: relative;
}

.highlight::after {
    content: '';
    position: absolute;
    background-color: yellow !important;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
    opacity: 0.5;
}

 
</style>
         
<script>
    
// Ajoutez cet écouteur d'événements dans votre script principal
document.addEventListener('keydown', function(event) {
    if (event.key === 'ArrowLeft') {
        navigateFile(-1); // Naviguer vers le fichier précédent
    } else if (event.key === 'ArrowRight') {
        navigateFile(1); // Naviguer vers le fichier suivant
    }
});

</script>
                              
@if(session('alert'))
    <script>
        if (confirm("{{ session('alert') }}")) {
            // Logique pour ajouter le fichier avec un suffixe (1)
            // Vous pouvez appeler une fonction pour gérer cela
            addFileWithSuffix();
        }
    </script>
@endif
@endsection

