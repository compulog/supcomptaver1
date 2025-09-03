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

                        <span class="nav-link-text ms-1">Utilisateurs</span>

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

                        <span class="nav-link-text ms-1">Utilisateurs</span>

                    </a>

                    <a class="nav-link {{ (Request::is('interlocuteurs') ? 'active' : '') }}" href="{{ url('interlocuteurs') }}">

                        <i class="fas fa-cogs"></i>

                        <span class="nav-link-text ms-1">Interlocuteurs</span>

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

                        <span class="nav-link-text ms-1">interlocuteurs</span>

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
  // Récupérer le nombre de notifications non lues
  fetch('/notifications/unread')
    .then(response => response.json())
    .then(data => {
 const notificationCount = data.messages.length + data.dossiers.length + data.soldes.length + data.files.length + data.folders.length + data.oldfiles.length + data.oldfolders.length + data.olddossiers.length + data.renamefiles.length + data.renamedossiers.length + data.renamefolders.length;
      document.getElementById('notification-count').innerText = notificationCount;
    
})
    .catch(error => console.error(error));
};
  function toggleDropdown() {
    const dropdown = document.getElementById("notificationDropdown");

    // Toggle display (on/off)
    if (dropdown.style.display === "block") {
      dropdown.style.display = "none";
      return;
    }

    // Charger les notifications depuis Laravel
    fetch('/notifications/unread')
      .then(response => response.json())
      .then(data => {
        dropdown.innerHTML = ''; // Vider le menu
//  const notificationCount = data.messages.length + data.dossiers.length + data.soldes.length + data.files.length + data.folders.length + data.oldfiles.length + data.oldfolders.length + data.olddossiers.length + data.renamefiles.length + data.renamedossiers.length + data.renamefolders.length;
//       document.getElementById('notification-count').innerText = notificationCount;
    
        // === Afficher les messages ===
        if (!data.messages || data.messages.length === 0) {
          dropdown.innerHTML += '<div class="dropdown-item">Aucun message non lu</div>';
        } else {
          data.messages.forEach(msg => {
            const file = msg.file;
            const type = file ? (file.type || '').toLowerCase().trim() : null;
            let route = '#';

            if (!file) {
              route = '#';
            } else {
              const folders = file.folders;
              if (!folders || folders === 0) {
                switch (type) {
                  case 'achat': route = '/achat'; break;
                  case 'vente': route = '/vente'; break;
                  case 'banque': route = '/banque'; break;
                  case 'paie': route = '/paie'; break;
                  case 'impot': route = '/impot'; break;
                  case 'dossier_permanant': route = '/Dossier_permanant'; break;
                  default:
                    if (file.dossier && file.dossier.id) {
                      route = `/Douvrir/${file.dossier.id}`;
                    }
                }
              } else {
                switch (type) {
                  case 'achat': route = `/folder/${folders}`; break;
                  case 'vente': route = `/foldersVente1/${folders}`; break;
                  case 'banque': route = `/foldersBanque1/${folders}`; break;
                  case 'paie': route = `/foldersPaie1/${folders}`; break;
                  case 'impot': route = `/foldersImpot1/${folders}`; break;
                  case 'dossier_permanant': route = `/foldersDossierPermanant1/${folders}`; break;
                  default:
                    if (file.dossier && file.dossier.id) {
                      route = `/dasousdossier/${file.dossier.id}`;
                    }
                }
              }
            }

            const item = `
              <div class="dropdown-item d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">

              <a href="${route}" class="dropdown-item" style="text-decoration: none; color: inherit;">
                <strong>${msg.user ? msg.user.name : 'Utilisateur inconnu'}</strong> a commenté le fichier 
                ${file ? `<strong>${file.name}</strong>` : '<em>un fichier inconnu</em>'} : 
                "${msg.text_message}"
                <br>
                <small>${formatDate(msg.created_at)}</small>
                ${msg.commentaire ? `<br><em style="color: #666;">${msg.commentaire}</em>` : ''}
              </a>
<i class="fas fa-trash-alt text-danger ms-2" style="cursor: pointer;" onclick="supprimerNotificationMessage(${msg.id})"></i>
  </div>

            `;
            dropdown.innerHTML += item;
          });
        }

        dropdown.innerHTML += '<hr>';

        // === Afficher les dossiers ===
        if (!data.dossiers || data.dossiers.length === 0) {
          dropdown.innerHTML += '<div class="dropdown-item">Aucun dossier</div>';
        } else {
          data.dossiers.forEach(dossier => {
            const societeId = dossier.societe_id || 0;
            const url = `/exercices/${societeId}`;

            dropdown.innerHTML += `
              <div class="dropdown-item d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">

              <a href="${url}" class="dropdown-item" style="text-decoration: none; color: inherit;">
                <strong>${dossier.user ? dossier.user.name : 'Utilisateur inconnu'}</strong> a créé un dossier 
                <strong>${dossier.name}</strong>
              </a>
                <i class="fas fa-trash-alt text-danger ms-2" style="cursor: pointer;"onclick="supprimerNotificationDossier(${dossier.id})"></i>
  </div>

            `;
          });
        }

        dropdown.innerHTML += '<hr>';

        // === Afficher les soldes mensuels ===
        if (!data.soldes || data.soldes.length === 0) {
          dropdown.innerHTML += '<div class="dropdown-item">Aucun solde mensuel clôturé</div>';
        } else {
          data.soldes.forEach(solde => {
            const periode = `${solde.mois.toString().padStart(2, '0')}/${solde.annee}`;
            const url = `/etat-de-caisse?mois=${solde.mois}&annee=${solde.annee}`;

            dropdown.innerHTML += `
              <div class="dropdown-item d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">

              <a href="${url}" class="dropdown-item" style="text-decoration: none; color: inherit;">
                <strong>${solde.updated_by ? solde.updated_by.name : 'Utilisateur inconnu'}</strong> a clôturé l'état de caisse de la période <strong>${periode}</strong>
              </a>
                  <i class="fas fa-trash-alt text-danger ms-2" style="cursor: pointer;"onclick="supprimerNotificationSolde(${solde.id})"></i>
  </div>

            `;
          });
        }

        dropdown.innerHTML += '<hr>';

        // === Afficher les fichiers ===
        if (!data.files || data.files.length === 0) {
          dropdown.innerHTML += '<div class="dropdown-item">Aucun fichier</div>';
        } else {
          data.files.forEach(file => {
            const nomFichier = file.name || 'Nom inconnu';
            const typeFichier = (file.type || 'Type inconnu').toLowerCase().trim();
            const utilisateur = file.updated_by ? file.updated_by.name : 'Utilisateur inconnu';
            const date = formatDate(file.updated_at);
            const folders = file.folders;

            const fileContent = `
              <strong>${utilisateur}</strong> a ajouté le fichier  
              <strong>${nomFichier}</strong> dans le dossier
              <strong>${file.type}</strong>
              <br><small>${date}</small>
            `;

            let route = null;

            if (folders === null || folders === 0) {
              switch (typeFichier) {
                case 'achat': route = '/achat'; break;
                case 'vente': route = '/vente'; break;
                case 'banque': route = '/banque'; break;
                case 'paie': route = '/paie'; break;
                case 'impot': route = '/impot'; break;
                case 'dossier_permanant': route = '/Dossier_permanant'; break;
                default:
                  if (file.dossier && file.dossier.id) {
                    route = `/Douvrir/${file.dossier.id}`;
                  }
              }
            } else {
              switch (typeFichier) {
                case 'achat': route = `/folder/${folders}`; break;
                case 'vente': route = `/foldersVente1/${folders}`; break;
                case 'banque': route = `/foldersBanque1/${folders}`; break;
                case 'paie': route = `/foldersPaie1/${folders}`; break;
                case 'impot': route = `/foldersImpot1/${folders}`; break;
                case 'dossier_permanant': route = `/foldersDossierPermanant1/${folders}`; break;
                default:
                  if (file.dossier && file.dossier.id) {
                    route = `/dasousdossier/${file.dossier.id}`;
                  }
              }
            }

            if (route) {
              dropdown.innerHTML += `
                <div class="dropdown-item d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">

                <a href="${route}" class="dropdown-item" style="text-decoration: none; color: inherit;">
                  ${fileContent}
                </a>
                    <i class="fas fa-trash-alt text-danger ms-2" style="cursor: pointer;"  onclick="supprimerNotificationFichier(${file.id})"></i>
  </div>

              `;
            } else {
              dropdown.innerHTML += `
                <div class="dropdown-item">
                  ${fileContent}
                </div>
              `;
            }
          });
        }

        // === Afficher les sous-dossiers (folders) ===
        dropdown.innerHTML += '<hr>';

        if (!data.folders || data.folders.length === 0) {
          dropdown.innerHTML += '<div class="dropdown-item">Aucun sous-dossier</div>';
      } else {
    data.folders.forEach(folder => {
  const utilisateur = folder.updated_by?.name || 'Utilisateur inconnu';
      const nomSousDossier = folder.name?.trim() || 'Nom inconnu';
      const typePrincipal = folder.type_folder?.trim().toLowerCase() || 'Type inconnu';
      const dateCreation = formatDate(folder.created_at);
      const folderId = folder.folder_id || 0;

      let url = '#';

      if (!folderId || folderId === 0) {
        switch (typePrincipal) {
          case 'achat': url = '/achat'; break;
          case 'vente': url = '/vente'; break;
          case 'banque': url = '/banque'; break;
          case 'paie': url = '/paie'; break;
          case 'impot': url = '/impot'; break;
          case 'dossier_permanant': url = '/Dossier_permanant'; break;
          default: url = '#';
        }
      } else {
        switch (typePrincipal) {
          case 'achat': url = `/folder/${folderId}`; break;
          case 'vente': url = `/foldersVente1/${folderId}`; break;
          case 'banque': url = `/foldersBanque1/${folderId}`; break;
          case 'paie': url = `/foldersPaie1/${folderId}`; break;
          case 'impot': url = `/foldersImpot1/${folderId}`; break;
          case 'dossier_permanant': url = `/foldersDossierPermanant1/${folderId}`; break;
          default: url = `/dasousdossier/${folderId}`;
        }
      }

      dropdown.innerHTML += `
        <div class="dropdown-item d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">

        <a href="${url}" class="dropdown-item" style="text-decoration: none; color: inherit;">
          <strong>${utilisateur}</strong> a créé un sous-dossier 
          <strong>${nomSousDossier}</strong> dans le dossier principal 
          <strong>${typePrincipal}</strong><br>
          <small>${dateCreation}</small>
        </a>
            <i class="fas fa-trash-alt text-danger ms-2" style="cursor: pointer;" onclick="supprimerNotificationSousDossier(${folder.id})"></i>
  </div>

      `;
    });
  }

  // === Afficher les oldfiles ===
  dropdown.innerHTML += '<hr>';

  if (!data.oldfiles || data.oldfiles.length === 0) {
    dropdown.innerHTML += '<div class="dropdown-item">Aucun ancien fichier</div>';
  } else {
    data.oldfiles.forEach(file => {
      const nomFichier = file.name || 'Nom inconnu';
      const typeFichier = (file.type || 'Type inconnu').toLowerCase().trim();
      const utilisateur = file.updated_by ? file.updated_by.name : 'Utilisateur inconnu';
      const date = formatDate(file.updated_at);
      const folders = file.folders;

      let route = '#';  // adapte selon ta logique si besoin

      // // Par exemple, on réutilise ta logique route existante (simplifiée ici)
      // if (folders === null || folders === 0) {
      //   switch (typeFichier) {
      //     case 'achat': route = '/achat'; break;
      //     case 'vente': route = '/vente'; break;
      //     case 'banque': route = '/banque'; break;
      //     case 'paie': route = '/paie'; break;
      //     case 'impot': route = '/impot'; break;
      //     case 'dossier_permanant': route = '/Dossier_permanant'; break;
      //     default:
      //       if (file.dossier && file.dossier.id) {
      //         route = `/Douvrir/${file.dossier.id}`;
      //       }
      //   }
      // } else {
      //   // autre logique si nécessaire
      //   route = `/folder/${folders}`;
      // }

      dropdown.innerHTML += `
        <div class="dropdown-item d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">

        <a href="${route}" class="dropdown-item" style="text-decoration: none; color: inherit;">
          <strong>${utilisateur}</strong> a supprimé le fichier  
          <strong>${nomFichier}</strong> dans le dossier <strong>${file.type}</strong>
          <br><small>${date}</small>
        </a>
<i class="fas fa-trash-alt text-danger ms-2" style="cursor: pointer;"  onclick="supprimerNotificationFichier(${file.id})"></i>
  </div>

      `;
    });
  }
  // === Afficher les oldfolders (sous-dossiers supprimés) ===
  dropdown.innerHTML += '<hr>';

  if (!data.oldfolders || data.oldfolders.length === 0) {
    dropdown.innerHTML += '<div class="dropdown-item">Aucun ancien sous-dossier</div>';
  } else {
    data.oldfolders.forEach(folder => {
      const utilisateur = folder.updated_by ? folder.updated_by.name : 'Utilisateur inconnu';
      const nomSousDossier = folder.name ? folder.name.trim() : 'Nom inconnu';
      const typePrincipal = folder.type_folder ? folder.type_folder.trim() : 'Type inconnu';

      dropdown.innerHTML += `
        <div class="dropdown-item d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">

        <div class="dropdown-item"  >
          <strong>${utilisateur}</strong> a supprimé le sous-dossier 
          <strong>${nomSousDossier}</strong> dans le dossier principal 
          <strong>${typePrincipal}</strong>
        </div>
            <i class="fas fa-trash-alt text-danger ms-2" style="cursor: pointer;" onclick="supprimerNotificationSousDossier(${folder.id})"></i>
  </div>

      `;
    });
  }
  // === Afficher les olddossiers (dossiers principaux supprimés) ===
  dropdown.innerHTML += '<hr>';

  if (!data.olddossiers || data.olddossiers.length === 0) {
    dropdown.innerHTML += '<div class="dropdown-item">Aucun ancien dossier principal</div>';
  } else {
    data.olddossiers.forEach(dossier => {
      const utilisateur = dossier.user ? dossier.user.name : 'Utilisateur inconnu';
      const nomDossier = dossier.name ? dossier.name.trim() : 'Nom inconnu';

      dropdown.innerHTML += `
        <div class="dropdown-item d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">

        <div class="dropdown-item" >
          <strong>${utilisateur}</strong> a supprimé un dossier principal 
          <strong>${nomDossier}</strong>
        </div>
                <i class="fas fa-trash-alt text-danger ms-2" style="cursor: pointer;"onclick="supprimerNotificationDossier(${dossier.id})"></i>
  </div>

      `;
    });
  }

  // === Afficher les fichiers renommés (renamefiles) ===
  dropdown.innerHTML += '<hr>';

  if (!data.renamefiles || data.renamefiles.length === 0) {
    dropdown.innerHTML += '<div class="dropdown-item">Aucun fichier renommé</div>';
  } else {
    data.renamefiles.forEach(file => {
      const nomFichier = file.name || 'Nom inconnu';
      const typeFichier = (file.type || 'Type inconnu').toLowerCase().trim();
      const utilisateur = file.updated_by ? file.updated_by.name : 'Utilisateur inconnu';
      const dateModif = formatDate(file.updated_at);
      const folders = file.folders;

      let route = '#';

      if (!folders || folders === 0) {
        switch (typeFichier) {
          case 'achat': route = '/achat'; break;
          case 'vente': route = '/vente'; break;
          case 'banque': route = '/banque'; break;
          case 'paie': route = '/paie'; break;
          case 'impot': route = '/impot'; break;
          case 'dossier_permanant': route = '/Dossier_permanant'; break;
          default:
            if (file.dossier && file.dossier.id) {
              route = `/Douvrir/${file.dossier.id}`;
            }
        }
      } else {
        switch (typeFichier) {
          case 'achat': route = `/folder/${folders}`; break;
          case 'vente': route = `/foldersVente1/${folders}`; break;
          case 'banque': route = `/foldersBanque1/${folders}`; break;
          case 'paie': route = `/foldersPaie1/${folders}`; break;
          case 'impot': route = `/foldersImpot1/${folders}`; break;
          case 'dossier_permanant': route = `/foldersDossierPermanant1/${folders}`; break;
          default:
            if (file.dossier && file.dossier.id) {
              route = `/dasousdossier/${file.dossier.id}`;
            }
        }
      }

      dropdown.innerHTML += `
        <div class="dropdown-item d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">

        <a href="${route}" class="dropdown-item" style="text-decoration: none; color: inherit;">
          <strong>${utilisateur}</strong> a renommé le fichier  
          <strong>${nomFichier}</strong> dans le dossier <strong>${file.type}</strong>
          <br><small>${dateModif}</small>
        </a>
<i class="fas fa-trash-alt text-danger ms-2" style="cursor: pointer;"  onclick="supprimerNotificationFichier(${file.id})"></i>
  </div>

      `;
    });
  }

  // === Afficher les dossiers renommés ===
  dropdown.innerHTML += '<hr>';

  if (!data.renamedossiers || data.renamedossiers.length === 0) {
    dropdown.innerHTML += '<div class="dropdown-item">Aucun dossier renommé</div>';
  } else {
    data.renamedossiers.forEach(dossier => {
      const utilisateur = dossier.user ? dossier.user.name : 'Utilisateur inconnu';
      const nouveauNom = dossier.name || 'Nom inconnu';
      const dateModif = formatDate(dossier.updated_at);
      const societeId = dossier.societe_id || 0;
      const route = `/exercices/${societeId}`;

      dropdown.innerHTML += `
        <div class="dropdown-item d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">

        <a href="${route}" class="dropdown-item" style="text-decoration: none; color: inherit;">
          <strong>${utilisateur}</strong> a renommé un dossier principal. Nouveau nom : 
          <strong>${nouveauNom}</strong><br>
          <small>${dateModif}</small>
        </a>
                <i class="fas fa-trash-alt text-danger ms-2" style="cursor: pointer;"onclick="supprimerNotificationDossier(${dossier.id})"></i>
  </div>

      `;
    });
  }


  // === Afficher les sous-dossiers renommés ===
  dropdown.innerHTML += '<hr>';

  if (!data.renamefolders || data.renamefolders.length === 0) {
    dropdown.innerHTML += '<div class="dropdown-item">Aucun sous-dossier renommé</div>';
  } else {
    data.renamefolders.forEach(folder => {
      const utilisateur = folder.updated_by ? folder.updated_by.name : 'Utilisateur inconnu';
      const nouveauNom = folder.name ? folder.name.trim() : 'Nom inconnu';
      const typePrincipal = folder.type_folder ? folder.type_folder.trim().toLowerCase() : 'Type inconnu';
      const dateModif = formatDate(folder.updated_at);
      const folderId = folder.folder_id || 0;

      let url = '#';

      if (!folderId || folderId === 0) {
        switch (typePrincipal) {
          case 'achat': url = '/achat'; break;
          case 'vente': url = '/vente'; break;
          case 'banque': url = '/banque'; break;
          case 'paie': url = '/paie'; break;
          case 'impot': url = '/impot'; break;
          case 'dossier_permanant': url = '/Dossier_permanant'; break;
          default:
            if (folder.dossier && folder.dossier.id) {
              url = `/Douvrir/${folder.dossier.id}`; // 🔄 modifié ici
            } else {
              url = '#';
            }
        }
      } else {
        switch (typePrincipal) {
          case 'achat': url = `/folder/${folderId}`; break;
          case 'vente': url = `/foldersVente1/${folderId}`; break;
          case 'banque': url = `/foldersBanque1/${folderId}`; break;
          case 'paie': url = `/foldersPaie1/${folderId}`; break;
          case 'impot': url = `/foldersImpot1/${folderId}`; break;
          case 'dossier_permanant': url = `/foldersDossierPermanant1/${folderId}`; break;
          default:
            if (folder.dossier && folder.dossier.id) {
              url = `/dasousdossier/${folder.dossier.id}`; // 🔄 modifié ici
            } else {
              url = '#';
            }
        }
      }

      dropdown.innerHTML += `
        <div class="dropdown-item d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">

        <a href="${url}" class="dropdown-item" style="text-decoration: none; color: inherit; position: relative; display: block;">
          <strong>${utilisateur}</strong> a renommé un sous-dossier. Nouveau nom :
          <strong>${nouveauNom}</strong> dans le dossier principal <strong>${typePrincipal}</strong><br>
          <small>${dateModif}</small>
        
        </a>
            <i class="fas fa-trash-alt text-danger ms-2" style="cursor: pointer;" onclick="supprimerNotificationSousDossier(${folder.id})"></i>
  </div>

      `;
    });
  }


        // Afficher le menu
        dropdown.style.display = "block";
      })
      .catch(error => {
        console.error("Erreur lors du chargement :", error);
        dropdown.innerHTML = '<div class="dropdown-item">Erreur de chargement</div>';
        dropdown.style.display = "block";
      });
  }

  // Fermer si on clique ailleurs
  window.onclick = function(event) {
    if (!event.target.matches('.notification-icon')) {
      const dropdown = document.getElementById("notificationDropdown");
      if (dropdown.style.display === "block") {
        dropdown.style.display = "none";
      }
    }
  };

  // Formater la date en FR (ex : 29 juillet 2025 à 13:45)
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

        if (dropdownList.style.display === "none" || dropdownList.style.display === "") {

            dropdownList.style.display = "block"; // Afficher la liste

        } else {

            dropdownList.style.display = "none"; // Cacher la liste

        }

    });
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
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        toggleDropdown();
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Notification supprimée !',
          showConfirmButton: false,
          timer: 1500
        });
      })
      .catch(error => console.error(error));
    }
  });
}

function supprimerNotificationDossier(id) {
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
      fetch('/notifications/supprimer-notification-dossier/' + id, {
        method: 'PUT',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        toggleDropdown();
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Notification supprimée !',
          showConfirmButton: false,
          timer: 1500
        });
      })
      .catch(error => console.error(error));
    }
  });
}

function supprimerNotificationSolde(id) {
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
      fetch('/notifications/supprimer-notification-solde/' + id, {
        method: 'PUT',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        toggleDropdown();
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Notification supprimée !',
          showConfirmButton: false,
          timer: 1500
        });
      })
      .catch(error => console.error(error));
    }
  });
}

function supprimerNotificationFichier(id) {
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
      fetch('/notifications/supprimer-notification-fichier/' + id, {
        method: 'PUT',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        toggleDropdown();
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Notification supprimée !',
          showConfirmButton: false,
          timer: 1500
        });
      })
      .catch(error => console.error(error));
    }
  });
}

function supprimerNotificationSousDossier(id) {
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
      fetch('/notifications/supprimer-notification-sous-dossier/' + id, {
        method: 'PUT',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        toggleDropdown();
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Notification supprimée !',
          showConfirmButton: false,
          timer: 1500
        });
      })
      .catch(error => console.error(error));
    }
  });
}



</script>



