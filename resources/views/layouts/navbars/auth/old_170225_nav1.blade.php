<head>
    <style>
        .dropdown-list {
            display: none; /* Initially hide the list */
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            position: absolute; /* To place it below the icon */
            right: 0; /* Align it to the right of the parent */
            top: 40px; /* Adjust according to your layout */
            z-index: 10; /* Ensure it appears above other elements */
        }

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
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">

  <div class="container-fluid py-1 px-3">
                 <!-- Icône et texte "supcompta" sur la même ligne -->
                <a class="align-items-center d-flex m-0 navbar-brand text-wrap" href="{{ route('dashboard') }}" style="padding:20px;">
                    <img src="../assets/img/acc.png" class="navbar-brand-img h-100" alt="..." style="width:30px">
                    <span class="ms-3 font-weight-bold" style="font-size:20px">supcompta</span>
                </a>

                <!-- Nom de la société -->
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                    <li class="breadcrumb-item text-sm active text-capitalize" aria-current="page">
                        {{ $societe->raison_sociale }}
                         <!-- {{ $societe->forme_juridique }} -->
                    </li>
                </ol>

        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 d-flex justify-content-end" id="navbar"> 
            <div class="nav-link">
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

        <!-- Sign Out link with icon -->
        <a href="{{ url('/logout')}}" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> <!-- Sign out icon -->
            <span class="d-sm-inline d-none">Sign Out</span>
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

        <!-- Sign Out link with icon -->
        <a href="{{ url('/logout')}}" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> <!-- Sign out icon -->
            <span class="d-sm-inline d-none">Sign Out</span>
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

        <!-- Sign Out link with icon -->
        <a href="{{ url('/logout')}}" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> <!-- Sign out icon -->
            <span class="d-sm-inline d-none">Sign Out</span>
        </a>
    </div>

    @else
    <div class="dropdown-list" id="dropdownList">
        <!-- Mon Profil link with icon (visible pour tous les utilisateurs) -->
        <a class="nav-link {{ (Request::is('user-profile') ? 'active' : '') }} " href="{{ url('user-profile') }}">
            <i class="fas fa-user"></i> <!-- Profile icon -->
            <span class="nav-link-text ms-1">Mon Profil</span> 
        </a>

        <!-- Sign Out link with icon -->
        <a href="{{ url('/logout')}}" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> <!-- Sign out icon -->
            <span class="d-sm-inline d-none">Sign Out</span>
        </a>
    </div>
    @endif
</li>

            
           
        </div>
    </div>
</nav>

<!-- Script to toggle dropdown visibility -->
<script>
    document.getElementById('dropdownListButton').addEventListener('click', function() {
        var dropdownList = document.getElementById('dropdownList');
        // Toggle display of the dropdown list
        if (dropdownList.style.display === "none" || dropdownList.style.display === "") {
            dropdownList.style.display = "block"; // Show the list
        } else {
            dropdownList.style.display = "none"; // Hide the list
        }
    });

    // Optional: Hide the dropdown if clicked outside
    window.addEventListener('click', function(event) {
        var dropdownList = document.getElementById('dropdownList');
        var button = document.getElementById('dropdownListButton');
        if (!button.contains(event.target) && !dropdownList.contains(event.target)) {
            dropdownList.style.display = "none"; // Hide the list if clicked outside
        }
    });
</script>
