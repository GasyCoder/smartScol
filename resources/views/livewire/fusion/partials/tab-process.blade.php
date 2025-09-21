<div id="content-process" 
     class="tab-content" 
     style="{{ $activeTab !== 'process' ? 'display: none;' : '' }}">

    {{-- Processus principal avec les étapes détaillées (seulement dans cet onglet) --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        
        {{-- 1. Vérification de cohérence --}}
        <div class="p-4 border rounded-lg {{ $statut === 'verification' && !$showFusionButton ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/10 dark:border-blue-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($statut === 'initial' || ($statut === 'verification' && !$showFusionButton))
                        <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-blue-500 dark:bg-blue-600">
                            <span class="text-sm font-medium">1</span>
                        </div>
                    @else
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                
                <div class="ml-4 flex-1">
                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                        Vérification de cohérence
                    </h4>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Analysez les données de rattrapage avant la fusion.
                        @else
                            Vérifiez la cohérence entre manchettes et copies.
                        @endif
                    </p>

                    @if($sessionActive && $sessionActive->type === 'Rattrapage' && $examen)
                        @php
                            $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
                            $compteursDonnees = $this->getCompteursDonneesSession();
                        @endphp

                        <div class="mt-2 p-2 bg-orange-50 rounded text-xs text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">
                            <div class="flex items-center space-x-3">
                                <span>Éligibles: {{ $etudiantsEligibles->count() }}</span>
                                <span>Manchettes: {{ $compteursDonnees['manchettes'] }}</span>
                                <span>Copies: {{ $compteursDonnees['copies'] }}</span>
                            </div>

                            @if($etudiantsEligibles->count() == 0 && $compteursDonnees['manchettes'] > 0)
                                <div class="mt-1 p-1 text-orange-800 bg-orange-100 border border-orange-300 rounded">
                                    ⚠️ Manchettes sans étudiants éligibles
                                </div>
                            @elseif($compteursDonnees['manchettes'] == 0 && $etudiantsEligibles->count() > 0)
                                <div class="mt-1 p-1 text-orange-800 bg-orange-100 border border-orange-300 rounded">
                                    ⚠️ {{ $etudiantsEligibles->count() }} éligible(s) mais aucune donnée
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="mt-3">
                        @if($statut === 'initial' || ($statut === 'verification' && !$showFusionButton))
                            <button wire:click="confirmVerification"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Vérifier rattrapage' : 'Vérifier cohérence' }}
                                <span wire:loading wire:target="confirmVerification" class="ml-2 animate-spin">⟳</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Fusion des données --}}
        <div class="p-4 border rounded-lg {{ $statut === 'fusion' ? 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/10 dark:border-yellow-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($statut === 'initial' || ($statut === 'verification' && !$showFusionButton))
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                            <span class="text-sm font-medium">2</span>
                        </div>
                    @elseif($statut === 'verification' && $showFusionButton)
                        <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-blue-500 dark:bg-blue-600">
                            <span class="text-sm font-medium">2</span>
                        </div>
                    @elseif($statut === 'fusion')
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-yellow-500 rounded-full dark:bg-yellow-600">
                            <span class="text-sm font-medium">2</span>
                        </div>
                    @else
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                
                <div class="ml-4 flex-1">
                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-100">
                        Fusion des données
                    </h4>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Associe les données de rattrapage aux étudiants éligibles.
                        @else
                            Associe les manchettes aux copies pour générer les résultats.
                        @endif
                    </p>

                    @if($statut === 'fusion')
                        <div class="px-3 py-2 mt-2 text-sm bg-gray-100 rounded-md dark:bg-gray-700">
                            <div class="font-medium text-gray-700 dark:text-gray-300">
                                @if($etapeFusion === 1)
                                    ✅ Fusion initiale terminée → Vérification requise
                                @elseif($etapeFusion === 2)
                                    ✅ Seconde fusion terminée → Vérification requise
                                @elseif($etapeFusion === 3)
                                    ✅ Fusion finale terminée → Prêt pour validation
                                @else
                                    ⏳ En attente de démarrage de la fusion
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    @if($showProgress)
                    <div class="mt-4 p-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/10 dark:border-blue-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">{{ $fusionStep }}</span>
                            <span class="text-sm text-blue-600 dark:text-blue-400">{{ $fusionProgress }}%</span>
                        </div>
                        <div class="w-full bg-blue-200 rounded-full h-2 dark:bg-blue-800">
                            <div class="bg-blue-600 h-2 rounded-full dark:bg-blue-400 transition-all duration-300" 
                                style="width: {{ $fusionProgress }}%"></div>
                        </div>
                    </div>
                    @endif

                    @if($isProcessing)
                        <div class="flex items-center mt-3 space-x-2 text-sm text-gray-600 dark:text-gray-400">
                            <span class="animate-spin">⟳</span>
                            <span>{{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Traitement rattrapage en cours...' : 'Traitement en cours...' }}</span>
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-2 mt-4">
                        @if($statut === 'verification' && $showFusionButton)
                            <button wire:click="$set('confirmingFusion', true)"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-yellow-500 border border-transparent rounded-md hover:bg-yellow-600 transition-colors disabled:opacity-50">
                                <em class="text-xl ni ni-repeat mr-2"></em> {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Fusion rattrapage' : 'Commencer fusion' }}
                                <span wire:loading wire:target="$set('confirmingFusion', true)" class="ml-2 animate-spin">⟳</span>
                            </button>
                        @elseif($statut === 'fusion')
                            @if($etapeFusion === 1)
                                <button wire:click="confirmVerify2"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-yellow-500 border border-transparent rounded-md hover:bg-yellow-600 transition-colors disabled:opacity-50">
                                     <em class="text-xl ni ni-repeat mr-2"></em> Étape 2
                                    <span wire:loading wire:target="confirmVerify2" class="ml-2 animate-spin">⟳</span>
                                </button>
                            @elseif($etapeFusion === 2)
                                <button wire:click="confirmVerify3"
                                        wire:loading.attr="disabled"
                                        wire:key="verify3-{{ now()->timestamp }}"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-yellow-500 border border-transparent rounded-md hover:bg-yellow-600 transition-colors disabled:opacity-50">
                                    <em class="text-xl ni ni-repeat mr-2"></em> Étape finale
                                    <span wire:loading wire:target="confirmVerify3" class="ml-2 animate-spin">⟳</span>
                                </button>
                            @endif
                            
                            @if($showResetButton)
                                @can('resultats.reset-fusion')
                                    <button wire:click="confirmResetFusion"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-100 border border-red-200 rounded-md hover:bg-red-200 transition-colors disabled:opacity-50">
                                        <em class="text-xl ni ni-trash-alt mr-2"></em> Réinitialiser
                                        <span wire:loading wire:target="confirmResetFusion" class="ml-2 animate-spin">⟳</span>
                                    </button>
                                @endcan
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>


        {{-- 3. Validation --}}
        <div class="p-4 border rounded-lg {{ $statut === 'valide' ? 'bg-purple-50 border-purple-200 dark:bg-purple-900/10 dark:border-purple-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($statut === 'initial' || $statut === 'verification' || ($statut === 'fusion' && $etapeFusion < 3))
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                            <span class="text-sm font-medium">3</span>
                        </div>
                    @elseif($statut === 'fusion' && $etapeFusion >= 3)
                        <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-blue-500 dark:bg-blue-600">
                            <span class="text-sm font-medium">3</span>
                        </div>
                    @elseif($statut === 'valide')
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-purple-500 rounded-full dark:bg-purple-600">
                            <span class="text-sm font-medium">3</span>
                        </div>
                    @else
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                
                <div class="ml-4 flex-1">
                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                        Validation des résultats
                    </h4>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Validez les résultats de rattrapage après vérification.
                        @else
                            Validez les résultats après vérification.
                        @endif
                    </p>
                   @if(auth()->user()->hasAnyRole(['superadmin']))  
                        @if($statut === 'fusion' && $etapeFusion >= 3)
                            <div class="mt-3">
                                <button wire:click="confirmValidation"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 transition-colors disabled:opacity-50">
                                    ✅ Valider les résultats
                                    <span wire:loading wire:target="confirmValidation" class="ml-2 animate-spin">⟳</span>
                                </button>
                            </div>
                        @endif
                    @endif

                    @if($statut === 'fusion' && $etapeFusion >= 1 && $etapeFusion <= 2)
                        <div class="mt-3">
                            <a href="{{ route('resultats.verification', ['examenId' => $examen_id]) }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 transition-colors">
                                👁️ Vérification étape {{ $etapeFusion }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        

        {{-- 4. Publication --}}
        <div class="p-4 border rounded-lg {{ $statut === 'publie' ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : ($statut === 'annule' ? 'bg-red-50 border-red-200 dark:bg-red-900/10 dark:border-red-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700') }}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($statut === 'initial' || $statut === 'verification' || $statut === 'fusion')
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                            <span class="text-sm font-medium">4</span>
                        </div>
                    @elseif($statut === 'valide')
                        <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-blue-500 dark:bg-blue-600">
                            <span class="text-sm font-medium">4</span>
                        </div>
                    @elseif($statut === 'annule')
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-red-500 rounded-full dark:bg-red-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    @else
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                
                <div class="ml-4 flex-1">
                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                        Publication des résultats
                    </h4>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Publiez les résultats de rattrapage pour les étudiants.
                        @else
                            Publiez les résultats pour les rendre accessibles.
                        @endif
                    </p>

                    @if($statut === 'valide')
                        <div class="flex flex-wrap gap-2 mt-3">
                            <button wire:click="confirmPublication"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 transition-colors disabled:opacity-50">
                                📤 Publier les résultats
                                <span wire:loading wire:target="confirmPublication" class="ml-2 animate-spin">⟳</span>
                            </button>
                        </div>
                    @endif

                    @if($statut === 'publie')
                        <div class="flex flex-wrap gap-2 mt-3">
                            <a href="{{ route('resultats.finale') }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 transition-colors">
                                👁️ Voir résultats
                            </a>
                           @if(auth()->user()->hasAnyRole(['superadmin']))
                            <button wire:click="$set('confirmingAnnulation', true)"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-md hover:bg-red-100 transition-colors disabled:opacity-50">
                                ❌ Annuler
                            </button>
                            @endif
                        </div>
                    @endif

                    @if($statut === 'annule')
                        <div class="mt-3">
                            <button wire:click="$set('confirmingRevenirValidation', true)"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors disabled:opacity-50">
                                ↻ Réactiver
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@include('livewire.fusion.partials.modals')