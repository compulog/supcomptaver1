@extends('layouts.user_type.auth')

@section('content')

<div class="container mt-4">
    <div class="row">
         <!-- Achat -->
         <div class="col-md-4 mb-3" id="achat-div">
            <div class="p-2 text-white" style="background-color: #007bff; border-radius: 15px; font-size: 0.75rem; height: 155px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Achat</h5>
                    <form id="form-achat" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Achat">
                        <input type="file" name="file" id="file-achat" style="display: none;" onchange="handleFileSelect(event, 'Achat')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">

                        <button type="button" class="btn btn-light btn-sm" style="background-color: #007bff; border: 1px solid white; border-radius: 10px; color: white;" onclick="document.getElementById('file-achat').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-achat">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $achatCount ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : 2</p>
            </div>
        </div>

        <!-- Vente -->
        <div class="col-md-4 mb-3" id="vente-div">
            <div class="p-2 text-white" style="background-color: #28a745; border-radius: 15px; font-size: 0.75rem; height: 155px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Vente</h5>
                    <form id="form-vente" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Vente">
                        <input type="file" name="file" id="file-vente" style="display: none;" onchange="handleFileSelect(event, 'Vente')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #28a745; border: 1px solid white; border-radius: 10px; color: white;" onclick="document.getElementById('file-vente').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-vente">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $venteCount ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : 2</p>
            </div>
        </div>

        <!-- Banque -->
        <div class="col-md-4 mb-3" id="banque-div">
            <div class="p-2 text-white" style="background-color: #ffc107; border-radius: 15px; font-size: 0.75rem; height: 155px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Banque</h5>
                    <form id="form-banque" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Banque">
                        <input type="file" name="file" id="file-banque" style="display: none;" onchange="handleFileSelect(event, 'Banque')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #ffc107; border: 1px solid white; border-radius: 10px; color: white;" onclick="document.getElementById('file-banque').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-banque">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $banqueCount ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : 2</p>
            </div>
        </div>

        <!-- Caisse -->
        <div class="col-md-4 mb-3" id="caisse-div">
            <div class="p-2 text-white" style="background-color: #dc3545; border-radius: 15px; font-size: 0.75rem; height: 155px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Caisse</h5>
                    <form id="form-caisse" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Caisse">
                        <input type="file" name="file" id="file-caisse" style="display: none;" onchange="handleFileSelect(event, 'Caisse')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #dc3545; border: 1px solid white; border-radius: 10px; color: white;" onclick="document.getElementById('file-caisse').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-caisse">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $caisseCount ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : 2</p>
            </div>
                <!-- Graphique Caisse -->
                <canvas id="caisseChart" style="height: 300px;"></canvas> <!-- Augmentation de la hauteur du graphique -->
       
       <!-- Graphique Banque -->
<canvas id="banqueChart" style="height: 300px;"></canvas> <!-- Augmentation de la hauteur du graphique -->
   
        </div>

        <!-- Impôt -->
        <div class="col-md-4 mb-3" id="impot-div">
            <div class="p-2 text-white" style="background-color: #6f42c1; border-radius: 15px; font-size: 0.75rem; height: 155px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Impôt</h5>
                    <form id="form-impot" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Impot">
                        <input type="file" name="file" id="file-impot" style="display: none;" onchange="handleFileSelect(event, 'Impot')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #6f42c1; border: 1px solid white; border-radius: 10px; color: white;" onclick="document.getElementById('file-impot').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-impot">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $impotCount ?? 0 }}</p>

                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : 2</p>
            </div>
            <!-- Graphique Impôt -->
            <canvas id="impotChart" style="height: 300px;"></canvas> <!-- Augmentation de la hauteur du graphique -->
     
      <!-- Graphique Vente -->
    <canvas id="venteChart" style="height: 300px;"></canvas> <!-- Augmentation de la hauteur du graphique -->
    
        </div>

        <!-- Paie -->
        <div class="col-md-4 mb-3" id="paie-div">
            <div class="p-2 text-white" style="background-color: #17a2b8; border-radius: 15px; font-size: 0.75rem; height: 155px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 style="color: white;">Paie</h5>
                    <form id="form-paie" action="{{ route('uploadFile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="Paie">
                        <input type="file" name="file" id="file-paie" style="display: none;" onchange="handleFileSelect(event, 'Paie')">
                        <input type="hidden" name="societe_id" value="{{ session()->get('societeId') }}">
                        <button type="button" class="btn btn-light btn-sm" style="background-color: #17a2b8; border: 1px solid white; border-radius: 10px; color: white;" onclick="document.getElementById('file-paie').click()">Charger</button>
                        <button type="submit" style="display: none;" id="submit-paie">Envoyer</button>
                    </form>
                </div>
                <p style="font-size: 0.7rem; line-height: 0.3;">total pièces : {{ $paieCount ?? 0 }}</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces traitées : 3</p>
                <p style="font-size: 0.7rem; line-height: 0.3;">pièces suspendues : 2</p>
            </div>
            <!-- Graphique Paie -->
            <canvas id="paieChart" style="height: 300px;"></canvas> <!-- Augmentation de la hauteur du graphique -->
       
          <!-- Graphique Achat -->
    <canvas id="achatChart" style="height: 300px;"></canvas> <!-- Augmentation de la hauteur du graphique -->
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



// Fonction de gestion des graphiques
function renderChart(chartId, labels, data) {
    const ctx = document.getElementById(chartId).getContext('2d');
    new Chart(ctx, {
        type: 'line', // Type de graphique
        data: {
            labels: labels, // Les mois ou dates
            datasets: [{
                label: 'Nombre de fichiers',
                data: data, // Nombre de fichiers téléchargés chaque mois
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                fill: false,
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { beginAtZero: true },
                y: { beginAtZero: true }
            }
        }
    });
}

// Initialisation des graphiques
document.addEventListener('DOMContentLoaded', function () {
    renderChart('achatChart', ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], [5, 3, 9, 7, 8, 6, 5, 4, 8, 6, 7, 5]);
    renderChart('venteChart', ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], [4, 6, 7, 5, 3, 8, 9, 5, 4, 6, 7, 6]);
    renderChart('banqueChart', ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], [3, 4, 2, 6, 7, 8, 5, 9, 3, 6, 5, 7]);
    renderChart('caisseChart', ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], [2, 4, 3, 5, 6, 7, 8, 9, 6, 5, 4, 8]);
    renderChart('impotChart', ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], [7, 6, 5, 3, 2, 6, 5, 4, 3, 7, 8, 6]);
    renderChart('paieChart', ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], [5, 6, 8, 4, 7, 3, 5, 8, 6, 4, 7, 5]);
});

</script>
