{{-- resources/views/livewire/resultats/partials/action-buttons.blade.php --}}
<div class="col-span-1 sm:col-span-2 lg:col-span-4 mt-3">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">

        {{-- ⚡ SIMULER --}}
        <button
            type="button"
            wire:click="simulerDeliberation"
            wire:loading.attr="disabled"
            wire:target="simulerDeliberation"
            @disabled($modeRapide)
            class="group relative px-4 py-2 rounded-lg text-sm font-bold text-white
                   bg-primary-600 hover:bg-primary-700 active:bg-primary-800
                   disabled:opacity-60 disabled:cursor-not-allowed transition-colors shadow-sm">
            <span class="flex items-center justify-center gap-2">
                <svg wire:loading.remove wire:target="simulerDeliberation" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <svg wire:loading wire:target="simulerDeliberation" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Simuler</span>
            </span>
            @if($simulationEnCours)
                <div class="absolute -top-1 -right-1 w-4 h-4 bg-amber-400 rounded-full animate-pulse"></div>
            @endif
        </button>

        {{-- ✅ Appliquer / Annuler --}}
        @if($simulationEnCours)
            <div class="grid grid-cols-2 gap-2">
                <button type="button"
                        wire:click="appliquerDeliberation"
                        wire:loading.attr="disabled"
                        wire:target="appliquerDeliberation"
                        class="relative px-3 py-2 rounded-lg text-sm font-bold text-white
                               bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800
                               disabled:opacity-60 disabled:cursor-not-allowed transition-colors shadow-sm">
                    <span class="flex items-center justify-center gap-1.5">
                        <svg wire:loading.remove wire:target="appliquerDeliberation" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg wire:loading wire:target="appliquerDeliberation" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Appliquer</span>
                    </span>
                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full animate-ping"></div>
                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full"></div>
                </button>

                <button type="button"
                        wire:click="annulerSimulation"
                        wire:loading.attr="disabled"
                        wire:target="annulerSimulation"
                        class="px-3 py-2 rounded-lg text-sm font-semibold
                               bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300
                               hover:bg-gray-200 dark:hover:bg-gray-600
                               disabled:opacity-60 disabled:cursor-not-allowed
                               transition-colors border border-gray-300 dark:border-gray-600">
                    <span class="flex items-center justify-center gap-1.5">
                        <svg wire:loading.remove wire:target="annulerSimulation" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <svg wire:loading wire:target="annulerSimulation" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Annuler</span>
                    </span>
                </button>
            </div>
        @else
            <button type="button" disabled
                    class="px-4 py-2 rounded-lg text-sm font-semibold
                           bg-gray-100 dark:bg-gray-700/30 text-gray-400 dark:text-gray-500
                           cursor-not-allowed border border-gray-200 dark:border-gray-600">
                <span class="flex items-center justify-center gap-2 opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Appliquer
                </span>
            </button>
        @endif
    </div>

    @if($simulationEnCours)
        <div class="mt-2 px-3 py-1.5 rounded-lg bg-amber-50/80 dark:bg-amber-900/10 border border-amber-200/50 dark:border-amber-800/50">
            <p class="text-xs text-amber-700 dark:text-amber-300 flex items-center gap-1.5">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Simulation active :</strong> Vérifiez les stats puis cliquez “Appliquer”.</span>
            </p>
        </div>
    @endif
</div>
