<div class="space-y-4">
    {{-- En-tête (inchangé) --}}
    <div class="bg-gradient-to-r from-slate-50 to-gray-50 dark:from-gray-900 dark:to-slate-950 border border-gray-200 dark:border-gray-700 rounded-lg shadow-md p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-primary-600 dark:bg-primary-700 rounded-lg shadow-sm">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Résultats PACES - Concours
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm flex items-center gap-2">
                        <span class="font-medium">{{ $anneeActive->libelle ?? 'N/A' }}</span>
                        <span class="text-slate-400">•</span>
                        <span class="font-medium">{{ $sessionActive->type ?? 'N/A' }}</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-primary-50 dark:bg-primary-900/20 rounded-lg px-3 py-2">
                <div class="text-center">
                    <div class="text-3xl font-black text-primary-700 dark:text-primary-400">{{ $parcoursPACES->count() }}</div>
                    <div class="text-xs text-slate-600 dark:text-slate-400 font-semibold">Parcours</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grille (cards sans loading individuel) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        @foreach($parcoursPACES as $parcours)
            <button type="button"
                    wire:click="selectionnerParcours({{ $parcours->id }})"
                    wire:key="parcours-{{ $parcours->id }}"
                    class="group relative overflow-hidden text-left p-4 rounded-lg border-2 bg-white dark:bg-gray-800 
                           border-gray-200 dark:border-gray-700
                           hover:border-primary-500 dark:hover:border-primary-400
                           hover:shadow-lg transition-all duration-200
                           focus:outline-none focus:ring-2 focus:ring-primary-500">
                
                {{-- Badge abréviation --}}
                <div class="absolute top-2 right-2">
                    <span class="px-2 py-1 rounded-md text-xs font-black bg-primary-600 dark:bg-primary-700 text-white shadow-sm">
                        {{ $parcours->abr }}
                    </span>
                </div>

                {{-- Contenu --}}
                <div class="pr-16">
                    <h4 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-3 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                        {{ $parcours->nom }}
                    </h4>

                    <div class="flex items-center gap-3 text-sm">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $parcours->etudiants_count }}</span>
                            <span class="text-slate-500 dark:text-slate-400">étudiants</span>
                        </div>
                    </div>

                    <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Quota</span>
                            <span class="font-bold text-primary-600 dark:text-primary-400">
                                {{ $parcours->quota_admission ?? '∞' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Icône flèche --}}
                <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-5 h-5 text-primary-500 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>

                {{-- Effet hover --}}
                <div class="absolute inset-0 bg-gradient-to-r from-primary-500/5 to-primary-600/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
            </button>
        @endforeach
    </div>
</div>