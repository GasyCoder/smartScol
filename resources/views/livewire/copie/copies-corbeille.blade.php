<div class="container px-4 py-6 mx-auto">
    <!-- En-tête fixe avec titre et actions globales -->
    <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal -->
            <div class="flex items-center">
                <h2 class="text-xl font-medium text-slate-700 dark:text-white">
                    <span class="mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </span>
                    Corbeille des Notes
                </h2>
            </div>

            <!-- Actions globales -->
            <div class="flex items-center space-x-2">
                @if(count($selectedItems) > 0)
                <button wire:click="restoreCopie()" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded bg-green-600 text-white hover:bg-green-700 focus:outline-none dark:bg-green-700 dark:hover:bg-green-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Restaurer sélectionnés ({{ count($selectedItems) }})
                </button>
                <button wire:click="confirmDelete()" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded bg-red-600 text-white hover:bg-red-700 focus:outline-none dark:bg-red-700 dark:hover:bg-red-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Supprimer définitivement sélectionnés
                </button>
                @endif
                <!-- Bouton de retour -->
                <a href="{{ route('copie.index') }}" class="mr-4 inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-blue-300 bg-blue-700 text-white hover:bg-blue-600 focus:outline-none dark:bg-blue-800 dark:border-blue-700 dark:text-blue-200 dark:hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour
                </a>
                <button wire:click="resetFilters" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Réinitialiser les filtres
                </button>
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
    <div class="mb-6">
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm dark:bg-gray-800 sm:rounded-lg dark:border-gray-700">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-900 dark:text-white">Filtres de recherche</h3>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <!-- Période -->
                    <div class="md:col-span-2 grid grid-cols-2 gap-3">
                        <div>
                            <label for="date_debut" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date début</label>
                            <input type="date" id="date_debut" wire:model.live="date_debut" class="block w-full py-2 px-3 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label for="date_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date fin</label>
                            <input type="date" id="date_fin" wire:model.live="date_fin" class="block w-full py-2 px-3 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                    <!-- Niveau -->
                    <div>
                        <label for="niveau_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Niveau</label>
                        <select id="niveau_id" wire:model.live="niveau_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Tous les niveaux</option>
                            @foreach($niveaux as $niveau)
                                <option value="{{ $niveau->id }}">{{ $niveau->abr }} - {{ $niveau->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Parcours -->
                    <div>
                        <label for="parcours_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parcours</label>
                        <select id="parcours_id" wire:model.live="parcours_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" {{ count($parcours) ? '' : 'disabled' }}>
                            <option value="">Tous les parcours</option>
                            @foreach($parcours as $parcour)
                                <option value="{{ $parcour->id }}">{{ $parcour->abr }} - {{ $parcour->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Utilisateur -->
                    <div>
                        <label for="saisie_par" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Saisi par</label>
                        <select id="saisie_par" wire:model.live="saisie_par" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Tous les utilisateurs</option>
                            @foreach($utilisateurs as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Recherche -->
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recherche par code</label>
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input wire:model.debounce.300ms="search" type="text" id="search" class="block w-full pl-10 pr-3 py-2 border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Rechercher un code d'anonymat...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des copies supprimées -->
    <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
            <div class="px-4 py-3 sm:px-6">
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                    <h3 class="text-base font-medium leading-6 text-gray-900 dark:text-white">Notes supprimées</h3>

                    <div class="flex items-center space-x-3">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $copies->total() }} résultat(s)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-2 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Code Anonymat
                        </th>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Matière
                        </th>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Note
                        </th>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Utilisateur
                        </th>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Supprimé le
                        </th>
                        <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-300">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700 dark:divide-gray-800">
                    @forelse($copies as $copie)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-2 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model.live="selectedItems" value="{{ $copie->id }}" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                @if($copie->codeAnonymat)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                        {{ $copie->codeAnonymat->code_complet }}
                                    </span>
                                @else
                                    <span class="text-red-500 dark:text-red-400">Code manquant</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $copie->ec->nom ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $copie->examen->session->type ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($copie->note, 2) }}/20</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $copie->utilisateurSaisie->name ?? 'Inconnu' }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $copie->deleted_at ? $copie->deleted_at->format('d/m/Y H:i') : 'N/A' }}
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <button wire:click="restoreCopie({{ $copie->id }})" wire:key="restoreCopie-{{ $copie->id }}" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Restaurer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </button>
                                <button wire:click="confirmDelete({{ $copie->id }})" wire:key="delete-{{ $copie->id }}" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Supprimer définitivement">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-sm text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <p class="mt-1">Aucune note supprimée trouvée.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($copies->hasPages())
        <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6 dark:bg-gray-800 dark:border-gray-700">
            {{ $copies->links() }}
        </div>
        @endif
    </div>

    <!-- Modal de confirmation de suppression définitive -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de suppression définitive</h3>
            <p class="mb-6 text-gray-700 dark:text-gray-300">
                @if($deleteMode === 'single' && $copieToDelete)
                    Êtes-vous sûr de vouloir supprimer définitivement cette note (Code: {{ $copieToDelete->codeAnonymat->code_complet ?? 'N/A' }}, Note: {{ number_format($copieToDelete->note, 2) }}/20) ? Cette action est irréversible.
                @else
                    Êtes-vous sûr de vouloir supprimer définitivement les {{ count($selectedItems) }} notes sélectionnées ? Cette action est irréversible.
                @endif
            </p>
            <div class="flex justify-end space-x-3">
                <button wire:click="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="deleteDefinitely" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800">
                    Supprimer définitivement
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
