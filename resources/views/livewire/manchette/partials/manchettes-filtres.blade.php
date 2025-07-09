<!-- Barre de filtres et contexte actuel - Design amélioré -->
<div id="id" class="mb-6 space-y-4">
    <!-- Filtres actuels / Fil d'Ariane avec badges interactifs -->
    @if($niveau_id || $parcours_id || $salle_id || $ec_id)
    <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-medium text-gray-700 dark:text-white">Filtres actifs</h3>
                <!-- NOUVEAU : Indicateur de session dans les filtres -->
                @if(isset($sessionInfo) && is_array($sessionInfo) && ($sessionInfo['is_active'] ?? false))
                    <div class="flex items-center px-2 py-1 text-xs rounded-full
                        {{ ($sessionInfo['type'] ?? '') === 'rattrapage' ? 'bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-900/30 dark:text-orange-300' : 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300' }}">
                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full
                            {{ ($sessionInfo['type'] ?? '') === 'rattrapage' ? 'bg-orange-400' : 'bg-blue-400' }}"></span>
                        Session {{ $sessionInfo['type'] ?? 'normale' }}
                    </div>
                @endif
            </div>
            <button wire:click="resetFiltres" class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 transition-colors bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <em class="mr-1 text-sm icon ni ni-reload"></em>
                Réinitialiser
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2 p-4">
            @if($niveau_id)
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-blue-800 transition-all duration-200 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                    {{ $niveaux->where('id', $niveau_id)->first()->nom ?? '' }}
                    <button wire:click="clearFilter('niveau_id')" class="ml-1 text-blue-500 transition-opacity opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif

            @if($parcours_id)
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-indigo-800 transition-all duration-200 bg-indigo-100 rounded-full dark:bg-indigo-900 dark:text-indigo-200 hover:bg-indigo-200 dark:hover:bg-indigo-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    {{ $parcours->where('id', $parcours_id)->first()->nom ?? '' }}
                    <button wire:click="clearFilter('parcours_id')" class="ml-1 text-indigo-500 transition-opacity opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif


            @if($ec_id && $ec_id !== 'all')
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium transition-all duration-200 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 hover:bg-amber-200 dark:hover:bg-amber-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    @php
                        $selectedEc = collect($ecs)->firstWhere('id', $ec_id);
                    @endphp
                    {{ $selectedEc->nom ?? '' }}
                    <button wire:click="clearFilter('ec_id')" class="ml-1 transition-opacity opacity-0 text-amber-500 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif

            @if($ec_id === 'all')
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium transition-all duration-200 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 hover:bg-amber-200 dark:hover:bg-amber-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Toutes les matières
                    <button wire:click="clearFilter('ec_id')" class="ml-1 transition-opacity opacity-0 text-amber-500 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif
        </div>

<!-- Barre de progression et statistiques pour manchettes - VERSION CORRIGÉE -->
        @if($ec_id && $ec_id !== 'all' && $totalEtudiantsCount > 0)
        @php
            // CALCUL CORRIGÉ : Utiliser les vraies données de présence
            $presenceStats = $this->getPresenceStatsIntelligente();
            $etudiantsPresents = $presenceStats['presents'] ?? 0;
            $etudiantsAbsents = $presenceStats['absents'] ?? 0;
            $hasPresenceData = $presenceStats !== null;
            
            // Base de calcul : étudiants présents si données disponibles, sinon fallback sur total
            $baseCalcul = $hasPresenceData ? $etudiantsPresents : $totalEtudiantsExpected;
            $pourcentageProgression = $baseCalcul > 0 ? round(($totalManchettesCount / $baseCalcul) * 100) : 0;
            $restantsSaisir = max(0, $baseCalcul - $totalManchettesCount);
        @endphp
        
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2">
                    <div class="text-xs font-medium text-gray-700 dark:text-gray-300">Progression</div>
                    
                    <!-- Type de session -->
                    @if(isset($sessionInfo) && is_array($sessionInfo) && ($sessionInfo['active'] ?? false))
                        <div class="px-2 py-0.5 text-xs rounded text-gray-600 bg-gray-100 dark:bg-gray-600 dark:text-gray-300">
                            Session {{ $sessionInfo['type'] ?? 'normale' }}
                        </div>
                    @endif
                    
                    <!-- NOUVEAU : Indicateur du type de données utilisées -->
                    @if($hasPresenceData)
                        <div class="px-2 py-0.5 text-xs rounded text-green-700 bg-green-100 dark:bg-green-800 dark:text-green-200">
                            @if($presenceStats['type'] === 'specifique')
                                📍 Présence spécifique
                            @else
                                🌐 Présence globale
                            @endif
                        </div>
                    @else
                        <div class="px-2 py-0.5 text-xs rounded text-amber-700 bg-amber-100 dark:bg-amber-800 dark:text-amber-200">
                            ⚠️ Estimé (pas de présence)
                        </div>
                    @endif
                    
                    <div class="flex items-center gap-2">
                        <div class="w-32 h-2 overflow-hidden bg-gray-200 rounded-full dark:bg-gray-600">
                            <div class="h-full rounded-full transition-all duration-500 ease-out
                                @if($pourcentageProgression >= 100)
                                    bg-green-500 dark:bg-green-400
                                @elseif($pourcentageProgression >= 80)
                                    bg-blue-500 dark:bg-blue-400
                                @elseif($pourcentageProgression >= 50)
                                    bg-amber-500 dark:bg-amber-400
                                @else
                                    bg-red-500 dark:bg-red-400
                                @endif"
                                style="width: {{ $pourcentageProgression }}%">
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ $totalManchettesCount }}/{{ $baseCalcul }}
                            ({{ $pourcentageProgression }}%)
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Manchettes saisies -->
                    <div class="flex items-center gap-1 text-xs text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $totalManchettesCount }} manchette{{ $totalManchettesCount > 1 ? 's' : '' }}</span>
                    </div>
                    
                    <!-- En attente (basé sur les présents) -->
                    <div class="flex items-center gap-1 text-xs text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $restantsSaisir }} en attente</span>
                    </div>
                    
                    <!-- NOUVEAU : Données de présence détaillées -->
                    @if($hasPresenceData)
                        <div class="flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                            </svg>
                            <span>{{ $etudiantsPresents }} présent{{ $etudiantsPresents > 1 ? 's' : '' }}</span>
                        </div>
                        
                        @if($etudiantsAbsents > 0)
                            <div class="flex items-center gap-1 text-xs text-red-500 dark:text-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $etudiantsAbsents }} absent{{ $etudiantsAbsents > 1 ? 's' : '' }}</span>
                            </div>
                        @endif
                    @endif
                    
                    <!-- Vos manchettes -->
                    <div class="flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM9 9a1 1 0 112 0v4a1 1 0 11-2 0V9z" />
                        </svg>
                        <span>{{ $userManchettesCount }} par vous</span>
                    </div>
                    
                    <!-- Statut de la session -->
                    @if(isset($sessionInfo) && is_array($sessionInfo))
                        @if($sessionInfo['can_add'] ?? false)
                            @if($hasPresenceData)
                                <div class="flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Prêt à saisir</span>
                                </div>
                            @else
                                <div class="flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Enregistrer présence</span>
                                </div>
                            @endif
                        @else
                            <div class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <span>Saisie bloquée</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            
            <!-- NOUVEAU : Ligne d'information contextuelle -->
            @if($hasPresenceData)
                <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>
                            @if($presenceStats['type'] === 'specifique')
                                Données de présence spécifiques à cette matière
                            @else
                                Données de présence globales pour cet examen
                            @endif
                        </span>
                        <span>
                            Taux de présence : {{ round($presenceStats['taux_presence'], 1) }}%
                        </span>
                    </div>
                </div>
            @else
                <div class="mt-2 pt-2 border-t border-amber-200 dark:border-amber-600 bg-amber-50 dark:bg-amber-900/20 -mx-4 px-4 py-2">
                    <div class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>Les calculs sont basés sur le nombre total d'étudiants. Enregistrez les données de présence pour plus de précision.</span>
                    </div>
                </div>
            @endif
        </div>
        @endif
    </div>
    @endif

    @include('livewire.manchette.partials.selection-filtres')

</div>
