        <div class="p-6 border rounded-lg bg-purple-50 dark:bg-purple-900/20 dark:border-purple-800">
            <h3 class="mb-4 text-lg font-semibold text-purple-900 dark:text-purple-300">
                <em class="mr-2 ni ni-setting"></em>
                Paramètres de Simulation Délibération
            </h3>

            {{-- Sélection du type de session à simuler - CODE ORIGINAL AVEC CORRECTIONS MINIMALES --}}
            <div class="mb-6">
                <label class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Session à simuler</label>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    {{-- Option Session 1 si disponible --}}
                    @if(!empty($resultatsSession1))
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ ($deliberationParams['session_type'] ?? 'session1') === 'session1' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                            <input type="radio"
                                name="session_type_group"
                                wire:model.live="deliberationParams.session_type"
                                value="session1"
                                class="w-4 h-4 text-purple-600">
                            <div class="ml-3">
                                <div class="font-medium text-gray-900 dark:text-gray-100">Session 1 (Normale)</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ count($resultatsSession1) }} étudiants • Critères admission directe
                                    @if(($this->dernieresValeursDeliberation['session1']['delibere'] ?? false))
                                        <span class="ml-2 text-green-600 dark:text-green-400">✓ Délibérée</span>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endif

                    {{-- Option Session 2 si disponible --}}
                    @if(!empty($resultatsSession2))
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ ($deliberationParams['session_type'] ?? 'session1') === 'session2' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                            <input type="radio"
                                name="session_type_group"
                                wire:model.live="deliberationParams.session_type"
                                value="session2"
                                class="w-4 h-4 text-purple-600">
                            <div class="ml-3">
                                <div class="font-medium text-gray-900 dark:text-gray-100">Session 2 (Rattrapage)</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ count($resultatsSession2) }} étudiants • Critères rattrapage
                                    @if(($this->dernieresValeursDeliberation['session2']['delibere'] ?? false))
                                        <span class="ml-2 text-green-600 dark:text-green-400">✓ Délibérée</span>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endif
                </div>
                @error('deliberationParams.session_type')
                    <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                @enderror
            </div>

            {{-- ✅ PARAMÈTRES SELON LA SESSION SÉLECTIONNÉE AVEC PROTECTION --}}
            @if(($deliberationParams['session_type'] ?? 'session1') === 'session1')
                {{-- Paramètres pour Session 1 (Normale) --}}
                <div class="p-4 mb-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                    {{-- ✅ AFFICHAGE DES DERNIÈRES VALEURS SI DÉLIBÉRÉE --}}
                    @if(($this->dernieresValeursDeliberation['session1']['delibere'] ?? false))
                        <div class="p-3 mb-4 bg-green-100 rounded-lg dark:bg-green-800/30">
                            <div class="flex items-center text-sm text-green-800 dark:text-green-200">
                                <em class="mr-2 ni ni-info-circle"></em>
                                <div>
                                    <div class="font-medium">Dernière délibération appliquée :</div>
                                    <div class="mt-1 text-xs">
                                        {{ ($this->dernieresValeursDeliberation['session1']['credits_admission_s1'] ?? 60) }} crédits requis •
                                        Note 0 {{ ($this->dernieresValeursDeliberation['session1']['note_eliminatoire_bloque_s1'] ?? true) ? 'bloque' : 'autorisée' }} •
                                        Le {{ isset($this->dernieresValeursDeliberation['session1']['date_deliberation']) ? \Carbon\Carbon::parse($this->dernieresValeursDeliberation['session1']['date_deliberation'])->format('d/m/Y à H:i') : 'Date inconnue' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Crédits requis pour admission directe
                                <span class="text-xs text-blue-600 dark:text-blue-400">
                                    (Dernière valeur: {{ ($this->dernieresValeursDeliberation['session1']['credits_admission_s1'] ?? 60) }})
                                </span>
                            </label>
                            <input type="number" min="40" max="60" step="1"
                                   wire:model="deliberationParams.credits_admission_s1"
                                   placeholder="{{ ($this->dernieresValeursDeliberation['session1']['credits_admission_s1'] ?? 60) }}"
                                   class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Si ≥ {{ ($deliberationParams['credits_admission_s1'] ?? $this->dernieresValeursDeliberation['session1']['credits_admission_s1'] ?? 60) }} crédits → Admis, sinon → Rattrapage
                            </p>
                            @error('deliberationParams.credits_admission_s1')
                                <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox"
                                   wire:model="deliberationParams.note_eliminatoire_bloque_s1"
                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 dark:border-gray-600">
                            <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                Bloquer admission si note éliminatoire
                                <span class="block text-xs text-gray-500 dark:text-gray-400">
                                    Dernière valeur: {{ (($this->dernieresValeursDeliberation['session1']['note_eliminatoire_bloque_s1'] ?? true)) ? 'Activé' : 'Désactivé' }}
                                </span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">
                                    Si activé : Note = 0 empêche admission directe (logique médecine standard)
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Aperçu de la logique --}}
                    <div class="p-3 mt-4 bg-blue-100 rounded-lg dark:bg-blue-800/30">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Logique appliquée :</strong>
                            @if(($deliberationParams['note_eliminatoire_bloque_s1'] ?? $this->dernieresValeursDeliberation['session1']['note_eliminatoire_bloque_s1'] ?? true))
                                Note 0 → Rattrapage automatique |
                            @endif
                            ≥ {{ ($deliberationParams['credits_admission_s1'] ?? $this->dernieresValeursDeliberation['session1']['credits_admission_s1'] ?? 60) }} crédits → Admis |
                            < {{ ($deliberationParams['credits_admission_s1'] ?? $this->dernieresValeursDeliberation['session1']['credits_admission_s1'] ?? 60) }} crédits → Rattrapage
                        </p>
                    </div>
                </div>

            @elseif(($deliberationParams['session_type'] ?? 'session1') === 'session2')
                {{-- Paramètres pour Session 2 (Rattrapage) --}}
                <div class="p-4 mb-4 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                    <h4 class="mb-3 font-medium text-green-900 dark:text-green-300">
                        <em class="mr-2 ni ni-repeat"></em>
                        Critères Session 2 (Rattrapage) - Logique Médecine
                    </h4>

                    {{-- ✅ AFFICHAGE DES DERNIÈRES VALEURS SI DÉLIBÉRÉE --}}
                    @if(($this->dernieresValeursDeliberation['session2']['delibere'] ?? false))
                        <div class="p-3 mb-4 bg-green-100 rounded-lg dark:bg-green-800/30">
                            <div class="flex items-center text-sm text-green-800 dark:text-green-200">
                                <em class="mr-2 ni ni-info-circle"></em>
                                <div>
                                    <div class="font-medium">Dernière délibération appliquée :</div>
                                    <div class="mt-1 text-xs">
                                        {{ ($this->dernieresValeursDeliberation['session2']['credits_admission_s2'] ?? 40) }} crédits admission •
                                        {{ ($this->dernieresValeursDeliberation['session2']['credits_redoublement_s2'] ?? 20) }} crédits redoublement •
                                        Le {{ isset($this->dernieresValeursDeliberation['session2']['date_deliberation']) ? \Carbon\Carbon::parse($this->dernieresValeursDeliberation['session2']['date_deliberation'])->format('d/m/Y à H:i') : 'Date inconnue' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Crédits minimum admission
                                <span class="text-xs text-green-600 dark:text-green-400">
                                    (Dernière: {{ ($this->dernieresValeursDeliberation['session2']['credits_admission_s2'] ?? 40) }})
                                </span>
                            </label>
                            <input type="number" min="30" max="60" step="1"
                                   wire:model="deliberationParams.credits_admission_s2"
                                   placeholder="{{ ($this->dernieresValeursDeliberation['session2']['credits_admission_s2'] ?? 40) }}"
                                   class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Si ≥ {{ ($deliberationParams['credits_admission_s2'] ?? $this->dernieresValeursDeliberation['session2']['credits_admission_s2'] ?? 40) }} crédits → Admis
                            </p>
                            @error('deliberationParams.credits_admission_s2')
                                <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Crédits minimum redoublement
                                <span class="text-xs text-green-600 dark:text-green-400">
                                    (Dernière: {{ ($this->dernieresValeursDeliberation['session2']['credits_redoublement_s2'] ?? 20) }})
                                </span>
                            </label>
                            <input type="number" min="0" max="40" step="1"
                                   wire:model="deliberationParams.credits_redoublement_s2"
                                   placeholder="{{ ($this->dernieresValeursDeliberation['session2']['credits_redoublement_s2'] ?? 20) }}"
                                   class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Si < {{ ($deliberationParams['credits_redoublement_s2'] ?? $this->dernieresValeursDeliberation['session2']['credits_redoublement_s2'] ?? 20) }} crédits → Exclus
                            </p>
                            @error('deliberationParams.credits_redoublement_s2')
                                <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox"
                                   wire:model="deliberationParams.note_eliminatoire_exclusion_s2"
                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 dark:border-gray-600">
                            <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                Exclusion automatique si note 0
                                <span class="block text-xs text-gray-500 dark:text-gray-400">
                                    Dernière valeur: {{ (($this->dernieresValeursDeliberation['session2']['note_eliminatoire_exclusion_s2'] ?? true)) ? 'Activé' : 'Désactivé' }}
                                </span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">
                                    Si activé : Note = 0 en rattrapage → Exclusion (logique médecine standard)
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Aperçu de la logique --}}
                    <div class="p-3 mt-4 bg-green-100 rounded-lg dark:bg-green-800/30">
                        <p class="text-sm text-green-800 dark:text-green-200">
                            <strong>Logique appliquée :</strong>
                            @if(($deliberationParams['note_eliminatoire_exclusion_s2'] ?? $this->dernieresValeursDeliberation['session2']['note_eliminatoire_exclusion_s2'] ?? true))
                                Note 0 → Exclusion automatique |
                            @endif
                            ≥ {{ ($deliberationParams['credits_admission_s2'] ?? $this->dernieresValeursDeliberation['session2']['credits_admission_s2'] ?? 40) }} crédits → Admis |
                            ≥ {{ ($deliberationParams['credits_redoublement_s2'] ?? $this->dernieresValeursDeliberation['session2']['credits_redoublement_s2'] ?? 20) }} crédits → Redoublant |
                            < {{ ($deliberationParams['credits_redoublement_s2'] ?? $this->dernieresValeursDeliberation['session2']['credits_redoublement_s2'] ?? 20) }} crédits → Exclus
                        </p>
                    </div>
                </div>
            @endif

            {{-- Boutons d'action --}}
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p><strong>Simulation :</strong> Teste l'impact des paramètres de délibération</p>
                    <p>sans modifier les résultats réels dans la base de données.</p>
                </div>
                <div class="flex items-center space-x-3">
                    {{-- Bouton Simulation avec Loading --}}
                    <button wire:click="simulerDeliberation"
                            wire:loading.attr="disabled"
                            wire:target="simulerDeliberation"
                            class="inline-flex items-center px-6 py-2 text-white transition-colors bg-purple-600 rounded-lg hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-600 disabled:opacity-60 disabled:cursor-not-allowed"
                            @if(empty($deliberationParams['session_type'] ?? '')) disabled @endif>
                        
                        {{-- Icône par défaut (cachée pendant loading) --}}
                        <em class="mr-2 ni ni-play" wire:loading.remove wire:target="simulerDeliberation"></em>
                        
                        {{-- Icône loading (visible seulement pendant loading) --}}
                        <em class="mr-2 text-xl ni ni-reload animate-spin" wire:loading wire:target="simulerDeliberation"></em>
                        
                        {{-- Texte normal --}}
                        <span wire:loading.remove wire:target="simulerDeliberation">Simuler Délibération</span>
                        {{-- Texte loading --}}
                        <span wire:loading wire:target="simulerDeliberation">Simulation...</span>
                    </button>

                    {{-- Bouton Actualiser avec Loading --}}
                    <button wire:click="refreshData"
                            wire:loading.attr="disabled"
                            wire:target="refreshData"
                            class="inline-flex items-center px-4 py-2 text-green-700 bg-green-200 rounded-lg hover:bg-green-300 dark:bg-green-700 dark:text-green-100 dark:hover:bg-green-600 disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                        
                        {{-- Icône par défaut (cachée pendant loading) --}}
                        <em class="mr-2 transition-transform duration-300 ni ni-reload group-hover:rotate-180" wire:loading.remove wire:target="refreshData"></em>
                        
                        {{-- Icône loading (visible seulement pendant loading) --}}
                        <em class="mr-2 text-xl ni ni-reload animate-spin" wire:loading wire:target="refreshData"></em>
                        
                        {{-- Texte normal --}}
                        <span wire:loading.remove wire:target="refreshData">Actualiser</span>
                        {{-- Texte loading --}}
                        <span wire:loading wire:target="refreshData">Actualisation...</span>
                    </button>

                    {{-- Bouton Reset avec Loading --}}
                    <button wire:click="resetComponent"
                            wire:loading.attr="disabled"
                            wire:target="resetComponent"
                            class="inline-flex items-center px-4 py-2 text-red-700 bg-red-100 rounded-lg hover:bg-red-300 dark:bg-red-700 dark:text-red-100 dark:hover:bg-red-600 disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                        
                        {{-- Icône par défaut (cachée pendant loading) --}}
                        <em class="mr-1 ni ni-cross" wire:loading.remove wire:target="resetComponent"></em>
                        
                        {{-- Icône loading (visible seulement pendant loading) --}}
                        <em class="mr-2 text-xl ni ni-reload animate-spin" wire:loading wire:target="resetComponent"></em>
                        
                        {{-- Texte normal --}}
                        <span wire:loading.remove wire:target="resetComponent">Reset</span>
                        {{-- Texte loading --}}
                        <span wire:loading wire:target="resetComponent">Reset...</span>
                    </button>
                </div>
            </div>

            @error('deliberation')
                <div class="p-3 mt-4 text-red-700 bg-red-100 border border-red-400 rounded dark:text-red-300 dark:bg-red-900/50 dark:border-red-800">
                    {{ $message }}
                </div>
            @enderror
        </div>