{{-- resources/views/livewire/pages/table-statistique.blade.php --}}
<div class="col-span-12 2xl:col-span-12">

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif



    <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900 shadow-lg">
        {{-- Header avec filtres --}}
        <div class="p-5 pb-2 sm:p-6 sm:pb-2 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">
                    Statistiques par Niveau et Parcours
                    {{-- Debug info --}}
                    <span class="text-xs text-gray-500 ml-2">
                        ({{ $statistiquesNiveaux->count() }} niveaux, {{ $statistiquesParcours->count() }} parcours)
                    </span>
                </h6>
                
                {{-- Filtres et actions --}}
                <div class="flex items-center space-x-3">
                    {{-- Filtre par niveau --}}
                    <div class="relative">
                        <select wire:model.live="selectedNiveauFilter" 
                                class="text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tous niveaux</option>
                            @forelse($statistiquesNiveaux as $niveau)
                                <option value="{{ $niveau->id }}">{{ $niveau->abr }} - {{ $niveau->nom }}</option>
                            @empty
                                <option disabled>Aucun niveau disponible</option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Sélecteur de vue --}}
                    <div class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                        <button wire:click="$set('viewMode', 'table')" 
                                class="px-3 py-1 text-xs rounded-md transition-all {{ $viewMode === 'table' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400' }}">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18m-9 8h9"></path>
                            </svg>
                            Tableau
                        </button>
                        <button wire:click="$set('viewMode', 'cards')" 
                                class="px-3 py-1 text-xs rounded-md transition-all {{ $viewMode === 'cards' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400' }}">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"></path>
                            </svg>
                            Cartes
                        </button>
                    </div>

                    {{-- Bouton refresh --}}
                    <button wire:click="refresh" 
                            class="px-3 py-2 text-xs bg-gray-50 text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            {{ $refreshing ? 'disabled' : '' }}>
                        <svg class="w-4 h-4 inline mr-1 {{ $refreshing ? 'animate-spin' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        {{ $refreshing ? 'Actualisation...' : 'Actualiser' }}
                    </button>

                    {{-- Export --}}
                    <button wire:click="exportTableData" 
                            class="px-3 py-2 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/50 dark:text-blue-400 dark:hover:bg-blue-900 rounded-lg transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export
                    </button>
                </div>
            </div>

            {{-- Métriques globales --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                @php
                    $totalEtudiants = $statistiquesParcours->sum('etudiants_count');
                    $totalAdmis = $etudiantsAdmis;
                    $totalRedoublants = $redoublants;
                    $tauxGlobalReussite = $totalEtudiants > 0 ? round(($totalAdmis / $totalEtudiants) * 100, 1) : 0;
                @endphp
                
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalEtudiants) }}</div>
                    <div class="text-xs text-blue-600/70 dark:text-blue-400/70">Total Étudiants</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $statistiquesParcours->count() }} parcours</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($totalAdmis) }}</div>
                    <div class="text-xs text-green-600/70 dark:text-green-400/70">Total Admis</div>
                </div>
                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3">
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($totalRedoublants) }}</div>
                    <div class="text-xs text-orange-600/70 dark:text-orange-400/70">Redoublants</div>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $tauxGlobalReussite }}%</div>
                    <div class="text-xs text-purple-600/70 dark:text-purple-400/70">Taux Global</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $statistiquesNiveaux->count() }} niveaux</div>
                </div>
            </div>
        </div>

        {{-- Contenu principal --}}
        <div class="p-5 sm:p-6">
            @if($statistiquesParcours->isEmpty())
                {{-- Cas où il n'y a pas de données --}}
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aucune donnée disponible</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">
                        Il n'y a actuellement aucun parcours ou étudiant dans la base de données.
                    </p>
                    
                    {{-- Instructions pour résoudre le problème --}}
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 max-w-md mx-auto">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Pour résoudre ce problème :</h4>
                        <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <p>1. Exécutez les seeders :</p>
                            <code class="block bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs">
                                php artisan db:seed --class=NiveauxSeeder<br>
                                php artisan db:seed --class=ParcoursSeeder<br>
                                php artisan db:seed --class=EtudiantsTestSeeder
                            </code>
                        </div>
                    </div>
                </div>
            @else
                @if(($viewMode ?? 'table') === 'table')
                    {{-- Vue Tableau --}}
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">
                                        Niveau
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">
                                        Parcours
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300 hidden md:table-cell">
                                        Total
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">
                                        Admis
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300 hidden lg:table-cell">
                                        Redoublants
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300 hidden xl:table-cell">
                                        Exclus
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">
                                        Taux Réussite
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @php
                                    $parcoursFiltered = $selectedNiveauFilter 
                                        ? $statistiquesParcours->where('niveau.id', $selectedNiveauFilter)
                                        : $statistiquesParcours;
                                @endphp

                                @forelse($parcoursFiltered as $parcours)
                                    @php
                                        $totalParcours = $parcours->etudiants_count ?? 0;
                                        $admisParcours = round($totalParcours * 0.7); // Simulation
                                        $redoublantsParcours = round($totalParcours * 0.15);
                                        $exclusParcours = round($totalParcours * 0.1);
                                        $rattrapage = $totalParcours - $admisParcours - $redoublantsParcours - $exclusParcours;
                                        $tauxReussite = $totalParcours > 0 ? round(($admisParcours / $totalParcours) * 100, 1) : 0;
                                        $couleurTaux = $tauxReussite >= 70 ? 'green' : ($tauxReussite >= 50 ? 'yellow' : 'red');
                                        $niveau = $parcours->niveau;
                                    @endphp
                                    
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                        {{-- Niveau --}}
                                        <td class="py-4 px-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                                                    {{ $niveau->abr ?? 'N/A' }}
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $niveau->nom ?? 'N/A' }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        @if($niveau && $niveau->is_concours)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
                                                                Concours
                                                            </span>
                                                        @endif
                                                        @if($niveau && $niveau->has_rattrapage)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 ml-1">
                                                                Rattrapage
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Parcours --}}
                                        <td class="py-4 px-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center text-white font-bold text-xs">
                                                    {{ $parcours->abr ?? substr($parcours->nom, 0, 2) }}
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $parcours->nom }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $totalParcours }} étudiants inscrits
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Total --}}
                                        <td class="py-4 px-4 hidden md:table-cell">
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($totalParcours) }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">étudiants</div>
                                        </td>

                                        {{-- Admis --}}
                                        <td class="py-4 px-4">
                                            <div class="flex items-center space-x-2">
                                                <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($admisParcours) }}</div>
                                                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 min-w-[40px]">
                                                    <div class="bg-green-500 h-2 rounded-full transition-all duration-500" 
                                                         style="width: {{ min($tauxReussite, 100) }}%"></div>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $tauxReussite }}%</div>
                                        </td>

                                        {{-- Redoublants --}}
                                        <td class="py-4 px-4 hidden lg:table-cell">
                                            <div class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ number_format($redoublantsParcours) }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $totalParcours > 0 ? round(($redoublantsParcours / $totalParcours) * 100, 1) : 0 }}%
                                            </div>
                                        </td>

                                        {{-- Exclus --}}
                                        <td class="py-4 px-4 hidden xl:table-cell">
                                            <div class="text-lg font-bold text-red-600 dark:text-red-400">{{ number_format($exclusParcours) }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $totalParcours > 0 ? round(($exclusParcours / $totalParcours) * 100, 1) : 0 }}%
                                            </div>
                                        </td>

                                        {{-- Taux de réussite --}}
                                        <td class="py-4 px-4">
                                            <div class="flex items-center space-x-2">
                                                <div class="text-lg font-bold text-{{ $couleurTaux }}-600 dark:text-{{ $couleurTaux }}-400">
                                                    {{ $tauxReussite }}%
                                                </div>
                                                @if($tauxReussite >= 70)
                                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @elseif($tauxReussite >= 50)
                                                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                @if($tauxReussite >= 70)
                                                    Excellent
                                                @elseif($tauxReussite >= 50)
                                                    Moyen
                                                @else
                                                    Faible
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center">
                                            <p class="text-gray-500 dark:text-gray-400">Aucun parcours trouvé pour le filtre sélectionné</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    {{-- Vue Cartes --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @php
                            $parcoursFiltered = $selectedNiveauFilter 
                                ? $statistiquesParcours->where('niveau.id', $selectedNiveauFilter)
                                : $statistiquesParcours;
                        @endphp

                        @forelse($parcoursFiltered as $parcours)
                            @php
                                $totalParcours = $parcours->etudiants_count ?? 0;
                                $admisParcours = round($totalParcours * 0.7);
                                $redoublantsParcours = round($totalParcours * 0.15);
                                $exclusParcours = round($totalParcours * 0.1);
                                $tauxReussite = $totalParcours > 0 ? round(($admisParcours / $totalParcours) * 100, 1) : 0;
                            @endphp
                            
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition-all duration-300 group">
                                {{-- Header de la carte --}}
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center text-white font-bold">
                                            {{ $parcours->abr ?? substr($parcours->nom, 0, 2) }}
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 dark:text-white">{{ $parcours->nom }}</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $parcours->niveau->nom ?? 'Niveau' }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Badge niveau --}}
                                <div class="mb-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                        {{ $parcours->niveau->abr ?? 'N/A' }} - {{ number_format($totalParcours) }} étudiants
                                    </span>
                                </div>

                                {{-- Statistiques détaillées --}}
                                <div class="space-y-4">
                                    {{-- Admis --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Admis</span>
                                            <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($admisParcours) }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full transition-all duration-1000" style="width: {{ $tauxReussite }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $tauxReussite }}% de réussite</div>
                                    </div>

                                    {{-- Redoublants --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Redoublants</span>
                                            <span class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ number_format($redoublantsParcours) }}</span>
                                        </div>
                                        @php $tauxRedoublement = $totalParcours > 0 ? round(($redoublantsParcours / $totalParcours) * 100, 1) : 0; @endphp
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-orange-500 h-2 rounded-full transition-all duration-1000" style="width: {{ $tauxRedoublement }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $tauxRedoublement }}% redoublent</div>
                                    </div>

                                    {{-- Exclus --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Exclus</span>
                                            <span class="text-lg font-bold text-red-600 dark:text-red-400">{{ number_format($exclusParcours) }}</span>
                                        </div>
                                        @php $tauxExclusion = $totalParcours > 0 ? round(($exclusParcours / $totalParcours) * 100, 1) : 0; @endphp
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-red-500 h-2 rounded-full transition-all duration-1000" style="width: {{ $tauxExclusion }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $tauxExclusion }}% exclus</div>
                                    </div>
                                </div>

                                {{-- Footer avec évaluation --}}
                                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Performance</span>
                                        <div class="flex items-center space-x-1">
                                            @if($tauxReussite >= 70)
                                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-xs font-semibold text-green-600 dark:text-green-400">Excellent</span>
                                            @elseif($tauxReussite >= 50)
                                                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-xs font-semibold text-yellow-600 dark:text-yellow-400">Moyen</span>
                                            @else
                                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-xs font-semibold text-red-600 dark:text-red-400">Faible</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <p class="text-gray-500 dark:text-gray-400">Aucun parcours trouvé pour le filtre sélectionné</p>
                            </div>
                        @endforelse
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Scripts pour l'interactivité --}}
@push('scripts')
<script>
document.addEventListener('livewire:initialized', () => {
    // Animation des barres de progression au chargement
    const progressBars = document.querySelectorAll('[style*="width:"]');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });

    // Message de confirmation après actualisation
    window.addEventListener('dashboard-refreshed', () => {
        console.log('Dashboard actualisé avec succès');
    });
});
</script>
@endpush