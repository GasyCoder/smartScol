<div>
    <!-- En-tête avec titre et actions -->
    <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800 rounded-lg">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <!-- Titre avec badge session -->
            <div class="flex flex-wrap items-center gap-3 min-w-0">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Liste des Copies
                </h1>
                
                @if($sessionActive)
                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full whitespace-nowrap
                        {{ $sessionActive->type === 'Normale'
                            ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200'
                            : 'bg-orange-100 text-orange-800 border border-orange-200 dark:bg-orange-900 dark:text-orange-200'
                        }}">
                        {{ $sessionInfo }}
                    </span>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-3">
                <button wire:click="resetFilters" 
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    <em class="mr-2 ni ni-reload"></em>
                    Réinitialiser
                </button>
                <a href="{{ route('copies.corbeille') }}" 
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                    <em class="mr-2 ni ni-trash-alt"></em>
                    Corbeille
                </a>
            </div>
        </div>
    </div>

    <!-- Filtres compacts -->
    <div class="grid grid-cols-1 gap-4 p-4 mb-6 bg-white rounded-lg shadow-sm md:grid-cols-2 lg:grid-cols-5 dark:bg-gray-800">
        <!-- Niveau -->
        <div>
            <label for="niveau" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Niveau</label>
            <select wire:model.live="niveau_id" id="niveau" 
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Tous les niveaux</option>
                @foreach($niveaux as $niveau)
                    <option value="{{ $niveau->id }}">{{ $niveau->nom }}</option>
                @endforeach
            </select>
        </div>

        <!-- Parcours -->
        <div>
            <label for="parcours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parcours</label>
            <select wire:model.live="parcours_id" id="parcours" 
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    {{ !$niveau_id ? 'disabled' : '' }}>
                <option value="">Tous les parcours</option>
                @foreach($parcours as $parcour)
                    <option value="{{ $parcour->id }}">{{ $parcour->nom }}</option>
                @endforeach
            </select>
        </div>

        <!-- ECS -->
        <div>
            <label for="matiere" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ECS</label>
            <select wire:model.live="ec_id" id="matiere" 
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    {{ !$parcours_id ? 'disabled' : '' }}>
                <option value="">Toutes les ECS</option>
                @foreach($ecs as $ec)
                    <option value="{{ $ec->id }}">{{ $ec->nom }}</option>
                @endforeach
            </select>
        </div>

        <!-- Recherche -->
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recherche</label>
            <div class="relative">
                <input wire:model.live.debounce.300ms="search" type="search" id="search" 
                       placeholder="Code, étudiant..." 
                       class="w-full px-3 py-2 pl-10 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                <svg class="absolute w-4 h-4 text-gray-400 transform -translate-y-1/2 left-3 top-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Secrétaire -->
        <div>
            <label for="secretaire" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Secrétaire</label>
            <select wire:model.live="secretaire_id" id="secretaire" 
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Tous les secrétaires</option>
                @foreach($secretaires as $secretaire)
                    <option value="{{ $secretaire->id }}">{{ $secretaire->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Tableau responsive -->
    <div class="overflow-hidden bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" 
                            wire:click="sortBy('code_anonymat')">
                            <div class="flex items-center">
                                Code Anonymat
                                @if($sortField === 'code_anonymat')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <div class="flex items-center">
                                Étudiant
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            ECS
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" 
                            wire:click="sortBy('note')">
                            <div class="flex items-center">
                                Note
                                @if($sortField === 'note')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" 
                            wire:click="sortBy('secretaire')">
                            <div class="flex items-center">
                                Saisi par
                                @if($sortField === 'secretaire')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" 
                            wire:click="sortBy('created_at')">
                            <div class="flex items-center">
                                Date
                                @if($sortField === 'created_at')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($copies as $copie)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                {{ $copie->codeAnonymat->code_complet ?? 'N/A' }}
                            </span>
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center mr-3 dark:bg-gray-600">
                                    <em class="ni ni-user text-gray-500 dark:text-gray-300"></em>
                                </div>
                                <div>
                                    @if($copie->etudiant)
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $copie->etudiant->nom ?? '' }} {{ $copie->etudiant->prenom ?? '' }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                       {{ $copie->etudiant->matricule ?? '' }}
                                    </div>
                                    @else
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Étudiant non identifié
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $copie->ec->nom ?? '' }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $copie->ec->ue->nom ?? '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($copie->note !== null)
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    {{ $copie->note >= 10 
                                        ? 'text-green-800 bg-green-100 dark:bg-green-900 dark:text-green-200' 
                                        : 'text-red-800 bg-red-100 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ number_format($copie->note, 2) }}/20
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-400">
                                    Non notée
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-6 w-6 rounded-full bg-green-100 flex items-center justify-center mr-2 dark:bg-green-900">
                                    <em class="ni ni-user-check text-green-600 text-xs dark:text-green-300"></em>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $copie->utilisateurSaisie->name ?? 'Inconnu' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Secrétaire
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $copie->created_at->format('d/m/Y H:i') }}
                            </div>
                            @if($copie->updated_at->ne($copie->created_at))
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Modifié le {{ $copie->updated_at->format('d/m/Y H:i') }}
                                </div>
                                @if($copie->utilisateurModification)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Par: {{ $copie->utilisateurModification->name }}
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                @can('copies.edit')
                                <button wire:click="editCopy({{ $copie->id }})" 
                                        class="p-1 text-indigo-600 rounded hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                        aria-label="Modifier la copie">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                @endcan 
                                @can('copies.delete')
                                <button wire:click="confirmDelete({{ $copie->id }})" 
                                        class="p-1 text-red-600 rounded hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                        aria-label="Supprimer la copie">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                @endcan 
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">Aucune copie trouvée</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if($copies->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $copies->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Édition -->
    @if($showEditModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-50" wire:click="closeEditModal"></div>
            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                            Modifier la copie
                        </h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="edit_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Code Anonymat (lecture seule)
                                </label>
                                <input wire:model="edit_code_copie" type="text" id="edit_code" readonly
                                       class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-600 dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300">
                            </div>
                            <div>
                                <label for="edit_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Note (/20)
                                </label>
                                <input wire:model="edit_note" type="number" step="0.01" min="0" max="20" id="edit_note"
                                       class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('edit_note') 
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
               <div class="flex justify-end space-x-3 mt-6">
                    <button wire:click="updateCopy" type="button"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                        Sauvegarder
                    </button>
                    <button wire:click="closeEditModal" type="button"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Suppression -->
    @if($showDeleteModal)
   <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-50" wire:click="closeDeleteModal"></div>
            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                            Confirmer la suppression
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Êtes-vous sûr de vouloir supprimer cette copie ? Cette action ne peut pas être annulée.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button wire:click="deleteCopy" type="button"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm">
                        Supprimer
                    </button>
                    <button wire:click="closeDeleteModal" type="button"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>