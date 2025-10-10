{{-- resources/views/livewire/resultats/partials/parametre-simulation.blade.php --}}

<div x-data="{ open: @entangle('simulationEnCours') }" 
     class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl shadow-md border border-gray-200/50 dark:border-gray-700/50 overflow-hidden transition-all hover:shadow-lg">
     
    {{-- 🎯 Header Compact & Élégant --}}
    <button @click="open = !open" 
            type="button"
            class="w-full px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all group">
        
        <div class="flex items-center gap-3 flex-1">
            {{-- Icône minimaliste --}}
            <div class="p-2 bg-gradient-to-br from-primary-500/10 to-primary-600/10 dark:from-primary-400/20 dark:to-primary-500/20 rounded-lg group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
            </div>

            {{-- Titre & Badge --}}
            <div class="flex items-center gap-2 flex-1">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                    Paramètres de Délibération
                </h3>
                @if($simulationEnCours)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-[10px] font-bold rounded-md border border-amber-300/50 dark:border-amber-700/50 animate-pulse">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Simulation active
                    </span>
                @endif
            </div>

            {{-- Badge délibération (si appliquée) --}}
            @if($this->statistiques_deliberation && $this->statistiques_deliberation->etudiants_deliberes > 0)
                <div class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200/50 dark:border-emerald-800/50 rounded-md">
                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-300">
                        {{ $this->statistiques_deliberation->etudiants_deliberes }} délibérés
                    </span>
                </div>
            @endif
        </div>

        {{-- Chevron --}}
        <svg class="w-4 h-4 text-gray-400 transition-transform duration-300 ml-3 group-hover:text-gray-600 dark:group-hover:text-gray-300"
             :class="open ? 'rotate-180' : ''"
             fill="none" 
             stroke="currentColor" 
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- 📋 Contenu Accordion --}}
    <div x-show="open" 
         x-collapse
         x-cloak>
        <div class="px-5 pb-4 pt-1 border-t border-gray-100/50 dark:border-gray-700/50">
            
            {{-- ℹ️ Info Dernière Délibération (compact) --}}
            @if($derniereDeliberation)
                <div class="mb-3 p-2.5 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border border-blue-200/50 dark:border-blue-800/50 rounded-lg">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium text-blue-700 dark:text-blue-300">
                                {{ $derniereDeliberation->applique_at->format('d/m/Y à H:i') }}
                            </span>
                            <span class="text-blue-600/70 dark:text-blue-400/70">•</span>
                            <span class="text-blue-600 dark:text-blue-400">
                                {{ $derniereDeliberation->utilisateur->name ?? 'Système' }}
                            </span>
                        </div>
                        @if($valeursModifiees)
                            <button wire:click="restaurerDernieresValeurs"
                                    class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Restaurer
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 🎛️ Grid Inputs (Compact & Élégant) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
                
                {{-- 1️⃣ Quota Admission --}}
                <div class="group">
                    <label class="flex items-center gap-1 text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                        <svg class="w-3 h-3 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Quota
                    </label>
                    <input type="number" 
                           wire:model.blur="quota_admission" 
                           min="0"
                           placeholder="∞"
                           class="w-full px-3 py-1.5 text-sm rounded-lg border 
                                  {{ $derniereDeliberation && $quota_admission == $derniereDeliberation->quota_admission 
                                     ? 'border-emerald-300 dark:border-emerald-700/50 bg-emerald-50/30 dark:bg-emerald-900/10' 
                                     : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50' }}
                                  text-gray-900 dark:text-white placeholder-gray-400 
                                  focus:border-primary-400 focus:ring-1 focus:ring-primary-400/30 transition-all outline-none
                                  hover:border-gray-300 dark:hover:border-gray-500">
                    @if($derniereDeliberation && $quota_admission != $derniereDeliberation->quota_admission)
                        <p class="mt-0.5 text-[10px] text-amber-600 dark:text-amber-400 flex items-center gap-0.5">
                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Avant: {{ $derniereDeliberation->quota_admission ?? '∞' }}
                        </p>
                    @endif
                </div>

                {{-- 2️⃣ Crédits Requis --}}
                <div class="group">
                    <label class="flex items-center gap-1 text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Crédits
                    </label>
                    <div class="relative">
                        <input type="number" 
                               wire:model.blur="credits_requis" 
                               min="0" 
                               max="60"
                               class="w-full px-3 pr-9 py-1.5 text-sm rounded-lg border 
                                      {{ $derniereDeliberation && $credits_requis == $derniereDeliberation->credits_requis 
                                         ? 'border-emerald-300 dark:border-emerald-700/50 bg-emerald-50/30 dark:bg-emerald-900/10' 
                                         : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50' }}
                                      text-gray-900 dark:text-white 
                                      focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400/30 transition-all outline-none
                                      hover:border-gray-300 dark:hover:border-gray-500">
                        <span class="absolute right-3 top-1.5 text-[10px] font-bold text-gray-400">/60</span>
                    </div>
                    <div class="mt-1 w-full bg-gray-200/50 dark:bg-gray-700/50 rounded-full h-0.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-0.5 rounded-full transition-all" 
                             style="width: {{ ($credits_requis / 60) * 100 }}%"></div>
                    </div>
                </div>

                {{-- 3️⃣ Moyenne Minimale --}}
                <div class="group">
                    <label class="flex items-center gap-1 text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                        <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        Moyenne
                    </label>
                    <div class="relative">
                        <input type="number" 
                               wire:model.blur="moyenne_requise" 
                               min="0" 
                               max="20" 
                               step="0.01"
                               class="w-full px-3 pr-9 py-1.5 text-sm rounded-lg border 
                                      {{ $derniereDeliberation && $moyenne_requise == $derniereDeliberation->moyenne_requise 
                                         ? 'border-emerald-300 dark:border-emerald-700/50 bg-emerald-50/30 dark:bg-emerald-900/10' 
                                         : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50' }}
                                      text-gray-900 dark:text-white 
                                      focus:border-amber-400 focus:ring-1 focus:ring-amber-400/30 transition-all outline-none
                                      hover:border-gray-300 dark:hover:border-gray-500">
                        <span class="absolute right-3 top-1.5 text-[10px] font-bold text-gray-400">/20</span>
                    </div>
                    <div class="mt-1 w-full bg-gray-200/50 dark:bg-gray-700/50 rounded-full h-0.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-0.5 rounded-full transition-all" 
                             style="width: {{ ($moyenne_requise / 20) * 100 }}%"></div>
                    </div>
                </div>

                {{-- 4️⃣ Note Éliminatoire (Checkbox élégante) --}}
                <div class="group flex flex-col justify-center">
                    <label class="flex items-center gap-1 text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                        <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Éliminatoire
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 hover:border-gray-300 dark:hover:border-gray-500 transition-all">
                        <input type="checkbox" 
                               wire:model.change="appliquer_note_eliminatoire" 
                               class="w-3.5 h-3.5 rounded border-gray-300 dark:border-gray-600 text-rose-600 focus:ring-1 focus:ring-rose-500/30 transition-all">
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            Note 0 = Exclusion
                        </span>
                    </label>
                </div>

            </div>

            {{-- 🎬 Boutons d'Action (Inclus depuis partiel) --}}
            @include('livewire.resultats.partials.action-buttons')

            {{-- 💡 Aide (Ultra-compact) --}}
            <div class="mt-3 pt-3 border-t border-gray-100/50 dark:border-gray-700/50">
                <div class="flex items-center justify-center gap-1.5 px-3 py-1.5 bg-cyan-50/50 dark:bg-cyan-900/10 border border-cyan-200/30 dark:border-cyan-800/30 rounded-lg">
                    <svg class="w-3 h-3 text-cyan-600 dark:text-cyan-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-[11px] font-medium text-cyan-700 dark:text-cyan-300">
                        Ajustez les critères → Simulez → Appliquez
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>