<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Liens pour les scripts et styles -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styles généraux */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            color: #1a73e8;
            margin-bottom: 20px;
        }

        /* Styles des boutons */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
            margin: 5px;
        }

        .btn-primary {
            background-color: #1a73e8;
        }

        .btn-primary:hover {
            background-color: #0c59b3;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #bdbdbd;
            transform: translateY(-2px);
        }

        /* Amélioration de la boîte de chat */
        .chat-box {
            position: fixed;
            right: 20px;
            top: -5px;
            width: 350px;
            height: 100%;
            border: 1px solid #ddd;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
             overflow-y: auto;
            z-index: 999;
        }

        .chat-box h5 {
            font-size: 20px;
            color: #1a73e8;
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
            margin-bottom: 10px;
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

        /* Styles des messages */
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

        /* Pour les fichiers téléchargés */
        .file-preview {
            text-align: center;
            margin: 20px 0;
        }
        .file-preview img {
            max-width: 100%;
            border-radius: 8px;
        }

        /* Navigation entre les fichiers */
        .navigation-container {
            display: flex;
            justify-content: center; /* Centre les éléments horizontalement */
            width: 100%;
            padding: 10px;
        }

        .navigation-container a {
            text-decoration: none;
            color: #1a73e8;
            font-size: 24px;
            transition: color 0.3s;
        }

        .navigation-container a:hover {
            color: #0c59b3;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Affichage du fichier selon son type -->
    @if(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'pdf')
    <div style="margin-left:600px;">
        <a href="{{ asset($file->path) }}" class="btn btn-primary mt-3" download>
            <i class="fas fa-download" title="Télécharger"></i>
        </a>

        <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="printPDF('{{ asset($file->path) }}')" title="Imprimer">
            <i class="fas fa-print"></i>
        </a>
        <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="closeFile()" title="Fermer">
        <i class="fas fa-times"></i>
          </a>
          <script>
       function closeFile() {
    var societeId = "{{ session('societeId') }}"; // Récupérer l'ID de la société depuis la session
    window.location.href = '/exercices/' + societeId; // Remplacez '/societe/' par l'URL de votre choix
}
          </script>
    </div>
        <div id="pdf-preview-{{ $file->id }}" class="pdf-preview" style="overflow: hidden; width: 70%;"></div>
        <div id="page-num-{{ $file->id }}" class="page-num" style="text-align: center; margin-top: 10px;"></div>
 
 <script>
            var url = "{{ asset($file->path) }}";  <!-- Modification ici -->
            var container = document.getElementById('pdf-preview-{{ $file->id }}');
            var pageNumDiv = document.getElementById('page-num-{{ $file->id }}'); // Pour afficher le numéro de page

            pdfjsLib.getDocument(url).promise.then(function(pdf) {
                var totalPages = pdf.numPages;
                var currentPage = 1;

                // Fonction pour afficher une page
                function renderPage(pageNum) {
                    container.innerHTML = '';
                    pdf.getPage(pageNum).then(function(page) {
                        var canvas = document.createElement('canvas');
                        container.appendChild(canvas);

                        var context = canvas.getContext('2d');
                        var scale = 0.9;
                        var viewport = page.getViewport({ scale: scale });

                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        page.render({ canvasContext: context, viewport: viewport });

                        // Mettre à jour la numérotation de la page
                        pageNumDiv.textContent = 'Page ' + currentPage + ' sur ' + totalPages;
                    });
                }

                // Afficher la première page
                renderPage(currentPage);

                // Navigation entre les pages avec la souris
                container.addEventListener('wheel', function(event) {
                    if (event.deltaY > 0) {
                        // Défilement vers le bas
                        if (currentPage < totalPages) {
                            currentPage++;
                            renderPage(currentPage);
                        }
                    } else {
                        // Défilement vers le haut
                        if (currentPage > 1) {
                            currentPage--;
                            renderPage(currentPage);
                        }
                    }
                    event.preventDefault(); // Empêche le défilement de la page
                });
            });

            function printPDF(url) {
                var printWindow = window.open(url, '_blank');
                printWindow.onload = function() {
                    printWindow.print();
                };
            }
        </script>

    @elseif(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'xlsx' || strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'xls')
    <div style="margin-left:600px;">  
    <a href="{{ asset($file->path) }}" class="btn btn-primary mt-3" download>
            <i class="fas fa-download" title="Télécharger"></i>
        </a>

        <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="printExcel('{{ asset($file->path) }}')" title="Imprimer">
            <i class="fas fa-print"></i>
        </a>
        <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="closeFile()" title="Fermer">
        <i class="fas fa-times"></i>
          </a>
          <script>
       function closeFile() {
    var societeId = "{{ session('societeId') }}"; // Récupérer l'ID de la société depuis la session
    window.location.href = '/exercices/' + societeId; // Remplacez '/societe/' par l'URL de votre choix
}
          </script>
        </div>
        <div id="excel-preview-{{ $file->id }}" class="excel-preview" style="overflow: clip;"></div>

        <script>
            var fileUrl = "{{ asset($file->path) }}";  <!-- Modification ici -->
            var xhr = new XMLHttpRequest();
            xhr.open('GET', fileUrl, true);
            xhr.responseType = 'arraybuffer';
            xhr.onload = function() {
                var data = xhr.response;
                var workbook = XLSX.read(data, { type: 'array' });
                var sheet = workbook.Sheets[workbook.SheetNames[0]];
                var htmlString = XLSX.utils.sheet_to_html(sheet);
                document.getElementById('excel-preview-{{ $file->id }}').innerHTML = htmlString;
            };
            xhr.send();

            function printExcel(fileUrl) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', fileUrl, true);
                xhr.responseType = 'arraybuffer';
                xhr.onload = function() {
                    var data = xhr.response;
                    var workbook = XLSX.read(data, { type: 'array' });
                    var sheet = workbook.Sheets[workbook.SheetNames[0]];
                    var htmlString = XLSX.utils.sheet_to_html(sheet);
                    var printWindow = window.open('', '_blank');
                    printWindow.document.write('<html><head><title>Impression</title></head><body>');
                    printWindow.document.write(htmlString);
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                    printWindow.print();
                };
                xhr.send();
            }
        </script>
@elseif(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'docx' || strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'doc')
<div style="margin-left:600px;">  
<a href="{{ asset($file->path) }}" class="btn btn-primary mt-3" download>
        <i class="fas fa-download" title="Télécharger"></i>
    </a>

    <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="printWord('{{ asset($file->path) }}')" title="Imprimer">
        <i class="fas fa-print"></i>
    </a>
    <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="closeFile()" title="Fermer">
        <i class="fas fa-times"></i>
          </a>
          <script>
       function closeFile() {
    var societeId = "{{ session('societeId') }}"; // Récupérer l'ID de la société depuis la session
    window.location.href = '/exercices/' + societeId; // Remplacez '/societe/' par l'URL de votre choix
}
          </script>
    </div>
    <div id="word-preview-{{ $file->id }}" class="word-preview" style="overflow: auto; width: 100%; margin: 0 auto;"></div>
    
    <script>
        var fileUrl = "{{ asset($file->path) }}";  <!-- Modification ici -->
        var xhr = new XMLHttpRequest();
        xhr.open('GET', fileUrl, true);
        xhr.responseType = 'arraybuffer';
        xhr.onload = function() {
            var data = xhr.response;
            mammoth.convertToHtml({ arrayBuffer: data }).then(function(result) {
                var previewContainer = document.getElementById('word-preview-{{ $file->id }}');
                previewContainer.innerHTML = result.value;

                // Réduire l'échelle de l'affichage pour que tout le document soit visible
                previewContainer.style.transform = 'scale(0.2)'; // Réduit à 50% de la taille originale
                previewContainer.style.transformOrigin = 'top left'; // Le point de référence pour l'échelle est le coin supérieur gauche
                previewContainer.style.width = '250%'; // Augmente la largeur pour éviter la coupure du contenu après l'échelle
            }).catch(function(err) {
                console.log("Erreur lors de la conversion du fichier Word:", err);
            });
        };
        xhr.send();
        
        function printWord(url) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.responseType = 'arraybuffer';
            xhr.onload = function() {
                var data = xhr.response;
                mammoth.convertToHtml({ arrayBuffer: data }).then(function(result) {
                    var printWindow = window.open('', '_blank');
                    printWindow.document.write('<html><head><title>Impression</title></head><body>');
                    printWindow.document.write(result.value);
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                    printWindow.print();
                }).catch(function(err) {
                    console.log("Erreur lors de la conversion du fichier Word:", err);
                });
            };
            xhr.send();
        }
    </script>

@else
    <div style="margin-left:600px;">
        <a href="{{ asset($file->path) }}" class="btn btn-primary mt-3" download>
            <i class="fas fa-download" title="Télécharger"></i>
        </a>
        <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="printImage('{{ asset($file->path) }}')" title="Imprimer">
            <i class="fas fa-print"></i>
        </a>
        <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="closeFile()" title="Fermer">
        <i class="fas fa-times"></i>
          </a>
          <script>
       function closeFile() {
    var societeId = "{{ session('societeId') }}"; // Récupérer l'ID de la société depuis la session
    window.location.href = '/exercices/' + societeId; // Remplacez '/societe/' par l'URL de votre choix
}
          </script>
        </div>
        <!-- Ajout des boutons de zoom pour l'image -->
        <div class="image-zoom-controls">
            <button id="zoom-out" class="btn btn-secondary" title="Zoom arrière">
                <i class="fas fa-minus"></i>
            </button>
            <button id="zoom-in" class="btn btn-secondary" title="Zoom avant">
                <i class="fas fa-plus"></i>
            </button>
        </div>

        <img id="image-preview" src="{{ asset($file->path) }}" alt="{{ $file->name }}" class="img-fluid mb-2" style="height: auto; width: 50%; transform: scale(1); transition: transform 0.3s;">
    @endif

    <!-- Boîte de communication -->
    <div class="chat-box">
        <h5>Communication</h5>
        <div id="messages-container" style="max-height: 650px; overflow-y: auto;">
            <!-- Les messages seront affichés ici -->
        </div>

        <form action="{{ route('messages.store') }}" method="POST" >
            @csrf  
            <textarea id="message_text" name="text_message" placeholder="Écrivez ici..." style="width:325px;"></textarea>
            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
            <input type="hidden" name="file_id" value="{{ $file->id ?? 'null' }}">
            <input type="hidden" name="societe_id" value="{{ session('societeId') }}">
            <button type="submit">Envoyer</button>
        </form>
    </div>

    <!-- Navigation entre les fichiers -->
    <div class="navigation-container" style="margin-left:-198px;">
        @if($currentFileIndex > 0)
            <a href="{{ route('achat.views', ['fileId' => $files[$currentFileIndex - 1]->id]) }}" class="btn btn-secondary">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif
        <button id="zoom-out" class="btn btn-secondary" title="Zoom arrière">
            <i class="fas fa-minus"></i>
        </button>
        <button id="zoom-in" class="btn btn-secondary" title="Zoom avant">
            <i class="fas fa-plus"></i>
        </button>
       
        @if($currentFileIndex < count($files) - 1)
            <a href="{{ route('achat.views', ['fileId' => $files[$currentFileIndex + 1]->id]) }}" class="btn btn-secondary">
                <i class="fas fa-chevron-right"></i>
            </a>
        @endif
    </div>
    
</div>

<script>
    var zoomLevel = 1; // Niveau de zoom initial

    // Fonction pour zoomer
    function zoomIn() {
        zoomLevel += 0.1; // Augmente le niveau de zoom
        updateZoom();
    }

    // Fonction pour dézoomer
    function zoomOut() {
        if (zoomLevel > 0.2) { // Limite le zoom arrière
            zoomLevel -= 0.1; // Diminue le niveau de zoom
            updateZoom();
        }
    }

    // Fonction pour mettre à jour le zoom
    function updateZoom() {
        // Applique le zoom sur le contenu à l'intérieur des conteneurs
        var pdfContainer = document.getElementById('pdf-preview-{{ $file->id }}');
        if (pdfContainer) {
            pdfContainer.firstChild.style.transform = 'scale(' + zoomLevel + ')'; // Applique le zoom au contenu
            pdfContainer.firstChild.style.transformOrigin = 'top left'; // Point d'origine pour le zoom
        }

        var excelContainer = document.getElementById('excel-preview-{{ $file->id }}');
        if (excelContainer) {
            excelContainer.firstChild.style.transform = 'scale(' + zoomLevel + ')'; // Applique le zoom au contenu
            excelContainer.firstChild.style.transformOrigin = 'top left';
        }

        var wordContainer = document.getElementById('word-preview-{{ $file->id }}');
        if (wordContainer) {
            wordContainer.firstChild.style.transform = 'scale(' + zoomLevel + ')'; // Applique le zoom au contenu
            wordContainer.firstChild.style.transformOrigin = 'top left';
        }

        // Appliquer le zoom à l'image
        var imagePreview = document.getElementById('image-preview');
        if (imagePreview) {
            imagePreview.style.transform = 'scale(' + zoomLevel + ')'; // Applique le zoom à l'image
            imagePreview.style.transformOrigin = 'top left'; // Point d'origine pour le zoom
        }
    }

    // Ajoutez les événements pour les boutons de zoom
    document.getElementById('zoom-in').addEventListener('click', zoomIn);
    document.getElementById('zoom-out').addEventListener('click', zoomOut);

    window.onload = function() {
        var fileId = "{{ $file->id }}"; 

        fetch(`/messages/getMessages?file_id=${fileId}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages) {
                    const messagesContainer = document.getElementById("messages-container");
                    messagesContainer.innerHTML = '';

                    data.messages.forEach(function(message) {
                        var messageDiv = document.createElement("div");
                        messageDiv.classList.add("message");

                        // Création du message
                        var userMessage = document.createElement("p");
                        userMessage.innerHTML = `<strong>${message.user_name}:</strong><i>Posté le: ${message.created_at}</i><br>${message.text_message}`;

                        // Actions du message
                        var actionsDiv = document.createElement("div");
                        actionsDiv.style.display = "flex"; 
                        actionsDiv.style.alignItems = "center"; 
                        actionsDiv.style.gap = "10px"; 

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

                        // Bouton de modification
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
                        
                        // Icône de lecture
                        var icon = document.createElement("i");
                        icon.classList.add("fas", message.is_read ? "fa-envelope-open" : "fa-envelope");
                        icon.style.cursor = "pointer";
                        icon.style.fontSize = "15px";
                        icon.style.color = message.is_read ? "#28a745" : "#e74a3b";
                        icon.title = message.is_read ? "Marquer comme non lue" : "Marquer comme lue";
                     
                        icon.addEventListener("click", function() {
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
                                    icon.classList.replace("fa-envelope", "fa-envelope-open");
                                    icon.style.color = "#28a745"; // Change la couleur pour indiquer que le message est lu
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

                        actionsDiv.appendChild(replyButton);
                        actionsDiv.appendChild(editButton);
                        actionsDiv.appendChild(icon);
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
                                replyDiv.innerHTML = `<p><strong>${reply.user_name}:</strong><i>Posté le: ${reply.created_at}</i><br>${reply.text_message}</p>`;
                                
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
                                markAsReadButton.innerHTML = '<i class="fas fa-envelope" title="Marquer comme lue" style="cursor: pointer; font-size: 15px; color: rgb(231, 74, 59);"></i>';
                                markAsReadButton.style = "background: none; border: none; cursor: pointer; color: #28a745;";
                                markAsReadButton.addEventListener("click", function() {
                                    fetch(`/messages/read/${reply.id}`)
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                alert("Réponse marquée comme lue");
                                                // Mise à jour de l'icône
                                                replyDiv.querySelector("i").classList.replace("fa-envelope", "fa-envelope-open");
                                            }
                                        })
                                        .catch(error => console.error("Erreur lors du marquage comme lu de la réponse:", error));
                                });

                                replyActionsDiv.appendChild(editReplyButton);
                                replyActionsDiv.appendChild(deleteReplyButton);
                                replyActionsDiv.appendChild(markAsReadButton); // Ajouter le bouton "Marquer comme lu"

                                replyDiv.appendChild(replyActionsDiv);
                                repliesDiv.appendChild(replyDiv);
                            });
                            messageDiv.appendChild(repliesDiv);
                        }
                    });
                }
            })
            .catch(error => console.error("Erreur lors du chargement des messages:", error));
    };
</script>

</body>
</html>