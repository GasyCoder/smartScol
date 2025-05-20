<!-- Onglet 1: Processus de fusion -->
<div id="content-process" class="tab-content" x-show="$wire.activeTab === 'process'" style="{{ $activeTab !== 'process' ? 'display: none;' : '' }}">
    <!-- Étapes du processus - Interface simplifiée -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- 1. Vérification de cohérence -->
        <div class="p-5 border rounded-lg {{ $statut === 'verification' ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/10 dark:border-blue-800' : ($statut === 'initial' ? 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700') }}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($statut === 'initial')
                        <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-primary-500 dark:bg-primary-600">
                            <span>1</span>
                        </div>
                    @else
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="ml-4">
                    <h4 class="text-base font-medium text-gray-800 dark:text-gray-200">Rapport de la cohérence</h4>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Génère un rapport pour vérifier la cohérence entre les manchettes et les copies.</p>

                    @if($statut !== 'initial')
                    <div class="mt-3">
                        <button
                            wire:click="verifierCoherence"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-700 dark:hover:bg-primary-600"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Vérifier la cohérence
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. Fusion des données -->
        <div class="p-5 border rounded-lg {{ $statut === 'fusion' ? 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/10 dark:border-yellow-800' : ($statut === 'initial' || $statut === 'verification' ? 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700') }}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($statut === 'initial' || $statut === 'verification')
                        <div class="flex items-center justify-center w-8 h-8 text-white {{ $statut === 'verification' ? 'bg-primary-500 dark:bg-primary-600' : 'bg-gray-400 dark:bg-gray-600' }} rounded-full">
                            <span>2</span>
                        </div>
                    @else
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @endif
                </div>
                <div wire:poll.5s="pollOperationStatus" class="ml-4 space-y-4">
                    <div class="flex items-center space-x-2">
                        <h4 class="text-base font-semibold text-gray-800 dark:text-gray-100">Fusion des données</h4>

                        <!-- Badge d'étape de fusion -->
                        @if($statut === 'fusion')
                        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{
                            $etapeFusion == 1 ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100' :
                            ($etapeFusion == 2 ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100' :
                            'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100')
                        }}">
                            Étape {{ $etapeFusion }}/3
                        </div>
                        @endif
                    </div>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Associe les manchettes aux copies pour générer les résultats provisoires.</p>

                    <!-- Progress Indicator -->
                    @if($isProcessing)
                        <div class="flex items-center mt-3 space-x-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-5 h-5 text-yellow-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Fusion en cours, veuillez patienter...</span>
                        </div>
                    @endif

                    <!-- Progress Bar -->
                    <div class="mt-3">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-yellow-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $etapeProgress }}%"></div>
                        </div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Progression: {{ $etapeProgress }}%</p>
                    </div>

                    <!-- Indicateur d'étape de fusion -->
                    @if($statut === 'fusion')
                    <div class="mt-2 text-sm">
                        <div class="flex items-center justify-between px-1">
                            <span class="{{ $etapeFusion >= 1 ? 'font-medium text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400' }}">
                                1ère fusion
                            </span>
                            <span class="{{ $etapeFusion >= 2 ? 'font-medium text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400' }}">
                                2ème fusion
                            </span>
                            <span class="{{ $etapeFusion >= 3 ? 'font-medium text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400' }}">
                                Fusion finale
                            </span>
                        </div>
                        <div class="relative w-full h-1 mt-1 bg-gray-200 rounded dark:bg-gray-700">
                            <div class="absolute top-0 left-0 w-1/3 h-1 bg-yellow-500 rounded {{ $etapeFusion >= 1 ? 'opacity-100' : 'opacity-30' }}"></div>
                            <div class="absolute top-0 left-1/3 w-1/3 h-1 bg-yellow-500 rounded {{ $etapeFusion >= 2 ? 'opacity-100' : 'opacity-30' }}"></div>
                            <div class="absolute top-0 left-2/3 w-1/3 h-1 bg-yellow-500 rounded {{ $etapeFusion >= 3 ? 'opacity-100' : 'opacity-30' }}"></div>
                        </div>
                    </div>
                    @endif

                    @if($statut === 'verification')
                        <div class="flex flex-col mt-4 space-y-3 sm:flex-row sm:space-x-3 sm:space-y-0">
                            <button
                                wire:click="confirmerFusion"
                                wire:loading.attr="disabled"
                                aria-label="Fusionner les données"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <em class="mr-2 text-sm icon ni ni-shuffle"></em>
                                Première fusion
                            </button>
                        </div>
                    @elseif($statut === 'fusion')
                        <div class="flex flex-col mt-4 space-y-3 sm:flex-row sm:space-x-3 sm:space-y-0">
                            @can('resultats.fusion')
                            <button
                                wire:click="confirmerFusion"
                                wire:loading.attr="disabled"
                                aria-label="{{ $etapeFusion == 1 ? 'Deuxième fusion' : ($etapeFusion == 2 ? 'Dernière fusion' : 'Refusionner') }}"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium transition-colors duration-200 border rounded-lg shadow-sm text-cyan-900 bg-cyan-100 border-cyan-200 hover:bg-cyan-200 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 dark:bg-cyan-800 dark:text-cyan-100 dark:border-cyan-700 dark:hover:bg-cyan-700 dark:focus:ring-cyan-600 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <em class="mr-2 text-sm icon ni ni-shuffle"></em>
                                @if($etapeFusion == 1)
                                    Deuxième fusion (moyennes)
                                @elseif($etapeFusion == 2)
                                    Dernière fusion (consolidation)
                                @else
                                    Refusionner
                                @endif
                            </button>
                            @endcan
                            @can('resultats.reset-fusion')
                                <button
                                    wire:click="confirmResetFusion"
                                    wire:loading.attr="disabled"
                                    aria-label="Réinitialiser la fusion"
                                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-red-700 transition-colors duration-200 bg-red-100 border border-red-200 rounded-lg shadow-sm hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:bg-red-800 dark:text-red-100 dark:border-red-700 dark:hover:bg-red-700 dark:focus:ring-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <em class="mr-2 text-sm icon ni ni-history"></em>
                                    Réinitialiser
                                </button>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Validation des résultats -->
        <div class="p-5 border rounded-lg {{ $statut === 'validation' ? 'bg-purple-50 border-purple-200 dark:bg-purple-900/10 dark:border-purple-800' : ($statut === 'initial' || $statut === 'verification' || $statut === 'fusion' ? 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700') }}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($statut === 'initial' || $statut === 'verification')
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                            <span>3</span>
                        </div>
                    @elseif($statut === 'fusion')
                        <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-primary-500 dark:bg-primary-600">
                            <span>3</span>
                        </div>
                    @else
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="ml-4">
                    <h4 class="text-base font-medium text-gray-800 dark:text-gray-200">Validation des résultats</h4>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Valide les résultats après vérification manuelle des copies.</p>

                    @if($statut === 'fusion')
                    <div class="mt-3">
                        <div class="flex flex-wrap gap-2">

                             <a href="{{ route('resultats.provisoires', ['examen_id' => $examen_id]) }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                <em class="mr-1 text-sm icon ni ni-form-validation-fill"></em>
                                Vérifier
                            </a>

                            <button
                                wire:click="validerResultats"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:bg-purple-700 dark:hover:bg-purple-600">
                               <em class="mr-1 text-sm icon ni ni-checkbox"></em>
                                Valider
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 4. Publication des résultats -->
        <div class="p-5 border rounded-lg {{ $statut === 'publie' ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : ($statut === 'initial' || $statut === 'verification' || $statut === 'fusion' || $statut === 'validation' ? 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700') }}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($statut === 'initial' || $statut === 'verification' || $statut === 'fusion')
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                            <span>4</span>
                        </div>
                    @elseif($statut === 'validation')
                        <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-primary-500 dark:bg-primary-600">
                            <span>4</span>
                        </div>
                    @else
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="ml-4">
                    <h4 class="text-base font-medium text-gray-800 dark:text-gray-200">Publication des résultats</h4>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Publie les résultats finaux pour consultation par les étudiants.</p>

                    @if($statut === 'validation')
                    <div class="mt-3">
                        <button
                            wire:click="publierResultats"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700 dark:hover:bg-green-600">
                            <em class="mr-1 text-sm icon ni ni-globe"></em>
                            Publier
                        </button>
                    </div>
                    @endif

                    @if($statut === 'publie')
                    <div class="mt-3">

                        <a href="{{ route('resultats.finale') }}"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700 dark:hover:bg-green-600"
                        >
                           <em class="mr-1 text-sm icon ni ni-eye"></em>
                            Consulter les résultats
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Guide d'étapes -->
    <div class="p-4 mt-6 border rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
        <h4 class="mb-2 text-base font-medium text-gray-900 dark:text-white">Guide d'étapes</h4>
        <div class="text-sm text-gray-600 dark:text-gray-300">
            @if($statut === 'initial')
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="ml-3">
                    <p>
                        <strong>Étape à suivre: Vérification de cohérence</strong><br>
                        Commencez par vérifier la cohérence entre les manchettes et les copies pour vous assurer que toutes les données sont correctes avant de procéder à la fusion.
                    </p>
                </div>
            </div>
            @elseif($statut === 'verification')
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="ml-3">
                    <p>
                        <strong>Étape actuelle: Vérification de cohérence</strong><br>
                        Vérifiez le rapport de cohérence pour vous assurer que toutes les manchettes correspondent à des copies.
                        Si les données sont cohérentes, passez à l'étape de fusion.
                    </p>
                </div>
            </div>
            @elseif($statut === 'fusion')
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 text-purple-500 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                </svg>
                <div class="ml-3">
                    <p>
                        <strong>Étape actuelle: Fusion des données
                            @if($etapeFusion == 1)
                                (1ère fusion)
                            @elseif($etapeFusion == 2)
                                (2ème fusion - Calcul des moyennes)
                            @else
                                (Fusion finale - Consolidation)
                            @endif
                        </strong>
                        <br>
                        @if($etapeFusion == 1)
                            Les résultats provisoires ont été générés. Vous pouvez passer à la 2ème fusion pour calculer les moyennes par UE et générales.
                        @elseif($etapeFusion == 2)
                            Les moyennes par UE et générales ont été calculées. Vous pouvez effectuer la dernière fusion pour consolider les résultats.
                        @else
                            Les résultats sont consolidés. Veuillez les vérifier en cliquant sur "Vérifier" puis validez-les pour passer à l'étape suivante.
                        @endif
                    </p>
                </div>
            </div>
            @elseif($statut === 'validation')
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="ml-3">
                    <p>
                        <strong>Étape actuelle: Validation des résultats</strong><br>
                        Les résultats ont été validés. Vous pouvez maintenant les publier pour les rendre accessibles aux étudiants et autres utilisateurs.
                        @if($estPACES)
                        <br><strong>Note:</strong> PACES 1ère année est considérée comme un concours sans délibération.
                        @endif
                    </p>
                </div>
            </div>
            @elseif($statut === 'publie')
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 text-teal-500 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div class="ml-3">
                    <p>
                        <strong>Étape terminée: <span class="text-green-800 dark:text-green-400">Résultats publiés</span></strong><br>
                        Les résultats ont été publiés avec succès et sont maintenant accessibles dans la section des résultats finaux.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
