{{-- ===== FILTRES + RECHERCHE SUR LA MÊME LIGNE ===== --}}
<div class="p-4">
    {{-- ✅ NOUVEAU : Bandeau info si simulation active --}}
    @if($simulationEnCours)
        <div class="mb-3 p-3 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-l-4 border-amber-500 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-bold text-amber-800 dark:text-amber-300">
                        💡 Résultats simulés affichés
                    </p>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                        Utilisez les filtres pour explorer les décisions. Les données ne sont pas encore sauvegardées.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        
{{-- GAUCHE: Filtres avec loading à droite --}}
<div class="flex flex-wrap items-center gap-2">
    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 mr-2">Filtrer:</span>
    
    @php
        $totalResultats = ($statistiquesDetailes['admis'] ?? 0) + 
                        ($statistiquesDetailes['redoublant_autorises'] ?? 0) + 
                        ($statistiquesDetailes['exclus'] ?? 0);
    @endphp

    {{-- Tous --}}
    <button wire:click="changerFiltre('tous')"
            wire:loading.attr="disabled"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all disabled:opacity-70 disabled:cursor-wait
                {{ $filtreDecision === 'tous' 
                    ? 'bg-primary-600 text-white shadow-md' 
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
        <span class="flex items-center gap-2">
            <span class="flex items-center gap-1">
                Tous <span class="font-bold">{{ $totalResultats }}</span>
                @if($totalResultats > 520)
                    <span class="text-xs">⚠️</span>
                @endif
            </span>
            {{-- Loading spinner à droite --}}
            <svg wire:loading wire:target="changerFiltre('tous')" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
    </button>
    
    {{-- Admis --}}
    <button wire:click="changerFiltre('admis')"
            wire:loading.attr="disabled"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all disabled:opacity-70 disabled:cursor-wait relative
                {{ $filtreDecision === 'admis' 
                    ? 'bg-green-600 text-white shadow-md ring-2 ring-green-300' 
                    : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 hover:bg-green-200' }}">
        <span class="flex items-center gap-2">
            <span class="flex items-center gap-1">
                Admis <span class="font-bold">{{ $statistiquesDetailes['admis'] ?? 0 }}</span>
            </span>
            {{-- Loading spinner à droite --}}
            <svg wire:loading wire:target="changerFiltre('admis')" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
        {{-- Badge "Recommandé" --}}
        @if($simulationEnCours && $filtreDecision !== 'admis')
            <span class="absolute -top-2 -right-2 px-1.5 py-0.5 bg-yellow-400 text-yellow-900 text-xs font-black rounded-full shadow-md animate-pulse">
                ⚡
            </span>
        @endif
    </button>
    
    {{-- Redoublants --}}
    <button wire:click="changerFiltre('redoublant')"
            wire:loading.attr="disabled"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all disabled:opacity-70 disabled:cursor-wait
                {{ $filtreDecision === 'redoublant' 
                    ? 'bg-orange-600 text-white shadow-md' 
                    : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 hover:bg-orange-200' }}">
        <span class="flex items-center gap-2">
            <span class="flex items-center gap-1">
                Redoublants <span class="font-bold">{{ $statistiquesDetailes['redoublant_autorises'] ?? 0 }}</span>
            </span>
            {{-- Loading spinner à droite --}}
            <svg wire:loading wire:target="changerFiltre('redoublant')" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
    </button>
    
    {{-- Exclus --}}
    <button wire:click="changerFiltre('exclus')"
            wire:loading.attr="disabled"
            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all disabled:opacity-70 disabled:cursor-wait
                {{ $filtreDecision === 'exclus' 
                    ? 'bg-red-600 text-white shadow-md' 
                    : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-200' }}">
        <span class="flex items-center gap-2">
            <span class="flex items-center gap-1">
                Exclus <span class="font-bold">{{ $statistiquesDetailes['exclus'] ?? 0 }}</span>
            </span>
            {{-- Loading spinner à droite --}}
            <svg wire:loading wire:target="changerFiltre('exclus')" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
    </button>
</div>
        {{-- DROITE: Recherche (inchangé) --}}
        <div class="flex items-center gap-2 lg:max-w-md">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="recherche"
                       placeholder="Matricule, nom, prénom..."
                       class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
            </div>

            @if(!empty($recherche))
                <button wire:click="reinitialiserRecherche"
                        class="p-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition"
                        title="Effacer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif

            @if(!empty($recherche))
                <span class="inline-flex items-center px-2.5 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-lg text-xs font-semibold whitespace-nowrap">
                    {{ $this->resultats_pagines->total() }}
                </span>
            @endif
        </div>
    </div>

    {{-- Message si aucun résultat (compact) --}}
    @if(!empty($recherche) && $this->resultats_pagines->total() === 0)
        <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                    Aucun résultat pour "{{ $recherche }}"
                </p>
            </div>
        </div>
    @endif
</div>