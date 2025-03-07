@extends('layouts.user_type.guest')

@section('content')

<div id="loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: white; display: flex; justify-content: center; align-items: center; z-index: 9999;">
    <video autoplay muted loop style="max-width: 100%; max-height: 100%; object-fit: contain;">
        <source src="{{ asset('vidio/SupcomptaAnimation.mp4') }}" type="video/mp4">
        Votre navigateur ne prend pas en charge la vidéo.
    </video>
</div>


  <main class="main-content mt-0">
    <section>
      <div class="page-header min-vh-75">
        <div class="container">
          <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
              <div class="card card-plain mt-8">
                <div class="card-header pb-0 text-left bg-transparent">
                <h3 class="font-weight-bolder" style="color: #cb0c9f;">Bienvenue</h3>
                </div>
                <div class="card-body">
                  <form role="form" method="POST" action="/session" id="loginForm">
                    @csrf
                    <label>Nom Complet</label>
                    <div class="mb-3">
                      <input type="text" class="form-control" name="name" id="name" placeholder="nom" value="" aria-label="name" aria-describedby="name-addon">
                      @error('name')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                      @enderror
                    </div>
                    <label>Mot de passe</label>
                    <div class="mb-3">
                      <input type="password" class="form-control" name="password" id="password" placeholder="Password" value="secret" aria-label="Password" aria-describedby="password-addon">
                      @error('password')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                      @enderror
                    </div>

                    <!-- Initially hidden select -->
                    <div class="mb-3" id="databaseSelectContainer" style="display: none;">
                      <select name="database" class="form-control" id="databaseSelect">
                        
                      <option value="">choisire une option</option>
                        @foreach ($dbNames as $dbName)
                       
                            <option value="{{ $dbName }}">{{ $dbName }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="rememberMe" checked=""/>
                      <label class="form-check-label" for="rememberMe">Souvenez-vous de moi</label>
                    </div>
                    <div class="text-center">
                       <button type="submit" class="btn" style="background-color: #cb0c9f; color: white; width: 100%; margin-top: 1rem; margin-bottom: 0;">Connexion</button>

                    </div>
                  </form>
                </div>
                <!-- <div class="card-footer text-center pt-0 px-lg-2 px-1">
                  <small class="text-muted">Forgot your password? Reset it 
                    <a href="/login/forgot-password" class="text-info text-gradient font-weight-bold">here</a>
                  </small>
                  <p class="mb-4 text-sm mx-auto">
                    Don't have an account?
                    <a href="register" class="text-info text-gradient font-weight-bold">Sign up</a>
                  </p>
                </div> -->
              </div>
            </div>
            <div class="col-md-6">
              <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('../assets/img/curved-images/5362163.jpg')"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
@endsection
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Attendre 3 secondes (3000 ms) avant de masquer l'animation et afficher la page de login
    setTimeout(function() {
      document.getElementById('loader').style.display = 'none';  // Cacher l'animation
      document.getElementById('loginPage').style.display = 'block';  // Afficher la page de login
    }, 3000);  // Délai de 3 secondes
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('name');
    const passwordInput = document.getElementById('password');
    const databaseSelectContainer = document.getElementById('databaseSelectContainer');
    
    // Function to check credentials
    function checkCredentials() {
      const name = nameInput.value;
      const password = passwordInput.value;
      
      // Check if the name and password match the correct values
      if (name === 'COMPULOG_CHABAANE' && password === 'compulog123') {
        // Show the select element if credentials are correct
        databaseSelectContainer.style.display = 'block';
      } else {
        // Hide the select element if credentials are incorrect
        databaseSelectContainer.style.display = 'none';
      }
    }

    // Listen to changes in the name and password fields
    nameInput.addEventListener('input', checkCredentials);
    passwordInput.addEventListener('input', checkCredentials);
    
    // Initial check in case the user has pre-filled the fields
    checkCredentials();
  });
</script>
