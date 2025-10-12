{{-- =========================================================== --}}
{{-- ✅ CAS 2 : PAS ENCORE DÉLIBÉRÉ → CTA Lancer la simulation --}}
{{-- =========================================================== --}}
<div class="relative bg-gradient-to-br from-white via-gray-50 to-primary-50/30 dark:from-gray-900 dark:via-gray-800 dark:to-primary-900/10 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-lg">
    
    {{-- Pattern Background --}}
    <div class="absolute inset-0 opacity-[0.02] dark:opacity-[0.03]">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="32" height="32" patternUnits="userSpaceOnUse">
                    <path d="M 32 0 L 0 0 0 32" fill="none" stroke="currentColor" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>

    <div class="relative px-6 py-8">
        <div class="max-w-5xl mx-auto">
            
            {{-- Header Compact --}}
            <div class="text-center mb-6">
                {{-- Icon Badge --}}
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary-500 to-indigo-600 dark:from-primary-600 dark:to-indigo-700 rounded-2xl shadow-lg mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                
                {{-- Title --}}
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                    Simuler la délibération
                </h2>
                
                {{-- Subtitle --}}
                <p class="text-sm text-gray-600 dark:text-gray-400 max-w-xl mx-auto">
                    Prévisualisez l'impact de vos critères avant validation définitive
                </p>
            </div>

            {{-- Features Compact Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                {{-- Feature 1 --}}
                <div class="group relative bg-white dark:bg-gray-800/50 rounded-lg px-4 py-3 border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-md transition-all">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 mb-0.5">Configurez</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-tight">
                                Ajustez quota, crédits, moyenne et note éliminatoire
                            </p>
                        </div>
                    </div>
                </div>
                
                {{-- Feature 2 --}}
                <div class="group relative bg-white dark:bg-gray-800/50 rounded-lg px-4 py-3 border border-gray-200 dark:border-gray-700 hover:border-amber-300 dark:hover:border-amber-600 hover:shadow-md transition-all">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 mb-0.5">Visualisez</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-tight">
                                Dashboard en temps réel avec stats détaillées
                            </p>
                        </div>
                    </div>
                </div>
                
                {{-- Feature 3 --}}
                <div class="group relative bg-white dark:bg-gray-800/50 rounded-lg px-4 py-3 border border-gray-200 dark:border-gray-700 hover:border-emerald-300 dark:hover:border-emerald-600 hover:shadow-md transition-all">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 mb-0.5">Appliquez</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-tight">
                                Enregistrez quand le résultat vous convient
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CTA Section --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                {{-- Primary Button --}}
                <a href="{{ route('resultats.paces-deliberation', ['parcoursSlug' => $parcoursSlug]) }}"
                   class="group inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Lancer la simulation</span>
                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>

                {{-- Info Badge --}}
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

    {{-- Bottom Accent --}}
    <div class="h-1 bg-gradient-to-r from-primary-500 via-indigo-500 to-purple-500"></div>
</div>