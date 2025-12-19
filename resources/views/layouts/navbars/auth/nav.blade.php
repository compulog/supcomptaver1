<head>
<!-- À placer dans ton HTML, de préférence dans le <head> ou avant la fin de <body> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>

        /* Navbar content alignée horizontalement */
/* body{
    background-color: #b88dc9ff;
} */
        .navbar-content {

            display: flex;

            align-items: center; /* Centrer verticalement tous les éléments */

            gap: 20px; /* Espacement entre les éléments */

            width: 100%;

        }
 


        /* Pour que le nom de la société et l'exercice soient sur la même ligne */

        .breadcrumb {

            margin-bottom: 0; /* Enlever la marge en bas pour éviter l'écart */

            padding: 0; /* Enlever tout padding inutile */

            text-align: left;

        }



        /* Section Exercice alignée correctement avec le reste */

        .exercice {

            display: flex;

            align-items: center;

            gap: 10px;

        }



        .exercice input, .exercice button {

            height: 25px; /* Pour avoir des inputs de taille plus uniforme */

        }

        .exercice button {

            width: 25px; /* Pour avoir des inputs de taille plus uniforme */

        }

        /* Navbar contenant l'icône et le texte */

        .navbar-brand {

            display: flex;

            align-items: center;

            gap: 10px; /* Espacement entre l'icône et le texte */

        }



        /* Liste déroulante */

        .dropdown-list {

            display: none; /* Cacher la liste par défaut */

            background-color: white;

            border: 1px solid #ccc;

            border-radius: 5px;

            width: 200px;

            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);

            position: absolute;

            top: 100%; /* Positionner juste en dessous de l'icône */

            right: 0; /* Alignement à droite */
            

        }



        .dropdown-list a {

            display: flex;

            align-items: center; /* Alignement des icônes et du texte */

            padding: 10px;

            text-decoration: none;

            color: black;

        }



        .dropdown-list a:hover {

            background-color: #f1f1f1;

        }



        .dropdown-list i {

            margin-right: 10px; /* Espacement entre l'icône et le texte */

        }

    </style>



    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>



<!-- Navbar -->

<nav class="navbar navbar-expand-lg navbar-dark px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true" style="background-color:
;">



  <div class="container-fluid py-1 px-3" style="background-color:#ffffff;">

        <nav aria-label="breadcrumb">

            <div class="navbar-content">

                <!-- Icône et texte "supcompta" sur la même ligne -->

                <a class="align-items-center d-flex m-0 navbar-brand text-wrap" href="{{ route('dashboard') }}">

                      <img src="../assets/img/curved-images/navlogo.png" alt="Image" style="width:45%;margin-left:-10%;">

                </a>



                <!-- Nom de la société -->

                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5"  style="margin-left:-23%;margin-top:-0.5%;">

                <li class="breadcrumb-item text-sm active text-capitalize" aria-current="page" style="color:#7A73D1; font-weight: bold;">
    <span class="truncate">{{ $societe->raison_sociale }}</span>
    {{ $societe->forme_juridique }}
</li>
                </ol>

@if(Auth::user()->type === 'interlocuteurs')



<!-- Exercice -->

<div class="exercice">

  <span style="color: #333;">Exercice:</span>

 

  <button type="button" class="btn-arrow left">

    <div class="arrow-left"></div>

  </button>



  <div class="date-range">

    Du <input type="date" value="{{ $societe->exercice_social_debut }}" >

    au <input type="date" value="{{ $societe->exercice_social_fin }}">

  </div>





</div>

@else



<!-- Exercice -->

<div class="exercice">

  <span style="color: #333;">Exercice:</span>

 

  <button type="button" class="btn-arrow left">

    <div class="arrow-left"></div>

  </button>



  <div class="date-range">

    Du <input type="date" value="{{ $societe->exercice_social_debut }}">

    au <input type="date" value="{{ $societe->exercice_social_fin }}">

  </div>

<div style="position: relative; display: inline-block;">
  <button id="btn-dropdown" type="button" class="btn-arrow right">
    <div class="arrow-right"></div>
  </button>

  <div id="dropdown-menu" class="dropdown">
    <div class="dropdown-option">Ouvrir nouveau exercice</div>
<div id="close-exercise" class="dropdown-option" onclick="envoyerFormulaire()">Clôturer exercice en cours</div>
  </div>
</div>

<!-- <button id="cloturer-exercice" onclick="envoyerFormulaire()">Cloturer l'exercice</button> -->

<form id="formulaire-cloturer-exercice" style="display: none;">
    @csrf
    <input type="hidden" name="date_debut" value="{{ $societe->exercice_social_debut }}">
    <input type="hidden" name="date_fin" value="{{ $societe->exercice_social_fin }}">
</form>

<script>

   function envoyerFormulaire() {
    // Récupérez les valeurs des champs du formulaire
    var date_debut = document.querySelector('#formulaire-cloturer-exercice input[name="date_debut"]').value;
    var date_fin = document.querySelector('#formulaire-cloturer-exercice input[name="date_fin"]').value;

    // Créez un objet FormData pour envoyer les données
    var formData = new FormData();
    formData.append('date_debut', date_debut);
    formData.append('date_fin', date_fin);

    // Soumettez le formulaire à la route /cloturer-exercice
    fetch('/cloturer-exercice', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        // Vérifier si la réponse est OK (code 200-299)
        if (!response.ok) {
            // Si erreur, on récupère le JSON et on affiche l'alerte avec le message
            return response.json().then(errorData => {
                alert(errorData.message); // Affiche le message d'erreur dans une alert JS
                throw new Error(errorData.message); // Stop la suite
            });
        }
        return response.json();
    })
    .then(data => {
        // Succès : affiche un message de confirmation par exemple
        alert(data.message);
        console.log(data.exercice);
    })
    .catch(error => {
        console.error('Erreur:', error);
        // Ici tu peux aussi gérer des erreurs réseau etc
    });
}

</script>
 

<style>
  .btn-arrow {
    position: relative;
    padding: 10px;
    background-color: #4D55CC;
    color: white;
    border: none;
    cursor: pointer;
  }

  .arrow-right {
    width: 0;
    height: 0;
    border-top: 8px solid transparent;
    border-bottom: 8px solid transparent;
    border-left: 8px solid white;
    display: inline-block;
  }

.dropdown {
  display: none;
  position: absolute;
  top: 0;
  left: 100%; /* Affiche à droite du bouton */
  margin-left: 5px;
  background: white;
  border: 1px solid #ccc;
  width: 600px;
  z-index: 1000;
}


  .dropdown-option {
    padding: 10px;
    cursor: pointer;
  }

  .dropdown-option:hover {
    background-color: #f1f1f1;
  }

  .dropdown.show {
    display: block;
  }
</style>
<script>
  const button = document.getElementById('btn-dropdown');
  const dropdown = document.getElementById('dropdown-menu');
  const closeOption = document.getElementById('close-exercise');

  button.addEventListener('click', () => {
    dropdown.classList.toggle('show');
  });

  // Fermer le menu si on clique ailleurs
  document.addEventListener('click', (event) => {
    if (!button.contains(event.target) && !dropdown.contains(event.target)) {
      dropdown.classList.remove('show');
    }
  });

  // Envoi au controller lors du clic sur "Clôturer exercice en cours"

</script>


</div>

@endif

<style>

  /* Style global de l'exercice */

.exercice {

 display: flex;


  gap: 15px;
 
  font-family: Arial, sans-serif;

  color: #333;

}



.exercice span {

  /* font-weight: bold; */

  font-size: 15px;

}



.date-range input {

  font-size: 14px;

  padding: 5px;

  border: 1px solid #ccc;

  border-radius: 5px;

  background-color: #f9f9f9;

}
.truncate {
  width: 150px; /* ajustez la largeur en fonction de vos besoins */
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}


.date-range input:focus {

  outline: none;

  border-color: #4D55CC;

}



.btn-arrow {

  background: none;

  border: none;

  padding: 0;

  cursor: pointer;

}



.btn-arrow:hover {

  transform: scale(1.1);

  transition: transform 0.2s ease-in-out;

}



/* Flèche droite */

.arrow-right {

  width: 0;

  height: 0;

  border-top: 5px solid transparent;

  border-bottom: 5px solid transparent;

  border-left: 15px solid #4D55CC; /* Flèche bleue */

}



/* Flèche gauche */

.arrow-left {

  width: 0;

  height: 0;

  border-top: 5px solid transparent;

  border-bottom: 5px solid transparent;

  border-right: 15px solid #4D55CC; /* Flèche bleue inversée */

}

 .notification-container {
      position: relative;
      display: inline-block;
    }

    /* Icône de la cloche */
    .notification-icon {
      color: #7A73D1;
      padding: 10px;
      cursor: pointer;
     }

    /* Liste déroulante cachée par défaut */
    .dropdown {
      display: none;
      position: absolute;
      top: 40px;
      right: 0;
      background-color: white;
      min-width: 200px;
      box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
      border-radius: 8px;
      overflow: hidden;
      z-index: 1;
      margin-left:-80%;
    }

    /* Élément de notification */
.dropdown-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
    color: #333;
    max-width: 500px; /* Définissez une valeur de largeur maximale */
    overflow: hidden; /* Cache le texte qui dépasse la largeur maximale */
    text-overflow: ellipsis; /* Affiche des points de suspension si le texte est tronqué */
}
    .dropdown-item:last-child {
      border-bottom: none;
    }

.dropdown-item:hover {
    overflow: visible; /* Affiche le texte complet lorsque l'utilisateur survole l'élément */
    text-overflow: clip; /* Supprime les points de suspension lorsque l'utilisateur survole l'élément */
    white-space: normal; /* Permet au texte de retourner à la ligne automatiquement lorsque l'utilisateur survole l'élément */
}

    #notificationDropdown {
        max-height: 500px;
        overflow-y: auto;
        background: white;
        border: 1px solid #ccc;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        position: absolute;
        width: 450px;
        z-index: 1000;
      }

</style>



                </div>

            </div>

        </nav>



        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 d-flex justify-content-end" id="navbar">

            


<style>
  #notification-count {
  position: absolute;
  top: 0;
  right: 3px;
  background-color: #ff0000;
  color: #ffffff;
  padding: 2px 5px;
  border-radius: 50%;
  font-size: 8px;
}
</style>
            <!-- Liste déroulante -->

            <li class="nav-item d-flex align-items-center" style="position: relative;margin-top:-50px;z-index:999;">
<div class="notification-container" >
<i class="fas fa-bell notification-icon" onclick="toggleDropdown()">
  <span id="notification-count" class="badge badge-danger"></span>
</i>
    <div class="dropdown" id="notificationDropdown" style="width:500px;margin-left:-450px;">
   
    </div>
  </div>

                <a href="javascript:;" id="dropdownListButton">
<div class="nav-link" style="margin-right:-10px; margin-top:3px; border: 1px solid #4D55CC; border-radius: 5px; padding: 5px 10px;">
    <span class="nav-link-text ms-1" style="display: block; line-height: 1;color:#4D55CC;">
        {{ Auth::user()->name }}
    </span>
    <span class="nav-link-text ms-1" style="font-size: 10px; display: block; line-height: 1.2;">
        @if(Auth::user()->type == 'admin')
            Administrateur
        @else
            {{ Auth::user()->type }}
        @endif
    </span>
</div>


                    <!-- <i class="fas fa-user-circle" style="font-size:22px;color:black;"></i> -->
 
                </a>



                <!-- Vérification du type d'utilisateur et affichage du menu approprié -->

                @if(Auth::user()->type === 'SuperAdmin')

                <div class="dropdown-list" id="dropdownList">
 @if(isset($societe->id))
    <a class="nav-link {{ Request::is('exercices/'.$societe->id) ? 'active' : '' }}" href="{{ url('exercices/'.$societe->id) }}">
        <i class="fas fa-tachometer-alt"></i>
        <span class="nav-link-text ms-1">Tableau de bord</span>
    </a>
@endif

                    <a class="nav-link {{ (Request::is('utilisateurs') ? 'active' : '') }}" href="{{ url('utilisateurs') }}">

                        <i class="fas fa-users"></i>

                        <span class="nav-link-text ms-1">Utilisateur</span>

                    </a>



                    <a class="nav-link {{ (Request::is('user-profile') ? 'active' : '') }} " href="{{ url('user-profile') }}">

                        <i class="fas fa-user"></i>

                        <span class="nav-link-text ms-1">Mon Profil</span>

                    </a>



                    <a href="{{ url('/logout')}}" class="nav-link">

                        <i class="fas fa-sign-out-alt"></i>

                        <span class="d-sm-inline d-none">Déconnexion</span>

                    </a>

                </div>

                @elseif(Auth::user()->type === 'admin')

                <div class="dropdown-list" id="dropdownList">
  @if(isset($societe->id))
    <a class="nav-link {{ Request::is('exercices/'.$societe->id) ? 'active' : '' }}" href="{{ url('exercices/'.$societe->id) }}">
        <i class="fas fa-tachometer-alt"></i>
        <span class="nav-link-text ms-1">Tableau de bord</span>
    </a>
@endif

                    <a class="nav-link {{ (Request::is('Admin') ? 'active' : '') }}" href="{{ url('Admin') }}">

                        <i class="fas fa-cogs"></i>

                        <span class="nav-link-text ms-1">Utilisateur</span>

                    </a>

                    <a class="nav-link {{ (Request::is('interlocuteurs') ? 'active' : '') }}" href="{{ url('interlocuteurs') }}">

                        <i class="fas fa-cogs"></i>

                        <span class="nav-link-text ms-1">Interlocuteur</span>

                    </a>



                    <a class="nav-link {{ (Request::is('user-profile') ? 'active' : '') }} " href="{{ url('user-profile') }}">

                        <i class="fas fa-user"></i>

                        <span class="nav-link-text ms-1">Mon Profil</span>

                    </a>



                    <a href="{{ url('/logout')}}" class="nav-link">

                        <i class="fas fa-sign-out-alt"></i>

                        <span class="d-sm-inline d-none">Déconnexion</span>

                    </a>

                </div>

                @elseif(Auth::user()->type === 'utilisateur')

                <div class="dropdown-list" id="dropdownList">
   @if(isset($societe->id))
    <a class="nav-link {{ Request::is('exercices/'.$societe->id) ? 'active' : '' }}" href="{{ url('exercices/'.$societe->id) }}">
        <i class="fas fa-tachometer-alt"></i>
        <span class="nav-link-text ms-1">Tableau de bord</span>
    </a>
@endif

                    <a class="nav-link {{ (Request::is('interlocuteurs') ? 'active' : '') }}" href="{{ url('interlocuteurs') }}">

                        <i class="fas fa-cogs"></i>

                        <span class="nav-link-text ms-1">Interlocuteur</span>

                    </a>



                    <a class="nav-link {{ (Request::is('user-profile') ? 'active' : '') }} " href="{{ url('user-profile') }}">

                        <i class="fas fa-user"></i>

                        <span class="nav-link-text ms-1">Mon Profil</span>

                    </a>



                    <a href="{{ url('/logout')}}" class="nav-link">

                        <i class="fas fa-sign-out-alt"></i>

                        <span class="d-sm-inline d-none">Déconnexion</span>

                    </a>

                </div>



                @else

                <div class="dropdown-list" id="dropdownList">

                    <a class="nav-link {{ (Request::is('user-profile') ? 'active' : '') }} " href="{{ url('user-profile') }}">

                        <i class="fas fa-user"></i>

                        <span class="nav-link-text ms-1">Mon Profil</span>

                    </a>



                    <a href="{{ url('/logout')}}" class="nav-link">

                        <i class="fas fa-sign-out-alt"></i>

                        <span class="d-sm-inline d-none">Déconnexion</span>

                    </a>

                </div>

                @endif

            </li>

        </div>

    </div>

</nav>
 


<!-- Script pour afficher/cacher la liste -->

<script>
window.onload = function() {
  fetch('/notifications/unread')
    .then(response => response.json())
    .then(data => {
      const notificationCount =
        data.messages.length +
        data.dossiers.length +
        data.soldes.length +
        data.files.length +
        data.folders.length +
        data.oldfiles.length +
        data.oldfolders.length +
        data.olddossiers.length +
        data.renamefiles.length +
        data.renamedossiers.length +
        data.renamefolders.length;

      document.getElementById('notification-count').innerText = notificationCount;
    })
    .catch(error => console.error(error));
};

/** ------------------ MARK AS READ ------------------ **/
function markAsRead(type, id, callback) {
  fetch(`/notifications/mark-as-read/${type}/${id}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
  .then(response => {
    if (response.ok) {
      if (callback) callback();
    } else {
      console.error('Erreur lors du marquage comme lu');
    }
  })
  .catch(err => console.error(err));
}

/** ------------------ TOGGLE DROPDOWN ------------------ **/
function toggleDropdown() {
  const dropdown = document.getElementById("notificationDropdown");

  if (dropdown.style.display === "block") {
    dropdown.style.display = "none";
    return;
  }

  /** Helpers **/
  function routeForFile(file) {
    if (!file) return '#';
    const type = (file.type || '').toLowerCase().trim();
    const folders = file.folders;
    if (!folders || folders === 0) {
      switch (type) {
        case 'achat': return '/achat';
        case 'vente': return '/vente';
        case 'banque': return '/banque';
        case 'paie': return '/paie';
        case 'impot': return '/impot';
        case 'dossier_permanant': return '/Dossier_permanant';
        default:
          return file.dossier && file.dossier.id ? `/Douvrir/${file.dossier.id}` : '#';
      }
    } else {
      switch (type) {
        case 'achat': return `/folder/${folders}`;
        case 'vente': return `/foldersVente1/${folders}`;
        case 'banque': return `/foldersBanque1/${folders}`;
        case 'paie': return `/foldersPaie1/${folders}`;
        case 'impot': return `/foldersImpot1/${folders}`;
        case 'dossier_permanant': return `/foldersDossierPermanant1/${folders}`;
        default:
          return file.dossier && file.dossier.id ? `/dasousdossier/${file.dossier.id}` : '#';
      }
    }
  }

  function routeForFolder(folder) {
    const folderId = folder.folder_id || 0;
    const type = (folder.type_folder || '').toLowerCase().trim();

    if (!folderId) {
      switch (type) {
        case 'achat': return '/achat';
        case 'vente': return '/vente';
        case 'banque': return '/banque';
        case 'paie': return '/paie';
        case 'impot': return '/impot';
        case 'dossier_permanant': return '/Dossier_permanant';
        default: return '#';
      }
    } else {
      switch (type) {
        case 'achat': return `/folder/${folderId}`;
        case 'vente': return `/foldersVente1/${folderId}`;
        case 'banque': return `/foldersBanque1/${folderId}`;
        case 'paie': return `/foldersPaie1/${folderId}`;
        case 'impot': return `/foldersImpot1/${folderId}`;
        case 'dossier_permanant': return `/foldersDossierPermanant1/${folderId}`;
        default: return `/dasousdossier/${folderId}`;
      }
    }
  }

  /** Charger notifications **/
  fetch('/notifications/unread')
    .then(response => response.json())
    .then(data => {
      dropdown.innerHTML = '';

      const all = [];

      (data.messages || []).forEach(m => all.push({ kind: 'message', obj: m, date: new Date(m.created_at) }));
      (data.dossiers || []).forEach(d => all.push({ kind: 'dossier', obj: d, date: new Date(d.created_at) }));
      (data.soldes || []).forEach(s => all.push({ kind: 'solde', obj: s, date: new Date(s.updated_at) }));
      (data.files || []).forEach(f => all.push({ kind: 'file', obj: f, date: new Date(f.updated_at) }));
      (data.folders || []).forEach(f => all.push({ kind: 'folder', obj: f, date: new Date(f.created_at) }));
      (data.oldfiles || []).forEach(f => all.push({ kind: 'oldfile', obj: f, date: new Date(f.updated_at) }));
      (data.oldfolders || []).forEach(f => all.push({ kind: 'oldfolder', obj: f, date: new Date(f.updated_at) }));
      (data.olddossiers || []).forEach(d => all.push({ kind: 'olddossier', obj: d, date: new Date(d.updated_at) }));
      (data.renamefiles || []).forEach(f => all.push({ kind: 'renamefile', obj: f, date: new Date(f.updated_at) }));
      (data.renamedossiers || []).forEach(d => all.push({ kind: 'renamedossier', obj: d, date: new Date(d.updated_at) }));
      (data.renamefolders || []).forEach(f => all.push({ kind: 'renamefolder', obj: f, date: new Date(f.updated_at) }));

      all.sort((a, b) => b.date - a.date);

      if (all.length === 0) {
        dropdown.innerHTML = '<div class="dropdown-item">Aucune notification</div>';
        dropdown.style.display = "block";
        return;
      }

      all.forEach(entry => {
        const obj = entry.obj;
        const when = formatDate(entry.date);
        const bg = obj.notif_bg_color == 0 ? '#dcdbed' : '#ffffff';
        let html = "";

        /** Fonction utilitaire pour le lien **/
        function notificationLink(route, type, id) {
          return `<a href="javascript:void(0)" 
                     style="text-decoration:none;color:inherit;"
                     onclick="markAsRead('${type}', ${id}, function() { window.location.href='${route}'; })">`;
        }

        /** ---------------- MESSAGE ---------------- **/
        if (entry.kind === "message") {
          const file = obj.file;
          const route = routeForFile(file);
          html = `
            <div class="dropdown-item d-flex justify-content-between align-items-center"
                 style="background:${bg};border-radius:4px;">
              ${notificationLink(route, 'message', obj.id)}
                <strong>${obj.user?.name || 'Utilisateur inconnu'}</strong>
                a commenté : <strong>${file?.name || 'Fichier'}</strong>
                <br>"${obj.text_message}"
                <br><small>${when}</small>
              </a>
              <i class="fas fa-trash-alt text-danger ms-2"
                 onclick="supprimerNotificationMessage(${obj.id})"
                 style="cursor:pointer;"></i>
            </div>
          `;
        }

        /** ---------------- DOSSIER ---------------- **/
        else if (entry.kind === "dossier") {
          const route = `/exercices/${obj.societe_id}`;
          html = `
            <div class="dropdown-item d-flex justify-content-between align-items-center"
                 style="background:${bg};border-radius:4px;">
              ${notificationLink(route, 'dossier', obj.id)}
                <strong>${obj.user?.name || 'Utilisateur inconnu'}</strong>
                a créé le dossier <strong>${obj.name}</strong>
                <br><small>${when}</small>
              </a>
              <i class="fas fa-trash-alt text-danger ms-2"
                 onclick="supprimerNotificationDossier(${obj.id})"
                 style="cursor:pointer;"></i>
            </div>
          `;
        }

        /** ---------------- SOLDE ---------------- **/
        else if (entry.kind === "solde") {
          const route = `/etat-de-caisse?mois=${obj.mois}&annee=${obj.annee}`;
          html = `
            <div class="dropdown-item d-flex justify-content-between align-items-center"
                 style="background:${bg};border-radius:4px;">
              ${notificationLink(route, 'solde', obj.id)}
                <strong>${obj.updated_by?.name || 'Utilisateur inconnu'}</strong>
                a clôturé l'état de caisse <strong>${obj.mois}/${obj.annee}</strong>
                <br><small>${when}</small>
              </a>
              <i class="fas fa-trash-alt text-danger ms-2"
                 onclick="supprimerNotificationSolde(${obj.id})"
                 style="cursor:pointer;"></i>
            </div>
          `;
        }

        /** ---------------- FICHIERS ---------------- **/
        else if (["file","oldfile","renamefile"].includes(entry.kind)) {
          const route = routeForFile(obj);
          const user = obj.updated_by?.name || 'Utilisateur inconnu';
          const action = entry.kind === "file" ? "ajouté" : entry.kind === "oldfile" ? "supprimé" : "renommé";
          html = `
            <div class="dropdown-item d-flex justify-content-between align-items-center"
                 style="background:${bg};border-radius:4px;">
              ${notificationLink(route, 'file', obj.id)}
                <strong>${user}</strong> a ${action} le fichier <strong>${obj.name}</strong> (${obj.type})
                <br><small>${when}</small>
              </a>
              <i class="fas fa-trash-alt text-danger ms-2"
                 onclick="supprimerNotificationFichier(${obj.id})"
                 style="cursor:pointer;"></i>
            </div>
          `;
        }

        /** ---------------- SOUS-DOSSIERS ---------------- **/
        else if (["folder","oldfolder","renamefolder"].includes(entry.kind)) {
          const route = routeForFolder(obj);
          const user = obj.updated_by?.name || 'Utilisateur inconnu';
          const action = entry.kind === "folder" ? "créé" : entry.kind === "oldfolder" ? "supprimé" : "renommé";
          html = `
            <div class="dropdown-item d-flex justify-content-between align-items-center"
                 style="background:${bg};border-radius:4px;">
              ${notificationLink(route, 'folder', obj.id)}
                <strong>${user}</strong> a ${action} le dossier <strong>${obj.name}</strong> (${obj.type_folder || obj.type})
                <br><small>${when}</small>
              </a>
              <i class="fas fa-trash-alt text-danger ms-2"
                 onclick="supprimerNotificationSousDossier(${obj.id})"
                 style="cursor:pointer;"></i>
            </div>
          `;
        }

        /** ---------------- DOSSIERS PRINCIPAUX ---------------- **/
        else if (["olddossier","renamedossier"].includes(entry.kind)) {
          const route = `/exercices/${obj.societe_id}`;
          const user = obj.user?.name || 'Utilisateur inconnu';
          const action = entry.kind === "olddossier" ? "supprimé" : "renommé";
          html = `
            <div class="dropdown-item d-flex justify-content-between align-items-center"
                 style="background:${bg};border-radius:4px;">
              ${notificationLink(route, 'dossier', obj.id)}
                <strong>${user}</strong> a ${action} un dossier principal : <strong>${obj.name}</strong>
                <br><small>${when}</small>
              </a>
              <i class="fas fa-trash-alt text-danger ms-2"
                 onclick="supprimerNotificationDossier(${obj.id})"
                 style="cursor:pointer;"></i>
            </div>
          `;
        }

        dropdown.innerHTML += html;
      });

      dropdown.style.display = "block";
    })
    .catch(error => {
      console.error("Erreur lors du chargement :", error);
      dropdown.innerHTML = '<div class="dropdown-item">Erreur de chargement</div>';
      dropdown.style.display = "block";
    });
}

/** ------------------ CLOSE ON CLICK OUTSIDE ------------------ **/
window.onclick = function(event) {
  if (!event.target.matches('.notification-icon')) {
    const dropdown = document.getElementById("notificationDropdown");
    if (dropdown.style.display === "block") {
      dropdown.style.display = "none";
    }
  }
}

/** ------------------ FORMAT DATE ------------------ **/
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleString('fr-FR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

document.getElementById('dropdownListButton').addEventListener('click', function() {
  var dropdownList = document.getElementById('dropdownList');
  dropdownList.style.display =
    (dropdownList.style.display === "none" || dropdownList.style.display === "")
      ? "block" : "none";
});

/** ------------------- FONCTIONS DE SUPPRESSION ------------------- **/
// Tes fonctions de suppression restent inchangées ici
  /** ------------------- FONCTIONS DE SUPPRESSION ------------------- **/

      function supprimerNotificationMessage(id) {
        Swal.fire({
          icon: 'info',
          title: 'Suppression de notification',
          text: "Vous êtes sûr de vouloir supprimer cette notification ?",
          showCancelButton: true,
          confirmButtonText: 'Oui, continuer',
          cancelButtonText: 'Non',
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6'
        }).then((result) => {
          if (result.isConfirmed) {
            fetch('/notifications/supprimer-notification-message/' + id, {
              method: 'PUT',
              headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(() => {
              toggleDropdown(); 
              Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'Notification supprimée !', showConfirmButton: false, timer: 1500
              });
            });
          }
        });
      }

      function supprimerNotificationDossier(id) {
        Swal.fire({
          icon: 'info',
          title: 'Suppression',
          text: "Supprimer cette notification ?",
          showCancelButton: true,
          confirmButtonText: 'Oui',
          cancelButtonText: 'Non',
          confirmButtonColor: '#d33'
        }).then(result => {
          if (result.isConfirmed) {
            fetch('/notifications/supprimer-notification-dossier/' + id, {
              method: 'PUT',
              headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(() => {
              toggleDropdown();
              Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'Notification supprimée !', showConfirmButton: false, timer: 1500
              });
            });
          }
        });
      }

      function supprimerNotificationSolde(id) {
        Swal.fire({
          icon: 'info',
          title: 'Suppression',
          text: "Supprimer cette notification ?",
          showCancelButton: true,
          confirmButtonText: 'Oui',
          cancelButtonText: 'Non',
          confirmButtonColor: '#d33'
        }).then(result => {
          if (result.isConfirmed) {
            fetch('/notifications/supprimer-notification-solde/' + id, {
              method: 'PUT',
              headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(() => {
              toggleDropdown(); 
              Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'Notification supprimée !', showConfirmButton: false, timer: 1500
              });
            });
          }
        });
      }

      function supprimerNotificationFichier(id) {
        Swal.fire({
          icon: 'info',
          title: 'Suppression',
          text: "Supprimer cette notification ?",
          showCancelButton: true,
          confirmButtonText: 'Oui',
          cancelButtonText: 'Non',
          confirmButtonColor: '#d33'
        }).then(result => {
          if (result.isConfirmed) {
            fetch('/notifications/supprimer-notification-fichier/' + id, {
              method: 'PUT',
              headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(() => {
              toggleDropdown();
              Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'Notification supprimée !', showConfirmButton: false, timer: 1500
              });
            });
          }
        });
      }

      function supprimerNotificationSousDossier(id) {
        Swal.fire({
          icon: 'info',
          title: 'Suppression',
          text: "Supprimer cette notification ?",
          showCancelButton: true,
          confirmButtonText: 'Oui',
          cancelButtonText: 'Non',
          confirmButtonColor: '#d33'
        }).then(result => {
          if (result.isConfirmed) {
            fetch('/notifications/supprimer-notification-sous-dossier/' + id, {
              method: 'PUT',
              headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(() => {
              toggleDropdown();
              Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'Notification supprimée !', showConfirmButton: false, timer: 1500
              });
            });
          }
        });
      }
</script>



