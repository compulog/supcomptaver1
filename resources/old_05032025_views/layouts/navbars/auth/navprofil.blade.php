<head>
    <style>
       

        .dropdown-list a {
            display: flex;
            align-items: center; /* Align icons and text */
            padding: 10px;
            text-decoration: none;
            color: black;
        }

        .dropdown-list a:hover {
            background-color: #f1f1f1;
        }

        .dropdown-list i {
            margin-right: 10px; /* Space between icon and text */
        }
        
     .dropdown-list {
        display: none; /* Initially hide the list */
        background-color: white;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 200px;
        max-height: 300px; /* Hauteur maximale */
        overflow-y: auto; /* Ajoute un défilement vertical si nécessaire */
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        position: fixed; /* Changez à fixed */
        right: 20px; /* Ajustez selon votre mise en page */
        top: 60px; /* Ajustez selon votre mise en page */
        z-index: 10; /* Ensure it appears above other elements */
    }
  
        
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">

  <div class="container-fluid py-1 px-3">
                 <!-- Icône et texte "supcompta" sur la même ligne -->
               
              
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 d-flex justify-content-end" id="navbar"> 
            <div class="nav-link" style="margin-left: 750px;">
                <span class="nav-link-text ms-1"> {{ Auth::user()->name }}</span>
            </div>


<!-- Liste déroulante avec icône -->
<li class="nav-item d-flex align-items-center" style="position: relative;">
    <a href="javascript:;" class="nav-link text-white p-0" id="dropdownListButton">
        <i class="fas fa-user-circle" style="font-size:22px;color:black;"></i>  
    </a>

    <!-- Vérification du type d'utilisateur et affichage du menu approprié -->
    @if(Auth::user()->type === 'SuperAdmin')
    <div class="dropdown-list" id="dropdownList">
       

        <!-- Utilisateurs link with icon -->
        <a class="nav-link {{ (Request::is('utilisateurs') ? 'active' : '') }}" href="{{ url('utilisateurs') }}">
            <i class="fas fa-users"></i> <!-- Users icon -->
            <span class="nav-link-text ms-1">Utilisateurs</span>  
        </a>

     

        <!-- Mon Profil link with icon -->
        <a class="nav-link {{ (Request::is('user-profile') ? 'active' : '') }} " href="{{ url('user-profile') }}">
            <i class="fas fa-user"></i> <!-- Profile icon -->
            <span class="nav-link-text ms-1">Mon Profil</span> 
        </a>

        <!-- Déconnexion link with icon -->
        <a href="{{ url('/logout')}}" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> <!-- Déconnexion icon -->
            <span class="d-sm-inline d-none">Déconnexion</span>
        </a>
    </div>
    
    @elseif(Auth::user()->type === 'Admin')
    <div class="dropdown-list" id="dropdownList">
      <!-- Admin link with icon -->
      <a class="nav-link {{ (Request::is('Admin') ? 'active' : '') }}" href="{{ url('Admin') }}">
            <i class="fas fa-cogs"></i> <!-- Admin icon -->
            <span class="nav-link-text ms-1">Admin</span>  
        </a>
     <!-- interlocuteurs link with icon -->
     <a class="nav-link {{ (Request::is('interlocuteurs') ? 'active' : '') }}" href="{{ url('interlocuteurs') }}">
            <i class="fas fa-cogs"></i> <!-- interlocuteurs icon -->
            <span class="nav-link-text ms-1">interlocuteurs</span>  
        </a>
        <!-- Mon Profil link with icon -->
        <a class="nav-link {{ (Request::is('user-profile') ? 'active' : '') }} " href="{{ url('user-profile') }}">
            <i class="fas fa-user"></i> <!-- Profile icon -->
            <span class="nav-link-text ms-1">Mon Profil</span> 
        </a>

        <!-- Déconnexion link with icon -->
        <a href="{{ url('/logout')}}" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> <!-- Déconnexion icon -->
            <span class="d-sm-inline d-none">Déconnexion</span>
        </a>
    </div>
    @elseif(Auth::user()->type === 'utilisateur')
    <div class="dropdown-list" id="dropdownList">
      <!-- interlocuteurs link with icon -->
      <a class="nav-link {{ (Request::is('interlocuteurs') ? 'active' : '') }}" href="{{ url('interlocuteurs') }}">
            <i class="fas fa-cogs"></i> <!-- interlocuteurs icon -->
            <span class="nav-link-text ms-1">interlocuteurs</span>  
        </a>

        <!-- Mon Profil link with icon -->
        <a class="nav-link {{ (Request::is('user-profile') ? 'active' : '') }} " href="{{ url('user-profile') }}">
            <i class="fas fa-user"></i> <!-- Profile icon -->
            <span class="nav-link-text ms-1">Mon Profil</span> 
        </a>

        <!-- Déconnexion link with icon -->
        <a href="{{ url('/logout')}}" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> <!-- Déconnexion icon -->
            <span class="d-sm-inline d-none">Déconnexion</span>
        </a>
    </div>

    @else
    <div class="dropdown-list" id="dropdownList">
        <!-- Mon Profil link with icon (visible pour tous les utilisateurs) -->
        <a class="nav-link {{ (Request::is('user-profile') ? 'active' : '') }} " href="{{ url('user-profile') }}">
            <i class="fas fa-user"></i> <!-- Profile icon -->
            <span class="nav-link-text ms-1">Mon Profil</span> 
        </a>

        <!-- Déconnexion link with icon -->
        <a href="{{ url('/logout')}}" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> <!-- Déconnexion icon -->
            <span class="d-sm-inline d-none">Déconnexion</span>
        </a>
    </div>
    @endif
</li>

            
           
        </div>
    </div>
</nav>

<!-- Script to toggle dropdown visibility -->
<script>
    document.getElementById('dropdownListButton').addEventListener('click', function(event) {
        event.preventDefault(); // Empêche le comportement par défaut
        var dropdownList = document.getElementById('dropdownList');
        // Toggle display of the dropdown list
        if (dropdownList.style.display === "none" || dropdownList.style.display === "") {
            dropdownList.style.display = "block"; // Show the list
            document.body.style.overflow = 'hidden'; // Empêche le défilement de la page
        } else {
            dropdownList.style.display = "none"; // Hide the list
            document.body.style.overflow = ''; // Rétablit le défilement de la page
        }
    });

    // Optional: Hide the dropdown if clicked outside
    window.addEventListener('click', function(event) {
        var dropdownList = document.getElementById('dropdownList');
        var button = document.getElementById('dropdownListButton');
        if (!button.contains(event.target) && !dropdownList.contains(event.target)) {
            dropdownList.style.display = "none"; // Hide the list if clicked outside
            document.body.style.overflow = ''; // Rétablit le défilement de la page
        }
    });
</script>
