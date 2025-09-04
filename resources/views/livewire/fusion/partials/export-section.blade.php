{{-- Section d'export des résultats --}}
<div class="p-6 mt-6 border border-gray-200 rounded-lg {{ $this->getExportSectionClasses() }}">
    <div class="flex items-start space-x-4">
        {{-- Icône indicatrice --}}
        <div class="flex-shrink-0">
            <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $this->getExportIconClasses() }}">
                <svg class="text-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
            </div>
        </div>

        {{-- Contenu principal --}}
        <div class="flex-1 min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                {{-- Section de titre et description --}}
                <div class="mb-4 sm:mb-0">
                    <h4 class="text-lg font-semibold {{ $this->getExportTitleClasses() }}">
                        {{ $sessionActive && $sessionActive->type === 'Rattrapage' 
                            ? 'Export des résultats de rattrapage' 
                            : 'Export des résultats' }}
                    </h4>
                    <p class="mt-1 text-sm {{ $this->getExportDescriptionClasses() }}">
                        {{ $sessionActive && $sessionActive->type === 'Rattrapage' 
                            ? 'Résultats de rattrapage publiés avec succès. Les meilleures notes ont été appliquées automatiquement.' 
                            : 'Résultats publiés avec succès. Téléchargez les données dans le format de votre choix.' }}
                    </p>
                </div>

                {{-- Section des boutons d'action --}}
                <div class="flex flex-col gap-3 sm:flex-row sm:ml-6">
                    {{-- Bouton Export Excel --}}
                    <button
                        wire:click="exporterExcel"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 bg-green-600 border border-transparent rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-green-700 dark:hover:bg-green-600 min-w-[140px]">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Export Excel</span>
                        <span wire:loading wire:target="exporterExcel" class="ml-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </span>
                    </button>

                    {{-- Bouton Export PDF --}}
                    <button
                        wire:click="exporterPDF"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 bg-red-600 border border-transparent rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-red-700 dark:hover:bg-red-600 min-w-[140px]">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <span>Export PDF</span>
                        <span wire:loading wire:target="exporterPDF" class="ml-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>