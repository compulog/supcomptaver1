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
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
        }

        /* Amélioration de la boîte de chat */
        .chat-box {
            position: fixed;
            right: 20px;
            top: 50px;
            width: 350px;
            height: 70%;
            border: 1px solid #ddd;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow-y: auto;
            z-index: 999;
        }

        .chat-box h5 {
            font-size: 20px;
            color: #007bff;
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
            border-color: #007bff;
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
            color: #007bff;
            font-size: 16px;
            transition: color 0.3s;
        }

        .message-actions button:hover {
            color: #0056b3;
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


    </style>
</head>
<body>

<!-- Affichage du fichier selon son type -->
@if(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'pdf')
    <a href="{{ Storage::url($file->path) }}" class="btn btn-primary mt-3" style="margin-left:800px;" download>
        <i class="fas fa-download" title="Télécharger"></i> 
    </a>
    
    <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="printPDF('{{ Storage::url($file->path) }}')" title="Imprimer">
        <i class="fas fa-print"></i>
    </a>

    <div id="pdf-preview-{{ $file->id }}" class="pdf-preview" style="overflow: hidden; width: 70%; margin: 0 auto;"></div>
    
    <script>
        var url = "{{ Storage::url($file->path) }}";
        var container = document.getElementById('pdf-preview-{{ $file->id }}');

        pdfjsLib.getDocument(url).promise.then(function(pdf) {
            var totalPages = pdf.numPages;
            for (var pageNum = 1; pageNum <= totalPages; pageNum++) {
                pdf.getPage(pageNum).then(function(page) {
                    var canvas = document.createElement('canvas');
                    container.appendChild(canvas);

                    var context = canvas.getContext('2d');
                    var scale = 0.7;
                    var viewport = page.getViewport({ scale: scale });

                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    page.render({ canvasContext: context, viewport: viewport });
                });
            }
        });

        function printPDF(url) {
            var printWindow = window.open(url, '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        }
    </script>
@elseif(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'xlsx' || strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'xls')
    <a href="{{ Storage::url($file->path) }}" class="btn btn-primary mt-3" style="margin-left:800px;" download>
        <i class="fas fa-download" title="Télécharger"></i> 
    </a>   

    <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="printExcel('{{ Storage::url($file->path) }}')" title="Imprimer">
        <i class="fas fa-print"></i>
    </a>

    <div id="excel-preview-{{ $file->id }}" class="excel-preview" style="overflow: clip;"></div>
    
    <script>
        var fileUrl = "{{ Storage::url($file->path) }}";
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
    <a href="{{ Storage::url($file->path) }}" class="btn btn-primary mt-3" download>
        <i class="fas fa-download" title="Télécharger"></i> 
    </a>   

    <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="printWord('{{ Storage::url($file->path) }}')" title="Imprimer">
        <i class="fas fa-print"></i>
    </a>

    <div id="word-preview-{{ $file->id }}" class="word-preview" ></div>
    
    <script>
        var fileUrl = "{{ Storage::url($file->path) }}";
        var xhr = new XMLHttpRequest();
        xhr.open('GET', fileUrl, true);
        xhr.responseType = 'arraybuffer';
        xhr.onload = function() {
            var data = xhr.response;
            mammoth.convertToHtml({ arrayBuffer: data }).then(function(result) {
                document.getElementById('word-preview-{{ $file->id }}').innerHTML = result.value;
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
    <a href="{{ Storage::url($file->path) }}" class="btn btn-primary mt-3" download>
        <i class="fas fa-download" title="Télécharger"></i> 
    </a>
    <a href="javascript:void(0);" class="btn btn-secondary mt-3" onclick="printImage('{{ Storage::url($file->path) }}')" title="Imprimer">
        <i class="fas fa-print"></i>
    </a>

    <img src="{{ Storage::url($file->path) }}" alt="{{ $file->name }}" class="img-fluid mb-2" style="overflow-clip-margin: content-box; overflow: clip; height: 50%; width: 50%">
@endif

<!-- Boîte de communication -->
<div class="chat-box">
    <h5>Communication</h5>
    <div id="messages-container" style="max-height: 400px; overflow-y: auto;">
        <!-- Les messages seront affichés ici -->
    </div>

    <form action="{{ route('messages.store') }}" method="POST">
        @csrf  
        <textarea id="message_text" name="text_message" placeholder="Écrivez ici..."></textarea>
        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
        <input type="hidden" name="file_id" value="{{ $file->id ?? 'null' }}">
        <input type="hidden" name="societe_id" value="{{ session('societeId') }}">
        <button type="submit">Envoyer</button>
    </form>
</div>


<!-- Navigation entre les fichiers -->
<div class="navigation-buttons">
        <a href="#" title="Fichier Précédent">
            <i class="fas fa-chevron-left" style="font-size: 24px; color: #007bff; cursor: pointer;"></i>
        </a>
        
        <a href="#" title="Fichier Suivant">
            <i class="fas fa-chevron-right" style="font-size: 24px; color: #007bff; cursor: pointer;"></i>
        </a>
</div>

<script>
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
                    replyButton.style = "background: none; border: none; cursor: pointer; color: #007bff;";
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
                        fetch(`/messages/read/${message.id}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    icon.classList.replace("fa-envelope", "fa-envelope-open");
                                    icon.style.color = "#28a745";
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
                    // Afficher les réponses
if (message.replies.length > 0) {
    var repliesDiv = document.createElement("div");
    repliesDiv.style.marginLeft = "20px"; // Décalage pour les réponses
    message.replies.forEach(function(reply) {
        var replyDiv = document.createElement("div");
        replyDiv.classList.add("message");

        // Affichage du message de la réponse
        replyDiv.innerHTML = `<p><strong>${reply.user_name}:</strong><i>Posté le: ${reply.created_at}</i><br>${reply.text_message}</p>`;
        
        // Afficher la date de la réponse
        // var replyDate = document.createElement("p");
        // replyDate.innerHTML = `<i>Posté le: ${reply.created_at}</i>`; // Afficher la date de la réponse
        // replyDiv.appendChild(replyDate); // Ajouter la date après le texte du message

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
