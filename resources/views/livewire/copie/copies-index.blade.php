<div>
    <div class="container px-4 py-6 mx-auto">
    <!-- En-tête fixe avec titre et actions globales -->
    <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal -->
            <h2 class="text-xl font-medium text-slate-700 dark:text-white">Saisie des Notes de Copies</h2>
            <!-- Actions globales -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('copies.corbeille') }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                   <em class="mr-1 text-sm icon ni ni-trash-alt"></em>
                    Corbeille
                </a>
            </div>
        </div>
    </div>

    <!-- Messages d'état -->
    @if($message)
    <div class="mb-4">
        <div class="{{ $messageType === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700' }} px-4 py-3 rounded relative border-l-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    </div>
    @endif

    <!-- Barre de filtres et contexte actuel -->
    @include('livewire.copie.partials.copies-filtre')
    <!-- Tableau des copies -->
    @include('livewire.copie.copies-table')

    <!-- Section des étudiants sans note - UI/UX améliorée -->
    @if($ec_id && $ec_id !== 'all' && !empty($etudiantsSansCopies))
    <div class="border-t border-gray-200 dark:border-gray-700">
        <div class="p-4 bg-gray-50 dark:bg-gray-800">
            <div class="flex flex-col mb-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="flex items-center text-base font-medium text-gray-900 dark:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Étudiants sans note <span class="ml-2 text-sm font-semibold bg-yellow-100 text-yellow-800 py-0.5 px-2 rounded-full dark:bg-yellow-900 dark:text-yellow-200">{{ count($etudiantsSansCopies) }}</span>
                </h3>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de saisie de note -->
    @include('livewire.copie.partials.copies-modal')


    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de suppression</h3>
            <p class="mb-6 text-gray-700 dark:text-gray-300">
                Êtes-vous sûr de vouloir supprimer cette note
                @if($copieToDelete)
                (Code: {{ $copieToDelete->codeAnonymat->code_complet ?? 'N/A' }},
                Note: {{ number_format($copieToDelete->note, 2) }}/20)
                @endif
                ? Cette action est réversible (via la corbeille).
            </p>
            <div class="flex justify-end space-x-3">
                <button wire:click="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="confirmDeleteCopie" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', function() {
            // Focus automatique sur le champ note
            Livewire.on('focus-note-field', function() {
                setTimeout(function() {
                    const noteField = document.getElementById('note');
                    if (noteField) {
                        noteField.focus();
                        noteField.select(); // Sélectionne tout le texte
                    }
                }, 100);
            });

            // Soumettre le formulaire avec la touche Entrée
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && document.getElementById('note') === document.activeElement) {
                    e.preventDefault();
                    // Trouver le bouton de soumission et le cliquer
                    const submitButton = document.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.click();
                    }
                }
            });
        });
    </script>
    @endpush
</div>
</div>
