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

<nav class="navbar d-flex fixed-top" style="background-color:#ffffff; z-index: 1030;">

                  



                    {{-- Assurez-vous que l'utilisateur est connecté et que son type n'est pas "interlocuteurs" --}}

@if(auth()->check() && auth()->user()->type !== 'interlocuteurs')

<button id="menuToggle" class="navbar-toggler" type="button" style="padding: 0; border: none; background: transparent;display: flex; align-items: center; border: 1px solid black; border-radius: 5px; transition: background-color 0.3s;">
    <!-- Icône -->
    <i class="fas fa-bars" style="font-size: 20px; color: black; padding: 5px;"></i>
    
    <!-- Texte Menu -->
    <span id="menuText" style="font-size: 15px; color: black;">Menu</span>
</button>

<!-- Style CSS pour le survol -->
<style>
    #menuToggle:hover {
        background-color: black;
    }

    #menuToggle:hover #menuText {
        color: white;
    }

    #menuToggle:hover .fas {
        color: white;
    }

    #menuToggle:hover  , #menuToggle:hover span {
        background-color: white;
    }
</style>


@endif





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
    document.getElementById("menuToggle").addEventListener("click", function () {
        document.getElementById("sidebar").classList.add("sidebar-open");
        document.getElementById("overlay").classList.add("overlay-open");
    });

    document.getElementById("overlay").addEventListener("click", function () {
        document.getElementById("sidebar").classList.remove("sidebar-open");
        document.getElementById("overlay").classList.remove("overlay-open");
    });
</script>




    <!-- Styles CSS pour cacher/montrer la sidebar -->

    <style>

        /* Cacher la sidebar par défaut */
 
 /* Cacher la sidebar par défaut */
#sidebar {
    margin-top:3.5%;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 290px;
    /* background-color: #ffffff; */
    background-color: #211C84;
    color: white;
    z-index: 1050;
    transform: translateX(-100%); /* On la déplace hors de l’écran */
    transition: transform 0.3s ease-in-out;
}

/* Quand elle est ouverte */
#sidebar.sidebar-open {
    transform: translateX(0); /* Elle revient à l’écran */
}

/* Overlay (fond sombre) */
#overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 100vw;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1049;
}

/* Quand l'overlay est actif */
#overlay.overlay-open {
    display: block;
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

            color: rgba(0,0,0,0);

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

