<!-- Onglet: Rapport et Statistiques -->
<div id="content-rapport-stats" class="tab-content" x-show="$wire.activeTab === 'rapport-stats'" style="{{ $activeTab !== 'rapport-stats' ? 'display: none;' : '' }}">
    <!-- En-tête adapté selon la session -->
    @if($sessionActive && $sessionActive->type === 'Rattrapage')
        <div class="p-4 mb-6 border border-orange-200 rounded-lg bg-orange-50 dark:bg-orange-900/20 dark:border-orange-700">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-medium text-orange-900 dark:text-orange-200">
                        Rapport et Statistiques - Session de Rattrapage
                    </h3>
                    <p class="text-sm text-orange-700 dark:text-orange-300">
                        Suivi complet des étudiants de la 1ère session vers le rattrapage
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistiques principales -->
    @if($resultatsStats && isset($resultatsStats['totalMatieres']) && $resultatsStats['totalMatieres'] > 0)

        @if($sessionActive->type === 'Rattrapage')
            <!-- INTERFACE RATTRAPAGE : Données depuis le composant -->

            @php
                // Récupérer toutes les données depuis les méthodes du composant
                $statistiquesCompletes = $statistiquesCompletes ?? [];
                $compteursDonnees = $compteursDonnees ?? [];
                $etudiantsEligibles = $etudiantsEligibles ?? collect();

                // Valeurs par défaut si pas de données
                $totalInscrits = $statistiquesCompletes['total_inscrits'] ?? 0;
                $admisPremiereSession = $statistiquesCompletes['admis_premiere_session'] ?? 0;
                $eligiblesRattrapage = $statistiquesCompletes['eligibles_rattrapage'] ?? 0;
                $participantsRattrapage = $statistiquesCompletes['participants_rattrapage'] ?? 0;

                // Calculs simples
                $tauxAdmissionPremiere = $totalInscrits > 0 ? round(($admisPremiereSession / $totalInscrits) * 100, 1) : 0;
                $tauxEligibilite = $totalInscrits > 0 ? round(($eligiblesRattrapage / $totalInscrits) * 100, 1) : 0;
                $tauxParticipationRattrapage = $eligiblesRattrapage > 0 ? round(($participantsRattrapage / $eligiblesRattrapage) * 100, 1) : 0;
                $coherenceOK = ($admisPremiereSession + $eligiblesRattrapage) === $totalInscrits;
            @endphp

            <!-- Vue d'ensemble du parcours -->
            <div class="p-4 mb-6 border border-gray-200 rounded-lg bg-gradient-to-r from-blue-50 to-orange-50 dark:from-blue-900/20 dark:to-orange-900/20 dark:border-gray-700">
                <h4 class="mb-3 text-base font-medium text-gray-900 dark:text-gray-100">
                    📊 Parcours des étudiants : 1ère session → Rattrapage
                </h4>
                <div class="flex items-center justify-center space-x-8 text-sm">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $totalInscrits }}</div>
                        <div class="text-blue-700">Total inscrits</div>
                    </div>
                    <div class="text-gray-400">→</div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $admisPremiereSession }}</div>
                        <div class="text-green-700">Admis 1ère</div>
                        <div class="text-xs text-green-600">({{ $tauxAdmissionPremiere }}%)</div>
                    </div>
                    <div class="text-gray-400">+</div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600">{{ $eligiblesRattrapage }}</div>
                        <div class="text-orange-700">Éligibles rattrapage</div>
                        <div class="text-xs text-orange-600">({{ $tauxEligibilite }}%)</div>
                    </div>
                    <div class="text-gray-400">→</div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $participantsRattrapage }}</div>
                        <div class="text-purple-700">Participants</div>
                        <div class="text-xs text-purple-600">({{ $tauxParticipationRattrapage }}%)</div>
                    </div>
                </div>

                <!-- Vérification de cohérence simple -->
                @if($coherenceOK)
                    <div class="p-2 mt-3 text-xs text-green-800 bg-green-100 border border-green-300 rounded">
                        ✅ Cohérence vérifiée : {{ $admisPremiereSession }} + {{ $eligiblesRattrapage }} = {{ $totalInscrits }}
                    </div>
                @else
                    <div class="p-2 mt-3 text-xs text-red-800 bg-red-100 border border-red-300 rounded">
                        ⚠️ Incohérence : {{ $admisPremiereSession }} + {{ $eligiblesRattrapage }} = {{ $admisPremiereSession + $eligiblesRattrapage }} ≠ {{ $totalInscrits }} (total)
                    </div>
                @endif
            </div>

            <!-- Statistiques détaillées du rattrapage -->
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">

                <!-- Total inscrits -->
                <div class="p-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/10 dark:border-blue-800">
                    <div class="text-sm font-medium text-blue-800 dark:text-blue-300">Total inscrits</div>
                    <div class="mt-1 text-3xl font-semibold text-blue-600 dark:text-blue-200">
                        {{ $totalInscrits }}
                    </div>
                    <div class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                        niveau complet
                    </div>
                </div>

                <!-- Éligibles rattrapage -->
                <div class="p-4 border border-orange-200 rounded-lg bg-orange-50 dark:bg-orange-900/10 dark:border-orange-800">
                    <div class="text-sm font-medium text-orange-800 dark:text-orange-300">Éligibles rattrapage</div>
                    <div class="mt-1 text-3xl font-semibold text-orange-600 dark:text-orange-200">
                        {{ $eligiblesRattrapage }}
                    </div>
                    <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                        {{ $tauxEligibilite }}% du total
                    </div>
                </div>

                <!-- Participants effectifs -->
                <div class="p-4 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/10 dark:border-green-800">
                    <div class="text-sm font-medium text-green-800 dark:text-green-300">Participants</div>
                    <div class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-200">
                        {{ $participantsRattrapage }}
                    </div>
                    <div class="mt-1 text-xs text-green-600 dark:text-green-400">
                        sur {{ $eligiblesRattrapage }} éligibles
                    </div>
                </div>

                <!-- Taux de participation -->
                <div class="p-4 border border-purple-200 rounded-lg bg-purple-50 dark:bg-purple-900/10 dark:border-purple-800">
                    <div class="text-sm font-medium text-purple-800 dark:text-purple-300">Taux participation</div>
                    <div class="mt-1 text-3xl font-semibold
                        @if($tauxParticipationRattrapage == 100) text-green-600 dark:text-green-400
                        @elseif($tauxParticipationRattrapage >= 80) text-blue-600 dark:text-blue-400
                        @elseif($tauxParticipationRattrapage >= 50) text-orange-600 dark:text-orange-400
                        @else text-red-600 dark:text-red-400
                        @endif">
                        {{ $tauxParticipationRattrapage }}%
                    </div>
                    <div class="mt-1 text-xs text-purple-600 dark:text-purple-400">
                        {{ $participantsRattrapage }}/{{ $eligiblesRattrapage }}
                    </div>
                </div>
            </div>

            <!-- Détails opérationnels -->
            <div class="p-4 mb-6 border border-orange-200 rounded-lg bg-orange-50 dark:bg-orange-900/30 dark:border-orange-700">
                <h4 class="mb-3 text-base font-medium text-orange-900 dark:text-orange-200">
                    📋 Données opérationnelles du rattrapage
                </h4>

                <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-300">
                            {{ $compteursDonnees['manchettes'] ?? 0 }}
                        </div>
                        <div class="text-orange-700 dark:text-orange-400">Manchettes créées</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-300">
                            {{ $compteursDonnees['copies'] ?? 0 }}
                        </div>
                        <div class="text-orange-700 dark:text-orange-400">Copies corrigées</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-300">
                            {{ $resultatsStats['totalMatieres'] }}
                        </div>
                        <div class="text-orange-700 dark:text-orange-400">Matières</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-300">
                            {{ $compteursDonnees['etudiants'] ?? 0 }}
                        </div>
                        <div class="text-orange-700 dark:text-orange-400">Étudiants actifs</div>
                    </div>
                </div>
            </div>

            <!-- Alertes simples -->
            @if($participantsRattrapage > $eligiblesRattrapage)
                <div class="p-3 mb-4 bg-red-100 border border-red-300 rounded dark:bg-red-900/30 dark:border-red-700">
                    <div class="flex items-center text-red-800 dark:text-red-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium">Erreur logique : Plus de participants que d'éligibles</span>
                    </div>
                </div>
            @elseif($tauxParticipationRattrapage == 100)
                <div class="p-3 mb-4 bg-green-100 border border-green-300 rounded dark:bg-green-900/30 dark:border-green-700">
                    <div class="flex items-center text-green-800 dark:text-green-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm font-medium">Participation complète au rattrapage</span>
                    </div>
                </div>
            @endif

        @else
            <!-- INTERFACE SESSION NORMALE SIMPLIFIÉE -->

            @php
                // Données depuis le composant pour session normale
                $totalInscrits = $this->getTotalEtudiantsInscrits();
                $participantsNormale = $resultatsStats['etudiants'] ?? 0;
                $tauxParticipationNormale = $totalInscrits > 0 ? round(($participantsNormale / $totalInscrits) * 100, 1) : 0;

                // Vérifier si on a des résultats publiés via les statistiques disponibles
                $hasPublishedResults = isset($statistiquesCompletes) && !empty($statistiquesCompletes);
            @endphp

            @if($hasPublishedResults)
                <!-- Vue d'ensemble pour session normale avec résultats publiés -->
                <div class="p-4 mb-6 border border-gray-200 rounded-lg bg-gradient-to-r from-blue-50 to-green-50 dark:from-blue-900/20 dark:to-green-900/20 dark:border-gray-700">
                    <h4 class="mb-3 text-base font-medium text-gray-900 dark:text-gray-100">
                        📊 Répartition des résultats - Session Normale
                    </h4>
                    <div class="flex items-center justify-center space-x-8 text-sm">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $statistiquesCompletes['total_inscrits'] ?? 0 }}</div>
                            <div class="text-blue-700">Total inscrits</div>
                        </div>
                        <div class="text-gray-400">→</div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $statistiquesCompletes['admis_premiere_session'] ?? 0 }}</div>
                            <div class="text-green-700">Admis</div>
                        </div>
                        <div class="text-gray-400">+</div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-600">{{ $statistiquesCompletes['eligibles_rattrapage'] ?? 0 }}</div>
                            <div class="text-orange-700">Rattrapage</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Statistiques détaillées session normale -->
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">

                <!-- Total inscrits -->
                <div class="p-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/10 dark:border-blue-800">
                    <div class="text-sm font-medium text-blue-800 dark:text-blue-300">Total inscrits</div>
                    <div class="mt-1 text-3xl font-semibold text-blue-600 dark:text-blue-200">
                        {{ $totalInscrits }}
                    </div>
                    <div class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                        niveau complet
                    </div>
                </div>

                @if($hasPublishedResults)
                    <!-- Admis en session normale -->
                    <div class="p-4 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/10 dark:border-green-800">
                        <div class="text-sm font-medium text-green-800 dark:text-green-300">Admis définitivement</div>
                        <div class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-200">
                            {{ $statistiquesCompletes['admis_premiere_session'] ?? 0 }}
                        </div>
                        <div class="mt-1 text-xs text-green-600 dark:text-green-400">
                            en 1ère session
                        </div>
                    </div>

                    <!-- Éligibles au rattrapage -->
                    <div class="p-4 border border-orange-200 rounded-lg bg-orange-50 dark:bg-orange-900/10 dark:border-orange-800">
                        <div class="text-sm font-medium text-orange-800 dark:text-orange-300">Éligibles rattrapage</div>
                        <div class="mt-1 text-3xl font-semibold text-orange-600 dark:text-orange-200">
                            {{ $statistiquesCompletes['eligibles_rattrapage'] ?? 0 }}
                        </div>
                        <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                            besoin 2ème chance
                        </div>
                    </div>
                @else
                    <!-- Participants (si pas encore de résultats publiés) -->
                    <div class="p-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-900/10 dark:border-gray-800">
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-300">Participants</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-600 dark:text-gray-200">
                            {{ $participantsNormale }}
                        </div>
                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                            avec résultats saisis
                        </div>
                    </div>

                    <!-- Taux de participation -->
                    <div class="p-4 border border-purple-200 rounded-lg bg-purple-50 dark:bg-purple-900/10 dark:border-purple-800">
                        <div class="text-sm font-medium text-purple-800 dark:text-purple-300">Taux participation</div>
                        <div class="mt-1 text-3xl font-semibold text-purple-600 dark:text-purple-200">
                            {{ $tauxParticipationNormale }}%
                        </div>
                        <div class="mt-1 text-xs text-purple-600 dark:text-purple-400">
                            {{ $participantsNormale }}/{{ $totalInscrits }}
                        </div>
                    </div>
                @endif

                <!-- Matières traitées -->
                <div class="p-4 border border-indigo-200 rounded-lg bg-indigo-50 dark:bg-indigo-900/10 dark:border-indigo-800">
                    <div class="text-sm font-medium text-indigo-800 dark:text-indigo-300">Matières</div>
                    <div class="mt-1 text-3xl font-semibold text-indigo-600 dark:text-indigo-200">
                        {{ $resultatsStats['totalMatieres'] }}
                    </div>
                    <div class="mt-1 text-xs text-indigo-600 dark:text-indigo-400">
                        @if($hasPublishedResults)
                            avec résultats publiés
                        @else
                            fusionnées
                        @endif
                    </div>
                </div>
            </div>
        @endif

    @else
        <!-- Message d'absence de données -->
        <div class="flex flex-col items-center justify-center p-8 mb-6 border border-gray-300 border-dashed rounded-lg dark:border-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>

            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                    Session de rattrapage non initialisée
                @else
                    Aucune donnée de fusion
                @endif
            </h3>

            <p class="max-w-md mt-2 text-sm text-center text-gray-600 dark:text-gray-400">
                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                    Pour commencer le rattrapage, vérifiez d'abord les résultats de la 1ère session.
                @else
                    Effectuez d'abord la fusion des données pour voir les statistiques.
                @endif
            </p>
        </div>
    @endif

    <!-- Rapport de cohérence -->
    @if(!empty($rapportCoherence))
        <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-medium text-gray-800 dark:text-gray-200">
                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                        Rapport de cohérence des données de rattrapage
                    @else
                        Rapport de cohérence des données
                    @endif
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                        Vérification de la correspondance entre les manchettes d'anonymat et les copies corrigées pour les étudiants en rattrapage.
                    @else
                        Vérification de la correspondance entre les manchettes d'anonymat et les copies corrigées pour chaque matière.
                    @endif
                </p>
            </div>
            <div class="p-4 sm:p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    Matière
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Manchettes rattrapage
                                    @else
                                        Manchettes
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Copies rattrapage
                                    @else
                                        Copies
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    Notes
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    État
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    Problèmes détectés
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach($rapportCoherence as $rapport)
                                <tr class="{{ $rapport['complet'] ? 'hover:bg-gray-50 dark:hover:bg-gray-700' : ($sessionActive && $sessionActive->type === 'Rattrapage' ? 'bg-orange-50 dark:bg-orange-900/10 hover:bg-orange-100 dark:hover:bg-orange-900/20' : 'bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20') }}">
                                    <!-- Nom de la matière -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $rapport['ec_nom'] ?? 'Matière inconnue' }}
                                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                    R
                                                </span>
                                            @endif
                                        </div>
                                        @if(isset($rapport['ec_abr']) && $rapport['ec_abr'] !== 'N/A')
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $rapport['ec_abr'] }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Manchettes -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            <span class="font-medium">{{ $rapport['manchettes_count'] ?? 0 }}</span>
                                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                @php
                                                    $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
                                                    $totalEligibles = $etudiantsEligibles->count();
                                                @endphp
                                                <span class="text-gray-500">/ {{ $totalEligibles }}</span>
                                            @else
                                                <span class="text-gray-500">/ {{ $rapport['total_etudiants'] ?? 0 }}</span>
                                            @endif
                                        </div>
                                        @if(isset($rapport['manchettes_count']))
                                            @php
                                                if($sessionActive && $sessionActive->type === 'Rattrapage') {
                                                    $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
                                                    $totalRef = $etudiantsEligibles->count();
                                                } else {
                                                    $totalRef = $rapport['total_etudiants'] ?? 0;
                                                }
                                                $pourcentageManchettes = $totalRef > 0 ? round(($rapport['manchettes_count'] / $totalRef) * 100, 1) : 0;
                                            @endphp
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $pourcentageManchettes }}%
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Copies -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            <span class="font-medium">{{ $rapport['copies_count'] ?? 0 }}</span>
                                            @if(isset($rapport['codes_count']))
                                                <span class="text-gray-500">/ {{ $rapport['codes_count'] }}</span>
                                            @endif
                                        </div>
                                        @if(isset($rapport['copies_count']) && isset($rapport['codes_count']))
                                            @php
                                                $pourcentageCopies = $rapport['codes_count'] > 0 ? round(($rapport['copies_count'] / $rapport['codes_count']) * 100, 1) : 0;
                                            @endphp
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $pourcentageCopies }}%
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Notes attribuées -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            <span class="font-medium">{{ $rapport['etudiants_avec_note'] ?? 0 }}</span>
                                            <span class="text-gray-500">/ {{ $rapport['copies_count'] ?? 0 }}</span>
                                        </div>
                                        @if(isset($rapport['etudiants_avec_note']) && isset($rapport['copies_count']))
                                            @php
                                                $pourcentageNotes = $rapport['copies_count'] > 0 ? round(($rapport['etudiants_avec_note'] / $rapport['copies_count']) * 100, 1) : 0;
                                            @endphp
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $pourcentageNotes }}%
                                            </div>
                                        @endif
                                    </td>

                                    <!-- État de cohérence -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($rapport['complet'] ?? false)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                    Complet (R)
                                                @else
                                                    Complet
                                                @endif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                    bg-orange-100 text-orange-800 dark:bg-orange-800/30 dark:text-orange-200
                                                @else
                                                    bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-200
                                                @endif">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                    Incomplet (R)
                                                @else
                                                    Incomplet
                                                @endif
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Problèmes détectés -->
                                    <td class="px-6 py-4">
                                        <div class="max-w-xs text-sm text-gray-500 dark:text-gray-400">
                                            @if($rapport['complet'] ?? false)
                                                <div class="flex items-center text-green-600 dark:text-green-400">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="text-xs">Aucun problème détecté</span>
                                                </div>
                                            @else
                                                <!-- Codes sans manchettes -->
                                                @if(isset($rapport['codes_sans_manchettes']['count']) && $rapport['codes_sans_manchettes']['count'] > 0)
                                                    <div class="mb-2
                                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                            text-orange-600 dark:text-orange-400
                                                        @else
                                                            text-red-600 dark:text-red-400
                                                        @endif">
                                                        <div class="mb-1 text-xs font-medium">
                                                            {{ $rapport['codes_sans_manchettes']['count'] }} code(s) sans manchette :
                                                        </div>
                                                        <div class="px-2 py-1 font-mono text-xs rounded
                                                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                                bg-orange-50 dark:bg-orange-900/20
                                                            @else
                                                                bg-red-50 dark:bg-red-900/20
                                                            @endif">
                                                            {{ implode(', ', array_slice($rapport['codes_sans_manchettes']['codes'] ?? [], 0, 3)) }}
                                                            @if(count($rapport['codes_sans_manchettes']['codes'] ?? []) > 3)
                                                                <span class="
                                                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                                        text-orange-400
                                                                    @else
                                                                        text-red-400
                                                                    @endif">... et {{ count($rapport['codes_sans_manchettes']['codes']) - 3 }} autres</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Codes sans copies -->
                                                @if(isset($rapport['codes_sans_copies']['count']) && $rapport['codes_sans_copies']['count'] > 0)
                                                    <div class="mb-2
                                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                            text-orange-600 dark:text-orange-400
                                                        @else
                                                            text-red-600 dark:text-red-400
                                                        @endif">
                                                        <div class="mb-1 text-xs font-medium">
                                                            {{ $rapport['codes_sans_copies']['count'] }} code(s) sans copie :
                                                        </div>
                                                        <div class="px-2 py-1 font-mono text-xs rounded
                                                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                                bg-orange-50 dark:bg-orange-900/20
                                                            @else
                                                                bg-red-50 dark:bg-red-900/20
                                                            @endif">
                                                            {{ implode(', ', array_slice($rapport['codes_sans_copies']['codes'] ?? [], 0, 3)) }}
                                                            @if(count($rapport['codes_sans_copies']['codes'] ?? []) > 3)
                                                                <span class="
                                                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                                        text-orange-400
                                                                    @else
                                                                        text-red-400
                                                                    @endif">... et {{ count($rapport['codes_sans_copies']['codes']) - 3 }} autres</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Étudiants sans manchette -->
                                                @if(isset($rapport['etudiants_sans_manchette']) && $rapport['etudiants_sans_manchette'] > 0)
                                                    <div class="mb-2 text-orange-600 dark:text-orange-400">
                                                        <div class="text-xs">
                                                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                                {{ $rapport['etudiants_sans_manchette'] }} étudiant(s) éligible(s) sans manchette pour cette matière
                                                            @else
                                                                {{ $rapport['etudiants_sans_manchette'] }} étudiant(s) sans manchette pour cette matière
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Message générique si aucun problème spécifique n'est identifié -->
                                                @if(
                                                    (!isset($rapport['codes_sans_manchettes']['count']) || $rapport['codes_sans_manchettes']['count'] === 0) &&
                                                    (!isset($rapport['codes_sans_copies']['count']) || $rapport['codes_sans_copies']['count'] === 0) &&
                                                    (!isset($rapport['etudiants_sans_manchette']) || $rapport['etudiants_sans_manchette'] === 0)
                                                )
                                                    <div class="text-yellow-600 dark:text-yellow-400">
                                                        <div class="text-xs">
                                                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                                Discordance rattrapage : manchettes ({{ $rapport['manchettes_count'] ?? 0 }}) et copies ({{ $rapport['copies_count'] ?? 0 }})
                                                            @else
                                                                Discordance entre les données : manchettes ({{ $rapport['manchettes_count'] ?? 0 }}) et copies ({{ $rapport['copies_count'] ?? 0 }})
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Résumé du rapport -->
                @if(count($rapportCoherence) > 0)
                    @php
                        $totalMatieres = count($rapportCoherence);
                        $matieresCompletes = collect($rapportCoherence)->where('complet', true)->count();
                        $matieresIncompletes = $totalMatieres - $matieresCompletes;
                        $pourcentageCompletude = $totalMatieres > 0 ? round(($matieresCompletes / $totalMatieres) * 100, 1) : 0;
                    @endphp
                    <div class="p-4 mt-6 rounded-lg
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            bg-orange-50 dark:bg-orange-700/30
                        @else
                            bg-gray-50 dark:bg-gray-700
                        @endif">
                        <h4 class="mb-2 text-sm font-medium
                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                text-orange-800 dark:text-orange-200
                            @else
                                text-gray-800 dark:text-gray-200
                            @endif">
                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                Résumé de la vérification - Session de rattrapage
                            @else
                                Résumé de la vérification
                            @endif
                        </h4>
                        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
                            <div>
                                <div class="
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        text-orange-600 dark:text-orange-400
                                    @else
                                        text-gray-600 dark:text-gray-400
                                    @endif">
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Total matières rattrapage
                                    @else
                                        Total matières
                                    @endif
                                </div>
                                <div class="font-semibold">{{ $totalMatieres }}</div>
                            </div>
                            <div>
                                <div class="text-green-600 dark:text-green-400">Complètes</div>
                                <div class="font-semibold text-green-600 dark:text-green-400">{{ $matieresCompletes }}</div>
                            </div>
                            <div>
                                <div class="text-red-600 dark:text-red-400">Incomplètes</div>
                                <div class="font-semibold text-red-600 dark:text-red-400">{{ $matieresIncompletes }}</div>
                            </div>
                            <div>
                                <div class="text-blue-600 dark:text-blue-400">Taux de complétude</div>
                                <div class="font-semibold text-blue-600 dark:text-blue-400">{{ $pourcentageCompletude }}%</div>
                            </div>
                        </div>

                        @if($pourcentageCompletude < 100)
                            <div class="p-3 mt-3 border
                                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                    border-orange-200 bg-orange-50 dark:bg-orange-900/20 dark:border-orange-800
                                @else
                                    border-yellow-200 bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-800
                                @endif rounded-md">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                            text-orange-600 dark:text-orange-400
                                        @else
                                            text-yellow-600 dark:text-yellow-400
                                        @endif mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="text-sm
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                            text-orange-800 dark:text-orange-200
                                        @else
                                            text-yellow-800 dark:text-yellow-200
                                        @endif">
                                        <strong>Attention :</strong>
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                            Certaines matières de rattrapage présentent des incohérences dans les données.
                                            Vérifiez les codes d'anonymat et assurez-vous que toutes les copies de rattrapage ont été importées correctement.
                                        @else
                                            Certaines matières présentent des incohérences dans les données.
                                            Vérifiez les codes d'anonymat et assurez-vous que toutes les copies ont été importées correctement.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-3 mt-3 border border-green-200 rounded-md bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="text-sm text-green-800 dark:text-green-200">
                                        <strong>Parfait !</strong>
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                            Toutes les matières de rattrapage sont complètes et prêtes pour la fusion.
                                            Vous pouvez procéder à la fusion des données de rattrapage en toute sécurité.
                                        @else
                                            Toutes les matières sont complètes et prêtes pour la fusion.
                                            Vous pouvez procéder à la fusion des données en toute sécurité.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Bouton "Voir les résultats à vérifier" -->
                        @if($statut === 'fusion' && $etapeFusion >= 1 && $examen_id)
                            <div class="mt-4">
                                <a
                                    href="{{ route('resultats.verification', ['examenId' => $examen_id]) }}"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white
                                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                            bg-orange-600 hover:bg-orange-700 focus:ring-orange-500
                                        @else
                                            bg-blue-600 hover:bg-blue-700 focus:ring-blue-500
                                        @endif border border-transparent rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                        Voir les résultats de rattrapage à vérifier
                                    @else
                                        Voir les résultats à vérifier
                                    @endif
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="p-6 text-center
            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                bg-orange-100 dark:bg-orange-700/30
            @else
                bg-gray-100 dark:bg-gray-700
            @endif rounded-lg">
            <svg class="w-12 h-12 mx-auto mb-4
                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                    text-orange-400 dark:text-orange-500
                @else
                    text-gray-400 dark:text-gray-500
                @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mb-2 text-lg font-medium
                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                    text-orange-800 dark:text-orange-200
                @else
                    text-gray-800 dark:text-gray-200
                @endif">
                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                    Aucun rapport de cohérence de rattrapage disponible
                @else
                    Aucun rapport de cohérence disponible
                @endif
            </h3>
            <p class="mb-4 text-sm
                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                    text-orange-600 dark:text-orange-300
                @else
                    text-gray-600 dark:text-gray-300
                @endif">
                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                    Pour générer un rapport de cohérence de rattrapage, vous devez d'abord effectuer une vérification des données de rattrapage.
                @else
                    Pour générer un rapport de cohérence, vous devez d'abord effectuer une vérification des données.
                @endif
            </p>
            <button
                wire:click="confirmVerification"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md
                    @if($sessionActive && $sessionActive->type === 'Rattrapage')
                        bg-orange-600 hover:bg-orange-700 focus:ring-orange-500
                    @else
                        bg-primary-600 hover:bg-primary-700 focus:ring-primary-500
                    @endif focus:outline-none focus:ring-2 focus:ring-offset-2"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                    Vérifier la cohérence rattrapage
                @else
                    Vérifier la cohérence
                @endif
            </button>
        </div>
    @endif

    @if($sessionActive && $sessionActive->type === 'Rattrapage' && $examen)
        <!-- Section spéciale de diagnostic pour les rattrapages -->
        <div class="p-4 mt-6 border border-orange-200 rounded-lg bg-orange-50 dark:bg-orange-900/20 dark:border-orange-700">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-medium text-orange-900 dark:text-orange-200">
                    Diagnostic des données de rattrapage
                </h4>
                <button
                    wire:click="diagnosticEligiblesRattrapage"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-orange-800 bg-orange-200 border border-orange-300 rounded hover:bg-orange-300 focus:outline-none dark:bg-orange-800 dark:text-orange-100 dark:border-orange-700 dark:hover:bg-orange-700">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Lancer diagnostic
                </button>
            </div>

            @php
                $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
                $compteursDonnees = $this->getCompteursDonneesSession();
            @endphp

            <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                <div class="p-3 bg-white border border-orange-200 rounded dark:bg-orange-900/10 dark:border-orange-700">
                    <div class="font-medium text-orange-700 dark:text-orange-300">Étudiants éligibles</div>
                    <div class="text-2xl font-bold text-orange-800 dark:text-orange-200">{{ $etudiantsEligibles->count() }}</div>
                    <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                        Source: {{ $etudiantsEligibles->isNotEmpty() ? $etudiantsEligibles->first()['source'] ?? 'N/A' : 'Aucune' }}
                    </div>
                </div>

                <div class="p-3 bg-white border border-orange-200 rounded dark:bg-orange-900/10 dark:border-orange-700">
                    <div class="font-medium text-orange-700 dark:text-orange-300">Manchettes</div>
                    <div class="text-2xl font-bold text-orange-800 dark:text-orange-200">{{ $compteursDonnees['manchettes'] }}</div>
                    <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                        Session rattrapage
                    </div>
                </div>

                <div class="p-3 bg-white border border-orange-200 rounded dark:bg-orange-900/10 dark:border-orange-700">
                    <div class="font-medium text-orange-700 dark:text-orange-300">Copies</div>
                    <div class="text-2xl font-bold text-orange-800 dark:text-orange-200">{{ $compteursDonnees['copies'] }}</div>
                    <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                        Avec notes saisies
                    </div>
                </div>

                <div class="p-3 bg-white border border-orange-200 rounded dark:bg-orange-900/10 dark:border-orange-700">
                    <div class="font-medium text-orange-700 dark:text-orange-300">Cohérence</div>
                    <div class="text-2xl font-bold text-orange-800 dark:text-orange-200">
                        @if($etudiantsEligibles->count() > 0 && $compteursDonnees['manchettes'] > 0)
                            ✓
                        @elseif($etudiantsEligibles->count() == 0 && $compteursDonnees['manchettes'] == 0)
                            ○
                        @else
                            ✗
                        @endif
                    </div>
                    <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                        @if($etudiantsEligibles->count() > 0 && $compteursDonnees['manchettes'] > 0)
                            OK
                        @elseif($etudiantsEligibles->count() == 0 && $compteursDonnees['manchettes'] == 0)
                            Vide
                        @else
                            Problème
                        @endif
                    </div>
                </div>
            </div>

            @if($etudiantsEligibles->count() == 0 && $compteursDonnees['manchettes'] > 0)
                <div class="p-3 mt-4 text-orange-900 bg-orange-200 border border-orange-400 rounded dark:bg-orange-800/50 dark:border-orange-600 dark:text-orange-100">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Incohérence détectée</div>
                            <div class="mt-1 text-sm">
                                {{ $compteursDonnees['manchettes'] }} manchette(s) trouvée(s) mais aucun étudiant éligible détecté.
                                <br>Possible cause : problème dans la méthode getEtudiantsEligiblesRattrapage().
                            </div>
                            <div class="mt-2 text-xs opacity-75">
                                Consultez les logs après avoir lancé le diagnostic pour plus de détails.
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
