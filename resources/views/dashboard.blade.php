<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau des Sociétés</title>

    <!-- Tabulator CSS -->
    <link href="https://unpkg.com/tabulator-tables@5.3.2/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://unpkg.com/tabulator-tables@5.3.2/dist/js/tabulator.min.js"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<link href="https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">
<script src="https://unpkg.com/tabulator-tables/dist/js/tabulator.min.js"></script>




<!-- Chargement de Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<!-- Chargement de Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Chargement de Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <style>
        body {
            background-color: #f9f9f9; 
            color: #333;
        }
    </style>
</head>
<body>


@extends('layouts.user_type.auths')
@section('content')

<h2>Liste des Sociétés</h2>
<div class="row">
    <div class="col-12">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                 
                    <button type="button" class="btn bg-gradient-primary btn-sm mb-0" id="open-modal-btn">+&nbsp; Nouvelle société</button>

                    <button id="import-societes" class="btn bg-gradient-primary btn-sm mb-0">Importer Sociétés</button>

                    <button id="export-button" class="btn bg-gradient-primary btn-sm mb-0">Liste Des Dossiers    </button>



<script>
    document.getElementById('export-button').addEventListener('click', function() {
    window.location.href = '/export-societes';
});

</script>
                </div><br>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <div id="societes-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une nouvelle société -->
<div class="modal fade" id="nouvelleSocieteModal" tabindex="-1" aria-labelledby="nouvelleSocieteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nouvelleSocieteModalLabel">Nouvelle Société</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form id="societe-form" action="{{ route('societes.store') }}" method="POST">
            @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="raison_sociale" class="form-label">Raison sociale</label>
                            <input type="text" class="form-control" name="raison_sociale" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="forme_juridique" class="form-label">Forme Juridique</label>
                            <select class="form-control" name="forme_juridique">
                                <<option value="Null">choisir un option</option>
                                <option value="SARL">SARL</option>
                                <option value="SARL-AU">SARL-AU</option>
                                <option value="SA">SA</option>
                                <option value="SAS">SAS</option>
                                <option value="SNC">SNC</option>
                                <option value="SCS">SCS</option>
                                <option value="SCI">SCI</option>
                                <option value="SEP">SEP</option>
                                <option value="GIE">GIE</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="siege_social" class="form-label">Siège Social</label>
                            <input type="text" class="form-control" name="siege_social" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="patente" class="form-label">Patente</label>
                            <input type="text" class="form-control" name="patente" required>
                        </div>
                        <div class="col-md-6 mb-3">
    <label for="rc" class="form-label">RC</label>
    <input type="text" class="form-control" name="rc" id="rc" required 
           oninput="this.value=this.value.replace(/[^0-9]/g, '')">
</div>

                        <div class="col-md-6 mb-3">
                            <label for="centre_rc" class="form-label">Centre RC</label>
                           
                      


                        <select id="ctl00_ctl36_g_69b20002_9278_429e_be53_84b78f0af32b_ctl00_DropDownList_Ville" class="form-control" name="centre_rc" required>
                        <option value="Null">choisir un option</option>
						<option value="AGADIR">AGADIR</option>
						<option value="AL HOCEIMA">AL HOCEIMA</option>
						<option value="AZILAL">AZILAL</option>
						<option value="AZROU">AZROU</option>
						<option value="BEN AHMED">BEN AHMED</option>
						<option value="BEN GUERIR">BEN GUERIR</option>
						<option value="BENI MELLAL">BENI MELLAL</option>
						<option value="BENSLIMANE">BENSLIMANE</option>
						<option value="BERKANE">BERKANE</option>
						<option value="BERRECHID">BERRECHID</option>
						<option value="BOUARFA">BOUARFA</option>
						<option value="BOUJAAD">BOUJAAD</option>
						<option value="BOULEMANE">BOULEMANE</option>
						<option value="CASABLANCA">CASABLANCA</option>
						<option value="CHEFCHAOUEN">CHEFCHAOUEN</option>
						<option value="CHICHAOUA">CHICHAOUA</option>
						<option value="DAKHLA">DAKHLA</option>
						<option value="EL JADIDA">EL JADIDA</option>
						<option value="EL KALAA SRAGHNA">EL KALAA SRAGHNA</option>
						<option value="ERRACHIDIA">ERRACHIDIA</option>
						<option value="ES SMARA">ES SMARA</option>
						<option value="ESSAOUIRA">ESSAOUIRA</option>
						<option value="FES">FES</option>
						<option value="FIGUIG">FIGUIG</option>
						<option value="FKIH BEN SALEH">FKIH BEN SALEH</option>
						<option value="GUELMIM">GUELMIM</option>
						<option value="GUERCIF">GUERCIF</option>
						<option value="IMINTANOUTE">IMINTANOUTE</option>
						<option value="INZEGANE">INZEGANE</option>
						<option value="KASBA TADLA">KASBA TADLA</option>
						<option value="KENITRA">KENITRA</option>
						<option value="KHEMISSET">KHEMISSET</option>
						<option value="KHENIFRA">KHENIFRA</option>
						<option value="KHOURIBGA">KHOURIBGA</option>
						<option value="KSAR EL KEBIR">KSAR EL KEBIR</option>
						<option value="LAAYOUNE">LAAYOUNE</option>
						<option value="LARACHE">LARACHE</option>
						<option value="MARRAKECH">MARRAKECH</option>
						<option value="MEKNES">MEKNES</option>
						<option value="MIDELT">MIDELT</option>
						<option value="MOHAMMEDIA">MOHAMMEDIA</option>
						<option value="NADOR">NADOR</option>
						<option value="OUARZAZATE">OUARZAZATE</option>
						<option value="Oued Ed-Dahab">Oued Ed-Dahab</option>
						<option value="OUED ZEM">OUED ZEM</option>
						<option value="OUEZZANE">OUEZZANE</option>
						<option value="OUJDA">OUJDA</option>
						<option value="RABAT">RABAT</option>
						<option value="SAFI">SAFI</option>
						<option value="SALE">SALE</option>
						<option value="SEFROU">SEFROU</option>
						<option value="SETTAT">SETTAT</option>
						<option value="SIDI BENNOUR">SIDI BENNOUR</option>
						<option value="SIDI KACEM">SIDI KACEM</option>
						<option value="SIDI SLIMANE">SIDI SLIMANE</option>
						<option value="SOUK LARBAA">SOUK LARBAA</option>
						<option value="TAN TAN">TAN TAN</option>
						<option value="TANGER">TANGER</option>
						<option value="TAOUNATE">TAOUNATE</option>
						<option value="TAOURIRT">TAOURIRT</option>
						<option value="TAROUDANT">TAROUDANT</option>
						<option value="TATA">TATA</option>
						<option value="TAZA">TAZA</option>
						<option value="TEMARA">TEMARA</option>
						<option value="TETOUAN">TETOUAN</option>
						<option value="TIFELT">TIFELT</option>
						<option value="TINGHIR">TINGHIR</option>
						<option value="TIZNIT">TIZNIT</option>
						<option value="YOUSSOUFIA">YOUSSOUFIA</option>
						<option value="ZAGORA">ZAGORA</option>

					</select>
                    </div>
                        <div class="col-md-6 mb-3">
                            <label for="identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" class="form-control" name="identifiant_fiscal" required id="identifiant_fiscal" maxlength="8" title="Veuillez entrer uniquement des chiffres (max 8 chiffres)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ice" class="form-label">ICE</label>
                            <input type="text" id="ice" class="form-control" name="ice" required maxlength="15" title="Veuillez entrer uniquement des chiffres (max 15 chiffres)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_creation" class="form-label">Date de Création</label>
                            <input type="date" class="form-control" name="date_creation" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="exercice_social_debut" class="form-label">Exercice Social Début</label>
                            <input type="date" name="exercice_social_debut" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="exercice_social_fin" class="form-label">Exercice Social Fin</label>
                            <input type="date" name="exercice_social_fin" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                    <label for="modele_comptable" class="form-label">Modèle Comptable</label>
                    <select class="form-control" name="modele_comptable" id="modele_comptable" required>
                    <option value="Null">choisir un option</option>
                        <option value="Normal">Normal</option>
                        <option value="Simplifié">Simplifié</option>
                    </select>
                </div>

                        <div class="col-md-6 mb-3">
                            <label for="nombre_chiffre_compte" class="form-label">Nombre caractères Compte</label>
                            <input type="number" class="form-control" name="nombre_chiffre_compte" required>
                        </div>
                     

                        <div class="col-md-6 mb-3">
                            <label for="nature_activite" class="form-label">Nature de l'Activité</label>
                            <select class="form-control" name="nature_activite">
                            <option value="Null">choisir un option</option>
                                
                                <option value="4.Vente de biens d'équipement">Vente de biens d'équipement</option>
                                <option value="5.Vente de travaux">Vente de travaux</option>
                                <option value="6.Vente de services">Vente de services</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="activite" class="form-label">Activité</label>
                            <input type="text" class="form-control" name="activite" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <select class="form-control" name="assujettie_partielle_tva" id="assujettie_partielle_tva" required>
                            <option value="Null">choisir un option</option>
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="text" class="form-control" name="prorata_de_deduction" id="prorata_de_deduction" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="regime_declaration" class="form-label">Régime de Déclaration de TVA</label>
                         
                            <select class="form-control" name="regime_declaration" required>
                            <option value="Null">choisir un option</option>
                                <option value="Mensuel de droit commun">Mensuel de droit commun</option>
                                <option value="Trimestriel de droit commun">Trimestriel de droit commun</option>
                                <option value="Mensuel de la marge">Mensuel de la marge</option>
                                <option value="Trimestriel de la marge">Trimestriel de la marge</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fait_generateur" class="form-label">Fait Générateur</label>
                            <select class="form-control" name="fait_generateur" required>
                            <option value="Null">choisir un option</option>
                                <option value="Encaissement">Encaissement</option>
                                <option value="Débit">Débit</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="rubrique_tva">Rubrique TVA</label>
                                <select class="form-control" id="rubrique_tva"  name="rubrique_tva">
                                    <!-- Les options seront ajoutées par JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="designation" class="form-label">Désignation</label>
                            <input type="text" class="form-control" name="designation" required>
                        </div>
                  
                    </div>
                   <!-- Boutons -->
                   <div class="d-flex justify-content-end">
                            <!-- Bouton Réinitialiser avec une très grande marge droite -->
                            <button type="reset" class="btn btn-secondary me-12">
                                <i class="fas fa-undo"></i> 
                            </button>
                            <!-- Bouton Ajouter avec une très grande marge gauche -->
                            <button type="submit" class="btn btn-primary ms-12" id="ajouter-societe">
                                <i class="fas fa-check"></i> Ajouter
                            </button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // Réinitialiser le formulaire lors de l'ouverture du modal
        $('#nouvelleSocieteModal').on('show.bs.modal', function (event) {
            $('#societe-form')[0].reset();
        });

        // Lorsqu'on clique sur le bouton "Ajouter"
        $('#ajouter-societe').on('click', function(event) {
            // Sélectionner les éléments Rubrique TVA et Désignation
            const rubriqueTvaSelect = $('#rubrique_tva');
            const designationInput = $('input[name="designation"]');

            // Vérifier si Désignation est vide et si Rubrique TVA a une option sélectionnée
            if (designationInput.val().trim() === '' && rubriqueTvaSelect.val()) {
                // Mettre à jour la valeur de Désignation avec l'option Rubrique TVA sélectionnée
                designationInput.val(rubriqueTvaSelect.find('option:selected').text());
            }
        });
    });
</script>

  <script>



// $(document).ready(function() {
//     $('#nouvelleSocieteModal').on('show.bs.modal', function (event) {
//         // Réinitialiser le formulaire lors de l'ouverture du modal
//         $('#societe-form')[0].reset();
//     });
// });





    // Fonction pour remplir les options de rubrique TVA dans le select
function remplirRubriquesTva(selectId, selectedValue = null) {
  $.ajax({
      url: '/rubriques-tva?type=Achat',
      type: 'GET',
      success: function(data) {
          var select = $("#" + selectId);
          
        //   // Détruire Select2 s'il est déjà initialisé
        //   if (select.hasClass("select2-hidden-accessible")) {
        //       select.select2("destroy");
        //   }
          
          select.empty();

          let categoriesArray = [];
          $.each(data.rubriques, function(categorie, rubriques) {
              let categories = categorie.split('/').map(cat => cat.trim());
              let mainCategory = categories[0];
              let subCategory = categories[1] ? categories[1].trim() : '';
              categoriesArray.push({
                  mainCategory: mainCategory,
                  subCategory: subCategory,
                  rubriques: rubriques.rubriques
              });
          });

          categoriesArray.sort((a, b) => a.mainCategory.localeCompare(b.mainCategory));
          let categoryCounter = 1;
          const excludedNumRacines = [147, 151, 152, 148, 144];

          $.each(categoriesArray, function(index, categoryObj) {
              let mainCategoryOption = new Option(`${categoryCounter}. ${categoryObj.mainCategory}`, '', true, true);
              mainCategoryOption.className = 'category';
              mainCategoryOption.disabled = true;
              select.append(mainCategoryOption);
              categoryCounter++;

              if (categoryObj.subCategory) {
                  let subCategoryOption = new Option(` ${categoryObj.subCategory}`, '', true, true);
                  subCategoryOption.className = 'subcategory';
                  subCategoryOption.disabled = true;
                  select.append(subCategoryOption);
              }

              categoryObj.rubriques.forEach(function(rubrique) {
                  if (!excludedNumRacines.includes(rubrique.Num_racines)) {
                      let searchText = `${rubrique.Num_racines} ${rubrique.Nom_racines} ${categoryObj.mainCategory}`;
                      let option = new Option(`${rubrique.Num_racines}: ${rubrique.Nom_racines} : ${Math.round(rubrique.Taux)}%`, rubrique.Num_racines);
                      option.setAttribute('data-search-text', searchText);
                      option.setAttribute('data-nom-racine', rubrique.Nom_racines);
                      select.append(option);
                  }
              });
          });

          select.select2({
              width: '100%',
              minimumResultsForSearch: 0,
              dropdownAutoWidth: true,
              templateResult: function(data) {
                  if (!data.id) return data.text;
                  if ($(data.element).hasClass('category')) {
                      return $('<span style="font-weight: bold;">' + data.text + '</span>');
                  } else if ($(data.element).hasClass('subcategory')) {
                      return $('<span style="font-weight: bold; padding-left: 10px;">' + data.text + '</span>');
                  }
                  return $('<span>' + data.text + '</span>');
              },
              matcher: function(params, data) {
                  if ($.trim(params.term) === '') return data;
                  var searchText = $(data.element).data('search-text');
                  return searchText && searchText.toLowerCase().includes(params.term.toLowerCase()) ? data : null;
              }
          });

          select.on("select2:open", function() {
              setTimeout(function() {
                  $('.select2-search__field').focus();
              }, 10);
          });

          if (selectedValue) {
              select.val(selectedValue).trigger('change');
          }
      },
      error: function(jqXHR, textStatus, errorThrown) {
          console.error('Erreur lors de la récupération des rubriques :', textStatus, errorThrown);
      }
  });
}

  </script>
<!-- Modal pour importer des sociétés -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Importer Sociétés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulaire d'Importation -->
                <form id="import-societe-form" action="{{ route('societes.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                    <div class="mb-3">
                        <label for="import_file" class="form-label">Choisir le fichier d'importation</label>
                        <input type="file" class="form-control" id="import_file" name="file" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="import_raison_sociale" class="form-label">Raison Sociale</label>
                            <input type="number" class="form-control" id="import_raison_sociale" name="raison_sociale" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_forme_juridique" class="form-label">Forme Juridique</label>
                            <input type="number" class="form-control" id="import_forme_juridique" name="forme_juridique">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_siège_social" class="form-label">Siège Social</label>
                            <input type="number" class="form-control" id="import_siège_social" name="siege_social">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_patente" class="form-label">Patente</label>
                            <input type="number" class="form-control" id="import_patente" name="patente">
                        </div>
                       
                        <div class="col-md-6 mb-3">
                            <label for="import_rc" class="form-label">RC</label>
                            <input type="number" class="form-control" id="import_rc" name="rc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_centre_rc" class="form-label">Centre RC</label>
                            <input type="number" class="form-control" id="import_centre_rc" name="centre_rc">
                        </div>
                       
                        <div class="col-md-6 mb-3">
                            <label for="import_identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="number" class="form-control" id="import_identifiant_fiscal" name="identifiant_fiscal" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_ice" class="form-label">ICE</label>
                            <input type="number" class="form-control" id="import_ice" name="ice" required maxlength="15" title="Veuillez entrer uniquement des chiffres (max 15 chiffres)">
                        </div>
                      
                   
                     
                      
                        <div class="col-md-6 mb-3">
                            <label for="import_date_creation" class="form-label">Date de Création</label>
                            <input type="number" class="form-control" id="import_date_creation" name="date_creation">
                        </div>
                        <div class="col-md-6 mb-3 d-flex">
                            <div class="me-2" style="flex: 1;">
                                <label for="import_exercice_social_debut" class="form-label">Exercice Social Début</label>
                                <input type="number" class="form-control" id="import_exercice_social_debut" name="exercice_social_debut">
                            </div>
                            <div style="flex: 1;">
                                <label for="import_exercice_social_fin" class="form-label">Exercice Social Fin</label>
                                <input type="number" class="form-control" id="import_exercice_social_fin" name="exercice_social_fin">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_model_comptable" class="form-label">Modèle Comptable</label>
                            <input type="number" class="form-control" id="import_model_comptable" name="modele_comptable" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_nombre_chiffre_compte" class="form-label">Nombre caractères Compte</label>
                            <input type="number" class="form-control" id="import_nombre_chiffre_compte" name="nombre_chiffre_compte">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_nature_activite" class="form-label">Nature d'Activité</label>
                            <input type="number" class="form-control" id="import_nature_activite" name="nature_activite">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_activite" class="form-label">Activité</label>
                            <input type="number" class="form-control" id="import_activite" name="activite">
                        </div>


                        <div class="col-md-6 mb-3">
                            <label for="import_assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <input type="number" class="form-control" id="import_assujettie_partielle_tva" name="assujettie_partielle_tva">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="number" class="form-control" id="import_prorata_de_deduction" name="prorata_de_deduction">
                        </div>
                       
                    
                        <div class="col-md-6 mb-3">
                            <label for="import_regime_declaration" class="form-label">Régime de Déclaration de TVA</label>
                            <input type="number" class="form-control" id="import_regime_declaration" name="regime_declaration">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_fait_generateur" class="form-label">Fait Générateur</label>
                            <input type="number" class="form-control" id="import_fait_generateur" name="fait_generateur">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_rubrique_tva" class="form-label">Rubrique TVA</label>
                            <input type="number" class="form-control" id="import_rubrique_tva" name="rubrique_tva">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="import_designation" class="form-label">Désignation</label>
                            <input type="number" class="form-control" id="import_designation" name="designation">
                        </div>
                      
                      
                    </div>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('#import-societe-form input:not([type="file"]):not([type="submit"])');

        inputs.forEach((input, index) => {
            input.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Empêche le formulaire de se soumettre
                    // Si c'est le dernier champ, soumettre le formulaire
                    if (!inputs[index + 1]) {
                        document.getElementById('import-societe-form').submit();
                    } else {
                        // Focus sur le champ suivant
                        inputs[index + 1].focus();
                    }
                }
            });
        });
    });
</script>


<!-- Modal Modifier Société -->
<div class="modal fade" id="modifierSocieteModal" tabindex="-1" aria-labelledby="modifierSocieteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifierSocieteModalLabel">Modifier Société</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form id="societe-modification-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="modification_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mod_raison_sociale" class="form-label">Raison Sociale</label>
                            <input type="text" class="form-control" id="mod_raison_sociale" name="raison_sociale" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_siège_social" class="form-label">Siège Social</label>
                            <input type="text" class="form-control" id="mod_siège_social" name="siege_social">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_ice" class="form-label">ICE</label>
                            <input type="text" class="form-control" id="mod_ice" name="ice" required maxlength="15" title="Veuillez entrer uniquement des chiffres (max 15 chiffres)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_rc" class="form-label">RC</label>
                            <input type="text" class="form-control" id="mod_rc" name="rc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_identifiant_fiscal" class="form-label">Identifiant Fiscal</label>
                            <input type="text" class="form-control" id="mod_identifiant_fiscal" name="identifiant_fiscal" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_patente" class="form-label">Patente</label>
                            <input type="text" class="form-control" id="mod_patente" name="patente">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_centre_rc" class="form-label">Centre RC</label>
                            <input type="text" class="form-control" id="mod_centre_rc" name="centre_rc">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_forme_juridique" class="form-label">Forme Juridique</label>
                            <input type="text" class="form-control" id="mod_forme_juridique" name="forme_juridique">
                        </div>
                        <div class="col-md-6 mb-3 d-flex">
                            <div class="me-2" style="flex: 1;">
                                <label for="mod_exercice_social_debut" class="form-label">Exercice Social Début</label>
                                <input type="date" class="form-control" id="mod_exercice_social_debut" name="exercice_social_debut">
                            </div>
                            <div style="flex: 1;">
                                <label for="mod_exercice_social_fin" class="form-label">Exercice Social Fin</label>
                                <input type="date" class="form-control" id="mod_exercice_social_fin" name="exercice_social_fin">
                            </div>
                        </div>
                       
                        <div class="col-md-6 mb-3">
                            <label for="mod_model_comptable" class="form-label">Modèle Comptable</label>
                            <input type="text" class="form-control" id="mod_model_comptable" name="modele_comptable" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_nombre_chiffre_compte" class="form-label">Nombre caractères Compte</label>
                            <input type="text" class="form-control" id="mod_nombre_chiffre_compte" name="nombre_chiffre_compte">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_date_creation" class="form-label">Date de Création</label>
                            <input type="date" class="form-control" id="mod_date_creation" name="date_creation">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_assujettie_partielle_tva" class="form-label">Assujettie Partielle TVA</label>
                            <input type="text" class="form-control" id="mod_assujettie_partielle_tva" name="assujettie_partielle_tva">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_prorata_de_deduction" class="form-label">Prorata de Déduction</label>
                            <input type="text" class="form-control" id="mod_prorata_de_deduction" name="prorata_de_deduction">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_nature_activite" class="form-label">Nature d'Activité</label>
                            <input type="text" class="form-control" id="mod_nature_activite" name="nature_activite">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_activite" class="form-label">Activité</label>
                            <input type="text" class="form-control" id="mod_activite" name="activite">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_regime_declaration" class="form-label">Régime de Déclaration de TVA</label>
                            <input type="text" class="form-control" id="mod_regime_declaration" name="regime_declaration">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_fait_generateur" class="form-label">Fait Générateur</label>
                            <input type="text" class="form-control" id="mod_fait_generateur" name="fait_generateur">
                        </div>
                        <div class="col-md-6 mb-3">
                        <label for="editRubriqueTVA">Rubrique TVA</label>
                               
                               <select class="form-control select2" id="editRubriqueTVA" name="rubrique_tva" required>
                               
                                   <!-- Les options seront ajoutées par JavaScript -->
                               </select>
                              </div>
                        <div class="col-md-6 mb-3">
                            <label for="mod_designation" class="form-label">Désignation</label>
                            <input type="text" class="form-control" id="mod_designation" name="designation">
                        </div>
                       
                    </div>
                     <!-- Bouton Réinitialiser avec marge très grande à droite -->
                     <button type="reset" class="btn btn-secondary me-8">
                            <i class="fas fa-undo"></i> 
                        </button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
  $(document).ready(function() {
    remplirRubriquesTva('editRubriqueTVA');
    
    // Variable pour empêcher l'exécution multiple
    var modalOpened = false;

    // Fonction pour ouvrir le modal directement sans demander de mot de passe
    function openModal(societeId) {
        // Requête AJAX pour obtenir les données de la société
        var url = '/societes/' + societeId; // URL pour récupérer les données de la société
        
        $.get(url, function(data) {
            // Remplir le formulaire avec les données de la société
            $('#modification_id').val(data.id);
            $('#mod_raison_sociale').val(data.raison_sociale);
            $('#mod_siège_social').val(data.siege_social);
            $('#mod_ice').val(data.ice);
            $('#mod_rc').val(data.rc);
            $('#mod_identifiant_fiscal').val(data.identifiant_fiscal);
            $('#mod_patente').val(data.patente);
            $('#mod_centre_rc').val(data.centre_rc);
            $('#mod_forme_juridique').val(data.forme_juridique);
            $('#mod_exercice_social_debut').val(data.exercice_social_debut);
            $('#mod_exercice_social_fin').val(data.exercice_social_fin);
            $('#mod_date_creation').val(data.date_creation);
            $('#mod_assujettie_partielle_tva').val(data.assujettie_partielle_tva);
            $('#mod_prorata_de_deduction').val(data.prorata_de_deduction);
            $('#mod_nature_activite').val(data.nature_activite);
            $('#mod_activite').val(data.activite);
            $('#mod_regime_declaration').val(data.regime_declaration);
            $('#mod_fait_generateur').val(data.fait_generateur);
            $('#editRubriqueTVA').val(data.rubrique_tva);
            $('#mod_designation').val(data.designation);
            $('#mod_nombre_chiffre_compte').val(data.nombre_chiffre_compte);
            $('#mod_model_comptable').val(data.modele_comptable);

            // Ouvrir le modal après avoir rempli les données
            $('#modifierSocieteModal').modal('show');
        });
    }

    // Événement lors de l'ouverture du modal de modification
    $('#modifierSocieteModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // bouton qui a déclenché le modal
        var societeId = button.data('id'); // récupère l'ID de la société
        
        // Si le modal a déjà été ouvert, on ne continue pas
        if (modalOpened) {
            return;  // Si le modal est déjà ouvert, on arrête tout
        }

        // Marquer le modal comme ouvert pour éviter les appels multiples
        modalOpened = true;

        // Appeler la fonction pour ouvrir le modal directement sans mot de passe
        openModal(societeId);
    });

    // Événement lors de la soumission du formulaire
    $('#societe-modification-form').on('submit', function(e) {
        e.preventDefault(); // Empêche le rechargement de la page
        var formData = $(this).serialize(); // Sérialiser les données du formulaire
        var societeId = $('#modification_id').val(); // ID de la société

        // Requête AJAX pour mettre à jour la société
        $.ajax({
            url: '/societes/' + societeId,
            type: 'PUT',
            data: formData,
            success: function(response) {
                // Fermer le modal
                $('#modifierSocieteModal').modal('hide');
                // Recharger la page après la mise à jour
                location.reload(); // Recharger la page après la mise à jour
            },
            error: function(xhr) {
                // Gérer les erreurs
                alert('Une erreur s\'est produite lors de la mise à jour de la société.');
            }
        });
    });
});




</script>
<!-- Table Tabulator -->

<div id="societes-table"></div>
<!-- Tabulator JS -->
<script>
        // Assigner les données des sociétés à une variable JS depuis PHP
        var societes = {!! $societes !!};

        // Initialiser Tabulator avec les données
        var table = new Tabulator("#societes-table", {
            data: societes, // Charger les données passées depuis le contrôleur
            layout: "fitColumns", // Ajuster les colonnes à la largeur du tableau
            columns: [
                {title: "Raison Sociale", field: "raison_sociale", formatter: function(cell) {
                    var nomEntreprise = cell.getData()["raison_sociale"];
                    var formeJuridique = cell.getData().forme_juridique;
                    return nomEntreprise + " " + formeJuridique;
                }, headerFilter: true},
                {title: "ICE", field: "ice", headerFilter: true},
                {title: "RC", field: "rc", headerFilter: true},
                {title: "Identifiant Fiscal", field: "identifiant_fiscal", headerFilter: true},
                {
            title: "Exercice en cours",
            field: "exercice_social", // Nom du champ dans vos données
            headerFilter: true,
            formatter: function(cell) {
                const rowData = cell.getRow().getData(); // Obtenir les données de la ligne
                return `Du ${rowData.exercice_social_debut} au ${rowData.exercice_social_fin}`; // Formater les dates
            },
            editor: "input", // Utiliser un éditeur de type input pour permettre la modification
            editorParams: {
                maxLength: 10, // Limiter la longueur de l'entrée, ajustez selon vos besoins
                placeholder: "Ex: 2023-2024", // Ajouter un placeholder pour guider l'utilisateur
            },
            validator: function(value) {
                // Valider que la valeur saisie est correcte, ajustez selon votre format de date
                return /^(\d{4})-(\d{4})$/.test(value); // Ex: 2023-2024
            },
            cellEdited: function(cell) {
                var newValue = cell.getValue();
                var rowData = cell.getRow().getData();
                // Ici vous pouvez envoyer les nouvelles données au serveur avec une requête AJAX
                console.log("Nouvelle valeur pour l'exercice social:", newValue);
                // Exemple : envoyer la mise à jour à votre serveur via AJAX
                fetch('/update-exercice-social', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: rowData.id,
                        exercice_social: newValue
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Données mises à jour:", data);
                })
                .catch(error => {
                    console.error("Erreur lors de la mise à jour:", error);
                });
            }
        },
                {
                    title: "Actions",
                    formatter: function(cell) {
                        var rowData = cell.getRow().getData();
                        return "<div class='action-icons'>" +
                        "<a href='/exercices/" + rowData.id + "' class='text-info mx-1'>" +
                        "<i class='fas fa-door-open' onclick='window.location=\"/exercices/" + rowData.id + "\"'></i></a>" +
                            "<a href='#' class='text-primary mx-1' data-bs-toggle='modal' data-bs-target='#modifierSocieteModal' " +
                            "data-id='" + rowData.id + "' " +
                            "data-nom-entreprise='" + rowData.raison_sociale + "' " +
                            "data-ice='" + rowData.ice + "' " +
                            "data-rc='" + rowData.rc + "' " +
                            "data-identifiant-fiscal='" + rowData.identifiant_fiscal + "'>" +
                            "<i class='fas fa-edit'></i></a>" +
                            "<a href='#' class='text-danger mx-1 delete-icon' data-id='" + rowData.id + "'>" +
                            "<i class='fas fa-trash'></i></a>" +
                           
                            "</div>";
                    },
                    width: 150,
                    hozAlign: "center"
                }
            ],
        });
        
  // Ajouter un gestionnaire d'événements pour le double clic sur une ligne
// table.on("rowDblClick", function(row) {
//     var rowData = row.getData(); // Obtenir les données de la ligne
//     window.location.href = "{{ route('exercices.show', '') }}/" + rowData.id; // Rediriger vers la vue 'exercices'
// });

//    // Écouteur d'événement pour le double clic sur une ligne du tableau
//    table.on("rowDblClick", function(row) {
//         var societeId = row.getData().id; // Récupérer l'ID de la société
//         window.location.href = `/exercice/${societeId}`; // Rediriger vers la vue "exercice"
//     });

	$(document).ready(function() {
  remplirRubriquesTva('rubrique_tva');
});


    // Ouvrir le modal au clic sur le bouton
    document.getElementById('open-modal-btn').addEventListener('click', function() {
        var myModal = new bootstrap.Modal(document.getElementById('nouvelleSocieteModal'));
        myModal.show();
        remplirRubriquesTva();
    });

    document.getElementById('societes-table').addEventListener('click', function(e) {
    if (e.target.closest('.delete-icon')) {
        const id = e.target.closest('.delete-icon').getAttribute('data-id');
        
        // Confirmer la suppression sans demander de mot de passe
        if (confirm("Êtes-vous sûr de vouloir supprimer cette société ?")) {
            // Envoyer la requête de suppression sans vérifier le mot de passe
            fetch("{{ url('societes') }}/" + id, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    "Content-Type": "application/json"
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau lors de la suppression');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Suppression de la ligne dans Tabulator
                    table.deleteRow(id); // Utilisez deleteRow avec l'ID pour supprimer la ligne visuellement
                    alert("Société supprimée avec succès.");
                } else {
                    alert("Erreur lors de la suppression : " + data.message);
                }
            })
            .catch(error => {
                console.error("Erreur :", error);
                alert("Une erreur s'est produite : " + error.message);
            });
        }
    }
});





// Gestion du changement de la valeur "Assujettie partielle à la TVA"
document.getElementById('assujettie_partielle_tva').addEventListener('change', function() {
    var prorataField = document.getElementById('prorata_de_deduction');
    
    if (this.value === "0") {
        prorataField.value = "100"; // Mettre la valeur à 10
        prorataField.setAttribute("readonly", true); // Rendre le champ non modifiable
    } else {
        prorataField.removeAttribute("readonly"); // Rendre le champ modifiable
        prorataField.value = ""; // Réinitialiser le champ si nécessaire
    }
});
 
</script>


<script>
    document.getElementById("identifiant_fiscal").addEventListener("input", function() {
    // Remplace tous les caractères non numériques par une chaîne vide
    this.value = this.value.replace(/\D/g, '');

    // Limite la longueur à 8 chiffres
    if (this.value.length > 8) {
        this.value = this.value.slice(0, 8);
    }
});

</script>

<script>
    document.getElementById("ice").addEventListener("input", function() {
    // Remplace tous les caractères non numériques par une chaîne vide
    this.value = this.value.replace(/\D/g, '');

    // Limite la longueur à 15 chiffres
    if (this.value.length > 15) {
        this.value = this.value.slice(0, 15);
    }
});

</script>

<script>
$(function() {
  $('#exercice_social').daterangepicker({
    opens: 'left',
    startDate: moment('2018-01-01'), // Date de début par défaut
    endDate: moment('2019-01-15'), // Date de fin par défaut
    locale: {
      format: 'YYYY-MM-DD'
    },
    // Permet de choisir une plage de dates
    singleDatePicker: false,
    showDropdowns: true,
    autoUpdateInput: true
  }, function(start, end) {
    // Met à jour le champ d'entrée avec les dates sélectionnées
    $('#exercice_social').val(start.format('YYYY-MM-DD') + ' au ' + end.format('YYYY-MM-DD'));
  });
});
</script>
<script>
    document.getElementById('import-societes').addEventListener('click', function() {
    // Logique d'importation, par exemple, ouvrir un modal
    openImportModal();
});

function openImportModal() {
    // Code pour afficher le modal d'importation 
    $('#importModal').modal('show'); // Utiliser Bootstrap modal si vous l'avez
}

</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

@endsection

</body>
</html>
