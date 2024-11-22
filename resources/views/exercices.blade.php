@extends('layouts.user_type.auth')

@section('content')

<div class="container mt-4">
    <div class="row">
        <!-- Achat -->
        <div class="col-md-3 mb-3" id="achat-div">
            <div class="p-2 text-white" style="background-color: #007bff; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Achat</h5>
                    <form id="form-achat" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Achat">
                        <input type="file" name="file" id="file-achat" style="display: none;" onchange="handleFileSelect(event, 'Achat')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">

                        <button type="button" class="btn btn-light btn-sm" style="background-color: #007bff; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-achat').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-achat">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $achatCount ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : 2</p>
            </div>
        </div>

        <!-- Vente -->
        <div class="col-md-3 mb-3" id="vente-div">
            <div class="p-2 text-white" style="background-color: #28a745; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Vente</h5>
                    <form id="form-vente" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Vente">
                        <input type="file" name="file" id="file-vente" style="display: none;" onchange="handleFileSelect(event, 'Vente')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #28a745; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-vente').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-vente">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $venteCount ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : 2</p>
            </div>
        </div>

        <!-- Banque -->
        <div class="col-md-3 mb-3" id="banque-div">
            <div class="p-2 text-white" style="background-color: #ffc107; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Banque</h5>
                    <form id="form-banque" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Banque">
                        <input type="file" name="file" id="file-banque" style="display: none;" onchange="handleFileSelect(event, 'Banque')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #ffc107; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-banque').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-banque">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $banqueCount ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : 2</p>
            </div>
        </div>

        <!-- Caisse -->
        <div class="col-md-3 mb-3" id="caisse-div">
            <div class="p-2 text-white" style="background-color: #dc3545; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Caisse</h5>
                    <form id="form-caisse" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Caisse">
                        <input type="file" name="file" id="file-caisse" style="display: none;" onchange="handleFileSelect(event, 'Caisse')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #dc3545; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-caisse').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-caisse">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $caisseCount ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : 2</p>
            </div>
        </div>
    </div>

    <!-- Deuxième ligne avec 2 divs -->
    <div class="row">
        <!-- Impôt -->
        <div class="col-md-3 mb-3" id="impot-div">
            <div class="p-2 text-white" style="background-color: #6f42c1; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Impôt</h5>
                    <form id="form-impot" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Impot">
                        <input type="file" name="file" id="file-impot" style="display: none;" onchange="handleFileSelect(event, 'Impot')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #6f42c1; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-impot').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-impot">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $impotCount ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces générées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 2</p>
            </div>
        </div>

        <!-- Paie -->
        <div class="col-md-3 mb-3" id="paie-div">
            <div class="p-2 text-white" style="background-color: #17a2b8; border-radius: 15px; font-size: 0.75rem; height: 130px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Paie</h5>
                    <form id="form-paie" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Paie">
                        <input type="file" name="file" id="file-paie" style="display: none;" onchange="handleFileSelect(event, 'Paie')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #17a2b8; border: 1px solid white; border-radius: 10px; color: white; width:100px;" onclick="document.getElementById('file-paie').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-paie">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $paieCount ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces générées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 2</p>
            </div>
        </div>
    </div>

</div>


@endsection



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




</script>





