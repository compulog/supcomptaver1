<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Ajout de FontAwesome pour l'icône de téléchargement -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

@if(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'pdf')
    <!-- Bouton de téléchargement -->
    <a href="{{ Storage::url($file->path) }}" class="btn btn-primary mt-3" style="margin-left:800px;" download>
        <i class="fas fa-download"></i> 
    </a>

    <!-- Conteneur pour toutes les pages du PDF -->
    <div id="pdf-preview-{{ $file->id }}" class="pdf-preview" style="overflow: hidden; width: 70%; margin: 0 auto;"></div>
    
    <script>
        var url = "{{ Storage::url($file->path) }}"; // Utilisez le chemin du fichier
        var container = document.getElementById('pdf-preview-{{ $file->id }}');

        // Charger le document PDF
        pdfjsLib.getDocument(url).promise.then(function(pdf) {
            var totalPages = pdf.numPages;
            for (var pageNum = 1; pageNum <= totalPages; pageNum++) {
                pdf.getPage(pageNum).then(function(page) {
                    var canvas = document.createElement('canvas');
                    container.appendChild(canvas);

                    var context = canvas.getContext('2d');
                    var scale = 0.7; // Ajuste la taille du PDF
                    var viewport = page.getViewport({ scale: scale });

                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    page.render({ canvasContext: context, viewport: viewport });
                });
            }
        });
    </script>

    <!-- Bouton pour marquer comme lu -->
    <!-- <button onclick="markAsRead({{ $file->id }})" class="btn btn-warning" style="margin-top: 20px;">
        Marquer comme lu
    </button> -->

@elseif(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'xlsx' || strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'xls')
    <!-- Bouton de téléchargement -->
    <a href="{{ Storage::url($file->path) }}" class="btn btn-primary mt-3" style="margin-left:800px;" download>
        <i class="fas fa-download"></i> 
    </a>   

    <!-- Conteneur pour l'aperçu Excel -->
    <div id="excel-preview-{{ $file->id }}" class="excel-preview" style="overflow: clip;"></div>

    <script>
        var fileUrl = "{{ Storage::url($file->path) }}"; // Utilisez le chemin du fichier
        
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
    </script>

    <!-- Bouton pour marquer comme lu -->
    <!-- <button onclick="markAsRead({{ $file->id }})" class="btn btn-warning" style="margin-top: 20px;">
        Marquer comme lu
    </button> -->

@elseif(strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'docx' || strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) == 'doc')
    <!-- Bouton de téléchargement -->
    <a href="{{ Storage::url($file->path) }}" class="btn btn-primary mt-3" download>
        <i class="fas fa-download"></i> 
    </a>   

    <!-- Conteneur pour l'aperçu Word -->
    <div id="word-preview-{{ $file->id }}" class="word-preview" style="overflow: clip;"></div>

    <script>
        var fileUrl = "{{ Storage::url($file->path) }}"; // Utilisez le chemin du fichier

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
    </script>

    <!-- Bouton pour marquer comme lu -->
    <!-- <button onclick="markAsRead({{ $file->id }})" class="btn btn-warning" style="margin-top: 20px;">
        Marquer comme lu
    </button> -->

@else
    <a href="{{ Storage::url($file->path) }}" class="btn btn-primary mt-3" download>
        <i class="fas fa-download"></i> 
    </a>
    <!-- Affichage de l'aperçu pour les autres types de fichiers -->
    <img src="{{ Storage::url($file->path) }}" alt="{{ $file->name }}" class="img-fluid mb-2" style="overflow-clip-margin: content-box; overflow: clip; height: 50%; width: 50%">
@endif
<!-- Boîte de communication ajoutée à droite -->
<div style="position: fixed; right: 10px; top: 50px; width: 300px; height: 100%; border: 1px solid #ccc; background-color: #f9f9f9; padding: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
    <h5>Communication</h5>
    <!-- Conteneur pour afficher les messages -->
    <div id="messages-container" style="margin-top: 20px; max-height: 400px; overflow-y: auto;">
        <!-- Les messages seront affichés ici -->
    </div>

    <form action="{{ route('messages.store') }}" method="POST">
        @csrf  <!-- Token CSRF pour la sécurité -->
        <textarea id="message_text" name="text_message" style="width: 100%; height: 80px; border: 1px solid #ddd;" placeholder="Écrivez ici..."></textarea>
        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
        <input type="hidden" name="file_id" value="{{ $file->id ?? 'null' }}">
        <input type="hidden" name="societe_id" value="{{ session('societeId') }}">
        <button type="submit" style="width: 100%; padding: 10px; margin-top: 5px; background-color: #007bff; color: white; border: none;">
            Envoyer
        </button>
    </form>
</div>

<script>
    // Fonction pour marquer un message comme "lu" via AJAX
    function markAsRead(messageId) {
        fetch(`/messages/updateStatus/${messageId}`, {
            method: 'POST', // Utiliser POST pour la mise à jour
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ is_read: 1 }) // Envoyer la valeur "1" pour marquer comme lu
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Message marqué comme lu avec succès.");
                var button = document.querySelector(`button[data-message-id="${messageId}"]`);
                button.textContent = "Marquer comme non lu"; // Change le texte du bouton
                button.classList.remove("btn-warning"); // Retirer la classe "non lu"
                button.classList.add("btn-success"); // Ajouter la classe "lu"
            } else {
                alert("Erreur lors de la mise à jour du message.");
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert("Une erreur s'est produite.");
        });
    }

    window.onload = function() {
        var fileId = "{{ $file->id }}"; // Utiliser l'ID du fichier pour récupérer les messages

        fetch(`/messages/getMessages?file_id=${fileId}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages) {
                    const messagesContainer = document.getElementById("messages-container");

                    // Vider le conteneur des anciens messages
                    messagesContainer.innerHTML = '';

                    // Parcourir les messages et les ajouter à la page
                    data.messages.forEach(function(message) {
                        var messageDiv = document.createElement("div");
                        messageDiv.classList.add("message");

                        var userMessage = document.createElement("p");
                        userMessage.textContent = message.user_name + ": " + message.text_message;

                        // Créer un bouton pour marquer le message comme "lu" ou "non lu"
                        var button = document.createElement("button");
                        button.textContent = message.is_read ? "Marquer comme non lu" : "Marquer comme lu";
                        button.classList.add("btn", message.is_read ? "btn-success" : "btn-warning");
                        button.setAttribute("data-message-id", message.id); // Ajouter un attribut personnalisé avec l'ID du message

                        // Ajouter un événement pour mettre à jour le statut de lecture via AJAX
                        button.addEventListener("click", function() {
                            var messageId = button.getAttribute("data-message-id");
                            var isRead = !message.is_read; // Inverser le statut de lecture

                            fetch(`/messages/updateStatus/${messageId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ is_read: isRead })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    message.is_read = isRead;
                                    button.textContent = isRead ? "Marquer comme non lu" : "Marquer comme lu";
                                    button.classList.toggle("btn-success");
                                    button.classList.toggle("btn-warning");
                                    location.reload();
                                } else {
                                    console.log("Erreur lors de la mise à jour du statut");
                                }
                            })
                            .catch(error => console.error("Erreur lors de la mise à jour du statut:", error));
                        });

                        // Ajouter des icônes pour modifier, supprimer, répondre
                        var actionsDiv = document.createElement("div");
                        actionsDiv.classList.add("message-actions");

                        // Modifier - Icône d'édition
                        var editButton = document.createElement("button");
                        editButton.innerHTML = '<i class="fas fa-edit"></i>'; // Icône de modification
                        actionsDiv.appendChild(editButton);

                        // Supprimer - Icône de suppression
                        var deleteButton = document.createElement("button");
                        deleteButton.innerHTML = '<i class="fas fa-trash"></i>'; // Icône de suppression
                        actionsDiv.appendChild(deleteButton);

                        // Répondre - Icône de réponse
                        var replyButton = document.createElement("button");
                        replyButton.innerHTML = '<i class="fas fa-reply"></i>'; // Icône de réponse
                        actionsDiv.appendChild(replyButton);

                        // Ajouter l'élément d'actions et le message au conteneur
                        messageDiv.appendChild(userMessage);
                        messageDiv.appendChild(button);
                        messageDiv.appendChild(actionsDiv);
                        messagesContainer.appendChild(messageDiv);
                    });
                }
            })
            .catch(error => console.error("Erreur lors du chargement des messages:", error));
    };
</script>
