<!-- Barre de filtres et contexte actuel - Design amélioré CORRIGÉ -->
<div class="mb-6 space-y-4">
    <!-- Filtres actuels / Fil d'Ariane avec badges interactifs -->
    @if($niveau_id || $parcours_id || $salle_id || $ec_id)
    <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-medium text-gray-700 dark:text-white">Filtres actifs</h3>
            <button wire:click="resetFiltres" class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 transition-colors bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Réinitialiser
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2 p-4">
            @if($niveau_id)
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-blue-800 transition-all duration-200 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
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
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium transition-all duration-200 rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 hover:bg-yellow-200 dark:hover:bg-yellow-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    @php
                        $selectedEc = collect($ecs)->firstWhere('id', $ec_id);
                    @endphp
                    {{ $selectedEc->nom ?? '' }}
                    <button wire:click="clearFilter('ec_id')" class="ml-1 transition-opacity opacity-0 text-yellow-500 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif

            @if($ec_id === 'all')
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium transition-all duration-200 rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 hover:bg-yellow-200 dark:hover:bg-yellow-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Toutes les matières
                    <button wire:click="clearFilter('ec_id')" class="ml-1 transition-opacity opacity-0 text-yellow-500 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif
        </div>

        <!-- Barre de progression et statistiques CORRIGÉE -->
        @if($ec_id && $ec_id !== 'all' && $totalEtudiantsCount > 0)
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2">
                    <div class="text-xs font-medium text-gray-700 dark:text-gray-300">Progression</div>
                    <div class="flex items-center gap-2">
                        <div class="w-32 h-2 overflow-hidden bg-gray-200 rounded-full dark:bg-gray-600">
                            @php
                                // ✅ CORRECTION : Calculer $restantes ici
                                $progression = $totalEtudiantsCount > 0 ? round(($totalCopiesCount / $totalEtudiantsCount) * 100) : 0;
                                $restantes = max(0, $totalEtudiantsCount - $totalCopiesCount);
                            @endphp
                            <div class="h-full rounded-full transition-all duration-500 ease-out
                                @if($progression >= 80)
                                    bg-green-500 dark:bg-green-400
                                @elseif($progression >= 50)
                                    bg-amber-500 dark:bg-amber-400
                                @else
                                    bg-red-500 dark:bg-red-400
                                @endif"
                                style="width: {{ $progression }}%">
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ $totalCopiesCount }}/{{ $totalEtudiantsCount }}
                            ({{ $progression }}%)
                        </span>
                        @if($restantes > 0)
                            <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-200">
                                {{ $restantes }} restante{{ $restantes > 1 ? 's' : '' }}
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                ✅ Terminé
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1 text-xs text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $totalCopiesCount }} notes saisies</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $restantes }} en attente</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                        </svg>
                        <span>{{ $userCopiesCount }} par vous</span>
                    </div>
                     <div class="flex items-center gap-1 text-xs text-gray-700 dark:text-gray-300">
                        <div class="w-2 h-2 {{ $enableDoubleVerification ? 'bg-blue-500' : 'bg-gray-400' }} rounded-full"></div>
                        <span class="text-gray-600 dark:text-gray-400">Mode:</span>
                        <span class="{{ $enableDoubleVerification ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $enableDoubleVerification ? 'Sécurisé' : 'Rapide' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Filtres de sélection avec animation et interactivité -->
    <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md dark:bg-gray-800 dark:border-gray-700">
        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="flex items-center gap-2 text-base font-medium text-gray-900 dark:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                    </svg>
                    Filtres de sélection
                </h3>
                <div class="flex items-center space-x-3">
                    @if($examen_id && $ec_id)
                    <button
                        wire:click="openCopieModal"
                        class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded bg-primary-600 text-white hover:bg-primary-700 focus:outline-none dark:bg-primary-700 dark:hover:bg-primary-800">
                    <em class="mr-1 text-sm icon ni ni-plus-circle"></em>
                        Ajouter une note
                    </button>
                    @endif
                    <a href="{{ route('copies.index') }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-cyan-300 bg-cyan text-cyan-700 hover:bg-cyan-50 focus:outline-none dark:bg-cyan-800 dark:border-cyan-700 dark:text-cyan-200 dark:hover:bg-cyan-700">
                        <em class="mr-1 text-sm icon ni ni-reload-alt"></em>
                        Actualiser
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <!-- Niveau avec icône et animation -->
                <div class="transition-all duration-300 transform hover:scale-[1.02]">
                    <label for="niveau_id" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                        Niveau
                    </label>
                    <div class="relative mt-1 rounded-md">
                        <select
                            id="niveau_id"
                            wire:model.live="niveau_id"
                            class="block w-full py-2 pl-3 pr-10 text-base transition-colors duration-200 border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Sélectionner un niveau</option>
                            @foreach($niveaux as $niveau)
                                <option value="{{ $niveau->id }}">{{ $niveau->abr }} - {{ $niveau->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Parcours avec icône et animation -->
                <div class="transition-all duration-300 transform hover:scale-[1.02]">
                    <label for="parcours_id" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        Parcours
                    </label>
                    <div class="relative mt-1 rounded-md">
                        <select
                            id="parcours_id"
                            wire:model.live="parcours_id"
                            class="block w-full py-2 pl-3 pr-10 text-base transition-colors duration-200 border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ count($parcours) ? '' : 'disabled' }}>
                            <option value="">Sélectionner un parcours</option>
                            @foreach($parcours as $parcour)
                                <option value="{{ $parcour->id }}">{{ $parcour->abr }} - {{ $parcour->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(!count($parcours) && $niveau_id)
                        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Aucun parcours trouvé pour ce niveau</p>
                    @endif
                </div>

                <!-- Matière/EC avec logique de présence intelligente -->
                <div class="col-span-6 sm:col-span-2 transition-all min-w-xl duration-300 transform hover:scale-[1.02]">
                    <div class="relative mb-5 last:mb-0">
                        <label for="ec_id" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Matière
                            @if(count($ecs) > 0)
                                <span class="text-xs text-green-600">({{ count($ecs) }} matière{{ count($ecs) > 1 ? 's' : '' }})</span>
                            @endif
                        </label>

                        <div class="relative mt-1 rounded-md">
                            <select
                                id="ec_id"
                                wire:model.live="ec_id"
                                class="block w-full py-2 pl-3 pr-10 text-base transition-colors duration-200 border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ count($ecs) ? '' : 'disabled' }}>

                                <option value="">Sélectionner une matière</option>

                                @if(count($ecs) > 1)
                                    <option value="all">Toutes les matières ({{ count($ecs) }})</option>
                                @endif

                                @if(count($ecs) > 0)
                                    @foreach($ecs as $ec)
                                        @php
                                            $copiesCount = $ec->copies_count ?? 0;
                                            
                                            // Récupérer les données de présence spécifiques à cette matière
                                            $presenceStatsMatiere = $this->getPresenceStatsParMatiere($ec->id);
                                            $etudiantsPresentsMatiere = $presenceStatsMatiere['presents'] ?? 0;
                                            $hasPresenceDataMatiere = $presenceStatsMatiere !== null;
                                            
                                            $progressionColor = '';
                                            $statusIcon = '';
                                            $affichageCompteur = '';
                                            
                                            if ($hasPresenceDataMatiere && $etudiantsPresentsMatiere > 0) {
                                                // Calcul basé sur les données SPÉCIFIQUES à cette matière
                                                $pourcentage = round(($copiesCount / $etudiantsPresentsMatiere) * 100);
                                                $affichageCompteur = "({$copiesCount}/{$etudiantsPresentsMatiere} - {$pourcentage}%)";
                                                
                                                if ($pourcentage >= 100) {
                                                    $progressionColor = 'text-green-600';
                                                    $statusIcon = '✅';
                                                } elseif ($pourcentage >= 75) {
                                                    $progressionColor = 'text-blue-600';
                                                    $statusIcon = '🔵';
                                                } elseif ($pourcentage >= 50) {
                                                    $progressionColor = 'text-yellow-600';
                                                    $statusIcon = '🟡';
                                                } elseif ($pourcentage > 0) {
                                                    $progressionColor = 'text-orange-600';
                                                    $statusIcon = '🟠';
                                                } else {
                                                    $progressionColor = 'text-red-600';
                                                    $statusIcon = '❌';
                                                }
                                            } else {
                                                // Pas de données de présence pour cette matière spécifique
                                                $progressionColor = 'text-gray-500';
                                                $statusIcon = '⚫';
                                                if ($copiesCount > 0) {
                                                    $affichageCompteur = "({$copiesCount}/? - Pas de présence)";
                                                } else {
                                                    $affichageCompteur = "(0/? - Pas de présence)";
                                                }
                                            }
                                        @endphp
                                        <option value="{{ $ec->id }}"
                                                class="{{ $progressionColor }}"
                                                data-copies="{{ $copiesCount }}"
                                                data-presents="{{ $etudiantsPresentsMatiere }}"
                                                data-has-presence="{{ $hasPresenceDataMatiere ? 'true' : 'false' }}">
                                            {{ $statusIcon }}
                                            {{ $ec->nom ?? 'Nom indisponible' }}
                                            {{ $affichageCompteur }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        @if(!count($ecs) && $salle_id)
                            <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Aucune matière trouvée pour cette salle</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicateur de présence (LECTURE SEULE) -->
@if($ec_id && $ec_id !== 'all' && $session_exam_id)
    @php
        $presenceStats = $this->getPresenceStats();
        $canStartSaisie = $this->canStartCopiesSaisie();
        $presenceStatus = $this->getPresenceStatusMessage();
    @endphp
    
    <div class="mt-4 p-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white flex items-center">
                <em class="mr-2 text-blue-600 ni ni-users dark:text-blue-400"></em>
                Données de présence
            </h4>
            
            @if($presenceStats)
                <div class="flex items-center space-x-2 text-xs">
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded dark:bg-green-900 dark:text-green-200">
                        ✅ {{ $presenceStats['presents'] }} présent(s)
                    </span>
                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded dark:bg-red-900 dark:text-red-200">
                        ❌ {{ $presenceStats['absents'] }} absent(s)
                    </span>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded dark:bg-blue-900 dark:text-blue-200">
                        📊 {{ round($presenceStats['taux_presence']) }}%
                    </span>
                </div>
            @endif
        </div>

        @if($presenceStatus)
            <div class="p-3 rounded-lg {{ $presenceStatus['type'] === 'success' ? 'bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-700' : 
                ($presenceStatus['type'] === 'info' ? 'bg-blue-50 border border-blue-200 dark:bg-blue-900/20 dark:border-blue-700' : 
                'bg-yellow-50 border border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-700') }}">
                <div class="flex items-start">
                    <em class="mr-2 mt-0.5 {{ $presenceStatus['type'] === 'success' ? 'text-green-600 dark:text-green-400' : 
                        ($presenceStatus['type'] === 'info' ? 'text-blue-600 dark:text-blue-400' : 
                        'text-yellow-600 dark:text-yellow-400') }} {{ $presenceStatus['icon'] }}"></em>
                    <div class="text-sm {{ $presenceStatus['type'] === 'success' ? 'text-green-800 dark:text-green-200' : 
                        ($presenceStatus['type'] === 'info' ? 'text-blue-800 dark:text-blue-200' : 
                        'text-yellow-800 dark:text-yellow-200') }}">
                        {{ $presenceStatus['message'] }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
</div>