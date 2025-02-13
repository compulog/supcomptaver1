@extends('layouts.user_type.auth')

@section('content')
<!-- Placer le script jQuery avant le vôtre -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Votre script personnalisé -->

<meta name="csrf-token" content="{{ csrf_token() }}">
 
<div class="container mt-4">
    <h6>Tableau De Board</h6>
    <div class="row">
            <div class="col-md-3 mb-3">
            <div class="card shadow-sm" style="border-radius: 15px; font-size: 0.75rem; height: 130px;" onclick="openCreateFolderForm()">
                <div class="card-body text-center d-flex flex-column justify-content-center align-items-center" style="height: 100%; background-color: #f8f9fa; border-radius: 15px;">
                    <i class="fas fa-plus fa-2x " style="color:#007bff;"></i>
                    <p class="mt-1" style="font-size: 0.8rem;">Ajouter un Dossier</p>
                </div>
            </div>
        </div>
        <!-- Achat -->
        <div class="col-md-3 mb-3" id="achat-div">
            <div class="p-2 text-white" style="background-color: #007bff; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Achat</h5>

                    
                    <form id="form-achat" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="achat">
                        <input type="file" name="file" id="file-achat" style="display: none;" onchange="handleFileSelect(event, 'achat')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <input type="hidden" name="folders_id" value="0">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #007bff; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-achat').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-achat">Envoyer</button>
                    </form>

                     
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['Achat'] ?? 0 }}</p>     
                           <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : </p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : </p>
            </div>
        </div>

        <!-- Vente -->
        <div class="col-md-3 mb-3" id="vente-div">
            <div class="p-2 text-white" style="background-color: #28a745; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Vente</h5>
                    <form id="form-vente" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="vente">
                        <input type="file" name="file" id="file-vente" style="display: none;" onchange="handleFileSelect(event, 'vente')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #28a745; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-vente').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-vente">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['Vente'] ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : </p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : </p>
            </div>
        </div>

        <!-- Banque -->
        <div class="col-md-3 mb-3" id="banque-div">
            <div class="p-2 text-white" style="background-color: #ffc107; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Banque</h5>
                    <form id="form-banque" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="banque">
                        <input type="file" name="file" id="file-banque" style="display: none;" onchange="handleFileSelect(event, 'banque')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #ffc107; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-banque').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-banque">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['Banque'] ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : </p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : </p>
            </div>
        </div>

</div>

    <!-- Deuxième ligne avec 2 divs -->
    <div class="row">
 <!-- Caisse -->
 <div class="col-md-3 mb-3" id="caisse-div">
            <div class="p-2 text-white" style="background-color: #dc3545; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Caisse</h5>
                    <form id="form-caisse" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="caisse">
                        <input type="file" name="file" id="file-caisse" style="display: none;" onchange="handleFileSelect(event, 'caisse')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #dc3545; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-caisse').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-caisse">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['Caisse'] ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : </p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : </p>
            </div>
        </div>
    
        <!-- Impôt -->
        <div class="col-md-3 mb-3" id="impot-div">
            <div class="p-2 text-white" style="background-color: #6f42c1; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Impôt</h5>
                    <form id="form-impot" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="impot">
                        <input type="file" name="file" id="file-impot" style="display: none;" onchange="handleFileSelect(event, 'impot')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #6f42c1; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-impot').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-impot">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['impot'] ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces générées : </p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : </p>
            </div>
        </div>

        <!-- Paie -->
        <div class="col-md-3 mb-3" id="paie-div">
            <div class="p-2 text-white" style="background-color: #17a2b8; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Paie</h5>
                    <form id="form-paie" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="paie">
                        <input type="file" name="file" id="file-paie" style="display: none;" onchange="handleFileSelect(event, 'paie')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #17a2b8; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-paie').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-paie">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['Paie'] ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces générées : </p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : </p>
            </div>
        </div>

   <!-- Dossier_permanant -->
        <div class="col-md-3 mb-3" id="Dossier_permanant-div">
            <div class="p-2 text-white" style="background-color:rgb(221, 232, 17); border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;font-size:12px;">Dossier permanant</h5>
                         <form id="form-dossier_permanant" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="dossier_permanant">
                    <input type="file" name="file" id="file-dossier_permanant" style="display: none;" onchange="handleFileSelect(event, 'dossier_permanant')">
                    <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                    
                    <input type="hidden" name="folders_id" value="0">

                    <!-- Charger Button -->
                     <button type="button" class="btn btn-light btn-sm" style="background-color: rgb(221, 232, 17); border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-dossier_permanant').click()">Charger</button>

                    <!-- Submit Button (hidden initially) -->
                    <button type="submit" style="display: none;" id="submit-vente">Envoyer</button>
                </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $fileCounts['Dossier_permanant'] ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces générées : </p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : </p>
            </div>

        
        </div>


        
    </div>

    <div class="row">
    @foreach($dossiers as $dossier)
        <div class="col-md-3 mb-3">
            <div class="p-2 text-white dossier-box" style="border-radius: 15px; font-size: 0.75rem; height: 130px;" data-id="{{ $dossier->id }}">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Affichage du nom du dossier -->
                    <h5 style="color: white; font-size: 12px;">{{ $dossier->name }}</h5>

                    <div class="dropdown">
                        <button class="btn btn-link text-white" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i> <!-- Icône des trois points -->
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item" href="#" onclick="openEditFolderModal('{{ $dossier->id }}', '{{ $dossier->name }}')">
                                    Renommer
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('dossier.delete', $dossier->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item" style="background: transparent; border: none; color: red;">
                                        Supprimer
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>

                    <!-- Formulaire pour charger un fichier -->
                    @csrf
                    <form id="form-{{ $dossier->id }}" action="{{ route('Douvrir.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <input type="hidden" name="folder_type" value="{{ $dossier->name }}"> 

                        <input type="file" name="file" id="file-{{ $dossier->id }}" style="display: none;" onchange="handleFileSelect(event, {{ $dossier->id }})">
                        <button type="button" class="btn btn-light btn-sm dossier-button" style="border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-{{ $dossier->id }}').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-{{ $dossier->id }}">Envoyer</button>
                    </form>

                </div>

 
                <!-- Affichage du nombre de fichiers dans le dossier -->
                <p style="font-size: 0.7rem; line-height: 0.3;">Total fichiers : {{ $dossierFileCounts[$dossier->id] ?? 0 }}</p>  
                <p style="font-size: 0.7rem; line-height: 0.3;">Pièces traitées : </p>
                <p style="font-size: 0.7rem; line-height: 0.3;">Pièces suspendues : </p>
            </div>
        </div>
    @endforeach
</div>

</div>
 
<!-- Modal pour créer un dossier -->
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createFolderModalLabel">Créer un Nouveau Dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form action="{{ route('dossier.store') }}" method="POST">
    @csrf
    <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">

    <div class="mb-3">
        <label for="folderName" class="form-label">Nom du Dossier</label>
        <input type="text" class="form-control" id="folderName" name="name" required>
    </div>
    <button type="submit" class="btn" style="background-color:#007bff; color: white;">Créer Dossier</button>    </form>


            </div>
        </div>
    </div>
</div>


 
<!-- Modal pour modifier un dossier -->
<div class="modal fade" id="editFolderModal" tabindex="-1" aria-labelledby="editFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFolderModalLabel">Renommer Dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form action="" method="POST" id="edit-folder-form">
    @csrf
    @method('PUT')
    <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
    <input type="hidden" name="dossier_id" id="dossier_id">
    <div class="mb-3">
        <label for="folderName" class="form-label">Nom du Dossier</label>

        <input type="text" class="form-control" id="folderName" name="name" placeholder="{{ $dossier->name ?? 'Nom du dossier' }}" required>    </div>
    <button type="submit" class="btn " style="background-color:#007bff; color: white;">Renommer Dossier</button>
</form>

            </div>
        </div>
    </div>
</div>




</div>
 
<script>

document.addEventListener('DOMContentLoaded', function () {
  // Ajout des événements de double-clic pour toutes les sections
  document.getElementById('achat-div').addEventListener('dblclick', function () {
      window.location.href = '{{ route("achat.view") }}';
  });

  document.getElementById('vente-div').addEventListener('dblclick', function () {
      window.location.href = '{{ route("vente.view") }}';
  });

  document.getElementById('banque-div').addEventListener('dblclick', function () {
      window.location.href = '{{ route("banque.view") }}';
  });

  document.getElementById('caisse-div').addEventListener('dblclick', function () {
      window.location.href = '{{ route("caisse.view") }}';
  });

  document.getElementById('impot-div').addEventListener('dblclick', function () {
      window.location.href = '{{ route("impot.view") }}';
  });

  document.getElementById('paie-div').addEventListener('dblclick', function () {
      window.location.href = '{{ route("paie.view") }}';
  });
  document.getElementById('Dossier_permanant-div').addEventListener('dblclick', function () {
      window.location.href = '{{ route("Dossier_permanant.view") }}';
  });
});
function handleFileSelect(event, type) {
const fileInput = document.getElementById(`file-${type.toLowerCase()}`);
const formId = `form-${type.toLowerCase()}`;  // Générer l'ID du formulaire

if (!fileInput.files.length) {
  alert("Veuillez sélectionner un fichier.");
  return;
}

// Soumettre le formulaire si un fichier est sélectionné
document.getElementById(formId).submit();
}


function openCreateFolderForm() {
var myModal = new bootstrap.Modal(document.getElementById('createFolderModal'));
myModal.show();
}

document.addEventListener('DOMContentLoaded', function () {
// Ajout des événements de double-clic pour chaque dossier dynamique
document.querySelectorAll('.dossier-box').forEach(function(div) {
  div.addEventListener('dblclick', function () {
      // On récupère l'ID du dossier à partir de l'attribut data-id
      const dossierId = div.getAttribute('data-id');
      
      // On redirige vers la route avec l'ID du dossier
      window.location.href = `/Douvrir/${dossierId}`;  // Assurez-vous que la route correspond bien à celle définie dans les routes Laravel
  });
});
});


</script>
<script src="{{ asset('js/exercices.js') }}"></script>

@endsection





