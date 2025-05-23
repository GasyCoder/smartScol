<!-- Étapes du processus - LOGIQUE SIMPLIFIÉE (3 étapes au lieu de 4) -->
<!-- Interface modernisée avec icônes NioIcon pour une cohérence parfaite -->
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <!-- 1. Vérification de cohérence -->
    <!-- Cette étape reste identique car elle est indépendante de la logique de statuts -->
    <div class="p-5 border rounded-lg {{ $statut === 'verification' ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/10 dark:border-blue-800' : ($statut === 'initial' ? 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700') }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($statut === 'initial')
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
                    <button
                        wire:click="confirmVerification"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-700 dark:hover:bg-primary-600"
                    >
                        <em class="mr-2 icon ni ni-clipboard"></em>
                        Vérifier la cohérence
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Fusion des données -->
    <div class="p-5 border rounded-lg {{ $statut === 'fusion' ? 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/10 dark:border-yellow-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700' }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($statut === 'initial' || $statut === 'verification')
                    <div class="flex items-center justify-center w-8 h-8 text-white {{ $statut === 'verification' ? 'bg-primary-500 dark:bg-primary-600' : 'bg-gray-400 dark:bg-gray-600' }} rounded-full">
                        <span>2</span>
                    </div>
                @elseif($statut === 'fusion')
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-yellow-500 rounded-full dark:bg-yellow-600">
                        <span>2</span>
                    </div>
                @else
                    <!-- États publie et annule - fusion terminée avec succès -->
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full dark:bg-green-600">
                        <em class="icon ni ni-check"></em>
                    </div>
                @endif
            </div>
            <div class="ml-4 space-y-4">
                <h4 class="text-base font-semibold text-gray-800 dark:text-gray-100">Fusion des données</h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Associe les manchettes aux copies pour générer les résultats provisoires.</p>

                <!-- Indicateur d'étape de fusion - logique inchangée -->
                @if($statut === 'fusion')
                    <div class="px-3 py-2 mt-2 text-sm bg-gray-100 rounded-md dark:bg-gray-700">
                        <div class="font-medium text-gray-700 dark:text-gray-300">
                            @if($etapeFusion === 1)
                                Étape 1: Association manchettes/copies
                            @elseif($etapeFusion === 2)
                                Étape 2: Validation des données fusionnées
                            @elseif($etapeFusion === 3)
                                Étape 3: Finalisation - Prêt pour publication
                            @else
                                En attente de fusion
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Indicateur de traitement -->
                @if($isProcessing)
                    <div class="flex items-center mt-3 space-x-2 text-sm text-gray-600 dark:text-gray-400">
                        <em class="text-yellow-500 icon ni ni-loader animate-spin"></em>
                        <span>Traitement en cours, veuillez patienter...</span>
                    </div>
                @endif

                <!-- Boutons d'action pour la fusion -->
                <div class="flex flex-col mt-4 space-y-3 sm:flex-row sm:space-x-3 sm:space-y-0">
                    @if($statut === 'verification')
                        <button
                            wire:click="confirmerFusion"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <em class="mr-2 icon ni ni-reload"></em>
                            Commencer la fusion
                        </button>
                    @elseif($statut === 'fusion')
                        @if($etapeFusion === 1)
                            <button
                                wire:click="confirmerFusion"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <em class="mr-2 icon ni ni-reload"></em>
                                Continuer la fusion
                            </button>
                        @elseif($etapeFusion === 2)
                            <button
                                wire:click="confirmerFusion"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <em class="mr-2 icon ni ni-reload"></em>
                                Finaliser la fusion
                            </button>
                        @elseif($etapeFusion >= 3)
                            <button
                                wire:click="confirmerFusion"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium transition-colors duration-200 border rounded-lg shadow-sm text-cyan-900 bg-cyan-100 border-cyan-200 hover:bg-cyan-200 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 dark:bg-cyan-800 dark:text-cyan-100 dark:border-cyan-700 dark:hover:bg-cyan-700 dark:focus:ring-cyan-600 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <em class="mr-2 icon ni ni-reload"></em>
                                Refusionner les données
                            </button>
                        @endif

                        <!-- Bouton de réinitialisation présent seulement si nécessaire -->
                        @if($showResetButton)
                            @can('resultats.reset-fusion')
                                <button
                                    wire:click="confirmResetFusion"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-red-700 transition-colors duration-200 bg-red-100 border border-red-200 rounded-lg shadow-sm hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:bg-red-800 dark:text-red-100 dark:border-red-700 dark:hover:bg-red-700 dark:focus:ring-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <em class="mr-2 icon ni ni-trash"></em>
                                    Réinitialiser
                                </button>
                            @endcan
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Publication des résultats (Validation ET Publication fusionnées) -->
    <!-- Cette étape combine maintenant validation et publication selon la nouvelle logique -->
    <div class="p-5 border rounded-lg {{ $statut === 'publie' ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : ($statut === 'annule' ? 'bg-red-50 border-red-200 dark:bg-red-900/10 dark:border-red-800' : 'bg-white border-gray-200 dark:bg-gray-800 dark:border-gray-700') }}">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($statut === 'initial' || $statut === 'verification')
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                        <span>3</span>
                    </div>
                @elseif($statut === 'fusion')
                    @if($etapeFusion >= 3)
                        <!-- Fusion terminée - prêt pour publication -->
                        <div class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-primary-500 dark:bg-primary-600">
                            <span>3</span>
                        </div>
                    @else
                        <!-- Fusion en cours - pas encore prêt -->
                        <div class="flex items-center justify-center w-8 h-8 text-white bg-gray-400 rounded-full dark:bg-gray-600">
                            <span>3</span>
                        </div>
                    @endif
                @elseif($statut === 'annule')
                    <div class="flex items-center justify-center w-8 h-8 text-white bg-red-500 rounded-full dark:bg-red-600">
                        <em class="icon ni ni-cross"></em>
                    </div>
                @else
                    <!-- État publié -->
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
                    Valide automatiquement et publie les résultats finaux pour consultation par les étudiants.
                </p>

                <!-- 🎯 INTERFACE ADAPTATIVE BASÉE SUR LES ATTRIBUTS MÉTIER -->
                <!-- Utilise maintenant les attributs configurables au lieu de noms en dur -->
                @if($statut === 'fusion' && $etapeFusion >= 1)
                <div class="mt-3">
                    <!-- Détection automatique du contexte via la propriété calculée -->
                    @php
                        $contexte = $this->contexteExamen;
                        $requiresDeliberation = $contexte['requires_deliberation'] ?? false;
                        $isConcours = $contexte['is_concours'] ?? false;
                        $hasRattrapage = $contexte['has_rattrapage'] ?? false;
                        $sessionType = $contexte['session_type'] ?? 'N/A';
                        $niveauNom = $contexte['niveau']->nom ?? 'N/A';
                        $anneeUniv = $contexte['annee_universitaire'] ?? 'N/A';
                    @endphp

                    <div class="flex flex-wrap gap-2">
                        <!-- Bouton pour voir les résultats à vérifier - disponible dès étape 1 -->
                        <a href="#"
                            wire:click="switchTab('rapport-stats')"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                            <em class="icon ni ni-eye mr-1.5"></em>
                            Vérifier les résultats
                        </a>

                        <!-- Bouton de publication - texte et couleur adaptatifs selon les attributs métier -->
                        @if($etapeFusion >= 3)
                        <button
                            wire:click="confirmValidation"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white {{ $requiresDeliberation ? 'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500' : ($isConcours ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500') }} border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 dark:{{ $requiresDeliberation ? 'bg-purple-700 hover:bg-purple-600' : ($isConcours ? 'bg-blue-700 hover:bg-blue-600' : 'bg-green-700 hover:bg-green-600') }}">
                            @if($requiresDeliberation)
                                <em class="icon ni ni-users mr-1.5"></em>
                                Délibérer et publier
                            @elseif($isConcours)
                                <em class="icon ni ni-target mr-1.5"></em>
                                Classer et publier
                            @else
                                <em class="icon ni ni-check mr-1.5"></em>
                                Valider et publier
                            @endif
                        </button>
                        @endif
                    </div>

                    <!-- Messages informatifs adaptatifs selon les attributs métier -->
                    @if($etapeFusion >= 3)
                        @if($requiresDeliberation)
                        <!-- Message pour délibération (2ème session, niveaux avec rattrapage) -->
                        <div class="p-3 mt-3 border border-purple-200 rounded-md bg-purple-50 dark:bg-purple-900/20 dark:border-purple-700">
                            <p class="text-sm text-purple-800 dark:text-purple-200">
                                <em class="mr-1 icon ni ni-users"></em>
                                <strong>Délibération requise :</strong> Cette action déclenchera automatiquement une délibération pour cette session de rattrapage, analysera les performances des étudiants selon les critères de validation des crédits UE, puis publiera les décisions finales.
                            </p>
                            <div class="mt-2 text-xs text-purple-700 dark:text-purple-300">
                                📋 <strong>Processus :</strong> Calcul automatique des moyennes UE → Analyse des crédits validés → Proposition de décisions → Application des décisions → Publication immédiate
                            </div>
                        </div>
                        @elseif($isConcours)
                        <!-- Message pour concours (PACES, etc.) -->
                        <div class="p-3 mt-3 border border-blue-200 rounded-md bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <em class="mr-1 icon ni ni-target"></em>
                                <strong>Concours détecté :</strong> Cette action effectuera le classement automatique selon les notes obtenues et publiera immédiatement les résultats. Aucune délibération n'est prévue pour ce type d'évaluation.
                            </p>
                            <div class="mt-2 text-xs text-blue-700 dark:text-blue-300">
                                🏆 <strong>Processus de concours :</strong> Calcul des moyennes → Classement selon les notes → Publication directe du classement
                            </div>
                        </div>
                        @else
                        <!-- Message pour publication directe (1ère session standard) -->
                        <div class="p-3 mt-3 border border-green-200 rounded-md bg-green-50 dark:bg-green-900/20 dark:border-green-700">
                            <p class="text-sm text-green-800 dark:text-green-200">
                                <em class="mr-1 icon ni ni-check"></em>
                                <strong>Publication directe :</strong> Cette action analysera automatiquement les performances, déterminera les décisions selon les critères de validation des crédits UE (admis/rattrapage), et publiera immédiatement les résultats.
                            </p>
                            <div class="mt-2 text-xs text-green-700 dark:text-green-300">
                                ⚡ <strong>Processus simplifié :</strong> Calcul des moyennes UE → Validation automatique des crédits → Décision admis/rattrapage → Publication immédiate
                            </div>
                        </div>
                        @endif
                    @else
                    <!-- Message pendant la fusion en cours -->
                    <div class="p-3 mt-3 border border-yellow-200 rounded-md bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            <em class="mr-1 icon ni ni-alert"></em>
                            <strong>Fusion en cours (Étape {{ $etapeFusion }}/3) :</strong> Vous pouvez vérifier les résultats partiels, mais
                            @if($requiresDeliberation)
                                la délibération sera disponible après finalisation complète.
                            @elseif($isConcours)
                                le classement sera disponible après finalisation complète.
                            @else
                                la publication sera disponible après finalisation complète.
                            @endif
                        </p>
                    </div>
                    @endif

                    <!-- Informations détaillées sur le contexte de l'examen -->
                    @if($contexte)
                    <div class="p-2 mt-2 text-xs border border-gray-200 rounded bg-gray-50 dark:bg-gray-800 dark:border-gray-600">
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                            <span>
                                📚 <strong>Contexte :</strong>
                                {{ $niveauNom }} - {{ $sessionType }} ({{ $anneeUniv }})
                                @if($hasRattrapage)
                                    • Rattrapage autorisé
                                @endif
                                @if($isConcours)
                                    • Mode concours
                                @endif
                            </span>
                            @if($requiresDeliberation)
                                <span class="px-2 py-1 text-xs text-purple-700 bg-purple-100 rounded dark:bg-purple-900 dark:text-purple-300">
                                    Délibération requise
                                </span>
                            @elseif($isConcours)
                                <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded dark:bg-blue-900 dark:text-blue-300">
                                    Concours - Classement
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded dark:bg-green-900 dark:text-green-300">
                                    Publication directe
                                </span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Affichage pour résultats publiés -->
                @if($statut === 'publie')
                <div class="flex mt-3 space-x-3">
                    <a href="{{ route('resultats.finale') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700 dark:hover:bg-green-600">
                        <em class="icon ni ni-eye mr-1.5"></em>
                        Consulter les résultats
                    </a>
                    <button
                        wire:click="confirmAnnulation"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-100 border border-red-200 rounded-md shadow-sm hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-800 dark:text-red-100 dark:border-red-700 dark:hover:bg-red-700"
                    >
                        <em class="icon ni ni-cross mr-1.5"></em>
                        Annuler la publication
                    </button>
                </div>
                @endif

                <!-- Affichage pour résultats annulés -->
                @if($statut === 'annule')
                <div class="flex mt-3 space-x-3">
                    <button
                        wire:click="confirmValidation"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700 dark:hover:bg-green-600">
                        <em class="icon ni ni-check mr-1.5"></em>
                        Republier les résultats
                    </button>
                    <button
                        wire:click="confirmRevenirValidation"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 border border-blue-200 rounded-md shadow-sm hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-800 dark:text-blue-100 dark:border-blue-700 dark:hover:bg-blue-700"
                    >
                        <em class="icon ni ni-arrow-left mr-1.5"></em>
                        Réactiver les résultats
                    </button>
                </div>

                <!-- Message d'explication pour les résultats annulés -->
                <div class="p-3 mt-3 border rounded-md bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-700">
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        <em class="mr-1 icon ni ni-alert"></em>
                        Les résultats annulés peuvent être republiés directement ou réactivés pour une nouvelle vérification.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ESPACE POUR UNE QUATRIÈME CARTE OPTIONNELLE -->
    <!-- Actions complémentaires et statistiques -->
    <div class="p-5 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800/50 dark:border-gray-700">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <div class="flex items-center justify-center w-8 h-8 text-gray-500 bg-gray-200 rounded-full dark:bg-gray-700 dark:text-gray-400">
                    <em class="icon ni ni-bar-chart"></em>
                </div>
            </div>
            <div class="ml-4">
                <h4 class="text-base font-medium text-gray-800 dark:text-gray-200">Actions complémentaires</h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Export et statistiques des résultats.</p>

                <div class="mt-3">
                    <button
                        wire:click="exporterResultats"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
                    >
                        <em class="mr-2 icon ni ni-download"></em>
                        Exporter les données
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
