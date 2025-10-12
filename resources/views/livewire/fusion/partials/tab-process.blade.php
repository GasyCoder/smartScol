<div id="content-process" 
     class="tab-content" 
     style="{{ $activeTab !== 'process' ? 'display: none;' : '' }}">

    {{-- En-tête de progression globale --}}
    <div class="mb-6 overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Progression du traitement
                </h3>
                <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full {{ $statut === 'publie' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($statut === 'annule' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400') }}">
                    {{ $statut === 'initial' ? 'Non démarré' : ($statut === 'verification' ? 'Vérification' : ($statut === 'fusion' ? 'Fusion en cours' : ($statut === 'valide' ? 'Validé' : ($statut === 'publie' ? 'Publié' : 'Annulé')))) }}
                </span>
            </div>
            
            {{-- Barre de progression --}}
            <div class="relative">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold inline-block text-blue-600 dark:text-blue-400">
                            Étape {{ $statut === 'initial' ? '0' : ($statut === 'verification' ? '1' : ($statut === 'fusion' ? '2' : ($statut === 'valide' ? '3' : '4'))) }}/4
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold inline-block text-blue-600 dark:text-blue-400">
                            {{ $etapeProgress }}%
                        </span>
                    </div>
                </div>
                <div class="overflow-hidden h-2 text-xs flex rounded-full bg-gray-200 dark:bg-gray-700">
                    <div style="width: {{ $etapeProgress }}%" 
                         class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500 ease-out dark:from-blue-600 dark:to-blue-700">
                    </div>
                </div>
            </div>

            {{-- Info session --}}
            @if($sessionActive)
                <div class="flex items-center gap-2 mt-3 text-xs text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-medium">Session : {{ $sessionActive->type }}</span>
                    <span class="px-2 py-0.5 text-xs font-medium rounded {{ $sessionActive->type === 'Rattrapage' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}">
                        {{ $sessionActive->anneeUniversitaire->nom ?? '' }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- Grille des étapes --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        
        {{-- ========================================
            ÉTAPE 1 : VÉRIFICATION DE COHÉRENCE
        ======================================== --}}
        <div class="group transition-all duration-300 hover:shadow-lg">
            <div class="relative overflow-hidden border rounded-lg {{ $statut === 'verification' && !$showFusionButton ? 'bg-blue-50 border-blue-300 dark:bg-blue-900/20 dark:border-blue-700' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
                {{-- Badge étape --}}
                <div class="absolute top-0 right-0 px-3 py-1 text-xs font-bold text-white bg-gradient-to-br from-blue-500 to-blue-600 rounded-bl-lg">
                    ÉTAPE 1
                </div>

                <div class="p-5">
                    <div class="flex items-start gap-4">
                        {{-- Icône --}}
                        <div class="flex-shrink-0">
                            @if($statut === 'initial' || ($statut === 'verification' && !$showFusionButton))
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl group-hover:scale-110 shadow-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gradient-to-br from-green-500 to-green-600 rounded-xl group-hover:scale-110 shadow-lg">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                Vérification de cohérence
                            </h4>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    Analysez les données de rattrapage avant la fusion
                                @else
                                    Vérifiez la cohérence entre manchettes et copies
                                @endif
                            </p>

                            {{-- Statistiques session rattrapage --}}
                            @if($sessionActive && $sessionActive->type === 'Rattrapage' && $examen)
                                @php
                                    $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
                                    $compteursDonnees = $this->getCompteursDonneesSession();
                                @endphp

                                <div class="mt-3 p-3 bg-orange-50 border border-orange-200 rounded-lg dark:bg-orange-900/20 dark:border-orange-800">
                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div>
                                            <div class="text-xs font-medium text-orange-600 dark:text-orange-400">Éligibles</div>
                                            <div class="text-lg font-bold text-orange-700 dark:text-orange-300">{{ $etudiantsEligibles->count() }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-medium text-orange-600 dark:text-orange-400">Manchettes</div>
                                            <div class="text-lg font-bold text-orange-700 dark:text-orange-300">{{ $compteursDonnees['manchettes'] }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-medium text-orange-600 dark:text-orange-400">Copies</div>
                                            <div class="text-lg font-bold text-orange-700 dark:text-orange-300">{{ $compteursDonnees['copies'] }}</div>
                                        </div>
                                    </div>

                                    @if($etudiantsEligibles->count() == 0 && $compteursDonnees['manchettes'] > 0)
                                        <div class="mt-2 p-2 text-xs font-medium text-orange-800 bg-orange-100 border border-orange-300 rounded-md dark:bg-orange-900/30 dark:text-orange-400">
                                            <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            Manchettes sans étudiants éligibles
                                        </div>
                                    @elseif($compteursDonnees['manchettes'] == 0 && $etudiantsEligibles->count() > 0)
                                        <div class="mt-2 p-2 text-xs font-medium text-orange-800 bg-orange-100 border border-orange-300 rounded-md dark:bg-orange-900/30 dark:text-orange-400">
                                            <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $etudiantsEligibles->count() }} éligible(s) mais aucune donnée
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="mt-4">
                                @if($statut === 'initial' || ($statut === 'verification' && !$showFusionButton))
                                    <button wire:click="confirmVerification"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Vérifier rattrapage' : 'Vérifier cohérence' }}
                                        <span wire:loading wire:target="confirmVerification" class="ml-2">
                                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                @else
                                    <div class="inline-flex items-center px-4 py-2 text-sm font-medium text-green-700 bg-green-100 border border-green-200 rounded-lg dark:bg-green-900/30 dark:text-green-400 dark:border-green-800">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Vérification terminée
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================
            ÉTAPE 2 : FUSION DES DONNÉES
        ======================================== --}}
        <div class="group transition-all duration-300 hover:shadow-lg">
            <div class="relative overflow-hidden border rounded-lg {{ $statut === 'fusion' ? 'bg-yellow-50 border-yellow-300 dark:bg-yellow-900/20 dark:border-yellow-700' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
                {{-- Badge étape --}}
                <div class="absolute top-0 right-0 px-3 py-1 text-xs font-bold text-white bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-bl-lg">
                    ÉTAPE 2
                </div>

                <div class="p-5">
                    <div class="flex items-start gap-4">
                        {{-- Icône --}}
                        <div class="flex-shrink-0">
                            @if($statut === 'initial' || ($statut === 'verification' && !$showFusionButton))
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gray-400 rounded-xl group-hover:scale-110 shadow-lg dark:bg-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                </div>
                            @elseif($statut === 'verification' && $showFusionButton)
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl group-hover:scale-110 shadow-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                </div>
                            @elseif($statut === 'fusion')
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl group-hover:scale-110 shadow-lg animate-pulse">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gradient-to-br from-green-500 to-green-600 rounded-xl group-hover:scale-110 shadow-lg">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                Fusion des données
                            </h4>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    Associe les données de rattrapage aux étudiants éligibles
                                @else
                                    Associe les manchettes aux copies pour générer les résultats
                                @endif
                            </p>

                            {{-- Statut fusion --}}
                            @if($statut === 'fusion')
                                <div class="mt-3 p-3 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg dark:from-yellow-900/20 dark:to-orange-900/20 dark:border-yellow-800">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">
                                                @if($etapeFusion === 1)
                                                    Fusion initiale terminée
                                                @elseif($etapeFusion === 2)
                                                    Seconde fusion terminée
                                                @elseif($etapeFusion === 3)
                                                    Fusion finale terminée
                                                @else
                                                    En attente de démarrage
                                                @endif
                                            </p>
                                            <p class="mt-1 text-xs text-yellow-700 dark:text-yellow-400">
                                                @if($etapeFusion >= 1 && $etapeFusion <= 3)
                                                    Vérification n°{{ $etapeFusion }} requise
                                                @else
                                                    Cliquez sur "Commencer fusion" pour débuter
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            


                            {{-- Indicateur de traitement --}}
                            @if($isProcessing)
                                <div class="flex items-center gap-2 mt-3 px-3 py-2 text-sm font-medium text-blue-700 bg-blue-100 border border-blue-200 rounded-lg dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800">
                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Traitement rattrapage en cours...' : 'Traitement en cours...' }}</span>
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="flex flex-wrap gap-2 mt-4">
                                @if($statut === 'verification' && $showFusionButton)
                                    <button wire:click="$set('confirmingFusion', true)"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg hover:from-yellow-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Fusion rattrapage' : 'Commencer fusion' }}
                                        <span wire:loading wire:target="$set('confirmingFusion', true)" class="ml-2">
                                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                @elseif($statut === 'fusion')
                                    @if($etapeFusion === 1)
                                        <button wire:click="confirmVerify2"
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg hover:from-yellow-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                            Étape 2
                                            <span wire:loading wire:target="confirmVerify2" class="ml-2">
                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </span>
                                        </button>
                                    @elseif($etapeFusion === 2)
                                        <button wire:click="confirmVerify3"
                                                wire:loading.attr="disabled"
                                                wire:key="verify3-{{ now()->timestamp }}"
                                                class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Terminer fusion
                                            <span wire:loading wire:target="confirmVerify3" class="ml-2">
                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </span>
                                        </button>
                                    @endif
                                    
                                    @if($showResetButton)
                                        @can('resultats.reset-fusion')
                                            <button wire:click="confirmResetFusion"
                                                    wire:loading.attr="disabled"
                                                    class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-red-700 transition-all duration-200 bg-red-100 border border-red-300 rounded-lg hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-red-900/30 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-900/50">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Réinitialiser
                                                <span wire:loading wire:target="confirmResetFusion" class="ml-2">
                                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </span>
                                            </button>
                                        @endcan
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================
            ÉTAPE 3 : VALIDATION
        ======================================== --}}
        <div class="group transition-all duration-300 hover:shadow-lg">
            <div class="relative overflow-hidden border rounded-lg {{ $statut === 'valide' ? 'bg-purple-50 border-purple-300 dark:bg-purple-900/20 dark:border-purple-700' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
                {{-- Badge étape --}}
                <div class="absolute top-0 right-0 px-3 py-1 text-xs font-bold text-white bg-gradient-to-br from-purple-500 to-purple-600 rounded-bl-lg">
                    ÉTAPE 3
                </div>

                <div class="p-5">
                    <div class="flex items-start gap-4">
                        {{-- Icône --}}
                        <div class="flex-shrink-0">
                            @if($statut === 'publie' || $statut === 'annule')
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gradient-to-br from-green-500 to-green-600 rounded-xl group-hover:scale-110 shadow-lg">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @elseif($statut === 'valide')
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl group-hover:scale-110 shadow-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @elseif($statut === 'fusion' && $etapeFusion >= 3)
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl group-hover:scale-110 shadow-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gray-400 rounded-xl group-hover:scale-110 shadow-lg dark:bg-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                Validation des résultats
                            </h4>
                            
                            {{-- Messages de statut --}}
                            @if($statut === 'valide')
                                <div class="flex items-center gap-2 mt-2 px-3 py-2 text-sm font-semibold text-purple-700 bg-purple-100 border border-purple-200 rounded-lg dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Validé - Prêt pour publication
                                </div>
                            @elseif($statut === 'publie')
                                <div class="flex items-center gap-2 mt-2 px-3 py-2 text-sm font-semibold text-green-700 bg-green-100 border border-green-200 rounded-lg dark:bg-green-900/30 dark:text-green-400 dark:border-green-800">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Résultats publiés avec succès
                                </div>
                            @elseif($statut === 'annule')
                                <div class="flex items-center gap-2 mt-2 px-3 py-2 text-sm font-semibold text-red-700 bg-red-100 border border-red-200 rounded-lg dark:bg-red-900/30 dark:text-red-400 dark:border-red-800">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Résultats annulés
                                </div>
                            @elseif($statut === 'fusion' && $etapeFusion >= 3)
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Les résultats de rattrapage sont prêts pour validation
                                    @else
                                        Les résultats sont prêts pour validation
                                    @endif
                                </p>
                            @else
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Validation disponible après la 3ème vérification de fusion
                                </p>
                            @endif

                            {{-- Actions par rôle --}}
                            <div class="mt-4 space-y-2">
                                {{-- SUPERADMIN --}}
                                @if(auth()->user()->hasRole('superadmin'))
                                    @if($statut === 'fusion' && $etapeFusion >= 3)
                                        <button wire:click="confirmValidation"
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Valider les résultats
                                            <span wire:loading wire:target="confirmValidation" class="ml-2">
                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </span>
                                        </button>
                                    @endif
                                @endif

                                {{-- ENSEIGNANT --}}
                                @if(auth()->user()->hasRole('enseignant'))
                                    @if($statut === 'fusion' && $etapeFusion >= 1 && $etapeFusion <= 2)
                                        <a href="{{ route('resultats.verification', ['examenId' => $examen_id]) }}"
                                           class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Vérification n° {{ $etapeFusion }}
                                        </a>
                                    @elseif($statut === 'fusion' && $etapeFusion >= 3)
                                        <div class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 border border-blue-200 rounded-lg dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800">
                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                            En attente de validation informaticien
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================
            ÉTAPE 4 : PUBLICATION
        ======================================== --}}
        @if(auth()->user()->hasAnyRole(['superadmin']))
        <div class="group transition-all duration-300 hover:shadow-lg">
            <div class="relative overflow-hidden border rounded-lg {{ $statut === 'publie' ? 'bg-green-50 border-green-300 dark:bg-green-900/20 dark:border-green-700' : ($statut === 'annule' ? 'bg-red-50 border-red-300 dark:bg-red-900/20 dark:border-red-700' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700') }}">
                {{-- Badge étape --}}
                <div class="absolute top-0 right-0 px-3 py-1 text-xs font-bold text-white bg-gradient-to-br {{ $statut === 'publie' ? 'from-green-500 to-green-600' : ($statut === 'annule' ? 'from-red-500 to-red-600' : 'from-indigo-500 to-indigo-600') }} rounded-bl-lg">
                    ÉTAPE 4
                </div>

                <div class="p-5">
                    <div class="flex items-start gap-4">
                        {{-- Icône --}}
                        <div class="flex-shrink-0">
                            @if($statut === 'initial' || $statut === 'verification' || $statut === 'fusion')
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gray-400 rounded-xl group-hover:scale-110 shadow-lg dark:bg-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </div>
                            @elseif($statut === 'valide')
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl group-hover:scale-110 shadow-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </div>
                            @elseif($statut === 'annule')
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gradient-to-br from-red-500 to-red-600 rounded-xl group-hover:scale-110 shadow-lg">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="flex items-center justify-center w-12 h-12 text-white transition-transform duration-300 bg-gradient-to-br from-green-500 to-green-600 rounded-xl group-hover:scale-110 shadow-lg">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                Publication des résultats
                            </h4>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    Publiez les résultats de rattrapage pour les étudiants
                                @else
                                    Publiez les résultats pour les rendre accessibles
                                @endif
                            </p>

                            {{-- Actions selon le statut --}}
                            <div class="flex flex-wrap gap-3 mt-4">
                                @if($statut === 'valide')
                                    <button wire:click="confirmPublication"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Publier les résultats
                                        <span wire:loading wire:target="confirmPublication" class="ml-2">
                                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                @endif

                                @if($statut === 'publie')
                                    <a href="{{ $estPACES ? route('resultats.paces-concours') : route('resultats.finale') }}"
                                       class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Voir résultats {{ $estPACES ? 'PACES' : '' }}
                                    </a>
                                    
                                    <button wire:click="$set('confirmingAnnulation', true)"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-red-700 transition-all duration-200 bg-red-100 border border-red-300 rounded-lg hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-red-900/30 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-900/50">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Annuler publication
                                    </button>
                                @endif

                                @if($statut === 'annule')
                                    <button wire:click="$set('confirmingRevenirValidation', true)"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Réactiver les résultats
                                        <span wire:loading wire:target="$set('confirmingRevenirValidation', true)" class="ml-2">
                                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@include('livewire.fusion.partials.modals')