<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\ParticipationController;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Redirection automatique de l'accueil vers la page de connexion
Route::get('/', function () {
    return redirect()->route('login');
});
// Affichage du formulaire de connexion
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
// Soumission du formulaire
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
// Déconnexion
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// Routes protégées par connexion
Route::middleware('auth')->group(function () {
    Route::get('/accueil', function () {
        return view('accueil');
    })->name('accueil');

    // Gestion des événements
    Route::get('/evenements', [EvenementController::class, 'index'])->name('evenements.index');
    Route::post('/evenements', [EvenementController::class, 'store'])->name('evenements.store');
    Route::put('/evenements/{evenement}', [EvenementController::class, 'update'])->name('evenements.update');
    Route::delete('/evenements/{evenement}', [EvenementController::class, 'destroy'])->name('evenements.destroy');

    // Gestion des étudiants
    Route::get('/etudiants', [EtudiantController::class, 'index'])->name('etudiants.index');
    Route::post('/etudiants', [EtudiantController::class, 'store'])->name('etudiants.store');
    Route::put('/etudiants/{etudiant}', [EtudiantController::class, 'update'])->name('etudiants.update');
    Route::delete('/etudiants/{etudiant}', [EtudiantController::class, 'destroy'])->name('etudiants.destroy');
    Route::post('/etudiants/import', [EtudiantController::class, 'import'])->name('etudiants.import');
});
