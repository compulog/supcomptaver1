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


Route::get('/achat/view/{fileId}', [AchatController::class, 'viewFile'])->name('achat.view');


// Route::get('/achat/view/{fileId}{folderId}', [AchatController::class, 'viewFile'])->name('achat.view');

Route::post('/dossier', [DossierController::class, 'store'])->name('dossier.store');

Route::get('/exercices/{societe_id}', [DossierController::class, 'show'])->name('exercices.show');
// Route::get('/exercices/{id}', [ExerciceController::class, 'show'])->name('exercices.show');

Route::group(['middleware' => 'auth'], function () {
   
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
        Route::get('/folder/{id}', [FolderController::class, 'index'])->name('folder.show');
        Route::delete('/societes/delete-selected', [SocieteController::class, 'deleteSelected'])->name('societes.deleteSelected');
        Route::delete('/folder/{id}', [FolderController::class, 'destroy'])->name('folder.delete');
        Route::delete('/file/{id}', [FileController::class, 'destroy'])->name('file.delete');
        Route::get('/folders', [FolderController::class, 'index'])->name('folder.index');
        Route::post('/folder/create', [FolderController::class, 'create'])->name('folder.create');
        Route::get('/achat', [AchatController::class, 'index'])->name('achat.view');
        Route::get('/file/view/{id}', [FileController::class, 'view'])->name('file.view');
        Route::get('/file/view/{id}', [FileController::class, 'view'])->name('file.view');
        Route::get('/file/view/{id}', [FileController::class, 'view'])->name('file.view');
        Route::post('/clients/delete-selected', [ClientController::class, 'deleteSelected'])->name('clients.deleteSelected');
        Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('/clients/delete-selected', [ClientController::class, 'deleteSelected'])->name('clients.deleteSelected');
        Route::post('/check-societe-password', [App\Http\Controllers\SocieteController::class, 'checkPassword']);
        Route::post('/check-password', [App\Http\Controllers\SocieteController::class, 'checkPassword']);
        Route::post('/societes/check-password', [SocieteController::class, 'checkPassword'])->name('societes.check-password');
        Route::get('file/{fileId}/download', [AchatController::class, 'download'])->name('file.download');
        Route::post('/export-clients-pdf', [ClientsPDFExportController::class, 'export'])->name('export.clients.pdf');
        Route::post('/export-clients', [ClientController::class, 'export'])->name('export.clients');
        // Route::get('/exercices/{id}', [ExerciceController::class, 'show'])->name('exercices.show');
        Route::get('/achat', [AchatController::class, 'index'])->name('achat.view');
        Route::get('/vente', [VenteController::class, 'index'])->name('vente.view');
        Route::get('/banque', [BanqueController::class, 'index'])->name('banque.view');
        Route::get('/caisse', [CaisseController::class, 'index'])->name('caisse.view');
        Route::get('/impot', [ImpotController::class, 'index'])->name('impot.view');
        Route::get('/paie', [PaieController::class, 'index'])->name('paie.view');
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
        Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');
        // Route::get('/exercice/{id}', function ($id) {
        // return view('exercice.show', ['id' => $id]);
        // })->name('exercice.show');
        Route::post('/import-excel', [ImportExcelController::class, 'import'])->name('import.excel');
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');  
        Route::post('/clients', [ClientController::class, 'store'])->name('client.store');
        Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::delete('/societes/{id}', [SocieteController::class, 'destroy'])->name('societes.destroy');
        Route::get('/societes/{id}', [SocieteController::class, 'show'])->name('societes.show');
        Route::get('/societes/data', [SocieteController::class, 'getData'])->name('societes.data');
        Route::post('/societes', [SocieteController::class, 'store'])->name('societes.store');
        Route::get('/societes/{id}/edit', [SocieteController::class, 'edit'])->name('societes.edit');
        Route::put('/societes/{id}', [SocieteController::class, 'update']);
        Route::get('/societes', [SocieteController::class, 'index'])->name('societes.index');
        Route::get('dashboard', [SocieteController::class, 'index'])->name('dashboard');
        Route::get('/', [HomeController::class, 'home']);
        Route::get('gestion_des_journaux', function () {
                return view('gestion_des_journaux');
            })->name('gestion_des_journaux');
        Route::get('profile', function () {
                return view('profile');
            })->name('profile');
        Route::get('/saisie', [SaisieMouvementController::class, 'index'])->name('saisie.index');
        Route::post('/saisie', [SaisieMouvementController::class, 'store'])->name('saisie.store');
        Route::get('client', function () {
                return view('client');
            })->name('client');
        Route::get('Fournisseurs', function () {
                return view('Fournisseurs');
            })->name('Fournisseurs');
        Route::get('saisie mouvement(J ACH-VTE)', function () {
                return view('saisie mouvement(J ACH-VTE)');
            })->name('saisie mouvement(J ACH-VTE)');
        Route::get('plan_comptable', function () {
                return view('plan_comptable');
            })->name('plan_comptable');
        Route::get('saisie de mouvement TRESO', function () {
                return view('saisie de mouvement TRESO');
            })->name('saisie de mouvement TRESO');
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

// // Route pour la page de connexion
// Route::get('/login', function () {
//     return view('session/login-session');
// })->name('login');
Route::get('/login', [SessionsController::class, 'create'])->name('login');

