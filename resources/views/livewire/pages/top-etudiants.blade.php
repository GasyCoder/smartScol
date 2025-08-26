<div class="min-h-screen bg-gray-50 dark:bg-gray-950 font-body">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 shadow-sm border-b border-gray-200 dark:border-gray-800">
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Admis</p>
                        <div class="flex items-baseline mt-1">
                            <p class="text-2xl font-bold text-green-700">{{ number_format($etudiantsAdmis) }}</p>
                            @if($totalEtudiants > 0)
                                <span class="ml-2 text-sm font-medium text-green-600">
                                    {{ round(($etudiantsAdmis / $totalEtudiants) * 100, 1) }}%
                                </span>
                            @endif
                        </div>
                        @if($progressionAdmis != 0)
                            <div class="mt-1 text-xs {{ $progressionAdmis >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $progressionAdmis >= 0 ? '↗' : '↘' }} {{ abs($progressionAdmis) }}% vs précédent
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Redoublants -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Redoublants</p>
                        <div class="flex items-baseline mt-1">
                            <p class="text-2xl font-bold text-yellow-700">{{ number_format($redoublants) }}</p>
                            @if($totalEtudiants > 0)
                                <span class="ml-2 text-sm font-medium text-yellow-600">
                                    {{ round(($redoublants / $totalEtudiants) * 100, 1) }}%
                                </span>
                            @endif
                        </div>
                        @if($progressionRedoublants != 0)
                            <div class="mt-1 text-xs {{ $progressionRedoublants >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $progressionRedoublants >= 0 ? '↗' : '↘' }} {{ abs($progressionRedoublants) }}% vs précédent
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rattrapage -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Rattrapage</p>
                        <div class="flex items-baseline mt-1">
                            <p class="text-2xl font-bold text-cyan-700">{{ number_format($rattrapage) }}</p>
                            @if($totalEtudiants > 0)
                                <span class="ml-2 text-sm font-medium text-cyan-600">
                                    {{ round(($rattrapage / $totalEtudiants) * 100, 1) }}%
                                </span>
                            @endif
                        </div>
                        @if($progressionRattrapage != 0)
                            <div class="mt-1 text-xs {{ $progressionRattrapage >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $progressionRattrapage >= 0 ? '↗' : '↘' }} {{ abs($progressionRattrapage) }}% vs précédent
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Exclus -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Exclus</p>
                        <div class="flex items-baseline mt-1">
                            <p class="text-2xl font-bold text-red-700">{{ number_format($exclus) }}</p>
                            @if($totalEtudiants > 0)
                                <span class="ml-2 text-sm font-medium text-red-600">
                                    {{ round(($exclus / $totalEtudiants) * 100, 1) }}%
                                </span>
                            @endif
                        </div>
                        @if($progressionExclus != 0)
                            <div class="mt-1 text-xs {{ $progressionExclus >= 0 ? 'text-red-600' : 'text-green-600' }}">
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-heading font-semibold text-gray-800">Taux de Réussite</h3>
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                    </div>
                </div>
                @php
                    $tauxReussite = $totalEtudiants > 0 ? round(($etudiantsAdmis / $totalEtudiants) * 100, 1) : 0;
                @endphp
                <div class="flex items-end space-x-2">
                    <span class="text-3xl font-bold text-green-600">{{ $tauxReussite }}%</span>
                    <span class="text-sm text-gray-600 pb-1">{{ $etudiantsAdmis }}/{{ $totalEtudiants }}</span>
                </div>
                <div class="mt-3 bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ $tauxReussite }}%"></div>
                </div>
            </div>

            <!-- Taux d'Échec -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-heading font-semibold text-gray-800">Taux d'Échec</h3>
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                        </svg>
                    </div>
                </div>
                @php
                    $tauxEchec = $totalEtudiants > 0 ? round((($redoublants + $exclus) / $totalEtudiants) * 100, 1) : 0;
                @endphp
                <div class="flex items-end space-x-2">
                    <span class="text-3xl font-bold text-red-600">{{ $tauxEchec }}%</span>
                    <span class="text-sm text-gray-600 pb-1">{{ $redoublants + $exclus }}/{{ $totalEtudiants }}</span>
                </div>
                <div class="mt-3 bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-red-500 to-red-600 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ $tauxEchec }}%"></div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Évolution Mensuelle -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-heading font-semibold text-gray-900">Évolution Mensuelle</h3>
                    <div class="flex items-center space-x-3">
                        <select wire:model="selectedYear" wire:change="changeYear($event.target.value)" 
                                class="text-sm border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                            @foreach($anneesUniversitaires as $annee)
                                <option value="{{ $annee->id }}">{{ $annee->nom }}</option>
                            @endforeach
                        </select>
                        <div class="flex rounded-md overflow-hidden border border-gray-300">
                            <button wire:click="changeChartType('line')" 
                                    class="px-3 py-1 text-xs font-medium transition-colors {{ $selectedChartType === 'line' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                                Ligne
                            </button>
                            <button wire:click="changeChartType('bar')" 
                                    class="px-3 py-1 text-xs font-medium transition-colors {{ $selectedChartType === 'bar' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                                Barres
                            </button>
                        </div>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="evolutionChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <!-- Répartition Actuelle -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-heading font-semibold text-gray-900">Répartition des Résultats</h3>
                </div>
                <div class="h-80">
                    <canvas id="distributionChart" class="w-full h-full"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="w-4 h-4 bg-green-500 rounded-full mx-auto mb-1"></div>
                        <p class="text-xs text-gray-600">Admis</p>
                        <p class="font-semibold text-green-600">{{ $etudiantsAdmis }}</p>
                    </div>
                    <div class="text-center">
                        <div class="w-4 h-4 bg-red-500 rounded-full mx-auto mb-1"></div>
                        <p class="text-xs text-gray-600">Échecs</p>
                        <p class="font-semibold text-red-600">{{ $redoublants + $exclus }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
            const evolutionChart = new Chart(evolutionCtx, {
                type: '{{ $selectedChartType }}',
                data: {
                    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                    datasets: [
                        {
                            label: 'Admis',
                            data: @json($chartDataAdmis),
                            borderColor: '#18b38a',
                            backgroundColor: '#18b38a20',
                            tension: 0.4,
                            fill: false
                        },
                        {
                            label: 'Redoublants',
                            data: @json($chartDataRedoublants),
                            borderColor: '#f4bd0e',
                            backgroundColor: '#f4bd0e20',
                            tension: 0.4,
                            fill: false
                        },
                        {
                            label: 'Exclus',
                            data: @json($chartDataExclus),
                            borderColor: '#e85347',
                            backgroundColor: '#e8534720',
                            tension: 0.4,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            const distributionCtx = document.getElementById('distributionChart').getContext('2d');
            const distributionChart = new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Admis', 'Redoublants', 'Exclus', 'Rattrapage'],
                    datasets: [{
                        data: [{{ $etudiantsAdmis }}, {{ $redoublants }}, {{ $exclus }}, {{ $rattrapage }}],
                        backgroundColor: ['#18b38a', '#f4bd0e', '#e85347', '#09c2de'],
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverBorderWidth: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            Livewire.on('dashboard-refreshed', () => {
                evolutionChart.config.type = '{{ $selectedChartType }}';
                evolutionChart.update();
                distributionChart.update();
            });
        });
    </script>
</div>