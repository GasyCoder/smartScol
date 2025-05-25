    <!-- Modale de saisie de manchette -->
    @if($showManchetteModal)
    <div class="fixed inset-0 z-10 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

            <!-- Centrage modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Contenu modal -->
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 dark:bg-gray-800">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                                {{ isset($editingManchetteId) ? 'Modifier une manchette' : 'Saisir une manchette' }}
                            </h3>

                            <!-- Informations contextuelles -->
                            <div class="p-3 mt-3 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-blue-900 dark:text-blue-200">
                                <div class="mb-1 font-semibold">Informations de saisie:</div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div><span class="font-medium">Salle:</span> {{ $currentSalleName }}</div>
                                    <div><span class="font-medium">Code salle:</span> {{ $selectedSalleCode }}</div>
                                    <div><span class="font-medium">Matière:</span> {{ $currentEcName }}</div>
                                    <div><span class="font-medium">Date:</span> {{ $currentEcDate }}</div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <form wire:submit.prevent="saveManchette">
                                    <div class="space-y-4">
                                        <!-- Code anonymat -->
                                        <div>
                                            <label for="code_anonymat" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Code d'anonymat
                                            </label>
                                            <div class="mt-1">
                                                <input type="text"
                                                wire:model="code_anonymat"
                                                id="code_anonymat"
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                placeholder="Ex: {{ $selectedSalleCode }}1"
                                                autofocus>
                                            </div>
                                            @error('code_anonymat') <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                        <!-- Recherche étudiant -->
                                        <div>
                                            <!-- Mode de recherche (matricule/nom) -->
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Rechercher par
                                                </label>
                                            <div class="flex mt-1 space-x-4">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" wire:model.live="searchMode" value="matricule" class="border-gray-300 text-primary-600 focus:ring-primary-500">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Matricule</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" wire:model.live="searchMode" value="nom" class="border-gray-300 text-primary-600 focus:ring-primary-500">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Nom/Prénom</span>
                                                </label>
                                            </div>
                                            <div class="relative mt-1">
                                                <input type="text"
                                                    wire:model.live="searchQuery"
                                                    id="searchQuery"
                                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                    placeholder="{{ $searchMode === 'matricule' ? 'Saisir un matricule' : 'Saisir un nom' }}"
                                                    autocomplete="off">
                                                <!-- Petit indicateur de mode -->
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    Mode actif : {{ $searchMode === 'matricule' ? 'Recherche par matricule' : 'Recherche par nom/prénom' }}
                                                </div>
                                                @if($searchQuery)
                                                <button type="button"
                                                    wire:click="$set('searchQuery', '')"
                                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                                @endif

                                                @error('etudiant_id') <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Résultats de recherche - corrigé -->
                                            @if($searchQuery && strlen($searchQuery) >= 2)
                                                <div class="mt-2 overflow-y-auto bg-white border border-gray-300 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 max-h-48">
                                                    @if(count($searchResults) > 0)
                                                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                                            @foreach($searchResults as $etudiant)
                                                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700" wire:click="selectEtudiant({{ $etudiant->id }})">
                                                                <div class="flex justify-between">
                                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $etudiant->nom }} {{ $etudiant->prenom }}</div>
                                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $etudiant->matricule }}</div>
                                                                </div>
                                                            </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <div class="px-4 py-2 text-sm text-red-700 dark:text-red-600">
                                                            Aucun étudiant trouvé
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <!-- Étudiant sélectionné - section séparée pour plus de clarté -->
                                        @if($etudiant_id && $matricule)
                                        <div class="p-3 mt-3 border border-gray-200 rounded-md bg-green-50 dark:bg-green-900 dark:border-green-800">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="text-sm font-medium text-green-900 dark:text-green-200">
                                                        Étudiant sélectionné:
                                                    </div>
                                                    <div class="text-sm text-green-800 dark:text-green-300">
                                                        {{ App\Models\Etudiant::find($etudiant_id)->nom ?? '' }} {{ App\Models\Etudiant::find($etudiant_id)->prenom ?? '' }}
                                                    </div>
                                                    <div class="text-xs text-green-700 dark:text-green-400">
                                                        Matricule: {{ $matricule }}
                                                    </div>
                                                </div>
                                                <button type="button" wire:click="$set('etudiant_id', null)" class="text-red-600 hover:text-red-800 dark:text-red-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                        @if(!isset($editingManchetteId))
                                        <button type="button" wire:click="$set('showManchetteModal', false)" class="inline-flex items-center justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                            <em class="mr-2 ni ni-cross"></em>
                                            Terminer
                                        </button>
                                        @else
                                        <button type="button" wire:click="$set('showManchetteModal', false)" class="inline-flex items-center justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                            <em class="mr-2 ni ni-cross"></em>
                                            Annuler
                                        </button>
                                        @endif

                                        <button type="submit" class="inline-flex items-center justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm
                                            {{ isset($editingManchetteId)
                                                ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-800'
                                                : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 dark:bg-primary-700 dark:hover:bg-primary-800'
                                            }}
                                            focus:outline-none focus:ring-2 focus:ring-offset-2 sm:col-start-2 sm:text-sm">

                                            @if(isset($editingManchetteId))
                                                <em class="mr-2 ni ni-update"></em>
                                                Mettre à jour
                                            @else
                                                <em class="mr-2 ni ni-save"></em>
                                                Enregistrer
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
    </div>
    @endif
