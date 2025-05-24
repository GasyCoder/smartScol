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
                <h4 class="text-base font-medium text-gray-800 dark:text-gray-200">Rapport de cohérence</h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Vérifiez la cohérence entre les manchettes et les copies avant de procéder à la fusion.</p>
                <div class="mt-3">
                    @if($statut === 'initial' || ($statut === 'verification' && !$showFusionButton))
                        <button
                            wire:click="confirmVerification"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-700 dark:hover:bg-primary-600 disabled:opacity-50"
                        >
                            <em class="mr-2 icon ni ni-clipboard"></em>
                            Vérifier la cohérence
                            <span wire:loading wire:target="confirmVerification" class="ml-2 animate-spin icon ni ni-loader"></span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Fusion des données -->
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
                <h4 class="text-base font-semibold text-gray-800 dark:text-gray-100">Fusion des données</h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Associe les manchettes aux copies pour générer les résultats provisoires.</p>
                @if($statut === 'fusion')
                    <div class="px-3 py-2 mt-2 text-sm bg-gray-100 rounded-md dark:bg-gray-700">
                        <div class="font-medium text-gray-700 dark:text-gray-300">
                            @if($etapeFusion === 1)
                                Étape 1: Première fusion
                            @elseif($etapeFusion === 2)
                                Étape 2: Seconde fusion
                            @elseif($etapeFusion === 3)
                                Étape 3: Fusion finale
                            @elseif($etapeFusion === 4)
                                Étape 4: Validation (VALIDE)
                            @else
                                En attente de fusion
                            @endif
                        </div>
                    </div>
                @endif
                @if($isProcessing)
                    <div class="flex items-center mt-3 space-x-2 text-sm text-gray-600 dark:text-gray-400">
                        <em class="text-yellow-500 icon ni ni-loader animate-spin"></em>
                        <span>Traitement en cours, veuillez patienter...</span>
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
                        Commencer la fusion
                        <span wire:loading wire:target="$set('confirmingFusion', true)" class="ml-2 animate-spin icon ni ni-loader"></span>
                    </button>
                    @elseif($statut === 'fusion')
                        @if($etapeFusion === 1)
                            <button
                                wire:click="confirmVerify2"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-500 disabled:opacity-50">
                                <em class="mr-2 icon ni ni-reload"></em>
                                Fusion - Étape 2
                                <span wire:loading wire:target="confirmVerify2" class="ml-2 animate-spin icon ni ni-loader"></span>
                            </button>
                        @elseif($etapeFusion === 2)
                            <button
                                wire:click="confirmVerify3"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-500 disabled:opacity-50">
                                <em class="mr-2 icon ni ni-reload"></em>
                                Fusion finale
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

    <!-- 3. Vérification et Validation -->
    <div class="p-5 border rounded-lg {{ $statut === 'valide' ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/10 dark:border-blue-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($statut === 'initial' || $statut === 'verification' || ($statut === 'fusion' && $etapeFusion < 1))
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                        <span>3</span>
                    </div>
                @elseif($statut === 'fusion' && $etapeFusion >= 1)
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
                <h4 class="text-base font-medium text-gray-800 dark:text-gray-200">Vérification et Validation</h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Vérifiez les résultats fusionnés et validez-les pour préparer la publication.</p>
                <div class="mt-3">
                    <div class="flex flex-wrap gap-2">
                        @if($statut === 'fusion' && $etapeFusion >= 1)
                            <a href="{{ route('resultats.verification', ['examenId' => $examen_id]) }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
                            >
                                <em class="icon ni ni-eye mr-1.5"></em>
                                Vérifier les résultats
                            </a>
                        @if($etapeFusion >= 3)
                            <button
                                wire:click="confirmValidation"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50"
                            >
                                <em class="icon ni ni-check mr-1.5"></em>
                                Valider les résultats
                                <span wire:loading wire:target="confirmValidation" class="ml-2 animate-spin icon ni ni-loader"></span>
                            </button>
                        @endif
                        @elseif($statut === 'valide')
                            <a href="#"
                                wire:click="switchTab('rapport-stats')"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
                            >
                                <em class="icon ni ni-eye mr-1.5"></em>
                                Consulter les résultats validés
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Publication ou transfert des résultats -->
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
                <h4 class="text-base font-medium text-gray-800 dark:text-gray-200">Publication ou transfert des résultats</h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Publiez les résultats pour les rendre accessibles aux étudiants ou transférez-les pour délibération.</p>
                @if($statut === 'valide')
                    @php
                        $contexte = $this->contexteExamen;
                        $requiresDeliberation = $contexte['requires_deliberation'] ?? false;
                        $isConcours = $contexte['is_concours'] ?? false;
                        $hasRattrapage = $contexte['has_rattrapage'] ?? false;
                        $sessionType = $contexte['session_type'] ?? 'N/A';
                        $niveauNom = $contexte['niveau']->nom ?? 'N/A';
                        $anneeUniv = $contexte['annee_universitaire'] ?? 'N/A';
                    @endphp
                    <div class="mt-3">
                        <div class="flex flex-wrap gap-2">
                            {{-- <a href="#"
                                wire:click="switchTab('rapport-stats')"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
                            >
                                <em class="icon ni ni-eye mr-1.5"></em>
                                Vérifier les résultats
                            </a> --}}
                            <button
                                wire:click="confirmPublication"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white {{ $requiresDeliberation ? 'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500' : ($isConcours ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500') }} border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 dark:{{ $requiresDeliberation ? 'bg-purple-700 hover:bg-purple-600' : ($isConcours ? 'bg-blue-700 hover:bg-blue-600' : 'bg-green-700 hover:bg-green-600') }} disabled:opacity-50"
                            >
                                @if($requiresDeliberation)
                                    <em class="icon ni ni-users mr-1.5"></em>
                                    Transférer pour délibération
                                @elseif($isConcours)
                                    <em class="icon ni ni-target mr-1.5"></em>
                                    Classer et publier
                                @else
                                    <em class="icon ni ni-check mr-1.5"></em>
                                    Publier les résultats
                                @endif
                                <span wire:loading wire:target="confirmPublication" class="ml-2 animate-spin icon ni ni-loader"></span>
                            </button>
                        </div>
                        <x-context-message
                            :requiresDeliberation="$requiresDeliberation"
                            :isConcours="$isConcours"
                        />
                    </div>
                @endif
                <!-- Section des actions post-publication avec interface optimisée -->
                @if($statut === 'publie')
                    <div class="mt-6 space-y-4">
                        <!-- Actions principales -->
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('resultats.finale') }}"
                                class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-200 bg-green-600 border border-transparent rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700 dark:hover:bg-green-600 min-w-[200px]"
                            >
                                <em class="mr-2 text-base icon ni ni-eye"></em>
                                <span>Consulter les résultats officiels</span>
                            </a>
                            <button
                                wire:click="$set('confirmingAnnulation', true)"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-red-700 transition-all duration-200 bg-red-50 border border-red-200 rounded-lg shadow-sm hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-900 dark:text-red-100 dark:border-red-700 dark:hover:bg-red-800 disabled:opacity-50 min-w-[200px]"
                            >
                                <em class="mr-2 text-base icon ni ni-cross"></em>
                                <span>Annuler les résultats</span>
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
                                        Résultats officiellement publiés
                                    </p>
                                    <p class="mt-1 text-xs text-green-700 dark:text-green-300">
                                        Les étudiants peuvent consulter leurs résultats. L'annulation reste possible en cas de besoin.
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
                                class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-blue-700 transition-all duration-200 bg-blue-50 border border-blue-200 rounded-lg shadow-sm hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-900 dark:text-blue-100 dark:border-blue-700 dark:hover:bg-blue-800 disabled:opacity-50 min-w-[200px]"
                            >
                                <em class="mr-2 text-base icon ni ni-arrow-left"></em>
                                <span>Réactiver les résultats</span>
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
                                        Résultats annulés
                                    </p>
                                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                        Les résultats annulés peuvent être réactivés pour une nouvelle vérification ou republication.
                                        Les données originales sont préservées pour permettre une restauration complète.
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
<div class="p-6 mt-6 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800/50 dark:border-gray-700">
    <div class="flex items-start space-x-4">
        <!-- Icône indicatrice -->
        <div class="flex-shrink-0">
            <div class="flex items-center justify-center w-10 h-10 text-green-600 bg-green-100 rounded-full dark:bg-green-800 dark:text-green-300">
                <em class="text-lg icon ni ni-download-cloud"></em>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="flex-1 min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <!-- Section de titre et description -->
                <div class="mb-4 sm:mb-0">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Export des résultats
                    </h4>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Résultats publiés avec succès. Téléchargez les données dans le format de votre choix.
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
