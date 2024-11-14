<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SaisieMouvementController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\PlanComptableController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AchatController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\BanqueController;
use App\Http\Controllers\PaieController;
use App\Http\Controllers\ImpotController;
use App\Http\Controllers\CaisseController;
use App\Exports\FournisseursExport;

use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\RacineController;
use App\Http\Controllers\OperationCouranteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SocieteController;
use App\Http\Controllers\ClientController;


use App\Http\Controllers\ImportExcelController;
use App\Exports\SocietesExport;



use App\Exports\ClientsExport;

use App\Http\Controllers\ClientsPDFExportController;
use App\Http\Controllers\SocietesPDFExportController;
use App\Http\Controllers\ExerciceController;


use App\Http\Controllers\FileUploadController;



// Route pour vérifier le mot de passe de l'utilisateur avant suppression du client
Route::post('/check-client-password', [App\Http\Controllers\ClientController::class, 'checkPassword']);



// Route pour vérifier le mot de passe de l'utilisateur
Route::post('/check-password', [App\Http\Controllers\SocieteController::class, 'checkPassword']);

// Route pour vérifier le mot de passe de l'utilisateur avant la suppression
Route::post('/societes/check-password', [SocieteController::class, 'checkPassword'])->name('societes.check-password');


Route::get('file/{fileId}/download', [AchatController::class, 'download'])->name('file.download');


Route::post('/export-clients-pdf', [ClientsPDFExportController::class, 'export'])->name('export.clients.pdf');

Route::post('/export-clients', [ClientController::class, 'export'])->name('export.clients');


Route::get('/exercices/{id}', [ExerciceController::class, 'show'])->name('exercices.show');



// Route pour le téléchargement de fichiers
Route::post('/upload-file', [FileUploadController::class, 'upload'])->name('uploadFile');


  

Route::post('/societes/import', [SocieteController::class, 'import'])->name('societes.import');


Route::group(['middleware' => 'auth'], function () {
Route::get('/rubriques-tva', [societeController::class, 'getRubriquesTva']);

// Route pour le téléchargement de fichiers
Route::post('/upload-file', [FileUploadController::class, 'upload'])->name('uploadFile');
Route::get('/achat', [AchatController::class, 'index'])->name('achat.view');
Route::get('/vente', [VenteController::class, 'index'])->name('vente.view');
Route::get('/banque', [BanqueController::class, 'index'])->name('banque.view');
Route::get('/caisse', [CaisseController::class, 'index'])->name('caisse.view');
Route::get('/impot', [ImpotController::class, 'index'])->name('impot.view');
Route::get('/paie', [PaieController::class, 'index'])->name('paie.view');



Route::post('/check-password', [FournisseurController::class, 'checkPassword'])->name('checkPassword');
    
// Route pour l'exportation PDF
Route::get('/societes/export', [SocietesPDFExportController::class, 'exportPDF'])->name('societes.export');


    Route::get('/export-clients-pdf', [ClientsPDFExportController::class, 'export'])->name('export.clients.pdf');


// Route::get('/export-clients', function () {
//     return Excel::download(new ClientsExport, 'clients.xlsx');
// })->name('export.clients');


Route::get('/export-societes', function () {
    return Excel::download(new SocietesExport, 'societes.xlsx');
})->name('export.societes');




// routes/web.php
Route::post('/operation-courante', [OperationCouranteController::class, 'store']);

    
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');

    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    
Route::post('/import-clients', [ClientController::class, 'importClients'])->name('import.clients');

Route::get('/rubriques-tva', [SocieteController::class, 'getRubriquesTVA']);



Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');


Route::get('/exercice/{id}', function ($id) {
    // Vous pouvez utiliser la logique pour récupérer les données nécessaires ici, si besoin.
    
    // Exemple d'utilisation pour afficher une vue en passant l'ID
    return view('exercice.show', ['id' => $id]);
})->name('exercice.show');



    Route::post('/import-excel', [ImportExcelController::class, 'import'])->name('import.excel');

   


    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    
    Route::post('/clients', [ClientController::class, 'store'])->name('client.store');
    Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');


   

   
    Route::delete('/societes/{id}', [SocieteController::class, 'destroy'])->name('societes.destroy');
    Route::get('/societes/{id}', [SocieteController::class, 'show'])->name('societes.show');
   
  

   
// Route pour récupérer les données des sociétés
Route::get('/societes/data', [SocieteController::class, 'getData'])->name('societes.data');
// Route pour afficher le formulaire de modification d'une société

Route::post('/societes', [SocieteController::class, 'store'])->name('societes.store');
// Dans web.php
Route::get('/societes/{id}/edit', [SocieteController::class, 'edit'])->name('societes.edit');

Route::put('/societes/{id}', [SocieteController::class, 'update']);

// Route pour récupérer les données des sociétés

// Route pour afficher la liste des sociétés (index)
Route::get('/societes', [SocieteController::class, 'index'])->name('societes.index');





    Route::get('dashboard', [SocieteController::class, 'index'])->name('dashboard'); // Afficher le dashboard
   

    // Autres routes de l'application
    Route::get('/', [HomeController::class, 'home']);
    Route::get('gestion-des-journaux', function () {
        return view('gestion-des-journaux');
    })->name('gestion-des-journaux');



    Route::get('/get-comptes-achats', [JournalController::class, 'getComptesAchats']);
Route::get('/get-comptes-ventes', [JournalController::class, 'getComptesVentes']);
Route::get('/get-comptes-tresoreries', [JournalController::class, 'getComptesTresoreries']);
 // Route pour afficher tous les journaux
Route::get('/journaux', [JournalController::class, 'index'])->name('journaux.index');

// Route pour récupérer les données des journaux (pour Tabulator)
Route::get('/journaux/data', [JournalController::class, 'getData'])->name('journaux.data');

// Route pour ajouter un nouveau journal
Route::post('/journaux', [JournalController::class, 'store'])->name('journaux.store');

// Route pour afficher un journal spécifique
Route::get('/journaux/{id}', [JournalController::class, 'show'])->name('journaux.show');

// Route pour mettre à jour un journal
Route::put('/journaux/{id}', [JournalController::class, 'update'])->name('journaux.update');
Route::post('/journaux/delete-selected', [JournalController::class, 'deleteSelected'])->name('journaux.deleteSelected');
// Route pour supprimer un journal
Route::delete('/journaux/{id}', [JournalController::class, 'destroy'])->name('journaux.destroy');
    Route::get('profile', function () {
        return view('profile');
    })->name('profile');

    Route::get('/export-fournisseurs-excel', function () {
        return Excel::download(new FournisseursExport, 'fournisseurs.xlsx');
    });
    
    Route::get('/export-fournisseurs-pdf', [ExportController::class, 'exportPDF']) ;
// routes/web.php
Route::post('/fournisseurs/delete-selected', [FournisseurController::class, 'deleteSelected'])->name('fournisseurs.deleteSelected');

Route::get('/fournisseurs/data', [FournisseurController::class, 'getData']) ;
// Routes pour l'API des fournisseurs
Route::get('/fournisseurs', [FournisseurController::class, 'index']) ;
Route::post('/fournisseurs', [FournisseurController::class, 'store']);
Route::put('/fournisseurs/{id}', [FournisseurController::class, 'update']) ;
Route::delete('/fournisseurs/{id}', [FournisseurController::class, 'destroy']);
Route::get('/fournisseurs/{id}', [FournisseurController::class, 'show']);
// Route pour afficher le formulaire d'édition
Route::get('/fournisseurs/{id}/edit', [FournisseurController::class, 'edit'])->name('fournisseurs.edit'); ;

Route::get('/get-next-compte', [FournisseurController::class, 'getNextCompte']);


Route::get('/rubriques-tva', [FournisseurController::class, 'getRubriquesTva']);
Route::get('/comptes', [FournisseurController::class, 'getComptes']);
Route::post('/fournisseurs/import', [FournisseurController::class, 'import'])->name('fournisseurs.import'); 

    Route::get('/saisie', [SaisieMouvementController::class, 'index'])->name('saisie.index');
    Route::post('/saisie', [SaisieMouvementController::class, 'store'])->name('saisie.store');

    Route::get('client', function () {
        return view('client');
    })->name('client');

    Route::get('Fournisseurs', function () {
        return view('Fournisseurs');
    })->name('Fournisseurs');

// Route pour supprimer plusieurs lignes sélectionnées
// Route pour supprimer plusieurs plans comptables




    Route::put('/plancomptable/{id}', [PlanComptableController::class, 'edit']);
    //Route::get('/plancomptable/{id}', [FournisseurController::class, 'show']);
    Route::get('/plancomptable/data', [PlanComptableController::class, 'getData']);
    Route::post('/plancomptable', [PlanComptableController::class, 'store']);
   
    Route::delete('/plancomptable/{id}', [PlanComptableController::class, 'destroy']);
    Route::put('/plancomptable/{id}', [PlanComptableController::class, 'update']);
    Route::post('/plancomptable/import', [PlanComptableController::class, 'import'])->name('plancomptable.import');
    Route::get('/plancomptable/data', [PlanComptableController::class, 'index'])->name('plancomptable.index');
    
    // Route dans web.php
    Route::post('/plancomptable/deleteSelected', [PlanComptableController::class, 'deleteSelected']);


    Route::get('/plan-comptable/import', [PlanComptableController::class, 'showImportForm'])->name('plancomptable.importForm');
    Route::post('/plan-comptable/import', [PlanComptableController::class, 'import'])->name('plancomptable.import');


// Route pour exporter le plan comptable au format PDF pour une société spécifique
Route::get('export-plan-comptable', [ExportController::class, 'export'])->name('export.plan_comptable');


        Route::get('/plan-comptable/excel', [PlanComptableController::class, 'exportExcel'])->name('plan.comptable.excel');

    Route::get('plancomptable', function () {
        return view('plancomptable');
    })->name('plancomptable');

    Route::get('saisie de mouvement TRESO', function () {
        return view('saisie de mouvement TRESO');
    })->name('saisie de mouvement TRESO');

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

// Route pour la page de connexion
Route::get('/login', function () {
    return view('session/login-session');
})->name('login');
