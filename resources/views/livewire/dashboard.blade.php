{{-- resources/views/livewire/dashboard.blade.php --}}
<div class="space-y-6">

    @if(auth()->user()->hasRole('secretaire'))
        @livewire('secretaire-dashboard')
    @else 
    {{-- Header du Dashboard --}}
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg">
        <div class="p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold font-heading -tracking-snug leading-tighter">
                        Tableau de Bord Académique
                    </h1>
                    <p class="text-blue-100 mt-2">
                        Année Universitaire {{ $anneeActive?->libelle ?? 'Non définie' }}
                        @if($sessionDeliberee)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                                Session délibérée
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                                Session en cours
                            </span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    {{-- Sélecteur d'année --}}
                    <div class="relative">
                        <select wire:model.live="selectedYear" 
                                class="bg-white bg-opacity-20 border border-white border-opacity-30 rounded-md px-3 py-2 text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50">
                            @foreach($anneesUniversitaires as $annee)
                                <option value="{{ $annee->id }}" class="text-gray-900">
                                    {{ $annee->date_start->format('Y') }}-{{ $annee->date_end->format('Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Bouton refresh --}}
                    <button wire:click="refresh" 
                            class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-md p-2 transition-all duration-200"
                            wire:loading.class="opacity-50 cursor-not-allowed">
                        <svg wire:loading.remove wire:target="refresh" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <svg wire:loading wire:target="refresh" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            {{-- Indicateurs rapides --}}
            <div class="mt-4 flex items-center space-x-6">
                <div class="text-sm">
                    <span class="text-blue-200">Dernière mise à jour:</span>
                    <span class="font-medium">{{ now()->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="text-sm">
                    <span class="text-blue-200">Total étudiants:</span>
                    <span class="font-bold">{{ number_format($totalEtudiants) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Cartes de Statistiques Principales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        {{-- Total Étudiants --}}
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 hover:shadow-lg transition-shadow dark:bg-gray-950 dark:border-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Étudiants</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($totalEtudiants) }}</p>
                    @if($progressionEtudiants > 0)
                        <p class="text-green-600 text-sm mt-1">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                +{{ $progressionEtudiants }}%
                            </span>
                        </p>
                    @elseif($progressionEtudiants < 0)
                        <p class="text-red-600 text-sm mt-1">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $progressionEtudiants }}%
                            </span>
                        </p>
                    @endif
                </div>
                <div class="bg-blue-100 p-3 rounded-full dark:bg-blue-900">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Étudiants Admis --}}
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 hover:shadow-lg transition-shadow dark:bg-gray-950 dark:border-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Étudiants Admis</h3>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($etudiantsAdmis) }}</p>
                    @if($progressionAdmis != 0)
                        <p class="text-{{ $progressionAdmis > 0 ? 'green' : 'red' }}-600 text-sm mt-1">
                            {{ $progressionAdmis > 0 ? '+' : '' }}{{ $progressionAdmis }}%
                        </p>
                    @endif
                </div>
                <div class="bg-green-100 p-3 rounded-full dark:bg-green-900">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Redoublants --}}
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 hover:shadow-lg transition-shadow dark:bg-gray-950 dark:border-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Redoublants</h3>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($redoublants) }}</p>
                    @if($progressionRedoublants != 0)
                        <p class="text-{{ $progressionRedoublants > 0 ? 'red' : 'green' }}-600 text-sm mt-1">
                            {{ $progressionRedoublants > 0 ? '+' : '' }}{{ $progressionRedoublants }}%
                        </p>
                    @endif
                </div>
                <div class="bg-blue-100 p-3 rounded-full dark:bg-blue-900">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Rattrapage --}}
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 hover:shadow-lg transition-shadow dark:bg-gray-950 dark:border-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Rattrapage</h3>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">{{ number_format($rattrapage) }}</p>
                    @if($progressionRattrapage != 0)
                        <p class="text-{{ $progressionRattrapage > 0 ? 'red' : 'green' }}-600 text-sm mt-1">
                            {{ $progressionRattrapage > 0 ? '+' : '' }}{{ $progressionRattrapage }}%
                        </p>
                    @endif
                </div>
                <div class="bg-yellow-100 p-3 rounded-full dark:bg-yellow-900">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Exclus --}}
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 hover:shadow-lg transition-shadow dark:bg-gray-950 dark:border-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Exclus</h3>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($exclus) }}</p>
                    @if($progressionExclus != 0)
                        <p class="text-{{ $progressionExclus > 0 ? 'red' : 'green' }}-600 text-sm mt-1">
                            {{ $progressionExclus > 0 ? '+' : '' }}{{ $progressionExclus }}%
                        </p>
                    @endif
                </div>
                <div class="bg-red-100 p-3 rounded-full dark:bg-red-900">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenu principal du dashboard --}}
    <div class="grid grid-cols-12 grid-flow-dense gap-7">
        {{-- Graphiques --}}
        @include('livewire.pages.graphiques')
        
        {{-- Tableau des statistiques --}}
        @include('livewire.pages.table-statistique')
        
        {{-- Top étudiants --}}
        @include('livewire.pages.top-etudiants')
    </div>

    {{-- Modal de chargement --}}
    @if($refreshing)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-sm mx-auto">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="text-gray-700">Actualisation des données...</span>
                </div>
            </div>
        </div>
    @endif
      @endif
</div>

@push('scripts')
    @vite(['resources/dashwin/js/charts.js'])
    <script type="module">
        // Vos graphiques existants avec données dynamiques

        // Graphique en ligne - Statistiques des résultats avec VOS données réelles
        var salesStatistics = {
            labels : ["01 Jan", "02 Jan", "03 Jan", "04 Jan", "05 Jan", "06 Jan", "07 Jan", "08 Jan", "09 Jan", "10 Jan", "11 Jan", "12 Jan","13 Jan", "14 Jan", "15 Jan", "16 Jan", "17 Jan", "18 Jan", "19 Jan", "20 Jan", "21 Jan", "22 Jan", "23 Jan", "24 Jan", "25 Jan", "26 Jan", "27 Jan", "28 Jan", "29 Jan", "30 Jan"],
            dataUnit : 'Étudiants',
            lineTension : .4,
            datasets : [{
                label : "Admis - Session normale",
                color : "#6576ff",
                dash : [0,0],
                background : hexRGB('#6576ff',.15),
                data: {!! json_encode($chartDataAdmis) !!}
            },{
                label : "Exclus - Session normale",
                color : "#eb6459",
                dash : [5,5],
                background : "transparent",
                data: {!! json_encode($chartDataExclus) !!}
            },{
                label : "Admis - Rattrapage",
                color : "#10b981",
                dash : [0,0],
                background : "transparent",
                data: {!! json_encode($chartDataRattrapage) !!}
            },{
                label : "Exclus - Rattrapage",
                color : "#f59e0b",
                dash : [3,3],
                background : "transparent",
                data: {!! json_encode($chartDataRedoublants) !!}
            }]
        };
        
        // Utiliser votre fonction Line existante
        Line({selector:'#salesStatistics', data:salesStatistics, tooltip: "tooltipDark", scales: "scales2" });

        // Graphique en anneau - Répartition avec VOS données réelles
        var orderStatistics = {
            labels : ["Admis", "Redoublants", "Exclus"],
            dataUnit : 'Étudiants',
            legend: false,
            datasets : [{
                borderColor : "#fff",
                background : ["#816bff","#13c9f2","#ff82b7"],
                data: [{{ $etudiantsAdmis ?? 0 }}, {{ $redoublants ?? 0 }}, {{ $exclus ?? 0 }}]
            }]
        };
        
        // Utiliser votre fonction Doughnut existante
        Doughnut({selector:'#orderStatistics', data:orderStatistics, tooltip: "tooltipDark"});

        // Event Listeners pour l'interactivité Livewire
        document.addEventListener('livewire:initialized', () => {
            @this.on('dashboard-refreshed', () => {
                // Recharger les graphiques après refresh
                setTimeout(() => {
                    location.reload();
                }, 500);
            });
            
            @this.on('top-etudiants-refreshed', () => {
                // Actions spécifiques pour top étudiants
                console.log('Top étudiants actualisés');
            });
        });

        // Auto-refresh toutes les 5 minutes (optionnel)
        setInterval(() => {
            console.log('Auto-refresh dashboard...');
            @this.call('refresh');
        }, 300000); // 5 minutes

    </script>
@endpush