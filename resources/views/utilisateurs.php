<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fournisseurs avec Validation et Modale</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
  <h2>Choisir un Fournisseur</h2>
  <div class="mb-3">
    <label for="fournisseur-select" class="form-label">Fournisseurs</label>
    <select id="fournisseur-select" class="form-select">
      <option value="">-- Sélectionnez un fournisseur --</option>
      <option value="1">Fournisseur A</option>
      <option value="2">Fournisseur B</option>
      <option value="3">Fournisseur C</option>
    </select>
    <button id="create-fournisseur" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addSupplierModal">Créer un Fournisseur</button>
  </div>
</div>

<!-- Modale Bootstrap -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addSupplierModalLabel">Ajouter un Fournisseur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="add-supplier-form">
          <div class="mb-3">
            <label for="supplier-name" class="form-label">Nom du Fournisseur</label>
            <input type="text" id="supplier-name" class="form-control" placeholder="Entrez le nom du fournisseur">
            <div id="error-message" class="text-danger mt-2" style="display: none;">Ce fournisseur existe déjà.</div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" id="save-supplier" class="btn btn-primary">Ajouter</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function () {
    // Initialisation de Select2
    $('#fournisseur-select').select2({
      placeholder: "-- Sélectionnez ou recherchez un fournisseur --",
      allowClear: true
    });

    // Ajouter un fournisseur
    $('#save-supplier').click(function () {
      const supplierName = $('#supplier-name').val().trim();
      const select = $('#fournisseur-select');
      const exists = !!select.find(`option`).filter(function () {
        return $(this).text().toLowerCase() === supplierName.toLowerCase();
      }).length;

      if (!supplierName) {
        $('#error-message').text("Le nom du fournisseur ne peut pas être vide.").show();
        return;
      }

      if (exists) {
        $('#error-message').text("Ce fournisseur existe déjà.").show();
        return;
      }

      // Ajouter le fournisseur à la liste Select2
      const newOption = new Option(supplierName, supplierName, true, true);
      select.append(newOption).trigger('change');
      
      // Fermer la modale
      $('#addSupplierModal').modal('hide');
      
      // Réinitialiser le formulaire
      $('#supplier-name').val('');
      $('#error-message').hide();

      alert(`Fournisseur "${supplierName}" ajouté avec succès !`);
    });

    // Réinitialiser les messages d'erreur à chaque ouverture de la modale
    $('#addSupplierModal').on('show.bs.modal', function () {
      $('#error-message').hide();
      $('#supplier-name').val('');
    });
  });
</script>
</body>
</html>
