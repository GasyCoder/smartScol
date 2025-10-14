{{-- =========================================================== --}}
{{-- ✅ SIMULATION DÉLIBÉRATION - Interface slim & moderne       --}}
{{-- =========================================================== --}}

<div class="relative bg-gradient-to-br from-white to-primary-50/20 dark:from-gray-900 dark:to-primary-950/10 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-lg">
    
    {{-- Pattern subtil --}}
    <div class="absolute inset-0 opacity-[0.02] dark:opacity-[0.03] pointer-events-none">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <pattern id="grid" width="32" height="32" patternUnits="userSpaceOnUse">
                <path d="M 32 0 L 0 0 0 32" fill="none" stroke="currentColor" stroke-width="0.5"/>
            </pattern>
            <rect width="100%" height="100%" fill="url(#grid)"/>
        </svg>
    </div>

    <div class="relative px-6 py-8">
        <div class="max-w-4xl mx-auto">
            
            {{-- Header --}}
            <div class="text-center mb-7">
                <div class="inline-flex items-center justify-center w-16 h-16 mb-4 bg-gradient-to-br from-primary-500 to-indigo-600 dark:from-primary-600 dark:to-indigo-700 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                    Simuler la délibération
                </h2>
                
                <p class="text-sm text-gray-600 dark:text-gray-400 max-w-xl mx-auto">
                    Prévisualisez l'impact de vos critères avant validation définitive
                </p>
            </div>

            {{-- Features Grid --}}
            <div class="grid sm:grid-cols-3 gap-4 mb-7">
                
                {{-- Feature 1 --}}
                <div class="group bg-white dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-md transition-all">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 mb-1">Configurez</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-tight">
                                Quota, crédits, moyenne, éliminatoire
                            </p>
                        </div>
                    </div>
                </div>
                
                {{-- Feature 2 --}}
                <div class="group bg-white dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:border-amber-300 dark:hover:border-amber-600 hover:shadow-md transition-all">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 mb-1">Visualisez</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-tight">
                                Dashboard temps réel avec stats
                            </p>
                        </div>
                    </div>
                </div>
                
                {{-- Feature 3 --}}
                <div class="group bg-white dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:border-emerald-300 dark:hover:border-emerald-600 hover:shadow-md transition-all">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 mb-1">Appliquez</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-tight">
                                Validez quand résultat ok
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CTA Buttons --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-5">
                
                {{-- Bouton Principal --}}
                <a href="{{ route('resultats.paces-deliberation', ['parcoursSlug' => $parcoursSlug]) }}"
                   class="group inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all">
                    <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Lancer la simulation</span>
                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>

                {{-- Bouton Cache avec gestion du loading --}}
                <button type="button"
                        wire:click="viderCachePACES"
                        wire:loading.attr="disabled"
                        wire:target="viderCachePACES"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 hover:bg-black dark:bg-gray-700 dark:hover:bg-gray-600 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    {{-- Icône par défaut --}}
                    <svg wire:loading.remove wire:target="viderCachePACES" 
                         class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    {{-- Spinner pendant le chargement --}}
                    <svg wire:loading wire:target="viderCachePACES" 
                         class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{-- Texte avec état --}}
                    <span wire:loading.remove wire:target="viderCachePACES">Vider le cache</span>
                    <span wire:loading wire:target="viderCachePACES">Nettoyage...</span>
                </button>
            </div>

            {{-- Info Badge --}}
            <div class="flex justify-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-200 dark:border-cyan-800 rounded-lg">
                    <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-xs font-medium text-cyan-700 dark:text-cyan-300">
                        Réversible et sans risque
                    </span>
                </div>
            </div>

        </div>
    </div>

    {{-- Accent bar --}}
    <div class="h-1 bg-gradient-to-r from-primary-500 via-indigo-500 to-purple-500"></div>
</div>

{{-- Toast Success --}}
@if (session('success'))
<div class="mt-4 animate-fade-in">
    <div class="flex items-start gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg shadow-sm">
        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">
            {{ session('success') }}
        </p>
    </div>
</div>
@endif

{{-- Animation CSS --}}
<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>