@extends('layouts.app')

@section('auth')

    @if(\Request::is('static-sign-up')) 
        @include('layouts.navbars.guest.nav')
        @yield('content')
        @include('layouts.footers.guest.footer')
    
    @elseif (\Request::is('static-sign-in')) 
        @include('layouts.navbars.guest.nav')
        @yield('content')
        @include('layouts.footers.guest.footer')
    
    @else
        <div id="overlay" class="overlay"></div>

        @if (\Request::is('rtl'))  
            <div id="sidebar" class="sidebar">
                @include('layouts.navbars.auth.sidebar-rtl')
            </div>
            <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg overflow-hidden">
                @include('layouts.navbars.auth.nav-rtl')
                <div class="container-fluid py-4">
                    @yield('content')
                    @include('layouts.footers.auth.footer') 
                </div>
            </main>
        @elseif (\Request::is('profile'))  
            <div id="sidebar" class="sidebar">
                @include('layouts.navbars.auth.sidebar')
            </div>
            <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
                @include('layouts.navbars.auth.nav')
                @yield('content')
            </div>
        @elseif (\Request::is('virtual-reality')) 
            <div id="sidebar" class="sidebar">
                @include('layouts.navbars.auth.sidebar')
            </div>
            <div class="border-radius-xl mt-3 mx-3 position-relative" style="background-image: url('../assets/img/vr-bg.jpg') ; background-size: cover;">
                @include('layouts.navbars.auth.nav')
                <main class="main-content mt-1 border-radius-lg">
                    @yield('content')
                </main>
            </div>
        @else
            <div id="sidebar" class="sidebar">
                @include('layouts.navbars.auth.sidebar')
            </div>
            <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg {{ (Request::is('rtl') ? 'overflow-hidden' : '') }}">
                <nav class="navbar d-flex">
                    <!-- Conteneur de l'icône du menu qui occupe 30px -->
                    <button id="menuToggle" class="navbar-toggler" type="button" style="padding: 0; border: none; background: transparent;">
                       <p style="font-size:15px;color:black;">Menu</p> 
                    </button>
                    
                    <!-- Navbar qui prend le reste de l'espace -->
                    <div class="navbar-links flex-grow-1">
                        @include('layouts.navbars.auth.nav') 
                    </div>
                </nav>
                <div class="container-fluid py-4">
                    @yield('content')
                    @include('layouts.footers.auth.footer')
                </div>
            </main>
        @endif
        
        @include('components.fixed-plugin')
    @endif

    <!-- Script JavaScript pour afficher/masquer la sidebar -->
    <script>
        // Lorsque l'utilisateur clique sur l'icône du menu
        document.getElementById("menuToggle").addEventListener("click", function() {
            // Basculer l'affichage de la sidebar
            document.getElementById("sidebar").classList.toggle("sidebar-open");
            
            // Ajouter ou enlever l'overlay semi-transparent
            document.getElementById("overlay").classList.toggle("overlay-open");
            
            // Ajuster le style du main-content pour qu'il prenne moins de place
            document.querySelector('.main-content').classList.toggle('sidebar-open');
        });

        // Lorsque l'utilisateur clique sur l'overlay, masquer la sidebar
        document.getElementById("overlay").addEventListener("click", function() {
            document.getElementById("sidebar").classList.remove("sidebar-open");
            document.getElementById("overlay").classList.remove("overlay-open");
            document.querySelector('.main-content').classList.remove('sidebar-open');
        });
    </script>

    <!-- Styles CSS pour cacher/montrer la sidebar -->
    <style>
        /* Cacher la sidebar par défaut */
        #sidebar {
            display: none;
            position: fixed;
            top: 0;
            left: -250px; /* Sidebar cachée à gauche */
            height: 100%;
            width: 250px;
            background-color: #333; /* Fond de la sidebar */
            color: white;
            transition: all 0.3s ease-in-out; /* Effet de transition */
             
        }

        /* Sidebar ouverte : elle occupe toute la largeur de l'écran */
        .sidebar-open {
            left: 0;
            display: block !important;
        }

        /* Ajustement du contenu principal quand la sidebar est ouverte */
        .sidebar-open + .main-content {
            margin-left: 250px; /* Pousse le contenu à droite pour faire de la place */
        }

        /* Cacher le contenu principal lorsqu'il est recouvert par la sidebar */
        .main-content.sidebar-open {
         }

        /* Styles pour l'icône du menu */
        #menuToggle {
             height: 30px; /* Hauteur de 30px pour l'icône */
            padding: 0;
             background: transparent;
            font-size: 30px;
            color: #ffffff;
            cursor: pointer;
        }

        /* Conteneur flexible pour la navbar */
        .navbar {
            display: flex;
            align-items: center; /* Centrer verticalement */
            width: 99%; /* Prendre toute la largeur */
            padding: 0; /* Pas de padding pour le conteneur de la navbar */
        }

        /* Navbar links prennent le reste de l'espace */
        .navbar-links {
            flex-grow: 1; /* Prendre tout l'espace restant */
        }

        /* Styles pour la navbar sans box-shadow */
        .navbar {
            box-shadow: none; /* Supprimer la boîte d'ombre de la navbar */
            justify-content: flex-start; /* L'élément suivant sera aligné à gauche */
        }

        /* Overlay semi-transparent qui recouvre la page lorsque la sidebar est ouverte */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Fond semi-transparent */
         }

        /* Affichage de l'overlay */
        .overlay-open {
         }
    </style>

@endsection
