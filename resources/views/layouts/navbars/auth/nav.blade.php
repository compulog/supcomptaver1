<head>
    <style>
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

        /* Navbar contenant le nom de la société et l'exercice */
        .navbar-content {
            display: flex;
            justify-content: space-between; /* Espacement entre les éléments */
            align-items: center; /* Centrer verticalement */
            width: 100%;
        }

        /* Alignement du texte pour la société */
        .breadcrumb {
            display: inline-block;
            margin-right: 20px;
            text-align: left;
        }

        /* Exercice aligné à droite */
        .exercice {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .exercice input {
            height: 20px;
        }

        .exercice button {
            border-radius: 50%;
            padding: 10px;
            height: 25px;
            width: 25px;
            margin-top: 12px;
        }

        .exercice span {
            margin-right: 10px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <div class="navbar-content">
                <!-- Nom de la société -->
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5" style="margin-top:-30px;">
                    <li class="breadcrumb-item text-sm active text-capitalize" aria-current="page">
                        {{ $societe->raison_sociale }} {{ $societe->forme_juridique }}
                    </li>
                </ol>

                <!-- Section Exercice -->
                <div class="exercice" style="margin-top:-25px;">
                    <span>Exercice:</span>

                    <!-- Flèches pour changer l'exercice -->
                    <button type="button" class="btn btn-light">
                        <i class="fas fa-arrow-left" style="font-size:8px;"></i>
                    </button>

                    <!-- Sélection des dates -->
                    Du <input type="date" value="{{ $societe->exercice_social_debut }}">
                    au <input type="date" value="{{ $societe->exercice_social_fin }}">

                    <button type="button" class="btn btn-light">
                        <i class="fas fa-arrow-right" style="font-size:8px;"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Autres éléments de la navbar -->
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 d-flex justify-content-end" id="navbar">
            <!-- Utilisateur connecté -->
            <div class="nav-link " style="margin-right:-10px;margin-top:-21px;">
                <span class="nav-link-text ms-1">{{ Auth::user()->name }}</span>
            </div>

                 
<!-- Liste déroulante avec icône -->
<li class="nav-item d-flex align-items-center" style="position: relative;margin-top:-20px;">
    <a href="javascript:;" class="nav-link text-white p-0" id="dropdownListButton">
        <i class="fas fa-user-circle" style="font-size:22px;color:black;"></i>  
    </a>

    <!-- Vérification du type d'utilisateur et affichage du menu approprié -->
    @if(Auth::user()->type === 'SuperAdmin')
    <div class="dropdown-list" id="dropdownList">
       

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
            <span class="d-sm-inline d-none">Sign Out</span>
        </a>
    </div>
    
    @elseif(Auth::user()->type === 'admin')
    <div class="dropdown-list" id="dropdownList">
       <a class="nav-link {{ (Request::is('Admin') ? 'active' : '') }}" href="{{ url('Admin') }}">
            <i class="fas fa-cogs"></i>  
            <span class="nav-link-text ms-1">Admin</span>  
        </a>
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
            <span class="d-sm-inline d-none">Sign Out</span>
        </a>
    </div>
    @elseif(Auth::user()->type === 'utilisateur')
    <div class="dropdown-list" id="dropdownList">
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
            <span class="d-sm-inline d-none">Sign Out</span>
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
            <span class="d-sm-inline d-none">Sign Out</span>
        </a>
    </div>
    @endif
</li>

        </div>
    </div>
</nav>

<!-- Script pour afficher/cacher la liste -->
<script>
    document.getElementById('dropdownListButton').addEventListener('click', function() {
        var dropdownList = document.getElementById('dropdownList');
        if (dropdownList.style.display === "none" || dropdownList.style.display === "") {
            dropdownList.style.display = "block"; // Afficher la liste
        } else {
            dropdownList.style.display = "none"; // Cacher la liste
        }
    });
</script>
