{{-- vue blade principale --}}
<div class="container px-4 py-6 mx-auto">
    {{-- ========================================
         EN-TÊTE AVEC TITRE ET ACTIONS
    ======================================== --}}
    <header class="sticky top-0 z-10 px-5 py-4 mb-6 shadow-sm">
        <div class="flex items-center justify-between">
            {{-- Titre principal --}}
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-medium text-gray-800 dark:text-gray-100">
                    Fusion et vérification des résultats d'examens
                </h2>
            </div>

            {{-- Actions de l'en-tête --}}
            <div class="flex items-center space-x-2">
                {{-- Bouton Voir les Résultats --}}
                <a href="#" 
                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Voir les Résultats
                </a>

                {{-- Bouton Diagnostic (rattrapage uniquement) --}}
                @if($sessionActive && $sessionActive->type === 'Rattrapage' && $examen)
                    <button wire:click="diagnosticEligiblesRattrapage" 
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-orange-700 bg-orange-50 border border-orange-300 rounded-md shadow-sm hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:bg-orange-900 dark:text-orange-200 dark:border-orange-600 dark:hover:bg-orange-800">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Diagnostic
                    </button>
                @endif
            </div>
        </div>
    </header>

    {{-- ========================================
         INCLUSION SESSION ACTIVE
    ======================================== --}}
    @include('livewire.fusion.partials.session-active')

    {{-- ========================================
         MESSAGES INFORMATIFS RATTRAPAGE
    ======================================== --}}
    @if($sessionActive && $sessionActive->type === 'Rattrapage' && $examen)
        @php
            $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
            $compteursDonnees = $this->getCompteursDonneesSession();
        @endphp

        @if($etudiantsEligibles->count() > 0)
            {{-- Message avec étudiants éligibles --}}
            <div class="p-4 mb-4 border border-blue-200 rounded-lg bg-gradient-to-r from-blue-50 to-amber-50 dark:from-blue-900/20 dark:to-blue-900/20 dark:border-blue-800">
                <div class="flex items-start justify-between">
                    {{-- Contenu du message --}}
                    <div class="flex items-start space-x-3">
                        {{-- Icône d'information --}}
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>

                        {{-- Texte du message --}}
                        <div>
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                Session de rattrapage active
                            </h3>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                <strong>{{ $etudiantsEligibles->count() }} étudiant(s) éligible(s)</strong> détecté(s) pour cette session.
                                @if($compteursDonnees['manchettes'] == 0)
                                    Les données de rattrapage ne sont pas encore initialisées.
                                @else
                                    <strong>{{ $compteursDonnees['manchettes'] }} manchette(s)</strong> et
                                    <strong>{{ $compteursDonnees['copies'] }} copie(s)</strong> en cours de traitement.
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Bouton d'initialisation (si nécessaire) --}}
                    @if($compteursDonnees['manchettes'] == 0)
                        <button wire:click="initialiserDonneesRattrapage"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-orange-800 bg-orange-100 border border-orange-300 rounded-md hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:bg-orange-800 dark:text-orange-100 dark:border-orange-700 dark:hover:bg-orange-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Initialiser les données
                        </button>
                    @endif
                </div>
            </div>
        @elseif($niveau_id && $parcours_id)
            {{-- Message aucun étudiant éligible --}}
            <div class="p-4 mb-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            Aucun étudiant éligible au rattrapage
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Tous les étudiants ont réussi la session normale ou n'ont pas de résultats publiés.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ========================================
         CONTENU PRINCIPAL
    ======================================== --}}
    @if($examen)
        {{-- Système d'onglets avec contenu --}}
        <div class="mb-6 overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
            
            {{-- Barre de progression avec indicateur --}}
            <div class="relative w-full bg-gray-200 dark:bg-gray-700">
                {{-- Barre de progression principale --}}
                <div class="h-2 transition-all duration-300 ease-in-out
                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                        bg-gradient-to-r from-orange-500 to-amber-500 dark:from-orange-400 dark:to-amber-400
                    @else
                        bg-primary-600 dark:bg-primary-500
                    @endif"
                    style="width: {{ $etapeProgress }}%">
                </div>

                {{-- Indicateur de type de session --}}
                <div class="absolute top-0 right-0 h-2 w-8
                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                        bg-orange-300 dark:bg-orange-600
                    @else
                        bg-primary-300 dark:bg-primary-700
                    @endif">
                </div>
            </div>

            {{-- Navigation des onglets --}}
            <nav class="flex border-b border-gray-200 dark:border-gray-700" id="tab-navigation">
                {{-- Onglet Processus de fusion --}}
                <button id="tab-process"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200 focus:outline-none
                        {{ $activeTab === 'process' 
                            ? 'border-primary-500 text-primary-600 dark:border-primary-400 dark:text-primary-300' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600' }}"
                        wire:click="switchTab('process')">
                    <div class="flex items-center space-x-2">
                        <span>Processus de fusion</span>
                    </div>
                </button>

                {{-- Onglet Rapport de la fusion --}}
                <button id="tab-rapport-stats"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200 focus:outline-none
                        {{ $activeTab === 'rapport-stats' 
                            ? 'border-primary-500 text-primary-600 dark:border-primary-400 dark:text-primary-300' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600' }}"
                        wire:click="switchTab('rapport-stats')">
                    Rapport de la fusion
                </button>
            </nav>

            {{-- Contenu des onglets --}}
            <div class="p-4 sm:p-6">
                @include('livewire.fusion.partials.tab-process')
                @include('livewire.fusion.partials.tab-rapport-stats')
            </div>
        </div>
    @else
        {{-- ========================================
             ÉTAT VIDE - AUCUN EXAMEN SÉLECTIONNÉ
        ======================================== --}}
        <div class="py-12 text-center">
            {{-- Icône principale --}}
            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            
            {{-- Titre principal --}}
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                Aucun examen sélectionné
            </h3>
            
            {{-- Description selon le contexte --}}
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($sessionActive)
                    Sélectionnez un niveau et un parcours pour commencer le processus de fusion
                    @if($sessionActive->type === 'Rattrapage')
                        des résultats de rattrapage
                    @else
                        des résultats de session normale
                    @endif.
                @else
                    Configurez d'abord une session active dans les paramètres du système.
                @endif
            </p>
            
            {{-- Message additionnel si applicable --}}
            @if($sessionActive && $niveau_id && $parcours_id && !$examen)
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                    @if($sessionActive->type === 'Rattrapage')
                        Aucun examen avec des étudiants éligibles au rattrapage trouvé pour ce niveau et parcours.
                    @else
                        Aucun examen avec des données trouvé pour ce niveau et parcours.
                    @endif
                </p>
            @endif
        </div>
    @endif
</div>