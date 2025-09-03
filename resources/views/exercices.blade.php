@extends('layouts.user_type.auth')

@section('content')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<br>
   <h5 style="color:#4D55CC;">Tableau De Board</h5>
<div class="container mt-4">
 
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm" style="border-radius: 15px; font-size: 0.75rem; height: 130px;" onclick="openCreateFolderForm()">
                <div class="card-body text-center d-flex flex-column justify-content-center align-items-center" style="height: 100%; background-color: #f8f9fa; border-radius: 15px;">
                    <i class="fas fa-plus fa-2x " style="color:#007bff;"></i>
                    
                    <p class="mt-1" style="font-size: 0.8rem;">Ajouter un Dossier</p>
                </div>
            </div>
        </div>

 <!-- Achat -->
<div class="col-md-3 mb-3" id="achat-div">
    <div class="p-2 text-white" style="background-color: #007bff; border-radius: 15px; font-size: 0.75rem; height: 130px;">
        <div class="d-flex justify-content-between align-items-center">
            <h5 style="color: white;">Achat</h5>
            <form id="form-achat" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="achat">
                 <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                <input type="hidden" name="folders_id" value="0">
    <input type="file" name="files[]" id="file-achat" style="display: none;" onchange="handleFileSelect(event, 'achat')" multiple>

                <!-- Dropdown sans flèche -->
                <div class="">
                    <button 
                        class="btn btn-light btn-sm" 
                        type="button" 
                        id="dropdownMenuButton" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false"
                        style="background-color: #007bff; border: 1px solid white; border-radius: 10px; color: white; width: 100px;">
                        Charger
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="#" onclick="handleUpload('importer')">Importer</a></li>
                        <!-- <li><a class="dropdown-item" href="#" onclick="handleUpload('scanner')">Scanner</a></li> -->
                        <li><a class="dropdown-item" href="#" onclick="handleUpload('fusionner')">Fusionner</a></li>
                    </ul>
                </div>

                <button type="submit" style="display: none;" id="submit-achat">Envoyer</button>
            </form>
        </div>
        <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['Achat'] ?? $fileCounts['achat'] ?? 0 }}</p>
        <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : </p>
        <!-- <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : </p> -->
    </div>
</div>

<!-- 📁 Champ de fichiers caché pour fusion -->
<input type="file" id="filesToMergeHidden" multiple accept="application/pdf,image/*" style="display: none;" onchange="mergeFilesDirect(event)">

<!-- 📦 PDF.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<!-- PDFLib nécessaire pour merge -->
<script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>

<!-- 🧠 Script principal -->
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

    function handleUpload(option) {
        if (option === 'importer') {
            document.getElementById('file-achat').click();
        } else if (option === 'scanner') {
            alert("Fonction de scan non implémentée.");
        } else if (option === 'fusionner') {
            document.getElementById('filesToMergeHidden').click();
        }
    }

    async function mergeFilesDirect(event) {
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
        await mergeFiles(selectedFiles);
    } 
async function mergeFiles(files) {
    const mergedPdf = await PDFLib.PDFDocument.create();

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const rotation = rotations[i] || 0;
        const arrayBuffer = await file.arrayBuffer();
        const fileType = file.type;

        if (fileType === 'application/pdf') {
            const pdf = await PDFLib.PDFDocument.load(arrayBuffer);
            const copiedPages = await mergedPdf.copyPages(pdf, pdf.getPageIndices());
            copiedPages.forEach((page) => {
                if (rotation !== 0) {
                    page.setRotation(PDFLib.degrees(rotation));
                }
                mergedPdf.addPage(page);
            });
        } else if (fileType.startsWith('image/')) {
            // Dimensions page portrait A4 en points
            const pageWidthPortrait = 595.28;
            const pageHeightPortrait = 841.89;
            let pageWidth, pageHeight;

            // Inverser dimensions si rotation 90 ou 270 (paysage)
            if (rotation === 90 || rotation === 270) {
                pageWidth = pageHeightPortrait;
                pageHeight = pageWidthPortrait;
            } else {
                pageWidth = pageWidthPortrait;
                pageHeight = pageHeightPortrait;
            }

            const imageBytes = new Uint8Array(arrayBuffer);
            let image;

            if (fileType === 'image/png') {
                image = await mergedPdf.embedPng(imageBytes);
            } else if (fileType === 'image/jpeg' || fileType === 'image/jpg') {
                image = await mergedPdf.embedJpg(imageBytes);
            } else {
                console.warn(`Type d'image non supporté: ${fileType}`);
                continue;
            }

            const rotated = (rotation % 180) !== 0;

            // Calcul taille cible en conservant ratio et en s'adaptant à la rotation
            const targetWidth = rotated ? pageHeight * 0.9 : pageWidth * 0.9;
            const scale = targetWidth / (rotated ? image.height : image.width);
            const targetHeight = (rotated ? image.width : image.height) * scale;

            // Calcul centre de la page
            const centerX = pageWidth / 2;
            const centerY = pageHeight / 2;

            // Calcul position pour dessiner l'image centrée autour de 0,0 (après translation)
            const imgX = - (rotated ? targetHeight : targetWidth) / 2;
            const imgY = - (rotated ? targetWidth : targetHeight) / 2;

            // Créer la page avec la bonne taille
            const page = mergedPdf.addPage([pageWidth, pageHeight]);

            // Appliquer transformation centrée : translation puis rotation
            page.pushOperators(
                PDFLib.pushGraphicsState(),
                PDFLib.translate(centerX, centerY),
                PDFLib.rotateDegrees(rotation),
            );

            // Dessiner l'image centrée
            page.drawImage(image, {
                x: imgX,
                y: imgY,
                width: rotated ? targetHeight : targetWidth,
                height: rotated ? targetWidth : targetHeight,
            });

            // Restaurer contexte graphique
            page.pushOperators(
                PDFLib.popGraphicsState()
            );
        }
    }

    // Sauvegarde et envoi du PDF fusionné
    const mergedPdfBytes = await mergedPdf.save();
    const blob = new Blob([mergedPdfBytes], { type: 'application/pdf' });

    const formData = new FormData();
const fileNameBase = files.map(f => f.name.replace(/\.[^/.]+$/, '')).join('_');

// Limiter la longueur si trop long (optionnel)
const safeFileName = fileNameBase.substring(0, 100).replace(/[^\w\-]/g, '_');

formData.append('file', blob, `${safeFileName}_${Date.now()}.pdf`);    formData.append('societe_id', '{{ session()->get('societeId') }}'); // Laravel dynamique
    formData.append('type', 'achat');

    const response = await fetch('/uploadFusionner', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    const data = await response.json();

    if (data.success) {
        const messageDiv = document.createElement('div');
        messageDiv.textContent = "Fichiers fusionnés avec succès !";
        Object.assign(messageDiv.style, {
            position: 'fixed',
            top: '150px',
            left: '50%',
            transform: 'translateX(-50%)',
            backgroundColor: '#4CAF50',
            color: 'white',
            padding: '10px 20px',
            borderRadius: '5px',
            zIndex: 9999
        });
        document.body.appendChild(messageDiv);
        setTimeout(() => location.reload(), 2000);
    } else {
        alert("Erreur lors de l'envoi.");
    }
}

 function addFile() {
    document.getElementById('filesToMergeHidden').click();
}
</script>
 
<!-- 💬 Modal HTML -->

<div id="fileOrderModal" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModal()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px; height: 100%;">
        <ul id="fileList" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
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
            <button onclick="confirmOrder()" style="background-color: #007BFF; color: white; padding: 10px 16px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s; margin-top: 20px;">Valider</button>
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
     /* padding: 20px; */
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

    



      <!-- Vente -->
<div class="col-md-3 mb-3" id="vente-div">
    <div class="p-2 text-white" style="background-color: #28a745; border-radius: 15px; font-size: 0.75rem; height: 130px;">
        <div class="d-flex justify-content-between align-items-center">
            <h5 style="color: white;">Vente</h5>
            <form id="form-vente" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="vente">
                <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
<input type="file" name="files[]" id="file-vente" style="display: none;" multiple onchange="handleFileSelect(event, 'vente')">

                <!-- ✅ Menu déroulant personnalisé -->
                <div class="">
                    <button 
                        class="btn btn-light btn-sm" 
                        type="button" 
                        id="dropdownMenuButtonVente" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false"
                        style="background-color: #28a745; border: 1px solid white; border-radius: 10px; color: white; width:100px;">
                        Charger
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonVente">
                        <li><a class="dropdown-item" href="#" onclick="handleUploadVente('importer')">Importer</a></li>
                        <!-- <li><a class="dropdown-item" href="#" onclick="handleUploadVente('scanner')">Scanner</a></li> -->
                        <li><a class="dropdown-item" href="#" onclick="handleUploadVente('fusionner')">Fusionner</a></li>
                    </ul>
                </div>

                <!-- Bouton d'envoi caché -->
                <button type="submit" style="display: none;" id="submit-vente">Envoyer</button>
            </form>
        </div>

        <!-- ✅ Statistiques -->
        <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['vente'] ?? $fileCounts['Vente'] ?? 0 }}</p>
        <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : </p>
        <!-- <p style="font-size : 0.7rem; line-height: 0.3;">pièces suspendues : </p> -->
    </div>
</div>

<!-- Input caché pour fusion Vente -->
<input type="file" id="filesToMergeVenteHidden" multiple style="display: none;" onchange="mergeFilesVenteDirect(event)">



<!-- 💬 Modal HTML -->
<div id="fileOrderModalVente" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModalVente()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;height:120%;">

        <ul id="fileListVente" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        <!-- Colonne verticale à droite -->
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; gap: 18px; height: 100%; margin-left: 10px;">
            <span onclick="addFileVente()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
            <span id="sortButtonVente" onclick="sortFilesVente()" 
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
            <button onclick="confirmOrderVente()" style="background-color: #007BFF; color: white; padding: 0 6px; font-size: 11px; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.2s; width: auto; min-width: 0; margin-top: 0; display: flex; align-items: center; justify-content: center; height: 16px; min-height: 0; max-height: 35px; line-height: 35px; box-sizing: border-box;">Valider</button>
        </div>
    </div>

</div>

 
 
<script>
    let selectedFilesVente = [];
let rotationsVente = [];
let sortOrderVente = 'asc';


function sortFilesVente() {
    // Inverser l'ordre de tri
    sortOrderVente = sortOrderVente === 'asc' ? 'desc' : 'asc';

    // Trier les fichiers en fonction de l'ordre
    selectedFilesVente.sort((a, b) => {
        return sortOrderVente === 'asc' 
            ? a.name.localeCompare(b.name) 
            : b.name.localeCompare(a.name);
    });

    // Mettre à jour l'affichage
    populateFileListVente();

    // Changer le texte et la couleur du bouton pour indiquer l'ordre actuel
    const sortButtonVente = document.getElementById('sortButtonVente');
    sortButtonVente.innerHTML = sortOrderVente === 'asc' 
        ? '<span style="font-size: 20px;">&#8593;</span><div style="line-height: 1;"><div style="font-size: 9px;">Z</div><div style="font-size: 9px;">A</div></div>'
        : '<span style="font-size: 20px;">&#8595;</span><div style="line-height: 1;"><div style="font-size: 9px;">A</div><div style="font-size: 9px;">Z</div></div>';
    
    sortButtonVente.style.backgroundColor = sortOrderVente === 'asc' ? '#cb0c9f' : '#e74c3c'; // Couleur pour A-Z et Z-A
}
function handleUploadVente(option) {
    if (option === 'importer') {
        document.getElementById('file-vente').click();
    } else if (option === 'scanner') {
        alert("Fonction de scan non implémentée.");
    } else if (option === 'fusionner') {
        document.getElementById('filesToMergeVenteHidden').click();
    }
}

function mergeFilesVenteDirect(event) {
    const newFiles = Array.from(event.target.files);
    if (!newFiles.length) return;
    selectedFilesVente.push(...newFiles);
    for (let i = 0; i < newFiles.length; i++) {
        rotationsVente.push(0);
    }
    if (selectedFilesVente.length < 2) {
        alert("Veuillez sélectionner au moins deux fichiers.");
        return;
    }
    showModalVente();
    populateFileListVente();
}


function showModalVente() {
    document.getElementById('fileOrderModalVente').style.display = 'block';
}

function closeModalVente() {
    document.getElementById('fileOrderModalVente').style.display = 'none';
}


  function drawImageWithRotationVente(ctx, img, rotationDeg, canvasWidth, canvasHeight) {
    const radians = rotationDeg * Math.PI / 180;
    ctx.clearRect(0, 0, canvasWidth, canvasHeight);
    ctx.save();
    ctx.translate(canvasWidth / 2, canvasHeight / 2);
    ctx.rotate(radians);

    const scale = Math.min(canvasWidth / img.width, canvasHeight / img.height);
    ctx.drawImage(img, -img.width * scale / 2, -img.height * scale / 2, img.width * scale, img.height * scale);
    ctx.restore();
  }

  function rotateImageVente(canvas, index) {
    rotationsVente[index] = (rotationsVente[index] + 90) % 360;
    const ctx = canvas.getContext('2d');
    const file = selectedFilesVente[index];
    const reader = new FileReader();

    reader.onload = function(e) {
      if (file.type.startsWith('image/')) {
        const img = new Image();
        img.onload = function() {
          drawImageWithRotationVente(ctx, img, rotationsVente[index], canvas.width, canvas.height);
        };
        img.src = URL.createObjectURL(file);
      }
    };

    reader.readAsArrayBuffer(file);
  }
  function populateFileListVente() {
        const fileListVente = document.getElementById('fileListVente');
        fileListVente.innerHTML = '';

        selectedFilesVente.forEach((file, index) => {
            const listItemVente = document.createElement('li');
            // listItem.style.display = 'flex';
            listItemVente.style.flexDirection = 'column';
            // listItem.style.alignItems = 'center';
            listItemVente.style.marginBottom = '15px';
            listItemVente.style.padding = '10px';
            listItemVente.style.marginLeft = '10px';
            listItemVente.style.backgroundColor = '#ffffff';
            listItemVente.style.border = '1px solid #ddd';
            listItemVente.style.borderRadius = '8px';
            listItemVente.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            listItemVente.style.transition = 'box-shadow 0.2s';
            listItemVente.style.width = '23%';
            listItemVente.draggable = true;

            listItemVente.addEventListener('mouseover', () => {
                listItemVente.style.boxShadow = '0 6px 14px rgba(0,0,0,0.1)';
            });
            listItemVente.addEventListener('mouseout', () => {
                listItemVente.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            });

            const topRowVente = document.createElement('div');
            topRowVente.style.display = 'flex';
            topRowVente.style.alignItems = 'center';
            topRowVente.style.justifyContent = 'space-between';
            topRowVente.style.width = '100%';
            topRowVente.style.marginBottom = '8px';

            const fileInfoVente = document.createElement('div');
            fileInfoVente.style.display = 'flex';
            fileInfoVente.style.alignItems = 'center';
            fileInfoVente.style.flexGrow = '1';
            fileInfoVente.style.gap = '5px';
            fileInfoVente.innerHTML = `<span style="font-size:11px; color:#333; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${file.name}</span>`;

            const iconRowVente = document.createElement('div');
            iconRowVente.style.display = 'flex';
            iconRowVente.style.gap = '10px';

            const deleteIconVente = document.createElement('span');
            deleteIconVente.textContent = '❌';
            deleteIconVente.title = 'Supprimer';
            deleteIconVente.style.cursor = 'pointer';
            deleteIconVente.style.transition = 'color 0.2s';
            deleteIconVente.onmouseover = () => deleteIconVente.style.color = '#e74c3c';
            deleteIconVente.onmouseout = () => deleteIconVente.style.color = 'inherit';
            deleteIconVente.onclick = () => {
                selectedFilesVente.splice(index, 1);
                rotationsVente.splice(index, 1); 
                populateFileListVente();
            };

            const rotateIconVente = document.createElement('span');
            rotateIconVente.textContent = '🔄';
            rotateIconVente.title = 'Rotation';
            rotateIconVente.style.cursor = 'pointer';
            rotateIconVente.style.transition = 'color 0.2s';
            rotateIconVente.onmouseover = () => rotateIconVente.style.color = '#3498db';
            rotateIconVente.onmouseout = () => rotateIconVente.style.color = 'inherit';

            iconRowVente.appendChild(deleteIconVente);
            iconRowVente.appendChild(rotateIconVente);

            topRowVente.appendChild(fileInfoVente);
            topRowVente.appendChild(iconRowVente);

            const previewVente = document.createElement('canvas');
            previewVente.width = 200;
            previewVente.height = 260;
            previewVente.style.border = '1px solid #ccc';
            previewVente.style.borderRadius = '4px';
            previewVente.style.marginBottom = '5px';

            const ctx = previewVente.getContext('2d');
            ctx.fillStyle = "#f0f0f0";
            ctx.fillRect(0, 0, previewVente.width, previewVente.height);

            rotateIconVente.onclick = () => rotateImageVente(previewVente, index);

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
                            const scale = Math.min(previewVente.width / viewport.width, previewVente.height / viewport.height);
                            const scaledViewport = page.getViewport({ scale });
                            ctx.clearRect(0, 0, previewVente.width, previewVente.height);
                            page.render({ canvasContext: ctx, viewport: scaledViewport });
                        });
                    }).catch(console.error);
                } else if (fileType.startsWith('image/')) {
                    const img = new Image();
                    img.onload = function () {
                        drawImageWithRotationVente(ctx, img, rotationsVente[index], previewVente.width, previewVente.height);
                    };
                    img.src = URL.createObjectURL(file);
                }
            };
            reader.readAsArrayBuffer(file);

            listItemVente.appendChild(topRowVente);
            listItemVente.appendChild(previewVente);
            listItemVente.appendChild(legend);

            listItemVente.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', index);
            });
            listItemVente.addEventListener('dragover', (e) => e.preventDefault());
            listItemVente.addEventListener('drop', (e) => {
                e.preventDefault();
                const fromIndex = e.dataTransfer.getData('text/plain');
                moveFileVente(fromIndex, index);
            });

            fileListVente.appendChild(listItemVente);
        });
    }

    function drawImageWithRotationVente(ctx, img, degrees, canvasWidth, canvasHeight) {
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

   function rotateImageVente(previewVente, index) {
    // Incrémente la rotation de 90° (modulo 360)
    rotationsVente[index] = (rotationsVente[index] + 90) % 360;

    // Redessine l'image avec la nouvelle rotation
    const ctx = previewVente.getContext('2d');
    const file = selectedFilesVente[index]; // Récupérer le fichier original
    const reader = new FileReader();

    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            drawImageWithRotationVente(ctx, img, rotationsVente[index], previewVente.width, previewVente.height);
        };
        img.src = e.target.result; // Utiliser le résultat du FileReader
    };
    reader.readAsDataURL(file); // Lire le fichier comme URL de données
}

function drawImageWithRotationVente(ctx, img, degrees, canvasWidth, canvasHeight) {
    ctx.clearRect(0, 0, canvasWidth, canvasHeight);
    ctx.save();
    ctx.translate(canvasWidth / 2, canvasHeight / 2);
    ctx.rotate(degrees * Math.PI / 180);

    let scale = Math.min(canvasWidth / img.width, canvasHeight / img.height);
    let width = img.width * scale;
    let height = img.height * scale;

    if (degrees % 180 !== 0) {
        [width, height] = [height, width];
    }

    ctx.drawImage(img, -width / 2, -height / 2, width, height);
    ctx.restore();
}

function rotateImageVente(previewVente, index) {
    rotationsVente[index] = (rotationsVente[index] + 90) % 360;
    populateFileListVente();
}

function moveFileVente(currentIndex, newIndex) {
    currentIndex = parseInt(currentIndex);
    if (newIndex < 0 || newIndex >= selectedFilesVente.length || currentIndex === newIndex) return;
    const [moved] = selectedFilesVente.splice(currentIndex, 1);
    const [rotMoved] = rotationsVente.splice(currentIndex, 1);
    selectedFilesVente.splice(newIndex, 0, moved);
    rotationsVente.splice(newIndex, 0, rotMoved);
    populateFileListVente();
}

function sortFilesVente() {
    sortOrderVente = sortOrderVente === 'asc' ? 'desc' : 'asc';
    selectedFilesVente.sort((a, b) => {
        return sortOrderVente === 'asc'
            ? a.name.localeCompare(b.name)
            : b.name.localeCompare(a.name);
    });
    populateFileListVente();

    const sortButton = document.getElementById('sortButtonVente');
    sortButton.innerHTML = sortOrderVente === 'asc'
        ? '<span style="font-size: 20px;">&#8595;</span><div style="line-height: 1;"><div style="font-size: 9px;">A</div><div style="font-size: 9px;">Z</div></div>'
        : '<span style="font-size: 20px;">&#8593;</span><div style="line-height: 1;"><div style="font-size: 9px;">Z</div><div style="font-size: 9px;">A</div></div>';

    sortButton.style.backgroundColor = sortOrderVente === 'asc' ? '#cb0c9f' : '#e74c3c';
}

async function confirmOrderVente() {
    closeModalVente();
    await mergeFilesVente();
}

async function mergeFilesVente() {
    try {
        const mergedPdf = await PDFLib.PDFDocument.create();
        for (let i = 0; i < selectedFilesVente.length; i++) {
            const file = selectedFilesVente[i];
            const arrayBuffer = await file.arrayBuffer();
            const fileType = file.type;

            if (fileType === 'application/pdf') {
                const pdf = await PDFLib.PDFDocument.load(arrayBuffer);
                const copiedPages = await mergedPdf.copyPages(pdf, pdf.getPageIndices());
                copiedPages.forEach(page => mergedPdf.addPage(page));
            } else if (fileType.startsWith('image/')) {
                const imagePdf = await PDFLib.PDFDocument.create();
                const imageBytes = new Uint8Array(arrayBuffer);
                let image;
                if (fileType === 'image/png') {
                    image = await imagePdf.embedPng(imageBytes);
                } else if (fileType === 'image/jpeg' || fileType === 'image/jpg') {
                    image = await imagePdf.embedJpg(imageBytes);
                } else {
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
                // On applique la rotation demandée au canvas, donc il faudrait aussi appliquer ici la rotation au dessin de l'image dans le PDF:
                // Mais PDFLib ne supporte pas rotation d'image directe, il faut faire une transformation manuelle.
                // Pour simplifier on peut ignorer la rotation dans la fusion ou gérer seulement rotation multiple de 90° via transformation.

                // Note: PDFLib ne gère pas directement la rotation d’image, on pourrait sauter ça ou faire une fonction avancée.

                page.drawImage(image, { x, y, width: targetWidth, height: targetHeight });

                const copiedPages = await mergedPdf.copyPages(imagePdf, [0]);
                copiedPages.forEach(page => mergedPdf.addPage(page));
            }
        }

        const pdfBytes = await mergedPdf.save();
        const blob = new Blob([pdfBytes], { type: 'application/pdf' });
        const formData = new FormData();
       const fileNameBase = selectedFilesVente.map(f => f.name.replace(/\.[^/.]+$/, '')).join('_');

// Limiter la longueur si trop long (optionnel)
const safeFileName = fileNameBase.substring(0, 100).replace(/[^\w\-]/g, '_');

formData.append('file', blob, `${safeFileName}_${Date.now()}.pdf`);
        formData.append('societe_id', '{{ session()->get("societeId") }}');
        formData.append('type', 'vente');

        const response = await fetch('/uploadFusionner', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        });

        if (response.ok) {
            const data = await response.json();
            alert("Fichier fusionné et uploadé avec succès !");
            // Actualiser la page ou autre action
            location.reload();
        } else {
            alert("Erreur lors de l'upload.");
        }
    } catch (error) {
        console.error(error);
        alert("Erreur lors de la fusion des fichiers.");
    }
}
function addFileVente() {
    document.getElementById('filesToMergeVenteHidden').click();
}
</script>


<style>
  #fileOrderModalVente {
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

  @keyframes fadeInVente {
    from {
      opacity: 0;
      transform: translate(-50%, -40%);
    }
    to {
      opacity: 1;
      transform: translate(-50%, -50%);
    }
  }

  #fileOrderModalVente h3 {
    margin-top: 0;
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
  }

  #fileOrderModalVente ul {
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

  #fileOrderModalVente ul li {
   background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 9px;
    display: inline-block;
    transition: background-color 0.2s;
  }

  #fileOrderModalVente ul li:hover {
    background-color: #f1f1f1;
  }

  #fileOrderModalVente span[onclick] {
    color: #007BFF;
    font-weight: 500;
    transition: color 0.2s;
  }

  #fileOrderModalVente span[onclick]:hover {
    color: #0056b3;
  }

  #fileOrderModalVente .action-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
  }

  #fileOrderModalVente button {
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

  #fileOrderModalVente button:hover {
    background-color: #0056b3;
  }

  #fileOrderModalVente button:first-of-type {
    background-color: #6c757d;
  }

  #fileOrderModalVente button:first-of-type:hover {
    background-color: #5a6268;
  }

  #fileOrderModalVente > span {
    position: absolute;
    top: 15px;
    right: 20px;
    cursor: pointer;
    font-size: 24px;
    font-weight: bold;
    color: #999;
  }

  #fileOrderModalVente > span:hover {
    color: #333;
  }
</style>

   <!-- Banque -->
<div class="col-md-3 mb-3" id="banque-div">
  <div class="p-2 text-white" style="background-color: #ffc107; border-radius: 15px; font-size: 0.75rem; height: 130px;">
    <div class="d-flex justify-content-between align-items-center">
      <h5 style="color: white;">Banque</h5>
      <form id="form-banque" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" value="Banque">
<input type="file" name="files[]" id="file-banque" style="display: none;" multiple onchange="handleFileSelect(event, 'banque')">
        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
        <input type="hidden" name="folders" value="0">

        <!-- Input file caché pour la fusion -->
        <input type="file" id="banqueMerge_filesToMergeHidden" multiple style="display: none;" onchange="banqueMerge_mergeFilesDirect(event)">

        <!-- Dropdown personnalisé sans flèche -->
        <div class="">
          <button 
            class="btn btn-light btn-sm" 
            type="button" 
            id="dropdownMenuButtonBanque" 
            data-bs-toggle="dropdown" 
            aria-expanded="false"
            style="background-color: #ffc107; border: 1px solid white; border-radius: 10px; color: white; width:100px;">
            Charger
          </button>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonBanque">
            <li><a class="dropdown-item" href="#" onclick="banqueMerge_handleUpload('importer')">Importer</a></li>
            <!-- <li><a class="dropdown-item" href="#" onclick="banqueMerge_handleUpload('scanner')">Scanner</a></li> -->
            <li><a class="dropdown-item" href="#" onclick="banqueMerge_handleUpload('fusionner')">Fusionner</a></li>
          </ul>
        </div>

        <button type="submit" style="display: none;" id="submit-banque">Envoyer</button>
      </form>
    </div>
    <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['Banque'] ?? $fileCounts['banque'] ?? 0 }}</p>
    <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : </p>
    <!-- <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : </p> -->
  </div>
</div>

<!-- 📁 Champ de fichiers caché pour fusion -->
<input type="file" id="banqueMerge_filesToMergeHidden" multiple style="display: none;" onchange="banqueMerge_mergeFilesDirect(event)">

<!-- 📦 PDF.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<!-- PDFLib nécessaire pour merge -->
<script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>

<!-- 🧠 Script principal -->
<script>
// Script principal
  let selectedFilesBanque = [];
    let rotationsBanque = []; // <-- tableau pour stocker rotation (en degrés) par fichier
let sortOrderBanque = 'asc'; // Variable d'état pour l'ordre de tri

function sortFilesBanque() {
    // Inverser l'ordre de tri
    sortOrderBanque = sortOrderBanque === 'asc' ? 'desc' : 'asc';

    // Trier les fichiers en fonction de l'ordre
    selectedFilesBanque.sort((a, b) => {
        return sortOrderBanque === 'asc' 
            ? a.name.localeCompare(b.name) 
            : b.name.localeCompare(a.name);
    });

    // Mettre à jour l'affichage
    populateFileListBanque();

    // Changer le texte et la couleur du bouton pour indiquer l'ordre actuel
    const sortButton = document.getElementById('sortButtonBanque');
    sortButton.innerHTML = sortOrderBanque === 'asc' 
        ? '<span style="font-size: 20px;">&#8593;</span><div style="line-height: 1;"><div style="font-size: 9px;">Z</div><div style="font-size: 9px;">A</div></div>'
        : '<span style="font-size: 20px;">&#8595;</span><div style="line-height: 1;"><div style="font-size: 9px;">A</div><div style="font-size: 9px;">Z</div></div>';

    sortButton.style.backgroundColor = sortOrderBanque === 'asc' ? '#cb0c9f' : '#e74c3c'; // Couleur pour A-Z et Z-A
}
function moveFileBanque(currentIndex, newIndex) {
    currentIndex = parseInt(currentIndex);
    if (newIndex < 0 || newIndex >= selectedFilesBanque.length || currentIndex === newIndex) return;
    const [moved] = selectedFilesBanque.splice(currentIndex, 1);
    const [rotMoved] = rotationsBanque.splice(currentIndex, 1);
    selectedFilesBanque.splice(newIndex, 0, moved);
    rotationsBanque.splice(newIndex, 0, rotMoved);
    populateFileListBanque();
}

function banqueMerge_handleUpload(option) {
    if (option === 'importer') {
        document.getElementById('file-banque').click();
    } else if (option === 'scanner') {
        alert("Fonction de scan non implémentée.");
    } else if (option === 'fusionner') {
        document.getElementById('banqueMerge_filesToMergeHidden').click();
    }
}

// ...dans le script Banque...
async function banqueMerge_mergeFilesDirect(event) {
    const newFiles = Array.from(event.target.files);
    if (!newFiles.length) return;
    selectedFilesBanque.push(...newFiles);
    for(let i = 0; i < newFiles.length; i++) {
        rotationsBanque.push(0); // ou la valeur par défaut de rotation
    }

    if (selectedFilesBanque.length < 2) {
        alert("Sélectionnez au moins deux fichiers pour fusionner.");
        return;
    }

    banqueMerge_showModal();
    populateFileListBanque();
}

// ...dans le script Banque...
function banqueMerge_showModal() {
    document.getElementById('fileOrderModalBanque').style.display = 'block';
}
function closeModalBanque() {
    document.getElementById('fileOrderModalBanque').style.display = 'none';
}



  function drawImageWithRotationBanque(ctx, img, rotationDeg, canvasWidth, canvasHeight) {
    const radians = rotationDeg * Math.PI / 180;
    ctx.clearRect(0, 0, canvasWidth, canvasHeight);
    ctx.save();
    ctx.translate(canvasWidth / 2, canvasHeight / 2);
    ctx.rotate(radians);

    const scale = Math.min(canvasWidth / img.width, canvasHeight / img.height);
    ctx.drawImage(img, -img.width * scale / 2, -img.height * scale / 2, img.width * scale, img.height * scale);
    ctx.restore();
  }

  function rotateImageBanque(canvas, index) {
    rotationsBanque[index] = (rotationsBanque[index] + 90) % 360;
    const ctx = canvas.getContext('2d');
    const file = selectedFilesBanque[index];
    const reader = new FileReader();

    reader.onload = function(e) {
      if (file.type.startsWith('image/')) {
        const img = new Image();
        img.onload = function() {
          drawImageWithRotationBanque(ctx, img, rotationsBanque[index], canvas.width, canvas.height);
        };
        img.src = URL.createObjectURL(file);
      }
    };

    reader.readAsArrayBuffer(file);
  }



    function populateFileListBanque() {
        const fileListBanque = document.getElementById('fileListBanque');
        fileListBanque.innerHTML = '';

        selectedFilesBanque.forEach((file, index) => {
            const listItemBanque = document.createElement('li');
            // listItem.style.display = 'flex';
            listItemBanque.style.flexDirection = 'column';
            // listItem.style.alignItems = 'center';
            listItemBanque.style.marginBottom = '15px';
            listItemBanque.style.padding = '10px';
            listItemBanque.style.marginLeft = '10px';
            listItemBanque.style.backgroundColor = '#ffffff';
            listItemBanque.style.border = '1px solid #ddd';
            listItemBanque.style.borderRadius = '8px';
            listItemBanque.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            listItemBanque.style.transition = 'box-shadow 0.2s';
            listItemBanque.style.width = '23%';
            listItemBanque.draggable = true;

            listItemBanque.addEventListener('mouseover', () => {
                listItemBanque.style.boxShadow = '0 6px 14px rgba(0,0,0,0.1)';
            });
            listItemBanque.addEventListener('mouseout', () => {
                listItemBanque.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            });

            const topRowBanque = document.createElement('div');
            topRowBanque.style.display = 'flex';
            topRowBanque.style.alignItems = 'center';
            topRowBanque.style.justifyContent = 'space-between';
            topRowBanque.style.width = '100%';
            topRowBanque.style.marginBottom = '8px';

            const fileInfoBanque = document.createElement('div');
            fileInfoBanque.style.display = 'flex';
            fileInfoBanque.style.alignItems = 'center';
            fileInfoBanque.style.flexGrow = '1';
            fileInfoBanque.style.gap = '5px';
            fileInfoBanque.innerHTML = `<span style="font-size:11px; color:#333; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${file.name}</span>`;

            const iconRowBanque = document.createElement('div');
            iconRowBanque.style.display = 'flex';
            iconRowBanque.style.gap = '10px';

            const deleteIconBanque = document.createElement('span');
            deleteIconBanque.textContent = '❌';
            deleteIconBanque.title = 'Supprimer';
            deleteIconBanque.style.cursor = 'pointer';
            deleteIconBanque.style.transition = 'color 0.2s';
            deleteIconBanque.onmouseover = () => deleteIconBanque.style.color = '#e74c3c';
            deleteIconBanque.onmouseout = () => deleteIconBanque.style.color = 'inherit';
            deleteIconBanque.onclick = () => {
                selectedFilesBanque.splice(index, 1);
                rotationsBanque.splice(index, 1); // Supprimer aussi la rotation correspondante
                populateFileListBanque();
            };

            const rotateIconBanque = document.createElement('span');
            rotateIconBanque.textContent = '🔄';
            rotateIconBanque.title = 'Rotation';
            rotateIconBanque.style.cursor = 'pointer';
            rotateIconBanque.style.transition = 'color 0.2s';
            rotateIconBanque.onmouseover = () => rotateIconBanque.style.color = '#3498db';
            rotateIconBanque.onmouseout = () => rotateIconBanque.style.color = 'inherit';

            iconRowBanque.appendChild(deleteIconBanque);
            iconRowBanque.appendChild(rotateIconBanque);

            topRowBanque.appendChild(fileInfoBanque);
            topRowBanque.appendChild(iconRowBanque);

            const previewBanque = document.createElement('canvas');
            previewBanque.width = 200;
            previewBanque.height = 260;
            previewBanque.style.border = '1px solid #ccc';
            previewBanque.style.borderRadius = '4px';
            previewBanque.style.marginBottom = '5px';

            const ctx = previewBanque.getContext('2d');
            ctx.fillStyle = "#f0f0f0";
            ctx.fillRect(0, 0, previewBanque.width, previewBanque.height);

rotateIconBanque.onclick = () => rotateImageBanque(previewBanque, index);

            const legendBanque = document.createElement('div');
            legendBanque.style.fontSize = '10px';
            legendBanque.style.color = '#777';
            legendBanque.style.marginTop = '4px';

            const reader = new FileReader();
            reader.onload = async function (e) {
                const arrayBuffer = e.target.result;
                const fileType = file.type;

                if (fileType === 'application/pdf') {
                    const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
                    loadingTask.promise.then((pdfDoc) => {
                        pdfDoc.getPage(1).then((page) => {
                            const viewport = page.getViewport({ scale: 1.0 });
                            const scale = Math.min(previewBanque.width / viewport.width, previewBanque.height / viewport.height);
                            const scaledViewport = page.getViewport({ scale });
                            ctx.clearRect(0, 0, previewBanque.width, previewBanque.height);
                            page.render({ canvasContext: ctx, viewport: scaledViewport });
                        });
                    }).catch(console.error);
                } else if (fileType.startsWith('image/')) {
                    const img = new Image();
                    img.onload = function () {
drawImageWithRotationBanque(ctx, img, rotationsBanque[index], previewBanque.width, previewBanque.height);                    };
                    img.src = URL.createObjectURL(file);
                }
            };
            reader.readAsArrayBuffer(file);

            listItemBanque.appendChild(topRowBanque);
            listItemBanque.appendChild(previewBanque);
            listItemBanque.appendChild(legendBanque);

            listItemBanque.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', index);
            });
            listItemBanque.addEventListener('dragover', (e) => e.preventDefault());
            listItemBanque.addEventListener('drop', (e) => {
                e.preventDefault();
                const fromIndex = e.dataTransfer.getData('text/plain');
                moveFileBanque(fromIndex, index);
            });

            fileListBanque.appendChild(listItemBanque);
        });
    }

    function drawImageWithRotationBanque(ctx, img, degrees, canvasWidth, canvasHeight) {
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

   function rotateImageBanque(preview, index) {
    // Incrémente la rotation de 90° (modulo 360)
    rotationsBanque[index] = (rotationsBanque[index] + 90) % 360;

    // Redessine l'image avec la nouvelle rotation
    const ctx = preview.getContext('2d');
    const file = selectedFilesBanque[index]; // Récupérer le fichier original
    const reader = new FileReader();

    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            drawImageWithRotationBanque(ctx, img, rotationsBanque[index], previewBanque.width, previewBanque.height);
        };
        img.src = e.target.result; // Utiliser le résultat du FileReader
    };
    reader.readAsDataURL(file); // Lire le fichier comme URL de données
}


function banqueMerge_drawImageWithRotation(ctx, img, degrees, canvasWidth, canvasHeight) {
    ctx.clearRect(0, 0, canvasWidth, canvasHeight);
    ctx.save();
    ctx.translate(canvasWidth / 2, canvasHeight / 2);
    ctx.rotate(degrees * Math.PI / 180);

    let scale = Math.min(canvasWidth / img.width, canvasHeight / img.height);
    let width = img.width * scale;
    let height = img.height * scale;

    if (degrees % 180 !== 0) {
        [width, height] = [height, width];
    }

    ctx.drawImage(img, -width / 2, -height / 2, width, height);
    ctx.restore();
}

function banqueMerge_rotateImage(preview, index) {
    rotationsBanque[index] = (rotationsBanque[index] + 90) % 360;
    populateFileListBanque();
}

function banqueMerge_moveFile(currentIndex, newIndex) {
    currentIndex = parseInt(currentIndex);
    if (newIndex < 0 || newIndex >=selectedFilesBanque.length) return;

    const file =selectedFilesBanque.splice(currentIndex, 1)[0];
    const rotation = rotationsBanque.splice(currentIndex, 1)[0];

   selectedFilesBanque.splice(newIndex, 0, file);
    rotationsBanque.splice(newIndex, 0, rotation);

    populateFileListBanque();
}

// async function banqueMerge_confirmOrder() {
//     banqueMerge_closeModal();
//     await banqueMerge_mergeFiles();
// }
function banqueMerge_triggerFilePicker() {
    document.getElementById('banqueMerge_filesToMergeHidden').click();
}
  async function confirmOrderBanque() {
        closeModal();
        await banqueMerge_mergeFiles(confirmOrderBanque);
    } 
async function banqueMerge_mergeFiles(files) {
    
    if (selectedFilesBanque.length < 2) {
        alert("Sélectionnez au moins deux fichiers pour fusionner.");
        return;
    }

    const mergedPdf = await PDFLib.PDFDocument.create();

    // On suppose que rotationsBanque est défini et correspond aux fichiers
    for (let i = 0; i <selectedFilesBanque.length; i++) {
        const file =selectedFilesBanque[i];
        const rotation = rotationsBanque[i] || 0;
        const arrayBuffer = await file.arrayBuffer();
        const fileType = file.type;

        if (fileType === 'application/pdf') {
            const pdf = await PDFLib.PDFDocument.load(arrayBuffer);
            const copiedPages = await mergedPdf.copyPages(pdf, pdf.getPageIndices());
            copiedPages.forEach((page) => {
                if (rotation !== 0) {
                    page.setRotation(PDFLib.degrees(rotation));
                }
                mergedPdf.addPage(page);
            });
        } else if (fileType.startsWith('image/')) {
            // Dimensions page portrait A4 en points
            const pageWidthPortrait = 595.28;
            const pageHeightPortrait = 841.89;
            let pageWidth, pageHeight;

            // Inverser dimensions si rotation 90 ou 270 (paysage)
            if (rotation === 90 || rotation === 270) {
                pageWidth = pageHeightPortrait;
                pageHeight = pageWidthPortrait;
            } else {
                pageWidth = pageWidthPortrait;
                pageHeight = pageHeightPortrait;
            }

            const imageBytes = new Uint8Array(arrayBuffer);
            let image;

            if (fileType === 'image/png') {
                image = await mergedPdf.embedPng(imageBytes);
            } else if (fileType === 'image/jpeg' || fileType === 'image/jpg') {
                image = await mergedPdf.embedJpg(imageBytes);
            } else {
                console.warn(`Type d'image non supporté: ${fileType}`);
                continue;
            }

            const rotated = (rotation % 180) !== 0;

            // Calcul taille cible en conservant ratio et en s'adaptant à la rotation
            const targetWidth = rotated ? pageHeight * 0.9 : pageWidth * 0.9;
            const scale = targetWidth / (rotated ? image.height : image.width);
            const targetHeight = (rotated ? image.width : image.height) * scale;

            // Calcul centre de la page
            const centerX = pageWidth / 2;
            const centerY = pageHeight / 2;

            // Calcul position pour dessiner l'image centrée autour de 0,0 (après translation)
            const imgX = - (rotated ? targetHeight : targetWidth) / 2;
            const imgY = - (rotated ? targetWidth : targetHeight) / 2;

            // Créer la page avec la bonne taille
            const page = mergedPdf.addPage([pageWidth, pageHeight]);

            // Appliquer transformation centrée : translation puis rotation
            page.pushOperators(
                PDFLib.pushGraphicsState(),
                PDFLib.translate(centerX, centerY),
                PDFLib.rotateDegrees(rotation),
            );

            // Dessiner l'image centrée
            page.drawImage(image, {
                x: imgX,
                y: imgY,
                width: rotated ? targetHeight : targetWidth,
                height: rotated ? targetWidth : targetHeight,
            });

            // Restaurer contexte graphique
            page.pushOperators(
                PDFLib.popGraphicsState()
            );
        }
    }

    // Sauvegarde et envoi du PDF fusionné
    const mergedPdfBytes = await mergedPdf.save();
    const blob = new Blob([mergedPdfBytes], { type: 'application/pdf' });

    const formData = new FormData();
const fileNameBase =selectedFilesBanque.map(f => f.name.replace(/\.[^/.]+$/, '')).join('_');

// Limiter la longueur si trop long (optionnel)
const safeFileName = fileNameBase.substring(0, 100).replace(/[^\w\-]/g, '_');

formData.append('file', blob, `${safeFileName}_${Date.now()}.pdf`);    formData.append('societe_id', '{{ session()->get('societeId') }}'); // Laravel dynamique
    formData.append('type', 'banque');

    try {
        const response = await fetch('/uploadFusionner', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();

        if (data.success) {
            const messageDiv = document.createElement('div');
            messageDiv.textContent = "Fichiers fusionnés avec succès !";
            Object.assign(messageDiv.style, {
                position: 'fixed',
                top: '150px',
                left: '50%',
                transform: 'translateX(-50%)',
                backgroundColor: '#4CAF50',
                color: 'white',
                padding: '10px 20px',
                borderRadius: '5px',
                zIndex: 9999
            });
            document.body.appendChild(messageDiv);
            setTimeout(() => location.reload(), 2000);
        } else {
            alert("Erreur lors de l'envoi.");
        }
    } catch (error) {
        console.error(error);
        alert("Erreur lors de la requête réseau.");
    }
}
function addFileBanque() {
    document.getElementById('banqueMerge_filesToMergeHidden').click();
}
</script>




        <!-- <ul id="fileList" style="list-style-type:none; padding:0; margin:0; max-height: 550px; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <span onclick="addFile()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
<span id="sortButton" onclick="sortFiles()" 
      style="display: flex; align-items: center; justify-content: center; 
             cursor: pointer; width: 60px; height: 60px; 
             background-color: #cb0c9f; color: white; border-radius: 50%; 
             font-size: 16px; user-select: none; padding: 5px;">
    <div style="display: flex; align-items: center; gap: 5px;">
        <span style="font-size: 20px;">&#8595;</span>
        <div style="line-height: 1;">
            <div style="font-size: 9px;">A</div>
            <div style="font-size: 9px;">Z</div>
        </div>
    </div>
</span>
        </div>
    </div> -->


<!-- 💬 Modal HTML -->
<div id="fileOrderModalBanque" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModalBanque()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;height:120%;">

        <ul id="fileListBanque" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        <!-- Colonne verticale à droite -->
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; gap: 18px; height: 100%; margin-left: 10px;">
            <span onclick="addFileBanque()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
            <span id="sortButtonBanque" onclick="sortFilesBanque()" 
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
            <button onclick="confirmOrderBanque()" style="background-color: #007BFF; color: white; padding: 0 6px; font-size: 11px; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.2s; width: auto; min-width: 0; margin-top: 0; display: flex; align-items: center; justify-content: center; height: 16px; min-height: 0; max-height: 35px; line-height: 35px; box-sizing: border-box;">Valider</button>
        </div>
    </div>

</div>

<!-- 💬 Modal HTML -->
<!-- <div id="fileOrderModalBanque" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModalBanque()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;height:120%;">
        <ul id="fileListBanque" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <span onclick="addFileBanque()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
<span id="sortButtonBanque" onclick="sortFilesBanque()" 
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
        </div>
    </div>

  <div style="margin-top: -72%;margin-left: 93%; width: fit-content;">
     <button onclick="confirmOrderBanque()" style="background-color: #007BFF; color: white; padding: 10px 16px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s;">Valider</button>
</div>

</div> -->
<!-- Input caché pour ajouter des fichiers -->
 
<style>
 #fileOrderModalBanque {
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
  
  #fileOrderModalBanque ul {
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

  #fileOrderModalBanque ul li {
    /* padding: 20px; */
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 9px;
    display: inline-block;
    transition: background-color 0.2s;
  }

    .banqueMerge-add-button {
    background: #e0e0e0;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 24px;
    color: #555;
    cursor: pointer;
    transition: background-color 0.2s ease;
    }

    .banqueMerge-add-button:hover {
    background-color: #d5d5d5;
    }



    

</style>




</div>


    <!-- Deuxième ligne avec 2 divs -->
    <div class="row">
   <!-- Caisse -->
<div class="col-md-3 mb-3" id="caisse-div">
    <div class="p-2 text-white" style="background-color: #dc3545; border-radius: 15px; font-size: 0.75rem; height: 130px;">
        <div class="d-flex justify-content-between align-items-center">
            <!-- <h5 style="color: white;">Caisse</h5> -->
            <h5 style="color: white;font-size:16px;">Etat De Caisse </br> Mensuel</h5>

            <form id="form-caisse" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="caisse">
                <input type="file" name="file" id="file-caisse" style="display: none;" onchange="handleFileSelect(event, 'caisse')">
                <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">

                <!-- Dropdown personnalisé sans flèche -->
                <div class="dropdown">
                   <a 
    href="{{ route('etat_de_caisse') }}" 
    class="btn btn-light btn-sm" 
    style="background-color: #dc3545; border: 1px solid white; border-radius: 10px; color: white; width:100px;">
    Charger
</a>

                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonCaisse">
                        <li><a class="dropdown-item" href="#" onclick="handleUploadCaisse('importer')">Importer</a></li>
                        <!-- <li><a class="dropdown-item" href="#" onclick="handleUploadCaisse('scanner')">Scanner</a></li> -->
                        <li><a class="dropdown-item" href="#" onclick="handleUploadCaisse('fusionner')">Fusionner</a></li>
                    </ul>
                </div>

                <button type="submit" style="display: none;" id="submit-caisse">Envoyer</button>
            </form>
        </div>
</br>
<p style="font-size: 0.7rem; line-height: 0.3;">
    Nombre de mois clôturé : {{ $closedCount }}
</p>
        <p style="font-size: 0.7rem; line-height: 0.3;">Nombre de mois traité : </p>
    </div>
</div>

<!-- Input caché pour fusion Caisse -->
<!-- <input type="file" id="filesToMergeCaisseHidden" multiple style="display: none;" onchange="mergeFilesCaisseDirect(event)">
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
 -->



      <!-- Impôt -->
<div class="col-md-3 mb-3" id="impot-div">
    <div class="p-2 text-white" style="background-color: #6f42c1; border-radius: 15px; font-size: 0.75rem; height: 130px;">
        <div class="d-flex justify-content-between align-items-center">
            <h5 style="color: white;">Impôt</h5>
            
            <!-- Formulaire upload (uniquement pour "Importer") -->
            <form id="form-impot" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="impot">
<input type="file" name="files[]" id="inputTaxFile" style="display: none;" multiple onchange="document.getElementById('submit-impot').click()">
                <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">

                <!-- Dropdown personnalisé -->
                <div class="">
                    <button 
                        class="btn btn-light btn-sm" 
                        type="button" 
                        id="dropdownMenuButtonImpot" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false"
                        style="background-color: #6f42c1; border: 1px solid white; border-radius: 10px; color: white; width:100px;">
                        Charger
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonImpot">
                        <li><a class="dropdown-item" href="#" onclick="uploadTaxFiles('import')">Importer</a></li>
                        <!-- <li><a class="dropdown-item" href="#" onclick="uploadTaxFiles('scan')">Scanner</a></li> -->
                        <li><a class="dropdown-item" href="#" onclick="uploadTaxFiles('merge')">Fusionner</a></li>
                    </ul>
                </div>

                <button type="submit" style="display: none;" id="submit-impot">Envoyer</button>
            </form>

            <!-- Input pour fusion caché -->
            <input type="file" id="hiddenFilesToMergeTax" multiple style="display: none;" onchange="handleMergeTaxFiles(event)">
        </div>

        <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['impot'] ?? $fileCounts['Impot'] ?? 0 }}</p>
        <p style="font-size: 0.7rem; line-height: 0.3;">pièces générées : </p>
        <p style="font-size: 0.7rem; line-height: 0.3;">Pièces validées : </p>
    </div>
</div>

<!-- Input caché pour fusion Vente -->
<input type="file" id="hiddenFilesToMergeTax" multiple style="display: none;" onchange="handleMergeTaxFiles(event)">

<script>
     let selectedFilesImpot = [];
    let rotationsImpot = []; // <-- tableau pour stocker rotation (en degrés) par fichier
let sortOrderImpot = 'asc'; // Variable d'état pour l'ordre de tri

function sortFilesImpot() {
    // Inverser l'ordre de tri
    sortOrderImpot = sortOrderImpot === 'asc' ? 'desc' : 'asc';

    // Trier les fichiers en fonction de l'ordre
    selectedFilesImpot.sort((a, b) => {
        return sortOrderImpot === 'asc' 
            ? a.name.localeCompare(b.name) 
            : b.name.localeCompare(a.name);
    });

    // Mettre à jour l'affichage
    populateFileListImpot();

    // Changer le texte et la couleur du bouton pour indiquer l'ordre actuel
    const sortButtonImpot = document.getElementById('sortButtonImpot');
    sortButtonImpot.innerHTML = sortOrderImpot === 'asc' 
        ? '<span style="font-size: 20px;">&#8593;</span><div style="line-height: 1;"><div style="font-size: 9px;">Z</div><div style="font-size: 9px;">A</div></div>'
        : '<span style="font-size: 20px;">&#8595;</span><div style="line-height: 1;"><div style="font-size: 9px;">A</div><div style="font-size: 9px;">Z</div></div>';

    sortButtonImpot.style.backgroundColor = sortOrderImpot === 'asc' ? '#cb0c9f' : '#e74c3c'; // Couleur pour A-Z et Z-A
}

 function uploadTaxFiles(option) {
     if (option === 'import') {
         document.getElementById('inputTaxFile').click();
     } else if (option === 'scan') {
         alert("Fonction de scan non implémentée.");
     } else if (option === 'merge') {
         document.getElementById('hiddenFilesToMergeTax').click();
     }
 }

   async function handleMergeTaxFiles(event) {
       const newSelectedFiles = Array.from(event.target.files);
       if (!newSelectedFiles.length) return;
       selectedFilesImpot.push(...newSelectedFiles);

       // Initialiser la rotation à 0 pour les nouveaux fichiers
       for(let i = 0; i < newSelectedFiles.length; i++) {
           rotationsImpot.push(0);
       }

       if (selectedFilesImpot.length < 2) {
           alert("Veuillez sélectionner au moins deux fichiers.");
           return;
       }

       openFileOrderModal();
        populateFileListImpot();
   }
   

 function openFileOrderModal() {
     document.getElementById('fileOrderModalImpot').style.display = 'block';
 }

 function hideFileOrderModal() {
     document.getElementById('fileOrderModalImpot').style.display = 'none';
 }

   
  function drawImageWithRotationImpot(ctx, img, rotationDeg, canvasWidth, canvasHeight) {
    const radians = rotationDeg * Math.PI / 180;
    ctx.clearRect(0, 0, canvasWidth, canvasHeight);
    ctx.save();
    ctx.translate(canvasWidth / 2, canvasHeight / 2);
    ctx.rotate(radians);

    const scale = Math.min(canvasWidth / img.width, canvasHeight / img.height);
    ctx.drawImage(img, -img.width * scale / 2, -img.height * scale / 2, img.width * scale, img.height * scale);
    ctx.restore();
  }

  function rotateImageImpot(canvas, index) {
    rotationsImpot[index] = (rotationsImpot[index] + 90) % 360;
    const ctx = canvas.getContext('2d');
    const file = selectedFilesImpot[index];
    const reader = new FileReader();

    reader.onload = function(e) {
      if (file.type.startsWith('image/')) {
        const img = new Image();
        img.onload = function() {
          drawImageWithRotationImpot(ctx, img, rotationsImpot[index], canvas.width, canvas.height);
        };
        img.src = URL.createObjectURL(file);
      }
    };

    reader.readAsArrayBuffer(file);
  }

  function populateFileListImpot() {
        const fileListImpot = document.getElementById('fileListImpot');
        fileListImpot.innerHTML = '';

        selectedFilesImpot.forEach((file, index) => {
            const listItemImpot = document.createElement('li');
            // listItem.style.display = 'flex';
            listItemImpot.style.flexDirection = 'column';
            // listItem.style.alignItems = 'center';
            listItemImpot.style.marginBottom = '15px';
            listItemImpot.style.padding = '10px';
            listItemImpot.style.marginLeft = '10px';
            listItemImpot.style.backgroundColor = '#ffffff';
            listItemImpot.style.border = '1px solid #ddd';
            listItemImpot.style.borderRadius = '8px';
            listItemImpot.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            listItemImpot.style.transition = 'box-shadow 0.2s';
            listItemImpot.style.width = '23%';
            listItemImpot.draggable = true;

            listItemImpot.addEventListener('mouseover', () => {
                listItemImpot.style.boxShadow = '0 6px 14px rgba(0,0,0,0.1)';
            });
            listItemImpot.addEventListener('mouseout', () => {
                listItemImpot.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            });

            const topRowImpot = document.createElement('div');
            topRowImpot.style.display = 'flex';
            topRowImpot.style.alignItems = 'center';
            topRowImpot.style.justifyContent = 'space-between';
            topRowImpot.style.width = '100%';
            topRowImpot.style.marginBottom = '8px';

            const fileInfoImpot = document.createElement('div');
            fileInfoImpot.style.display = 'flex';
            fileInfoImpot.style.alignItems = 'center';
            fileInfoImpot.style.flexGrow = '1';
            fileInfoImpot.style.gap = '5px';
            fileInfoImpot.innerHTML = `<span style="font-size:11px; color:#333; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${file.name}</span>`;

            const iconRowImpot = document.createElement('div');
            iconRowImpot.style.display = 'flex';
            iconRowImpot.style.gap = '10px';

            const deleteIconImpot = document.createElement('span');
            deleteIconImpot.textContent = '❌';
            deleteIconImpot.title = 'Supprimer';
            deleteIconImpot.style.cursor = 'pointer';
            deleteIconImpot.style.transition = 'color 0.2s';
            deleteIconImpot.onmouseover = () => deleteIconImpot.style.color = '#e74c3c';
            deleteIconImpot.onmouseout = () => deleteIconImpot.style.color = 'inherit';
            deleteIconImpot.onclick = () => {
                selectedFilesImpot.splice(index, 1);
                rotationsImpot.splice(index, 1); // Supprimer aussi la rotation correspondante
                populateFileListImpot();
            };

            const rotateIconImpot = document.createElement('span');
            rotateIconImpot.textContent = '🔄';
            rotateIconImpot.title = 'Rotation';
            rotateIconImpot.style.cursor = 'pointer';
            rotateIconImpot.style.transition = 'color 0.2s';
            rotateIconImpot.onmouseover = () => rotateIconImpot.style.color = '#3498db';
            rotateIconImpot.onmouseout = () => rotateIconImpot.style.color = 'inherit';

            iconRowImpot.appendChild(deleteIconImpot);
            iconRowImpot.appendChild(rotateIconImpot);

            topRowImpot.appendChild(fileInfoImpot);
            topRowImpot.appendChild(iconRowImpot);

            const previewImpot = document.createElement('canvas');
            previewImpot.width = 200;
            previewImpot.height = 260;
            previewImpot.style.border = '1px solid #ccc';
            previewImpot.style.borderRadius = '4px';
            previewImpot.style.marginBottom = '5px';

            const ctx = previewImpot.getContext('2d');
            ctx.fillStyle = "#f0f0f0";
            ctx.fillRect(0, 0, previewImpot.width, previewImpot.height);

            rotateIconImpot.onclick = () => rotateImage(previewImpot, index);

            const legendImpot = document.createElement('div');
            legendImpot.style.fontSize = '10px';
            legendImpot.style.color = '#777';
            legendImpot.style.marginTop = '4px';

            const reader = new FileReader();
            reader.onload = async function (e) {
                const arrayBuffer = e.target.result;
                const fileType = file.type;

                if (fileType === 'application/pdf') {
                    const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
                    loadingTask.promise.then((pdfDoc) => {
                        pdfDoc.getPage(1).then((page) => {
                            const viewport = page.getViewport({ scale: 1.0 });
                            const scale = Math.min(previewImpot.width / viewport.width, previewImpot.height / viewport.height);
                            const scaledViewport = page.getViewport({ scale });
                            ctx.clearRect(0, 0, previewImpot.width, previewImpot.height);
                            page.render({ canvasContext: ctx, viewport: scaledViewport });
                        });
                    }).catch(console.error);
                } else if (fileType.startsWith('image/')) {
                    const img = new Image();
                    img.onload = function () {
                        drawImageWithRotationImpot(ctx, img, rotationsImpot[index], previewImpot.width, previewImpot.height);
                    };
                    img.src = URL.createObjectURL(file);
                }
            };
            reader.readAsArrayBuffer(file);

            listItemImpot.appendChild(topRowImpot);
            listItemImpot.appendChild(previewImpot);
            listItemImpot.appendChild(legendImpot);

            listItemImpot.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', index);
            });
            listItemImpot.addEventListener('dragover', (e) => e.preventDefault());
            listItemImpot.addEventListener('drop', (e) => {
                e.preventDefault();
                const fromIndex = e.dataTransfer.getData('text/plain');
                moveFile(fromIndex, index);
            });

            fileListImpot.appendChild(listItemImpot);
        });
    }

    function drawImageWithRotationImpot(ctx, img, degrees, canvasWidth, canvasHeight) {
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

   function rotateImageImpot(previewImpot, index) {
    // Incrémente la rotation de 90° (modulo 360)
    rotations[index] = (rotations[index] + 90) % 360;

    // Redessine l'image avec la nouvelle rotation
    const ctx = previewImpot.getContext('2d');
    const file = selectedFilesImpot[index]; // Récupérer le fichier original
    const reader = new FileReader();

    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
    drawImageWithRotationImpot(ctx, img, rotationsImpot[index], previewImpot.width, previewImpot.height);
        };
        img.src = e.target.result; // Utiliser le résultat du FileReader
    };
    reader.readAsDataURL(file); // Lire le fichier comme URL de données
}



    function reorderFiles(currentIdx, targetIdx) {
        currentIdx = parseInt(currentIdx);
        if (targetIdx < 0 || targetIdx >= selectedFilesImpot.length || currentIdx === targetIdx) return;
        const [movedFile] = selectedFilesImpot.splice(currentIdx, 1);
        const [movedRotation] = rotationsImpot.splice(currentIdx, 1);
        selectedFilesImpot.splice(targetIdx, 0, movedFile);
        rotationsImpot.splice(targetIdx, 0, movedRotation);
        displayFileList();
    }

 async function validateFileOrder() {
     hideFileOrderModal();
     await mergeTaxFiles();
 }

async function mergeTaxFiles() {
    try {
        const mergedPdfDoc = await PDFLib.PDFDocument.create();

        for (const file of selectedFilesImpot) {
            const buffer = await file.arrayBuffer();
            const fileType = file.type;

            if (fileType === 'application/pdf') {
                const pdfDoc = await PDFLib.PDFDocument.load(buffer);
                const copiedPages = await mergedPdfDoc.copyPages(pdfDoc, pdfDoc.getPageIndices());
                copiedPages.forEach(page => mergedPdfDoc.addPage(page));
            } else if (fileType.startsWith('image/')) {
                const tempPdf = await PDFLib.PDFDocument.create();
                const bytesImage = new Uint8Array(buffer);
                let embeddedImage;

                if (fileType === 'image/png') {
                    embeddedImage = await tempPdf.embedPng(bytesImage);
                } else if (fileType === 'image/jpeg' || fileType === 'image/jpg') {
                    embeddedImage = await tempPdf.embedJpg(bytesImage);
                } else {
                    console.warn(`Type d'image non supporté: ${fileType}`);
                    continue;
                }

                const pageWidth = 595.28;
                const pageHeight = 841.89;
                const widthTarget = pageWidth * 0.9;
                const scaleRatio = widthTarget / embeddedImage.width;
                const heightTarget = embeddedImage.height * scaleRatio;
                const posX = (pageWidth - widthTarget) / 2;
                const posY = (pageHeight - heightTarget) / 2;

                const page = tempPdf.addPage([pageWidth, pageHeight]);
                page.drawImage(embeddedImage, {
                    x: posX,
                    y: posY,
                    width: widthTarget,
                    height: heightTarget,
                });

                const copiedPages = await mergedPdfDoc.copyPages(tempPdf, [0]);
                copiedPages.forEach(page => mergedPdfDoc.addPage(page));
            }
        }

        const mergedPdfBytes = await mergedPdfDoc.save();
        const blobPdf = new Blob([mergedPdfBytes], { type: 'application/pdf' });

        const formData = new FormData();

   const fileNameBase = selectedFilesImpot.map(f => f.name.replace(/\.[^/.]+$/, '')).join('_');
        const safeFileName = fileNameBase.substring(0, 100).replace(/[^\w\-]/g, '_'); // Limiter la longueur et nettoyer le nom
        formData.append('file', blobPdf, `${safeFileName}_merged.pdf`); // Utiliser le nom concaténé
             formData.append('societe_id', '{{ session()->get('societeId') }}');
        formData.append('type', 'impot');

        const response = await fetch('/uploadFusionner', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const result = await response.json();

        if (result.success) {
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

            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            alert("Erreur lors de l'envoi du fichier fusionné.");
        }

    } catch (error) {
        console.error('Erreur pendant la fusion :', error);
        alert("Une erreur est survenue lors de la fusion.");
    }
}

function addMoreFiles() {
    document.getElementById('hiddenFilesToMergeTax').click();
}

  function closeModalImpot() {
        document.getElementById('fileOrderModalImpot').style.display = 'none';
    }

  function addFileImpot() {
    document.getElementById('hiddenFilesToMergeTax').click();
}
async function confirmOrderImpot() {
    closeModalImpot();
    await mergeTaxFiles();
}
</script>



<!-- 💬 Modal HTML -->
<div id="fileOrderModalImpot" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModalImpot()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;height:120%;">

        <ul id="fileListImpot" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        <!-- Colonne verticale à droite -->
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; gap: 18px; height: 100%; margin-left: 10px;">
            <span onclick="addFileImpot()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
            <span id="sortButtonImpot" onclick="sortFilesImpot()" 
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
            <button onclick="confirmOrderImpot()" style="background-color: #007BFF; color: white; padding: 0 6px; font-size: 11px; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.2s; width: auto; min-width: 0; margin-top: 0; display: flex; align-items: center; justify-content: center; height: 16px; min-height: 0; max-height: 35px; line-height: 35px; box-sizing: border-box;">Valider</button>
        </div>
    </div>

</div>


<!-- 💬 Modal HTML -->
<!-- <div id="fileOrderModalImpot" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModalImpot()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;height:120%;">
        <ul id="fileListImpot" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <span onclick="addFileImpot()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
<span id="sortButtonImpot" onclick="sortFilesImpot()" 
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
        </div>
    </div>

  <div style="margin-top: -72%;margin-left: 93%; width: fit-content;">
     <button onclick="confirmOrderImpot()" style="background-color: #007BFF; color: white; padding: 10px 16px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s;">Valider</button>
</div>

</div> -->


<style>
  #fileOrderModalImpot {
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

  #fileOrderModalImpot h3 {
    margin-top: 0;
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
  }

  #fileOrderModalImpot ul {
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

  #fileOrderModalImpot ul li {
    /* padding: 20px; */
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 9px;
    display: inline-block;
    transition: background-color 0.2s;
  }

  #fileOrderModalImpot ul li:hover {
    background-color: #f1f1f1;
  }

  #fileOrderModalImpot span[onclick] {
    color: #007BFF;
    font-weight: 500;
    transition: color 0.2s;
  }

  #fileOrderModalImpot span[onclick]:hover {
    color: #0056b3;
  }

  #fileOrderModalImpot .action-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
  }

  #fileOrderModalImpot button {
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

  #fileOrderModalImpot button:hover {
    background-color: #0056b3;
  }

  #fileOrderModalImpot button:first-of-type {
    background-color: #6c757d;
  }

  #fileOrderModalImpot button:first-of-type:hover {
    background-color: #5a6268;
  }

  #fileOrderModalImpot > span {
    position: absolute;
    top: 15px;
    right: 20px;
    cursor: pointer;
    font-size: 24px;
    font-weight: bold;
    color: #999;
  }

  #fileOrderModalImpot > span:hover {
    color: #333;
  }

</style>



<!-- 💬 Modal HTML -->
<!-- <div id="fileOrderModal" style="display:none; position:fixed; top:30%; left:40%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:50%;height:80%;">
    <span onclick="closeModal()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;">
        <ul id="fileList" style="list-style-type:none; padding:0; margin:0; max-height: 550px; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <span onclick="addFile()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
<span id="sortButton" onclick="sortFiles()" 
      style="display: flex; align-items: center; justify-content: center; 
             cursor: pointer; width: 60px; height: 60px; 
             background-color: #cb0c9f; color: white; border-radius: 50%; 
             font-size: 16px; user-select: none; padding: 5px;">
    <div style="display: flex; align-items: center; gap: 5px;">
        <span style="font-size: 20px;">&#8595;</span>
        <div style="line-height: 1;">
            <div style="font-size: 9px;">A</div>
            <div style="font-size: 9px;">Z</div>
        </div>
    </div>
</span>
        </div>
    </div>

  <div style="display: flex; justify-content: center; gap: 15px; margin-top: 20px; width: fit-content; margin-left: auto; margin-right: auto;">
    <button onclick="closeModal()" style="background-color: #6c757d; color: white; padding: 10px 16px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s;">Annuler</button>
    <button onclick="confirmOrder()" style="background-color: #007BFF; color: white; padding: 10px 16px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s;">Valider</button>
</div>

</div> -->



<!-- Modal pour ordre des fichiers -->
<!-- <div id="modalFileOrder" style="display:none; position:fixed; top:30%; left:40%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:50%;height:80%;">
       <span onclick="closeModal()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>

            <h3 style="margin:0; font-weight:700; font-size:20px; color:#333;">Fusionner</h3>
  
    <div style="display: flex; align-items: flex-start; gap: 20px;">
            <ul id="ulFileList" style="list-style-type:none; padding:0; margin:0; max-height: 550px; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
          <div style="display: flex; flex-direction: column; gap: 10px;">
 <button id="btnAddFile" onclick="document.getElementById('hiddenFilesToMergeTax').click();" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;"
        style="background:#28a745; border:none; border-radius:6px; color:#fff; padding:7px 10px; font-size:14px; cursor:pointer; display:flex; align-items:center; gap:6px;">
        <span style="font-size: 18px;">➕</span> 
    </button>
        <button id="btnSortFiles" onclick="toggleSortFiles()" style="background:#cb0c9f; border:none; border-radius:6px; color:#fff; padding:7px 10px; font-size:14px; cursor:pointer; display:flex; align-items:center; gap:6px; margin-bottom:10px;">
    
        <div style="display: flex; align-items: center; gap: 5px;">
    <span style="font-size: 20px;">&#8595;</span>
    <div style="line-height: 1;">
        <div style="font-size: 9px;">A</div>
        <div style="font-size: 9px;">Z</div>
    </div>
</div>
</button>
</div>
</div>
  <div style="display: flex; justify-content: center; gap: 15px; margin-top: 20px; width: fit-content; margin-left: auto; margin-right: auto;">
    <button onclick="closeModal()" style="background-color: #6c757d; color: white; padding: 10px 16px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s;">Annuler</button>

        <button onclick="validateFileOrder()" style="background-color: #007BFF; color: white; padding: 10px 16px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s;">
            Valider
        </button>
        
    </div>
</div> -->

 

<style>
      #modalFileOrder {
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
</style>



<!-- Paie -->
<div class="col-md-3 mb-3" id="paie-div">
    <div class="p-2 text-white" style="background-color: #17a2b8; border-radius: 15px; font-size: 0.75rem; height: 130px;">
        <div class="d-flex justify-content-between align-items-center">
            <h5 style="color: white;">Paie</h5>

            <form id="form-paie" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="paie">
<input type="file" name="files[]" id="file-paie" style="display: none;" multiple onchange="paie_handleUploadOption('importer')">
                <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">

                <!-- Dropdown Bootstrap -->
                <div class="">
                    <button 
                        class="btn btn-light btn-sm" 
                        type="button" 
                        id="dropdownMenuButtonPaie" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false"
                        style="background-color: #17a2b8; border: 1px solid white; border-radius: 10px; color: white; width: 100px;">
                        Charger
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonPaie">
                        <li><a class="dropdown-item" href="#" onclick="paie_handleUploadOption('importer'); return false;">Importer</a></li>
                        <!-- <li><a class="dropdown-item" href="#" onclick="paie_handleUploadOption('scanner'); return false;">Scanner</a></li> -->
                        <li><a class="dropdown-item" href="#" onclick="paie_handleUploadOption('fusionner'); return false;">Fusionner</a></li>
                    </ul>
                </div>

                <button type="submit" style="display: none;" id="submit-paie">Envoyer</button>
            </form>
        </div>

        <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['paie'] ?? $fileCounts['Paie'] ?? 0 }}</p>
        <p style="font-size: 0.7rem; line-height: 0.3;">pièces générées : </p>
        <p style="font-size: 0.7rem; line-height: 0.3;">Pièces validées : </p>
    </div>

    <!-- Input caché pour fusion (hors formulaire) -->
 </div>

<!-- Input caché pour fusion Paie -->
<input type="file" id="paieFilesToMergeHidden" multiple style="display: none;" onchange="paie_handleMergeInput(event)">
<script>
   let selectedFilesPaie = [];
    let rotationsPaie = []; // <-- tableau pour stocker rotation (en degrés) par fichier
let sortOrderPaie = 'asc'; // Variable d'état pour l'ordre de tri

function sortFilesPaie() {
    // Inverser l'ordre de tri
    sortOrderPaie = sortOrderPaie === 'asc' ? 'desc' : 'asc';

    // Trier les fichiers en fonction de l'ordre
    selectedFilesPaie.sort((a, b) => {
        return sortOrderPaie === 'asc'
            ? a.name.localeCompare(b.name)
            : b.name.localeCompare(a.name);
    });

    // Mettre à jour l'affichage
    populateFileListPaie();

    // Changer le texte et la couleur du bouton pour indiquer l'ordre actuel
    const sortButtonPaie = document.getElementById('sortButtonPaie');
    sortButtonPaie.innerHTML = sortOrderPaie === 'asc'
        ? '<span style="font-size: 20px;">&#8593;</span><div style="line-height: 1;"><div style="font-size: 9px;">Z</div><div style="font-size: 9px;">A</div></div>'
        : '<span style="font-size: 20px;">&#8595;</span><div style="line-height: 1;"><div style="font-size: 9px;">A</div><div style="font-size: 9px;">Z</div></div>';

    sortButtonPaie.style.backgroundColor = sortOrderPaie === 'asc' ? '#cb0c9f' : '#e74c3c'; // Couleur pour A-Z et Z-A
}


// Gestion du choix d'import
function paie_handleUploadOption(option) {
    if (option === 'importer') {
        document.getElementById('file-paie').click();
    } else if (option === 'scanner') {
        alert("Fonction de scan non implémentée.");
    } else if (option === 'fusionner') {
        document.getElementById('paieFilesToMergeHidden').click();
    }
}

// Ajout fichiers pour fusion
async function paie_handleMergeInput(event) {
    const newFiles = Array.from(event.target.files);
    if (!newFiles.length) return;

    // Ajoute uniquement les nouveaux fichiers (évite les doublons par nom et taille)
    for (const file of newFiles) {
        if (!selectedFilesPaie.some(f => f.name === file.name && f.size === file.size)) {
            selectedFilesPaie.push(file);
            rotationsPaie.push(0);
        }
    }

    // Réinitialise l’input pour permettre de re-sélectionner le même fichier plus tard
    event.target.value = '';

    if (selectedFilesPaie.length < 2) {
        alert("Veuillez sélectionner au moins deux fichiers.");
        return;
    }

    showModalPaie();
    populateFileListPaie();
}
 function showModalPaie() {
        document.getElementById('fileOrderModalPaie').style.display = 'block';
    }
function paie_showModal() {
    document.getElementById('paieFileOrderModal').style.display = 'block';
}

function paie_closeModal() {
    document.getElementById('paieFileOrderModal').style.display = 'none';
}


  function drawImageWithRotationPaie(ctx, img, rotationDeg, canvasWidth, canvasHeight) {
    const radians = rotationDeg * Math.PI / 180;
    ctx.clearRect(0, 0, canvasWidth, canvasHeight);
    ctx.save();
    ctx.translate(canvasWidth / 2, canvasHeight / 2);
    ctx.rotate(radians);

    const scale = Math.min(canvasWidth / img.width, canvasHeight / img.height);
    ctx.drawImage(img, -img.width * scale / 2, -img.height * scale / 2, img.width * scale, img.height * scale);
    ctx.restore();
  }

  function rotateImagePaie(canvas, index) {
    rotationsPaie[index] = (rotationsPaie[index] + 90) % 360;
    const ctx = canvas.getContext('2d');
    const file = selectedFilesPaie[index];
    const reader = new FileReader();

    reader.onload = function(e) {
      if (file.type.startsWith('image/')) {
        const img = new Image();
        img.onload = function() {
          drawImageWithRotationPaie(ctx, img, rotationsPaie[index], canvas.width, canvas.height);
        };
        img.src = URL.createObjectURL(file);
      }
    };

    reader.readAsArrayBuffer(file);
  }





// Affiche la liste avec preview, icônes, drag & drop
// function paie_renderFileList() {
//     const fileList = document.getElementById('paieFileList');
//     fileList.innerHTML = '';

//     selectedFilesPaie.forEach((file, index) => {
//         const listItem = document.createElement('li');
//         // Styles inline pour affichage clair (flex, padding, border, ombre)
//         listItem.style.cssText = `
//             list-style: none;
//             margin: 10px 0;
//             padding: 10px;
//             background: white;
//             border-radius: 6px;
//             box-shadow: 0 2px 5px rgba(0,0,0,0.1);
//             display: flex;
//             align-items: center;
//             justify-content: space-between;
//             cursor: grab;
//             user-select: none;
//         `;
//         listItem.draggable = true;

//         // Gestion drag & drop pour réorganisation
//         listItem.addEventListener('dragstart', e => {
//             e.dataTransfer.setData('text/plain', index.toString());
//             e.currentTarget.style.opacity = '0.5';
//         });
//         listItem.addEventListener('dragend', e => {
//             e.currentTarget.style.opacity = '1';
//         });
//         listItem.addEventListener('dragover', e => {
//             e.preventDefault();
//             e.dataTransfer.dropEffect = 'move';
//         });
//         listItem.addEventListener('drop', e => {
//             e.preventDefault();
//             const draggedIndex = parseInt(e.dataTransfer.getData('text/plain'));
//             if (draggedIndex !== index) {
//                 paie_moveFile(draggedIndex, index);
//             }
//         });

//         // Ligne info fichier + icônes
//         const topRow = document.createElement('div');
//         topRow.style.cssText = `
//             display: flex;
//             justify-content: space-between;
//             align-items: center;
//             width: 300px;
//         `;

//         const fileInfo = document.createElement('div');
//         fileInfo.textContent = file.name;
//         fileInfo.style.cssText = `
//             font-weight: 600;
//             overflow: hidden;
//             text-overflow: ellipsis;
//             white-space: nowrap;
//             max-width: 230px;
//         `;

//         const iconRow = document.createElement('div');
//         iconRow.style.cssText = `
//             display: flex;
//             gap: 12px;
//             font-size: 18px;
//         `;

//         const deleteIcon = document.createElement('span');
//         deleteIcon.textContent = '❌';
//         deleteIcon.style.cssText = `
//             cursor: pointer;
//             user-select: none;
//             transition: color 0.2s;
//         `;
//         deleteIcon.title = 'Supprimer';
//         deleteIcon.onmouseenter = () => deleteIcon.style.color = '#e74c3c';
//         deleteIcon.onmouseleave = () => deleteIcon.style.color = 'black';
//         deleteIcon.onclick = () => {
//             selectedFilesPaie.splice(index, 1);
//             rotationsPaie.splice(index, 1);
//             paie_renderFileList();
//         };

//         const rotateIcon = document.createElement('span');
//         rotateIcon.textContent = '🔄';
//         rotateIcon.style.cssText = `
//             cursor: pointer;
//             user-select: none;
//             transition: color 0.2s;
//         `;
//         rotateIcon.title = 'Tourner';
//         rotateIcon.onmouseenter = () => rotateIcon.style.color = '#cb0c9f';
//         rotateIcon.onmouseleave = () => rotateIcon.style.color = 'black';
//         rotateIcon.onclick = () => paie_rotateImage(index);

//         iconRow.appendChild(deleteIcon);
//         iconRow.appendChild(rotateIcon);
//         topRow.appendChild(fileInfo);
//         topRow.appendChild(iconRow);

//         // Canvas preview
//         const preview = document.createElement('canvas');
//         preview.width = 200;
//         preview.height = 260;
//         preview.style.marginLeft = '15px';
//         const ctx = preview.getContext('2d');
//         ctx.fillStyle = "#f0f0f0";
//         ctx.fillRect(0, 0, preview.width, preview.height);

//         // Chargement preview PDF ou image
//         const reader = new FileReader();
//         reader.onload = async function (e) {
//             const arrayBuffer = e.target.result;
//             if (file.type === 'application/pdf') {
//                 try {
//                     const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
//                     const page = await pdf.getPage(1);
//                     const viewport = page.getViewport({ scale: Math.min(preview.width / page.getViewport({ scale: 1 }).width, preview.height / page.getViewport({ scale: 1 }).height) });
//                     page.render({ canvasContext: ctx, viewport });
//                 } catch (err) {
//                     ctx.fillStyle = '#f44336';
//                     ctx.fillRect(0, 0, preview.width, preview.height);
//                     ctx.fillStyle = 'white';
//                     ctx.font = '16px sans-serif';
//                     ctx.fillText('Erreur PDF', 40, 130);
//                 }
//             } else if (file.type.startsWith('image/')) {
//                 const img = new Image();
//                 img.onload = () => paie_drawImageWithRotation(ctx, img, rotationsPaie[index], preview.width, preview.height);
//                 img.src = URL.createObjectURL(file);
//             }
//         };
//         reader.readAsArrayBuffer(file);

//         listItem.appendChild(topRow);
//         listItem.appendChild(preview);
//         fileList.appendChild(listItem);
//     });
// }

// // Dessine image avec rotation sur canvas
// function paie_drawImageWithRotation(ctx, img, degrees, w, h) {
//     ctx.clearRect(0, 0, w, h);
//     ctx.save();
//     ctx.translate(w / 2, h / 2);
//     ctx.rotate(degrees * Math.PI / 180);
//     let scale = Math.min(w / img.width, h / img.height);
//     let width = img.width * scale;
//     let height = img.height * scale;
//     if (degrees % 180 !== 0) [width, height] = [height, width];
//     ctx.drawImage(img, -width / 2, -height / 2, width, height);
//     ctx.restore();
// }

// // Rotation image (modifie angle, redessine la liste)
// function paie_rotateImage(index) {
//     rotationsPaie[index] = (rotationsPaie[index] + 90) % 360;
//     paie_renderFileList();
// }



    function populateFileListPaie() {
        const fileListPaie = document.getElementById('fileListPaie');
        fileList.innerHTML = '';

        selectedFilesPaie.forEach((file, index) => {
            const listItemPaie = document.createElement('li');
            // listItem.style.display = 'flex';
            listItemPaie.style.flexDirection = 'column';
            // listItem.style.alignItems = 'center';
            listItemPaie.style.marginBottom = '15px';
            listItemPaie.style.padding = '10px';
            listItemPaie.style.marginLeft = '10px';
            listItemPaie.style.backgroundColor = '#ffffff';
            listItemPaie.style.border = '1px solid #ddd';
            listItemPaie.style.borderRadius = '8px';
            listItemPaie.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            listItemPaie.style.transition = 'box-shadow 0.2s';
            listItemPaie.style.width = '23%';
            listItemPaie.draggable = true;

            listItemPaie.addEventListener('mouseover', () => {
                listItemPaie.style.boxShadow = '0 6px 14px rgba(0,0,0,0.1)';
            });
            listItemPaie.addEventListener('mouseout', () => {
                listItemPaie.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            });

            const topRowPaie = document.createElement('div');
            topRowPaie.style.display = 'flex';
            topRowPaie.style.alignItems = 'center';
            topRowPaie.style.justifyContent = 'space-between';
            topRowPaie.style.width = '100%';
            topRowPaie.style.marginBottom = '8px';

            const fileInfoPaie = document.createElement('div');
            fileInfoPaie.style.display = 'flex';
            fileInfoPaie.style.alignItems = 'center';
            fileInfoPaie.style.flexGrow = '1';
            fileInfoPaie.style.gap = '5px';
            fileInfoPaie.innerHTML = `<span style="font-size:11px; color:#333; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${file.name}</span>`;

            const iconRowPaie = document.createElement('div');
            iconRowPaie.style.display = 'flex';
            iconRowPaie.style.gap = '10px';

            const deleteIconPaie = document.createElement('span');
            deleteIconPaie.textContent = '❌';
            deleteIconPaie.title = 'Supprimer';
            deleteIconPaie.style.cursor = 'pointer';
            deleteIconPaie.style.transition = 'color 0.2s';
            deleteIconPaie.onmouseover = () => deleteIconPaie.style.color = '#e74c3c';
            deleteIconPaie.onmouseout = () => deleteIconPaie.style.color = 'inherit';
            deleteIconPaie.onclick = () => {
                selectedFilesPaie.splice(index, 1);
                rotationsPaie.splice(index, 1); // Supprimer aussi la rotation correspondante
                populateFileListPaie();
            };

            const rotateIconPaie = document.createElement('span');
            rotateIconPaie.textContent = '🔄';
            rotateIconPaie.title = 'Rotation';
            rotateIconPaie.style.cursor = 'pointer';
            rotateIconPaie.style.transition = 'color 0.2s';
            rotateIconPaie.onmouseover = () => rotateIconPaie.style.color = '#3498db';
            rotateIconPaie.onmouseout = () => rotateIconPaie.style.color = 'inherit';

            iconRowPaie.appendChild(deleteIconPaie);
            iconRowPaie.appendChild(rotateIconPaie);

            topRowPaie.appendChild(fileInfoPaie);
            topRowPaie.appendChild(iconRowPaie);

            const previewPaie = document.createElement('canvas');
            previewPaie.width = 200;
            previewPaie.height = 260;
            previewPaie.style.border = '1px solid #ccc';
            previewPaie.style.borderRadius = '4px';
            previewPaie.style.marginBottom = '5px';

            const ctx = previewPaie.getContext('2d');
            ctx.fillStyle = "#f0f0f0";
            ctx.fillRect(0, 0, previewPaie.width, previewPaie.height);

            rotateIconPaie.onclick = () => rotateImagePaie(previewPaie, index);

            const legendPaie = document.createElement('div');
            legendPaie.style.fontSize = '10px';
            legendPaie.style.color = '#777';
            legendPaie.style.marginTop = '4px';

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
                        drawImageWithRotation(ctx, img, rotationsPaie[index], previewPaie.width, previewPaie.height);
                    };
                    img.src = URL.createObjectURL(file);
                }
            };
            reader.readAsArrayBuffer(file);

            listItemPaie.appendChild(topRowPaie);
            listItemPaie.appendChild(previewPaie);
            listItemPaie.appendChild(legendPaie);

            listItemPaie.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', index);
            });
            listItemPaie.addEventListener('dragover', (e) => e.preventDefault());
            listItemPaie.addEventListener('drop', (e) => {
                e.preventDefault();
                const fromIndex = e.dataTransfer.getData('text/plain');
                moveFile(fromIndex, index);
            });

            fileListPaie.appendChild(listItemPaie);
        });
    }

    function drawImageWithRotationPaie(ctx, img, degrees, canvasWidth, canvasHeight) {
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

   function rotateImage(previewPaie, index) {
    // Incrémente la rotation de 90° (modulo 360)
    rotations[index] = (rotations[index] + 90) % 360;

    // Redessine l'image avec la nouvelle rotation
    const ctx = previewPaie.getContext('2d');
    const file = selectedFilesPaie[index]; // Récupérer le fichier original
    const reader = new FileReader();

    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            drawImageWithRotationPaie(ctx, img, rotations[index], previewPaie.width, previewPaie.height);
        };
        img.src = e.target.result; // Utiliser le résultat du FileReader
    };
    reader.readAsDataURL(file); // Lire le fichier comme URL de données
}
// Déplacement fichier dans la liste (drag & drop)
function paie_moveFile(currentIndex, newIndex) {
    currentIndex = parseInt(currentIndex);
    if (newIndex < 0 || newIndex >= selectedFilesPaie.length || currentIndex === newIndex) return;
    const [file] = selectedFilesPaie.splice(currentIndex, 1);
    const [rotation] = rotationsPaie.splice(currentIndex, 1);
    selectedFilesPaie.splice(newIndex, 0, file);
    rotationsPaie.splice(newIndex, 0, rotation);
    populateFileListPaie();
}

// Confirme la fusion (ferme modal et lance fusion)
async function paie_confirmMerge() {
    paie_closeModal();
    await paie_mergeFiles();
}

// Fusionne tous les fichiers sélectionnés dans un PDF
async function paie_mergeFiles(files) {
    try {
        const mergedPdf = await PDFLib.PDFDocument.create();

        for (let i = 0; i < selectedFilesPaie.length; i++) {
            const file = selectedFilesPaie[i];
            const rotation = rotationsPaie[i];
            const buffer = await file.arrayBuffer();

            if (file.type === 'application/pdf') {
                const pdf = await PDFLib.PDFDocument.load(buffer);
                const pages = await mergedPdf.copyPages(pdf, pdf.getPageIndices());
                pages.forEach((page) => mergedPdf.addPage(page));
            } else if (file.type.startsWith('image/')) {
                const tempPdf = await PDFLib.PDFDocument.create();
                const imageBytes = new Uint8Array(buffer);
                let image;
                if (file.type === 'image/png') {
                    image = await tempPdf.embedPng(imageBytes);
                } else {
                    image = await tempPdf.embedJpg(imageBytes);
                }
                const page = tempPdf.addPage([595.28, 841.89]);
                // Applique rotation sur image
                const radians = rotation * Math.PI / 180;
                let width = image.width;
                let height = image.height;
                let x = (595.28 - width) / 2;
                let y = (841.89 - height) / 2;

                // Pour gérer rotation 90 ou 270, on inverse width/height
                let rotateOptions = {};
                if (rotation % 180 !== 0) {
                    [width, height] = [height, width];
                }

                page.drawImage(image, {
                    x,
                    y,
                    width,
                    height,
                    rotate: PDFLib.degrees(rotation),
                    // Le pivot par défaut est en bas à gauche — centrer rotation (optionnel)
                });

                const [copied] = await mergedPdf.copyPages(tempPdf, [0]);
                mergedPdf.addPage(copied);
            }
        }

        const pdfBytes = await mergedPdf.save();
        const blob = new Blob([pdfBytes], { type: 'application/pdf' });
        const formData = new FormData();
       const fileNameBase = selectedFilesPaie.map(f => f.name.replace(/\.[^/.]+$/, '')).join('_');

// Limiter la longueur si trop long (optionnel)
const safeFileName = fileNameBase.substring(0, 100).replace(/[^\w\-]/g, '_');

formData.append('file', blob, `${safeFileName}_${Date.now()}.pdf`);
        formData.append('societe_id', '{{ session()->get('societeId') }}');
        formData.append('type', 'paie');

        const response = await fetch('/uploadFusionner', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const result = await response.json();

        if (result.success) {
            const msg = document.createElement('div');
            msg.textContent = "Fusion réussie !";
            Object.assign(msg.style, {
                position: 'fixed', top: '150px', left: '50%',
                transform: 'translateX(-50%)',
                backgroundColor: '#4CAF50',
                color: 'white',
                padding: '10px 20px',
                borderRadius: '5px',
                zIndex: 9999,
                boxShadow: '0px 0px 10px rgba(0, 0, 0, 0.2)'
            });
            document.body.appendChild(msg);
            setTimeout(() => location.reload(), 2000);
        } else {
            alert("Erreur serveur.");
        }
    } catch (err) {
        console.error(err);
        alert("Erreur lors de la fusion.");
    }
}

// Pour ouvrir la sélection de fichiers depuis un bouton
// function paie_addFileManually() {
//     document.getElementById('paieFilesToMergeHidden').click();
// }
function addFilePaie() {
    document.getElementById('paieFilesToMergeHidden').click();
}
 async function confirmOrderPaie() {
        closeModal();
        await paie_mergeFiles(selectedFilesPaie);
    } 

</script>
 


<!-- 💬 Modal HTML -->
<div id="fileOrderModalPaie" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModalPaie()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;height:120%;">

        <ul id="fileListPaie" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        <!-- Colonne verticale à droite -->
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; gap: 18px; height: 100%; margin-left: 10px;">
            <span onclick="addFilePaie()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
            <span id="sortButtonPaie" onclick="sortFilesPaie()" 
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
            <button  onclick="confirmOrderPaie()" style="background-color: #007BFF; color: white; padding: 0 6px; font-size: 11px; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.2s; width: auto; min-width: 0; margin-top: 0; display: flex; align-items: center; justify-content: center; height: 16px; min-height: 0; max-height: 35px; line-height: 35px; box-sizing: border-box;">Valider</button>
        </div>
    </div>

</div>

<!-- 💬 Modal HTML -->
<!-- <div id="fileOrderModalPaie" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModalPaie()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;height:120%;">
        <ul id="fileListPaie" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <span onclick="addFilePaie()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
<span id="sortButtonPaie" onclick="sortFilesPaie()" 
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
        </div>
    </div>

  <div style="margin-top: -72%;margin-left: 93%; width: fit-content;">
     <button onclick="confirmOrderPaie()" style="background-color: #007BFF; color: white; padding: 10px 16px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s;">Valider</button>
</div>

</div> -->

<style>
  #fileOrderModalPaie {
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

  #fileOrderModalPaie h3 {
    margin-top: 0;
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
  }

  #fileOrderModalPaie ul {
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

  #fileOrderModalPaie ul li {
    /* padding: 20px; */
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 9px;
    display: inline-block;
    transition: background-color 0.2s;
  }

  #fileOrderModalPaie ul li:hover {
    background-color: #f1f1f1;
  }

  #fileOrderModalPaie span[onclick] {
    color: #007BFF;
    font-weight: 500;
    transition: color 0.2s;
  }

  #fileOrderModalPaie span[onclick]:hover {
    color: #0056b3;
  }

  #fileOrderModalPaie .action-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
  }

  #fileOrderModalPaie button {
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

  #fileOrderModalPaie button:hover {
    background-color: #0056b3;
  }

  #fileOrderModalPaie button:first-of-type {
    background-color: #6c757d;
  }

  #fileOrderModalPaie button:first-of-type:hover {
    background-color: #5a6268;
  }

  #fileOrderModalPaie > span {
    position: absolute;
    top: 15px;
    right: 20px;
    cursor: pointer;
    font-size: 24px;
    font-weight: bold;
    color: #999;
  }

  #fileOrderModalPaie > span:hover {
    color: #333;
  }
 
</style>

  
 


     <!-- Dossier permanent -->
<div class="col-md-3 mb-3" id="FusionDocuments-div">
    <div class="p-2 text-white" style="background-color:#333333b8; border-radius: 15px; font-size: 0.75rem; height: 130px;">
        <div class="d-flex justify-content-between align-items-center">
            <h5 style="color: white; font-size: 12px;">Dossier permanent</h5>
            <form id="form-FusionDocuments" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="dossier permanent">
<input type="file" name="files[]" id="file-FusionDocuments" style="display: none;" multiple onchange="handleFileSelect(event, 'FusionDocuments')">
                <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                <input type="hidden" name="folders_id" value="0">

                <div class="">
                    <button 
                        class="btn btn-light btn-sm" 
                        type="button" 
                        id="dropdownMenuButtonFusionDocuments" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false"
                        style="background-color: #3333332b; border: 1px solid white; border-radius: 10px; color: white; width:100px;">
                        Charger
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonFusionDocuments">
                        <li><a class="dropdown-item" href="#" onclick="handleUploadFusionDocuments('importer')">Importer</a></li>
                        <!-- <li><a class="dropdown-item" href="#" onclick="handleUploadFusionDocuments('scanner')">Scanner</a></li> -->
                        <li><a class="dropdown-item" href="#" onclick="handleUploadFusionDocuments('fusionner')">Fusionner</a></li>
                    </ul>
                </div>

                <button type="submit" style="display: none;" id="submit-FusionDocuments">Envoyer</button>
            </form>
        </div>
        <p style="font-size : 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['dossier_permanant'] ?? 0 }}</p>
        <p style="font-size: 0.7rem; line-height: 0.3;">pièces générées : </p>
        <p style="font-size: 0.7rem; line-height: 0.3;">Pièces validées : </p>
    </div>
</div>

 <!-- Input caché pour fusion -->
<input type="file" id="filesToMergeFusionDocumentsHidden" multiple style="display: none;" onchange="mergeFilesFusionDocumentsDirect(event)">

<script>
let selectedFilesDossierPermanant = [];
let rotationsDossierPermanant = []; // <-- tableau pour stocker rotation (en degrés) par fichier
let sortOrderDossierPermanant = 'asc'; // Variable d'état pour l'ordre de tri

function sortFilesDossierPermanant() {
    // Inverser l'ordre de tri
    sortOrderDossierPermanant = sortOrderDossierPermanant === 'asc' ? 'desc' : 'asc';

    // Trier les fichiers en fonction de l'ordre
    selectedFilesDossierPermanant.sort((a, b) => {
        return sortOrderDossierPermanant === 'asc' 
            ? a.name.localeCompare(b.name) 
            : b.name.localeCompare(a.name);
    });

    // Mettre à jour l'affichage
    populateFileListDossierPermanant();

    // Changer le texte et la couleur du bouton pour indiquer l'ordre actuel
    const sortButtonDossierPermanant = document.getElementById('sortButtonDossierPermanant');
    sortButtonDossierPermanant.innerHTML = sortOrderDossierPermanant === 'asc' 
        ? '<span style="font-size: 20px;">&#8593;</span><div style="line-height: 1;"><div style="font-size: 9px;">Z</div><div style="font-size: 9px;">A</div></div>'
        : '<span style="font-size: 20px;">&#8595;</span><div style="line-height: 1;"><div style="font-size: 9px;">A</div><div style="font-size: 9px;">Z</div></div>';

    sortButtonDossierPermanant.style.backgroundColor = sortOrderDossierPermanant === 'asc' ? '#cb0c9f' : '#e74c3c'; // Couleur pour A-Z et Z-A
}
 function handleUploadFusionDocuments(option) {
     if (option === 'importer') {
         document.getElementById('file-FusionDocuments').click();
     } else if (option === 'scanner') {
         alert("Fonction de scan non implémentée.");
     } else if (option === 'fusionner') {
         document.getElementById('filesToMergeFusionDocumentsHidden').click();
     }
 }

 async function mergeFilesFusionDocumentsDirect(event) {
     const newFiles = Array.from(event.target.files);
     if (!newFiles.length) return;
     selectedFilesDossierPermanant.push(...newFiles);
     for(let i = 0; i < newFiles.length; i++) {
         rotationsDossierPermanant.push(0);
     }
     if (selectedFilesDossierPermanant.length < 2) {
         alert("Veuillez sélectionner au moins deux fichiers.");
         return;
     }
     showModalDossierPermanant();
     populateFileListDossierPermanant();
 }
  function showModalDossierPermanant() {
        document.getElementById('fileOrderModalDossierPermanant').style.display = 'block';
    }


 function showModalFusionDocuments() {
     document.getElementById('fileOrderModalFusionDocuments').style.display = 'block';
 }

 function closeModalFusionDocuments() {
     document.getElementById('fileOrderModalFusionDocuments').style.display = 'none';
 }

    
  function drawImageWithRotationDossierPermanant(ctx, img, rotationDeg, canvasWidth, canvasHeight) {
    const radians = rotationDeg * Math.PI / 180;
    ctx.clearRect(0, 0, canvasWidth, canvasHeight);
    ctx.save();
    ctx.translate(canvasWidth / 2, canvasHeight / 2);
    ctx.rotate(radians);

    const scale = Math.min(canvasWidth / img.width, canvasHeight / img.height);
    ctx.drawImage(img, -img.width * scale / 2, -img.height * scale / 2, img.width * scale, img.height * scale);
    ctx.restore();
  }

  function rotateImageDossierPermanant(canvas, index) {
    rotationsDossierPermanant[index] = (rotationsDossierPermanant[index] + 90) % 360;
    const ctx = canvas.getContext('2d');
    const file = selectedFilesDossierPermanant[index];
    const reader = new FileReader();

    reader.onload = function(e) {
      if (file.type.startsWith('image/')) {
        const img = new Image();
        img.onload = function() {
          drawImageWithRotationDossierPermanant(ctx, img, rotationsDossierPermanant[index], canvas.width, canvas.height);
        };
        img.src = URL.createObjectURL(file);
      }
    };

    reader.readAsArrayBuffer(file);
  }

function populateFileListDossierPermanant() {
        const fileListDossierPermanant = document.getElementById('fileListDossierPermanant');
        fileListDossierPermanant.innerHTML = '';

        selectedFilesDossierPermanant.forEach((file, index) => {
            const listItemDossierPermanant = document.createElement('li');
            // listItem.style.display = 'flex';
            listItemDossierPermanant.style.flexDirection = 'column';
            // listItem.style.alignItems = 'center';
            listItemDossierPermanant.style.marginBottom = '15px';
            listItemDossierPermanant.style.padding = '10px';
            listItemDossierPermanant.style.marginLeft = '10px';
            listItemDossierPermanant.style.backgroundColor = '#ffffff';
            listItemDossierPermanant.style.border = '1px solid #ddd';
            listItemDossierPermanant.style.borderRadius = '8px';
            listItemDossierPermanant.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            listItemDossierPermanant.style.transition = 'box-shadow 0.2s';
            listItemDossierPermanant.style.width = '23%';
            listItemDossierPermanant.draggable = true;

            listItemDossierPermanant.addEventListener('mouseover', () => {
                listItemDossierPermanant.style.boxShadow = '0 6px 14px rgba(0,0,0,0.1)';
            });
            listItemDossierPermanant.addEventListener('mouseout', () => {
                listItemDossierPermanant.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            });

            const topRowDossierPermanant = document.createElement('div');
            topRowDossierPermanant.style.display = 'flex';
            topRowDossierPermanant.style.alignItems = 'center';
            topRowDossierPermanant.style.justifyContent = 'space-between';
            topRowDossierPermanant.style.width = '100%';
            topRowDossierPermanant.style.marginBottom = '8px';

            const fileInfoDossierPermanant = document.createElement('div');
            fileInfoDossierPermanant.style.display = 'flex';
            fileInfoDossierPermanant.style.alignItems = 'center';
            fileInfoDossierPermanant.style.flexGrow = '1';
            fileInfoDossierPermanant.style.gap = '5px';
            fileInfoDossierPermanant.innerHTML = `<span style="font-size:11px; color:#333; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${file.name}</span>`;

            const iconRowDossierPermanant = document.createElement('div');
            iconRowDossierPermanant.style.display = 'flex';
            iconRowDossierPermanant.style.gap = '10px';

            const deleteIconDossierPermanant = document.createElement('span');
            deleteIconDossierPermanant.textContent = '❌';
            deleteIconDossierPermanant.title = 'Supprimer';
            deleteIconDossierPermanant.style.cursor = 'pointer';
            deleteIconDossierPermanant.style.transition = 'color 0.2s';
            deleteIconDossierPermanant.onmouseover = () => deleteIconDossierPermanant.style.color = '#e74c3c';
            deleteIconDossierPermanant.onmouseout = () => deleteIconDossierPermanant.style.color = 'inherit';
            deleteIconDossierPermanant.onclick = () => {
                selectedFilesDossierPermanant.splice(index, 1);
                rotationsDossierPermanant.splice(index, 1); // Supprimer aussi la rotation correspondante
                populateFileList();
            };

            const rotateIconDossierPermanant = document.createElement('span');
            rotateIconDossierPermanant.textContent = '🔄';
            rotateIconDossierPermanant.title = 'Retation';
            rotateIconDossierPermanant.style.cursor = 'pointer';
            rotateIconDossierPermanant.style.transition = 'color 0.2s';
            rotateIconDossierPermanant.onmouseover = () => rotateIconDossierPermanant.style.color = '#3498db';
            rotateIconDossierPermanant.onmouseout = () => rotateIconDossierPermanant.style.color = 'inherit';

            iconRowDossierPermanant.appendChild(deleteIconDossierPermanant);
            iconRowDossierPermanant.appendChild(rotateIconDossierPermanant);

            topRowDossierPermanant.appendChild(fileInfoDossierPermanant);
            topRowDossierPermanant.appendChild(iconRowDossierPermanant);

            const previewDossierPermanant = document.createElement('canvas');
            previewDossierPermanant.width = 200;
            previewDossierPermanant.height = 260;
            previewDossierPermanant.style.border = '1px solid #ccc';
            previewDossierPermanant.style.borderRadius = '4px';
            previewDossierPermanant.style.marginBottom = '5px';

            const ctx = previewDossierPermanant.getContext('2d');
            ctx.fillStyle = "#f0f0f0";
            ctx.fillRect(0, 0, previewDossierPermanant.width, previewDossierPermanant.height);

            rotateIconDossierPermanant.onclick = () => rotateImage(previewDossierPermanant, index);

            const legendDossierPermanant = document.createElement('div');
            legendDossierPermanant.style.fontSize = '10px';
            legendDossierPermanant.style.color = '#777';
            legendDossierPermanant.style.marginTop = '4px';

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
                        drawImageWithRotationDossierPermanant(ctx, img, rotationsDossierPermanant[index], previewDossierPermanant.width, previewDossierPermanant.height);
                    };
                    img.src = URL.createObjectURL(file);
                }
            };
            reader.readAsArrayBuffer(file);

            listItemDossierPermanant.appendChild(topRowDossierPermanant);
            listItemDossierPermanant.appendChild(previewDossierPermanant);
            listItemDossierPermanant.appendChild(legendDossierPermanant);

            listItemDossierPermanant.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', index);
            });
            listItemDossierPermanant.addEventListener('dragover', (e) => e.preventDefault());
            listItemDossierPermanant.addEventListener('drop', (e) => {
                e.preventDefault();
                const fromIndex = e.dataTransfer.getData('text/plain');
                moveFile(fromIndex, index);
            });

            fileListDossierPermanant.appendChild(listItemDossierPermanant);
        });
    }

    function drawImageWithRotationDossierPermanant(ctx, img, degrees, canvasWidth, canvasHeight) {
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

   function rotateImageDossierPermanant(preview, index) {
    // Incrémente la rotation de 90° (modulo 360)
    rotations[index] = (rotations[index] + 90) % 360;

    // Redessine l'image avec la nouvelle rotation
    const ctx = previewDossierPermanant.getContext('2d');
    const file = selectedFilesDossierPermanant[index]; // Récupérer le fichier original
    const reader = new FileReader();

    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            drawImageWithRotationDossierPermanant(ctx, img, rotations[index], previewDossierPermanant.width, previewDossierPermanant.height);
        };
        img.src = e.target.result; // Utiliser le résultat du FileReader
    };
    reader.readAsDataURL(file); // Lire le fichier comme URL de données
}


 function moveFileFusionDocuments(currentIndex, newIndex) {
     currentIndex = parseInt(currentIndex);
     if (newIndex < 0 || newIndex >= selectedFilesDossierPermanant.length || currentIndex === newIndex) return;
     const [moved] = selectedFilesDossierPermanant.splice(currentIndex, 1);
     const [rotMoved] = rotationsDossierPermanant.splice(currentIndex, 1);
     selectedFilesDossierPermanant.splice(newIndex, 0, moved);
     rotationsDossierPermanant.splice(newIndex, 0, rotMoved);
     populateFileListFusionDocuments();
 }

 async function confirmOrderFusionDocuments() {
     closeModalFusionDocuments();
     await mergeFilesFusionDocuments();
 }
  async function confirmOrderDossierPermanant() {
        closeModal();
        await mergeFilesFusionDocuments(selectedFilesDossierPermanant);
    } 
 async function mergeFilesFusionDocuments() {
     try {
         const mergedPdf = await PDFLib.PDFDocument.create();
          for (const file of selectedFilesDossierPermanant) {
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
                 }

                 const page = imagePdf.addPage([595.28, 841.89]);
                 page.drawImage(image, { x: 30, y: 100, width: 500, height: 700 });

                 const copiedPages = await mergedPdf.copyPages(imagePdf, [0]);
                 copiedPages.forEach((page) => mergedPdf.addPage(page));
             }
         }

         const pdfBytes = await mergedPdf.save();
         const blob = new Blob([pdfBytes], { type: 'application/pdf' });
         const formData = new FormData();
         const fileNameBase = selectedFilesDossierPermanant.map(f => f.name.replace(/\.[^/.]+$/, '')).join('_');

// Limiter la longueur si trop long (optionnel)
const safeFileName = fileNameBase.substring(0, 100).replace(/[^\w\-]/g, '_');

formData.append('file', blob, `${safeFileName}_${Date.now()}.pdf`);
         formData.append('societe_id', '{{ session()->get('societeId') }}');
         formData.append('type', 'dossier permanent');

         const response = await fetch('/uploadFusionner', {
             method: 'POST',
             body: formData,
             headers: {
                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
             }
         });

         const data = await response.json();

         if (data.success) {
             const messageDiv = document.createElement('div');
             messageDiv.textContent = "Fichiers fusionnés avec succès.";
             document.body.appendChild(messageDiv);
             setTimeout(() => { location.reload(); }, 2000);
         } else {
             alert("Erreur lors de l'envoi des fichiers.");
         }
     } catch (e) {
         console.error(e);
         alert("Erreur lors de la fusion.");
     }
 }

 function addFileDossierPermanant() {
    document.getElementById('filesToMergeFusionDocumentsHidden').click();
}
function closeModalDossierPermanant() {
        document.getElementById('fileOrderModalDossierPermanant').style.display = 'none';
    }

</script>




<!-- 💬 Modal HTML -->
<div id="fileOrderModalDossierPermanant" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModalDossierPermanant()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;height:120%;">

        <ul id="fileListDossierPermanant" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        <!-- Colonne verticale à droite -->
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; gap: 18px; height: 100%; margin-left: 10px;">
            <span onclick="addFileDossierPermanant() style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
<span id="sortButtonDossierPermanant" onclick="sortFilesDossierPermanant()" 
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
            <button onclick="confirmOrderDossierPermanant()" style="background-color: #007BFF; color: white; padding: 0 6px; font-size: 11px; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.2s; width: auto; min-width: 0; margin-top: 0; display: flex; align-items: center; justify-content: center; height: 16px; min-height: 0; max-height: 35px; line-height: 35px; box-sizing: border-box;">Valider</button>
        </div>
    </div>

</div>


<!-- 💬 Modal HTML -->
 <!-- <div id="fileOrderModalDossierPermanant" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModalDossierPermanant()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;height:120%;">
        <ul id="fileListDossierPermanant" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <span onclick="addFileDossierPermanant()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
<span id="sortButtonDossierPermanant" onclick="sortFilesDossierPermanant()" 
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
        </div>
    </div>
  <div style="margin-top: -72%;margin-left: 93%; width: fit-content;">
    <button onclick="confirmOrderDossierPermanant()" style="background-color: #007BFF; color: white; padding: 10px 16px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s;">Valider</button>
</div>

</div> -->

<style>
  #fileOrderModalDossierPermanant {
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

  #fileOrderModalDossierPermanant h3 {
    margin-top: 0;
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
  }

  #fileOrderModalDossierPermanant ul {
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

  #fileOrderModalDossierPermanant ul li {
    /* padding: 20px; */
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 9px;
    display: inline-block;
    transition: background-color 0.2s;
  }

  #fileOrderModalDossierPermanant ul li:hover {
    background-color: #f1f1f1;
  }

  #fileOrderModalDossierPermanant span[onclick] {
    color: #007BFF;
    font-weight: 500;
    transition: color 0.2s;
  }

  #fileOrderModalDossierPermanant span[onclick]:hover {
    color: #0056b3;
  }

  #fileOrderModalDossierPermanant .action-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
  }

  #fileOrderModalDossierPermanant button {
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

  #fileOrderModalDossierPermanant button:hover {
    background-color: #0056b3;
  }

  #fileOrderModalDossierPermanant button:first-of-type {
    background-color: #6c757d;
  }

  #fileOrderModalDossierPermanant button:first-of-type:hover {
    background-color: #5a6268;
  }

  #fileOrderModalDossierPermanant > span {
    position: absolute;
    top: 15px;
    right: 20px;
    cursor: pointer;
    font-size: 24px;
    font-weight: bold;
    color: #999;
  }

  #fileOrderModalDossierPermanant > span:hover {
    color: #333;
  }
 
</style>

    <div class="row">
        <!-- Modal Fusion Dossier Dynamique -->

        
<!-- 💬 Modal HTML -->
<div id="fileOrderModalDossierDynamic" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModalDossierDynamic()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;height:120%;">

        <ul id="fileListDossierDynamic" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        <!-- Colonne verticale à droite -->
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; gap: 18px; height: 100%; margin-left: 10px;">
            <span onclick="addFileDossierDynamic()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
            <span id="sortButtonDossierDynamic" onclick="sortFilesDossierDynamic()" 
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
            <button onclick="confirmOrderDossierDynamic()" style="background-color: #007BFF; color: white; padding: 0 6px; font-size: 11px; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.2s; width: auto; min-width: 0; margin-top: 0; display: flex; align-items: center; justify-content: center; height: 16px; min-height: 0; max-height: 35px; line-height: 35px; box-sizing: border-box;">Valider</button>
        </div>
    </div>

</div>



<!-- <div id="fileOrderModalDossierDynamic" style="display:none; position:fixed; top:30%; left:32%; transform:translate(-30%, -30%); background-color:white; border:1px solid #ccc; padding:20px; z-index:10000;width:90%;height:90%;">
    <span onclick="closeModalDossierDynamic()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; font-weight:bold;">&times;</span>
    <h3>Fusionner</h3>
    <div style="display: flex; align-items: flex-start; gap: 20px;height:120%;">
        <ul id="fileListDossierDynamic" style="list-style-type:none; padding:0; margin:0; max-height: 70%; overflow-y: auto; border: 1px solid #eee; border-radius: 4px; padding: 10px; background-color: #f9f9f9; flex: 1;"></ul>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <span onclick="addFileDossierDynamic()" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; font-size: 24px; user-select: none; color: #555;">➕</span>
            <span id="sortButtonDossierDynamic" onclick="sortFilesDossierDynamic()" 
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
        </div>
    </div>
    <div style="margin-top: -72%;margin-left: 93%; width: fit-content;">
        <button onclick="confirmOrderDossierDynamic()" style="background-color: #007BFF; color: white; padding: 10px 16px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s;">Valider</button>
    </div>
</div> -->
 @foreach($dossiers as $dossier)
@php
    $bgColor = $dossier->color ?? ('#' . substr(md5($dossier->id), 0, 6));
@endphp


<div class="col-md-3 mb-3">
    <div class="p-2 text-white dossier-box" 
         style="border-radius: 15px; font-size: 0.75rem; height: 130px; background-color: {{ $bgColor }};" 
         data-id="{{ $dossier->id }}">

        <div class="d-flex justify-content-between align-items-center">
            <h5 style="color: white; font-size: 12px;text-transform: capitalize;">
                {{ $dossier->name }}
            </h5>

            <!-- Dropdown renommage/suppression -->
            <div class="">
                <button class="btn btn-link text-white" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li>
                        <a class="dropdown-item" href="#" onclick="openEditFolderModal('{{ $dossier->id }}', '{{ $dossier->name }}')">Renommer</a>
                    </li>
                    <li>
                        <form action="{{ route('dossier.delete', $dossier->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item" style="background: transparent; border: none; color: red;">Supprimer</button>
                        </form>
                    </li>
                </ul>
            </div>

            <!-- Dropdown bouton Charger -->
            <div class="">
                <button class="btn btn-light btn-sm" type="button" id="dropdownMenuButtons" data-bs-toggle="dropdown" aria-expanded="false"
                    style="border: 1px solid white; border-radius: 10px; color: white; width:100px; background-color: {{ $bgColor }};">
                    Charger
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="#" onclick="handleUploadX('importer', {{ $dossier->id }}, '{{ $dossier->name }}')">Importer</a></li>
                    <li><a class="dropdown-item" href="#" onclick="handleUploadX('fusionner', {{ $dossier->id }}, '{{ $dossier->name }}')">Fusionner</a></li>
                </ul>
            </div>

            <form id="form-{{ $dossier->id }}" action="{{ route('Douvrir.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                <input type="hidden" name="folder_type" value="{{ $dossier->name }}">
                <input type="file" name="file" id="file-{{ $dossier->id }}" style="display: none;" onchange="handleFileSelect(event, {{ $dossier->id }})">
                <button type="button" style="display: none;" id="submit-{{ $dossier->id }}">Envoyer</button>
            </form>
        </div>

        <p style="font-size: 0.7rem; line-height: 0.3;">Total fichiers : {{ $dossierFileCounts[$dossier->id] ?? 0 }}</p>
        <p style="font-size: 0.7rem; line-height: 0.3;">Pièces traitées : </p>
        <!-- <p style="font-size: 0.7rem; line-height: 0.3;">Pièces suspendues : </p> -->
    </div>
</div>

@endforeach


<!-- Input caché pour sélectionner les fichiers à fusionner -->
<input type="file" id="filesToMergeX" multiple style="display: none;" onchange="mergeFilesX(event)">
<script>
    // ...existing code...
let selectedFilesDossierDynamic = [];
let rotationsDossierDynamic = [];
let sortOrderDossierDynamic = 'asc';
let currentDossierDynamic = { id: null, name: null };

function handleUploadX(option, dossierId, dossierName) {
    currentDossierDynamic.id = dossierId;
    currentDossierDynamic.name = dossierName;
    if (option === 'importer') {
        document.getElementById('file-' + dossierId).click();
    } else if (option === 'scanner') {
        alert("Fonction de scan non implémentée.");
    } else if (option === 'fusionner') {
        document.getElementById('filesToMergeX').value = '';
        document.getElementById('filesToMergeX').click();
    }
}

// ...existing code...
document.getElementById('filesToMergeX').onchange = function(event) {
    const files = Array.from(event.target.files);
    if (!files.length) return;
    // Ajoute les nouveaux fichiers à la liste existante
    selectedFilesDossierDynamic.push(...files);
    rotationsDossierDynamic.push(...files.map(() => 0));
    if (selectedFilesDossierDynamic.length < 2) {
        alert("Veuillez sélectionner au moins deux fichiers.");
        return;
    }
    showModalDossierDynamic();
    populateFileListDossierDynamic();
};
// ...existing code...

function showModalDossierDynamic() {
    document.getElementById('fileOrderModalDossierDynamic').style.display = 'block';
}
function closeModalDossierDynamic() {
    document.getElementById('fileOrderModalDossierDynamic').style.display = 'none';
}
function addFileDossierDynamic() {
    document.getElementById('filesToMergeX').click();
}
function sortFilesDossierDynamic() {
    sortOrderDossierDynamic = sortOrderDossierDynamic === 'asc' ? 'desc' : 'asc';
    selectedFilesDossierDynamic.sort((a, b) => sortOrderDossierDynamic === 'asc' ? a.name.localeCompare(b.name) : b.name.localeCompare(a.name));
    populateFileListDossierDynamic();
    const sortButton = document.getElementById('sortButtonDossierDynamic');
    sortButton.innerHTML = sortOrderDossierDynamic === 'asc'
        ? '<span style="font-size: 20px;">&#8593;</span><div style="line-height: 1;"><div style="font-size: 9px;">Z</div><div style="font-size: 9px;">A</div></div>'
        : '<span style="font-size: 20px;">&#8595;</span><div style="line-height: 1;"><div style="font-size: 9px;">A</div><div style="font-size: 9px;">Z</div></div>';
    sortButton.style.backgroundColor = sortOrderDossierDynamic === 'asc' ? '#cb0c9f' : '#e74c3c';
}
function populateFileListDossierDynamic() {
    const fileList = document.getElementById('fileListDossierDynamic');
    fileList.innerHTML = '';
    selectedFilesDossierDynamic.forEach((file, index) => {
        const li = document.createElement('li');
        li.style.flexDirection = 'column';
        li.style.marginBottom = '15px';
        li.style.padding = '10px';
        li.style.marginLeft = '10px';
        li.style.backgroundColor = '#ffffff';
        li.style.border = '1px solid #ddd';
        li.style.borderRadius = '8px';
        li.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
        li.style.transition = 'box-shadow 0.2s';
        li.style.width = '23%';
        li.draggable = true;
        li.addEventListener('mouseover', () => li.style.boxShadow = '0 6px 14px rgba(0,0,0,0.1)');
        li.addEventListener('mouseout', () => li.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)');
        li.addEventListener('dragstart', (e) => e.dataTransfer.setData('text/plain', index));
        li.addEventListener('dragover', (e) => e.preventDefault());
        li.addEventListener('drop', (e) => {
            e.preventDefault();
            const fromIndex = e.dataTransfer.getData('text/plain');
            moveFileDossierDynamic(fromIndex, index);
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
            selectedFilesDossierDynamic.splice(index, 1);
            rotationsDossierDynamic.splice(index, 1);
            populateFileListDossierDynamic();
        };

        const rotateIcon = document.createElement('span');
        rotateIcon.textContent = '🔄';
        rotateIcon.title = 'Rotation';
        rotateIcon.style.cursor = 'pointer';
        rotateIcon.style.transition = 'color 0.2s';
        rotateIcon.onmouseover = () => rotateIcon.style.color = '#3498db';
        rotateIcon.onmouseout = () => rotateIcon.style.color = 'inherit';
        rotateIcon.onclick = () => {
            rotationsDossierDynamic[index] = (rotationsDossierDynamic[index] + 90) % 360;
            populateFileListDossierDynamic();
        };

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
                    // Rotation preview
                    ctx.clearRect(0, 0, preview.width, preview.height);
                    ctx.save();
                    ctx.translate(preview.width / 2, preview.height / 2);
                    ctx.rotate(rotationsDossierDynamic[index] * Math.PI / 180);
                    let scale = Math.min(preview.width / img.width, preview.height / img.height);
                    let width = img.width * scale;
                    let height = img.height * scale;
                    if (rotationsDossierDynamic[index] % 180 !== 0) [width, height] = [height, width];
                    ctx.drawImage(img, -width / 2, -height / 2, width, height);
                    ctx.restore();
                };
                img.src = URL.createObjectURL(file);
            }
        };
        reader.readAsArrayBuffer(file);

        li.appendChild(topRow);
        li.appendChild(preview);
        fileList.appendChild(li);
    });
}
function moveFileDossierDynamic(currentIndex, newIndex) {
    currentIndex = parseInt(currentIndex);
    if (newIndex < 0 || newIndex >= selectedFilesDossierDynamic.length || currentIndex === newIndex) return;
    const [moved] = selectedFilesDossierDynamic.splice(currentIndex, 1);
    const [rotMoved] = rotationsDossierDynamic.splice(currentIndex, 1);
    selectedFilesDossierDynamic.splice(newIndex, 0, moved);
    rotationsDossierDynamic.splice(newIndex, 0, rotMoved);
    populateFileListDossierDynamic();
}
async function confirmOrderDossierDynamic() {
    closeModalDossierDynamic();
    await mergeFilesDossierDynamic();
}
async function mergeFilesDossierDynamic() {
    try {
        const mergedPdf = await PDFLib.PDFDocument.create();
        for (let i = 0; i < selectedFilesDossierDynamic.length; i++) {
            const file = selectedFilesDossierDynamic[i];
            const rotation = rotationsDossierDynamic[i] || 0;
            const arrayBuffer = await file.arrayBuffer();
            const fileType = file.type;
            if (fileType === 'application/pdf') {
                const pdf = await PDFLib.PDFDocument.load(arrayBuffer);
                const copiedPages = await mergedPdf.copyPages(pdf, pdf.getPageIndices());
                copiedPages.forEach((page) => {
                    if (rotation !== 0) page.setRotation(PDFLib.degrees(rotation));
                    mergedPdf.addPage(page);
                });
            } else if (fileType.startsWith('image/')) {
                const pageWidth = 595.28, pageHeight = 841.89;
                let pageW = (rotation === 90 || rotation === 270) ? pageHeight : pageWidth;
                let pageH = (rotation === 90 || rotation === 270) ? pageWidth : pageHeight;
                const imageBytes = new Uint8Array(arrayBuffer);
                let image;
                if (fileType === 'image/png') image = await mergedPdf.embedPng(imageBytes);
                else if (fileType === 'image/jpeg' || fileType === 'image/jpg') image = await mergedPdf.embedJpg(imageBytes);
                else continue;
                const rotated = (rotation % 180) !== 0;
                const targetWidth = rotated ? pageH * 0.9 : pageW * 0.9;
                const scale = targetWidth / (rotated ? image.height : image.width);
                const targetHeight = (rotated ? image.width : image.height) * scale;
                const centerX = pageW / 2, centerY = pageH / 2;
                const imgX = - (rotated ? targetHeight : targetWidth) / 2;
                const imgY = - (rotated ? targetWidth : targetHeight) / 2;
                const page = mergedPdf.addPage([pageW, pageH]);
                page.pushOperators(
                    PDFLib.pushGraphicsState(),
                    PDFLib.translate(centerX, centerY),
                    PDFLib.rotateDegrees(rotation),
                );
                page.drawImage(image, {
                    x: imgX,
                    y: imgY,
                    width: rotated ? targetHeight : targetWidth,
                    height: rotated ? targetWidth : targetHeight,
                });
                page.pushOperators(PDFLib.popGraphicsState());
            }
        }
        const mergedPdfBytes = await mergedPdf.save();
        const blob = new Blob([mergedPdfBytes], { type: 'application/pdf' });
        const formData = new FormData();
        const fileNameBase = selectedFilesDossierDynamic.map(f => f.name.replace(/\.[^/.]+$/, '')).join('_');
        const safeFileName = fileNameBase.substring(0, 100).replace(/[^\w\-]/g, '_');
        formData.append('file', blob, `${safeFileName}_${Date.now()}.pdf`);
        formData.append('societe_id', '{{ session()->get('societeId') }}');
        formData.append('type', currentDossierDynamic.name);
        const response = await fetch('/uploadFusionner', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await response.json();
        if (data.success) {
            const messageDiv = document.createElement('div');
            messageDiv.textContent = "Fichiers fusionnés et envoyés avec succès !";
            Object.assign(messageDiv.style, {
                position: 'fixed',
                top: '150px',
                left: '50%',
                transform: 'translateX(-50%)',
                backgroundColor: '#4CAF50',
                color: 'white',
                padding: '10px 20px',
                borderRadius: '5px',
                zIndex: 9999
            });
            document.body.appendChild(messageDiv);
            setTimeout(() => location.reload(), 2000);
        } else {
            alert("Erreur lors de l'envoi des fichiers fusionnés.");
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert("Une erreur s'est produite lors de la fusion des fichiers.");
    }
}
</script>

<style>
  #fileOrderModalDossierDynamic {
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

  #fileOrderModalDossierDynamic h3 {
    margin-top: 0;
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
  }

  #fileOrderModalDossierDynamic ul {
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

  #fileOrderModalDossierDynamic ul li {
    /* padding: 20px; */
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 9px;
    display: inline-block;
    transition: background-color 0.2s;
  }

  #fileOrderModalDossierDynamic ul li:hover {
    background-color: #f1f1f1;
  }

  #fileOrderModalDossierDynamic span[onclick] {
    color: #007BFF;
    font-weight: 500;
    transition: color 0.2s;
  }

  #fileOrderModalDossierDynamic span[onclick]:hover {
    color: #0056b3;
  }

  #fileOrderModalDossierDynamic .action-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
  }

  #fileOrderModalDossierDynamic button {
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

  #fileOrderModalDossierDynamic button:hover {
    background-color: #0056b3;
  }

  #fileOrderModalDossierDynamic button:first-of-type {
    background-color: #6c757d;
  }

  #fileOrderModalDossierDynamic button:first-of-type:hover {
    background-color: #5a6268;
  }

  #fileOrderModalDossierDynamic > span {
    position: absolute;
    top: 15px;
    right: 20px;
    cursor: pointer;
    font-size: 24px;
    font-weight: bold;
    color: #999;
  }

  #fileOrderModalDossierDynamic > span:hover {
    color: #333;
  }
  .modal{
    background-color:black;
  }
</style>



    </div>
</div>
<!-- Modal pour créer un dossier -->
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createFolderModalLabel">Créer un Nouveau Dossier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('dossier.store') }}" method="POST" id="create-folder-form">
          @csrf
<input type="hidden" name="exercice_debut" value="{{ $societe->exercice_social_debut }}">
<input type="hidden" name="exercice_fin" value="{{ $societe->exercice_social_fin }}">


          <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
          <input type="hidden" name="color" id="color-value" />

          <!-- Ligne avec champ texte et color picker alignés -->
          <div class="d-flex align-items-center gap-2 mb-3">
            <!-- Champ texte -->
            <div class="flex-grow-1">
              <label for="folderName" class="form-label mb-0">Nom du Dossier</label>
              <input type="text" class="form-control" id="folderName" name="name" required>
            </div>

            <!-- Color picker -->
            <div style="width: 40px;">
              <label for="color-picker" class="form-label mb-0" style="font-size: 0.8rem;">Couleur</label>
              <div id="color-picker"></div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Créer</button>
          <button type="reset" class="btn btn-secondary ms-2">Réinitialiser</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- CSS pour Pickr + ajustement taille bouton couleur -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css"/>

<style>
  /* Ajuste la taille du bouton Pickr pour aligner avec input Bootstrap */
  #color-picker .pcr-button {
    height: 38px;  /* Hauteur champ input Bootstrap standard */
    width: 38px;
    border-radius: 4px;
  }

  /* Optionnel : réduire un peu la taille du label "Couleur" */
  label[for="color-picker"] {
    display: block;
    font-size: 0.8rem;
    margin-bottom: 4px;
  }
</style>

<!-- JS Pickr -->
<script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr"></script>
<script>
  const pickr = Pickr.create({
    el: '#color-picker',
    theme: 'classic',
    default: null,
    components: {
      preview: true,
      opacity: true,
      hue: true,
      interaction: {
        hex: true,
        rgba: true,
        input: true,
        save: true
      }
    }
  });

  pickr.on('change', (color) => {
    const hexColor = color.toHEXA().toString();
    document.getElementById('color-value').value = hexColor;
  });

  pickr.on('save', (color) => {
    const hexColor = color.toHEXA().toString();
    document.getElementById('color-value').value = hexColor;
  });
</script>






<!-- Modal pour modifier un dossier -->
<div class="modal fade" id="editFolderModal" tabindex="-1" aria-labelledby="editFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFolderModalLabel">Renommer Dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="edit-folder-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                    <input type="hidden" name="dossier_id" id="dossier_id">
                    <div class="mb-3">
                        <label for="folderName1" class="form-label">Nom du Dossier</label>
                        <input type="text" class="form-control" id="folderName1" name="name" placeholder="{{ $dossier->name ?? 'Nom du dossier' }}" required>
                    </div>
                    <button type="submit" class="btn" style="background-color:#007bff; color: white;">Renommer Dossier</button>
                    <button type="reset" class="btn btn-secondary" style="margin-left: 10px;">Réinitialiser</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <!-- <h6>Graphiques des Transactions</h6> -->
    <div class="row">
        <div class="col-md-3">
            <canvas id="achatChart" width="200" height="200"></canvas>
        </div>
        <div class="col-md-3">
            <canvas id="venteChart" width="200" height="200"></canvas>
        </div>
        <div class="col-md-3">
            <canvas id="banqueChart" width="200" height="200"></canvas>
        </div>
        <div class="col-md-3">
            <canvas id="caisseChart" width="200" height="200"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const commonOptions = {
        responsive: true,
        elements: {
            line: {
                tension: 0.4,
                borderWidth: 3,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
            }
        },
        plugins: {
            tooltip: {
                enabled: true,
                backgroundColor: 'rgba(0, 0, 0, 0.7)',
                titleColor: '#fff',
                bodyColor: '#fff',
            }
        },
        scales: {
            x: {
                grid: {
                    display: false,
                },
            },
            y: {
                type: 'linear',
                beginAtZero: true,
                min: 0,
                max: 5000,
                ticks: {
                    stepSize: 500,
                    font: {
                        size: 14,
                        family: 'Arial',
                        weight: 'lighter',
                    },
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)',
                    lineWidth: 1,
                },
            },
        },
    };

    const ctxAchat = document.getElementById('achatChart').getContext('2d');
    const achatChart = new Chart(ctxAchat, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            datasets: [{
                label: 'Achat',
                data: [2000, 1500, 1800, 2200, 2400, 2100],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 3,
                fill: true,
                pointRadius: 0,
            }]
        },
        options: commonOptions
    });

    const ctxVente = document.getElementById('venteChart').getContext('2d');
    const venteChart = new Chart(ctxVente, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            datasets: [{
                label: 'Vente',
                data: [1500, 1700, 1600, 1800, 2000, 2300],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 3,
                fill: true,
                pointRadius: 0,
            }]
        },
        options: commonOptions
    });

    const ctxBanque = document.getElementById('banqueChart').getContext('2d');
    const banqueChart = new Chart(ctxBanque, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            datasets: [{
                label: 'Banque',
                data: [1000, 1200, 1100, 1300, 1400, 1500],
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 3,
                fill: true,
                pointRadius: 0,
            }]
        },
        options: commonOptions
    });

    const ctxCaisse = document.getElementById('caisseChart').getContext('2d');
    const caisseChart = new Chart(ctxCaisse, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            datasets: [{
                label: 'Caisse',
                data: [500, 700, 600, 800, 900, 1000],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 3,
                fill: true,
                pointRadius: 0,
            }]
        },
        options: commonOptions
    });
});

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('achat-div').addEventListener('dblclick', function () {
        window.location.href = '{{ route("achat.view") }}';
    });

 document.getElementById('vente-div').addEventListener('dblclick', function () {
    var societeId = {{ session('societeId') }};
    window.location.href = '{{ route("vente.view") }}' + '?societeId=' + societeId;
});

    document.getElementById('banque-div').addEventListener('dblclick', function () {
        window.location.href = '{{ route("banque.view") }}';
    });

    document.getElementById('caisse-div').addEventListener('dblclick', function () {
        window.location.href = '{{ route("etat_de_caisse") }}';
    });

    document.getElementById('impot-div').addEventListener('dblclick', function () {
        window.location.href = '{{ route("impot.view") }}';
    });

    document.getElementById('paie-div').addEventListener('dblclick', function () {
        window.location.href = '{{ route("paie.view") }}';
    });

    document.getElementById('FusionDocuments-div').addEventListener('dblclick', function () {
        window.location.href = '{{ route("Dossier_permanant.view") }}';
    });
});

function handleFileSelect(event, type) {
    const fileInput = document.getElementById(`file-${type.toLowerCase()}`);
    const formId = `form-${type.toLowerCase()}`;

    if (!fileInput.files.length) {
        alert("Veuillez sélectionner un ou plusieurs fichiers.");
        return;
    }

    const fileNames = Array.from(fileInput.files).map(file => file.name).join(', ');
    console.log(`Fichiers sélectionnés pour ${type}: ${fileNames}`);

    document.getElementById(formId).submit();
}

function openCreateFolderForm() {
    var myModal = new bootstrap.Modal(document.getElementById('createFolderModal'));
    myModal.show();
    document.getElementById('createFolderModal').addEventListener('shown.bs.modal', function () {
        document.getElementById('folderName').focus();
        
        // Ajouter une sélection de couleur
        // var colorPicker = document.getElementById('color-picker');
        // if (!colorPicker) {
        //     colorPicker = document.createElement('input');
        //     colorPicker.type = 'color';
        //     colorPicker.id = 'color-picker';
        //     colorPicker.value = '#007bff'; // Défaut
        //     document.getElementById('createFolderModal').querySelector('.modal-body').appendChild(colorPicker);
        // }
    });
}


document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.dossier-box').forEach(function(div) {
        div.addEventListener('dblclick', function () {
            const dossierId = div.getAttribute('data-id');
            window.location.href = `/Douvrir/${dossierId}`;
        });
    });
});
</script>

<script src="{{ asset('js/exercices.js') }}"></script>

@endsection