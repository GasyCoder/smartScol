{{-- resources/views/livewire/resultats/simulation-deliberation.blade.php --}}
<div class="space-y-6">
    
    {{-- 🔙 Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('resultats.paces-concours') }}" 
           wire:navigate
           class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Retour aux Résultats</span>
        </a>
        
        <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
        </svg>
        
        <div class="flex items-center gap-2">
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $parcours->nom }}</span>
            <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                🎭 Simulation
            </span>
        </div>
    </div>

    {{-- ⚡ Loading Overlay --}}
    <div wire:loading.flex
         wire:target="simuler,appliquer,exporterPDF,exporterExcelPaces"
         class="fixed inset-0 bg-slate-900/80 dark:bg-black/85 backdrop-blur-sm z-[9999] items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl px-8 py-6 min-w-[320px] border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <svg class="animate-spin h-10 w-10 text-primary-600 dark:text-primary-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        <span wire:loading wire:target="simuler">⚡ Simulation en cours</span>
                        <span wire:loading wire:target="appliquer">✅ Application en cours</span>
                        <span wire:loading wire:target="exporterExcelPaces,exporterPDF">📊 Export en cours</span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span wire:loading wire:target="simuler">Calcul des décisions...</span>
                        <span wire:loading wire:target="appliquer">Enregistrement en base de données...</span>
                        <span wire:loading wire:target="exporterExcelPaces">Génération Excel...</span>
                        <span wire:loading wire:target="exporterPDF">Génération PDF...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 📊 DASHBOARD MÉGA --}}
    @if(!empty($statistiquesDetailes))
        @include('livewire.resultats.partials.paces.dashboard-mega-paces', [
            'statistiquesDetailes' => $statistiquesDetailes,
            'simulationEnCours' => true,
            'resultatsVersion' => 1
        ])
    @endif

    {{-- ⚙️ PARAMÈTRES SIMULATION --}}
    <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-gradient-to-br from-primary-500/10 to-primary-600/10 dark:from-primary-400/20 dark:to-primary-500/20 rounded-lg">
                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Paramètres de Délibération</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Quota --}}
            <div>
                <label class="flex items-center gap-1 text-xs font-bold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">
                    <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Quota Admission
                </label>
                <input type="number" wire:model.live="quota_admission" min="0" placeholder="∞"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-primary-400 focus:ring-1 focus:ring-primary-400/30 transition-all">
            </div>

            {{-- Crédits --}}
            <div>
                <label class="flex items-center gap-1 text-xs font-bold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Crédits Requis
                </label>
                <div class="relative">
                    <input type="number" wire:model.live="credits_requis" min="0" max="60"
                           class="w-full px-3 pr-10 py-2 rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400/30 transition-all">
                    <span class="absolute right-3 top-2 text-xs font-bold text-gray-400">/60</span>
                </div>
            </div>

            {{-- Moyenne --}}
            <div>
                <label class="flex items-center gap-1 text-xs font-bold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674z"/>
                    </svg>
                    Moyenne Requise
                </label>
                <div class="relative">
                    <input type="number" wire:model.live="moyenne_requise" min="0" max="20" step="0.01"
                           class="w-full px-3 pr-10 py-2 rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-amber-400 focus:ring-1 focus:ring-amber-400/30 transition-all">
                    <span class="absolute right-3 top-2 text-xs font-bold text-gray-400">/20</span>
                </div>
            </div>

            {{-- Note éliminatoire --}}
            <div class="flex flex-col justify-center">
                <label class="flex items-center gap-1 text-xs font-bold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Note Éliminatoire
                </label>
                <label class="flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 hover:border-gray-400 dark:hover:border-gray-500 transition-all">
                    <input type="checkbox" wire:model.live="appliquer_note_eliminatoire"
                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-rose-600 focus:ring-1 focus:ring-rose-500/30">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Note 0 = Exclusion
                    </span>
                </label>
            </div>
        </div>

        {{-- Boutons Action --}}
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button wire:click="simuler"
                    wire:loading.attr="disabled"
                    class="flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white rounded-lg text-base font-bold shadow-md transition-all disabled:opacity-60">
                <svg wire:loading.remove wire:target="simuler" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <svg wire:loading wire:target="simuler" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Recalculer la Simulation</span>
            </button>

            <button wire:click="annuler"
                    class="flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-base font-semibold transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>Annuler et Retour</span>
            </button>
        </div>
    </div>

    {{-- 📊 BARRE FILTRES + EXPORTS (TOUJOURS VISIBLE) --}}
    @if($simulationCalculee)
        <div class="sticky top-2 z-40">
            <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg px-5 py-3.5">
                <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                    
                    {{-- Filtres décision --}}
                    @include('livewire.resultats.partials.paces.filtres-decisions', [
                        'statistiquesDetailes' => $statistiquesDetailes,
                        'filtreDecision' => $filtreDecision
                    ])

                    <div class="flex-1"></div>

                    {{-- 📤 EXPORTS (TOUJOURS VISIBLES) --}}
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

        {{-- Message info --}}
        <div class="bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-200 dark:border-cyan-800 rounded-xl px-5 py-3">
            <div class="flex items-center gap-2.5 text-sm">
                <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span class="text-cyan-800 dark:text-cyan-200">
                    <strong class="font-bold">💡 Astuce :</strong> Vous pouvez exporter les résultats de la simulation (Excel/PDF) selon le filtre actif (<strong>{{ match($filtreDecision) {
                        'admis' => 'Admis',
                        'redoublant' => 'Redoublants',
                        'exclus' => 'Exclus',
                        default => 'Tous'
                    } }}</strong>) avant de l'appliquer définitivement.
                </span>
            </div>
        </div>
    @endif

    {{-- ✅ ZONE D'ACTION : Appliquer la délibération --}}
    @if($simulationCalculee)
        <div class="bg-gradient-to-br from-emerald-50 via-teal-50 to-emerald-50 dark:from-emerald-900/20 dark:via-teal-900/20 dark:to-emerald-900/20 border-2 border-emerald-300 dark:border-emerald-700 rounded-2xl overflow-hidden shadow-lg">
            
            {{-- Header --}}
            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 dark:from-emerald-600 dark:to-teal-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-white">
                            ✅ Simulation Terminée
                        </h3>
                        <p class="text-sm text-white/90">
                            Vérifiez les résultats ci-dessus puis appliquez définitivement
                        </p>
                    </div>
                </div>
            </div>

            {{-- Contenu --}}
            <div class="p-6">
                {{-- Récapitulatif --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border-2 border-green-200 dark:border-green-800 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Admis</p>
                                <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                                    {{ $compteurs['admis'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border-2 border-orange-200 dark:border-orange-800 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Redoublants</p>
                                <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                                    {{ $compteurs['redoublant'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border-2 border-red-200 dark:border-red-800 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Exclus</p>
                                <p class="text-3xl font-bold text-red-600 dark:text-red-400">
                                    {{ $compteurs['exclus'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Message explicatif --}}
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-5 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-amber-900 dark:text-amber-100 mb-2">
                                ⚠️ Attention : Action Irréversible
                            </p>
                            <ul class="text-sm text-amber-800 dark:text-amber-200 space-y-1 list-disc list-inside">
                                <li>En cliquant sur "Appliquer", ces décisions seront <strong>enregistrées définitivement</strong> en base de données</li>
                                <li>Les résultats seront visibles par tous les enseignants et étudiants</li>
                                <li>Vous serez redirigé vers la liste des résultats avec le tableau complet</li>
                                <li><strong>💡 Exportez les résultats ci-dessus si vous souhaitez les conserver avant application</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- CTA Final --}}
                <div class="flex items-center justify-center gap-3">
                    <button wire:click="appliquer"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-lg font-bold shadow-xl hover:shadow-2xl transition-all disabled:opacity-60">
                        <svg wire:loading.remove wire:target="appliquer" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg wire:loading wire:target="appliquer" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Appliquer Définitivement la Délibération</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>