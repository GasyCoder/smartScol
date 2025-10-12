{{-- resources/views/livewire/resultats/partials/affichage-resultats-paces.blade.php --}}
<div class="space-y-6">
    
    {{-- ✅ OVERLAY LOADING (exports uniquement, plus de simulation ici) --}}
    <div wire:loading.flex
         wire:target="exporterPDF,exporterExcelPaces"
         class="fixed inset-0 bg-slate-900/80 dark:bg-black/85 backdrop-blur-sm z-[9999] items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl px-8 py-6 min-w-[320px] border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <svg class="animate-spin h-10 w-10 text-primary-600 dark:text-primary-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        📊 Export en cours
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span wire:loading wire:target="exporterExcelPaces">Génération Excel...</span>
                        <span wire:loading wire:target="exporterPDF">Génération PDF...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 📊 DASHBOARD MEGA : TOUJOURS VISIBLE --}}
    @if($this->afficher_dashboard_mega)
        @include('livewire.resultats.partials.paces.dashboard-mega-paces')
    @endif

    {{-- ========================================= --}}
    {{-- 🎯 AFFICHAGE CONDITIONNEL                --}}
    {{-- ========================================= --}}
    
    @if($afficherTableau)
        {{-- ===================================================== --}}
        {{-- ✅ CAS 1 : DÉLIBÉRATION APPLIQUÉE → Tableau complet --}}
        {{-- ===================================================== --}}
        <div class="space-y-4" wire:key="resultats-container-{{ $resultatsVersion }}">
            
            {{-- 🎉 Badge info délibération --}}
            @if($derniereDeliberation)
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border-2 border-emerald-300 dark:border-emerald-700 rounded-xl px-5 py-3.5 shadow-sm">
                    <div class="flex items-center gap-3 text-sm">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1 flex flex-wrap items-center gap-x-2.5 gap-y-1">
                            <span class="font-bold text-emerald-700 dark:text-emerald-300 text-base">
                                ✅ Délibération appliquée
                            </span>
                            <span class="text-emerald-600/50 dark:text-emerald-400/50">•</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-medium">
                                {{ $derniereDeliberation->applique_at->format('d/m/Y à H:i') }}
                            </span>
                            <span class="text-emerald-600/50 dark:text-emerald-400/50">•</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-medium">
                                {{ $derniereDeliberation->utilisateur->name ?? 'Système' }}
                            </span>
                        </div>
                        {{-- Critères appliqués --}}
                        <div class="flex items-center gap-2 text-xs">
                            <span class="px-2 py-1 bg-white/60 dark:bg-emerald-900/40 rounded-md border border-emerald-300/50 dark:border-emerald-700/50">
                                <span class="font-semibold text-emerald-700 dark:text-emerald-300">Quota:</span>
                                <span class="text-emerald-600 dark:text-emerald-400 ml-1">{{ $derniereDeliberation->quota_admission ?? '∞' }}</span>
                            </span>
                            <span class="px-2 py-1 bg-white/60 dark:bg-emerald-900/40 rounded-md border border-emerald-300/50 dark:border-emerald-700/50">
                                <span class="font-semibold text-emerald-700 dark:text-emerald-300">Moy:</span>
                                <span class="text-emerald-600 dark:text-emerald-400 ml-1">{{ $derniereDeliberation->moyenne_requise }}/20</span>
                            </span>
                            <span class="px-2 py-1 bg-white/60 dark:bg-emerald-900/40 rounded-md border border-emerald-300/50 dark:border-emerald-700/50">
                                <span class="font-semibold text-emerald-700 dark:text-emerald-300">Crédits:</span>
                                <span class="text-emerald-600 dark:text-emerald-400 ml-1">{{ $derniereDeliberation->credits_requis }}/60</span>
                            </span>
                        </div>
                    </div>
                </div>
            @endif
            
            {{-- 🎛️ Barre d'actions sticky --}}
            <div class="sticky top-2 z-40">
                <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg px-5 py-3.5">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                        {{-- Filtres décision --}}
                        @include('livewire.resultats.partials.paces.filtres-decisions')

                        <div class="flex-1"></div>

                        {{-- Recherche --}}
                        <div class="flex items-center gap-2">
                            <div class="relative">
                                <input type="search" 
                                       wire:model.live.debounce.300ms="recherche"
                                       placeholder="Rechercher (matricule, nom, prénom)…"
                                       class="w-56 lg:w-72 pl-10 pr-4 py-2.5 rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors shadow-sm" />
                                <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            @if(!empty($recherche))
                                <button wire:click="reinitialiserRecherche"
                                        class="p-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                        title="Effacer la recherche">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                        </div>

                        {{-- Exports --}}
                        <div class="flex items-center gap-2">
                            <button 
                                wire:click="exporterExcelPaces"
                                wire:loading.attr="disabled"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold
                                       bg-green-600 hover:bg-green-700 active:bg-green-800 text-white shadow-md 
                                       disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                Excel
                            </button>

                            <button 
                                wire:click="exporterPDF"
                                wire:loading.attr="disabled"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold
                                       bg-red-600 hover:bg-red-700 active:bg-red-800 text-white shadow-md 
                                       disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                                </svg>
                                PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 📋 Tableau résultats --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700"
                 wire:key="tableau-{{ $filtreDecision }}-{{ $resultatsVersion }}">
                @php
                    $config = match($filtreDecision) {
                        'admis' => ['titre' => 'ADMIS', 'couleur' => 'green', 'icone' => '✓', 'gradient' => 'from-green-500 to-emerald-600'],
                        'redoublant' => ['titre' => 'AUTORISÉS À REDOUBLER', 'couleur' => 'orange', 'icone' => '↻', 'gradient' => 'from-orange-500 to-amber-600'],
                        'exclus' => ['titre' => 'EXCLUS', 'couleur' => 'red', 'icone' => '✗', 'gradient' => 'from-red-500 to-rose-600'],
                        default => ['titre' => 'Tous les résultats', 'couleur' => 'blue', 'icone' => '📋', 'gradient' => 'from-blue-500 to-indigo-600']
                    };
                @endphp

                {{-- Header tableau --}}
                <div class="bg-gradient-to-r {{ $config['gradient'] }} px-6 py-5 border-b-2 border-{{ $config['couleur'] }}-300 dark:border-{{ $config['couleur'] }}-800">
                    <div class="flex items-center gap-4">
                        <div class="text-4xl drop-shadow-sm">{{ $config['icone'] }}</div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-black text-white drop-shadow-sm">
                                {{ $config['titre'] }}
                            </h3>
                            <p class="text-sm text-white/90 font-medium">
                                {{ $this->resultats_pagines->total() }} étudiant(s)
                                @if($filtreDecision !== 'tous')
                                    • 
                                    @php
                                        $pct = $statistiquesDetailes['total_presents'] > 0 
                                            ? round(($this->resultats_pagines->total() / $statistiquesDetailes['total_presents']) * 100, 1) 
                                            : 0;
                                    @endphp
                                    {{ $pct }}% des présents
                                @endif
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
                        @include('livewire.resultats.partials.paces.tableau-resultats-paces', [
                            'resultats' => $this->resultats_pagines->items(),
                            'couleur' => $config['couleur'],
                            'uesStructure' => $uesStructure
                        ])
                        <div class="mt-6">
                            {{ $this->resultats_pagines->links() }}
                        </div>
                    @else
                        <div class="text-center py-20">
                            <svg class="w-24 h-24 mx-auto mb-6 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <p class="text-2xl font-bold text-gray-500 dark:text-gray-400 mb-3">
                                Aucun résultat {{ $filtreDecision !== 'tous' ? 'dans cette catégorie' : 'trouvé' }}
                            </p>
                            @if(!empty($recherche))
                                <p class="text-sm text-gray-400 dark:text-gray-500 mb-6">
                                    Recherche active : <strong class="text-gray-600 dark:text-gray-300">"{{ $recherche }}"</strong>
                                </p>
                                <button wire:click="reinitialiserRecherche"
                                        class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 active:bg-primary-800 transition-all text-sm font-bold shadow-md">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Effacer la recherche
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

    @else
        {{-- =========================================================== --}}
        {{-- ✅ CAS 2 : PAS ENCORE DÉLIBÉRÉ → CTA Lancer la simulation --}}
        {{-- =========================================================== --}}
         @include('livewire.resultats.partials.paces.non-simule')
    @endif
</div>

<style>
    /* Animation pour le bouton CTA */
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    .group:hover svg {
        animation: float 2s ease-in-out infinite;
    }
</style>