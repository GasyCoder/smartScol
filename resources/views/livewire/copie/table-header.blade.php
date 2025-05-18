    <!-- En-tête du tableau avec stats et filtres -->
    <div class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
        <div class="px-4 py-3 sm:px-6">
            <!-- Titre et informations de la matière -->
            <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                <div>
                    <h3 class="text-base font-medium leading-6 text-gray-900 dark:text-white">
                        @if($examen_id && $ec_id)
                            Notes pour toutes les matières
                            @if($currentSalleName)
                                <span class="inline-flex items-center mx-1 px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                   <em class="mr-1 text-sm icon ni ni-building"></em>
                                    {{ $currentSalleName }}
                                </span>
                            @endif
                        @else
                            Notes d'examen
                        @endif
                    </h3>

                    <!-- Statistiques rapides -->
                    {{-- @if($ec_id && $ec_id !== 'all')
                        <div class="flex mt-1 space-x-3 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Progression: {{ $totalCopiesCount }}/{{ $totalEtudiantsCount }} ({{ $totalEtudiantsCount > 0 ? round(($totalCopiesCount / $totalEtudiantsCount) * 100) : 0 }}%)
                            </span>
                            <span class="inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ ($totalEtudiantsCount - $totalCopiesCount) }} étudiants sans note
                            </span>
                        </div>
                    @endif --}}
                </div>

                <!-- Outils de recherche et statistiques avec champ de recherche agrandi -->
                <div class="flex items-center ml-4 space-x-2">
                    <!-- Filtre rapide -->
                    <div class="items-center hidden mr-1 space-x-1 sm:flex">
                        <button wire:click="$set('noteFilter', 'all')" class="flex items-center px-2 py-1 text-xs font-medium rounded-md transition-colors {{ isset($noteFilter) && $noteFilter === 'all' ? 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="mr-1 ni ni-view-list"></i> Toutes
                        </button>
                        <button wire:click="$set('noteFilter', 'success')" class="flex items-center px-2 py-1 text-xs font-medium rounded-md transition-colors {{ isset($noteFilter) && $noteFilter === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="mr-1 ni ni-check-circle"></i> Réussies
                        </button>
                        <button wire:click="$set('noteFilter', 'failed')" class="flex items-center px-2 py-1 text-xs font-medium rounded-md transition-colors {{ isset($noteFilter) && $noteFilter === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="mr-1 ni ni-cross-circle"></i> Échouées
                        </button>
                    </div>

                    <!-- Barre de recherche AGRANDIE -->
                    <div class="relative flex-1 min-w-[200px] sm:min-w-[250px] md:min-w-[300px]">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <em class="text-base text-gray-400 icon ni ni-search"></em>
                        </div>
                        <input
                            wire:model.live="search"
                            type="text"
                            class="block w-full py-1.5 pl-10 pr-3 text-sm leading-5 placeholder-gray-500 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                            placeholder="Rechercher par code ou note...">
                    </div>

                    <!-- Compteur de copies avec animations -->
                    {{-- <div class="hidden gap-2 text-xs font-medium text-gray-500 sm:flex dark:text-gray-400 whitespace-nowrap">
                        <span class="px-2 py-1 transition-all duration-300 ease-in-out rounded-md bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 hover:shadow-sm">
                            {{ $copies->total() ?? 0 }} total
                        </span>
                        <span class="px-2 py-1 transition-all duration-300 ease-in-out rounded-md bg-secondary-100 text-secondary-800 dark:bg-secondary-900 dark:text-secondary-200 hover:shadow-sm">
                            {{ $userCopiesCount ?? 0 }} par vous
                        </span>
                    </div> --}}

                    <!-- Menu d'actions rapides -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="p-1.5 text-gray-500 bg-white rounded-md hover:bg-gray-100 focus:outline-none dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                           <em class="icon ni ni-more-v-alt"></em>
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute right-0 z-10 w-48 mt-2 bg-white rounded-md shadow-lg dark:bg-gray-800 ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <button wire:click="exportNotes" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                    <em class="mr-1 icon ni ni-file-xls"></em>
                                    Exporter (Excel)
                                </button>
                                <button wire:click="printNotes" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                    <em class="mr-1 icon ni ni-printer"></em>
                                    Imprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
