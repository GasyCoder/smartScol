    <!-- En-tête du tableau avec stats et filtres -->
    <div class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
        <div class="px-4 py-3 sm:px-6">
            <!-- Titre et informations de la matière -->
            <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                <div>
                    <h3 class="text-base font-medium leading-6 text-gray-900 dark:text-white">
                        @if($examen_id && $ec_id)
                            Manchettes pour toutes les matières
                            @if($currentSalleName)
                                <span class="inline-flex items-center mx-1 px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    <em class="mr-1 text-sm icon ni ni-building"></em>
                                    {{ $currentSalleName }}
                                </span>
                            @endif
                        @else
                            Manchettes d'examen
                        @endif
                    </h3>

                    <!-- Statistiques rapides -->
                    {{-- @if($ec_id && $ec_id !== 'all')
                        <div class="flex mt-1 space-x-3 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Progrès: {{ $manchettes->total() ?? 0 }}/{{ $totalEtudiantsCount }}
                                (<span class="font-semibold">{{ $totalEtudiantsCount > 0 ? round(($manchettes->total() / $totalEtudiantsCount) * 100) : 0 }}%</span>)
                            </span>
                            <span class="inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ $totalEtudiantsCount - ($manchettes->total() ?? 0) }} étudiants sans manchette
                            </span>
                        </div>
                    @endif --}}
                </div>

                <!-- Outils de recherche et statistiques -->
                <div class="flex items-center flex-1 max-w-md ml-auto space-x-2">
                    <!-- Barre de recherche améliorée -->
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            wire:model.live="search"
                            type="text"
                            class="block w-full py-1.5 pl-10 pr-3 text-sm leading-5 placeholder-gray-500 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                            placeholder="Rechercher code ou matricule...">
                    </div>

                    <!-- Compteurs avec animations -->
                    {{-- <div class="flex gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        <span class="px-2 py-1 transition-all duration-300 ease-in-out rounded-md bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 hover:shadow-sm">
                            {{ $manchettes->total() ?? 0 }} total
                        </span>
                        <span class="px-2 py-1 transition-all duration-300 ease-in-out rounded-md bg-secondary-100 text-secondary-800 dark:bg-secondary-900 dark:text-secondary-200 hover:shadow-sm">
                            {{ $userManchettesCount ?? 0 }} par vous
                        </span>
                    </div> --}}

                    <!-- Menu d'actions rapides -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="p-1.5 text-gray-500 bg-white rounded-md hover:bg-gray-100 focus:outline-none dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <em class="icon ni ni-more-v-alt"></em>
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute right-0 z-10 w-48 mt-2 bg-white rounded-md shadow-lg dark:bg-gray-800 ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <button wire:click="exportManchettes" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                   <em class="mr-1 icon ni ni-file-xls"></em>
                                    Exporter (Excel)
                                </button>
                                <button wire:click="printManchettes" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
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
