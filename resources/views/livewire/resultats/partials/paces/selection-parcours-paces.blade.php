<div class="space-y-6">
    {{-- En-tête compact et élégant --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-gradient-to-br from-primary-600 to-primary-700 rounded-lg shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                    Résultats PACES
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <span>{{ $anneeActive->libelle ?? 'N/A' }}</span>
                    <span>•</span>
                    <span>{{ $sessionActive->type ?? 'N/A' }}</span>
                </p>
            </div>
        </div>
        
        <div class="text-right">
            <div class="text-2xl font-black text-primary-600 dark:text-primary-400">{{ $parcoursPACES->count() }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">parcours disponibles</div>
        </div>
    </div>

    {{-- Grille des parcours - Design sublime et compact --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($parcoursPACES as $parcours)
            @php
                $stats = $this->getStatistiquesParcours($parcours->id);
                $estDelibere = $this->parcours_deliberes[$parcours->id] ?? false;
            @endphp

            <button type="button"
                    wire:click="selectionnerParcours({{ $parcours->id }})"
                    wire:key="parcours-{{ $parcours->id }}"
                    class="group relative overflow-hidden text-left rounded-xl border bg-white dark:bg-gray-800 
                           border-gray-200 dark:border-gray-700
                           hover:border-primary-400 dark:hover:border-primary-500
                           hover:shadow-xl transition-all duration-300
                           focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                
                {{-- Header avec nom et badges --}}
                <div class="p-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <h4 class="text-base font-bold text-gray-900 dark:text-gray-100 line-clamp-2 flex-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                            {{ $parcours->nom }}
                        </h4>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-primary-600 dark:bg-primary-700 text-white shadow-sm shrink-0">
                            {{ $parcours->abr }}
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-2">
                    @if($estDelibere)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 shadow-sm w-fit">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Délibération appliquée
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 w-fit">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            En attente
                        </span>
                    @endif
                    </div>
                </div>

                {{-- Statistiques compactes --}}
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        {{-- Inscrits --}}
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-blue-50 dark:bg-blue-900/10">
                            <div class="p-1.5 rounded bg-blue-100 dark:bg-blue-900/30">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">Inscrits</div>
                                <div class="text-lg font-black text-blue-700 dark:text-blue-300">{{ $stats['total_inscrits'] }}</div>
                            </div>
                        </div>

                        {{-- Présents --}}
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/10">
                            <div class="p-1.5 rounded bg-emerald-100 dark:bg-emerald-900/30">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Présents</div>
                                <div class="text-lg font-black text-emerald-700 dark:text-emerald-300">{{ $stats['presents'] }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Stats secondaires en ligne --}}
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                            <span class="text-gray-600 dark:text-gray-400">Nouveaux:</span>
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $stats['nouveaux'] }}</span>
                        </div>
                        
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                            <span class="text-gray-600 dark:text-gray-400">Redoub:</span>
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $stats['redoublants'] }}</span>
                        </div>
                        
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full bg-red-500"></div>
                            <span class="text-gray-600 dark:text-gray-400">Absents:</span>
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $stats['absents'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Barre de progression taux de présence --}}
                <div class="px-4 pb-4">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">Taux de présence</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['taux_presence'] }}%</span>
                    </div>
                    <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full transition-all duration-500"
                             style="width: {{ $stats['taux_presence'] }}%"></div>
                    </div>
                </div>

                {{-- Hover effect --}}
                <div class="absolute inset-0 bg-gradient-to-br from-primary-500/0 to-primary-600/0 group-hover:from-primary-500/5 group-hover:to-primary-600/10 transition-all duration-300 pointer-events-none rounded-xl"></div>
                
                {{-- Arrow icon --}}
                <div class="absolute top-1/2 -translate-y-1/2 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-1">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </button>
        @endforeach
    </div>
</div>