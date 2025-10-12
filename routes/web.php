<?php

use App\Livewire\Dashboard;
use App\Livewire\UEEC\AddUnite;
use App\Livewire\UEEC\EditUnite;
use App\Livewire\Examen\AddExamen;
use App\Livewire\Salle\SalleIndex;
use App\Livewire\Student\Students;
use App\Livewire\Copie\CopieSaisie;
use App\Livewire\Copie\CopiesIndex;
use App\Livewire\Examen\EditExamen;
use App\Livewire\UEEC\UniteElement;
use App\Livewire\Examen\IndexExamen;
use App\Livewire\Fusion\FusionIndex;
use App\Livewire\Student\AddEtudiant;
use Illuminate\Support\Facades\Route;
use App\Livewire\Student\EditEtudiant;
use App\Livewire\Copie\CopiesCorbeille;
use App\Livewire\Resultats\ReleveNotes;
use App\Livewire\Settings\SessionExamens;
use App\Livewire\Settings\UserManagement;
use App\Livewire\Resultats\ResultatsPACES;
use App\Livewire\Manchette\ManchetteSaisie;
use App\Livewire\Manchette\ManchettesIndex;
use App\Livewire\Resultats\ResultatsFinale;
use App\Livewire\Settings\AnneeUniversites;
use App\Livewire\Settings\RolesPermissions;
use App\Livewire\Manchette\ManchettesCorbeille;
use App\Livewire\Resultats\ListeResultatsPACES;
use App\Livewire\Resultats\ResultatVerification;
use App\Livewire\Resultats\SimulationDeliberation;

Route::redirect('/', '/login');
Route::redirect('/register', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // ========================================
    // SCOLARITÉS - Réservé SUPERADMIN UNIQUEMENT
    // ========================================
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('/etudiants/ajouter/{niveau}/{parcour}', AddEtudiant::class)->name('add_etudiant');
        Route::get('/etudiants/modifier/{etudiant}', EditEtudiant::class)->name('edit_etudiant');
        Route::get('/salle', SalleIndex::class)->name('salles.index');
    });

    // ========================================
    // TRAITEMENTS - Accessible aux : superadmin, enseignant, secretaire
    // ========================================
    Route::middleware(['role:secretaire'])->group(function () {
        // Copies
        Route::prefix('copies')->name('copies.')->group(function () {
            Route::get('/saisie', CopieSaisie::class)->name('saisie');
        });

        // Manchettes
        Route::prefix('manchettes')->name('manchettes.')->group(function () {
            Route::get('/saisie', ManchetteSaisie::class)->name('saisie');
        });

        // Students
        Route::get('/etudiants', Students::class)->name('students');
    });

    Route::middleware(['role:superadmin|enseignant|secretaire'])->group(function () {
        // Copies
        Route::prefix('copies')->name('copies.')->group(function () {
            Route::get('/saisie', CopieSaisie::class)->name('saisie');
        });

        // Manchettes
        Route::prefix('manchettes')->name('manchettes.')->group(function () {
            Route::get('/saisie', ManchetteSaisie::class)->name('saisie');
        });

        // Students
        Route::get('/etudiants', Students::class)->name('students');
    });

    // ========================================
    // RÉSULTATS - Accessible aux : superadmin, enseignant
    // ========================================
    Route::middleware(['role:superadmin|enseignant'])->group(function () {
        Route::prefix('resultats')->name('resultats.')->group(function () {
            Route::get('/fusion', FusionIndex::class)->name('fusion');
            Route::get('/verifier/{examenId}', ResultatVerification::class)->name('verification');
            Route::get('/finale', ResultatsFinale::class)->name('finale');
            
            // ✅ Route liste PACES (existante)
            Route::get('/resultats-paces', ListeResultatsPACES::class)->name('paces-concours');
            // ✅ NOUVELLE ROUTE : Simulation avec paramètre parcours
            Route::get('/resultats-paces/deliberation/{parcoursSlug}', SimulationDeliberation::class)
                ->name('paces-deliberation');

            Route::get('/releve-notes', ReleveNotes::class)->name('releve-notes.index');

            Route::get('/releve-notes/{etudiant}/{session}', function($etudiantId, $sessionId) {
                $releveComponent = new ReleveNotes();
                $donneesReleve = $releveComponent->getDonneesReleve($etudiantId, $sessionId);
                
                    return view('livewire.resultats.partials.releve-notes-show', $donneesReleve);
                })->name('releve-notes.show');
            });

        });

            Route::get('/liste-manchettes', ManchettesIndex::class)->name('manchette.index');
            Route::get('/liste-copies-notes', CopiesIndex::class)->name('copie.index');

            Route::get('/corbeille/manchettes', ManchettesCorbeille::class)->name('manchettes.corbeille');
            Route::get('/corbeille/copies', CopiesCorbeille::class)->name('copies.corbeille');

            Route::prefix('examens')->name('examens.')->group(function () {
            Route::get('/', IndexExamen::class)->name('index');
            Route::get('/ajouter/{niveau}-{parcour}', AddExamen::class)->name('create');
            Route::get('/modifier/{examen}', EditExamen::class)->name('edit');
            });
            Route::get('/examens/reset', function () {
                session()->forget(['examen_niveau_id', 'examen_parcours_id']);
                return redirect()->route('examens.index');
            })->name('examens.reset');


            Route::get('/unite-enseignement', UniteElement::class)->name('unite_e');
            Route::get('/unite-enseignement/ajouter/{niveau}-{parcour}', AddUnite::class)->name('add_ue');
            Route::get('/unite-enseignement/edit/{ue}', EditUnite::class)->name('edit_ue');
    });

    // ========================================
    // PARAMÈTRAGES - Réservé SUPERADMIN UNIQUEMENT
    // ========================================
    Route::middleware(['role:superadmin'])->group(function () {
        Route::prefix('parametres')->name('setting.')->group(function () {
            Route::get('/gestion-utilisateurs', UserManagement::class)->name('user_management');
            Route::get('/annee-universite', AnneeUniversites::class)->name('annee_universite');
            Route::get('/session-examen', SessionExamens::class)->name('session_examen');
            Route::get('/roles-permission', RolesPermissions::class)->name('roles_permission');
        });
    });


require __DIR__ . '/auth.php';