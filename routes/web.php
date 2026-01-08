<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SaisieMouvementController;
use App\Http\Controllers\SessionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocieteController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ImportExcelController;
use App\Exports\SocietesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientsExport;
use App\Http\Controllers\ClientsPDFExportController;
use App\Http\Controllers\SocietesPDFExportController;
use App\Http\Controllers\ExerciceController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\AchatController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\BanqueController;
use App\Http\Controllers\CaisseController;
use App\Http\Controllers\ImpotController;
use App\Http\Controllers\PaieController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InterlocuteursController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FolderVenteController;
use App\Http\Controllers\FolderBanqueController;
use App\Http\Controllers\FolderCaisseController;
use App\Http\Controllers\FolderImpotController;
use App\Http\Controllers\FolderPaieController;
use App\Http\Controllers\DossierPermanantController;
use App\Http\Controllers\FolderDossierPermanantController;
use App\Http\Controllers\FoldersVente1Controller;
use App\Http\Controllers\FoldersBanque1Controller;
use App\Http\Controllers\FoldersCaisse1Controller;
use App\Http\Controllers\FoldersImpot1Controller;
use App\Http\Controllers\FoldersPaie1Controller;
use App\Http\Controllers\FoldersDossierPermanant1Controller;
use App\Http\Controllers\DouvrirController;
use App\Http\Controllers\DasousdossierController;
use App\Http\Controllers\EtatDeCaisseController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SoldeMensuelController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\PlanComptableController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ExportController;
use App\Exports\FournisseursExport;
// use App\Http\Controllers\racineController;
use App\Http\Controllers\RacineController;
use App\Http\Controllers\OperationCouranteController;
use App\Http\Middleware\SetSocieteId;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\OperationCaisseBanqueController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ExerciceComptableController;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\ReleveBancaireController;


Route::group(['middleware' => 'auth'], function () {

    Route::post('/operation-courante-vente-store', [OperationCouranteController::class, 'storeVenteOperation'])
     ->name('operation-courante-vente.store');

    Route::post('/achats/update-row', [OperationCouranteController::class, 'updateRow'])
    ->name('achats.updateRow');

    Route::get('/api/file/{fileId}', [OperationCaisseBanqueController::class, 'getFileUrl']);

    Route::get('/files/{id}', [OperationCouranteController::class, 'preview']);

 Route::put('/notifications/mark-as-read/{type}/{id}', [NotificationController::class, 'markAsReadbg']);

Route::post('/operation-courante-achat-store', [OperationCouranteController::class, 'storeAchatOperation'])
     ->name('operation-courante-achat.store');

     Route::get('/get-plan-comptable', [OperationCouranteController::class, 'getPlanComptable'])->name('get.plan.comptable');

     Route::get('/operation-courante/existing-comptes', function() {
    return response()->json(\App\Models\OperationCourante::pluck('compte')->unique()->toArray());
});
 Route::get('/plancomptable', [JournalController::class, 'listPlanComptable'])->name('plancomptable.list'); // ?q= recherche
    Route::post('/plancomptable', [JournalController::class, 'storePlanComptable'])->name('plancomptable.store');
Route::post('/operation-courante/replace-accounts', [OperationCouranteController::class, 'replaceAccounts'])
    ->name('operation-courante.replace-accounts');

Route::get('/plan-comptable/search', [PlanComptableController::class, 'search'])
    ->name('plan-comptable.search');
Route::post('/operation-courante/{id}/update-field', [OperationCouranteController::class, 'updateField']);


Route::post('/operation-courante/transfer-journal-ach', [OperationCouranteController::class, 'transferJournalACH'])
    ->name('operation-courante.transfer-ach');
      Route::post('/operation-courante/transfer-journal-vte', [OperationCouranteController::class, 'transferJournalVTE'])
    ->name('operation-courante.transfer-vte');
Route::post('/operation-courante/transfer-journal-op', [OperationCouranteController::class, 'transferJournalOP'])
    ->name('operation-courante.transfer-op');
    Route::get('/plan-comptable/for-societe', [\App\Http\Controllers\PlanComptableController::class, 'forSociete']);
Route::get('/clients/data', [ClientController::class, 'getData'])->name('clients.data');






    Route::get('/soldeActuel', [OperationCaisseBanqueController::class, 'getSoldeActuel']);

Route::post('/modifier-tous-compte-caise', [OperationCaisseBanqueController::class, 'modifierTousCompteCaisse']);
Route::post('/modifier-tous-compte-banque', [OperationCaisseBanqueController::class, 'modifierTousCompteBanque']);

Route::get('/api/solde-initial', [OperationCaisseBanqueController::class, 'getSoldeInitialCaisse']);

    Route::post('/transfere-banque', [OperationCaisseBanqueController::class, 'transfereBanque'])->name('transfere.banque');
     Route::post('/transfere-caisse', [OperationCaisseBanqueController::class, 'transfereCaisse'])->name('transfere.caisse');

    Route::post('/importer-operation-courante-caisse', [OperationCaisseBanqueController::class, 'importerOperationCouranteCaisse'])
    ->name('importerOperationCouranteCaisse');
    Route::get('//etat-de-caisse/view', [ReleveBancaireController::class, 'viewFilecaisse'])->name('etatcaisse.view');

    Route::post('/operation-courante-caisse-store', [OperationCaisseBanqueController::class, 'storeCaisse']);

Route::get('/racine-tva/{num?}', [OperationCouranteController::class, 'getTVAop']);

    Route::post('/operations/diverses', [OperationCouranteController::class, 'storeOperationDiverses'])->name('operations.diverses.store');
Route::get('/get-nfacturelettreeOP', [OperationCouranteController::class, 'searchFactureOP']);
Route::get('/get-nfacturelettree', [OperationCaisseBanqueController::class, 'searchFacture']);

Route::get('/fichiers/{id}', [OperationCouranteController::class, 'preview'])->name('files.preview');
Route::post('/operation-courante/assign-file', [App\Http\Controllers\OperationCouranteController::class, 'assignFile'])
     ->name('operation.assign-file');
Route::post('/fichiers', [App\Http\Controllers\OperationCouranteController::class, 'storeFile'])
     ->name('files.store');

     Route::get('piece/last', [OperationCouranteController::class, 'getLastNumeroPiece']);

        Route::post('/pieces/check-exists', [OperationCouranteController::class, 'checkExists']);
Route::get('/check-numero-facture', [OperationCouranteController::class, 'checkNumeroFacture']);
    Route::get('/rubriques-tva-vente', [JournalController::class, 'getRubriquesTVAVente']);
Route::get('/select-folder-achat',  [OperationCouranteController::class, 'selectFolderAchat']);
Route::get('/select-folder-vente',  [OperationCouranteController::class, 'selectFolderVente']);




Route::post('/update-banque-operation', [OperationCaisseBanqueController::class, 'updateBanqueOperation']);

Route::put('/banque/{id}', [OperationCaisseBanqueController::class, 'updateBanque'])->name('banque.update');

Route::get('/get-nfacturelettree', [OperationCaisseBanqueController::class, 'searchFacture']);

Route::post('/importer-operation-courante-banque', [OperationCaisseBanqueController::class, 'importerOperationCouranteBanque'])
    ->name('importerOperationCouranteBanque');
Route::get('/releve-bancaire/view', [ReleveBancaireController::class, 'viewFile'])->name('releve.view');

Route::post('/upload-releve-bancaire', [ReleveBancaireController::class, 'upload'])
    ->name('releve-bancaire.upload');
Route::post('/upload-etat-de-caisse', [ReleveBancaireController::class, 'uploadetatdecaisse'])
    ->name('upload-etat-de-caisse.upload');
// Route pour afficher le formulaire de création d'une nouvelle racine
Route::get('/racines/create', [RacineController::class, 'create'])->name('racines.create');
Route::get('/racines/{id}/edit', [RacineController::class, 'edit'])->name('racines.edit');

// Route pour enregistrer une nouvelle racine
Route::post('/racines', [RacineController::class, 'store'])->name('racines.store');
    Route::get('/racines', [RacineController::class, 'index']);
// Route::put('/racines/{id}', [RacineController::class, 'update'])->name('racines.update');
Route::put('/racines/{id}', [RacineController::class, 'update']);


Route::get('/racines/{id}/check-fournisseurs', [RacineController::class, 'checkFournisseurs'])
         ->name('racines.checkFournisseurs');
    // Suppression RESTful
    Route::delete('/racines/{id}', [RacineController::class, 'destroy'])
         ->name('racines.destroy');
Route::post('/racines/toggle-visibility/{id}', [RacineController::class, 'toggleVisibility']);
 Route::get('/search-plan-comptable', [RacineController::class, 'searchPlanComptable']);
Route::post('/close-exercice', [OperationCouranteController::class, 'closeExercice']);
    Route::post('/plan-comptable', [RacineController::class, 'storePlanComptable']);

    Route::get('/get-compte-tva-type', [RacineController::class, 'getCompteTvaType']);

 
    Route::get('/get-categories', [RacineController::class, 'getCategories']);

Route::get('/operation-courante/select-folder', [OperationCouranteController::class, 'selectFolder']);

// routes/web.php
Route::put('/notifications/supprimer-notification-sous-dossier/{id}', [NotificationController::class, 'supprimerNotificationSousDossier']);
Route::put('/notifications/supprimer-notification-fichier/{id}', [NotificationController::class, 'supprimerNotificationFichier']);
Route::put('/notifications/supprimer-notification-solde/{id}', [NotificationController::class, 'supprimerNotificationSolde']);
Route::put('/notifications/supprimer-notification-dossier/{id}', [NotificationController::class, 'supprimerNotificationDossier']);
Route::put('/notifications/supprimer-notification-message/{id}', [NotificationController::class, 'supprimerNotificationMessage']);
Route::get('/notifications/unread', [MessageController::class, 'unreadMessages']);

Route::post('/cloturer-exercice', [ExerciceComptableController::class, 'cloturerExercice']);

Route::get('/operation_courante/{piece}/edit', [OperationCouranteController::class, 'edit'])->name('operation_courante.edit');
Route::get('/api/operation_courante/by-piece/{piece}', [OperationCouranteController::class, 'apiByPiece']);


Route::get('/grand-livre', [GrandLivreController::class, 'index'])->name('grandlivre.index');
Route::get('/get-comptes-plan', [GrandLivreController::class, 'getComptesPlan'])->name('grandlivre.getcomptesplan');
Route::get('/grandlivre/filter', [GrandLivreController::class, 'filter'])->name('grandlivre.filter');
Route::get('/popup-modification', function () {
    return view('popupModification');
})->name('popupModification');
Route::post('/upload-attachment', [EtatDeCaisseController::class, 'upload'])->name('upload.attachment');
Route::get('/attachments/{filename}', [EtatDeCaisseController::class, 'view'])->name('attachments.view');

Route::get('/contact', [MessageController::class, 'showForm'])->name('contact.form');
Route::post('/contact', [MessageController::class, 'sendEmail'])->name('contact.send');

// routes/api.php
Route::get('/pdf/{pdf}/rows', [PdfController::class,'rows']);
Route::post('/upload-pdf', [PdfController::class, 'upload'])->name('pdf.upload');
Route::post('/merge-files', [FileController::class, 'mergeFiles'])->name('mergeFiles');
Route::post('/extract-pdf', [PdfController::class, 'extractData']);
Route::get('/operation-courante-banque', [OperationCaisseBanqueController::class, 'getBanque']);

Route::post('/operation-courante-banque', [OperationCaisseBanqueController::class, 'storeBanque']);
Route::post('/operation-courante-caisse', [OperationCaisseBanqueController::class, 'store']);

Route::delete('/operation-courante-caisse', [OperationCaisseBanqueController::class, 'destroy']);
Route::get('/operation-courante-caisse', [OperationCaisseBanqueController::class, 'get']);

 Route::get('/files/{filename}', [OperationCouranteController::class, 'download'])->name('files.download');

Route::get('Charger-document', function () {
    return view('Charger-document');
})->name('Charger-document');
Route::get('/getAllPlanComptable', [OperationCaisseBanqueController::class, 'getAllPlanComptable']);

Route::get('/caisseop', [OperationCaisseBanqueController::class, 'index'])->name('caisseop');

Route::get('/caisseop', [OperationCaisseBanqueController::class, 'showView'])->name('caisseop');

    Route::put('/file/{id}', [FileController::class, 'update'])->name('file.update');
    Route::put('/folder/{id}', [FolderController::class, 'update'])->name('folder.update');
    Route::get('/balance', [BalanceController::class, 'index'])->name('balance.index');
    Route::get('/balance/export-excel', [BalanceController::class, 'exportExcel'])->name('balance.exportExcel');
    Route::get('/balance/export-pdf', [BalanceController::class, 'exportPdf'])->name('balance.exportPdf');
    Route::get('/fournisseurs-comptes', [OperationCouranteController::class, 'getComptes'])
    ->name('fournisseurs.comptes');

    Route::get('/get-next-compte-client/{societeId}', [ClientController::class, 'getNextCompteForClient'])
    ->name('client.getNextCompte');
    Route::post('/fournisseurs/check-compte', [FournisseurController::class, 'checkCompte'])
    ->name('fournisseurs.checkCompte');

    Route::get('/check-journal', [JournalController::class, 'checkJournal'])->name('journaux.check');
    Route::post('/journaux', [JournalController::class, 'store'])->name('journaux.store');
    Route::get('/rubriques-tva', [FournisseurController::class, 'getRubriquesTva'])->name('rubriques.tva');
        Route::post('/messages/read/{id}', [MessageController::class, 'markAsRead'])->name('messages.read');
        Route::get('/comptes-achats', [JournalController::class, 'getComptesAchats']);
        Route::get('/comptes-ventes', [JournalController::class, 'getComptesVentes']);
        Route::get('/comptes-Caisse', [JournalController::class, 'getComptesCaisse']);
        Route::get('/comptes-Banque', [JournalController::class, 'getComptesBanque']);
        Route::get('/journaux', [JournalController::class, 'index'])->name('journaux.index');
        // Route::get('/gestion-des-journaux', [JournalController::class, 'index'])->name('gestion.des.journaux');


        Route::get('/journaux/data', [JournalController::class, 'getData'])->name('journaux.data');
        Route::post('/journaux', [JournalController::class, 'store'])->name('journaux.store');
        Route::get('/journaux/{id}', [JournalController::class, 'show'])->name('journaux.show');
        Route::get('/journals/{id}/edit', [JournalController::class, 'edit'])->name('journals.edit');
        Route::put('/journaux/{id}', [JournalController::class, 'update']);
        Route::post('/journaux/delete-selected', [JournalController::class, 'deleteSelected'])->name('journaux.deleteSelected');
        Route::delete('/journaux/{id}', [JournalController::class, 'destroy'])->name('journaux.destroy');
        Route::get('/journaux/contre-partie/{typeJournal}', [JournalController::class, 'getComptesContrePartie']);
        Route::get('/export-fournisseurs-excel', function () {
            return Excel::download(new FournisseursExport, 'fournisseurs.xlsx');
        });
        Route::get('/export-fournisseurs-pdf', [ExportController::class, 'exportPDF']) ;
        Route::post('/fournisseurs/delete-selected', [FournisseurController::class, 'deleteSelected'])->name('fournisseurs.deleteSelected');
        Route::get('/fournisseurs/data', [FournisseurController::class, 'getData']) ;
        Route::get('/fournisseurs', [FournisseurController::class, 'index']) ;
        Route::post('/fournisseurs', [FournisseurController::class, 'store']);
        Route::put('/fournisseurs/{id}', [FournisseurController::class, 'update']) ;
        Route::delete('/fournisseurs/{id}', [FournisseurController::class, 'destroy']);
        Route::get('/fournisseurs/{id}', [FournisseurController::class, 'show']);
        Route::get('/fournisseurs/{id}/edit', [FournisseurController::class, 'edit'])->name('fournisseurs.edit'); ;
        Route::post('/fournisseurs/verifier-compte', [FournisseurController::class, 'verifierCompte']);
        Route::get('/get-next-compte/{societeId}', [FournisseurController::class, 'getNextCompte']);  // Route pour récupérer le prochain compte
        Route::get('/rubriques-tva', [FournisseurController::class, 'getRubriquesTva']);
        Route::get('/comptes', [FournisseurController::class, 'getComptes']);
        Route::post('/fournisseurs/import', [FournisseurController::class, 'import'])->name('fournisseurs.import');
        Route::post('/import-fournisseur', [FournisseurController::class, 'importFournisseur']);
        Route::resource('operation_courante', OperationCouranteController::class);
        //  Route::get('/journaux', [OperationCouranteController::class, 'getJournaux'])->name('journaux.get');
        Route::post('/lignes', [OperationCouranteController::class, 'store']);
        Route::post('/verifier-lignes-existantes', [OperationCouranteController::class, 'verifierLignesExistantes']);
        Route::get('/periodes', [OperationCouranteController::class, 'getPeriodes'])->name('periodes.get');
        Route::post('/get-tva', [OperationCouranteController::class, 'getTva']);
        Route::get('/get-session-prorata', [OperationCouranteController::class, 'getSessionProrata']);
        Route::get('/comptesOP', [OperationCouranteController::class, 'getComptesOP'])->name('comptesOP.get');
        Route::delete('/delete-operations', [OperationCouranteController::class, 'deleteOperations'])->name('deleteOperations');
        Route::get('/fournisseurs', [OperationCouranteController::class, 'getFournisseurs']);
        Route::get('/clients', [OperationCouranteController::class, 'getClients']);
        Route::get('/get-compte-tva', [OperationCouranteController::class, 'getCompteTva']);
        Route::post('/create-compte-tva', [OperationCouranteController::class, 'createCompteTva']);
        Route::get('/session-social', [OperationCouranteController::class, 'getSessionSocial']);
        Route::post('/upload', [OperationCouranteController::class, 'upload']);
        Route::get('/get-contre-parties-ventes', [OperationCouranteController::class, 'getContrePartiesVentes']);
        Route::get('/get-all-contre-parties', [OperationCouranteController::class, 'getAllContreParties']);
        Route::get('/getRubriqueSociete', [OperationCouranteController::class, 'getRubriqueSociete'])
            ->name('getRubriqueSociete');
        Route::get('operationcourante/getFournisseurs', [OperationCouranteController::class, 'getFournisseurs'])->name('operationcourante.getFournisseurs');
        Route::get('operationcourante/fournisseurs/details', [OperationCouranteController::class, 'getFournisseursAvecDetails'])->name('operationcourante.getFournisseursAvecDetails');
        Route::get('/get-journaux-by-societe', [OperationCouranteController::class, 'getJournauxBySociete'])
            ->name('getJournauxBySociete');
        Route::delete('/operationcourante/{id}', [OperationCouranteController::class, 'delete'])->name('operationcourante.delete');
        Route::get('/getJournalByCode', [OperationCouranteController::class, 'getJournalByCode'])->name('getJournalByCode');
        Route::post('/get-operations', [OperationCouranteController::class, 'getOperations'])->name('get.operations');
        Route::post('/delete-rows', [OperationCouranteController::class, 'deleteRows']);
        Route::put('/operations/{id}', [OperationCouranteController::class, 'updateField']);
        Route::put('/plancomptable/{id}', [PlanComptableController::class, 'edit']);
        Route::delete('/plancomptable/{id}', [PlanComptableController::class, 'destroy']);
        Route::put('/plancomptable/{id}', [PlanComptableController::class, 'update']);
        // Route::post('/plancomptable/import', [PlanComptableController::class, 'import'])->name('plancomptable.import');
         Route::post('/plancomptable/deleteSelected', [PlanComptableController::class, 'deleteSelected']);
        Route::get('/plan-comptable/import', [PlanComptableController::class, 'showImportForm'])->name('plancomptable.importForm');
        Route::get('/', [HomeController::class, 'home']);
        Route::get('profile', function () {
                    return view('profile');
                })->name('profile');
        Route::get('plancomptable', function () {
                    return view('plancomptable');
                })->name('plancomptable');
        // Route::get('Operation_Courante', function () {
        //             return view('Operation_Courante');
        //         })->name('Operation_Courante');
        Route::get('Operation_Courante', [OperationCouranteController::class, 'index'])->name('Operation_Courante');
        Route::get('Fournisseurs', function () {
                    return view('Fournisseurs');
                })->name('Fournisseurs');
        Route::resource('operation_courante', OperationCouranteController::class);
        Route::get('/operations', [OperationCouranteController::class, 'index'])->name('operations.index');
        Route::put('update-row/{id}', [OperationCouranteController::class, 'updateRow']);
        Route::delete('delete-row/{id}', [OperationCouranteController::class, 'deleteRow']);
        Route::get('empty-row', [OperationCouranteController::class, 'getEmptyRow']);
        Route::post('/save-or-update-row-data', [OperationCouranteController::class, 'saveOrUpdateRowData']);
        Route::post('/operation-courante/save', [OperationCouranteController::class, 'saveOperation']);
        Route::get('/societe/details', [OperationCouranteController::class, 'getSocieteDetails']);
        Route::get('/get-rubriques-tva-vente', [OperationCouranteController::class, 'getRubriquesTVAVente'])->name('rubriques-tva-vente');
        Route::get('/get-rubriques-tva', [OperationCouranteController::class, 'getRubriquesTva'])->name('rubriques-tva');
        Route::get('/get-compte-tva-ach', [OperationCouranteController::class, 'getCompteTvaAch']);
        Route::get('/get-compte-tva-vente', [OperationCouranteController::class, 'getCompteTvaVente']);
        Route::get('/get-clients', [OperationCouranteController::class, 'getClients']);
        Route::get('/get-fournisseurs-avec-details', [OperationCouranteController::class, 'getFournisseursAvecDetails']);
        Route::get('/get-details-par-compte', [OperationCouranteController::class, 'getDetailsParCompte']);
        Route::get('/fournisseurs', [FournisseurController::class, 'index'])->name('getFournisseurs');
        Route::get('/journaux-achats', [OperationCouranteController::class, 'getJournauxACH']);
        // Ancienne route qui pose problème :
// Route::get('/journaux', [OperationCouranteController::class, 'getJournaux'])->name('journaux.get');

// Nouvelle route utilisant la méthode existante :
Route::get('/journaux', [OperationCouranteController::class, 'getJournauxACH'])->name('journaux.get');
 
 
Route::post('/set-dossier-session', function (\Illuminate\Http\Request $request) {
    $dossier = \App\Models\Dossier::find($request->id);
    if ($dossier) {
        session(['dossierName' => $dossier->name]);
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false], 404);
})->name('set.dossier.session');
 
        Route::get('/journaux-ventes', [OperationCouranteController::class, 'getJournauxVTE']);
        Route::get('/journaux-Banque', [OperationCouranteController::class, 'getJournauxBanque']);
        Route::get('/journaux-Caisse', [OperationCouranteController::class, 'getJournauxCaisse']);
        Route::get('/journaux-operations-diverses', [OperationCouranteController::class, 'getJournauxOPE']);
        Route::get('/get-journaux-by-societe', [OperationCouranteController::class, 'getJournauxBySociete'])
            ->name('getJournauxBySociete');
        Route::post('/delete-rows', [OperationCouranteController::class, 'deleteRows']);
        Route::get('/get-contre-parties', [OperationCouranteController::class, 'getContreParties']);
        Route::put('/operations/{id}', [OperationCouranteController::class, 'updateField']);
        Route::put('/plancomptable/{id}', [PlanComptableController::class, 'edit']);
        Route::get('/plancomptable/data', [PlanComptableController::class, 'getData']);
        Route::post('/plancomptable', [PlanComptableController::class, 'store']);
        Route::delete('/plancomptable/{id}', [PlanComptableController::class, 'destroy']);
        Route::put('/plancomptable/{id}', [PlanComptableController::class, 'update']);
        Route::post('/plancomptable/import', [PlanComptableController::class, 'import'])->name('plancomptable.import');
        Route::get('/plancomptable/data', [PlanComptableController::class, 'index'])->name('plancomptable.index');
        Route::post('/plancomptable/deleteSelected', [PlanComptableController::class, 'deleteSelected']);
        Route::get('export-plan-comptable', [ExportController::class, 'export'])->name('export.plan_comptable');
        Route::get('/plan-comptable/excel', [PlanComptableController::class, 'exportExcel'])->name('plan.comptable.excel');
        Route::post('/cloturer-solde', [SoldeMensuelController::class, 'cloturerSolde']);
        Route::post('/delete-transaction', [TransactionController::class, 'delete'])->name('transaction.delete');
        Route::post('/save-solde', [SoldeMensuelController::class, 'saveSolde'])->name('save-solde');
        Route::put('/update-transaction/{id}', [EtatDeCaisseController::class, 'update'])->name('update-transaction');
        Route::get('/etat-caisse/{id}/edit', [EtatDeCaisseController::class, 'edit'])->name('etat-caisse.edit');
        Route::post('/save-transaction', [EtatDeCaisseController::class, 'save'])->name('save-transaction');
        Route::get('/etat-de-caisse', [EtatDeCaisseController::class, 'index'])->name('etat_de_caisse');
        Route::post('/uploadFileda', [DasousdossierController::class, 'upload'])->name('uploadFileda');
        Route::post('/folderdasouas/create', [DasousdossierController::class, 'create'])->name('folderdasouas.create');
        Route::get('/dasousdossier/{folderId}', [DasousdossierController::class, 'showSousDossier'])->name('dasousdossier.show');
        Route::put('/dossier/{id}', [DossierController::class, 'update']);
        Route::get('/Douvrir/{id}', [DouvrirController::class, 'show'])->name('Douvrir');
        Route::post('/Douvrir/upload', [DouvrirController::class, 'uploadFile'])->name('Douvrir.upload');
        Route::post('/Douvrir/create', [DouvrirController::class, 'create'])->name('Douvrir.create');
        Route::delete('/dossier/{id}/delete', [DossierController::class, 'destroy'])->name('dossier.delete');
        // Route::post('/messages/reply/{parentMessageId}', [MessageController::class, 'replyToMessage'])->name('messages.reply');
        Route::post('/messages/update/{id}', [MessageController::class, 'update'])->name('messages.update');
        Route::delete('/messages/delete/{id}', [MessageController::class, 'destroy']);
        Route::get('/folderDossier_permanant/{id}', [FolderDossierPermanantController::class, 'index']);
        Route::get('/Dossier_permanant', [DossierPermanantController::class, 'index'])->name('Dossier_permanant.view');
        Route::get('/folderPaie/{id}', [FolderPaieController::class, 'index']);
        Route::get('/folderImpot/{id}', [FolderImpotController::class, 'index']);
        Route::get('/folderCaisse/{id}', [FolderCaisseController::class, 'index']);
        Route::get('/folderBanque/{id}', [FolderBanqueController::class, 'index']);
        Route::get('/folderVente/{id}', [FolderVenteController::class, 'index']);
        Route::post('/messages/updateStatus/{messageId}', [MessageController::class, 'updateStatus'])->name('messages.updateStatus');
        Route::get('/messages/getMessages/{file_id}', [MessageController::class, 'getMessages']);        
        Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
        Route::get('/achat/view/{fileId}', [AchatController::class, 'viewFile'])->name('achat.views');
        Route::post('/dossier', [DossierController::class, 'store'])->name('dossier.store');
        Route::middleware(['auth', 'permission:vue_tableau_de_board'])->get('/exercices/{societe_id}', [DossierController::class, 'show'])->name('exercices.show');
        Route::put('/utilisateurs/{id}', [UserController::class, 'update'])->name('utilisateurs.update');
        Route::get('/utilisateurs/{id}/edit', [UserController::class, 'edit'])->name('utilisateurs.edit');
        Route::get('/user/{id}', [InterlocuteursController::class, 'edit']);
        Route::put('/interlocuteurs/{id}', [InterlocuteursController::class, 'update'])->name('interlocuteurs.update');
        Route::get('/interlocuteurs', [InterlocuteursController::class, 'index'])->name('interlocuteurs.index');
        Route::get('admin/{id}', [AdminController::class, 'show'])->name('admin.show');
        Route::put('admin/{id}', [AdminController::class, 'update'])->name('admin.update');
        Route::get('/Admin', [AdminController::class, 'index'])->name('Admin.index');
        Route::delete('/Admin/{id}', [AdminController::class, 'destroy'])->name('Admin.destroy');
        Route::delete('/utilisateurs/{id}', [UserController::class, 'destroy'])->name('utilisateurs.destroy');
        Route::get('/utilisateurs', [UserController::class, 'index'])->name('utilisateurs.index');
        Route::post('/utilisateurs', [UserController::class, 'store'])->name('utilisateurs.store');
        Route::post('/select-database', [SessionsController::class, 'selectDatabase'])->name('your_action_here');
        Route::get('import', [SocieteController::class, 'showImportForm'])->name('import.form');
        // Route::post('import', [SocieteController::class, 'import'])->name('societes.import');
        Route::get('/foldersVente1/{id}', [FoldersVente1Controller::class, 'index'])->name('foldersVente1');
        Route::post('/folderVente/create', [FoldersVente1Controller::class, 'create'])->name('folderVente.create');
        Route::get('/foldersBanque1/{id}', [FoldersBanque1Controller::class, 'index'])->name('foldersBanque1');
        Route::post('/foldersBanque1/create', [FoldersBanque1Controller::class, 'create'])->name('foldersBanque1.create');
        Route::get('/foldersCaisse1/{id}', [FoldersCaisse1Controller::class, 'index'])->name('foldersCaisse1');
        Route::post('/foldersCaisse1/create', [FoldersCaisse1Controller::class, 'create'])->name('foldersCaisse1.create');
        Route::get('/foldersImpot1/{id}', [FoldersImpot1Controller::class, 'index'])->name('foldersImpot1');
        Route::post('/foldersImpot1/create', [FoldersImpot1Controller::class, 'create'])->name('foldersImpot1.create');
        Route::get('/foldersPaie1/{id}', [FoldersPaie1Controller::class, 'index'])->name('foldersPaie1');
        Route::post('/foldersPaie1/create', [FoldersPaie1Controller::class, 'create'])->name('foldersPaie1.create');
        Route::get('/foldersDossierPermanant1/{id}', [FoldersDossierPermanant1Controller::class, 'index'])->name('foldersDossierPermanant1');
        Route::post('/foldersDossierPermanant1/create', [FoldersDossierPermanant1Controller::class, 'create'])->name('foldersDossierPermanant1.create');
        Route::get('/folder/{id}', [FolderController::class, 'index'])->name('folder.show');
        Route::delete('/societes/delete-selected', [SocieteController::class, 'deleteSelected'])->name('societes.deleteSelected');
        Route::delete('/folder/{id}', [FolderController::class, 'destroy'])->name('folder.delete');
        Route::delete('/file/{id}', [FileController::class, 'destroy'])->name('file.delete');
        Route::get('/folders', [FolderController::class, 'index'])->name('folder.index');
        Route::post('/folder/create', [FolderController::class, 'create'])->name('folder.create');
        Route::get('/file/view/{id}', [FileController::class, 'view'])->name('file.view');
        Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('/clients/delete-selected', [ClientController::class, 'deleteSelected'])->name('clients.deleteSelected');
        Route::post('/check-societe-password', [App\Http\Controllers\SocieteController::class, 'checkPassword']);
        Route::post('/check-password', [App\Http\Controllers\SocieteController::class, 'checkPassword']);
        Route::post('/societes/check-password', [SocieteController::class, 'checkPassword'])->name('societes.check-password');
        Route::get('file/{fileId}/download', [AchatController::class, 'download'])->name('file.download');
        Route::post('/export-clients-pdf', [ClientsPDFExportController::class, 'export'])->name('export.clients.pdf');
        Route::get('/export-clients', [ClientController::class, 'export'])->name('export.clients');
        Route::get('/achat', [AchatController::class, 'index'])->name('achat.view');
        Route::get('/vente', [VenteController::class, 'index'])->name('vente.view');
        Route::get('/banque', [BanqueController::class, 'index'])->name('banque.view');
        Route::get('/caisse', [CaisseController::class, 'index'])->name('caisse.view');
        Route::get('/impot', [ImpotController::class, 'index'])->name('impot.view');
        Route::get('/paie', [PaieController::class, 'index'])->name('paie.view');
        Route::post('/uploadFusionner', [FileUploadController::class, 'uploadFusionner'])->name('uploadFusionner');
        Route::post('/upload-file', [FileUploadController::class, 'upload'])->name('uploadFile');
        Route::post('/societes/import', [SocieteController::class, 'import'])->name('societes.import');
        Route::get('/rubriques-tva', [societeController::class, 'getRubriquesTva']);
        Route::get('/societes/export', [SocietesPDFExportController::class, 'exportPDF'])->name('societes.export');
        Route::get('/export-clients-pdf', [ClientsPDFExportController::class, 'export'])->name('export.clients.pdf');
        Route::get('/export-societes', function () {
            return Excel::download(new SocietesExport, 'societes.xlsx');
        })->name('export.societes');
        Route::post('/operation-courante', [OperationCouranteController::class, 'store']);
        Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::post('/import-clients', [ClientController::class, 'importClients'])->name('import.clients');
        Route::get('/rubriques-tva', [SocieteController::class, 'getRubriquesTVA']);
        Route::post('/import-excel', [ImportExcelController::class, 'import'])->name('import.excel');
        Route::middleware(['auth', 'permission:vue_client'])->get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::post('/clients', [ClientController::class, 'store'])->name('client.store');
        Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');

        Route::delete('/societes/{id}', [SocieteController::class, 'destroy'])->name('societes.destroy');
        Route::get('/societes/{id}', [SocieteController::class, 'show'])->name('societes.show');
        Route::get('/societes/data', [SocieteController::class, 'getData'])->name('societes.data');
        Route::post('/societes', [SocieteController::class, 'store'])->name('societes.store');
        Route::get('/societes/{id}/edit', [SocieteController::class, 'edit'])->name('societes.edit');
        Route::put('/societes/{id}', [SocieteController::class, 'update']);
        Route::get('/societes', [SocieteController::class, 'index'])->name('societes.index');
        Route::middleware(['auth', 'permission:vue_dashboard'])->get('dashboard', [SocieteController::class, 'index'])->name('dashboard');
        Route::get('/', [HomeController::class, 'home']);
        Route::get('gestion-des-journaux', function () {
                return view('gestion-des-journaux');
            })->name('gestion-des-journaux');
        Route::get('profile', function () {
                return view('profile');
            })->name('profile');


        Route::get('Grand-livre', function () {
                return view('Grand-livre');
            })->name('Grand-livre');
        Route::get('static-sign-in', function () {
                return view('static-sign-in');
            })->name('sign-in');
        Route::get('static-sign-up', function () {
                return view('static-sign-up');
            })->name('sign-up');
        Route::get('/logout', [SessionsController::class, 'destroy']);
        Route::get('/user-profile', [InfoUserController::class, 'create']);
        Route::post('/user-profile', [InfoUserController::class, 'store']);
        Route::get('/login', function () {
                return view('dashboard');
            })->name('sign-up');
});



Route::group(['middleware' => 'guest'], function () {
        Route::get('/register', [RegisterController::class, 'create']);
        Route::post('/register', [RegisterController::class, 'store']);
        Route::get('/login', [SessionsController::class, 'create']);
        Route::post('/session', [SessionsController::class, 'store']);
        Route::get('/login/forgot-password', [ResetController::class, 'create']);
        Route::post('/forgot-password', [ResetController::class, 'sendEmail']);
        Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('password.reset');
        Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');
});


Route::get('/login', [SessionsController::class, 'create'])->name('login');

