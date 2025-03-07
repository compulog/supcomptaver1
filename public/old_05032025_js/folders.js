
 
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

function viewFile(fileId) {
   
        window.location.href = '/achat/view/' + fileId ;
}

// function viewFile(fileId,folderId) {
//     alert(folderId);
//         window.location.href = '/achat/view/' + folderId + fileId;
// }

 