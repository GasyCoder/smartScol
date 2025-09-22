<div class="space-y-6">
@if(auth()->user()->hasRole('secretaire'))
    @livewire('secretaire-dashboard')
@else
<div class="font-body">
    <!-- Header -->
    <div class="container mx-auto px-6 py-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-heading font-bold text-gray-800 dark:text-gray-100">Dashboard</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">Vue d'ensemble des performances académiques</p>
            </div>
            <button wire:click="refresh" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all duration-200 {{ $refreshing ? 'opacity-75' : '' }}">
                <svg class="w-4 h-4 mr-2 {{ $refreshing ? 'animate-spin' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ $refreshing ? 'Actualisation...' : 'Actualiser' }}
            </button>
        </div>
    </div>

    <div class="container mx-auto px-6 py-8">
        <!-- Main Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Étudiants -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Total</p>
                        <div class="flex items-baseline mt-1">
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($totalEtudiants) }}</p>
                            @if($progressionEtudiants != 0)
                                <span class="ml-2 text-sm font-semibold {{ $progressionEtudiants >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $progressionEtudiants >= 0 ? '+' : '' }}{{ $progressionEtudiants }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admis -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Admis</p>
                        <div class="flex items-baseline mt-1">
                            <p class="text-2xl font-bold text-green-700 dark:text-green-400">{{ number_format($etudiantsAdmis) }}</p>
                            @if($totalEtudiants > 0)
                                <span class="ml-2 text-sm font-medium text-green-600 dark:text-green-500">
                                    {{ round(($etudiantsAdmis / $totalEtudiants) * 100, 1) }}%
                                </span>
                            @endif
                        </div>
                        @if($progressionAdmis != 0)
                            <div class="mt-1 text-xs {{ $progressionAdmis >= 0 ? 'text-green-600 dark:text-green-500' : 'text-red-600 dark:text-red-500' }}">
                                {{ $progressionAdmis >= 0 ? '↗' : '↘' }} {{ abs($progressionAdmis) }}% vs précédent
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Redoublants -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Redoublants</p>
                        <div class="flex items-baseline mt-1">
                            <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ number_format($redoublants) }}</p>
                            @if($totalEtudiants > 0)
                                <span class="ml-2 text-sm font-medium text-yellow-600 dark:text-yellow-500">
                                    {{ round(($redoublants / $totalEtudiants) * 100, 1) }}%
                                </span>
                            @endif
                        </div>
                        @if($progressionRedoublants != 0)
                            <div class="mt-1 text-xs {{ $progressionRedoublants >= 0 ? 'text-red-600 dark:text-red-500' : 'text-green-600 dark:text-green-500' }}">
                                {{ $progressionRedoublants >= 0 ? '↗' : '↘' }} {{ abs($progressionRedoublants) }}% vs précédent
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rattrapage -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Rattrapage</p>
                        <div class="flex items-baseline mt-1">
                            <p class="text-2xl font-bold text-cyan-700 dark:text-cyan-400">{{ number_format($rattrapage) }}</p>
                            @if($totalEtudiants > 0)
                                <span class="ml-2 text-sm font-medium text-cyan-600 dark:text-cyan-500">
                                    {{ round(($rattrapage / $totalEtudiants) * 100, 1) }}%
                                </span>
                            @endif
                        </div>
                        @if($progressionRattrapage != 0)
                            <div class="mt-1 text-xs {{ $progressionRattrapage >= 0 ? 'text-red-600 dark:text-red-500' : 'text-green-600 dark:text-green-500' }}">
                                {{ $progressionRattrapage >= 0 ? '↗' : '↘' }} {{ abs($progressionRattrapage) }}% vs précédent
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Exclus -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Exclus</p>
                        <div class="flex items-baseline mt-1">
                            <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ number_format($exclus) }}</p>
                            @if($totalEtudiants > 0)
                                <span class="ml-2 text-sm font-medium text-red-600 dark:text-red-500">
                                    {{ round(($exclus / $totalEtudiants) * 100, 1) }}%
                                </span>
                            @endif
                        </div>
                        @if($progressionExclus != 0)
                            <div class="mt-1 text-xs {{ $progressionExclus >= 0 ? 'text-red-600 dark:text-red-500' : 'text-green-600 dark:text-green-500' }}">
                                {{ $progressionExclus >= 0 ? '↗' : '↘' }} {{ abs($progressionExclus) }}% vs précédent
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Taux de Réussite -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-heading font-semibold text-gray-800 dark:text-gray-100">Taux de Réussite</h3>
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                    </div>
                </div>
                @php
                    $tauxReussite = $totalEtudiants > 0 ? round(($etudiantsAdmis / $totalEtudiants) * 100, 1) : 0;
                @endphp
                <div class="flex items-end space-x-2">
                    <span class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $tauxReussite }}%</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400 pb-1">{{ $etudiantsAdmis }}/{{ $totalEtudiants }}</span>
                </div>
                <div class="mt-3 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ $tauxReussite }}%"></div>
                </div>
            </div>

            <!-- Taux d'Échec -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-heading font-semibold text-gray-800 dark:text-gray-100">Taux d'Échec</h3>
                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                        </svg>
                    </div>
                </div>
                @php
                    $tauxEchec = $totalEtudiants > 0 ? round((($redoublants + $exclus) / $totalEtudiants) * 100, 1) : 0;
                @endphp
                <div class="flex items-end space-x-2">
                    <span class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $tauxEchec }}%</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400 pb-1">{{ $redoublants + $exclus }}/{{ $totalEtudiants }}</span>
                </div>
                <div class="mt-3 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-gradient-to-r from-red-500 to-red-600 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ $tauxEchec }}%"></div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Taux de Réussite Mensuel -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-heading font-semibold text-gray-800 dark:text-gray-100">
                        <i class="fas fa-chart-line mr-2 text-green-500"></i>
                        Taux de Réussite Mensuel
                    </h3>
                    <div class="flex items-center space-x-3">
                        <select wire:model="selectedYear" wire:change="changeYear($event.target.value)" 
                                class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                            @foreach($anneesUniversitaires as $annee)
                                <option value="{{ $annee->id }}">{{ $annee->libelle }}</option>
                            @endforeach
                        </select>
                        <div class="flex rounded-md overflow-hidden border border-gray-300 dark:border-gray-600">
                            <button wire:click="changeChartType('line')" 
                                    class="px-3 py-1 text-xs font-medium transition-colors {{ $selectedChartType === 'line' ? 'bg-primary-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Ligne
                            </button>
                            <button wire:click="changeChartType('bar')" 
                                    class="px-3 py-1 text-xs font-medium transition-colors {{ $selectedChartType === 'bar' ? 'bg-primary-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Barres
                            </button>
                        </div>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="tauxReussiteChart" class="w-full h-full"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-green-600">
                            {{ $totalEtudiants > 0 ? round(($etudiantsAdmis / $totalEtudiants) * 100, 1) : 0 }}%
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Taux Global</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-600">{{ $etudiantsAdmis }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Total Admis</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-600">{{ $totalEtudiants }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Total Candidats</div>
                    </div>
                </div>
            </div>

            <!-- Performance Académique Détaillée -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-heading font-semibold text-gray-800 dark:text-gray-100">
                        <i class="fas fa-graduation-cap mr-2 text-blue-500"></i>
                        Performance Académique
                    </h3>
                </div>
                <div class="h-80">
                    <canvas id="performanceChart" class="w-full h-full"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-4 gap-2 text-center">
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mb-1"></div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Admis</p>
                        <p class="font-semibold text-green-600 dark:text-green-400">{{ $etudiantsAdmis }}</p>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full mx-auto mb-1"></div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Rattrapage</p>
                        <p class="font-semibold text-yellow-600 dark:text-yellow-400">{{ $rattrapage }}</p>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3">
                        <div class="w-3 h-3 bg-orange-500 rounded-full mx-auto mb-1"></div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Redoublants</p>
                        <p class="font-semibold text-orange-600 dark:text-orange-400">{{ $redoublants }}</p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                        <div class="w-3 h-3 bg-red-500 rounded-full mx-auto mb-1"></div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Exclus</p>
                        <p class="font-semibold text-red-600 dark:text-red-400">{{ $exclus }}</p>
                    </div>
                </div>
            </div>
        </div>


        <!-- Statistics Tables -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
            <!-- Statistiques par Niveau -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-heading font-semibold text-gray-800 dark:text-gray-100">Statistiques par Niveau</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Niveau</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Étudiants</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Admis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Taux</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($statistiquesNiveaux as $niveau)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-medium text-white">{{ $niveau->abr }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $niveau->nom }}</div>
                                                <div class="flex space-x-1 mt-1">
                                                    @if($niveau->is_concours)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">
                                                            Concours
                                                        </span>
                                                    @endif
                                                    @if($niveau->has_rattrapage)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cyan-100 dark:bg-cyan-900 text-cyan-800 dark:text-cyan-200">
                                                            Rattrapage
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($niveau->etudiants_count) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-green-600 dark:text-green-400">{{ $niveau->admis_count ?? 0 }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $niveau->taux_reussite ?? 0 }}%</div>
                                            <div class="ml-2 w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                <div class="bg-green-500 h-2 rounded-full transition-all duration-300" 
                                                     style="width: {{ $niveau->taux_reussite ?? 0 }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucun niveau trouvé</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Statistiques par Parcours -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-heading font-semibold text-gray-800 dark:text-gray-100">Statistiques par Parcours</h3>
                        <div class="flex space-x-2">
                            <select wire:model="selectedNiveauFilter" 
                                    class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Tous les niveaux</option>
                                @foreach($statistiquesNiveaux as $niveau)
                                    <option value="{{ $niveau->id }}">{{ $niveau->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Parcours</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Niveau</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Admis/Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Taux</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @php
                                $filteredParcours = $selectedNiveauFilter ? 
                                    $statistiquesParcours->where('niveau.id', $selectedNiveauFilter) : 
                                    $statistiquesParcours;
                            @endphp
                            @forelse($filteredParcours as $parcours)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-medium text-white">{{ substr($parcours->abr, 0, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $parcours->nom }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $parcours->abr }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200">
                                            {{ $parcours->niveau->abr }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            <span class="text-green-600 dark:text-green-400">{{ $parcours->admis_count ?? 0 }}</span>/<span>{{ $parcours->etudiants_count }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $parcours->taux_reussite ?? 0 }}%</div>
                                            <div class="ml-2 w-12 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                                <div class="bg-green-500 h-1.5 rounded-full transition-all duration-300" 
                                                     style="width: {{ $parcours->taux_reussite ?? 0 }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucun parcours trouvé</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Session Status Alert -->
        @if($sessionDeliberee)
            <div class="mt-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-800 dark:text-green-200">
                            <span class="font-medium">Session délibérée</span> - Les résultats ont été validés par le jury.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endif
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isDarkMode = document.documentElement.classList.contains('dark');
        const gridColor = isDarkMode ? '#374151' : '#f1f5f9';
        const textColor = isDarkMode ? '#d1d5db' : '#6b7280';

        // ✅ GRAPHIQUE 1 : Taux de Réussite Mensuel (adapté éducation)
        const tauxReussiteCtx = document.getElementById('tauxReussiteChart').getContext('2d');
        const tauxReussiteChart = new Chart(tauxReussiteCtx, {
            type: '{{ $selectedChartType }}',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [
                    {
                        label: 'Taux de Réussite (%)',
                        data: @json($chartDataTauxReussite ?? array_fill(0, 12, 0)),
                        borderColor: '#10b981',
                        backgroundColor: '#10b98120',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Nombre d\'Admis',
                        data: @json($chartDataAdmis),
                        borderColor: '#3b82f6',
                        backgroundColor: '#3b82f620',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Moyenne Générale',
                        data: @json($chartDataMoyennes ?? array_fill(0, 12, 10)),
                        borderColor: '#8b5cf6',
                        backgroundColor: '#8b5cf620',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y2'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Évolution des Performances Académiques',
                        color: textColor
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            color: textColor
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label === 'Taux de Réussite (%)') {
                                    return label + ': ' + context.parsed.y + '%';
                                } else if (label === 'Moyenne Générale') {
                                    return label + ': ' + context.parsed.y + '/20';
                                } else {
                                    return label + ': ' + context.parsed.y + ' étudiants';
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: textColor
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Nombre d\'Étudiants',
                            color: textColor
                        },
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Taux de Réussite (%)',
                            color: textColor
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            color: textColor,
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    y2: {
                        type: 'linear',
                        display: false,
                        position: 'right',
                        min: 0,
                        max: 20,
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });

        // ✅ GRAPHIQUE 2 : Performance Académique (adapté éducation)
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(performanceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Admis', 'Rattrapage', 'Redoublants', 'Exclus'],
                datasets: [{
                    data: [{{ $etudiantsAdmis }}, {{ $rattrapage }}, {{ $redoublants }}, {{ $exclus }}],
                    backgroundColor: [
                        '#10b981', // Vert pour admis
                        '#f59e0b', // Jaune pour rattrapage  
                        '#f97316', // Orange pour redoublants
                        '#ef4444'  // Rouge pour exclus
                    ],
                    borderWidth: 3,
                    borderColor: isDarkMode ? '#111827' : '#ffffff',
                    hoverBorderWidth: 4,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '50%',
                plugins: {
                    title: {
                        display: true,
                        text: 'Répartition des Résultats Scolaires',
                        color: textColor,
                        padding: 20
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            color: textColor,
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return {
                                            text: `${label}: ${value} (${percentage}%)`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            pointStyle: 'circle',
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} étudiants (${percentage}%)`;
                            }
                        }
                    }
                },
                // Animation d'entrée éducative
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1500,
                    easing: 'easeOutBounce'
                }
            }
        });

        // ✅ Mise à jour dynamique des graphiques
        Livewire.on('dashboard-refreshed', () => {
            tauxReussiteChart.config.type = '{{ $selectedChartType }}';
            tauxReussiteChart.update('active');
            performanceChart.update('active');
        });

        // ✅ Animation au scroll pour les graphiques éducatifs
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.querySelector('canvas').style.opacity = '1';
                    entry.target.querySelector('canvas').style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.bg-white.dark\\:bg-gray-900').forEach(chart => {
            if (chart.querySelector('canvas')) {
                chart.querySelector('canvas').style.opacity = '0';
                chart.querySelector('canvas').style.transform = 'translateY(20px)';
                chart.querySelector('canvas').style.transition = 'all 0.6s ease-out';
                observer.observe(chart);
            }
        });
    });
</script>
@endpush