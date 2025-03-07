@extends('layouts.user_type.authsss')

@section('content')


<div>
    <div class="container-fluid">
        <div class="page-header min-height-300 border-radius-xl mt-4" style="background-image: url('../assets/img/curved-images/analysis-graphs.jpg'); background-position-y: 50%;">
            <span class="mask bg-gradient-primary opacity-6"></span>
        </div>
        <div class="card card-body blur shadow-blur mx-4 mt-n6">
            <div class="row gx-4">
                <div class="col-auto">
                    <div class="avatar avatar-xl position-relative">
                        <!-- Image actuelle ou image par défaut -->
                        <!-- <img id="profileImage" src="{{ auth()->user()->profile_image ? asset('storage/profile_images/' . auth()->user()->profile_image) : '../assets/img/bruce-mars.jpg' }}" alt="..." class="w-100 border-radius-lg shadow-sm"> -->

                        
                        <!-- Bouton pour ouvrir le dialogue de sélection d'image -->
                        <!-- <input type="file" id="imageInput" name="profile_image" style="display:none;" onchange="previewImage(event)">
                        <a href="javascript:;" class="btn btn-sm btn-icon-only bg-gradient-light position-absolute bottom-0 end-0 mb-n2 me-n2" onclick="document.getElementById('imageInput').click();">
                            <i class="fa fa-pen top-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Image"></i>
                        </a> -->
                    </div>
                </div>
                <div class="col-auto my-auto">
                    <div class="h-100">
                        <h5 class="mb-1">
                            {{ auth()->user()->name }}  <!-- Utilisation du nom de l'utilisateur -->
                        </h5>
                        <p class="mb-0 font-weight-bold text-sm">
                            {{ auth()->user()->about_me }}  <!-- Utilisation de "About Me" pour l'utilisateur -->
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
                    <div class="nav-wrapper position-relative end-0">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header pb-0 px-3">
                <h6 class="mb-0">{{ __('Profile Information') }}</h6>
            </div>
            <div class="card-body pt-4 p-3">
            <form action="/user-profile" method="POST" role="form text-left" enctype="multipart/form-data">

                    @csrf
                    @if($errors->any())
                        <div class="mt-3  alert alert-primary alert-dismissible fade show" role="alert">
                            <span class="alert-text text-white">
                            {{$errors->first()}}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <i class="fa fa-close" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="m-3  alert alert-success alert-dismissible fade show" id="alert-success" role="alert">
                            <span class="alert-text text-white">
                            {{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <i class="fa fa-close" aria-hidden="true"></i>
                            </button>
                        </div>
                    @endif

                    <!-- Form fields for Name, Email, etc. -->
                    <div class="row">
                        <div class="">
                            <div class="form-group">
                                <label for="user-name" class="form-control-label">{{ __('Nom Complet') }}</label>
                                <div class="@error('user.name')border border-danger rounded-3 @enderror">
                                    <input class="form-control" value="{{ auth()->user()->name }}" type="text" placeholder="Name" id="user-name" name="name">
                                    @error('name')
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user-raw_password" class="form-control-label">{{ __('Mot DE Passe') }}</label>
                                <div class="@error('user.raw_password')border border-danger rounded-3 @enderror">
                                    <input class="form-control" value="{{ auth()->user()->raw_password }}" type="text" placeholder="raw_password" id="user-raw_password" name="raw_password">
                                    @error('raw_password')
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>



                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user-email" class="form-control-label">{{ __('Email') }}</label>
                                <div class="@error('email')border border-danger rounded-3 @enderror">
                                    <input class="form-control" value="{{ auth()->user()->email }}" type="email" placeholder="@example.com" id="user-email" name="email">
                                    @error('email')
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user.phone" class="form-control-label">{{ __('Telephone') }}</label>
                                <div class="@error('user.phone')border border-danger rounded-3 @enderror">
                                    <input class="form-control" type="tel" placeholder="40770888444" id="number" name="phone" value="{{ auth()->user()->phone }}">
                                    @error('phone')
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user.location" class="form-control-label">{{ __('Adresse') }}</label>
                                <div class="@error('user.location') border border-danger rounded-3 @enderror">
                                    <input class="form-control" type="text" placeholder="" id="name" name="location" value="{{ auth()->user()->location }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="about">{{ 'A propos de moi' }}</label>
                        <div class="@error('user.about')border border-danger rounded-3 @enderror">
                            <textarea class="form-control" id="about" rows="3" placeholder="" name="about_me">{{ auth()->user()->about_me }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn bg-gradient-dark btn-md mt-4 mb-4">{{ 'Modifier' }}</button>
                        <a href="{{ route('exercices.show', ['societe_id' => $societeId]) }}" class="btn btn-secondary btn-md mt-4 mb-4 ms-2">Retourner</a>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
    // Fonction pour prévisualiser l'image avant de la télécharger
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('profileImage');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
