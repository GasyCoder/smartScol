<div>
    <div class="container px-4 py-6 mx-auto">
        <!-- En-tête avec titre -->
        <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-medium text-gray-800 dark:text-gray-100">Fusion et vérification des résultats d'examens</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Génération de résultats à partir des manchettes et copies anonymes</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="#" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Voir les Résultats
                    </a>
                </div>
            </div>
        </div>

        @include('livewire.resultats.partials.session-active')

        <!-- Système d'onglets -->
        @if($examen)
        <div class="mb-6 overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <!-- Barre de progression -->
            <div class="w-full bg-gray-200 dark:bg-gray-700">
                <div class="h-2 bg-primary-600 dark:bg-primary-500" style="width: {{ $etapeProgress }}%"></div>
            </div>

            <!-- Navigation des onglets -->
            <div class="flex border-b border-gray-200 dark:border-gray-700" id="tab-navigation">
                <button
                    id="tab-process"
                    class="px-6 py-3 text-sm font-medium border-b-2 {{ $activeTab === 'process' ? 'border-primary-500 text-primary-600 dark:border-primary-400 dark:text-primary-300' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600' }} focus:outline-none"
                    wire:click="switchTab('process')"
                >
                    Processus de fusion
                </button>
                <button
                    id="tab-rapport-stats"
                    class="px-6 py-3 text-sm font-medium border-b-2 {{ $activeTab === 'rapport-stats' ? 'border-primary-500 text-primary-600 dark:border-primary-400 dark:text-primary-300' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600' }} focus:outline-none"
                    wire:click="switchTab('rapport-stats')"
                >
                    Rapport de la fusion
                </button>
            </div>

            <!-- Contenu des onglets -->
            <div class="p-4 sm:p-6">
                @include('livewire.resultats.partials.tab-process')
                @include('livewire.resultats.partials.tab-rapport-stats')
            </div>
        </div>
        @endif
    </div>
</div>
