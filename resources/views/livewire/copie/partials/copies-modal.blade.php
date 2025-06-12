<!-- Modale de saisie de copie - Design moderne avec OCR -->
@if($showCopieModal)
<div class="fixed inset-0 z-10 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

        <!-- Centrage modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Contenu modal - ÉLARGI pour plus d'espace -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 dark:bg-gray-800">
                <div class="sm:flex sm:items-start">
                    <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <!-- En-tête avec icône -->
                        <div class="flex items-center mb-4">
                            <div class="flex items-center justify-center w-12 h-12 mx-auto mr-4 bg-blue-100 rounded-full dark:bg-blue-900">
                                <em class="text-xl text-blue-600 icon ni ni-form-validation dark:text-blue-400"></em>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                    {{ isset($editingCopieId) ? 'Modifier une note' : 'Saisir une note' }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Session {{ $currentSessionType ?? 'Normale' }} - Saisie anonyme
                                </p>
                            </div>
                        </div>

                        <!-- Informations contextuelles améliorées -->
                        <div class="p-4 mb-4 border border-blue-200 rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 dark:border-blue-700">
                            <div class="flex items-center mb-2">
                                <em class="mr-2 text-blue-600 icon ni ni-info-circle dark:text-blue-400"></em>
                                <span class="text-sm font-semibold text-blue-800 dark:text-blue-200">Contexte de saisie</span>
                            </div>
                            <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                <div class="flex items-center">
                                    <em class="mr-2 text-gray-500 icon ni ni-building"></em>
                                    <span class="text-gray-600 dark:text-gray-300">Salle: <strong>{{ $currentSalleName }}</strong></span>
                                </div>
                                <div class="flex items-center">
                                    <em class="mr-2 text-gray-500 icon ni ni-hash"></em>
                                    <span class="text-gray-600 dark:text-gray-300">Code salle: <strong>{{ $selectedSalleCode }}</strong></span>
                                </div>
                                <div class="flex items-center">
                                    <em class="mr-2 text-gray-500 icon ni ni-book"></em>
                                    <span class="text-gray-600 dark:text-gray-300">Matière: <strong>{{ $currentEcName }}</strong></span>
                                </div>
                                <div class="flex items-center">
                                    <em class="mr-2 text-gray-500 icon ni ni-calendar"></em>
                                    <span class="text-gray-600 dark:text-gray-300">Date: <strong>{{ $currentEcDate ?: 'Non définie' }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Badge d'anonymat -->
                        <div class="flex items-center justify-center p-3 mb-4 border border-yellow-200 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                            <em class="mr-2 text-yellow-600 icon ni ni-shield-check dark:text-yellow-400"></em>
                            <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Saisie anonyme - Aucune information d'étudiant visible
                            </span>
                        </div>

                        <!-- Formulaire -->
                        <form wire:submit.prevent="saveCopie">
                            <div class="space-y-6">
                                <!-- Code anonymat avec design amélioré -->
                                <div>
                                    <label for="code_anonymat" class="flex items-center mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <em class="mr-2 text-primary-600 icon ni ni-code dark:text-primary-400"></em>
                                        Code d'anonymat
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                            wire:model.live="code_anonymat"
                                            id="code_anonymat"
                                            class="block w-full px-4 py-3 font-mono text-lg border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
                                            placeholder="Ex: {{ $selectedSalleCode }}1"
                                            autofocus>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <em class="text-gray-400 icon ni ni-hash"></em>
                                        </div>
                                    </div>
                                    @if($code_anonymat)
                                    <div class="flex items-center mt-2 text-xs text-green-600 dark:text-green-400">
                                        <em class="mr-1 icon ni ni-check-circle"></em>
                                        Code suggéré: <strong class="ml-1 font-mono">{{ $code_anonymat }}</strong> - Vérifiez qu'il correspond à celui de la copie
                                    </div>
                                    @endif
                                    @error('code_anonymat')
                                    <div class="flex items-center mt-2 text-sm text-red-600 dark:text-red-500">
                                        <em class="mr-1 icon ni ni-alert-circle"></em>
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Note avec OCR -->
                                <div>
                                    <label for="note" class="flex items-center mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <em class="mr-2 text-primary-600 icon ni ni-edit dark:text-primary-400"></em>
                                        Note sur 20
                                    </label>
                                    <div class="relative">
                                        <input type="number"
                                            wire:model="note"
                                            id="note"
                                            step="0.01"
                                            min="0"
                                            max="20"
                                            class="block w-full px-4 py-3 pr-20 text-lg border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
                                            placeholder="Ex: 15.50">

                                        <!-- Bouton OCR -->
                                        <button type="button"
                                                onclick="startOCR()"
                                                class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 transition-colors rounded-r-lg hover:text-primary-600 hover:bg-gray-50 dark:hover:text-primary-400 dark:hover:bg-gray-600"
                                                title="Scanner la note avec OCR">
                                            <em class="text-lg icon ni ni-scan"></em>
                                        </button>
                                    </div>

                                    <!-- Indicateur de validation de note -->
                                    @if($note)
                                    <div class="mt-2">
                                        @if($note >= 0 && $note <= 20)
                                            <div class="flex items-center text-xs">
                                                @if($note >= 16)
                                                    <span class="inline-flex items-center px-2 py-1 text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                                        <em class="mr-1 icon ni ni-star-fill"></em>
                                                        Très bien ({{ number_format($note, 2) }}/20)
                                                    </span>
                                                @elseif($note >= 14)
                                                    <span class="inline-flex items-center px-2 py-1 text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                                        <em class="mr-1 icon ni ni-thumbs-up"></em>
                                                        Bien ({{ number_format($note, 2) }}/20)
                                                    </span>
                                                @elseif($note >= 12)
                                                    <span class="inline-flex items-center px-2 py-1 text-indigo-800 bg-indigo-100 rounded-full dark:bg-indigo-900 dark:text-indigo-200">
                                                        <em class="mr-1 icon ni ni-check-circle"></em>
                                                        Assez bien ({{ number_format($note, 2) }}/20)
                                                    </span>
                                                @elseif($note >= 10)
                                                    <span class="inline-flex items-center px-2 py-1 text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-200">
                                                        <em class="mr-1 icon ni ni-check"></em>
                                                        Passable ({{ number_format($note, 2) }}/20)
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 text-red-800 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-200">
                                                        <em class="mr-1 icon ni ni-cross-circle"></em>
                                                        Non validé ({{ number_format($note, 2) }}/20)
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    @endif

                                    @error('note')
                                    <div class="flex items-center mt-2 text-sm text-red-600 dark:text-red-500">
                                        <em class="mr-1 icon ni ni-alert-circle"></em>
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Boutons d'action améliorés -->
                            <div class="flex flex-col gap-3 mt-6 sm:flex-row sm:justify-end">
                                @if(!isset($editingCopieId))
                                <button type="button"
                                        wire:click="$set('showCopieModal', false)"
                                        class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                    <em class="mr-2 icon ni ni-cross"></em>
                                    Terminer la saisie
                                </button>
                                @else
                                <button type="button"
                                        wire:click="$set('showCopieModal', false)"
                                        class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                    <em class="mr-2 icon ni ni-cross"></em>
                                    Annuler
                                </button>
                                @endif

                                <button type="submit"
                                        class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-white transition-colors border border-transparent rounded-lg shadow-sm
                                        {{ isset($editingCopieId)
                                            ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-800'
                                            : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 dark:bg-primary-700 dark:hover:bg-primary-800'
                                        }}
                                        focus:outline-none focus:ring-2 focus:ring-offset-2">

                                    @if(isset($editingCopieId))
                                        <em class="mr-2 icon ni ni-update"></em>
                                        Mettre à jour la note
                                    @else
                                        <em class="mr-2 icon ni ni-save"></em>
                                        Enregistrer et continuer
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript pour OCR et interactions -->
@push('scripts')
<script>
// Fonction OCR simulée (à remplacer par une vraie implémentation)
function startOCR() {
    // Afficher une notification d'information
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 z-50 px-4 py-3 bg-blue-600 text-white rounded-lg shadow-lg flex items-center';
    notification.innerHTML = `
        <em class="mr-2 icon ni ni-scan animate-pulse"></em>
        <div>
            <div class="font-medium">Fonctionnalité OCR</div>
            <div class="text-sm opacity-90">En cours de développement...</div>
        </div>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);

    // Exemple : focus sur le champ note après simulation OCR
    setTimeout(() => {
        const noteField = document.getElementById('note');
        if (noteField) {
            noteField.focus();
        }
    }, 500);
}

// Gestion des raccourcis clavier
document.addEventListener('keydown', function(e) {
    // Ctrl+Enter pour enregistrer
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        const modal = document.querySelector('[aria-modal="true"]');
        if (modal) {
            e.preventDefault();
            const submitButton = document.querySelector('button[type="submit"]');
            if (submitButton && !submitButton.disabled) {
                submitButton.click();
            }
        }
    }

    // Échap pour fermer
    if (e.key === 'Escape') {
        const modal = document.querySelector('[aria-modal="true"]');
        if (modal) {
            e.preventDefault();
            @this.set('showCopieModal', false);
        }
    }
});

// Focus automatique et sélection du texte
document.addEventListener('livewire:init', function() {
    Livewire.on('focus-note-field', function() {
        setTimeout(function() {
            const noteField = document.getElementById('note');
            if (noteField) {
                noteField.focus();
                noteField.select();
            }
        }, 200);
    });
});

// Animation de la progression des notes
function animateNoteProgress() {
    const noteField = document.getElementById('note');
    if (noteField) {
        noteField.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value >= 0 && value <= 20) {
                // Animation subtile du champ
                this.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            }
        });
    }
}

// Initialiser les animations quand le DOM est prêt
document.addEventListener('DOMContentLoaded', animateNoteProgress);
</script>
@endpush
@endif
