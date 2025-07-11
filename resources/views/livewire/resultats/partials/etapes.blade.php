<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <!-- 1. Vérification de cohérence -->
    <div class="p-5 border rounded-lg {{ $statut === 'verification' && !$showFusionButton ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/10 dark:border-blue-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($statut === 'initial' || ($statut === 'verification' && !$showFusionButton))
                    <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-primary-500 dark:bg-primary-600">
                        <span>1</span>
                    </div>
                @else
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                        <em class="icon ni ni-check"></em>
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
                        <button
                            wire:click="confirmVerification"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-700 dark:hover:bg-primary-600 disabled:opacity-50"
                        >
                            <em class="mr-2 icon ni ni-clipboard"></em>
                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                Vérifier cohérence rattrapage
                            @else
                                Vérifier la cohérence
                            @endif
                            <span wire:loading wire:target="confirmVerification" class="ml-2 animate-spin icon ni ni-loader"></span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Fusion des données - SECTION ADAPTÉE POUR SESSIONS -->
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
                        <em class="icon ni ni-check"></em>
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
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    → <span class="text-orange-600 dark:text-orange-400">Vérification rattrapage requise</span>
                                @else
                                    → <span class="text-blue-600 dark:text-blue-400">Première vérification requise</span>
                                @endif
                            @elseif($etapeFusion === 2)
                                ✅ Seconde fusion terminée
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    → <span class="text-orange-600 dark:text-orange-400">Seconde vérification rattrapage requise</span>
                                @else
                                    → <span class="text-blue-600 dark:text-blue-400">Seconde vérification requise</span>
                                @endif
                            @elseif($etapeFusion === 3)
                                ✅ Fusion finale terminée
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    → <span class="text-green-600 dark:text-green-400">Rattrapage prêt pour validation</span>
                                @else
                                    → <span class="text-green-600 dark:text-green-400">Prêt pour validation</span>
                                @endif
                            @elseif($etapeFusion === 4)
                                ✅ Toutes vérifications terminées
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    → <span class="text-green-600 dark:text-green-400">Rattrapage prêt pour validation finale</span>
                                @else
                                    → <span class="text-green-600 dark:text-green-400">Prêt pour validation finale</span>
                                @endif
                            @else
                                ⏳ En attente de démarrage de la fusion
                            @endif
                        </div>
                    </div>
                @endif

                @if($isProcessing)
                    <div class="flex items-center mt-3 space-x-2 text-sm text-gray-600 dark:text-gray-400">
                        <em class="text-yellow-500 icon ni ni-loader animate-spin"></em>
                        <span>
                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                Traitement des données de rattrapage en cours...
                            @else
                                Traitement en cours, veuillez patienter...
                            @endif
                        </span>
                    </div>
                @endif

                <div class="flex flex-col mt-4 space-y-3 sm:flex-row sm:space-x-3 sm:space-y-0">
                    @if($statut === 'verification' && $showFusionButton)
                    <button
                        wire:click="$set('confirmingFusion', true)"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-500 disabled:opacity-50"
                    >
                        <em class="mr-2 icon ni ni-reload"></em>
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Commencer fusion rattrapage
                        @else
                            Commencer la fusion
                        @endif
                        <span wire:loading wire:target="$set('confirmingFusion', true)" class="ml-2 animate-spin icon ni ni-loader"></span>
                    </button>
                    @elseif($statut === 'fusion')
                        @if($etapeFusion === 1)
                            <button
                                wire:click="confirmVerify2"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-500 disabled:opacity-50">
                                <em class="mr-2 icon ni ni-reload"></em>
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    Fusion rattrapage - Étape 2
                                @else
                                    Fusion - Étape 2
                                @endif
                                <span class="ml-2 text-xs text-gray-200">(après première vérification)</span>
                                <span wire:loading wire:target="confirmVerify2" class="ml-2 animate-spin icon ni ni-loader"></span>
                            </button>
                        @elseif($etapeFusion === 2)
                            <button
                                wire:click="confirmVerify3"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-500 disabled:opacity-50">
                                <em class="mr-2 icon ni ni-reload"></em>
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    Fusion finale rattrapage - Étape 3
                                @else
                                    Fusion finale - Étape 3
                                @endif
                                <span class="ml-2 text-xs text-gray-200">(après seconde vérification)</span>
                                <span wire:loading wire:target="confirmVerify3" class="ml-2 animate-spin icon ni ni-loader"></span>
                            </button>
                        @endif
                        @if($showResetButton)
                            @can('resultats.reset-fusion')
                                <button
                                    wire:click="confirmResetFusion"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-red-700 transition-colors duration-200 bg-red-100 border border-red-200 rounded-lg shadow-sm hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:bg-red-800 dark:text-red-100 dark:border-red-700 dark:hover:bg-red-700 dark:focus:ring-red-600 disabled:opacity-50"
                                >
                                    <em class="mr-2 icon ni ni-trash"></em>
                                    Réinitialiser
                                    <span wire:loading wire:target="confirmResetFusion" class="ml-2 animate-spin icon ni ni-loader"></span>
                                </button>
                            @endcan
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Vérification et Validation - VERSION ADAPTÉE SESSIONS -->
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
                        <em class="icon ni ni-check"></em>
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

                <!-- Indicateur de l'étape de vérification actuelle -->
                @if($statut === 'fusion')
                    <div class="px-3 py-2 mt-3 text-sm bg-gray-100 rounded-md dark:bg-gray-700">
                        <div class="font-medium text-gray-700 dark:text-gray-300">
                            @if($etapeFusion === 1)
                                📋 <span class="text-blue-600 dark:text-blue-400">Première vérification disponible</span>
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">Vérifiez les données de rattrapage de la fusion initiale</div>
                                @else
                                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">Vérifiez les résultats de la fusion initiale</div>
                                @endif
                            @elseif($etapeFusion === 2)
                                📋 <span class="text-blue-600 dark:text-blue-400">Seconde vérification disponible</span>
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">Vérifiez les résultats après la seconde fusion de rattrapage</div>
                                @else
                                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">Vérifiez les résultats après la seconde fusion</div>
                                @endif
                            @elseif($etapeFusion === 3)
                                ✅ <span class="text-green-600 dark:text-green-400">Fusion et vérifications terminées</span>
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">Les résultats de rattrapage sont prêts pour la validation finale.</div>
                                @else
                                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">Les résultats sont prêts pour la validation finale.</div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mt-4">
                    <div class="flex flex-wrap gap-3">
                        <!-- Bouton de vérification - Disponible uniquement pour les étapes 1 et 2 -->
                        @if($statut === 'fusion' && $etapeFusion >= 1 && $etapeFusion <= 2)
                            <a href="{{ route('resultats.verification', ['examenId' => $examen_id]) }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-200 bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-700 dark:hover:bg-blue-600"
                            >
                                <em class="mr-2 icon ni ni-eye"></em>
                                @if($etapeFusion === 1)
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Effectuer la première vérification rattrapage
                                    @else
                                        Effectuer la première vérification
                                    @endif
                                @elseif($etapeFusion === 2)
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Effectuer la seconde vérification rattrapage
                                    @else
                                        Effectuer la seconde vérification
                                    @endif
                                @endif
                            </a>

                            <!-- Indicateur de progression des vérifications -->
                            <div class="inline-flex items-center px-3 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg dark:bg-gray-700 dark:text-gray-300">
                                <em class="mr-2 text-blue-500 icon ni ni-info"></em>
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    Étape {{ $etapeFusion }}/2 vérification rattrapage
                                @else
                                    Étape {{ $etapeFusion }}/2 de vérification
                                @endif
                            </div>
                        @elseif($statut === 'valide')
                            <!-- Mode consultation après validation -->
                            <div class="inline-flex items-center px-3 py-2 text-sm text-green-600 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                                <em class="mr-2 icon ni ni-check-circle"></em>
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    Toutes les vérifications de rattrapage terminées
                                @else
                                    Toutes les vérifications terminées
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Séparateur visuel pour la validation finale -->
                    @if($statut === 'fusion' && $etapeFusion >= 3)
                        <div class="pt-4 mt-6 border-t border-gray-200 dark:border-gray-600">
                            <h5 class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    Validation finale des résultats de rattrapage
                                @else
                                    Validation finale
                                @endif
                            </h5>
                            <p class="mb-3 text-xs text-gray-600 dark:text-gray-400">
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    Une fois toutes les vérifications effectuées, vous pouvez valider définitivement les résultats de rattrapage.
                                    Les meilleures notes entre session normale et rattrapage seront automatiquement retenues.
                                @else
                                    Une fois toutes les vérifications effectuées, vous pouvez valider définitivement les résultats.
                                @endif
                            </p>
                            <button
                                wire:click="confirmValidation"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-200 bg-green-600 border border-transparent rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700 dark:hover:bg-green-600 disabled:opacity-50"
                            >
                                <em class="mr-2 icon ni ni-check"></em>
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    Valider définitivement les résultats de rattrapage
                                @else
                                    Valider définitivement les résultats
                                @endif
                                <span wire:loading wire:target="confirmValidation" class="ml-2 animate-spin icon ni ni-loader"></span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<!-- 4. Publication ou transfert des résultats - VERSION ADAPTÉE SESSIONS -->
    <div class="p-5 border rounded-lg {{ $statut === 'publie' ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : ($statut === 'annule' ? 'bg-red-50 border-red-200 dark:bg-red-900/10 dark:border-red-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700') }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($statut === 'initial' || $statut === 'verification' || $statut === 'fusion' || $statut === 'valide')
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                        <span>4</span>
                    </div>
                @elseif($statut === 'annule')
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-red-500 rounded-full dark:bg-red-600">
                        <em class="icon ni ni-cross"></em>
                    </div>
                @else
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                        <em class="icon ni ni-check"></em>
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
                        // Vérifier s'il y a des résultats en attente (cas de réactivation)
                        $resultatsEnAttente = \App\Models\ResultatFinal::where('examen_id', $examen_id)
                            ->where('session_exam_id', $sessionActive->id)
                            ->where('statut', \App\Models\ResultatFinal::STATUT_EN_ATTENTE)
                            ->exists();

                        $estReactivation = $resultatsEnAttente;
                    @endphp

                    <div class="mt-3">
                        <div class="flex flex-wrap gap-2">
                            {{-- Bouton de publication/republication --}}
                            <button
                                wire:click="confirmPublication"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white {{ $estReactivation ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-600' }} border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50"
                            >
                                @if($estReactivation)
                                    <em class="icon ni ni-repeat mr-1.5"></em>
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Republier résultats rattrapage
                                    @else
                                        Republier les résultats
                                    @endif
                                @else
                                    <em class="icon ni ni-check mr-1.5"></em>
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Publier résultats rattrapage
                                    @else
                                        Publier les résultats
                                    @endif
                                @endif
                                <span wire:loading wire:target="confirmPublication" class="ml-2 animate-spin icon ni ni-loader"></span>
                            </button>

                            {{-- Bouton d'aperçu --}}
                            <a href="{{ route('resultats.finale') }}"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                <em class="icon ni ni-eye mr-1.5"></em>
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    Aperçu résultats rattrapage
                                @else
                                    Aperçu des résultats
                                @endif
                            </a>
                        </div>

                        {{-- Message informatif adapté --}}
                        <div class="p-4 mt-4 border {{ $estReactivation ? 'border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700' : 'border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700' }} rounded-lg">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    @if($estReactivation)
                                        <em class="text-blue-600 icon ni ni-repeat dark:text-blue-400"></em>
                                    @else
                                        <em class="text-blue-600 icon ni ni-info dark:text-blue-400"></em>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium {{ $estReactivation ? 'text-blue-800 dark:text-blue-200' : 'text-blue-800 dark:text-blue-200' }}">
                                        @if($estReactivation)
                                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                Republication après réactivation - Session de rattrapage
                                            @else
                                                Republication après réactivation
                                            @endif
                                        @else
                                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                Publication directe - Session de rattrapage
                                            @else
                                                Publication directe
                                            @endif
                                        @endif
                                    </p>
                                    <p class="mt-1 text-xs {{ $estReactivation ? 'text-blue-700 dark:text-blue-300' : 'text-blue-700 dark:text-blue-300' }}">
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                            @if($estReactivation)
                                                Les résultats de rattrapage précédemment annulés seront republiés. Les meilleures notes entre session normale et rattrapage seront automatiquement appliquées.
                                            @else
                                                Les résultats de rattrapage seront publiés. Les meilleures notes entre session normale et rattrapage seront automatiquement appliquées pour déterminer les décisions finales.
                                            @endif
                                        @else
                                            @if($estReactivation)
                                                Les résultats précédemment annulés seront republiés après recalcul des décisions (admis/rattrapage/exclus) selon la moyenne UE.
                                            @else
                                                Les résultats seront publiés directement. Les décisions (admis/rattrapage/exclus) seront calculées automatiquement selon la moyenne UE.
                                            @endif
                                        @endif
                                    </p>
                                    @if($sessionActive)
                                        <p class="mt-2 text-xs {{ $estReactivation ? 'text-blue-700 dark:text-blue-300' : 'text-blue-700 dark:text-blue-300' }}">
                                            <span class="font-medium">Session :</span> {{ $sessionActive->type }}
                                            ({{ $sessionActive->anneeUniversitaire->libelle ?? 'N/A' }})
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Section des actions post-publication -->
                @if($statut === 'publie')
                    <div class="mt-6 space-y-4">
                        <!-- Actions principales -->
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('resultats.finale') }}"
                                class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 bg-green-600 border border-transparent rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700 dark:hover:bg-green-600 min-w-[200px]"
                            >
                                <em class="mr-2 text-base icon ni ni-eye"></em>
                                <span>
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Consulter résultats rattrapage
                                    @else
                                        Consulter les résultats officiels
                                    @endif
                                </span>
                            </a>
                            <button
                                wire:click="$set('confirmingAnnulation', true)"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-red-700 transition-all duration-200 bg-red-50 border border-red-200 rounded-lg shadow-sm hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-900 dark:text-red-100 dark:border-red-700 dark:hover:bg-red-800 disabled:opacity-50 min-w-[200px]"
                            >
                                <em class="mr-2 text-base icon ni ni-cross"></em>
                                <span>
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Annuler résultats rattrapage
                                    @else
                                        Annuler les résultats
                                    @endif
                                </span>
                                <span wire:loading wire:target="$set('confirmingAnnulation', true)" class="ml-2">
                                    <em class="animate-spin icon ni ni-loader"></em>
                                </span>
                            </button>
                        </div>

                        <!-- Message informatif pour les résultats publiés -->
                        <div class="p-4 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-700">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <em class="text-green-600 icon ni ni-check-circle dark:text-green-400"></em>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                            Résultats de rattrapage officiellement publiés
                                        @else
                                            Résultats officiellement publiés
                                        @endif
                                    </p>
                                    <p class="mt-1 text-xs text-green-700 dark:text-green-300">
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                            Les étudiants peuvent consulter leurs résultats finaux de rattrapage. Les meilleures notes ont été automatiquement appliquées.
                                        @else
                                            Les étudiants peuvent consulter leurs résultats. L'annulation reste possible en cas de besoin.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($statut === 'annule')
                    <div class="mt-6 space-y-4">
                        <!-- Action de réactivation -->
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button
                                wire:click="$set('confirmingRevenirValidation', true)"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-blue-700 transition-all duration-200 bg-blue-50 border border-blue-200 rounded-lg shadow-sm hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-900 dark:text-blue-100 dark:border-blue-700 dark:hover:blue-800 disabled:opacity-50 min-w-[200px]"
                            >
                                <em class="mr-2 text-base icon ni ni-arrow-left"></em>
                                <span>
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Réactiver résultats rattrapage
                                    @else
                                        Réactiver les résultats
                                    @endif
                                </span>
                                <span wire:loading wire:target="$set('confirmingRevenirValidation', true)" class="ml-2">
                                    <em class="animate-spin icon ni ni-loader"></em>
                                </span>
                            </button>
                        </div>

                        <!-- Message informatif pour les résultats annulés -->
                        <div class="p-4 border rounded-lg bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-700">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <em class="text-amber-600 icon ni ni-alert dark:text-amber-400"></em>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                            Résultats de rattrapage annulés
                                        @else
                                            Résultats annulés
                                        @endif
                                    </p>
                                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                            Les résultats de rattrapage annulés peuvent être réactivés pour une nouvelle vérification ou republication.
                                            Les données de fusion rattrapage sont préservées.
                                        @else
                                            Les résultats annulés peuvent être réactivés pour une nouvelle vérification ou republication.
                                            Les données originales sont préservées pour permettre une restauration complète.
                                        @endif
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

<!-- Actions export - Affiché seulement après publication -->
@if($statut === 'publie' && $etapeProgress === 100)
<div class="p-6 mt-6 border border-gray-200 rounded-lg
    @if($sessionActive && $sessionActive->type === 'Rattrapage')
        bg-orange-50 border-orange-200 dark:bg-orange-900/20 dark:border-orange-800
    @else
        bg-gray-50 dark:bg-gray-800/50 dark:border-gray-700
    @endif">
    <div class="flex items-start space-x-4">
        <!-- Icône indicatrice -->
        <div class="flex-shrink-0">
            <div class="flex items-center justify-center w-10 h-10
                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                    text-orange-600 bg-orange-100 dark:bg-orange-800 dark:text-orange-300
                @else
                    text-green-600 bg-green-100 dark:bg-green-800 dark:text-green-300
                @endif rounded-full">
                <em class="text-lg icon ni ni-download-cloud"></em>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="flex-1 min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <!-- Section de titre et description -->
                <div class="mb-4 sm:mb-0">
                    <h4 class="text-lg font-semibold
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            text-orange-900 dark:text-orange-200
                        @else
                            text-gray-900 dark:text-gray-100
                        @endif">
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Export des résultats de rattrapage
                        @else
                            Export des résultats
                        @endif
                    </h4>
                    <p class="mt-1 text-sm
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            text-orange-700 dark:text-orange-300
                        @else
                            text-gray-600 dark:text-gray-300
                        @endif">
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Résultats de rattrapage publiés avec succès. Les meilleures notes ont été appliquées automatiquement.
                        @else
                            Résultats publiés avec succès. Téléchargez les données dans le format de votre choix.
                        @endif
                    </p>
                </div>

                <!-- Section des boutons d'action -->
                <div class="flex flex-col gap-3 sm:flex-row sm:ml-6">
                    <!-- Bouton Export Excel -->
                    <button
                        wire:click="exporterExcel"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 bg-green-600 border border-transparent rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-green-700 dark:hover:bg-green-600 min-w-[140px]"
                    >
                        <!-- Icône et texte parfaitement alignés -->
                        <em class="mr-2 text-base icon ni ni-file-xls"></em>
                        <span>Export Excel</span>
                        <!-- Indicateur de chargement -->
                        <span wire:loading wire:target="exporterExcel" class="ml-2">
                            <em class="animate-spin icon ni ni-loader"></em>
                        </span>
                    </button>

                    <!-- Bouton Export PDF -->
                    <button
                        wire:click="exporterPDF"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 bg-red-600 border border-transparent rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-red-700 dark:hover:bg-red-600 min-w-[140px]"
                    >
                        <!-- Icône et texte parfaitement alignés -->
                        <em class="mr-2 text-base icon ni ni-file-pdf"></em>
                        <span>Export PDF</span>
                        <!-- Indicateur de chargement -->
                        <span wire:loading wire:target="exporterPDF" class="ml-2">
                            <em class="animate-spin icon ni ni-loader"></em>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
