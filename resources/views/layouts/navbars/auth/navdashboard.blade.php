<!-- Navbar -->
<style>
         .dropdown-list {
            display: none; /* Cacher la liste par défaut */
           
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .dropdown-list a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: black;
        }
        .dropdown-list a:hover {
            background-color: #f1f1f1;
        }
    
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
<nav class="navbar navbar-expand-lg navbar-dark px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <!-- <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
                <li class="breadcrumb-item text-sm text-white active text-capitalize" aria-current="page">{{ str_replace('-', ' ', Request::path()) }}</li>
            </ol> -->
            <!-- <img src="/assets/img/SUPCOMPTA.png" alt="Animation" style="max-width: 30%; max-height: 20%; object-fit: contain;"> -->
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 d-flex justify-content-end" id="navbar"> 
            <div class="nav-item d-flex align-self-end">
            <!-- <a href="{{ route('dashboard') }}" target="_blank" class="btn btn-primary active mb-0 text-white" role="button" aria-pressed="true">
   ACCEUIL
</a> -->

            </div>
            <!-- <div class="ms-md-3 pe-md-3 d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text text-body bg-dark border-light"><i class="fas fa-search" aria-hidden="true"></i></span>
                    <input type="text" class="form-control bg-dark text-white border-light" placeholder="Type here...">
                </div>
            </div> -->
            <!-- <ul class="navbar-nav justify-content-end">
                
                <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-bell cursor-pointer"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md" href="javascript:;">
                                <div class="d-flex py-1">
                                    <div class="my-auto">
                                        <img src="../assets/img/team-2.jpg" class="avatar avatar-sm me-3 ">
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            <span class="font-weight-bold">New message</span> 
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">
                                            <i class="fa fa-clock me-1"></i>
                                            13 minutes ago
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md" href="javascript:;">
                                <div class="d-flex py-1">
                                    <div class="my-auto">
                                        <img src="../assets/img/small-logos/logo-spotify.svg" class="avatar avatar-sm bg-gradient-dark me-3 ">
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                       
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item border-radius-md" href="javascript:;">
                                <div class="d-flex py-1">
                                    <div class="avatar avatar-sm bg-gradient-secondary me-3 my-auto">
                                        <svg width="12px" height="12px" viewBox="0 0 43 36" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                            <title>credit-card</title>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(-2169.000000, -745.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                                    <g transform="translate(1716.000000, 291.000000)">
                                                    <g transform="translate(453.000000, 454.000000)">
                                                        <path class="color-background" d="M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z" opacity="0.593633743"></path>
                                                        <path class="color-background" d="M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z"></path>
                                                    </g>
                                                    </g>
                                                </g>
                                            </g>
                                        </svg>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>

             

                 <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </li>

                 <li class="nav-item px-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0">
                        <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
                    </a>
                </li>
            </ul> -->
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
               
               <!-- Liste déroulante avec icône -->
                 
<!-- Liste déroulante avec icône -->
<li class="nav-item d-flex align-items-center" style="position: relative;">
                <!-- <a href="javascript:;" class="nav-link text-white p-0" id="dropdownListButton">
                    <i class="fas fa-user-circle" style="font-size:22px;color:black;"></i>
                </a> -->

                <!-- Vérification du type d'utilisateur et affichage du menu approprié -->
                @if(Auth::user()->type === 'SuperAdmin')
                <div class="dropdown-list" id="dropdownList">
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
 
</nav>
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
<!-- End Navbar -->
