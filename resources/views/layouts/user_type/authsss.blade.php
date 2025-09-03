@extends('layouts.app')

@section('auth')

            @if(auth()->check())  <!-- Vérifie si un utilisateur est connecté -->
                @if(auth()->user()->type === 'interlocuteurs') <!-- Condition si l'utilisateur est un interlocuteur -->
                <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg {{ (Request::is('rtl') ? 'overflow-hidden' : '') }}">

                <div id="overlay" class="overlay"></div>
                        <div class="container-fluid py-4">
                           
                            @include('layouts.navbars.auth.navprofil')    
                            @yield('content')
                         </div>
                          </main>
                @else
                    <div id="sidebar" class="sidebar">
                        @include('layouts.navbars.auth.sidebar') <!-- Inclure la sidebar par défaut -->
                    </div>
                    <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg {{ (Request::is('rtl') ? 'overflow-hidden' : '') }}">
                        <nav class="navbar" style="background-color:#ffffff; z-index: 1030;">
                            @if(isset($societe))         
                                <button id="menuToggle" class="navbar-toggler d-none d-lg-block" style="padding: 0; border: none; background: transparent;display: flex; align-items: center; border: 1px solid black; border-radius: 5px; transition: background-color 0.3s;">
 <i class="fas fa-bars" style="font-size: 20px; color: black; padding: 5px;"></i>
    
    <!-- Texte Menu -->
    <span id="menuText" style="font-size: 15px; color: black;">Menu</span>                                </button>
                            @endif
                            @include('layouts.navbars.auth.nav1') <!-- Navbar par défaut -->
                        </nav>
                        <div id="overlay" class="overlay"></div>
                        <div class="container-fluid py-4">
                            @yield('content')
                            @include('layouts.footers.auth.footer')
                        </div>
                    </main>
                @endif
         
        @endif

        @include('components.fixed-plugin')
   

    <script>
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

    <style>
        /* Style de la navbar sans box-shadow */
        .navbar {
            box-shadow: none; /* Retirer l'ombre de la navbar */
        }

        /* Style de la sidebar */
        #sidebar {
            display: none; /* Initialement cachée */
            position: fixed;
            top: 0;
            left: -250px; /* Cachée sur la gauche */
            height: 100%;
            width: 250px;
            background-color: #333;
            color: white;
            transition: all 0.3s ease-in-out;
         }

        /* Quand la sidebar est ouverte, elle est décalée à gauche à 0 et visible */
        .sidebar-open {
            left: 0;
            display: block !important;
        }

        /* Lorsque la sidebar est ouverte, on réduit la largeur du contenu principal */
        .main-content.sidebar-open {
            margin-left: 250px;
         }

        /* Style du bouton de menu */
        #menuToggle {
            border: none;
            background: transparent;
            font-size: 30px;
            cursor: pointer;
            color: rgba(0,0,0,0);
        }

        /* Style de l'overlay (fond semi-transparent) */
        .overlay {
            display: none; /* Initialement caché */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
         }

        /* Quand l'overlay est activé, il devient visible */
        .overlay-open {
         }
    </style>

@endsection
