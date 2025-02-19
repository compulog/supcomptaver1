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
use App\Http\Controllers\racineController;
use App\Http\Controllers\OperationCouranteController;
use App\Http\Middleware\SetSocieteId;

Route::group(['middleware' => 'auth'], function () {

    Route::get('/comptes-achats', [JournalController::class, 'getComptesAchats']);
    Route::get('/comptes-ventes', [JournalController::class, 'getComptesVentes']);
    Route::get('/comptes-Caisse', [JournalController::class, 'getComptesCaisse']);
    Route::get('/comptes-Banque', [JournalController::class, 'getComptesBanque']);

    // Route pour afficher tous les journaux
    Route::get('/journaux', [JournalController::class, 'index'])->name('journaux.index');

    // Route pour récupérer les données des journaux (pour Tabulator)
    Route::get('/journaux/data', [JournalController::class, 'getData'])->name('journaux.data');

    // Route pour ajouter un nouveau journal
    Route::post('/journaux', [JournalController::class, 'store'])->name('journaux.store');

    // Route pour afficher un journal spécifique
    Route::get('/journaux/{id}', [JournalController::class, 'show'])->name('journaux.show');

    // Route pour afficher le formulaire d'édition
    Route::get('/journals/{id}/edit', [JournalController::class, 'edit'])->name('journals.edit');

    // Route pour mettre à jour un journal (PUT)
    Route::put('/journaux/{id}', [JournalController::class, 'update']);

    Route::post('/journaux/delete-selected', [JournalController::class, 'deleteSelected'])->name('journaux.deleteSelected');
    // Route pour supprimer un journal
    Route::delete('/journaux/{id}', [JournalController::class, 'destroy'])->name('journaux.destroy');

    // Ajouter une route pour récupérer les comptes en fonction du type de journal
    Route::get('/journaux/contre-partie/{typeJournal}', [JournalController::class, 'getComptesContrePartie']);


    Route::get('/export-fournisseurs-excel', function () {
        return Excel::download(new FournisseursExport, 'fournisseurs.xlsx');
    });


    Route::get('/export-fournisseurs-pdf', [ExportController::class, 'exportPDF']) ;
    // routes/web.php
    Route::post('/fournisseurs/delete-selected', [FournisseurController::class, 'deleteSelected'])->name('fournisseurs.deleteSelected');

    Route::get('/fournisseurs/data', [FournisseurController::class, 'getData']) ;
    // Routes pour l'API des fournisseurs
    Route::post('/fournisseurs', [FournisseurController::class, 'store']);
    Route::put('/fournisseurs/{id}', [FournisseurController::class, 'update']) ;
    Route::delete('/fournisseurs/{id}', [FournisseurController::class, 'destroy']);
    Route::get('/fournisseurs/{id}', [FournisseurController::class, 'show']);
    // Route pour afficher le formulaire d'édition
    Route::get('/fournisseurs/{id}/edit', [FournisseurController::class, 'edit'])->name('fournisseurs.edit'); ;
    Route::post('/fournisseurs/verifier-compte', [FournisseurController::class, 'verifierCompte']);


    // Route::get('/get-next-compte', [FournisseurController::class, 'getNextCompte']);
    Route::get('/get-next-compte/{societeId}', [FournisseurController::class, 'getNextCompte']);  // Route pour récupérer le prochain compte

    Route::get('/rubriques-tva', [FournisseurController::class, 'getRubriquesTva'])->name('rubriques.tva');

    Route::get('/comptes', [FournisseurController::class, 'getComptes']);
    Route::post('/fournisseurs/import', [FournisseurController::class, 'import'])->name('fournisseurs.import');
    Route::post('/import-fournisseur', [FournisseurController::class, 'importFournisseur']);

    Route::resource('operation_courante', OperationCouranteController::class);

    Route::get('/operations', [OperationCouranteController::class, 'index'])->name('operations.index');
    Route::put('update-row/{id}', [OperationCouranteController::class, 'updateRow']);
    Route::delete('delete-row/{id}', [OperationCouranteController::class, 'deleteRow']);
    Route::get('/journaux', [OperationCouranteController::class, 'getJournaux'])->name('journaux.get');
    Route::post('/lignes', [OperationCouranteController::class, 'store']);
    Route::post('/verifier-lignes-existantes', [OperationCouranteController::class, 'verifierLignesExistantes']);
    // Route pour charger les périodes
    Route::get('/periodes', [OperationCouranteController::class, 'getPeriodes'])->name('periodes.get');
    Route::post('/get-tva', [OperationCouranteController::class, 'getTva']);
    Route::get('/get-session-prorata', [OperationCouranteController::class, 'getSessionProrata']);
    Route::delete('/delete-operations', [OperationCouranteController::class, 'deleteOperations'])->name('deleteOperations');

    Route::get('/clients', [OperationCouranteController::class, 'getClients']);

    Route::get('/societe/details', [OperationCouranteController::class, 'getSocieteDetails']);
    Route::get('/get-rubriques-tva-vente', [OperationCouranteController::class, 'getRubriquesTVAVente'])->name('rubriques-tva-vente');

    // Route pour récupérer les fournisseurs
    Route::get('/journaux-achats', [OperationCouranteController::class, 'getJournauxACH']);
    Route::get('/journaux-ventes', [OperationCouranteController::class, 'getJournauxVTE']);
    Route::get('/journaux-Banque', [OperationCouranteController::class, 'getJournauxBanque']);
    Route::get('/journaux-Caisse', [OperationCouranteController::class, 'getJournauxCaisse']);
    Route::get('/journaux-operations-diverses', [OperationCouranteController::class, 'getJournauxOPE']);
    Route::get('/get-contre-parties-ventes', [OperationCouranteController::class, 'getContrePartiesVentes']);
    Route::get('/get-all-contre-parties', [OperationCouranteController::class, 'getAllContreParties']);
    Route::get('/getRubriqueSociete', [OperationCouranteController::class, 'getRubriqueSociete'])
        ->name('getRubriqueSociete');
    Route::get('/get-journaux-by-societe', [OperationCouranteController::class, 'getJournauxBySociete'])
        ->name('getJournauxBySociete');
    // Route pour supprimer une ligne
    Route::delete('/operationcourante/{id}', [OperationCouranteController::class, 'delete'])->name('operationcourante.delete');

    Route::post('/delete-rows', [OperationCouranteController::class, 'deleteRows']);
    Route::get('/get-contre-parties', [OperationCouranteController::class, 'getContreParties']);

    // Supprimer une opération
    Route::put('/plancomptable/{id}', [PlanComptableController::class, 'edit']);
    //Route::get('/plancomptable/{id}', [FournisseurController::class, 'show']);
    Route::get('/plancomptable/data', [PlanComptableController::class, 'getData']);
    Route::post('/plancomptable', [PlanComptableController::class, 'store']);

    Route::delete('/plancomptable/{id}', [PlanComptableController::class, 'destroy']);
    Route::put('/plancomptable/{id}', [PlanComptableController::class, 'update']);
    Route::get('/plancomptable/data', [PlanComptableController::class, 'index'])->name('plancomptable.index');
    Route::get('/plan-comptable/import', [PlanComptableController::class, 'showImportForm'])->name('plancomptable.importForm');
    Route::post('/plan-comptable/import', [PlanComptableController::class, 'import'])->name('plancomptable.import');
    // Route pour exporter le plan comptable au format PDF pour une société spécifique
    Route::get('export-plan-comptable', [ExportController::class, 'export'])->name('export.plan_comptable');

    Route::post('/cloturer-solde', [SoldeMensuelController::class, 'cloturerSolde']);

    Route::get('/fournisseurs', [OperationCouranteController::class, 'getFournisseurs']);
    Route::get('/clients', [OperationCouranteController::class, 'getClients']);

    // Route pour récupérer les informations de la société (exercice_social_debut)
    Route::get('/session-social', [OperationCouranteController::class, 'getSessionSocial']);

    // Routes pour récupérer les données pour le formulaire

    // Route::get('/comptes', [OperationCouranteController::class, 'getComptes'])->name('getComptes');
    Route::get('/societe/details', [OperationCouranteController::class, 'getSocieteDetails']);
    Route::get('/get-rubriques-tva', [OperationCouranteController::class, 'getRubriquesTva'])->name('rubriques-tva');
    // Route pour récupérer les comptes TVA pour les achats
    Route::get('/get-compte-tva-ach', [OperationCouranteController::class, 'getCompteTvaAch']);

    // Route pour récupérer les comptes TVA pour les ventes
    Route::get('/get-compte-tva-vente', [OperationCouranteController::class, 'getCompteTvaVente']);
    Route::get('/get-clients', [OperationCouranteController::class, 'getClients']);
    Route::get('/get-fournisseurs-avec-details', [OperationCouranteController::class, 'getFournisseursAvecDetails']);
    Route::get('/get-details-par-compte', [OperationCouranteController::class, 'getDetailsParCompte']);

    // Route pour récupérer les fournisseurs
    Route::get('/fournisseurs', [FournisseurController::class, 'index'])->name('getFournisseurs');

    // Route pour supprimer une ligne
    Route::delete('/operationcourante/{id}', [OperationCouranteController::class, 'delete'])->name('operationcourante.delete');

    Route::post('/get-operations', [OperationCouranteController::class, 'getOperations'])->name('get.operations');
    Route::post('/delete-rows', [OperationCouranteController::class, 'deleteRows']);

    Route::post('/plancomptable/deleteSelected', [PlanComptableController::class, 'deleteSelected']);


    Route::get('/plan-comptable/excel', [PlanComptableController::class, 'exportExcel'])->name('plan.comptable.excel');


    Route::get('plancomptable', function () {
        return view('plancomptable');
    })->name('plancomptable');

    Route::get('Operation_Courante', function () {
        return view('Operation_Courante');
    })->name('Operation_Courante');

    Route::get('Fournisseurs', function () {
        return view('Fournisseurs');
    })->name('Fournisseurs');

    Route::post('/delete-transaction', [TransactionController::class, 'delete'])->name('transaction.delete');
    Route::post('/save-solde', [SoldeMensuelController::class, 'saveSolde'])->name('save-solde');
    Route::put('/update-transaction/{id}', [EtatDeCaisseController::class, 'update'])->name('update-transaction');
    Route::get('/etat-caisse/{id}/edit', [EtatDeCaisseController::class, 'edit'])->name('etat-caisse.edit');
    // Route::post('/save-transaction', [TransactionController::class, 'save'])->name('save-transaction');
    Route::post('/save-transaction', [EtatDeCaisseController::class, 'save'])->name('save-transaction');
    Route::get('/etat-de-caisse', [EtatDeCaisseController::class, 'index'])->name('etat_de_caisse');
    Route::post('/uploadFileda', [DasousdossierController::class, 'upload'])->name('uploadFileda');
    Route::post('/folderdasouas/create', [DasousdossierController::class, 'create'])->name('folderdasouas.create');
    Route::get('/dasousdossier/{folderId}', [DasousdossierController::class, 'showSousDossier'])->name('dasousdossier.show');
    Route::put('/dossier/{id}', [DossierController::class, 'update']);
    Route::get('/Douvrir/{id}', [DouvrirController::class, 'show'])->name('Douvrir');
    Route::post('/Douvrir/upload', [DouvrirController::class, 'uploadFile'])->name('Douvrir.upload');
    Route::post('/Douvrir/create', [DouvrirController::class, 'create'])->name('Douvrir.create');
    // Route::post('/folder/create', [DossierOController::class, 'create'])->name('folder.create');
    // Route::post('/dossier/upload', [DossierOController::class, 'uploadFile'])->name('dossier.upload');
    // Route::get('/dossier/ouvrir/{id}', [DossierOController::class, 'show'])->name('dossier.ouvrir');
    Route::delete('/dossier/{id}/delete', [DossierController::class, 'destroy'])->name('dossier.delete');
    Route::post('/messages/reply/{parentMessageId}', [MessageController::class, 'replyToMessage'])->name('messages.reply');
    Route::post('/messages/update/{id}', [MessageController::class, 'update'])->name('messages.update');
    Route::delete('/messages/delete/{id}', [MessageController::class, 'destroy']);
    Route::get('/folderDossier_permanant/{id}', [FolderDossierPermanantController::class, 'index'])->name('folder.show');
    Route::get('/Dossier_permanant', [DossierPermanantController::class, 'index'])->name('Dossier_permanant.view');
    Route::get('/folderPaie/{id}', [FolderPaieController::class, 'index'])->name('folder.show');
    Route::get('/folderImpot/{id}', [FolderImpotController::class, 'index'])->name('folder.show');
    Route::get('/folderCaisse/{id}', [FolderCaisseController::class, 'index'])->name('folder.show');
    Route::get('/folderBanque/{id}', [FolderBanqueController::class, 'index'])->name('folder.show');
    Route::get('/folderVente/{id}', [FolderVenteController::class, 'index'])->name('folder.show');
    Route::post('/messages/updateStatus/{messageId}', [MessageController::class, 'updateStatus'])->name('messages.updateStatus');
    Route::get('/messages/getMessages', [MessageController::class, 'getMessages']);
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/achat/view/{fileId}', [AchatController::class, 'viewFile'])->name('achat.views');
    // Route::get('/achat/view/{fileId}{folderId}', [AchatController::class, 'viewFile'])->name('achat.view');
    Route::post('/dossier', [DossierController::class, 'store'])->name('dossier.store');
    Route::middleware(['auth', 'permission:vue_tableau_de_board'])->get('/exercices/{societe_id}', [DossierController::class, 'show'])->name('exercices.show');
    // Route::get('/exercices/{id}', [ExerciceController::class, 'show'])->name('exercices.show');
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
    Route::post('import', [SocieteController::class, 'import'])->name('societes.import');
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
    Route::get('/achat', [AchatController::class, 'index'])->name('achat.view');
    Route::get('/file/view/{id}', [FileController::class, 'view'])->name('file.view');
    Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
    Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::post('/clients/delete-selected', [ClientController::class, 'deleteSelected'])->name('clients.deleteSelected');

Route::get('/get-next-compte-client/{societeId}', [ClientController::class, 'getNextCompteForClient'])
->name('client.getNextCompte');
    Route::get('file/{fileId}/download', [AchatController::class, 'download'])->name('file.download');
    Route::post('/export-clients-pdf', [ClientsPDFExportController::class, 'export'])->name('export.clients.pdf');
    Route::post('/export-clients', [ClientController::class, 'export'])->name('export.clients');
    // Route::get('/exercices/{id}', [ExerciceController::class, 'show'])->name('exercices.show');
    Route::get('/vente', [VenteController::class, 'index'])->name('vente.view');
    Route::get('/banque', [BanqueController::class, 'index'])->name('banque.view');
    Route::get('/caisse', [CaisseController::class, 'index'])->name('caisse.view');
    Route::get('/impot', [ImpotController::class, 'index'])->name('impot.view');
    Route::get('/paie', [PaieController::class, 'index'])->name('paie.view');
    Route::post('/upload-file', [FileUploadController::class, 'upload'])->name('uploadFile');
    Route::post('/societes/import', [SocieteController::class, 'import'])->name('societes.import');
    Route::get('/rubriques-tva', [societeController::class, 'getRubriquesTva']);
    Route::get('/societes/export', [SocietesPDFExportController::class, 'exportPDF'])->name('societes.export');
    Route::get('/export-societes', function () {
        return Excel::download(new SocietesExport, 'societes.xlsx');
    })->name('export.societes');
    Route::post('/operation-courante', [OperationCouranteController::class, 'store']);
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::post('/import-clients', [ClientController::class, 'importClients'])->name('import.clients');

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
    Route::get('gestion_des_journaux', function () {
            return view('gestion_des_journaux');
        })->name('gestion_des_journaux');
    Route::get('profile', function () {
            return view('profile');
        })->name('profile');

    Route::get('Grand_livre', function () {
            return view('Grand_livre');
        })->name('Grand_livre');
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

// Routes accessibles par les utilisateurs non authentifiés
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


