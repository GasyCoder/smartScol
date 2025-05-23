<div class="container px-4 py-6 mx-auto">
    <!-- En-tête fixe avec titre et actions globales -->
    <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal -->
            <h2 class="text-xl font-medium text-slate-700 dark:text-white">Saisie des Manchettes</h2>
            <!-- Actions globales -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('manchettes.corbeille') }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                    <em class="mr-1 icon ni ni-trash-alt"></em>
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

    <!-- Filtres de recherche -->
    @include('livewire.manchette.partials.manchettes-filtres')
    <!-- Liste des manchettes -->
    @include('livewire.manchette.manchettes-table')

    <!-- Modal de saisie de note -->
    @include('livewire.manchette.partials.manchettes-modal')

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de suppression</h3>
            <p class="mb-6 text-gray-700 dark:text-gray-300">
                Êtes-vous sûr de vouloir supprimer cette manchette
                @if($manchetteToDelete)
                (Code: {{ $manchetteToDelete->codeAnonymat->code_complet ?? 'N/A' }},
                Étudiant: {{ $manchetteToDelete->etudiant->matricule ?? 'N/A' }})
                @endif
                ? Cette action est réversible (via la corbeille).
            </p>
            <div class="flex justify-end space-x-3">
                <button wire:click="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="deleteManchette" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('livewire:load', function () {
            window.livewire.on('focus-search-field', function () {
                setTimeout(function() {
                    document.getElementById('searchQuery').focus();
                }, 100);
            });
        });
    </script>
    @endpush
</div>
