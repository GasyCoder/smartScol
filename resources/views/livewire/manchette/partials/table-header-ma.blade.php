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
