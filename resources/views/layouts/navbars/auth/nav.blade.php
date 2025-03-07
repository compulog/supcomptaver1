<head>

    <style>

        /* Navbar content alignée horizontalement */

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

<nav class="navbar navbar-expand-lg navbar-dark px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">



  <div class="container-fluid py-1 px-3">

        <nav aria-label="breadcrumb">

            <div class="navbar-content">

                <!-- Icône et texte "supcompta" sur la même ligne -->

                <a class="align-items-center d-flex m-0 navbar-brand text-wrap" href="{{ route('dashboard') }}">

                    <img src="../assets/img/acc.png" class="navbar-brand-img h-100" alt="..." style="width:30px">

                    <span class="ms-3 font-weight-bold" style="font-size:20px">supcompta</span>

                </a>



                <!-- Nom de la société -->

                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">

                    <li class="breadcrumb-item text-sm active text-capitalize" aria-current="page">

                        {{ $societe->raison_sociale }}

                        {{ $societe->forme_juridique }} 

                    </li>

                </ol>

@if(Auth::user()->type === 'interlocuteurs')



<!-- Exercice -->

<div class="exercice">

  <span>Exercice:</span>

 

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

  <span>Exercice:</span>

 

  <button type="button" class="btn-arrow left">

    <div class="arrow-left"></div>

  </button>



  <div class="date-range">

    Du <input type="date" value="{{ $societe->exercice_social_debut }}">

    au <input type="date" value="{{ $societe->exercice_social_fin }}">

  </div>



 

  <button type="button" class="btn-arrow right">

    <div class="arrow-right"></div>

  </button>

</div>

@endif

<style>

  /* Style global de l'exercice */

.exercice {

 display: flex;

 margin-left:-9%;

  gap: 15px;

  font-family: Arial, sans-serif;

  color: #333;

}



.exercice span {

  font-weight: bold;

  font-size: 16px;

}



.date-range input {

  font-size: 14px;

  padding: 5px;

  border: 1px solid #ccc;

  border-radius: 5px;

  background-color: #f9f9f9;

}



.date-range input:focus {

  outline: none;

  border-color: #007bff;

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

  border-left: 15px solid #007bff; /* Flèche bleue */

}



/* Flèche gauche */

.arrow-left {

  width: 0;

  height: 0;

  border-top: 5px solid transparent;

  border-bottom: 5px solid transparent;

  border-right: 15px solid #007bff; /* Flèche bleue inversée */

}



</style>



                </div>

            </div>

        </nav>



        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 d-flex justify-content-end" id="navbar">

            <div class="nav-link" style="margin-right:-10px;margin-top:-55px;">

                <span class="nav-link-text ms-1">{{ Auth::user()->name }}</span>

            </div>



            <!-- Liste déroulante -->

            <li class="nav-item d-flex align-items-center" style="position: relative;margin-top:-50px;">

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



