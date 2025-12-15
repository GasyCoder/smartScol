{{-- Statistiques synchronisées avec base de données --}}
@if(!empty($resultatsSession1) || (!empty($resultatsSession2) && $showSession2))
    <div class="mb-6">
        <div class="grid grid-cols-1 {{ $showSession2 ? 'lg:grid-cols-2' : '' }} gap-6">
            {{-- Stats Session 1 --}}
            @if(!empty($resultatsSession1))
                <div class="p-4 bg-white border border-gray-200 dark:bg-gray-800 rounded-xl dark:border-gray-700 mt-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center justify-center w-6 h-6 bg-blue-500 rounded-md">
                                <em class="text-white ni ni-check-circle"></em>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Session 1</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">(Normale)</span>
                            
                            {{-- Indicateur simulation --}}
                            @php
                                $useSimulationS1 = ($activeTab === 'simulation' && !empty($simulationDeliberation['statistiques']) && 
                                                   ($simulationDeliberation['parametres_utilises']['session_type'] ?? '') === 'session1');
                            @endphp
                            @if($useSimulationS1)
                                <span class="px-2 py-1 text-xs text-orange-800 bg-orange-100 rounded-full dark:bg-orange-900/50 dark:text-orange-200">
                                    Simulé
                                </span>
                            @endif
                        </div>
                        
                        {{-- Taux de réussite --}}
                        <div class="text-right">
                            @php
                                if ($useSimulationS1) {
                                    $stats1 = $simulationDeliberation['statistiques'];
                                    $total1 = $stats1['admis'] + $stats1['rattrapage'] + ($stats1['redoublant'] ?? 0) + ($stats1['exclus'] ?? 0);
                                } else {
                                    $resultatsS1 = collect($resultatsSession1);
                                    $stats1 = [
                                        'total_etudiants' => $resultatsS1->count(),
                                        'admis' => $resultatsS1->where('decision', 'admis')->count(),
                                        'rattrapage' => $resultatsS1->where('decision', 'rattrapage')->count(),
                                        'redoublant' => $resultatsS1->where('decision', 'redoublant')->count(),
                                        'exclus' => $resultatsS1->where('decision', 'exclus')->count(),
                                    ];
                                    $total1 = $stats1['total_etudiants'];
                                }
                                $tauxReussiteS1 = $total1 > 0 ? round(($stats1['admis'] / $total1) * 100, 2) : 0;
                            @endphp
                            <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                {{ $tauxReussiteS1 }}%
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">réussite</div>
                        </div>
                    </div>

                    {{-- Grille statistiques S1 --}}
                    <div class="grid grid-cols-4 gap-2 mb-3">
                        <div class="p-2 text-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                {{ $total1 }}
                            </div>
                            <div class="text-xs text-blue-600 dark:text-blue-400">Total</div>
                        </div>

                        <div class="p-2 text-center rounded-lg bg-green-50 dark:bg-green-900/20">
                            <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                {{ $stats1['admis'] ?? 0 }}
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400">Admis</div>
                        </div>

                        <div class="p-2 text-center rounded-lg bg-orange-50 dark:bg-orange-900/20">
                            <div class="text-lg font-bold text-orange-600 dark:text-orange-400">
                                {{ $stats1['rattrapage'] ?? 0 }}
                            </div>
                            <div class="text-xs text-orange-600 dark:text-orange-400">Rattrapage</div>
                        </div>

                        {{-- ✅ MODIFICATION : Afficher Redoublant ou Exclus selon niveau --}}
                        @php
                            $niveau = \App\Models\Niveau::find($selectedNiveau);
                            $afficherExclus = $niveau && in_array($niveau->abr, ['PACES', 'L1']);
                        @endphp
                        
                        @if($afficherExclus)
                            <div class="p-2 text-center rounded-lg bg-red-50 dark:bg-red-900/20">
                                <div class="text-lg font-bold text-red-600 dark:text-red-400">
                                    {{ $stats1['exclus'] ?? 0 }}
                                </div>
                                <div class="text-xs text-red-600 dark:text-red-400">Exclus</div>
                            </div>
                        @else
                            <div class="p-2 text-center rounded-lg bg-yellow-50 dark:bg-yellow-900/20">
                                <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">
                                    {{ $stats1['redoublant'] ?? 0 }}
                                </div>
                                <div class="text-xs text-yellow-600 dark:text-yellow-400">Redoublant</div>
                            </div>
                        @endif
                    </div>

                    {{-- Barre de progression S1 --}}
                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                        <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-1000 ease-out"
                            style="width: {{ $tauxReussiteS1 }}%"></div>
                    </div>

                    {{-- Statut délibération/simulation S1 --}}
                    @if($useSimulationS1)
                        @php $changementsS1 = $simulationDeliberation['statistiques']['changements'] ?? 0; @endphp
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
                    @elseif($deliberationStatus['session1'] ?? false)
                        <div class="p-2 mt-2 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                            <div class="text-xs text-center text-green-800 dark:text-green-200">
                                <em class="mr-1 ni ni-check-circle"></em>
                                Délibération appliquée
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Stats Session 2 --}}
            @if($showSession2 && !empty($resultatsSession2))
                <div class="p-4 bg-white border border-gray-200 dark:bg-gray-800 rounded-xl dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center justify-center w-6 h-6 bg-green-500 rounded-md">
                                <em class="text-white ni ni-repeat"></em>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Session 2</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">(Rattrapage)</span>
                            
                            {{-- Indicateur simulation --}}
                            @php
                                $useSimulationS2 = ($activeTab === 'simulation' && !empty($simulationDeliberation['statistiques']) && 
                                                   ($simulationDeliberation['parametres_utilises']['session_type'] ?? '') === 'session2');
                            @endphp
                            @if($useSimulationS2)
                                <span class="px-2 py-1 text-xs text-orange-800 bg-orange-100 rounded-full dark:bg-orange-900/50 dark:text-orange-200">
                                    Simulé
                                </span>
                            @endif
                        </div>
                        
                        {{-- Taux de réussite --}}
                        <div class="text-right">
                            @php
                                if ($useSimulationS2) {
                                    $stats2 = $simulationDeliberation['statistiques'];
                                    $total2 = $stats2['admis'] + ($stats2['rattrapage'] ?? 0) + $stats2['redoublant'] + $stats2['exclus'];
                                } else {
                                    $resultatsS2 = collect($resultatsSession2);
                                    $stats2 = [
                                        'total_etudiants' => $resultatsS2->count(),
                                        'admis' => $resultatsS2->where('decision', 'admis')->count(),
                                        'rattrapage' => $resultatsS2->where('decision', 'rattrapage')->count(),
                                        'redoublant' => $resultatsS2->where('decision', 'redoublant')->count(),
                                        'exclus' => $resultatsS2->where('decision', 'exclus')->count(),
                                    ];
                                    $total2 = $stats2['total_etudiants'];
                                }
                                $tauxReussiteS2 = $total2 > 0 ? round(($stats2['admis'] / $total2) * 100, 2) : 0;
                            @endphp
                            <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                {{ $tauxReussiteS2 }}%
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">réussite</div>
                        </div>
                    </div>

                    {{-- Grille statistiques S2 --}}
                    <div class="grid grid-cols-4 gap-2 mb-3">
                        <div class="p-2 text-center rounded-lg bg-green-50 dark:bg-green-900/20">
                            <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                {{ $total2 }}
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400">Total</div>
                        </div>

                        <div class="p-2 text-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $stats2['admis'] ?? 0 }}
                            </div>
                            <div class="text-xs text-emerald-600 dark:text-emerald-400">Admis</div>
                        </div>

                        <div class="p-2 text-center rounded-lg bg-yellow-50 dark:bg-yellow-900/20">
                            <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">
                                {{ $stats2['redoublant'] ?? 0 }}
                            </div>
                            <div class="text-xs text-yellow-600 dark:text-yellow-400">Redoublants</div>
                        </div>

                        {{-- ✅ MODIFICATION : Afficher Exclus seulement pour PACES --}}
                        @if($afficherExclus)
                            <div class="p-2 text-center border border-red-200 rounded-lg bg-red-50 dark:bg-red-900/20 dark:border-red-800">
                                <div class="text-lg font-bold text-red-700 dark:text-red-300">
                                    {{ $stats2['exclus'] ?? 0 }}
                                </div>
                                <div class="text-xs text-red-700 dark:text-red-300">Exclus</div>
                            </div>
                        @else
                            <div class="p-2 text-center rounded-lg bg-gray-50 dark:bg-gray-900/20">
                                <div class="text-lg font-bold text-gray-600 dark:text-gray-400">
                                    -
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">-</div>
                            </div>
                        @endif
                    </div>

                    {{-- Barre de progression S2 --}}
                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                        <div class="bg-green-500 h-1.5 rounded-full transition-all duration-1000 ease-out"
                            style="width: {{ $tauxReussiteS2 }}%"></div>
                    </div>

                    {{-- Statut délibération/simulation S2 --}}
                    @if($useSimulationS2)
                        @php $changementsS2 = $simulationDeliberation['statistiques']['changements'] ?? 0; @endphp
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
                    @elseif($deliberationStatus['session2'] ?? false)
                        <div class="p-2 mt-2 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                            <div class="text-xs text-center text-green-800 dark:text-green-200">
                                <em class="mr-1 ni ni-check-circle"></em>
                                Délibération appliquée
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Historique des délibérations --}}
    @if(($deliberationStatus['session1'] ?? false) || ($deliberationStatus['session2'] ?? false))
        <div class="p-3 mb-4 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
            <div class="flex items-center mb-2">
                <em class="mr-2 text-green-600 ni ni-history dark:text-green-400"></em>
                <h4 class="font-semibold text-green-800 dark:text-green-200">Historique des Délibérations</h4>
            </div>
            <div class="space-y-1 text-sm text-green-700 dark:text-green-300">
                @if($deliberationStatus['session1'] ?? false)
                    <div>• Session 1 : Délibération appliquée avec succès</div>
                @endif
                @if($deliberationStatus['session2'] ?? false)
                    <div>• Session 2 : Délibération appliquée avec succès</div>
                @endif
            </div>
        </div>
    @endif
@endif