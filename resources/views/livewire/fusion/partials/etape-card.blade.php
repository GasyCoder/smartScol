{{-- Carte d'étape réutilisable --}}
<div class="p-5 border rounded-lg {{ $this->getEtapeCardClasses($numero) }}">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            {!! $this->getEtapeIcon($numero) !!}
        </div>
        
        <div class="ml-4 {{ $numero === 2 ? 'space-y-4' : '' }}">
            <div>
                <h4 class="text-base font-medium text-gray-800 dark:text-gray-200">
                    {{ $titre }}
                </h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    {!! $description !!}
                </p>
            </div>

            {{-- Informations spéciales selon le type d'étape --}}
            @if($numero === 1 && $sessionActive && $sessionActive->type === 'Rattrapage' && $examen)
                {!! $this->getRattrapageInfo() !!}
            @endif

            @if(isset($showProgressInfo) && $showProgressInfo && $statut === 'fusion')
                {!! $this->getFusionProgressInfo() !!}
            @endif

            @if(isset($showVerificationInfo) && $showVerificationInfo && $statut === 'fusion')
                {!! $this->getVerificationInfo() !!}
            @endif

            {{-- Zone de traitement --}}
            @if($isProcessing && in_array($numero, [1, 2, 3, 4]))
                <div class="flex items-center mt-3 space-x-2 text-sm text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4 text-yellow-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>
                        {{ $sessionActive && $sessionActive->type === 'Rattrapage' 
                            ? 'Traitement des données de rattrapage en cours...' 
                            : 'Traitement en cours, veuillez patienter...' }}
                    </span>
                </div>
            @endif

            {{-- Actions de l'étape --}}
            @if(!empty($actions))
                <div class="mt-4">
                    <div class="flex flex-col space-y-3 sm:flex-row sm:space-x-3 sm:space-y-0">
                        {!! $actions !!}
                    </div>
                </div>
            @endif

            {{-- Section spéciale pour l'étape de vérification --}}
            @if(isset($showVerificationInfo) && $showVerificationInfo && $statut === 'fusion' && $etapeFusion >= 3)
                <div class="pt-4 mt-6 border-t border-gray-200 dark:border-gray-600">
                    <h5 class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                        {{ $sessionActive && $sessionActive->type === 'Rattrapage' 
                            ? 'Validation finale des résultats de rattrapage' 
                            : 'Validation finale' }}
                    </h5>
                    <p class="mb-3 text-xs text-gray-600 dark:text-gray-400">
                        {{ $sessionActive && $sessionActive->type === 'Rattrapage' 
                            ? 'Une fois toutes les vérifications effectuées, vous pouvez valider définitivement les résultats de rattrapage.'
                            : 'Une fois toutes les vérifications effectuées, vous pouvez valider définitivement les résultats.' }}
                    </p>
                    <button
                        wire:click="confirmValidation"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-200 bg-green-600 border border-transparent rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700 dark:hover:bg-green-600 disabled:opacity-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ $sessionActive && $sessionActive->type === 'Rattrapage' 
                            ? 'Valider définitivement les résultats de rattrapage' 
                            : 'Valider définitivement les résultats' }}
                        <span wire:loading wire:target="confirmValidation" class="ml-2 animate-spin">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            @endif

            {{-- Section spéciale pour l'étape de publication --}}
            @if(isset($showPublicationInfo) && $showPublicationInfo)
                {!! $this->getPublicationSection() !!}
            @endif
        </div>
    </div>
</div>