        {{-- ✅ RÉSULTATS DE SIMULATION AVEC PROTECTION CONTRE LES ERREURS --}}
        @if(!empty($simulationDeliberation))
            <div class="bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <em class="mr-2 ni ni-bar-chart"></em>
                            Résultats de la Simulation Délibération
                        </h3>
                        <div class="flex items-center space-x-3">
                            {{-- ✅ DROPDOWN EXPORT APRÈS SIMULATION --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                        class="flex items-center px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                                    <em class="mr-2 ni ni-download"></em>
                                    Export Résultats
                                    <em class="ml-2 transition-transform ni ni-chevron-down" :class="{ 'rotate-180': open }"></em>
                                </button>

                                <div x-show="open"
                                    @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 z-10 w-64 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-700">

                                    <div class="py-2">
                                        {{-- En-tête exports par décision --}}
                                        <div class="px-4 py-1 text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                                            Par Décision
                                        </div>

                                        @php
                                            $stats = $simulationDeliberation['statistiques'] ?? [];
                                            $sessionType = $deliberationParams['session_type'] ?? 'session1';
                                        @endphp

                                        {{-- Export admis si disponibles --}}
                                        @if(($stats['admis'] ?? 0) > 0)
                                            <button wire:click="exporterParDecisionSimulation('admis', 'pdf')"
                                                    @click="open = false"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-green-50 dark:text-gray-300 dark:hover:bg-green-900/20">
                                                <em class="mr-3 text-green-500 ni ni-check-circle"></em>
                                                <div class="flex-1">
                                                    <div>Admis (PDF)</div>
                                                    <div class="text-xs text-green-600">Étudiants admis uniquement</div>
                                                </div>
                                                <span class="text-xs font-medium text-green-600">{{ ($stats['admis'] ?? 0) }}</span>
                                            </button>
                                        @endif

                                        {{-- Export selon le type de session --}}
                                        @if($sessionType === 'session1')
                                            {{-- Session 1: Rattrapage --}}
                                            @if(($stats['rattrapage'] ?? 0) > 0)
                                                <button wire:click="exporterParDecisionSimulation('rattrapage', 'pdf')"
                                                        @click="open = false"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-orange-50 dark:text-gray-300 dark:hover:bg-orange-900/20">
                                                    <em class="mr-3 text-orange-500 ni ni-clock"></em>
                                                    <div class="flex-1">
                                                        <div>Rattrapage (PDF)</div>
                                                        <div class="text-xs text-orange-600">Étudiants en rattrapage</div>
                                                    </div>
                                                    <span class="text-xs font-medium text-orange-600">{{ ($stats['rattrapage'] ?? 0) }}</span>
                                                </button>
                                            @endif
                                        @else
                                            {{-- Session 2: Redoublants et Exclus --}}
                                            @if(($stats['redoublant'] ?? 0) > 0)
                                                <button wire:click="exporterParDecisionSimulation('redoublant', 'pdf')"
                                                        @click="open = false"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-red-50 dark:text-gray-300 dark:hover:bg-red-900/20">
                                                    <em class="mr-3 text-red-500 ni ni-refresh"></em>
                                                    <div class="flex-1">
                                                        <div>Redoublants (PDF)</div>
                                                        <div class="text-xs text-red-600">Étudiants redoublants</div>
                                                    </div>
                                                    <span class="text-xs font-medium text-red-600">{{ ($stats['redoublant'] ?? 0) }}</span>
                                                </button>
                                            @endif

                                            @if(($stats['exclus'] ?? 0) > 0)
                                                <button wire:click="exporterParDecisionSimulation('exclus', 'pdf')"
                                                        @click="open = false"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-red-100 dark:text-gray-300 dark:hover:bg-red-900/30">
                                                    <em class="mr-3 text-red-800 ni ni-times-circle"></em>
                                                    <div class="flex-1">
                                                        <div>Exclus (PDF)</div>
                                                        <div class="text-xs text-red-800">Étudiants exclus</div>
                                                    </div>
                                                    <span class="text-xs font-medium text-red-800">{{ ($stats['exclus'] ?? 0) }}</span>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if(($simulationDeliberation['statistiques']['changements'] ?? 0) > 0)
                                <button wire:click="ouvrirConfirmationDeliberation"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-200 bg-orange-600 rounded-lg shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 hover:shadow-md">
                                    
                                    <em class="mr-2 ni ni-check-circle"></em>
                                    <span>Appliquer Délibération</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ✅ STATISTIQUES DE SIMULATION AVEC PROTECTION --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50">
                    @php
                        // ✅ PROTECTION CONTRE LES ERREURS ET VALEURS MANQUANTES
                        if (!empty($simulationDeliberation['statistiques'])) {
                            $stats = $simulationDeliberation['statistiques'];
                            $totalSimulation = $simulationDeliberation['total_etudiants'] ?? 0;
                            $changements = $stats['changements'] ?? 0;
                            $admisSimulation = $stats['admis'] ?? 0;
                            $rattrapageSimulation = $stats['rattrapage'] ?? 0;
                            $redoublantsSimulation = $stats['redoublant'] ?? 0;
                            $exclusSimulation = $stats['exclus'] ?? 0;
                            $sourceStats = 'simulation';
                        } else {
                            // Fallback vers les statistiques actuelles
                            $statsActuelles = $activeTab === 'session1' ? ($statistiquesSession1 ?? []) : ($statistiquesSession2 ?? []);
                            $totalSimulation = $statsActuelles['total_etudiants'] ?? 0;
                            $changements = 0;
                            $admisSimulation = $statsActuelles['admis'] ?? 0;
                            $rattrapageSimulation = $statsActuelles['rattrapage'] ?? 0;
                            $redoublantsSimulation = $statsActuelles['redoublant'] ?? 0;
                            $exclusSimulation = $statsActuelles['exclus'] ?? 0;
                            $sourceStats = 'actuelle';
                        }
                    @endphp

                    <div class="grid grid-cols-2 gap-4 mb-2 md:grid-cols-6">
                        {{-- Total étudiants --}}
                        <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalSimulation }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total étudiants</div>
                            @if($sourceStats === 'simulation')
                                <div class="mt-1 text-xs text-blue-500">✨ Simulé</div>
                            @endif
                        </div>

                        {{-- Changements (uniquement si simulation) --}}
                        @if($sourceStats === 'simulation')
                            <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $changements }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Changements</div>
                                <div class="mt-1 text-xs text-orange-500">vs Actuel</div>
                            </div>
                        @endif

                        {{-- Admis --}}
                        <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $admisSimulation }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Admis</div>
                            @if($sourceStats === 'simulation')
                                <div class="mt-1 text-xs text-green-500">✨ Nouveau</div>
                            @endif
                        </div>

                        {{-- ✅ AFFICHAGE CONDITIONNEL SELON LE TYPE DE SESSION --}}
                        @if(($deliberationParams['session_type'] ?? 'session1') === 'session1')
                            {{-- Session 1 : Afficher Rattrapage --}}
                            <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $rattrapageSimulation }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Rattrapage</div>
                                @if($sourceStats === 'simulation')
                                    <div class="mt-1 text-xs text-blue-500">✨ Nouveau</div>
                                @endif
                            </div>
                        @else
                            {{-- Session 2 : Afficher Redoublants --}}
                            <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $redoublantsSimulation }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Redoublants</div>
                                @if($sourceStats === 'simulation')
                                    <div class="mt-1 text-xs text-red-500">✨ Nouveau</div>
                                @endif
                            </div>
                        @endif

                        {{-- ✅ EXCLUS : AFFICHAGE POUR TOUTES LES SESSIONS --}}
                        <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                            <div class="text-2xl font-bold text-red-800 dark:text-red-300">{{ $exclusSimulation }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Exclus</div>
                            @if($sourceStats === 'simulation')
                                <div class="mt-1 text-xs text-red-500">✨ Nouveau</div>
                            @endif
                        </div>
                    </div>

                    {{-- ✅ MESSAGE DE CHANGEMENTS ADAPTATIF --}}
                    @if($sourceStats === 'simulation')
                        @if($changements > 0)
                            <div class="p-3 mb-4 bg-orange-100 border border-orange-300 rounded-lg dark:bg-orange-900/30 dark:border-orange-800">
                                <p class="text-sm text-orange-800 dark:text-orange-200">
                                    <em class="mr-2 ni ni-alert-circle"></em>
                                    <strong>{{ $changements }} changement(s) détecté(s)</strong> par rapport aux décisions actuelles.
                                    Utilisez le bouton "Appliquer Délibération" pour valider ces modifications.
                                </p>
                            </div>
                        @else
                            <div class="p-3 mb-4 bg-green-100 border border-green-300 rounded-lg dark:bg-green-900/30 dark:border-green-800">
                                <p class="text-sm text-green-800 dark:text-green-200">
                                    <em class="mr-2 ni ni-check-circle"></em>
                                    <strong>Aucun changement</strong> par rapport aux décisions actuelles avec ces paramètres.
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="p-3 mb-4 bg-blue-100 border border-blue-300 rounded-lg dark:bg-blue-900/30 dark:border-blue-800">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <em class="mr-2 ni ni-info-circle"></em>
                                <strong>Statistiques actuelles</strong> basées sur les décisions enregistrées en base de données.
                            </p>
                        </div>
                    @endif
                </div>
                @include('livewire.resultats.sessions.partials.table-result-deliberation')
            </div>
        @endif