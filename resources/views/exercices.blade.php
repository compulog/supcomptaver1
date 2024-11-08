@extends('layouts.user_type.auth')

@section('content')

@php
    $totalAchatFiles = \App\Models\File::where('type', 'Achat')->count();
    $totalVenteFiles = \App\Models\File::where('type', 'Vente')->count();
    $totalBanqueFiles = \App\Models\File::where('type', 'Banque')->count();
    $totalCaisseFiles = \App\Models\File::where('type', 'Caisse')->count();
    $totalImpotFiles = \App\Models\File::where('type', 'Impot')->count();
    $totalPaieFiles = \App\Models\File::where('type', 'Paie')->count();
@endphp

<div class="container mt-4">
    <div class="row">
        <!-- Achat -->
        <div class="col-md-4 mb-3" ondblclick="handleDoubleClick('achat')">
            <div class="p-2 text-white" style="background-color: #007bff; border-radius: 5px; font-size: 0.75rem; height: 155px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>Achat</h5>
                    <form id="form-achat" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Achat">
                        <input type="file" name="file" id="file-achat" style="display: none;" onchange="handleFileSelect(event, 'Achat')">
                        <button type="button" class="btn btn-light btn-sm" onclick="document.getElementById('file-achat').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-achat">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem;">total pièces : 5</p>
                <p style="font-size: 0.7rem;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem;">5 pièces suspendues : 2</p>
            </div>
        </div>



        <!-- Vente -->
        <div class="col-md-4 mb-3">
            <div class="p-2 text-white" style="background-color: #28a745; border-radius: 5px; font-size: 0.75rem; height: 155px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>Vente</h5>
                    <!-- Formulaire pour charger le fichier -->
                    <form id="form-vente" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Vente">
                        <input type="file" name="file" id="file-vente" style="display: none;" onchange="handleFileSelect(event, 'Vente')">
                        <button type="button" class="btn btn-light btn-sm" onclick="document.getElementById('file-vente').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-vente">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem;">total pièces : 5</p>
                <p style="font-size: 0.7rem;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem;">5 pièces suspendues : 2</p>
            </div>
        </div>

        <!-- Banque -->
        <div class="col-md-4 mb-3">
            <div class="p-2 text-white" style="background-color: #ffc107; border-radius: 5px; font-size: 0.75rem; height: 155px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>Banque</h5>
                    <!-- Formulaire pour charger le fichier -->
                    <form id="form-banque" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Banque">
                        <input type="file" name="file" id="file-banque" style="display: none;" onchange="handleFileSelect(event, 'Banque')">
                        <button type="button" class="btn btn-light btn-sm" onclick="document.getElementById('file-banque').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-banque">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem;">total pièces : 5</p>
                <p style="font-size: 0.7rem;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem;">5 pièces suspendues : 2</p>
            </div>
        </div>

        <!-- Caisse -->
        <div class="col-md-4 mb-3">
            <div class="p-2 text-white" style="background-color: #dc3545; border-radius: 5px; font-size: 0.75rem; height: 155px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>Caisse</h5>
                    <!-- Formulaire pour charger le fichier -->
                    <form id="form-caisse" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Caisse">
                        <input type="file" name="file" id="file-caisse" style="display: none;" onchange="handleFileSelect(event, 'Caisse')">
                        <button type="button" class="btn btn-light btn-sm" onclick="document.getElementById('file-caisse').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-caisse">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem;">total pièces : 5</p>
                <p style="font-size: 0.7rem;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem;">5 pièces suspendues : 2</p>
            </div>
        </div>

       <!-- Impôt -->
<div class="col-md-4 mb-3">
    <div class="p-2 text-white" style="background-color: #6f42c1; border-radius: 5px; font-size: 0.75rem; height: 155px;">
        <div class="d-flex justify-content-between align-items-center">
            <h5>Impôt</h5>
            <!-- Formulaire pour charger le fichier -->
            <form id="form-impot" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="Impot">
                <input type="file" name="file" id="file-impot" style="display: none;" onchange="handleFileSelect(event, 'Impot')">
                <button type="button" class="btn btn-light btn-sm" onclick="document.getElementById('file-impot').click()">Charger</button>
                <button type="submit" style="display: none;" id="submit-impot">Envoyer</button>
            </form>
        </div>
        <p style="font-size: 0.7rem;">total pièces : 5</p>
        <p style="font-size: 0.7rem;">pièces traitées : 3</p>
        <p style="font-size: 0.7rem;">5 pièces suspendues : 2</p>
    </div>
</div>


        <!-- Paie -->
        <div class="col-md-4 mb-3">
            <div class="p-2 text-white" style="background-color: #17a2b8; border-radius: 5px; font-size: 0.75rem; height: 155px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>Paie</h5>
                    <!-- Formulaire pour charger le fichier -->
                    <form id="form-paie" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Paie">
                        <input type="file" name="file" id="file-paie" style="display: none;" onchange="handleFileSelect(event, 'Paie')">
                        <button type="button" class="btn btn-light btn-sm" onclick="document.getElementById('file-paie').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-paie">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem;">total pièces : 5</p>
                <p style="font-size: 0.7rem;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem;">5 pièces suspendues : 2</p>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
function handleFileSelect(event, type) {
    const formId = `form-${type.toLowerCase()}`;  // Générer l'ID du formulaire
    document.getElementById(formId).submit();  // Soumettre le formulaire
}

// Déclaration de la variable pour gérer les double-clics
let lastClickTime = 0;

function handleDoubleClick(type) {
    const now = new Date().getTime();

    // Si le délai entre les clics est inférieur à 500ms, c'est un double clic
    if (now - lastClickTime < 500) {
        // Redirection après le double clic
        switch (type) {
            case 'achat':
                window.location.href = "{{ route('achat.view') }}"; // Redirection vers la page d'achat
                break;
            case 'vente':
                window.location.href = "{{ route('vente.view') }}"; // Redirection vers la page de vente
                break;
            case 'banque':
                window.location.href = "{{ route('banque.view') }}"; // Redirection vers la page de banque
                break;
            case 'caisse':
                window.location.href = "{{ route('caisse.view') }}"; // Redirection vers la page de caisse
                break;
            case 'impot':
                window.location.href = "{{ route('impot.view') }}"; // Redirection vers la page d'impôt
                break;
            case 'paie':
                window.location.href = "{{ route('paie.view') }}"; // Redirection vers la page de paie
                break;
            default:
                console.log("Type non défini");
                break;
        }
    }

    lastClickTime = now; // Mettre à jour l'heure du dernier clic
}
</script>
