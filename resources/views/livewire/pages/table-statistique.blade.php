{{-- resources/views/livewire/pages/table-statistique.blade.php --}}
<div class="col-span-12 2xl:col-span-12">
    <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900 shadow-lg">
        {{-- Header avec filtres --}}
        <div class="p-5 pb-2 sm:p-6 sm:pb-2 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">
                    Statistiques par Niveau et Parcours
                </h6>
                
                {{-- Filtres et actions --}}
                <div class="flex items-center space-x-3">
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
                    $totalEtudiants = $statistiquesNiveaux->sum('etudiants_count');
                    $totalAdmis = $etudiantsAdmis;
                    $totalRedoublants = $redoublants;
                    $tauxGlobalReussite = $totalEtudiants > 0 ? round(($totalAdmis / $totalEtudiants) * 100, 1) : 0;
                @endphp
                
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalEtudiants) }}</div>
                    <div class="text-xs text-blue-600/70 dark:text-blue-400/70">Total Étudiants</div>
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
                </div>
            </div>
        </div>

        {{-- Contenu principal --}}
        <div class="p-5 sm:p-6">
            @if(($viewMode ?? 'table') === 'table')
                {{-- Vue Tableau Moderne --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-center space-x-2">
                                        <span>Niveau</span>
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </div>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300 hidden sm:table-cell">
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
                                <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700 dark:text-gray-300">
                                    Progression
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($statistiquesNiveaux as $niveau)
                                @php
                                    $totalNiveau = $niveau->etudiants_count ?? 0;
                                    $admisNiveau = round($totalNiveau * 0.7); // Simulation
                                    $redoublantsNiveau = round($totalNiveau * 0.2); // Simulation
                                    $tauxReussite = $totalNiveau > 0 ? round(($admisNiveau / $totalNiveau) * 100, 1) : 0;
                                    $couleurTaux = $tauxReussite >= 70 ? 'green' : ($tauxReussite >= 50 ? 'yellow' : 'red');
                                @endphp
                                
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                    {{-- Niveau --}}
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                                                {{ $niveau->abr }}
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ $niveau->nom }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    @if($niveau->is_concours)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
                                                            Concours
                                                        </span>
                                                    @endif
                                                    @if($niveau->has_rattrapage)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 ml-1">
                                                            Rattrapage
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Parcours --}}
                                    <td class="py-4 px-4 hidden sm:table-cell">
                                        <div class="space-y-1">
                                            @foreach($statistiquesParcours->where('niveau.id', $niveau->id)->take(2) as $parcours)
                                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $parcours->nom }}</div>
                                            @endforeach
                                            @if($statistiquesParcours->where('niveau.id', $niveau->id)->count() > 2)
                                                <div class="text-xs text-blue-600 dark:text-blue-400">
                                                    +{{ $statistiquesParcours->where('niveau.id', $niveau->id)->count() - 2 }} autres
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Total --}}
                                    <td class="py-4 px-4 hidden md:table-cell">
                                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($totalNiveau) }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">étudiants</div>
                                    </td>

                                    {{-- Admis --}}
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-2">
                                            <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($admisNiveau) }}</div>
                                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 min-w-[60px]">
                                                <div class="bg-green-500 h-2 rounded-full transition-all duration-500" 
                                                     style="width: {{ $tauxReussite }}%"></div>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $tauxReussite }}% de réussite</div>
                                    </td>

                                    {{-- Redoublants --}}
                                    <td class="py-4 px-4 hidden lg:table-cell">
                                        <div class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ number_format($redoublantsNiveau) }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">redoublants</div>
                                    </td>

                                    {{-- Progression --}}
                                    <td class="py-4 px-4">
                                        @php
                                            $progression = rand(-10, 20); // Simulation
                                        @endphp
                                        <div class="flex items-center space-x-2">
                                            @if($progression > 0)
                                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-green-600 dark:text-green-400 font-semibold">+{{ $progression }}%</span>
                                            @elseif($progression < 0)
                                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-red-600 dark:text-red-400 font-semibold">{{ $progression }}%</span>
                                            @else
                                                <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-gray-600 dark:text-gray-400 font-semibold">0%</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">vs année précédente</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center">
                                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-4.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                                        </svg>
                                        <p class="text-gray-500 dark:text-gray-400">Aucune donnée disponible</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                {{-- Vue Cartes --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($statistiquesNiveaux as $niveau)
                        @php
                            $totalNiveau = $niveau->etudiants_count ?? 0;
                            $admisNiveau = round($totalNiveau * 0.7);
                            $redoublantsNiveau = round($totalNiveau * 0.2);
                            $tauxReussite = $totalNiveau > 0 ? round(($admisNiveau / $totalNiveau) * 100, 1) : 0;
                        @endphp
                        
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition-all duration-300 group">
                            {{-- Header de la carte --}}
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                        {{ $niveau->abr }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 dark:text-white">{{ $niveau->nom }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($totalNiveau) }} étudiants</p>
                                    </div>
                                </div>
                                
                                {{-- Menu dropdown --}}
                                <div class="relative">
                                    <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Badges --}}
                            <div class="flex flex-wrap gap-2 mb-4">
                                @if($niveau->is_concours)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Concours
                                    </span>
                                @endif
                                @if($niveau->has_rattrapage)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                        </svg>
                                        Rattrapage
                                    </span>
                                @endif
                            </div>

                            {{-- Statistiques --}}
                            <div class="space-y-4">
                                {{-- Admis --}}
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Admis</span>
                                        <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($admisNiveau) }}</span>
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
                                        <span class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ number_format($redoublantsNiveau) }}</span>
                                    </div>
                                    @php $tauxRedoublement = $totalNiveau > 0 ? round(($redoublantsNiveau / $totalNiveau) * 100, 1) : 0; @endphp
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-orange-500 h-2 rounded-full transition-all duration-1000" style="width: {{ $tauxRedoublement }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $tauxRedoublement }}% redoublent</div>
                                </div>
                            </div>

                            {{-- Footer avec progression --}}
                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                @php $progression = rand(-10, 20); @endphp
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">vs année précédente</span>
                                    <div class="flex items-center space-x-1">
                                        @if($progression > 0)
                                            <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-xs font-semibold text-green-600 dark:text-green-400">+{{ $progression }}%</span>
                                        @elseif($progression < 0)
                                            <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-xs font-semibold text-red-600 dark:text-red-400">{{ $progression }}%</span>
                                        @else
                                            <svg class="w-3 h-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">0%</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-4.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Aucune donnée disponible</p>
                            <p class="text-gray-400 dark:text-gray-500 text-sm">Les statistiques apparaîtront ici une fois les données disponibles.</p>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Script pour l'interactivité --}}
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
});
</script>
@endpush