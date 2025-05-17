<?php


use App\Livewire\Dashboard;
use App\Livewire\UEEC\AddUnite;
use App\Livewire\UEEC\EditUnite;
use App\Livewire\Examen\AddExamen;
use App\Livewire\Salle\SalleIndex;
use App\Livewire\Student\Students;
use App\Livewire\Copie\CopiesIndex;
use App\Livewire\Examen\EditExamen;
use App\Livewire\UEEC\UniteElement;
use App\Livewire\Examen\IndexExamen;
use App\Livewire\Student\AddEtudiant;
use Illuminate\Support\Facades\Route;
use App\Livewire\Student\EditEtudiant;
use App\Livewire\Copie\CopiesCorbeille;
use App\Http\Controllers\ProfileController;
use App\Livewire\Manchette\ManchettesIndex;
use App\Livewire\Manchette\ManchettesCorbeille;


Route::redirect('/', '/login');
Route::redirect('/register', '/login');

// Routes nécessitant une authentification
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - nécessite vérification de l'email
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/unite-enseignement', UniteElement::class)->name('unite_e');
    Route::get('/unite-enseignement/ajouter/{niveau}-{parcour}', AddUnite::class)->name('add_ue');
    Route::get('/unite-enseignement/edit/{ue}', EditUnite::class)->name('edit_ue');

    Route::get('/etudiants', Students::class)->name('students');
    Route::get('/etudiants/ajouter/{niveau}/{parcour}', AddEtudiant::class)->name('add_etudiant');
    Route::get('/etudiants/modifier/{etudiant}', EditEtudiant::class)->name('edit_etudiant');

    Route::get('/salle', SalleIndex::class)->name('salles.index');

    // Examens
    Route::prefix('examens')->name('examens.')->group(function () {
        Route::get('/', IndexExamen::class)->name('index');
        Route::get('/ajouter/{niveau}-{parcour}', AddExamen::class)->name('create');
        Route::get('/modifier/{examen}', EditExamen::class)->name('edit');
    });

    Route::get('/examens/reset', function () {
        session()->forget(['examen_niveau_id', 'examen_parcours_id']);
        return redirect()->route('examens.index');
    })->name('examens.reset');

    //Copies
    Route::prefix('copies')->name('copies.')->group(function () {
        Route::get('/', CopiesIndex::class)->name('index');
        Route::get('/copies/corbeille', CopiesCorbeille::class)->name('corbeille');
    });

    // Manchettes
    Route::prefix('manchettes')->name('manchettes.')->group(function () {
        Route::get('/', ManchettesIndex::class)->name('index');
        Route::get('/manchette/corbeille', ManchettesCorbeille::class)->name('corbeille');
    });


    // Routes de gestion du profil
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});

require __DIR__ . '/auth.php';
