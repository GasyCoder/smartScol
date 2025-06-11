<div class="container px-4 py-6 mx-auto">
    <!-- En-tête fixe avec titre et actions globales -->
    <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal avec informations de session -->
             <div class="flex items-center space-x-3">
                <h2 class="text-xl font-medium text-slate-700 dark:text-white">Saisie des Manchettes</h2>
                <!-- NOUVEAU : Affichage des informations de session -->
                @if($currentSessionType)
                    <span class="px-3 py-1 text-sm font-medium rounded-full
                        {{ $currentSessionType === 'Normale'
                            ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200 dark:border-green-700'
                            : 'bg-orange-100 text-orange-800 border border-orange-200 dark:bg-orange-900 dark:text-orange-200 dark:border-orange-700'
                        }}">
                        Session {{ $currentSessionType }}
                        @if($session_exam_id)
                            @php
                                $sessionInfo = App\Models\SessionExam::find($session_exam_id);
                            @endphp
                            @if($sessionInfo && $sessionInfo->date_debut)
                                <span class="ml-1 text-xs opacity-75">
                                    ({{ \Carbon\Carbon::parse($sessionInfo->date_debut)->format('d/m/Y') }})
                                </span>
                            @endif
                        @endif
                    </span>
                @endif
            </div>
            <!-- Actions globales -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('manchettes.corbeille') }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                    <em class="mr-1 icon ni ni-trash-alt"></em>
                    Corbeille
                </a>
            </div>
        </div>
    </div>

    <!-- NOUVEAU : Alerte d'information de session -->
    @if(isset($sessionInfo) && is_array($sessionInfo) && ($sessionInfo['message'] ?? ''))
    <div class="mb-4">
        <div class="flex items-center p-4 rounded-lg
            {{ ($sessionInfo['can_add'] ?? false) ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800' : 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800' }}">
            <div class="flex-shrink-0">
                @if($sessionInfo['can_add'] ?? false)
                    <em class="text-blue-400 icon ni ni-info-circle"></em>
                @else
                    <em class="text-red-400 icon ni ni-alert-triangle"></em>
                @endif
            </div>
            <div class="ml-3">
                <p class="text-sm
                    {{ ($sessionInfo['can_add'] ?? false) ? 'text-blue-800 dark:text-blue-200' : 'text-red-800 dark:text-red-200' }}">
                    {{ $sessionInfo['message'] }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Messages d'état -->
    @if($message)
    <div class="mb-4">
        <div class="{{ $messageType === 'success' ? 'bg-green-100 border-green-500 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-200' : 'bg-red-100 border-red-500 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-200' }} px-4 py-3 rounded relative border-l-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    </div>
    @endif

    <!-- NOUVEAU : Alerte si aucune session active -->
    @if(isset($sessionInfo) && is_array($sessionInfo) && !($sessionInfo['can_add'] ?? true))
    <div class="mb-6">
        <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <em class="text-yellow-400 icon ni ni-alert-triangle"></em>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Saisie des manchettes indisponible
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>{{ $sessionInfo['message'] ?? 'Aucune session active' }}</p>
                        <p class="mt-1">Veuillez contacter l'administrateur pour activer une session d'examen.</p>
                    </div>
                </div>
            </div>
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
                <!-- NOUVEAU : Afficher la session de la manchette -->
                @if(isset($manchetteToDelete->sessionExam))
                de la session {{ $manchetteToDelete->sessionExam->type ?? 'Inconnue' }}
                @endif
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

            // NOUVEAU : Écouter les changements de session
            window.livewire.on('session-changed', function (data) {
                console.log('Session changée:', data);
                // Vous pouvez ajouter des actions supplémentaires ici
            });
        });
    </script>
    @endpush
</div>
