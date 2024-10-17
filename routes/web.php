<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\saisiemouvementController;
use App\Http\Controllers\SessionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['middleware' => 'auth'], function () {

    Route::get('/', [HomeController::class, 'home']);
	Route::get('dashboard', function () {
		return view('dashboard');
	})->name('dashboard');

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

Route::get('/login', function () {
    return view('session/login-session');
})->name('login');