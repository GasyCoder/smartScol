{{-- livewire.fusion.partials.etapes--}}
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    
    {{-- 1. Vérification de cohérence --}}
    <div class="p-5 border rounded-lg {{ $statut === 'verification' && !$showFusionButton ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/10 dark:border-blue-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($statut === 'initial' || ($statut === 'verification' && !$showFusionButton))
                    <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-primary-500 dark:bg-primary-600">
                        <span>1</span>
                    </div>
                @else
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                        ✓
                    </div>
                @endif
            </div>
            
            <div class="ml-4">
                <h4 class="text-base font-medium text-gray-800 dark:text-gray-200">
                    Rapport de cohérence
                </h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                        Vérifiez la cohérence des données de rattrapage avant la fusion. Seuls les étudiants éligibles sont concernés.
                    @else
                        Vérifiez la cohérence entre les manchettes et les copies avant de procéder à la fusion.
                    @endif
                </p>

                @if($sessionActive && $sessionActive->type === 'Rattrapage' && $examen)
                    @php
                        $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
                        $compteursDonnees = $this->getCompteursDonneesSession();
                    @endphp

                    <div class="mt-2 text-xs text-orange-700 dark:text-orange-300">
                        <div class="flex items-center space-x-4">
                            <span>Éligibles: {{ $etudiantsEligibles->count() }}</span>
                            <span>Manchettes: {{ $compteursDonnees['manchettes'] }}</span>
                            <span>Copies: {{ $compteursDonnees['copies'] }}</span>
                        </div>

                        @if($etudiantsEligibles->count() == 0 && $compteursDonnees['manchettes'] > 0)
                            <div class="p-2 mt-2 text-orange-800 bg-orange-100 border border-orange-300 rounded dark:bg-orange-900/50 dark:border-orange-700 dark:text-orange-200">
                                ⚠️ Problème détecté: Manchettes existantes mais aucun étudiant éligible trouvé
                            </div>
                        @elseif($compteursDonnees['manchettes'] == 0 && $etudiantsEligibles->count() > 0)
                            <div class="p-2 mt-2 text-orange-800 bg-orange-100 border border-orange-300 rounded dark:bg-orange-900/50 dark:border-orange-700 dark:text-orange-200">
                                ⚠️ {{ $etudiantsEligibles->count() }} étudiant(s) éligible(s) mais aucune donnée initialisée
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mt-3">
                    @if($statut === 'initial' || ($statut === 'verification' && !$showFusionButton))
                        <button wire:click="confirmVerification"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-700 dark:hover:bg-primary-600 disabled:opacity-50">
                            📋 {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Vérifier cohérence rattrapage' : 'Vérifier la cohérence' }}
                            <span wire:loading wire:target="confirmVerification" class="ml-2 animate-spin">⟳</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Fusion des données --}}
    <div class="p-5 border rounded-lg {{ $statut === 'fusion' ? 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/10 dark:border-yellow-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($statut === 'initial' || ($statut === 'verification' && !$showFusionButton))
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                        <span>2</span>
                    </div>
                @elseif($statut === 'verification' && $showFusionButton)
                    <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-primary-500 dark:bg-primary-600">
                        <span>2</span>
                    </div>
                @elseif($statut === 'fusion')
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-yellow-500 rounded-full dark:bg-yellow-600">
                        <span>2</span>
                    </div>
                @else
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                        ✓
                    </div>
                @endif
            </div>
            
            <div class="ml-4 space-y-4">
                <div>
                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-100">
                        Fusion des données en 3 étapes
                    </h4>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Associe les données de rattrapage aux étudiants éligibles pour générer les résultats finaux.
                        @else
                            Associe les manchettes aux copies pour générer les résultats provisoires.
                        @endif
                    </p>
                </div>

                @if($statut === 'fusion')
                    <div class="px-3 py-2 mt-2 text-sm bg-gray-100 rounded-md dark:bg-gray-700">
                        <div class="font-medium text-gray-700 dark:text-gray-300">
                            @if($etapeFusion === 1)
                                ✅ Fusion initiale terminée
                                → <span class="{{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'text-orange-600 dark:text-orange-400' : 'text-blue-600 dark:text-blue-400' }}">Vérification requise</span>
                            @elseif($etapeFusion === 2)
                                ✅ Seconde fusion terminée
                                → <span class="{{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'text-orange-600 dark:text-orange-400' : 'text-blue-600 dark:text-blue-400' }}">Seconde vérification requise</span>
                            @elseif($etapeFusion === 3)
                                ✅ Fusion finale terminée
                                → <span class="text-green-600 dark:text-green-400">Prêt pour validation</span>
                            @else
                                ⏳ En attente de démarrage de la fusion
                            @endif
                        </div>
                    </div>
                @endif

                @if($isProcessing)
                    <div class="flex items-center mt-3 space-x-2 text-sm text-gray-600 dark:text-gray-400">
                        <span class="animate-spin">⟳</span>
                        <span>{{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Traitement des données de rattrapage en cours...' : 'Traitement en cours, veuillez patienter...' }}</span>
                    </div>
                @endif

                <div class="flex flex-col mt-4 space-y-3 sm:flex-row sm:space-x-3 sm:space-y-0">
                    @if($statut === 'verification' && $showFusionButton)
                        <button wire:click="$set('confirmingFusion', true)"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 disabled:opacity-50">
                            🔄 {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Commencer fusion rattrapage' : 'Commencer la fusion' }}
                            <span wire:loading wire:target="$set('confirmingFusion', true)" class="ml-2 animate-spin">⟳</span>
                        </button>
                    @elseif($statut === 'fusion')
                        @if($etapeFusion === 1)
                            <button wire:click="confirmVerify2"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 disabled:opacity-50">
                                🔄 Fusion - Étape 2
                                <span class="ml-2 text-xs text-gray-200">(après première vérification)</span>
                                <span wire:loading wire:target="confirmVerify2" class="ml-2 animate-spin">⟳</span>
                            </button>
                        @elseif($etapeFusion === 2)
                            <button wire:click="confirmVerify3"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 disabled:opacity-50">
                                🔄 Fusion finale - Étape 3
                                <span class="ml-2 text-xs text-gray-200">(après seconde vérification)</span>
                                <span wire:loading wire:target="confirmVerify3" class="ml-2 animate-spin">⟳</span>
                            </button>
                        @endif
                        
                        @if($showResetButton)
                            @can('resultats.reset-fusion')
                                <button wire:click="confirmResetFusion"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-red-700 transition-colors duration-200 bg-red-100 border border-red-200 rounded-lg shadow-sm hover:bg-red-200 disabled:opacity-50">
                                    🗑️ Réinitialiser
                                    <span wire:loading wire:target="confirmResetFusion" class="ml-2 animate-spin">⟳</span>
                                </button>
                            @endcan
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Vérification et Validation --}}
    <div class="p-5 border rounded-lg {{ $statut === 'valide' ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/10 dark:border-blue-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($statut === 'initial' || $statut === 'verification' || ($statut === 'fusion' && $etapeFusion < 1))
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                        <span>3</span>
                    </div>
                @elseif($statut === 'fusion' && $etapeFusion >= 1 && $etapeFusion <= 3)
                    <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-primary-500 dark:bg-primary-600">
                        <span>3</span>
                    </div>
                @elseif($statut === 'valide')
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-blue-500 rounded-full dark:bg-blue-600">
                        <span>3</span>
                    </div>
                @else
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                        ✓
                    </div>
                @endif
            </div>
            
            <div class="ml-4">
                <h4 class="text-base font-medium text-gray-800 dark:text-gray-200">
                    Vérification des résultats
                </h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                        Vérifiez les résultats de rattrapage après chaque fusion. Les meilleures notes entre sessions seront retenues.
                    @else
                        Vérifiez les résultats après chaque fusion pour vous assurer de leur exactitude.
                    @endif
                </p>

                @if($statut === 'fusion')
                    <div class="px-3 py-2 mt-3 text-sm bg-gray-100 rounded-md dark:bg-gray-700">
                        <div class="font-medium text-gray-700 dark:text-gray-300">
                            @if($etapeFusion === 1)
                                📋 <span class="text-blue-600 dark:text-blue-400">Première vérification disponible</span>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Vérifiez les données de rattrapage de la fusion initiale' : 'Vérifiez les résultats de la fusion initiale' }}
                                </div>
                            @elseif($etapeFusion === 2)
                                📋 <span class="text-blue-600 dark:text-blue-400">Seconde vérification disponible</span>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Vérifiez les résultats après la seconde fusion de rattrapage' : 'Vérifiez les résultats après la seconde fusion' }}
                                </div>
                            @elseif($etapeFusion === 3)
                                ✅ <span class="text-green-600 dark:text-green-400">Fusion et vérifications terminées</span>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Les résultats de rattrapage sont prêts pour la validation finale.' : 'Les résultats sont prêts pour la validation finale.' }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mt-4">
                    <div class="flex flex-wrap gap-3">
                        @if($statut === 'fusion' && $etapeFusion >= 1 && $etapeFusion <= 2)
                            <a href="{{ route('resultats.verification', ['examenId' => $examen_id]) }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-200 bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700">
                                👁️ Effectuer la {{ $etapeFusion === 1 ? 'première' : 'seconde' }} vérification
                            </a>
                            
                            <div class="inline-flex items-center px-3 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg dark:bg-gray-700 dark:text-gray-300">
                                📊 Étape {{ $etapeFusion }}/2 de vérification
                            </div>
                        @elseif($statut === 'valide')
                            <div class="inline-flex items-center px-3 py-2 text-sm text-green-600 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                                ✅ Toutes les vérifications terminées
                            </div>
                        @endif
                    </div>

                    @if($statut === 'fusion' && $etapeFusion >= 3)
                        <div class="pt-4 mt-6 border-t border-gray-200 dark:border-gray-600">
                            <h5 class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Validation finale des résultats de rattrapage' : 'Validation finale' }}
                            </h5>
                            <p class="mb-3 text-xs text-gray-600 dark:text-gray-400">
                                {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Une fois toutes les vérifications effectuées, vous pouvez valider définitivement les résultats de rattrapage.' : 'Une fois toutes les vérifications effectuées, vous pouvez valider définitivement les résultats.' }}
                            </p>
                            <button wire:click="confirmValidation"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-200 bg-green-600 border border-transparent rounded-lg shadow-sm hover:bg-green-700 disabled:opacity-50">
                                ✅ Valider définitivement les résultats
                                <span wire:loading wire:target="confirmValidation" class="ml-2 animate-spin">⟳</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Publication des résultats --}}
    <div class="p-5 border rounded-lg {{ $statut === 'publie' ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : ($statut === 'annule' ? 'bg-red-50 border-red-200 dark:bg-red-900/10 dark:border-red-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700') }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($statut === 'initial' || $statut === 'verification' || $statut === 'fusion' || $statut === 'valide')
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                        <span>4</span>
                    </div>
                @elseif($statut === 'annule')
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-red-500 rounded-full dark:bg-red-600">
                        ✕
                    </div>
                @else
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                        ✓
                    </div>
                @endif
            </div>
            
            <div class="ml-4">
                <h4 class="text-base font-medium text-gray-800 dark:text-gray-200">
                    Publication des résultats
                </h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                        Publiez les résultats de rattrapage. Les meilleures notes entre sessions seront automatiquement appliquées.
                    @else
                        Publiez les résultats pour les rendre accessibles aux étudiants.
                    @endif
                </p>

                @if($statut === 'valide')
                    @php
                        $resultatsEnAttente = \App\Models\ResultatFinal::where('examen_id', $examen_id)
                            ->where('session_exam_id', $sessionActive->id)
                            ->where('statut', \App\Models\ResultatFinal::STATUT_EN_ATTENTE)
                            ->exists();
                        $estReactivation = $resultatsEnAttente;
                    @endphp

                    <div class="mt-3">
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="confirmPublication"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 {{ $estReactivation ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500' }}">
                                {{ $estReactivation ? '🔄 Republier les résultats' : '📤 Publier les résultats' }}
                                <span wire:loading wire:target="confirmPublication" class="ml-2 animate-spin">⟳</span>
                            </button>

                            <a href="{{ route('resultats.finale') }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                                👁️ Aperçu des résultats
                            </a>
                        </div>

                        <div class="p-4 mt-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    {{ $estReactivation ? '🔄' : 'ℹ️' }}
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                        {{ $estReactivation ? 'Republication après réactivation' : 'Publication directe' }}
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage') - Session de rattrapage @endif
                                    </p>
                                    <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                            Les résultats de rattrapage seront publiés. Les meilleures notes entre sessions seront automatiquement appliquées.
                                        @else
                                            Les résultats seront publiés directement. Les décisions seront calculées automatiquement.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($statut === 'publie')
                    <div class="mt-6 space-y-4">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('resultats.finale') }}"
                               class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 bg-green-600 border border-transparent rounded-lg shadow-sm hover:bg-green-700 min-w-[200px]">
                                👁️ Consulter les résultats officiels
                            </a>
                            <button wire:click="$set('confirmingAnnulation', true)"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-red-700 transition-all duration-200 bg-red-50 border border-red-200 rounded-lg shadow-sm hover:bg-red-100 disabled:opacity-50 min-w-[200px]">
                                ✕ Annuler les résultats
                            </button>
                        </div>

                        <div class="p-4 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-700">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">✅</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                        Résultats officiellement publiés
                                    </p>
                                    <p class="mt-1 text-xs text-green-700 dark:text-green-300">
                                        {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Les étudiants peuvent consulter leurs résultats finaux de rattrapage.' : 'Les étudiants peuvent consulter leurs résultats. L\'annulation reste possible en cas de besoin.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($statut === 'annule')
                    <div class="mt-6 space-y-4">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button wire:click="$set('confirmingRevenirValidation', true)"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-blue-700 transition-all duration-200 bg-blue-50 border border-blue-200 rounded-lg shadow-sm hover:bg-blue-100 disabled:opacity-50 min-w-[200px]">
                                ← Réactiver les résultats
                            </button>
                        </div>

                        <div class="p-4 border rounded-lg bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-700">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">⚠️</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                        Résultats annulés
                                    </p>
                                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                        {{ $sessionActive && $sessionActive->type === 'Rattrapage' ? 'Les résultats de rattrapage annulés peuvent être réactivés.' : 'Les résultats annulés peuvent être réactivés pour une nouvelle vérification.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>