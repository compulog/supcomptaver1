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
        @if (\Request::is('rtl'))  
            @include('layouts.navbars.auth.sidebar-rtl')
            <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg overflow-hidden">
                @include('layouts.navbars.auth.nav-rtl')
                <div class="container-fluid py-4">
                    @yield('content')
                    @include('layouts.footers.auth.footer') 
                </div>
            </main>

        @elseif (\Request::is('profile'))  
            @include('layouts.navbars.auth.sidebar')
            <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
                @include('layouts.navbars.auth.nav1')
                @yield('content')
            </div>

        @elseif (\Request::is('virtual-reality')) 
            @include('layouts.navbars.auth.nav1')
            <div class="border-radius-xl mt-3 mx-3 position-relative" style="background-image: url('../assets/img/vr-bg.jpg') ; background-size: cover;">
                @include('layouts.navbars.auth.sidebar')
                <main class="main-content mt-1 border-radius-lg">
                    @yield('content')
                </main>
            </div>
            @include('')

        @else
        <div id="sidebar" class="sidebar">
                @include('layouts.navbars.auth.sidebar')
            </div>
            <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg {{ (Request::is('rtl') ? 'overflow-hidden' : '') }}">
                <nav class="navbar">
                    <button id="menuToggle" class="navbar-toggler d-none d-lg-block" type="button" style="padding: 0; border: none; background: transparent;">
                    <p style="font-size:15px;color:black;">Menu</p> 
                    </button>
                    @include('layouts.navbars.auth.nav1')
                </nav>
                <div id="overlay" class="overlay"></div>
                <div id="sidebar" class="sidebar">
                    <!-- Contenu de la sidebar ici -->
                </div>
                <div class="container-fluid py-4">
                    @yield('content')
                    @include('layouts.footers.auth.footer')
                </div>
            </main>
        @endif

        @include('components.fixed-plugin')
    @endif

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
