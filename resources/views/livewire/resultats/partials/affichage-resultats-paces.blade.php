{{-- resources/views/livewire/resultats/partials/affichage-resultats-paces.blade.php --}}
<div class="space-y-6">
    {{-- ✅ OVERLAY LOADING (exports / appliquer) --}}
    <div wire:loading.flex
         wire:target="exporterPDF,exporterExcelPaces,appliquerDeliberation"
         class="fixed inset-0 bg-slate-900/80 dark:bg-black/85 backdrop-blur-sm z-[9999] items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl px-8 py-6 min-w-[320px] border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <svg class="animate-spin h-10 w-10 text-primary-600 dark:text-primary-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        <span wire:loading wire:target="exporterExcelPaces,exporterPDF">📊 Export en cours</span>
                        <span wire:loading wire:target="appliquerDeliberation">⚙️ Application en cours</span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span wire:loading wire:target="exporterExcelPaces">Génération Excel...</span>
                        <span wire:loading wire:target="exporterPDF">Génération PDF...</span>
                        <span wire:loading wire:target="appliquerDeliberation">Enregistrement des décisions...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 📊 DASHBOARD MEGA : TOUJOURS VISIBLE --}}
    @if($this->afficher_dashboard_mega)
        @include('livewire.resultats.partials.dashboard-mega-paces')
    @endif

    {{-- ⚙️ PARAMÈTRES SIMULATION --}}
    @include('livewire.resultats.partials.parametre-simulation')

    {{-- 🔀 AFFICHAGE CONDITIONNEL : Tableau OU Message --}}
    @if($afficherTableau)
        {{-- ========================================= --}}
        {{-- 📋 TABLEAU DÉTAILLÉ (après application)  --}}
        {{-- ========================================= --}}
        <div class="space-y-4">
            {{-- Barre d'actions sticky --}}
            <div class="sticky top-2 z-40">
                <div class="bg-white/90 dark:bg-gray-900/80 backdrop-blur rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg px-3 py-2">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                        {{-- Filtres décision --}}
                        @include('livewire.resultats.partials.filtres-decisions')

                        <div class="flex-1"></div>

                        {{-- Recherche --}}
                        <div class="flex items-center gap-2">
                            <input type="search" wire:model.live.debounce.300ms="recherche"
                                   placeholder="Rechercher (matricule, nom, prénom)…"
                                   class="w-56 lg:w-64 rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500" />
                            <button wire:click="reinitialiserRecherche"
                                    class="px-3 py-1.5 rounded-lg text-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800">
                                Réinitialiser
                            </button>
                        </div>

                        {{-- Exports --}}
                        <div class="flex items-center gap-2">
                            <button 
                                wire:click="exporterExcelPaces"
                                wire:loading.attr="disabled"
                                class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold
                                       bg-green-600 hover:bg-green-700 text-white shadow-md disabled:opacity-60">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                Excel
                            </button>

                            <button 
                                wire:click="exporterPDF"
                                wire:loading.attr="disabled"
                                class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold
                                       bg-red-600 hover:bg-red-700 text-white shadow-md disabled:opacity-60">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                                </svg>
                                PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tableau résultats --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                @php
                    $config = match($filtreDecision) {
                        'admis' => ['titre' => 'ADMIS', 'couleur' => 'green', 'icone' => '✓'],
                        'redoublant' => ['titre' => 'AUTORISÉS À REDOUBLER', 'couleur' => 'orange', 'icone' => '↻'],
                        'exclus' => ['titre' => 'EXCLUS', 'couleur' => 'red', 'icone' => '✗'],
                        default => ['titre' => 'Tous les résultats', 'couleur' => 'blue', 'icone' => '📋']
                    };
                @endphp

                {{-- Header tableau --}}
                <div class="bg-gradient-to-r from-{{ $config['couleur'] }}-50 to-{{ $config['couleur'] }}-100 dark:from-{{ $config['couleur'] }}-900/20 dark:to-{{ $config['couleur'] }}-900/10 px-6 py-4 border-b border-{{ $config['couleur'] }}-200 dark:border-{{ $config['couleur'] }}-800">
                    <div class="flex items-center gap-3">
                        <div class="text-3xl">{{ $config['icone'] }}</div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-{{ $config['couleur'] }}-700 dark:text-{{ $config['couleur'] }}-300">
                                {{ $config['titre'] }}
                            </h3>
                            <p class="text-sm text-{{ $config['couleur'] }}-600 dark:text-{{ $config['couleur'] }}-400">
                                {{ $this->resultats_pagines->total() }} étudiant(s)
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Pagination controls --}}
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Afficher :</label>
                            <select wire:model.live="perPage" 
                                    class="rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                @foreach($perPageOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            <span class="text-sm text-gray-600 dark:text-gray-400">par page</span>
                        </div>

                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            @if($this->resultats_pagines->total() > 0)
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $this->infos_pagination['de'] }} - {{ $this->infos_pagination['a'] }}
                                </span>
                                sur
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $this->infos_pagination['total'] }}
                                </span>
                                résultats
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Contenu tableau --}}
                <div class="p-6">
                    @if($this->resultats_pagines->total() > 0)
                        @include('livewire.resultats.partials.tableau-resultats-paces', [
                            'resultats' => $this->resultats_pagines->items(),
                            'couleur' => $config['couleur'],
                            'uesStructure' => $uesStructure
                        ])
                        <div class="mt-6">
                            {{ $this->resultats_pagines->links() }}
                        </div>
                    @else
                        <div class="text-center py-16">
                            <svg class="w-20 h-20 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xl font-medium text-gray-500 dark:text-gray-400">Aucun résultat</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>