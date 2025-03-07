@extends('layouts.user_type.auth')

@section('content')
  <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
    <div class="container-fluid">
      <div class="page-header min-height-300 border-radius-xl mt-4" style="background-image: url('../assets/img/curved-images/analysis-graphs.jpg'); background-position-y: 50%;">
        <span class="mask bg-gradient-primary opacity-6"></span>
      </div>
      <div class="card card-body blur shadow-blur mx-4 mt-n6 overflow-hidden">
        <div class="row gx-4">
          <div class="col-auto my-auto">
            <div class="h-100">
              <!-- Nom de l'utilisateur statique -->
              <h5 class="mb-1">
                John Doe
              </h5>
              <!-- Rôle de l'utilisateur statique -->
              <p class="mb-0 font-weight-bold text-sm">
                CEO / Co-Founder
              </p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
            <div class="nav-wrapper position-relative end-0">
              <!-- Contenu additionnel si nécessaire -->
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12 col-xl-4">
          <!-- Informations utilisateur statiques -->
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">Informations utilisateur</h6>
            </div>
            <div class="card-body">
              <ul class="list-unstyled">
                <li><strong>ID:</strong> 1</li>
                <li><strong>Email:</strong> john.doe@example.com</li>
                <li><strong>Numéro de téléphone:</strong> +1234567890</li>
                <li><strong>Localisation:</strong> New York, USA</li>
                <li><strong>A propos:</strong> Je suis un entrepreneur passionné par la technologie et l'innovation.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    @include('layouts.footers.auth.footer') 
  </div>
@endsection
