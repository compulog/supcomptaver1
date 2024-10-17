@extends('layouts.user_type.auth')

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg ">

  <!-- Formulaire de saisie -->
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h3>Ajouter un client</h3>

        <!-- Tabs pour choisir entre saisie manuelle et importation Excel -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link active" id="manual-tab" data-bs-toggle="tab" href="#manual" role="tab" aria-controls="manual" aria-selected="true">Nouveau client</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link" id="import-tab" data-bs-toggle="tab" href="#import" role="tab" aria-controls="import" aria-selected="false">Importer un fichier Excel</a>
          </li>
        </ul>

        <!-- Contenu des Tabs -->
        <div class="tab-content" id="myTabContent">
          
          <!-- Formulaire de saisie manuelle -->
          <div class="tab-pane fade show active" id="manual" role="tabpanel" aria-labelledby="manual-tab">
            <form id="form-saisie-manuel" class="mt-3">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="compte" class="form-label">Compte</label>
                  <input type="text" class="form-control" id="compte" name="compte" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="intitule" class="form-label">Intitulé</label>
                  <input type="text" class="form-control" id="intitule" name="intitule" required>
                </div>
                <!-- Modifié pour mettre Identifiant fiscal et Type client sur la même ligne -->
                <div class="col-md-6 mb-3">
                  <label for="identifiant_fiscal" class="form-label">Identifiant fiscal</label>
                  <input type="text" class="form-control" id="identifiant_fiscal" name="identifiant_fiscal" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="type_client" class="form-label">Type client</label>
                  <input type="text" class="form-control" id="type_client" name="type_client" required>
                </div>
              </div>

              <div class="row">
                <!-- Modifié pour mettre ICE et Désignation sur la même ligne -->
                <div class="col-md-6 mb-3">
                  <label for="ice" class="form-label">ICE</label>
                  <input type="text" class="form-control" id="ice" name="ice" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="designation" class="form-label">Désignation</label>
                  <input type="text" class="form-control" id="designation" name="designation" required>
                </div>
              </div>

              <button type="submit" class="btn btn-primary">Ajouter</button>
            </form>
          </div>

          <!-- Formulaire d'importation Excel avec les mêmes champs que pour la saisie manuelle -->
          <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
            <form id="form-import-excel" class="mt-3">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="compte-import" class="form-label">Compte</label>
                  <input type="text" class="form-control" id="compte-import" name="compte-import" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="intitule-import" class="form-label">Intitulé</label>
                  <input type="text" class="form-control" id="intitule-import" name="intitule-import" required>
                </div>
                <!-- Modifié pour mettre Identifiant fiscal et Type client sur la même ligne -->
                <div class="col-md-6 mb-3">
                  <label for="identifiant_fiscal-import" class="form-label">Identifiant fiscal</label>
                  <input type="text" class="form-control" id="identifiant_fiscal-import" name="identifiant_fiscal-import" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="type_client-import" class="form-label">Type client</label>
                  <input type="text" class="form-control" id="type_client-import" name="type_client-import" required>
                </div>
              </div>

              <div class="row">
                <!-- Modifié pour mettre ICE et Désignation sur la même ligne -->
                <div class="col-md-6 mb-3">
                  <label for="ice-import" class="form-label">ICE</label>
                  <input type="text" class="form-control" id="ice-import" name="ice-import" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="designation-import" class="form-label">Désignation</label>
                  <input type="text" class="form-control" id="designation-import" name="designation-import" required>
                </div>
              </div>

              <div class="mb-3">
                <label for="excel-file" class="form-label">Importer un fichier Excel</label>
                <input type="file" class="form-control" id="excel-file" name="excel-file" accept=".xlsx, .xls" required>
              </div>
              <button type="submit" class="btn btn-primary">Importer</button>
            </form>
          </div>
          
        </div>
        
      </div>
    </div>
  </div>

  <!-- Tableau avec recherche -->
  <div class="container mt-4">
    <div class="row">
      <div class="col-md-12">
        <h3>Liste des clients</h3>
        <input class="form-control mb-3" id="searchInput" type="text" placeholder="Rechercher...">

        <table class="table table-bordered" id="table-list">
          <thead>
            <tr>
              <th>Compte</th>
              <th>Intitulé</th>
              <th>Identifiant fiscal</th>
              <th>ICE</th>
              <th>Type client</th>
              <th>Désignation</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="table-body">
            <!-- Les entrées ajoutées par le formulaire apparaîtront ici -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

</main>

@endsection

@push('scripts')
<script>
  // Fonction pour ajouter une ligne dans le tableau (saisie manuelle)
  document.getElementById('form-saisie-manuel').addEventListener('submit', function(event) {
    event.preventDefault();

    const compte = document.getElementById('compte').value;
    const intitule = document.getElementById('intitule').value;
    const identifiantFiscal = document.getElementById('identifiant_fiscal').value;
    const typeClient = document.getElementById('type_client').value;
    const ice = document.getElementById('ice').value;
    const designation = document.getElementById('designation').value;

    if (compte && intitule && identifiantFiscal && typeClient && ice && designation) {
      const tableBody = document.getElementById('table-body');
      const newRow = document.createElement('tr');

      const compteCell = document.createElement('td');
      compteCell.textContent = compte;
      newRow.appendChild(compteCell);

      const intituleCell = document.createElement('td');
      intituleCell.textContent = intitule;
      newRow.appendChild(intituleCell);

      const identifiantFiscalCell = document.createElement('td');
      identifiantFiscalCell.textContent = identifiantFiscal;
      newRow.appendChild(identifiantFiscalCell);

      const iceCell = document.createElement('td');
      iceCell.textContent = ice;
      newRow.appendChild(iceCell);

      const typeClientCell = document.createElement('td');
      typeClientCell.textContent = typeClient;
      newRow.appendChild(typeClientCell);

      const designationCell = document.createElement('td');
      designationCell.textContent = designation;
      newRow.appendChild(designationCell);

      const actionCell = document.createElement('td');
      actionCell.innerHTML = `
        <button class="btn btn-warning btn-sm me-2 edit-btn">Modifier</button>
        <button class="btn btn-danger btn-sm delete-btn">Supprimer</button>
      `;
      newRow.appendChild(actionCell);

      tableBody.appendChild(newRow);
      document.getElementById('form-saisie-manuel').reset();
    }
  });

  // Fonction pour importer un fichier Excel (ajouter la logique pour l'importer ici)
  document.getElementById('form-import-excel').addEventListener('submit', function(event) {
    event.preventDefault();

    const fileInput = document.getElementById('excel-file');
    const file = fileInput.files[0];

    if (file) {
      alert('Fichier Excel importé avec succès!');
      document.getElementById('form-import-excel').reset();
    } else {
      alert('Veuillez sélectionner un fichier Excel');
    }
  });

  // Fonction de recherche dans le tableau
  document.getElementById('searchInput').addEventListener('input', function(event) {
    const filter = event.target.value.toLowerCase();
    const rows = document.querySelectorAll('#table-body tr');

    rows.forEach(row => {
      const cells = row.getElementsByTagName('td');
      const compte = cells[0].textContent.toLowerCase();
      const intitule = cells[1].textContent.toLowerCase();
      const identifiantFiscal = cells[2].textContent.toLowerCase();
      const typeClient = cells[4].textContent.toLowerCase();
      const designation = cells[5].textContent.toLowerCase();

      if (compte.includes(filter) || intitule.includes(filter) || identifiantFiscal.includes(filter) || typeClient.includes(filter) || designation.includes(filter)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });

  // Fonction pour gérer la modification et suppression des lignes
  document.addEventListener('click', function(event) {
    if (event.target && event.target.classList.contains('edit-btn')) {
      const row = event.target.closest('tr');
      const cells = row.cells;
      document.getElementById('compte').value = cells[0].textContent;
      document.getElementById('intitule').value = cells[1].textContent;
      document.getElementById('identifiant_fiscal').value = cells[2].textContent;
      document.getElementById('type_client').value = cells[4].textContent;
      document.getElementById('ice').value = cells[3].textContent;
      document.getElementById('designation').value = cells[5].textContent;

      row.remove();
    }

    if (event.target && event.target.classList.contains('delete-btn')) {
      const row = event.target.closest('tr');
      row.remove();
    }
  });
</script>
@endpush
