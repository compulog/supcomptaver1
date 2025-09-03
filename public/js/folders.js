

 

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



function downloadFile(fileId) {

    window.location.href = '/file/download/' + fileId;

}

let currentFileIndex = 0; // Index du fichier actuel
 
function navigateFile(direction) {
    if (files.length === 0) {
        console.error('Aucun fichier à naviguer.');
        return;
    }

    currentFileIndex += direction;

    if (currentFileIndex < 0) {
        currentFileIndex = 0;
    } else if (currentFileIndex >= files.length) {
        currentFileIndex = files.length - 1;
    }

    console.log(currentFileIndex);
    console.log(files);

    viewFile(files[currentFileIndex].id);
}

let currentPage = 1;
function viewFile(fileId) {
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

                document.body.appendChild(input);



            currentPageDisplay.replaceWith(input);
            input.focus();

            const validateInput = () => {
                const pageNum = parseInt(input.value);
                if (!isNaN(pageNum) && pageNum >= 1 && pageNum <= totalPages) {
                    const targetPage = document.querySelector(`.pdf-page[data-page-number='${pageNum}']`);
                    if (targetPage) {
                        targetPage.scrollIntoView({ behavior: 'smooth' });
                    }
                }
                currentPageDisplay.innerText = `Page ${currentPage} sur ${totalPages}`;
                input.replaceWith(currentPageDisplay);
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
    userMessage.innerHTML = `<strong>${message.user_name}:</strong> ${message.text_message} <i>Posté le: ${message.created_at}</i>`;
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
    document.getElementById("filePreviewContent").addEventListener("mouseup", function () {
        const selection = window.getSelection();
        if (selection.toString().length > 0) {
            selectedText = selection.toString();
            range = selection.getRangeAt(0);
            
            // Insérer le texte sélectionné dans la boîte de communication
            document.getElementById("message_text").value = selectedText + '@ '; // Insérer le texte sélectionné
            document.getElementById("message_text").focus(); // Met le focus sur le champ de message
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


// Fonction pour gérer le clic sur l'icône de commentaire
function handleCommentClick(commentText) {
    console.log("Commentaire cliqué:", commentText); // Log du commentaire
    searchAndHighlightText(commentText);
}

// Fonction pour rechercher et sélectionner le texte dans le PDF
function searchAndHighlightText(text) {
    const textLayer = document.querySelector('.textLayer'); // Sélectionner la couche de texte du PDF

    if (textLayer) {
        const textElements = textLayer.getElementsByTagName('span'); // Récupérer tous les éléments de texte
        console.log("Éléments de texte trouvés:", textElements.length); // Log du nombre d'éléments

        for (let i = 0; i < textElements.length; i++) {
            console.log("Vérification de l'élément:", textElements[i].textContent); // Log du contenu de chaque élément
            if (textElements[i].textContent.includes(text)) {
                console.log("Texte trouvé dans l'élément:", textElements[i].textContent); // Log si le texte est trouvé
                // Si le texte est trouvé, le sélectionner
                const range = document.createRange();
                range.selectNodeContents(textElements[i]);
                const selection = window.getSelection();
                selection.removeAllRanges(); // Effacer les sélections précédentes
                selection.addRange(range); // Ajouter la nouvelle sélection

                // Optionnel : ajouter un style de surlignage
                textElements[i].classList.add('highlight');
                break; // Sortir de la boucle après avoir trouvé le texte
            }
        }
    } else {
        console.error("Aucun texte trouvé dans le document.");
    }
}


function openRenameModal(folderId, folderName) {
    document.getElementById('newFolderName').value = folderName;
    document.getElementById('renameFolderForm').action = '/folder/' + folderId; // Met à jour l'action du formulaire avec l'ID du dossier
    var myModal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
    myModal.show();
}
 


// function viewFile(fileId,folderId) {

//     alert(folderId);

//         window.location.href = '/achat/view/' + folderId + fileId;

// }



 