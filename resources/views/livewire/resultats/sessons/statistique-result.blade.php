{{-- ✅ SECTION STATISTIQUES SYNCHRONISÉE AVEC BASE DE DONNÉES --}}
@if(!empty($resultatsSession1) || (!empty($resultatsSession2) && $showSession2))
    <div class="mb-6">
        <div class="grid grid-cols-1 {{ $showSession2 ? 'lg:grid-cols-2' : '' }} gap-6">

            <!-- ✅ Stats Session 1 - DONNÉES SYNCHRONISÉES AVEC BASE -->
            <div class="p-4 bg-white border border-gray-200 dark:bg-gray-800 rounded-xl dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center justify-center w-6 h-6 bg-blue-500 rounded-md">
                            <em class="text-white ni ni-graduation"></em>
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">Session 1</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">(Normale)</span>
                        @php
                            // ✅ DÉTERMINER LA SOURCE DES DONNÉES POUR SESSION 1
                            $useSimulationS1 = ($activeTab === 'session1' && !empty($simulationDeliberation['statistiques']));

                            // ✅ CALCULER LES VRAIES STATS DEPUIS LES DONNÉES ACTUELLES
                            if ($useSimulationS1) {
                                // Utiliser les stats de simulation
                                $stats1 = $simulationDeliberation['statistiques'];
                                $stats1['total_etudiants'] = $stats1['admis'] + $stats1['rattrapage'] + ($stats1['redoublant'] ?? 0) + ($stats1['exclus'] ?? 0);
                            } else {
                                // Calculer depuis les résultats réels de la session 1
                                $resultatsS1 = collect($resultatsSession1);
                                $stats1 = [
                                    'total_etudiants' => $resultatsS1->count(),
                                    'admis' => $resultatsS1->where('decision', 'admis')->count(),
                                    'rattrapage' => $resultatsS1->where('decision', 'rattrapage')->count(),
                                    'redoublant' => $resultatsS1->where('decision', 'redoublant')->count(),
                                    'exclus' => $resultatsS1->where('decision', 'exclus')->count(),
                                ];
                            }
                        @endphp
                        @if($useSimulationS1)
                            <span class="px-2 py-1 text-xs text-orange-800 bg-orange-100 rounded-full dark:bg-orange-900/50 dark:text-orange-200">
                                🔄 Simulé
                            </span>
                        @endif
                    </div>
                    <div class="text-right">
                        @php
                            $tauxReussiteS1 = ($stats1['total_etudiants'] ?? 0) > 0
                                ? round((($stats1['admis'] ?? 0) / $stats1['total_etudiants']) * 100, 2)
                                : 0;
                        @endphp
                        <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                            {{ $tauxReussiteS1 }}%
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">réussite</div>
                        @if($useSimulationS1)
                            @php
                                // Calculer le taux actuel pour comparaison
                                $resultatsActuelsS1 = collect($resultatsSession1);
                                $tauxActuelS1 = $resultatsActuelsS1->count() > 0
                                    ? round(($resultatsActuelsS1->where('decision', 'admis')->count() / $resultatsActuelsS1->count()) * 100, 2)
                                    : 0;
                            @endphp
                            <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">vs {{ $tauxActuelS1 }}% actuel</div>
                        @endif
                    </div>
                </div>

                {{-- ✅ GRILLE 4 COLONNES POUR SESSION 1 : Total + Admis + Rattrapage + Exclus --}}
                <div class="grid grid-cols-4 gap-2 mb-3">
                    <div class="p-2 text-center rounded-lg bg-blue-50 dark:bg-blue-900/20 {{ $useSimulationS1 ? 'ring-2 ring-orange-300' : '' }}">
                        <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                            {{ $stats1['total_etudiants'] ?? 0 }}
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400">Total</div>
                        @if($useSimulationS1)
                            @php $totalActuelS1 = collect($resultatsSession1)->count(); @endphp
                            @if(($stats1['total_etudiants'] ?? 0) !== $totalActuelS1)
                                <div class="mt-1 text-xs text-orange-600">{{ $totalActuelS1 }} → {{ $stats1['total_etudiants'] ?? 0 }}</div>
                            @endif
                        @endif
                    </div>

                    <div class="p-2 text-center rounded-lg bg-green-50 dark:bg-green-900/20 {{ $useSimulationS1 ? 'ring-2 ring-orange-300' : '' }}">
                        <div class="text-lg font-bold text-green-600 dark:text-green-400">
                            {{ $stats1['admis'] ?? 0 }}
                        </div>
                        <div class="text-xs text-green-600 dark:text-green-400">Admis</div>
                        @if($useSimulationS1)
                            @php $admisActuelS1 = collect($resultatsSession1)->where('decision', 'admis')->count(); @endphp
                            @if(($stats1['admis'] ?? 0) !== $admisActuelS1)
                                <div class="mt-1 text-xs text-orange-600">{{ $admisActuelS1 }} → {{ $stats1['admis'] ?? 0 }}</div>
                            @endif
                        @endif
                    </div>

                    <div class="p-2 text-center rounded-lg bg-orange-50 dark:bg-orange-900/20 {{ $useSimulationS1 ? 'ring-2 ring-orange-300' : '' }}">
                        <div class="text-lg font-bold text-orange-600 dark:text-orange-400">
                            {{ $stats1['rattrapage'] ?? 0 }}
                        </div>
                        <div class="text-xs text-orange-600 dark:text-orange-400">Rattrapage</div>
                        @if($useSimulationS1)
                            @php $rattrapageActuelS1 = collect($resultatsSession1)->where('decision', 'rattrapage')->count(); @endphp
                            @if(($stats1['rattrapage'] ?? 0) !== $rattrapageActuelS1)
                                <div class="mt-1 text-xs text-orange-600">{{ $rattrapageActuelS1 }} → {{ $stats1['rattrapage'] ?? 0 }}</div>
                            @endif
                        @endif
                    </div>

                    {{-- ✅ EXCLUS POUR SESSION 1 --}}
                    <div class="p-2 text-center rounded-lg bg-red-50 dark:bg-red-900/20 {{ $useSimulationS1 ? 'ring-2 ring-orange-300' : '' }}">
                        <div class="text-lg font-bold text-red-600 dark:text-red-400">
                            {{ $stats1['exclus'] ?? 0 }}
                        </div>
                        <div class="text-xs text-red-600 dark:text-red-400">Exclus</div>
                        @if($useSimulationS1)
                            @php $exclusActuelS1 = collect($resultatsSession1)->where('decision', 'exclus')->count(); @endphp
                            @if(($stats1['exclus'] ?? 0) !== $exclusActuelS1)
                                <div class="mt-1 text-xs text-orange-600">{{ $exclusActuelS1 }} → {{ $stats1['exclus'] ?? 0 }}</div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- ✅ BARRE DE PROGRESSION AVEC ANIMATION --}}
                <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                    <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-1000 ease-out {{ $useSimulationS1 ? 'animate-pulse' : '' }}"
                        style="width: {{ $tauxReussiteS1 }}%"></div>
                </div>

                {{-- ✅ INDICATION DE CHANGEMENT POUR SESSION 1 --}}
                @if($useSimulationS1)
                    @php
                        $changementsS1 = $simulationDeliberation['statistiques']['changements'] ?? 0;
                    @endphp
                    @if($changementsS1 > 0)
                        <div class="p-2 mt-2 border border-orange-200 rounded-lg bg-orange-50 dark:bg-orange-900/20 dark:border-orange-800">
                            <div class="text-xs text-center text-orange-800 dark:text-orange-200">
                                <em class="mr-1 ni ni-arrow-up-right"></em>
                                {{ $changementsS1 }} changement(s) simulé(s)
                            </div>
                        </div>
                    @else
                        <div class="p-2 mt-2 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                            <div class="text-xs text-center text-green-800 dark:text-green-200">
                                <em class="mr-1 ni ni-check"></em>
                                Aucun changement
                            </div>
                        </div>
                    @endif
                @else
                    {{-- ✅ AFFICHER STATUS DÉLIBÉRATION SI APPLIQUÉE --}}
                    @if($deliberationStatus['session1'] ?? false)
                        <div class="p-2 mt-2 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                            <div class="text-xs text-center text-green-800 dark:text-green-200">
                                <em class="mr-1 ni ni-check-circle"></em>
                                Délibération appliquée
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- ✅ Stats Session 2 - DONNÉES SYNCHRONISÉES AVEC BASE -->
            @if($showSession2)
                <div class="p-4 bg-white border border-gray-200 dark:bg-gray-800 rounded-xl dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center justify-center w-6 h-6 bg-green-500 rounded-md">
                                <em class="text-white ni ni-repeat"></em>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Session 2</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">(Rattrapage)</span>
                            @php
                                // ✅ DÉTERMINER LA SOURCE DES DONNÉES POUR SESSION 2
                                $useSimulationS2 = ($activeTab === 'session2' && !empty($simulationDeliberation['statistiques']));

                                // ✅ CALCULER LES VRAIES STATS DEPUIS LES DONNÉES ACTUELLES
                                if ($useSimulationS2) {
                                    // Utiliser les stats de simulation
                                    $stats2 = $simulationDeliberation['statistiques'];
                                    $stats2['total_etudiants'] = $stats2['admis'] + ($stats2['rattrapage'] ?? 0) + $stats2['redoublant'] + $stats2['exclus'];
                                } else {
                                    // Calculer depuis les résultats réels de la session 2
                                    $resultatsS2 = collect($resultatsSession2);
                                    $stats2 = [
                                        'total_etudiants' => $resultatsS2->count(),
                                        'admis' => $resultatsS2->where('decision', 'admis')->count(),
                                        'rattrapage' => $resultatsS2->where('decision', 'rattrapage')->count(),
                                        'redoublant' => $resultatsS2->where('decision', 'redoublant')->count(),
                                        'exclus' => $resultatsS2->where('decision', 'exclus')->count(),
                                    ];
                                }
                            @endphp
                            @if($useSimulationS2)
                                <span class="px-2 py-1 text-xs text-orange-800 bg-orange-100 rounded-full dark:bg-orange-900/50 dark:text-orange-200">
                                    🔄 Simulé
                                </span>
                            @endif
                        </div>
                        <div class="text-right">
                            @php
                                $tauxReussiteS2 = ($stats2['total_etudiants'] ?? 0) > 0
                                    ? round((($stats2['admis'] ?? 0) / $stats2['total_etudiants']) * 100, 2)
                                    : 0;
                            @endphp
                            <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                {{ $tauxReussiteS2 }}%
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">réussite</div>
                            @if($useSimulationS2)
                                @php
                                    // Calculer le taux actuel pour comparaison
                                    $resultatsActuelsS2 = collect($resultatsSession2);
                                    $tauxActuelS2 = $resultatsActuelsS2->count() > 0
                                        ? round(($resultatsActuelsS2->where('decision', 'admis')->count() / $resultatsActuelsS2->count()) * 100, 2)
                                        : 0;
                                @endphp
                                <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">vs {{ $tauxActuelS2 }}% actuel</div>
                            @endif
                        </div>
                    </div>

                    {{-- ✅ GRILLE 4 COLONNES POUR SESSION 2 : Total + Admis + Redoublants + Exclus --}}
                    <div class="grid grid-cols-4 gap-2 mb-3">
                        <div class="p-2 text-center rounded-lg bg-green-50 dark:bg-green-900/20 {{ $useSimulationS2 ? 'ring-2 ring-orange-300' : '' }}">
                            <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                {{ $stats2['total_etudiants'] ?? 0 }}
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400">Total</div>
                            @if($useSimulationS2)
                                @php $totalActuelS2 = collect($resultatsSession2)->count(); @endphp
                                @if(($stats2['total_etudiants'] ?? 0) !== $totalActuelS2)
                                    <div class="mt-1 text-xs text-orange-600">{{ $totalActuelS2 }} → {{ $stats2['total_etudiants'] ?? 0 }}</div>
                                @endif
                            @endif
                        </div>

                        <div class="p-2 text-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20 {{ $useSimulationS2 ? 'ring-2 ring-orange-300' : '' }}">
                            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $stats2['admis'] ?? 0 }}
                            </div>
                            <div class="text-xs text-emerald-600 dark:text-emerald-400">Admis</div>
                            @if($useSimulationS2)
                                @php $admisActuelS2 = collect($resultatsSession2)->where('decision', 'admis')->count(); @endphp
                                @if(($stats2['admis'] ?? 0) !== $admisActuelS2)
                                    <div class="mt-1 text-xs text-orange-600">{{ $admisActuelS2 }} → {{ $stats2['admis'] ?? 0 }}</div>
                                @endif
                            @endif
                        </div>

                        <div class="p-2 text-center rounded-lg bg-red-50 dark:bg-red-900/20 {{ $useSimulationS2 ? 'ring-2 ring-orange-300' : '' }}">
                            <div class="text-lg font-bold text-red-600 dark:text-red-400">
                                {{ $stats2['redoublant'] ?? 0 }}
                            </div>
                            <div class="text-xs text-red-600 dark:text-red-400">Redoublants</div>
                            @if($useSimulationS2)
                                @php $redoublantActuelS2 = collect($resultatsSession2)->where('decision', 'redoublant')->count(); @endphp
                                @if(($stats2['redoublant'] ?? 0) !== $redoublantActuelS2)
                                    <div class="mt-1 text-xs text-orange-600">{{ $redoublantActuelS2 }} → {{ $stats2['redoublant'] ?? 0 }}</div>
                                @endif
                            @endif
                        </div>

                        {{-- ✅ EXCLUS POUR SESSION 2 --}}
                        <div class="p-2 text-center border border-red-200 rounded-lg bg-red-50 dark:bg-red-900/20 dark:border-red-800 {{ $useSimulationS2 ? 'ring-2 ring-orange-300' : '' }}">
                            <div class="text-lg font-bold text-red-700 dark:text-red-300">
                                {{ $stats2['exclus'] ?? 0 }}
                            </div>
                            <div class="text-xs text-red-700 dark:text-red-300">Exclus</div>
                            @if($useSimulationS2)
                                @php $exclusActuelS2 = collect($resultatsSession2)->where('decision', 'exclus')->count(); @endphp
                                @if(($stats2['exclus'] ?? 0) !== $exclusActuelS2)
                                    <div class="mt-1 text-xs text-orange-600">{{ $exclusActuelS2 }} → {{ $stats2['exclus'] ?? 0 }}</div>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- ✅ BARRE DE PROGRESSION AVEC ANIMATION --}}
                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                        <div class="bg-green-500 h-1.5 rounded-full transition-all duration-1000 ease-out {{ $useSimulationS2 ? 'animate-pulse' : '' }}"
                            style="width: {{ $tauxReussiteS2 }}%"></div>
                    </div>

                    {{-- ✅ INDICATION DE CHANGEMENT POUR SESSION 2 --}}
                    @if($useSimulationS2)
                        @php
                            $changementsS2 = $simulationDeliberation['statistiques']['changements'] ?? 0;
                        @endphp
                        @if($changementsS2 > 0)
                            <div class="p-2 mt-2 border border-orange-200 rounded-lg bg-orange-50 dark:bg-orange-900/20 dark:border-orange-800">
                                <div class="text-xs text-center text-orange-800 dark:text-orange-200">
                                    <em class="mr-1 ni ni-arrow-up-right"></em>
                                    {{ $changementsS2 }} changement(s) simulé(s)
                                </div>
                            </div>
                        @else
                            <div class="p-2 mt-2 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                                <div class="text-xs text-center text-green-800 dark:text-green-200">
                                    <em class="mr-1 ni ni-check"></em>
                                    Aucun changement
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- ✅ AFFICHER STATUS DÉLIBÉRATION SI APPLIQUÉE --}}
                        @if($deliberationStatus['session2'] ?? false)
                            <div class="p-2 mt-2 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                                <div class="text-xs text-center text-green-800 dark:text-green-200">
                                    <em class="mr-1 ni ni-check-circle"></em>
                                    Délibération appliquée
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- ✅ LÉGENDE EXPLICATIVE MISE À JOUR --}}
    <div class="p-3 mb-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
        <div class="text-sm text-blue-800 dark:text-blue-200">
            <em class="mr-2 ni ni-info-circle"></em>
            <strong>Statistiques en temps réel :</strong>
            Les chiffres reflètent les vraies données de la base après délibération ou simulation.
            Le badge "🔄 Simulé" indique les données temporaires, et les flèches montrent les changements "ancien → nouveau".
        </div>
    </div>

    {{-- ✅ SECTION HISTORIQUE DES DÉLIBÉRATIONS --}}
    @if(isset($deliberationStatus['session1']) && $deliberationStatus['session1'] || isset($deliberationStatus['session2']) && $deliberationStatus['session2'])
        <div class="p-3 mb-4 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
            <div class="flex items-center mb-2">
                <em class="mr-2 text-green-600 ni ni-history dark:text-green-400"></em>
                <h4 class="font-semibold text-green-800 dark:text-green-200">Historique des Délibérations</h4>
            </div>
            <div class="space-y-1 text-sm text-green-700 dark:text-green-300">
                @if(isset($deliberationStatus['session1']) && $deliberationStatus['session1'])
                    <div>• Session 1 : Délibération appliquée avec succès</div>
                @endif
                @if(isset($deliberationStatus['session2']) && $deliberationStatus['session2'])
                    <div>• Session 2 : Délibération appliquée avec succès</div>
                @endif
            </div>
        </div>
    @endif
@endif

{{-- ✅ AJOUT CSS POUR LES ANIMATIONS AMÉLIORÉES --}}
@push('styles')
<style>
    /* Animation pour les cartes simulées */
    .ring-2.ring-orange-300 {
        animation: pulse-orange 2s infinite;
    }

    @keyframes pulse-orange {
        0%, 100% {
            box-shadow: 0 0 0 2px rgba(251, 146, 60, 0.4);
        }
        50% {
            box-shadow: 0 0 0 4px rgba(251, 146, 60, 0.1);
        }
    }

    /* Transition smooth pour les changements de valeurs */
    .text-lg.font-bold {
        transition: all 0.3s ease;
    }

    /* Animation pour les barres de progression */
    .bg-blue-500, .bg-green-500 {
        transition: width 1s ease-out;
    }

    /* Effet hover sur les statistiques */
    .hover\\:scale-105:hover {
        transform: scale(1.05);
    }
</style>
@endpush
