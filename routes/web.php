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


Route::get('/exercices/{id}', [ExerciceController::class, 'show'])->name('exercices.show');

Route::get('/achat', [AchatController::class, 'index'])->name('achat.view');
Route::get('/vente', [VenteController::class, 'index'])->name('vente.view');
Route::get('/banque', [BanqueController::class, 'index'])->name('banque.view');
Route::get('/caisse', [CaisseController::class, 'index'])->name('caisse.view');
Route::get('/impot', [ImpotController::class, 'index'])->name('impot.view');
Route::get('/paie', [PaieController::class, 'index'])->name('paie.view');

// Route pour le téléchargement de fichiers
Route::post('/upload-file', [FileUploadController::class, 'upload'])->name('uploadFile');


    Route::get('/exercices/{id}', [ExerciceController::class, 'show'])->name('exercices.show');

Route::post('/societes/import', [SocieteController::class, 'import'])->name('societes.import');



Route::get('/rubriques-tva', [societeController::class, 'getRubriquesTva']);


Route::group(['middleware' => 'auth'], function () {
    
// Route pour l'exportation PDF
Route::get('/societes/export', [SocietesPDFExportController::class, 'exportPDF'])->name('societes.export');


Route::get('/export-clients-pdf', [ClientsPDFExportController::class, 'export'])->name('export.clients.pdf');


Route::get('/export-clients', function () {
    return Excel::download(new ClientsExport, 'clients.xlsx');
})->name('export.clients');


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

// Route::post('/societes/import', [SocieteController::class, 'import'])->name('societes.import');
   
// Route::get('/societes/import', [SocieteController::class, 'showImportForm'])->name('societes.import.form');

// Route::post('/societes/import', [SocieteController::class, 'import'])->name('societes.import');

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

// Route pour la page de connexion
Route::get('/login', function () {
    return view('session/login-session');
})->name('login');
