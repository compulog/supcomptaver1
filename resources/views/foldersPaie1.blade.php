
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    <h6  style="margin-top: 1.5rem !important;">
    <a href="{{ route('exercices.show', ['societe_id' => session()->get('societeId')]) }}"  style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">Tableau De Board</a>
     <span style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">➢</span> 

    <a href="{{ route('paie.view') }}"  style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">Paie</a>
      <span style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">➢</span> 


        @php
        $currentFolder = $folder;
        $breadcrumbs = [];

        while ($currentFolder) {
            $breadcrumbs[] = $currentFolder;
            $currentFolder = $currentFolder->parent;  
        }

        $breadcrumbs = array_reverse($breadcrumbs);
        $breadcrumbTrail = collect($breadcrumbs)->pluck('name')->implode(' > ');
        $breadcrumbTrailEncoded = urlencode($breadcrumbTrail);
    @endphp

    <!-- Affichage des trois derniers dossiers -->
    @foreach ($breadcrumbs as $index => $breadcrumb)
        @if ($index >= count($breadcrumbs) - 3)
            <a href="{{ route('folder.show', ['id' => $breadcrumb->id]) }}?breadcrumb={{ $breadcrumbTrailEncoded }}&clicked={{ urlencode($breadcrumb->name) }}&type=paie"
               style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">
               {{ $breadcrumb->name }}
            </a>
            @if (!$loop->last)
                <span style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">➢</span>
            @endif
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
                    @if ($index < count($breadcrumbs) - 3)
                        <li>
                            <a href="{{ route('folder.show', ['id' => $breadcrumb->id]) }}?breadcrumb={{ $breadcrumbTrailEncoded }}&clicked={{ urlencode($breadcrumb->name) }}&type=paie"
                               style="color:rgb(34, 146, 245); text-decoration: underline; font-weight: bold;">
                               {{ $breadcrumb->name }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    <script>
        function toggleMenu() {
            const menu = document.getElementById('folderMenuWrapper');
            menu.style.display = (menu.style.display === 'none') ? 'block' : 'none';
        }
    </script>
</h6>
<div class="container mt-4">

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





<div class="row" style="position: absolute; right: 0;">

        <!-- Conteneur flexible pour aligner les éléments sur la même ligne -->
        <div class="d-flex align-items-center mb-3">
         
        <div class="p-0" style="background-color: transparent; border-radius: 15px; font-size: 0.75rem; display: inline-flex; justify-content: center; align-items: center; height: auto; width: auto;">
        <form id="form-paie" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="paie">
<input type="file" name="file" id="file-paie" style="display: none;" onchange="handleFileSelect(event, 'paie')" multiple>
                <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                <input type="hidden" name="folders" value="{{ session()->get('foldersId') }}">
<input type="hidden" name="exercice_debut" value="{{ $societe->exercice_social_debut }}">
<input type="hidden" name="exercice_fin" value="{{ $societe->exercice_social_fin }}">

                <!-- Dropdown sans flèche Bootstrap -->
                <div class="">
                    <button 
                        class="btn btn-primary btn-sm" 
                        type="button" 
                        id="dropdownMenuButtonPaie" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false"
                        style="background-color:#4D55CC;border: 1px solid white; border-radius: 10px; color: white; width:100px;">
                        Charger
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonPaie">
                        <li><a class="dropdown-item" href="#" onclick="handleUploadPaie('importer')">Importer</a></li>
                        <!-- <li><a class="dropdown-item" href="#" onclick="handleUploadPaie('scanner')">Scanner</a></li> -->
                        <li><a class="dropdown-item" href="#" onclick="handleUploadPaie('fusionner')">Fusionner</a></li>
                    </ul>
                </div>

                <button type="submit" style="display: none;" id="submit-paie">Envoyer</button>
            </form>
            </div>
            <!-- Formulaire de téléchargement (Charger) -->
            <div class="p-0" style="background-color: transparent; border-radius: 15px; font-size: 0.75rem; display: inline-flex; justify-content: center; align-items: center; height: auto; width: auto;">
               <!-- Formulaire de filtrage -->
               <form method="GET" action="{{ url()->current() }}" class="d-flex me-3">
    <div class="input-group">
        <button class="btn btn-primary btn-sm" type="submit" style="height: 38px; order: -1;background-color:#4D55CC;">Triée par</button>
        
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

<!-- Input caché pour fusion Vente -->
<input type="file" id="filesToMergePaieHidden" multiple style="display: none;" onchange="mergeFilesPaieDirect(event)">

<script>
  let selectedFiles = [];
    let rotations = []; // <-- tableau pour stocker rotation (en degrés) par fichier
let sortOrder = 'asc'; // Variable d'état pour l'ordre de tri

function sortFiles() {
    // Inverser l'ordre de tri
    sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';

    // Trier les fichiers en fonction de l'ordre
    selectedFiles.sort((a, b) => {
        return sortOrder === 'asc' 
            ? a.name.localeCompare(b.name) 
            : b.name.localeCompare(a.name);
    });

    // Mettre à jour l'affichage
    populateFileList();

    // Changer le texte et la couleur du bouton pour indiquer l'ordre actuel
    const sortButton = document.getElementById('sortButton');
    sortButton.innerHTML = sortOrder === 'asc' 
        ? '<span style="font-size: 20px;">&#8593;</span><div style="line-height: 1;"><div style="font-size: 9px;">Z</div><div style="font-size: 9px;">A</div></div>'
        : '<span style="font-size: 20px;">&#8595;</span><div style="line-height: 1;"><div style="font-size: 9px;">A</div><div style="font-size: 9px;">Z</div></div>';
    
    sortButton.style.backgroundColor = sortOrder === 'asc' ? '#cb0c9f' : '#e74c3c'; // Couleur pour A-Z et Z-A
}

 function handleUploadPaie(option) {
     if (option === 'importer') {
         document.getElementById('file-paie').click();
     } else if (option === 'scanner') {
         alert("Fonction de scan non implémentée.");
     } else if (option === 'fusionner') {
         document.getElementById('filesToMergePaieHidden').click();
     }
 }

   async function mergeFilesPaieDirect(event) {
       const newFiles = Array.from(event.target.files);
       if (!newFiles.length) return;
       selectedFiles.push(...newFiles);

       // Initialiser la rotation à 0 pour les nouveaux fichiers
       for(let i = 0; i < newFiles.length; i++) {
           rotations.push(0);
       }

       if (selectedFiles.length < 2) {
           alert("Veuillez sélectionner au moins deux fichiers.");
           return;
       }

       showModal();
       populateFileList();
   }
   

 function showModal() {
     document.getElementById('fileOrderModal').style.display = 'block';
 }

 function closeModal() {
     document.getElementById('fileOrderModal').style.display = 'none';
 }


   
  function drawImageWithRotation(ctx, img, rotationDeg, canvasWidth, canvasHeight) {
    const radians = rotationDeg * Math.PI / 180;
    ctx.clearRect(0, 0, canvasWidth, canvasHeight);
    ctx.save();
    ctx.translate(canvasWidth / 2, canvasHeight / 2);
    ctx.rotate(radians);

    const scale = Math.min(canvasWidth / img.width, canvasHeight / img.height);
    ctx.drawImage(img, -img.width * scale / 2, -img.height * scale / 2, img.width * scale, img.height * scale);
    ctx.restore();
  }

  function rotateImage(canvas, index) {
    rotations[index] = (rotations[index] + 90) % 360;
    const ctx = canvas.getContext('2d');
    const file = selectedFiles[index];
    const reader = new FileReader();

    reader.onload = function(e) {
      if (file.type.startsWith('image/')) {
        const img = new Image();
        img.onload = function() {
          drawImageWithRotation(ctx, img, rotations[index], canvas.width, canvas.height);
        };
        img.src = URL.createObjectURL(file);
      }
    };

    reader.readAsArrayBuffer(file);
  }


  function populateFileList() {
        const fileList = document.getElementById('fileList');
        fileList.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const listItem = document.createElement('li');
            // listItem.style.display = 'flex';
            listItem.style.flexDirection = 'column';
            // listItem.style.alignItems = 'center';
            listItem.style.marginBottom = '15px';
            listItem.style.padding = '10px';
            listItem.style.marginLeft = '10px';
            listItem.style.backgroundColor = '#ffffff';
            listItem.style.border = '1px solid #ddd';
            listItem.style.borderRadius = '8px';
            listItem.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            listItem.style.transition = 'box-shadow 0.2s';
            listItem.style.width = '23%';
            listItem.draggable = true;

            listItem.addEventListener('mouseover', () => {
                listItem.style.boxShadow = '0 6px 14px rgba(0,0,0,0.1)';
            });
            listItem.addEventListener('mouseout', () => {
                listItem.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            });

            const topRow = document.createElement('div');
            topRow.style.display = 'flex';
            topRow.style.alignItems = 'center';
            topRow.style.justifyContent = 'space-between';
            topRow.style.width = '100%';
            topRow.style.marginBottom = '8px';

            const fileInfo = document.createElement('div');
            fileInfo.style.display = 'flex';
            fileInfo.style.alignItems = 'center';
            fileInfo.style.flexGrow = '1';
            fileInfo.style.gap = '5px';
            fileInfo.innerHTML = `<span style="font-size:11px; color:#333; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${file.name}</span>`;

            const iconRow = document.createElement('div');
            iconRow.style.display = 'flex';
            iconRow.style.gap = '10px';

            const deleteIcon = document.createElement('span');
            deleteIcon.textContent = '❌';
            deleteIcon.title = 'Supprimer';
            deleteIcon.style.cursor = 'pointer';
            deleteIcon.style.transition = 'color 0.2s';
            deleteIcon.onmouseover = () => deleteIcon.style.color = '#e74c3c';
            deleteIcon.onmouseout = () => deleteIcon.style.color = 'inherit';
            deleteIcon.onclick = () => {
                selectedFiles.splice(index, 1);
                rotations.splice(index, 1); // Supprimer aussi la rotation correspondante
                populateFileList();
            };

            const rotateIcon = document.createElement('span');
            rotateIcon.textContent = '🔄';
            rotateIcon.title = 'Retation';
            rotateIcon.style.cursor = 'pointer';
            rotateIcon.style.transition = 'color 0.2s';
            rotateIcon.onmouseover = () => rotateIcon.style.color = '#3498db';
            rotateIcon.onmouseout = () => rotateIcon.style.color = 'inherit';

            iconRow.appendChild(deleteIcon);
            iconRow.appendChild(rotateIcon);

            topRow.appendChild(fileInfo);
            topRow.appendChild(iconRow);

            const preview = document.createElement('canvas');
            preview.width = 200;
            preview.height = 260;
            preview.style.border = '1px solid #ccc';
            preview.style.borderRadius = '4px';
            preview.style.marginBottom = '5px';

            const ctx = preview.getContext('2d');
            ctx.fillStyle = "#f0f0f0";
            ctx.fillRect(0, 0, preview.width, preview.height);

            rotateIcon.onclick = () => rotateImage(preview, index);

            const legend = document.createElement('div');
            legend.style.fontSize = '10px';
            legend.style.color = '#777';
            legend.style.marginTop = '4px';

            const reader = new FileReader();
            reader.onload = async function (e) {
                const arrayBuffer = e.target.result;
                const fileType = file.type;

                if (fileType === 'application/pdf') {
                    const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
                    loadingTask.promise.then((pdfDoc) => {
                        pdfDoc.getPage(1).then((page) => {
                            const viewport = page.getViewport({ scale: 1.0 });
                            const scale = Math.min(preview.width / viewport.width, preview.height / viewport.height);
                            const scaledViewport = page.getViewport({ scale });
                            ctx.clearRect(0, 0, preview.width, preview.height);
                            page.render({ canvasContext: ctx, viewport: scaledViewport });
                        });
                    }).catch(console.error);
                } else if (fileType.startsWith('image/')) {
                    const img = new Image();
                    img.onload = function () {
                        drawImageWithRotation(ctx, img, rotations[index], preview.width, preview.height);
                    };
                    img.src = URL.createObjectURL(file);
                }
            };
            reader.readAsArrayBuffer(file);

            listItem.appendChild(topRow);
            listItem.appendChild(preview);
            listItem.appendChild(legend);

            listItem.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', index);
            });
            listItem.addEventListener('dragover', (e) => e.preventDefault());
            listItem.addEventListener('drop', (e) => {
                e.preventDefault();
                const fromIndex = e.dataTransfer.getData('text/plain');
                moveFile(fromIndex, index);
            });

            fileList.appendChild(listItem);
        });
    }

    function drawImageWithRotation(ctx, img, degrees, canvasWidth, canvasHeight) {
        ctx.clearRect(0, 0, canvasWidth, canvasHeight);
        ctx.save();
        ctx.translate(canvasWidth / 2, canvasHeight / 2);
        ctx.rotate(degrees * Math.PI / 180);

        // Calculer la taille à dessiner après rotation
        let scale = Math.min(canvasWidth / img.width, canvasHeight / img.height);
        let width = img.width * scale;
        let height = img.height * scale;

        // Lorsque rotation 90 ou 270°, on inverse largeur/hauteur
        if (degrees % 180 !== 0) {
            [width, height] = [height, width];
        }

        ctx.drawImage(img, -width / 2, -height / 2, width, height);
        ctx.restore();
    }

   function rotateImage(preview, index) {
    // Incrémente la rotation de 90° (modulo 360)
    rotations[index] = (rotations[index] + 90) % 360;

    // Redessine l'image avec la nouvelle rotation
    const ctx = preview.getContext('2d');
    const file = selectedFiles[index]; // Récupérer le fichier original
    const reader = new FileReader();

    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            drawImageWithRotation(ctx, img, rotations[index], preview.width, preview.height);
        };
        img.src = e.target.result; // Utiliser le résultat du FileReader
    };
    reader.readAsDataURL(file); // Lire le fichier comme URL de données
}


    function moveFile(currentIndex, newIndex) {
        currentIndex = parseInt(currentIndex);
        if (newIndex < 0 || newIndex >= selectedFiles.length || currentIndex === newIndex) return;
        const [moved] = selectedFiles.splice(currentIndex, 1);
        const [rotMoved] = rotations.splice(currentIndex, 1);
        selectedFiles.splice(newIndex, 0, moved);
        rotations.splice(newIndex, 0, rotMoved);
        populateFileList();
    }

 async function confirmOrder() {
     closeModal();
     await mergeFilesPaie(confirmOrder);
 }

 async function mergeFilesPaie(files) {
      const mergedPdf = await PDFLib.PDFDocument.create();

  // Afficher un message de progression
  const messageDiv = document.createElement('div');
  messageDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fusion en cours...';
  messageDiv.style.position = 'fixed';
  messageDiv.style.top = '150px';
  messageDiv.style.right = '15px';
  messageDiv.style.transform = 'translateX(-50%)';
  messageDiv.style.backgroundColor = '#ffffff';
  messageDiv.style.color = '#333';
  messageDiv.style.padding = '10px 20px';
  messageDiv.style.borderRadius = '5px';
  messageDiv.style.zIndex = 9999;
  document.body.appendChild(messageDiv);
     try {
         const mergedPdf = await PDFLib.PDFDocument.create();

         for (const file of selectedFiles) {
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

                 const pageWidth = 595.28;
                 const pageHeight = 841.89;
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
        const fileNameBase = selectedFiles.map(f => f.name.replace(/\.[^/.]+$/, '')).join('_');

// Limiter la longueur si trop long (optionnel)
const safeFileName = fileNameBase.substring(0, 100).replace(/[^\w\-]/g, '_');

formData.append('file', blob, `${safeFileName}_${Date.now()}.pdf`);
         formData.append('societe_id', '{{ session()->get('societeId') }}');
         formData.append('type', 'paie');
   const foldersInput = document.querySelector('input[name="folders"]');
    if (foldersInput) {
      formData.append('folders', foldersInput.value);
    }
    formData.append('exercice_debut', '{{ $societe->exercice_social_debut }}');
formData.append('exercice_fin', '{{ $societe->exercice_social_fin }}');
         const response = await fetch('/uploadFusionner', {
             method: 'POST',
             body: formData,
             headers: {
                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
             }
         });

         const data = await response.json();

           if (data.success) {
    // Afficher un message de succès
    messageDiv.innerHTML = '<i class="fas fa-check-circle"></i> Fichiers fusionnés avec succès !';
    messageDiv.style.backgroundColor = '#ffffff';
    messageDiv.style.color = '#333';
    setTimeout(() => {
      document.body.removeChild(messageDiv);
      location.reload();
    }, 2000);
  } else {
    // Afficher un message d'erreur
    messageDiv.innerHTML = '<i class="fas fa-times-circle"></i> Erreur lors de l\'envoi.';
    messageDiv.style.backgroundColor = '#f44336';
    messageDiv.style.color = 'white';
  }
     } catch (error) {
         console.error('Erreur:', error);
         alert("Une erreur s'est produite lors de la fusion des fichiers.");
     }
 }

 function addFile() {
     // Ouvrir le sélecteur de fichiers caché
     document.getElementById('filesToMergeHidden').click();
 }
</script>

<!-- 💬 Modal HTML -->
<div id="fileOrderModal" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModal()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>

    <div style="display: flex; align-items: flex-start; gap: 20px; height:120%;">
        <ul id="fileList" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1; display: flex; flex-wrap: wrap; align-items: flex-start;"></ul>
        <div style="display: flex; flex-direction: column; gap: 10px; align-items: center;">
            <span onclick="addFile()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
            <span id="sortButton" onclick="sortFiles()" 
                style="display: flex; align-items: center; justify-content: center; 
                cursor: pointer; width: 60px; height: 60px; 
                background-color: #cb0c9f; color: white; border-radius: 50%; 
                font-size: 16px; user-select: none; padding: 5px;">
                <div style="display: flex; align-items: center; gap: 5px;">
                    <span style="font-size: 20px;">&#8593;</span>
                    <div style="line-height: 1;">
                        <div style="font-size: 9px;">Z</div>
                        <div style="font-size: 9px;">A</div>
                    </div>
                </div>
            </span>
            <button onclick="confirmOrder()" style="background-color: #007BFF; color: white; padding: 10px 16px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s; width: 100px; margin-top: 10px;">Valider</button>
        </div>
    </div>

</div>

<input type="file" id="filesToMergeHidden" style="display:none;" multiple accept="application/pdf,image/*" onchange="mergeFilesDirect(event)">

<style>
  #fileOrderModal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #ffffff;
    border-radius: 10px;
    border: 1px solid #ddd;
    padding: 30px;
    width: 500px;
    max-width: 90%;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    z-index: 10000;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translate(-50%, -40%);
    }
    to {
      opacity: 1;
      transform: translate(-50%, -50%);
    }
  }

  #fileOrderModal h3 {
    margin-top: 0;
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
  }

  #fileOrderModal ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid #eee;
    border-radius: 4px;
    padding: 10px;
    background-color: #f9f9f9;
  }

  #fileOrderModal ul li {
     background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 9px;
    display: inline-block;
    transition: background-color 0.2s;
  }

  #fileOrderModal ul li:hover {
    background-color: #f1f1f1;
  }

  #fileOrderModal span[onclick] {
    color: #007BFF;
    font-weight: 500;
    transition: color 0.2s;
  }

  #fileOrderModal span[onclick]:hover {
    color: #0056b3;
  }

  #fileOrderModal .action-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
  }

  #fileOrderModal button {
    background-color: #007BFF;
    color: white;
    padding: 10px 15px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.2s;
    flex: 1;
    margin: 0 5px;
  }

  #fileOrderModal button:hover {
    background-color: #0056b3;
  }

  #fileOrderModal button:first-of-type {
    background-color: #6c757d;
  }

  #fileOrderModal button:first-of-type:hover {
    background-color: #5a6268;
  }

  #fileOrderModal > span {
    position: absolute;
    top: 15px;
    right: 20px;
    cursor: pointer;
    font-size: 24px;
    font-weight: bold;
    color: #999;
  }

  #fileOrderModal > span:hover {
    color: #333;
  }
</style>



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
            <p>Aucun dossier trouvé.</p>
        @else
            @foreach ($folders as $folder)
                <div class="col" ondblclick="openFile({{ $folder->id }})">
                    <div class="card shadow-sm" style="width: 10rem; height: 50px; cursor: pointer;">
                        <div class="card-body text-center d-flex flex-column justify-content-between" style="padding: 0.5rem;background-color:#17a2b8;border-radius:17px;">
                            <!-- Icône du Dossier -->
                            <!-- <i class="fas fa-folder fa-2x mb-1" style="color:rgb(227, 231, 235);"></i> -->
                            <h5 class="card-title text-truncate" style="font-size: 0.9rem; font-weight: bold;color:rgb(227, 231, 235);">
                                {{ $folder->name }} 
                            </h5>
                            <div class="d-flex justify-content-between" style="font-size: 0.8rem;">

<!-- Menu contextuel -->
<div  style="margin-top:-30px;margin-left:135px;">

                    <button class="btn btn-link p-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v" style="color:rgb(227, 231, 235);"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                          <li>
            <span class="dropdown-item text-muted" style="font-size: 0.85em; cursor: default;">
                  Dernière modification : {{ \Carbon\Carbon::parse($folder->updated_at)->format('d/m/Y H:i') }}
</br>     par : {{ $folder->updatedBy ? $folder->updatedBy->name : ($folder->user ? $folder->user->name : 'Inconnu') }}

           
                </span>
        </li>    
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
                <form action="{{ route('foldersPaie1.create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="folders_id" value="{{ $foldersId }}">
                    <input type="hidden" name="type_folder" value="paie">

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
        <p>Aucun fichier trouvé.</p>
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
                <div  style="margin-top:-230px;margin-left:190px;">

                        <button class="btn btn-link p-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        
                          <li>
            <span class="dropdown-item text-muted" style="font-size: 0.85em; cursor: default;">
                  Dernière modification : {{ \Carbon\Carbon::parse($file->updated_at)->format('d/m/Y H:i') }}
</br>     par : {{ $file->updatedBy ? $file->updatedBy->name : ($file->user ? $file->user->name : 'Inconnu') }}

           
                </span>
        </li>
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
                    <h5>Discussion </h5>
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
<style>
    #messageForm {
    margin-bottom: 0px;
    position: fixed;
    bottom: 0;
}
</style>
<script>
    document.getElementById('messageForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
        },
        body: formData
    })
    .then(async response => {
        const contentType = response.headers.get("content-type");

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Erreur HTTP :", errorText);

            if (contentType && contentType.includes("application/json")) {
                try {
                    const jsonError = JSON.parse(errorText);
                    alert("Erreur : " + (jsonError.message || "Une erreur est survenue."));
                } catch (e) {
                    alert("Erreur : Réponse JSON invalide.");
                }
            } else {
                alert("Erreur serveur : " + errorText.slice(0, 200)); // Limite l'affichage
            }

            return;
        }

        if (contentType && contentType.includes("application/json")) {
            const data = await response.json();
            // console.log('Réponse JSON :', data);
            // alert('Message envoyé avec succès !');
                  const fileId = document.querySelector('input[name="file_id"]').value;
                document.getElementById("message_text").value = '';
                viewFile(parseInt(fileId)); // <-- Toujours rappeler viewFile même si c'est le premier message
                highlightComments(data.comments); // Assurez-vous que `data.comments` contient les commentaires à colorer

            form.reset();
        } else {
            const text = await response.text();
            console.warn("Réponse inattendue du serveur :", text);
            alert("Message envoyé, mais le serveur n’a pas retourné de JSON.");
        }
    })
    .catch(error => {
        console.error('Erreur JavaScript :', error);
        alert("Erreur lors de l'envoi du message : " + error.message);
    });
});
 
function addFile() {
    document.getElementById('filesToMergeHidden').click();
}

// $('#fileModal').on('hidden.bs.modal', function () {
//         location.reload(); // Recharge la page
//     });
// Corrige le backdrop Bootstrap qui reste après fermeture du modal
document.getElementById('fileModal').addEventListener('hidden.bs.modal', function () {
    document.body.classList.remove('modal-open');
    // Supprime tous les backdrops Bootstrap restants
    document.querySelectorAll('.modal-backdrop').forEach(bd => bd.remove());
});
    
 
 
function renderMessages(messages) {
    const messagesContainer = document.getElementById("messages-container");
    messagesContainer.innerHTML = '';

    // Trier les messages par date croissante (du plus ancien au plus récent)
messages.sort((b, a) => new Date(a.created_at) - new Date(b.created_at));
    messages.forEach(function(message) {
        var messageDiv = document.createElement("div");
        messageDiv.classList.add("message");
        messageDiv.setAttribute("data-message-id", message.id);

        var userMessage = document.createElement("p");
        userMessage.innerHTML = `<strong>${message.user_name}:</strong></br/><i style="font-size:10px;">  ${message.created_at}</i> </br/><p style="font-size:18px;"> ${message.text_message} </p>`;
        messageDiv.appendChild(userMessage);

        // Actions (marquer comme lu, éditer, supprimer, répondre)
        var actionsDiv = document.createElement("div");
        actionsDiv.classList.add("message-actions");

        // Marquer comme lu
        var markAsReadButton = document.createElement("i");
        markAsReadButton.classList.add("fas", message.is_read ? "fa-envelope-open" : "fa-envelope");
        markAsReadButton.style.cursor = "pointer";
        markAsReadButton.style.fontSize = "15px";
        markAsReadButton.style.color = message.is_read ? "#28a745" : "#e74a3b";
        markAsReadButton.title = message.is_read ? "Marqué comme lu" : "Marquer comme lue";
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
                    markAsReadButton.style.color = "#28a745";
                } else {
                    alert(data.message);
                }
            });
        });

        // Supprimer
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
                });
            }       
        });

        // Modifier
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
                        reloadMessages(message.file_id);
                    } else {
                        alert("Erreur lors de la modification du message.");
                    }
                });
            }
        }); 

        // Répondre
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

            var cancelButton = replyForm.querySelector(".cancel-reply");
            cancelButton.addEventListener("click", function() {
                replyForm.remove();
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
        if (message.replies && message.replies.length > 0) {
            var repliesDiv = document.createElement("div");
            repliesDiv.style.marginLeft = "20px";
            // Trier les réponses aussi par date croissante
            message.replies.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            message.replies.forEach(function(reply) {
                var replyDiv = document.createElement("div");
                replyDiv.classList.add("message");
                replyDiv.innerHTML = `<p><strong>${reply.user_name}:</strong></br><i style="font-size:10px;"> ${reply.created_at}</i><br><p style="font-size:18px;">${reply.text_message}</p></p>`;
                repliesDiv.appendChild(replyDiv);
            });
            messageDiv.appendChild(repliesDiv);
        }
    });

    // Scroll automatique en bas après affichage

    // Scroll automatique en bas après affichage (attendre le rendu DOM)
 // À la fin de renderMessages
setTimeout(() => {
    messagesContainer.scrollTop = 0; // Scroll tout en haut pour voir le plus récent (qui est en bas visuellement)
}, 100);
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
viewFile(parseInt(fileId))
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


// Ajoute ce bloc pour TOUS les formulaires de reply dynamiques
document.addEventListener('submit', function(event) {
    // Vérifie que c'est bien un formulaire de reply inline (et PAS le formulaire principal)
    if (
        event.target.classList.contains('inline-reply-box') // reply inline
    ) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                viewFile(files[currentFileIndex].id);
            } else {
                alert("Erreur lors de l'envoi du message.");
            }
        })
        .catch(error => console.error("Erreur lors de l'envoi du message:", error));
    }
}, true);

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
 

.spinner {
  border: 4px solid rgba(51, 51, 51, 0.3);  /* Couleur #333 en semi-transparent */
    border-top: 4px solid #333;     
    border-radius: 50%;
    width: 18px;
    height: 18px;
    animation: spin 1s linear infinite;
    display: inline-block;
    margin-right: 10px;
    vertical-align: middle;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}


</style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
 
document.addEventListener('DOMContentLoaded', function () {
    // Ajout des événements de double-clic pour toutes les sections
    document.getElementById('achat-div').addEventListener('dblclick', function () {
        window.location.href = '{{ route("achat.view") }}';
    });
});
function handleFileSelect(event, type) {
    const fileInput = document.getElementById('file-' + type.toLowerCase());
    if (!fileInput.files.length) {
        alert("Veuillez sélectionner un fichier.");
        return;
    }

    const formData = new FormData();
    for (let i = 0; i < fileInput.files.length; i++) {
        formData.append('files[]', fileInput.files[i]);
    }
    formData.append('type', type);
    formData.append('societe_id', '{{ session()->get('societeId') }}');
    formData.append('folders', '{{ session()->get('foldersId') }}');
formData.append('exercice_debut', '{{ $societe->exercice_social_debut }}');
formData.append('exercice_fin', '{{ $societe->exercice_social_fin }}');
showSuccessNotification("Importation en cours...", true);


    fetch('{{ route('uploadFile') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
removeSuccessNotification();
      if (data.exists) {
    Swal.fire({
        title: `Le fichier "${data.name}" existe déjà.`,
        text: "Voulez-vous continuer ?",
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Oui',
        cancelButtonText: 'Non'
    }).then((result) => {
        if (result.isConfirmed) {
            formData.append('force', 1);

showSuccessNotification("Importation en cours...", true);

            fetch('{{ route('uploadFile') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(() => {
                removeSuccessNotification();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Fichier importé avec succès !',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }
    });
}else if (data.success) {
            showSuccessNotification("Fichier importé avec succès !");
            setTimeout(() => location.reload(), 1500);
        } else {
            showSuccessNotification("Erreur lors de l'import.");
        }
    })
    .catch(error => {
        removeSuccessNotification();
        showSuccessNotification('Erreur réseau : ' + error);
    });
}


// Ajoutez/remplacez ces fonctions utilitaires :
let notifDiv = null;
function showSuccessNotification(message, withSpinner = false) {
    if (!notifDiv) {
        notifDiv = document.createElement('div');
        notifDiv.style.position = 'fixed';
        notifDiv.style.top = '30px';
        notifDiv.style.right = '30px';
        notifDiv.style.background = 'white'; // ✅ vert succès
notifDiv.style.color = '#333';
        notifDiv.style.padding = '16px 28px';
        notifDiv.style.borderRadius = '8px';
        notifDiv.style.fontSize = '16px';
        notifDiv.style.zIndex = 9999;
        notifDiv.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
        notifDiv.style.transition = 'opacity 0.5s';
        notifDiv.style.display = 'flex';
        notifDiv.style.alignItems = 'center';
        document.body.appendChild(notifDiv);
    }

    notifDiv.innerHTML = withSpinner
        ? `<div class="spinner"></div><span>${message}</span>`
        : message;

    notifDiv.style.opacity = '1';
}
function removeSuccessNotification() {
    if (notifDiv) {
        notifDiv.style.opacity = '0';
        setTimeout(() => {
            if (notifDiv && notifDiv.parentNode) notifDiv.parentNode.removeChild(notifDiv);
            notifDiv = null;
        }, 500);
    }
}

function openCreateFolderForm() {
    var myModal = new bootstrap.Modal(document.getElementById('createFolderModal'));
    myModal.show();
}

function openFile(folderId) {
    window.location.href = '/foldersPaie1/' + folderId;
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
     const idx = files.findIndex(f => f.id == fileId);
    if (idx !== -1) {
        currentFileIndex = idx;
    }

    
    const file = files.find(f => f.id === fileId); // Trouver le fichier actuel
    if (!file) return;

    // Mettre à jour le titre du fichier
    const fileTitleElement = document.querySelector('#fileModal h6');
    if (fileTitleElement) {
        fileTitleElement.textContent = file.name; // Mettre à jour le titre
    }
   

    // Mettre à jour le lien de téléchargement
    // ...existing code...
const downloadButton = document.querySelector('.action-buttons a.btn-primary');
if (downloadButton) {
    downloadButton.href = file.path.startsWith('http') ? file.path : '{{ asset('') }}' + file.path;
    downloadButton.setAttribute('download', file.name);
}
const printButton = document.querySelector('.action-buttons a.btn-secondary');
if (printButton) {
    printButton.setAttribute('onclick', `printFile('${file.path.startsWith('http') ? file.path : '{{ asset('') }}' + file.path}')`);
}
// ...existing code...
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
                            renderMessages(data.messages);

                    console.log(data);
                    highlightComments(data.messages); // Appeler la fonction pour surligner les commentaires

                    const messagesContainer = document.getElementById("messages-container");
                    messagesContainer.innerHTML = ''; // Vider le conteneur avant d'ajouter les nouveaux messages

                    data.messages.forEach(function(message) {
var messageDiv = document.createElement("div");
messageDiv.classList.add("message");
messageDiv.setAttribute("data-message-id", message.id); // Ajoute l'ID ici
    
    // Création du message
    var userMessage = document.createElement("p");
    userMessage.innerHTML = `<strong>${message.user_name}:</strong></br/><i style="font-size:10px;">  ${message.created_at}</i> </br/><p style="font-size:18px;"> ${message.text_message} </p>`;
    console.log("Message:", message);

    // Vérifiez si le message a un commentaire
// Dans la boucle où vous créez les messages
// if (message.commentaire !== null) {
//     var commentIcon = document.createElement("i");
//     commentIcon.classList.add("fas", "fa-comment");
//     commentIcon.title = "Ce message a un commentaire";
//     userMessage.appendChild(commentIcon);

//     // Ajoutez l'événement de clic ici
//     commentIcon.addEventListener("click", function() {
//         handleCommentClick(message.commentaire); // Passer le texte du commentaire à la fonction
//     });
// }


// Ajoute cette fonction juste après renderMessages ou à la fin de ton script
// Place cette fonction tout en haut de ton <script> principal, AVANT le addEventListener du formulaire
// Placez ceci AVANT le addEventListener du formulaire
function reloadMessages(fileId) {
    fetch(`/messages/getMessages/${fileId}`)
        .then(response => response.json())
        .then(data => {
            renderMessages(data.messages);
        })
        .catch(error => console.error("Erreur lors du rechargement des messages:", error));
}

// ...puis le reste de votre script...
// if (!window.messageFormListenerAdded) {
//     document.getElementById('messageForm').addEventListener('submit', function(event) {
//         event.preventDefault();
//         const formData = new FormData(this);
//         fetch("{{ route('messages.store') }}", {
//             method: 'POST',
//             body: formData,
//             headers: {
//                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
//             }
//         })
//         .then(response => response.json())
      
//         .then(data => {
            
//             if (data.success) {
//                 const fileId = document.querySelector('input[name="file_id"]').value;
//                 document.getElementById("message_text").value = '';
//                 viewFile(parseInt(fileId)); // <-- Toujours rappeler viewFile même si c'est le premier message
//                 highlightComments(data.comments); // Assurez-vous que `data.comments` contient les commentaires à colorer

            
//             } else {
//                 alert("Erreur lors de l'envoi du message.");
//             }
//         })

//         .catch(error => console.error("Erreur lors de l'envoi du message:", error));
//     });
//     window.messageFormListenerAdded = true;
// }


messageDiv.addEventListener("mouseover", function() {
    highlightComments(data.messages, message.commentaire); // Passe le commentaire du message survolé
   if (message.commentaire) {
        showBigFlesh(messageDiv, message.commentaire); // Nouvelle fonction pour la grande flèche
    }
});
messageDiv.addEventListener("mouseout", function() {
    highlightComments(data.messages); // Remet le surlignage normal
        removeBigFlesh(); // Supprimer la flèche quand on quitte le message

});



function showBigFlesh(messageDiv, commentText) {
    removeBigFlesh();

    const textLayer = document.querySelector('.textLayer');
    if (!textLayer) return;
    const textElements = textLayer.getElementsByTagName('span');
    let targetSpan = null;
    for (let el of textElements) {
        if (el.textContent.trim().includes(commentText.trim())) {
            targetSpan = el;
            break;
        }
    }
    if (!targetSpan) return;

    const spanRect = targetSpan.getBoundingClientRect();
    const msgRect = messageDiv.getBoundingClientRect();

    // Départ : coin supérieur gauche du message
    const startX = msgRect.left;
    const startY = msgRect.top;
    // Arrivée : coin inférieur droit du commentaire
    const endX = spanRect.right;
    const endY = spanRect.bottom;

    // Calcul du cône
    const dx = endX - startX;
    const dy = endY - startY;
    const length = Math.sqrt(dx * dx + dy * dy);
    const coneWidth = 24;
    const halfWidth = coneWidth / 2;
    const nx = -dy / length;
    const ny = dx / length;

    // Points du triangle (large côté message, pointe côté commentaire)
    const p1x = startX + nx * halfWidth;
    const p1y = startY + ny * halfWidth;
    const p2x = startX - nx * halfWidth;
    const p2y = startY - ny * halfWidth;
    const p3x = endX;
    const p3y = endY;

    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.id = "big-flesh-svg";
    svg.style.position = "fixed";
    svg.style.left = "0";
    svg.style.top = "0";
    svg.style.width = "100vw";
    svg.style.height = "100vh";
    svg.style.pointerEvents = "none";
    svg.style.zIndex = "2000";
    
    const polygon = document.createElementNS("http://www.w3.org/2000/svg", "polygon");
    polygon.setAttribute("points", `${p1x},${p1y} ${p2x},${p2y} ${p3x},${p3y}`);
    polygon.setAttribute("fill", "yellow");
    polygon.setAttribute("opacity", "0.7");

    svg.appendChild(polygon);
    document.body.appendChild(svg);
}

// Fonction pour supprimer la flèche
function removeBigFlesh() {
    const oldSvg = document.getElementById("big-flesh-svg");
    if (oldSvg) oldSvg.remove();
}











// Affiche une flèche du commentaire (dans le PDF) vers son message (dans la chat-box)

function showFleshFromCommentToMessage(commentText, hoveredSpan = null) {
    removeBigFlesh();

    // Utiliser le span survolé si fourni, sinon le chercher
    let targetSpan = hoveredSpan;
    if (!targetSpan) {
        const textLayer = document.querySelector('.textLayer');
        if (!textLayer) return;
        const textElements = textLayer.getElementsByTagName('span');
        for (let el of textElements) {
            if (el.textContent.trim().includes(commentText.trim())) {
                targetSpan = el;
                break;
            }
        }
    }
    if (!targetSpan) return;

    // Trouver le message correspondant dans la chat-box
    const messages = document.querySelectorAll('#messages-container .message');
    let targetMessage = null;
    messages.forEach(msg => {
        if (msg.innerText.includes(commentText.trim())) {
            targetMessage = msg;
        }
    });
    if (!targetMessage) return;

    // Scroll le message dans la vue
    targetMessage.scrollIntoView({ behavior: "smooth", block: "center" });

    // Surligner le message
    targetMessage.style.backgroundColor = "orange";
    setTimeout(() => {
        targetMessage.style.backgroundColor = "";
    }, 1000);

    // Attendre que le scroll soit terminé avant de dessiner la flèche
   
setTimeout(() => {
    const spanRect = targetSpan.getBoundingClientRect();
    const msgRect = targetMessage.getBoundingClientRect();

    // Point de départ : bord droit du commentaire
    const startX = spanRect.right;
    const startY = spanRect.top + (spanRect.height / 2);
    // Point d'arrivée : bord gauche du message
    const endX = msgRect.left;
    const endY = msgRect.top + (msgRect.height / 2);

    // Calculer la direction du vecteur
    const dx = endX - startX;
    const dy = endY - startY;
    const length = Math.sqrt(dx * dx + dy * dy);

    // Largeur du cône (ajustez selon vos besoins)
    const coneWidth = 20; // largeur à la base (départ)
    const halfWidth = coneWidth / 2;

    // Calculer le vecteur perpendiculaire normalisé
    const nx = -dy / length;
    const ny = dx / length;

    // Points du triangle (cône)
    const p1x = startX + nx * halfWidth;
    const p1y = startY + ny * halfWidth;
    const p2x = startX - nx * halfWidth;
    const p2y = startY - ny * halfWidth;
    const p3x = endX;
    const p3y = endY;

    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.id = "big-flesh-svg";
    svg.style.position = "fixed";
    svg.style.left = "0";
    svg.style.top = "0";
    svg.style.width = "100vw";
    svg.style.height = "100vh";
    svg.style.pointerEvents = "none";
    svg.style.zIndex = "2000";

    // Créer le triangle (cône)
    const polygon = document.createElementNS("http://www.w3.org/2000/svg", "polygon");
    polygon.setAttribute("points", `${p1x},${p1y} ${p2x},${p2y} ${p3x},${p3y}`);
    polygon.setAttribute("fill", "yellow");
    polygon.setAttribute("opacity", "0.7");

    svg.appendChild(polygon);
    document.body.appendChild(svg);
}, 400); // 400ms pour laisser le scroll se faire

}


// Ajoutez ce bloc après le rendu du PDF et l'appel à highlightComments

document.addEventListener('mouseover', function(event) {
    const span = event.target.closest('.highlight1, .highlight2');
    if (span && span.getAttribute('data-comment')) {
        showFleshFromCommentToMessage(span.getAttribute('data-comment'), span);

        // Surligner le message et ses réponses
        const comment = span.getAttribute('data-comment');
        document.querySelectorAll('#messages-container .message').forEach(msg => {
            if (msg.innerText.includes(comment)) {
                msg.style.backgroundColor = 'yellow';
                // Surligner aussi les réponses
                msg.querySelectorAll('.message').forEach(reply => {
                    reply.style.backgroundColor = 'yellow';
                });
            }
        });
    }
});

document.addEventListener('mouseout', function(event) {
    const span = event.target.closest('.highlight1, .highlight2');
    if (span) {
        removeBigFlesh();
        // Retirer le surlignage jaune
        document.querySelectorAll('#messages-container .message').forEach(msg => {
            msg.style.backgroundColor = '';
            msg.querySelectorAll('.message').forEach(reply => {
                reply.style.backgroundColor = '';
            });
        });
    }
});



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
viewFile(parseInt(fileId))
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
                            replyForm.className = "inline-reply-box"; 
                            replyForm.method = "POST";
                            replyForm.innerHTML = `@csrf
                                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                                <input type="hidden" name="file_id" value="{{ $file->id ?? 'null' }}">
                                <input type="hidden" name="reply_to_message_id" value="${message.id}">
                                <input type="hidden" name="societe_id" value="{{ session('societeId') }}">
                                <textarea name="text_message" placeholder="Répondre..." style="width: 100%; height: 60px;"></textarea>
                                <div style="display: flex; width: 100%; gap: 0;">
  <!-- Bouton Envoyer -->
  <button type="submit" style="
    width: 50%;
    background-color: #28a745;
    color: #ffffff;
    border: none;
    padding: 10px 0;
    font-size: 16px;
    border-radius: 4px 0 0 4px;
    cursor: pointer;
  ">
    Envoyer
  </button>

  <!-- Bouton Annuler -->
  <button type="button" class="cancel-reply" style="
  margin-left:8px;
    width: 50%;
    background-color: #28a745;
    color: #ffffff;
    border: none;
    padding: 10px 0;
    font-size: 16px;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
  ">
    Annuler
  </button>
</div>
`;

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
viewFile(parseInt(fileId))
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
    // Créer un div qui contiendra les deux icônes superposées
    commentIcon = document.createElement("div");
    commentIcon.style.position = "absolute";
    commentIcon.style.cursor = "pointer";
    commentIcon.style.zIndex = "1500";
    commentIcon.style.width = "40px";
    commentIcon.style.height = "40px";
    commentIcon.style.display = "flex";
    commentIcon.style.alignItems = "center";
    commentIcon.style.justifyContent = "center";
    // commentIcon.style.backgroundColor = "#ffffff";
    // commentIcon.style.border = "2px solid #1a73e8";
    commentIcon.style.borderRadius = "8px";
    // commentIcon.style.boxShadow = "0 2px 5px rgba(0,0,0,0.1)";
    // commentIcon.style.transition = "background-color 0.3s";

    // Créer un conteneur pour superposer les icônes
    const iconStack = document.createElement("span");
    iconStack.className = "fa-stack";
    iconStack.style.fontSize = "16px";

    // Icône commentaire (en arrière-plan)
    const commentBase = document.createElement("i");
    commentBase.className = "fas fa-comment fa-stack-2x";
    commentBase.style.color = "#1a73e8";

    // Icône plus (au-dessus)
    const plusIcon = document.createElement("i");
    plusIcon.className = "fas fa-plus fa-stack-1x";
    plusIcon.style.color = "#ffffff";

    // Ajouter les deux icônes au stack
    iconStack.appendChild(commentBase);
    iconStack.appendChild(plusIcon);

    // Ajouter le stack au bouton
    commentIcon.appendChild(iconStack);

    // Effets de survol
    // commentIcon.addEventListener("mouseenter", () => {
    //     // commentIcon.style.backgroundColor = "#e8f0fe";
    // });

    // commentIcon.addEventListener("mouseleave", () => {
    //     // commentIcon.style.backgroundColor = "#ffffff";
    // });

    document.body.appendChild(commentIcon);
}


        // Positionner l'icône à droite de la sélection
        const range = selection.getRangeAt(0);
        const rect = range.getBoundingClientRect();
        commentIcon.style.top = `${rect.bottom + window.scrollY + 5}px`; // Positionner juste en dessous de la sélection
        commentIcon.style.left = `${rect.right + window.scrollX + 5}px`; // Positionner à droite de la sélection, avec une marge de 5px
        commentIcon.style.display = "flex"; // Afficher l'icône dans le carré

        // Ajouter un événement de clic à l'icône
        // ...dans l'écouteur mouseup sur filePreviewContent...
commentIcon.onclick = function() {
    // Ne pas insérer le texte sélectionné dans la zone de message
    const messageText = document.getElementById("message_text");
    messageText.value = ""; // Laisser vide

    // Ajouter ou mettre à jour un champ caché pour le commentaire
    let commentInput = document.getElementById("selected_comment");
    if (!commentInput) {
        commentInput = document.createElement("input");
        commentInput.type = "hidden";
        commentInput.name = "commentaire";
        commentInput.id = "selected_comment";
        messageText.form.appendChild(commentInput);
    }
    commentInput.value = selectedText;
    searchAndHighlightText(commentInput.value);
 const textarea = document.getElementById("message_text");
    textarea.placeholder = "commentez ici...";
    messageText.focus();
    commentIcon.style.display = "none";
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
 
document.addEventListener('click', function (event) {
    const span = event.target.closest('.highlight1');
    if (span) {
        const comment = span.getAttribute('data-comment');
        if (comment) {
            // Chercher tous les messages dans la boîte de messages
            const messages = document.querySelectorAll('#messages-container .message');
            messages.forEach(msg => {
    if (msg.innerText.includes(comment)) {
        const messageId = msg.getAttribute('data-message-id');
        showReplyBox(msg, comment, messageId); // Passe l'ID ici
    }
});
        }
    }
});


// SUPPRIMEZ ou commentez ce bloc pour que la boîte reste affichée
// document.addEventListener('mouseout', function (event) {
//     const span = event.target.closest('.highlight1');
//     if (span) {
//         // Masquer toutes les boîtes de réponse affichées par ce survol
//         hideAllReplyBoxes();
//     }
// });

// Fonction pour afficher la boîte de réponse sous le message correspondant

// Fonction pour afficher la boîte de réponse sous le message correspondant
function showReplyBox(messageDiv, comment, messageId) {
    // Vérifier si une boîte de réponse existe déjà
    if (messageDiv.querySelector('.inline-reply-box')) return;

    // Créer la boîte de réponse
    const replyBox = document.createElement('form');
    replyBox.className = 'inline-reply-box';
    replyBox.action = "{{ route('messages.store') }}";
    replyBox.method = "POST";
    replyBox.style.marginTop = "10px";
    replyBox.innerHTML = `@csrf
        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
        <input type="hidden" name="file_id" value="{{ $file->id ?? 'null' }}">
    <input type="hidden" name="reply_to_message_id" value="${messageId}">
        <input type="hidden" name="societe_id" value="{{ session('societeId') }}">
        <textarea name="text_message" placeholder="Répondre..." style="width: 100%; height: 60px;"></textarea>
       <div style="display: flex; width: 100%; gap: 0;">
  <!-- Bouton Envoyer -->
  <button type="submit" style="
    width: 50%;
    background-color: #28a745;
    color: #ffffff;
    border: none;
    padding: 10px 0;
    font-size: 16px;
    border-radius: 4px 0 0 4px;
    cursor: pointer;
  ">
    Envoyer
  </button>

  <!-- Bouton Annuler -->
  <button type="button" class="cancel-reply" style="
  margin-left:8px;
    width: 50%;
    background-color: #28a745;
    color: #ffffff;
    border: none;
    padding: 10px 0;
    font-size: 16px;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
  ">
    Annuler
  </button>
</div>
    `;

    // Annuler la réponse
    replyBox.querySelector('.cancel-reply').onclick = function() {
        replyBox.remove();
    };

    // Focus sur le textarea dès l'affichage
    setTimeout(() => {
        const textarea = replyBox.querySelector('textarea[name="text_message"]');
        if (textarea) textarea.focus();
    }, 0);

    // Empêcher le submit classique (optionnel)
    replyBox.onsubmit = function(e) {
        // e.preventDefault();
        // ...votre logique AJAX ici si besoin...
    };

    messageDiv.appendChild(replyBox);
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

    // Nettoyer tous les anciens surlignages (rouge et jaune)
    for (let el of textElements) {
        el.classList.remove('highlight', 'highlight1');
        el.style.backgroundColor = '';
    }

    let found = false;
    for (let i = 0; i < textElements.length; i++) {
        if (textElements[i].textContent.trim().includes(text.trim())) {
            textElements[i].classList.add('highlight');
            found = true;
            break; // Surligne la première occurrence
        }
    }
    if (!found) {
        console.log("Texte non trouvé");
    }
}

function highlightComments(comments, darkComment = null) {
    const textLayer = document.querySelector('.textLayer');
    if (!textLayer) return;
    const textElements = textLayer.getElementsByTagName('span');

    // Nettoyer les anciens surlignages
    for (let el of textElements) {
        el.classList.remove('highlight1', 'highlight2');
        el.style.backgroundColor = '';
        el.removeAttribute('data-comment');
    }

    comments.forEach(comment => {
        if (!comment.commentaire || comment.commentaire.trim() === "") return;
        const commentText = comment.commentaire;
        const messageText = comment.text_message;
        for (let i = 0; i < textElements.length; i++) {
            if (textElements[i].textContent.trim().includes(commentText.trim())) {
                if (darkComment && commentText.trim() === darkComment.trim()) {
                    textElements[i].classList.add('highlight2');
                    textElements[i].style.backgroundColor = 'orange';
                } else {
                    textElements[i].classList.add('highlight1');
                    textElements[i].style.backgroundColor = 'yellow';
                }
                textElements[i].setAttribute('data-comment', messageText);
            }
        }
    });
}
// ...dans la fonction viewFile, après le rendu PDF...
pdfjsLib.getDocument({ url: fileURL }).promise.then(pdf => {
    // ...rendu des pages...
    for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
        pdf.getPage(pageNum).then(page => {
            // ...rendu canvas...
            page.render({ canvasContext: context, viewport }).promise.then(() => {
                // Attendre que la couche de texte soit ajoutée
                page.getTextContent().then(textContent => {
                    // ...création de la textLayer...
                    pdfjsLib.renderTextLayer({
                        textContent,
                        container: textLayerDiv,
                        viewport,
                        textDivs: []
                    }).promise.then(() => {
                        // Appeler highlightComments ici, quand la textLayer est prête
                        highlightComments(data.messages);
                    });
                });
            });
        });
    }
});



    function openRenameModal(folderId, folderName) {
        document.getElementById('newFolderName').value = folderName;
        document.getElementById('renameFolderForm').action = '/folder/' + folderId; // Met à jour l'action du formulaire avec l'ID du dossier
        var myModal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
        myModal.show();
    }

</script>

 
<style>
    
#messages-container {
    display: flex;
    flex-direction: column-reverse; /* Les messages s'empilent du bas vers le haut */
    justify-content: flex-start;
    height: 80%;
    overflow-y: auto;
}
    .highlight1 {
    background-color: yellow; /* Surlignage en rouge */
        opacity: 0.5;

 }
.highlight2 {
    background-color: yellow !important;
    opacity: 0.8;
}
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

