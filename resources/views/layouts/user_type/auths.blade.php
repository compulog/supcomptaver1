

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
    
   
        @extends('layouts.navbars.auth.navdashboard')


        @elseif (\Request::is('profile'))  
            
            <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
               
                @yield('content')
            </div>

        @elseif (\Request::is('virtual-reality')) 
         
            <div class="border-radius-xl mt-3 mx-3 position-relative" style="background-image: url('../assets/img/vr-bg.jpg') ; background-size: cover;">
                @include('layouts.navbars.auth.sidebar')
                <main class="main-content mt-1 border-radius-lg">
                    @yield('content')
                </main>
            </div>
            @include('')

        @else
           
            <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg {{ (Request::is('rtl') ? 'overflow-hidden' : '') }}">
                <div class="container-fluid py-4">
                    @yield('content')
                   @include('layouts.footers.auth.footer')
                </div>
            </main>
        @endif
        

        @include('components.fixed-plugin')
  

    

@endsection