{{-- ===== PARAMÈTRES SIMULATION EN ACCORDION ===== --}}
<div x-data="{ open: @entangle('simulationEnCours') }" 
     class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transition-all">
     
    {{-- Header simplifié --}}
    <button @click="open = !open" 
            type="button"
            class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
        
        <div class="flex items-center gap-4 flex-1">
            {{-- Icône --}}
            <div class="p-2.5 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl shadow-md group-hover:shadow-lg transition-all">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>

            {{-- Titre --}}
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Paramètres de Délibération
                    </h3>
                    @if($simulationEnCours)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-xs font-bold rounded-full border border-amber-300 dark:border-amber-700">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Simulation
                        </span>
                    @endif
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                    Configurez et appliquez les critères de délibération
                </p>
            </div>

            {{-- État délibération (si appliquée) --}}
            @if($this->statistiques_deliberation && $this->statistiques_deliberation->etudiants_deliberes > 0)
                <div class="flex items-center gap-2 px-3 py-1.5 bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-200 dark:border-cyan-800 rounded-lg">
                    <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-xs">
                        <span class="font-bold text-cyan-900 dark:text-cyan-100">
                            {{ $this->statistiques_deliberation->etudiants_deliberes }}
                        </span>
                        <span class="text-cyan-600 dark:text-cyan-400">délibérés</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Icône chevron --}}
        <svg class="w-5 h-5 text-gray-400 transition-transform duration-300 ml-4"
             :class="open ? 'rotate-180' : ''"
             fill="none" 
             stroke="currentColor" 
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Contenu accordion --}}
    <div x-show="open" 
         x-collapse
         x-cloak>
        <div class="px-6 pb-6 pt-2 border-t border-gray-100 dark:border-gray-700">
            
            {{-- Bandeau info si dernière délibération existe --}}
            @if($derniereDeliberation)
                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-medium text-blue-700 dark:text-blue-300">
                                Dernière délibération : {{ $derniereDeliberation->applique_at->format('d/m/Y à H:i') }}
                                par {{ $derniereDeliberation->utilisateur->name ?? 'Système' }}
                            </span>
                        </div>
                        @if($valeursModifiees)
                            <button wire:click="restaurerDernieresValeurs"
                                    class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                Restaurer les valeurs
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Grid responsive des inputs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                
                {{-- Quota Admission --}}
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Quota Admission
                        @if($derniereDeliberation && !$valeursModifiees)
                            <span class="text-xs text-green-600 dark:text-green-400">✓</span>
                        @endif
                    </label>
                    <input type="number" 
                           wire:model.blur="quota_admission" 
                           min="0"
                           placeholder="Illimité"
                           class="w-full px-3 py-2 text-sm rounded-lg border-2 
                                  {{ $derniereDeliberation && $quota_admission == $derniereDeliberation->quota_admission 
                                     ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20' 
                                     : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700' }}
                                  text-gray-900 dark:text-white placeholder-gray-400 
                                  focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all outline-none">
                    @if($derniereDeliberation)
                        <p class="mt-1 text-xs {{ $quota_admission == $derniereDeliberation->quota_admission ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $quota_admission == $derniereDeliberation->quota_admission 
                               ? 'Valeur appliquée' 
                               : 'Dernière: ' . ($derniereDeliberation->quota_admission ?? 'Illimité') }}
                        </p>
                    @else
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Vide = illimité</p>
                    @endif
                </div>

                {{-- Crédits Requis --}}
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Crédits Requis
                        @if($derniereDeliberation && !$valeursModifiees)
                            <span class="text-xs text-green-600 dark:text-green-400">✓</span>
                        @endif
                    </label>
                    <div class="relative">
                        <input type="number" 
                               wire:model.blur="credits_requis" 
                               min="0" 
                               max="60"
                               class="w-full px-3 pr-10 py-2 text-sm rounded-lg border-2 
                                      {{ $derniereDeliberation && $credits_requis == $derniereDeliberation->credits_requis 
                                         ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20' 
                                         : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700' }}
                                      text-gray-900 dark:text-white 
                                      focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all outline-none">
                        <span class="absolute right-3 top-2 text-xs font-bold text-gray-400">/60</span>
                    </div>
                    <div class="mt-1.5 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1 overflow-hidden">
                        <div class="bg-green-500 h-1 rounded-full transition-all" 
                             style="width: {{ ($credits_requis / 60) * 100 }}%"></div>
                    </div>
                    @if($derniereDeliberation && $credits_requis != $derniereDeliberation->credits_requis)
                        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                            Dernière: {{ $derniereDeliberation->credits_requis }}
                        </p>
                    @endif
                </div>

                {{-- Moyenne Minimale --}}
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        <svg class="w-3.5 h-3.5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        Moyenne Minimale
                        @if($derniereDeliberation && !$valeursModifiees)
                            <span class="text-xs text-green-600 dark:text-green-400">✓</span>
                        @endif
                    </label>
                    <div class="relative">
                        <input type="number" 
                               wire:model.blur="moyenne_requise" 
                               min="0" 
                               max="20" 
                               step="0.01"
                               class="w-full px-3 pr-10 py-2 text-sm rounded-lg border-2 
                                      {{ $derniereDeliberation && $moyenne_requise == $derniereDeliberation->moyenne_requise 
                                         ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20' 
                                         : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700' }}
                                      text-gray-900 dark:text-white 
                                      focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20 transition-all outline-none">
                        <span class="absolute right-3 top-2 text-xs font-bold text-gray-400">/20</span>
                    </div>
                    <div class="mt-1.5 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1 overflow-hidden">
                        <div class="bg-yellow-500 h-1 rounded-full transition-all" 
                             style="width: {{ ($moyenne_requise / 20) * 100 }}%"></div>
                    </div>
                    @if($derniereDeliberation && $moyenne_requise != $derniereDeliberation->moyenne_requise)
                        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                            Dernière: {{ $derniereDeliberation->moyenne_requise }}
                        </p>
                    @endif
                </div>

                {{-- Boutons Actions --}}
                <div class="flex flex-col gap-2">
                    
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 mb-0">
                        <svg class="w-3.5 h-3.5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Actions
                    </label>

                    {{-- ✅ Bouton Simuler SIMPLIFIÉ (instantané, pas de loading) --}}
                    <button wire:click="simulerDeliberation"
                            class="w-full px-3 py-2 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm rounded-lg transition-all shadow-sm hover:shadow-md font-semibold hover:scale-105 active:scale-95">
                        <span class="flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            ⚡ Simuler (instantané)
                        </span>
                    </button>
                                        
                    {{-- Bouton Appliquer (uniquement si simulation active) --}}
                    @if($simulationEnCours)
                        <button wire:click="appliquerDeliberation"
                                wire:loading.attr="disabled"
                                class="w-full px-3 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-sm rounded-lg transition-all shadow-sm hover:shadow-md font-semibold animate-pulse">
                            <span wire:loading.remove wire:target="appliquerDeliberation" class="flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                ✅ Appliquer la délibération
                            </span>
                            <span wire:loading wire:target="appliquerDeliberation" class="flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Application...
                            </span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Options --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" 
                           wire:model.change="appliquer_note_eliminatoire" 
                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-2 focus:ring-red-500/20 transition-all">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">
                        Note 0 = Exclusion automatique
                    </span>
                    @if($derniereDeliberation && $appliquer_note_eliminatoire == $derniereDeliberation->note_eliminatoire)
                        <span class="text-xs text-green-600 dark:text-green-400">✓ Appliqué</span>
                    @elseif($derniereDeliberation)
                        <span class="text-xs text-amber-600 dark:text-amber-400">
                            (Dernière: {{ $derniereDeliberation->note_eliminatoire ? 'Oui' : 'Non' }})
                        </span>
                    @endif
                </label>

                <div class="flex items-center gap-2 px-3 py-1.5 bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-200 dark:border-cyan-800 rounded-lg">
                    <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-xs font-medium text-cyan-700 dark:text-cyan-300">
                        Simulez avant d'appliquer
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>