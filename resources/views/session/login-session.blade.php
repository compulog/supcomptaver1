@extends('layouts.user_type.guest')

@section('content')
  <main class="main-content mt-0">
    <section>
      <div class="page-header min-vh-75">
        <div class="container">
          <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
              <div class="card card-plain mt-8">
                <div class="card-header pb-0 text-left bg-transparent">
                  <h3 class="font-weight-bolder text-info text-gradient">Welcome back</h3>
                </div>
                <div class="card-body">
                  <form role="form" method="POST" action="/session" id="loginForm">
                    @csrf
                    <label>Email</label>
                    <div class="mb-3">
                      <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="admin@softui.com" aria-label="Email" aria-describedby="email-addon">
                      @error('email')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                      @enderror
                    </div>
                    <label>Password</label>
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
                      <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    <div class="text-center">
                      <button type="submit" class="btn bg-gradient-info w-100 mt-4 mb-0">Sign in</button>
                    </div>
                  </form>
                </div>
                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                  <small class="text-muted">Forgot your password? Reset it 
                    <a href="/login/forgot-password" class="text-info text-gradient font-weight-bold">here</a>
                  </small>
                  <p class="mb-4 text-sm mx-auto">
                    Don't have an account?
                    <a href="register" class="text-info text-gradient font-weight-bold">Sign up</a>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('../assets/img/curved-images/tabletop.jpg')"></div>
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
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const databaseSelectContainer = document.getElementById('databaseSelectContainer');
    
    // Function to check credentials
    function checkCredentials() {
      const email = emailInput.value;
      const password = passwordInput.value;
      
      // Check if the email and password match the correct values
      if (email === 'compulog@gmail.com' && password === 'compulog123') {
        // Show the select element if credentials are correct
        databaseSelectContainer.style.display = 'block';
      } else {
        // Hide the select element if credentials are incorrect
        databaseSelectContainer.style.display = 'none';
      }
    }

    // Listen to changes in the email and password fields
    emailInput.addEventListener('input', checkCredentials);
    passwordInput.addEventListener('input', checkCredentials);
    
    // Initial check in case the user has pre-filled the fields
    checkCredentials();
  });
</script>
