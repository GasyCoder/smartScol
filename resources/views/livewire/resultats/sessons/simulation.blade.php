{{-- simulation.blade.php --}}
@if($activeTab === 'simulation' && (!empty($resultatsSession1) || !empty($resultatsSession2)))
    <div class="space-y-6">
        {{-- ✅ STATUT DE DÉLIBÉRATION AVEC DERNIÈRES CONFIGURATIONS --}}
        @if(isset($deliberationStatus['session1']) || isset($deliberationStatus['session2']))
            <div class="p-4 border rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                <h4 class="mb-3 font-medium text-blue-900 dark:text-blue-300">
                    <em class="mr-2 ni ni-settings"></em>
                    Statut des Délibérations & Dernières Configurations
                </h4>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    {{-- Session 1 --}}
                    @if(!empty($resultatsSession1))
                        <div class="p-4 bg-white rounded-lg dark:bg-gray-800">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-medium text-gray-900 dark:text-gray-100">Session 1 (Normale)</span>
                                @if(($this->dernieresValeursDeliberation['session1']['delibere'] ?? false))
                                    <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-200 rounded-full dark:text-green-300 dark:bg-green-800">
                                        ✅ Délibérée
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium text-orange-700 bg-orange-200 rounded-full dark:text-orange-300 dark:bg-orange-800">
                                        ⏳ Non délibérée
                                    </span>
                                @endif
                            </div>

                            {{-- ✅ DERNIÈRES VALEURS SESSION 1 AVEC PROTECTION --}}
                            @if(isset($this->dernieresValeursDeliberation['session1']) && !empty($this->dernieresValeursDeliberation['session1']))
                                @php
                                    $config1 = $this->dernieresValeursDeliberation['session1'];
                                @endphp
                                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                                    <div class="mb-2 text-xs font-medium text-gray-700 dark:text-gray-300">Dernière configuration :</div>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Crédits requis :</span>
                                            <span class="font-medium text-blue-600 dark:text-blue-400">{{ $config1['credits_admission_s1'] ?? 60 }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Note 0 bloque :</span>
                                            <span class="font-medium {{ ($config1['note_eliminatoire_bloque_s1'] ?? true) ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                                {{ ($config1['note_eliminatoire_bloque_s1'] ?? true) ? 'OUI' : 'NON' }}
                                            </span>
                                        </div>
                                    </div>

                                    @if($config1['delibere'] ?? false)
                                        <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-600">
                                            <div class="text-xs text-green-600 dark:text-green-400">
                                                <em class="mr-1 ni ni-check-circle"></em>
                                                Délibérée le {{ isset($config1['date_deliberation']) ? \Carbon\Carbon::parse($config1['date_deliberation'])->format('d/m/Y à H:i') : 'Date inconnue' }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/30">
                                    <div class="text-xs text-yellow-700 dark:text-yellow-300">
                                        <em class="mr-1 ni ni-alert-triangle"></em>
                                        Aucune configuration trouvée
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Session 2 --}}
                    @if(!empty($resultatsSession2))
                        <div class="p-4 bg-white rounded-lg dark:bg-gray-800">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-medium text-gray-900 dark:text-gray-100">Session 2 (Rattrapage)</span>
                                @if(($this->dernieresValeursDeliberation['session2']['delibere'] ?? false))
                                    <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-200 rounded-full dark:text-green-300 dark:bg-green-800">
                                        ✅ Délibérée
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium text-orange-700 bg-orange-200 rounded-full dark:text-orange-300 dark:bg-orange-800">
                                        ⏳ Non délibérée
                                    </span>
                                @endif
                            </div>

                            {{-- ✅ DERNIÈRES VALEURS SESSION 2 AVEC PROTECTION --}}
                            @if(isset($this->dernieresValeursDeliberation['session2']) && !empty($this->dernieresValeursDeliberation['session2']))
                                @php
                                    $config2 = $this->dernieresValeursDeliberation['session2'];
                                @endphp
                                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                                    <div class="mb-2 text-xs font-medium text-gray-700 dark:text-gray-300">Dernière configuration :</div>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Admis :</span>
                                            <span class="font-medium text-green-600 dark:text-green-400">{{ $config2['credits_admission_s2'] ?? 40 }} crédits</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Redoublement :</span>
                                            <span class="font-medium text-orange-600 dark:text-orange-400">{{ $config2['credits_redoublement_s2'] ?? 20 }} crédits</span>
                                        </div>
                                        <div class="flex justify-between col-span-2">
                                            <span class="text-gray-600 dark:text-gray-400">Note 0 exclut :</span>
                                            <span class="font-medium {{ ($config2['note_eliminatoire_exclusion_s2'] ?? true) ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                                {{ ($config2['note_eliminatoire_exclusion_s2'] ?? true) ? 'OUI' : 'NON' }}
                                            </span>
                                        </div>
                                    </div>

                                    @if($config2['delibere'] ?? false)
                                        <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-600">
                                            <div class="text-xs text-green-600 dark:text-green-400">
                                                <em class="mr-1 ni ni-check-circle"></em>
                                                Délibérée le {{ isset($config2['date_deliberation']) ? \Carbon\Carbon::parse($config2['date_deliberation'])->format('d/m/Y à H:i') : 'Date inconnue' }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/30">
                                    <div class="text-xs text-yellow-700 dark:text-yellow-300">
                                        <em class="mr-1 ni ni-alert-triangle"></em>
                                        Aucune configuration trouvée
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- ✅ RÉSUMÉ LOGIQUE MÉDECINE APPLIQUÉE AVEC PROTECTION --}}
                <div class="p-3 mt-4 bg-blue-100 rounded-lg dark:bg-blue-800/30">
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <div class="mb-1 font-medium">🏥 Logique Médecine Appliquée :</div>
                        <div class="grid grid-cols-1 gap-2 text-xs md:grid-cols-2">
                            <div>
                                • Session 1: {{ ($this->dernieresValeursDeliberation['session1']['credits_admission_s1'] ?? 60) }} crédits → Admis, sinon → Rattrapage
                            </div>
                            <div>
                                • Session 2: {{ ($this->dernieresValeursDeliberation['session2']['credits_admission_s2'] ?? 40) }} crédits → Admis, {{ ($this->dernieresValeursDeliberation['session2']['credits_redoublement_s2'] ?? 20) }} → Redoublant
                            </div>
                            @if(($this->dernieresValeursDeliberation['session1']['note_eliminatoire_bloque_s1'] ?? true) || ($this->dernieresValeursDeliberation['session2']['note_eliminatoire_exclusion_s2'] ?? true))
                                <div class="col-span-2 text-red-700 dark:text-red-300">
                                    • Note éliminatoire (0) :
                                    @if($this->dernieresValeursDeliberation['session1']['note_eliminatoire_bloque_s1'] ?? true)
                                        S1 = Rattrapage automatique
                                    @endif
                                    @if(($this->dernieresValeursDeliberation['session1']['note_eliminatoire_bloque_s1'] ?? true) && ($this->dernieresValeursDeliberation['session2']['note_eliminatoire_exclusion_s2'] ?? true))
                                        ,
                                    @endif
                                    @if($this->dernieresValeursDeliberation['session2']['note_eliminatoire_exclusion_s2'] ?? true)
                                        S2 = Exclusion automatique
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ✅ PARAMÈTRES DE SIMULATION DÉLIBÉRATION --}}
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
                    {{-- Bouton Simulation --}}
                    <button wire:click="simulerDeliberation"
                            class="px-6 py-2 text-white transition-colors bg-purple-600 rounded-lg hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-600"
                            @if(empty($deliberationParams['session_type'] ?? '')) disabled @endif>
                        <em class="mr-2 ni ni-play"></em>
                        Simuler Délibération
                    </button>
                    <button wire:click="refreshData"
                        class="px-4 py-2 text-green-700 bg-green-200 rounded-lg hover:bg-green-300 dark:bg-green-700 dark:text-green-100 dark:hover:bg-green-600">
                        <em class="mr-2 transition-transform duration-300 ni ni-reload group-hover:rotate-180"></em>
                        <span>Actualiser</span>
                    </button>
                    {{-- Boutons d'action de base --}}
                    <button wire:click="resetComponent"
                            class="px-4 py-2 text-red-700 bg-red-100 rounded-lg hover:bg-red-300 dark:bg-red-700 dark:text-red-100 dark:hover:bg-red-600">
                        <em class="mr-1 ni ni-cross"></em>
                        Reset
                    </button>
                </div>
            </div>

            @error('deliberation')
                <div class="p-3 mt-4 text-red-700 bg-red-100 border border-red-400 rounded dark:text-red-300 dark:bg-red-900/50 dark:border-red-800">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- ✅ ACTIONS RAPIDES DE DÉLIBÉRATION --}}
        <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
            <h4 class="mb-3 font-medium text-gray-900 dark:text-gray-100">
                <em class="mr-2 ni ni-tool"></em>
                Actions Rapides Délibération
            </h4>
            <div class="flex flex-wrap gap-3">
                {{-- Appliquer logique médecine standard --}}
                <button wire:click="appliquerLogiqueStandard('{{ $deliberationParams['session_type'] ?? 'session1' }}')"
                        wire:confirm="Appliquer la logique médecine standard (60 crédits S1, 40 crédits S2) ?"
                        class="px-4 py-2 text-sm text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 dark:text-blue-300 dark:bg-blue-900/50 dark:hover:bg-blue-900">
                    <em class="mr-1 ni ni-shield-check"></em>
                    Logique Médecine Standard
                </button>
                {{-- Restaurer dernières valeurs --}}
                @if(isset($this->dernieresValeursDeliberation['session1']) || isset($this->dernieresValeursDeliberation['session2']))
                    <button wire:click="restaurerDernieresValeurs"
                            wire:confirm="Restaurer les dernières valeurs de délibération utilisées ?"
                            class="px-4 py-2 text-sm text-purple-700 bg-purple-100 rounded-lg hover:bg-purple-200 dark:text-purple-300 dark:bg-purple-900/50 dark:hover:bg-purple-900">
                        <em class="mr-1 ni ni-history"></em>
                        Restaurer Dernières Valeurs
                    </button>
                @endif
                {{-- Recalculer tout --}}
                <button wire:click="recalculerTout"
                        wire:confirm="Recalculer toutes les sessions disponibles selon la logique médecine ?"
                        class="px-4 py-2 text-sm text-green-700 bg-green-100 rounded-lg hover:bg-green-200 dark:text-green-300 dark:bg-green-900/50 dark:hover:bg-green-900">
                    <em class="mr-1 ni ni-refresh"></em>
                    Recalculer Tout
                </button>
            </div>

            {{-- Informations sur la délibération avec protection --}}
            @if(isset($statistiquesDeliberation[$deliberationParams['session_type'] ?? 'session1']))
                @php
                    $sessionType = $deliberationParams['session_type'] ?? 'session1';
                    $statsDelib = $statistiquesDeliberation[$sessionType] ?? null;
                @endphp
                @if($statsDelib && ($statsDelib['configuration_existante'] ?? false))
                    <div class="p-3 mt-4 bg-blue-100 rounded-lg dark:bg-blue-800/30">
                        <div class="text-sm text-blue-800 dark:text-blue-200">
                            <p><strong>Configuration actuelle :</strong></p>
                            <div class="grid grid-cols-2 gap-4 mt-2 md:grid-cols-4">
                                <div>Crédits S1: {{ ($statsDelib['parametres']['credits_admission_s1'] ?? 60) }}</div>
                                <div>Crédits S2: {{ ($statsDelib['parametres']['credits_admission_s2'] ?? 40) }}</div>
                                <div>Redoublement: {{ ($statsDelib['parametres']['credits_redoublement_s2'] ?? 20) }}</div>
                                <div>
                                    Note 0: {{ (($statsDelib['parametres']['note_eliminatoire_bloque_s1'] ?? true)) ? 'Bloque' : 'Autorisée' }}
                                </div>
                            </div>
                            @if($statsDelib['delibere'] ?? false)
                                <p class="mt-2 text-xs">
                                    <em class="mr-1 ni ni-check-circle"></em>
                                    Délibérée le {{ ($statsDelib['date_deliberation'] ?? 'Date inconnue') }}
                                    par {{ ($statsDelib['delibere_par'] ?? 'Utilisateur inconnu') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- ✅ RÉSULTATS DE SIMULATION AVEC PROTECTION CONTRE LES ERREURS --}}
        @if(!empty($simulationDeliberation))
            <div class="bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <em class="mr-2 ni ni-bar-chart"></em>
                            Résultats de la Simulation Délibération
                        </h3>
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1 text-sm font-medium text-purple-700 bg-purple-100 rounded-full dark:text-purple-300 dark:bg-purple-900/50">
                                {{ ucfirst($deliberationParams['session_type'] ?? 'session1') }}
                            </span>

                            {{-- ✅ DROPDOWN EXPORT APRÈS SIMULATION --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                        class="flex items-center px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                                    <em class="mr-2 ni ni-download"></em>
                                    Export Résultats
                                    <em class="ml-2 transition-transform ni ni-chevron-down" :class="{ 'rotate-180': open }"></em>
                                </button>

                                <div x-show="open"
                                    @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 z-10 w-64 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-700">

                                    <div class="py-2">
                                        {{-- En-tête --}}
                                        <div class="px-4 py-2 text-xs font-medium text-gray-500 uppercase border-b border-gray-200 dark:text-gray-400 dark:border-gray-700">
                                            Exports avec Configuration
                                        </div>

                                        {{-- Export PDF configuré --}}
                                        <button wire:click="ouvrirModalExport('pdf', 'simulation')"
                                                @click="open = false"
                                                class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                            <em class="mr-3 text-red-500 ni ni-file-pdf"></em>
                                            <div class="flex-1">
                                                <div>Export PDF</div>
                                                <div class="text-xs text-gray-500">Choisir colonnes et filtres</div>
                                            </div>
                                            <em class="text-gray-400 ni ni-setting"></em>
                                        </button>

                                        {{-- Export Excel configuré --}}
                                        <button wire:click="ouvrirModalExport('excel', 'simulation')"
                                                @click="open = false"
                                                class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                            <em class="mr-3 text-green-500 ni ni-file-xls"></em>
                                            <div class="flex-1">
                                                <div>Export Excel</div>
                                                <div class="text-xs text-gray-500">Choisir colonnes et filtres</div>
                                            </div>
                                            <em class="text-gray-400 ni ni-setting"></em>
                                        </button>

                                        {{-- Séparateur --}}
                                        <div class="my-1 border-t border-gray-200 dark:border-gray-700"></div>

                                        {{-- En-tête exports rapides --}}
                                        <div class="px-4 py-1 text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                                            Exports Rapides
                                        </div>

                                        {{-- Export rapide tous PDF --}}
                                        <button wire:click="exporterTousSimulation('pdf')"
                                                @click="open = false"
                                                class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                            <em class="mr-3 text-red-400 ni ni-file-pdf"></em>
                                            <div class="flex-1">
                                                <div>Tous (PDF)</div>
                                                <div class="text-xs text-gray-500">Colonnes par défaut</div>
                                            </div>
                                            <span class="text-xs font-medium text-blue-600">{{ ($simulationDeliberation['total_etudiants'] ?? 0) }}</span>
                                        </button>

                                        {{-- Export rapide tous Excel --}}
                                        <button wire:click="exporterTousSimulation('excel')"
                                                @click="open = false"
                                                class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                            <em class="mr-3 text-green-400 ni ni-file-xls"></em>
                                            <div class="flex-1">
                                                <div>Tous (Excel)</div>
                                                <div class="text-xs text-gray-500">Colonnes par défaut</div>
                                            </div>
                                            <span class="text-xs font-medium text-blue-600">{{ ($simulationDeliberation['total_etudiants'] ?? 0) }}</span>
                                        </button>

                                        {{-- Séparateur --}}
                                        <div class="my-1 border-t border-gray-200 dark:border-gray-700"></div>

                                        {{-- En-tête exports par décision --}}
                                        <div class="px-4 py-1 text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                                            Par Décision
                                        </div>

                                        @php
                                            $stats = $simulationDeliberation['statistiques'] ?? [];
                                            $sessionType = $deliberationParams['session_type'] ?? 'session1';
                                        @endphp

                                        {{-- Export admis si disponibles --}}
                                        @if(($stats['admis'] ?? 0) > 0)
                                            <button wire:click="exporterParDecisionSimulation('admis', 'pdf')"
                                                    @click="open = false"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-green-50 dark:text-gray-300 dark:hover:bg-green-900/20">
                                                <em class="mr-3 text-green-500 ni ni-check-circle"></em>
                                                <div class="flex-1">
                                                    <div>Admis (PDF)</div>
                                                    <div class="text-xs text-green-600">Étudiants admis uniquement</div>
                                                </div>
                                                <span class="text-xs font-medium text-green-600">{{ ($stats['admis'] ?? 0) }}</span>
                                            </button>
                                        @endif

                                        {{-- Export selon le type de session --}}
                                        @if($sessionType === 'session1')
                                            {{-- Session 1: Rattrapage --}}
                                            @if(($stats['rattrapage'] ?? 0) > 0)
                                                <button wire:click="exporterParDecisionSimulation('rattrapage', 'pdf')"
                                                        @click="open = false"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-orange-50 dark:text-gray-300 dark:hover:bg-orange-900/20">
                                                    <em class="mr-3 text-orange-500 ni ni-clock"></em>
                                                    <div class="flex-1">
                                                        <div>Rattrapage (PDF)</div>
                                                        <div class="text-xs text-orange-600">Étudiants en rattrapage</div>
                                                    </div>
                                                    <span class="text-xs font-medium text-orange-600">{{ ($stats['rattrapage'] ?? 0) }}</span>
                                                </button>
                                            @endif
                                        @else
                                            {{-- Session 2: Redoublants et Exclus --}}
                                            @if(($stats['redoublant'] ?? 0) > 0)
                                                <button wire:click="exporterParDecisionSimulation('redoublant', 'pdf')"
                                                        @click="open = false"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-red-50 dark:text-gray-300 dark:hover:bg-red-900/20">
                                                    <em class="mr-3 text-red-500 ni ni-refresh"></em>
                                                    <div class="flex-1">
                                                        <div>Redoublants (PDF)</div>
                                                        <div class="text-xs text-red-600">Étudiants redoublants</div>
                                                    </div>
                                                    <span class="text-xs font-medium text-red-600">{{ ($stats['redoublant'] ?? 0) }}</span>
                                                </button>
                                            @endif

                                            @if(($stats['exclus'] ?? 0) > 0)
                                                <button wire:click="exporterParDecisionSimulation('exclus', 'pdf')"
                                                        @click="open = false"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-red-100 dark:text-gray-300 dark:hover:bg-red-900/30">
                                                    <em class="mr-3 text-red-800 ni ni-times-circle"></em>
                                                    <div class="flex-1">
                                                        <div>Exclus (PDF)</div>
                                                        <div class="text-xs text-red-800">Étudiants exclus</div>
                                                    </div>
                                                    <span class="text-xs font-medium text-red-800">{{ ($stats['exclus'] ?? 0) }}</span>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Bouton Appliquer Délibération --}}
                            @if(($simulationDeliberation['statistiques']['changements'] ?? 0) > 0)
                                <button wire:click="appliquerDeliberation"
                                        wire:confirm="Êtes-vous sûr de vouloir appliquer cette délibération ? Cette action mettra à jour les décisions et marquera la session comme délibérée."
                                        class="flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-200 bg-orange-600 rounded-lg shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 hover:shadow-md">
                                    <em class="mr-2 ni ni-check-circle"></em>
                                    <span>Appliquer Délibération</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ✅ STATISTIQUES DE SIMULATION AVEC PROTECTION --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50">
                    @php
                        // ✅ PROTECTION CONTRE LES ERREURS ET VALEURS MANQUANTES
                        if (!empty($simulationDeliberation['statistiques'])) {
                            $stats = $simulationDeliberation['statistiques'];
                            $totalSimulation = $simulationDeliberation['total_etudiants'] ?? 0;
                            $changements = $stats['changements'] ?? 0;
                            $admisSimulation = $stats['admis'] ?? 0;
                            $rattrapageSimulation = $stats['rattrapage'] ?? 0;
                            $redoublantsSimulation = $stats['redoublant'] ?? 0;
                            $exclusSimulation = $stats['exclus'] ?? 0;
                            $sourceStats = 'simulation';
                        } else {
                            // Fallback vers les statistiques actuelles
                            $statsActuelles = $activeTab === 'session1' ? ($statistiquesSession1 ?? []) : ($statistiquesSession2 ?? []);
                            $totalSimulation = $statsActuelles['total_etudiants'] ?? 0;
                            $changements = 0;
                            $admisSimulation = $statsActuelles['admis'] ?? 0;
                            $rattrapageSimulation = $statsActuelles['rattrapage'] ?? 0;
                            $redoublantsSimulation = $statsActuelles['redoublant'] ?? 0;
                            $exclusSimulation = $statsActuelles['exclus'] ?? 0;
                            $sourceStats = 'actuelle';
                        }
                    @endphp

                    <div class="grid grid-cols-2 gap-4 mb-2 md:grid-cols-6">
                        {{-- Total étudiants --}}
                        <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalSimulation }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total étudiants</div>
                            @if($sourceStats === 'simulation')
                                <div class="mt-1 text-xs text-blue-500">✨ Simulé</div>
                            @endif
                        </div>

                        {{-- Changements (uniquement si simulation) --}}
                        @if($sourceStats === 'simulation')
                            <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $changements }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Changements</div>
                                <div class="mt-1 text-xs text-orange-500">vs Actuel</div>
                            </div>
                        @endif

                        {{-- Admis --}}
                        <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $admisSimulation }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Admis</div>
                            @if($sourceStats === 'simulation')
                                <div class="mt-1 text-xs text-green-500">✨ Nouveau</div>
                            @endif
                        </div>

                        {{-- ✅ AFFICHAGE CONDITIONNEL SELON LE TYPE DE SESSION --}}
                        @if(($deliberationParams['session_type'] ?? 'session1') === 'session1')
                            {{-- Session 1 : Afficher Rattrapage --}}
                            <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $rattrapageSimulation }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Rattrapage</div>
                                @if($sourceStats === 'simulation')
                                    <div class="mt-1 text-xs text-blue-500">✨ Nouveau</div>
                                @endif
                            </div>
                        @else
                            {{-- Session 2 : Afficher Redoublants --}}
                            <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $redoublantsSimulation }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Redoublants</div>
                                @if($sourceStats === 'simulation')
                                    <div class="mt-1 text-xs text-red-500">✨ Nouveau</div>
                                @endif
                            </div>
                        @endif

                        {{-- ✅ EXCLUS : AFFICHAGE POUR TOUTES LES SESSIONS --}}
                        <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                            <div class="text-2xl font-bold text-red-800 dark:text-red-300">{{ $exclusSimulation }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Exclus</div>
                            @if($sourceStats === 'simulation')
                                <div class="mt-1 text-xs text-red-500">✨ Nouveau</div>
                            @endif
                        </div>
                    </div>

                    {{-- ✅ MESSAGE DE CHANGEMENTS ADAPTATIF --}}
                    @if($sourceStats === 'simulation')
                        @if($changements > 0)
                            <div class="p-3 mb-4 bg-orange-100 border border-orange-300 rounded-lg dark:bg-orange-900/30 dark:border-orange-800">
                                <p class="text-sm text-orange-800 dark:text-orange-200">
                                    <em class="mr-2 ni ni-alert-circle"></em>
                                    <strong>{{ $changements }} changement(s) détecté(s)</strong> par rapport aux décisions actuelles.
                                    Utilisez le bouton "Appliquer Délibération" pour valider ces modifications.
                                </p>
                            </div>
                        @else
                            <div class="p-3 mb-4 bg-green-100 border border-green-300 rounded-lg dark:bg-green-900/30 dark:border-green-800">
                                <p class="text-sm text-green-800 dark:text-green-200">
                                    <em class="mr-2 ni ni-check-circle"></em>
                                    <strong>Aucun changement</strong> par rapport aux décisions actuelles avec ces paramètres.
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="p-3 mb-4 bg-blue-100 border border-blue-300 rounded-lg dark:bg-blue-900/30 dark:border-blue-800">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <em class="mr-2 ni ni-info-circle"></em>
                                <strong>Statistiques actuelles</strong> basées sur les décisions enregistrées en base de données.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- ✅ TABLEAU DES RÉSULTATS DÉTAILLÉS AVEC PROTECTION --}}
                @if(!empty($simulationDeliberation['resultats_detailles']))
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                        Rang
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Étudiant
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                        Moyenne
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                        Crédits
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                        Décision Actuelle
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                        Décision Simulée
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                        Impact
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @foreach($simulationDeliberation['resultats_detailles'] as $index => $result)
                                    <tr class="{{ ($result['changement'] ?? false) ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                        {{-- Rang --}}
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ ($result['rang'] ?? ($index + 1)) }}
                                            </span>
                                        </td>

                                        {{-- Informations étudiant avec protection --}}
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ ($result['etudiant']['nom'] ?? $result['nom'] ?? 'Nom inconnu') }} {{ ($result['etudiant']['prenom'] ?? $result['prenom'] ?? 'Prénom inconnu') }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ ($result['etudiant']['matricule'] ?? $result['matricule'] ?? 'Matricule inconnu') }}
                                            </div>
                                        </td>

                                        {{-- Moyenne --}}
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <span class="text-sm font-medium {{ (($result['moyenne_generale'] ?? 0) >= 10) ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ number_format(($result['moyenne_generale'] ?? 0), 2) }}
                                            </span>
                                            @if($result['has_note_eliminatoire'] ?? false)
                                                <span class="ml-1 text-red-500 dark:text-red-400" title="Note éliminatoire">⚠️</span>
                                            @endif
                                        </td>

                                        {{-- Crédits --}}
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ ($result['credits_valides'] ?? $result['total_credits'] ?? 0) }}/60
                                            </span>
                                        </td>

                                        {{-- Décision actuelle --}}
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            @if(isset($result['decision_actuelle']) && !empty($result['decision_actuelle']))
                                                @php
                                                    $decisionClass = match($result['decision_actuelle']) {
                                                        'admis' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300',
                                                        'rattrapage' => 'bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-300',
                                                        'redoublant' => 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300',
                                                        'exclus' => 'bg-red-200 dark:bg-red-900/70 text-red-900 dark:text-red-200',
                                                        default => 'bg-gray-100 dark:bg-gray-900/50 text-gray-800 dark:text-gray-300'
                                                    };
                                                @endphp
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $decisionClass }}">
                                                    {{ ucfirst($result['decision_actuelle']) }}
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full dark:bg-gray-900/50 dark:text-gray-300">
                                                    Non définie
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Décision simulée --}}
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            @php
                                                $decisionSimulee = $result['decision_simulee'] ?? 'non_definie';
                                                $decisionClass = match($decisionSimulee) {
                                                    'admis' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300',
                                                    'rattrapage' => 'bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-300',
                                                    'redoublant' => 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300',
                                                    'exclus' => 'bg-red-200 dark:bg-red-900/70 text-red-900 dark:text-red-200',
                                                    default => 'bg-gray-100 dark:bg-gray-900/50 text-gray-800 dark:text-gray-300'
                                                };
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $decisionClass }}">
                                                {{ ucfirst($decisionSimulee) }}
                                            </span>
                                        </td>

                                        {{-- Impact --}}
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            @if($result['changement'] ?? false)
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full dark:text-orange-300 dark:bg-orange-900/50">
                                                    <em class="mr-1 ni ni-alert-circle"></em>
                                                    Modifié
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:text-green-300 dark:bg-green-900/50">
                                                    <em class="mr-1 ni ni-check-circle"></em>
                                                    Identique
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- ✅ SECTION EXPORT APRÈS LE TABLEAU --}}
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <p class="font-medium">📊 Résultats de simulation prêts pour export</p>
                                <p>{{ $totalSimulation }} étudiants • {{ $changements }} changements détectés</p>
                            </div>
                            <div class="flex space-x-2">
                                {{-- Export rapide colonnes essentielles --}}
                                <button wire:click="exporterAdmisRapide('pdf')"
                                        class="px-3 py-2 text-sm text-red-700 bg-red-100 rounded-lg hover:bg-red-200 dark:text-red-300 dark:bg-red-900/50 dark:hover:bg-red-900">
                                    <em class="mr-1 ni ni-file-pdf"></em>
                                    Export admis en PDF
                                </button>
                                <button wire:click="exporterTousSimulation('excel')"
                                        class="px-3 py-2 text-sm text-green-700 bg-green-100 rounded-lg hover:bg-green-200 dark:text-green-300 dark:bg-green-900/50 dark:hover:bg-green-900">
                                    <em class="mr-1 ni ni-file-xls"></em>
                                    Export en Excel
                                </button>
                                {{-- Export configuré --}}
                                <button wire:click="ouvrirModalExport('pdf', 'simulation')"
                                        class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                                    <em class="mr-2 ni ni-settings"></em>
                                    Export Configuré
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif

{{-- ✅ MESSAGES D'INDISPONIBILITÉ --}}
@if($activeTab === 'simulation' && empty($resultatsSession1) && empty($resultatsSession2))
    <div class="py-12 text-center">
        <em class="text-6xl text-purple-400 ni ni-setting dark:text-purple-500"></em>
        <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
            Simulation de délibération non disponible
        </p>
        <p class="text-sm text-gray-400 dark:text-gray-500">
            @if($selectedNiveau && $selectedAnneeUniversitaire)
                Aucun résultat publié pour effectuer une simulation pour ce niveau.
            @else
                Veuillez d'abord sélectionner un niveau et une année universitaire.
            @endif
        </p>

        {{-- Actions de récupération --}}
        @if($selectedNiveau && $selectedAnneeUniversitaire)
            <div class="max-w-md p-4 mx-auto mt-6 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                <p class="mb-3 text-sm text-blue-700 dark:text-blue-300">
                    Aucun résultat disponible pour ce niveau et cette année.
                </p>
                <div class="flex flex-col space-y-2">
                    <button wire:click="loadResultats"
                            class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        <em class="mr-1 ni ni-refresh"></em>
                        Recharger les Résultats
                    </button>
                    @if($sessionNormale)
                        <button wire:click="appliquerLogiqueStandard('session1')"
                                class="px-4 py-2 text-sm text-green-700 bg-green-100 rounded-lg hover:bg-green-200 dark:text-green-300 dark:bg-green-800 dark:hover:bg-green-700">
                            <em class="mr-1 ni ni-calculator"></em>
                            Calculer Résultats Session 1
                        </button>
                    @endif
                    @if($sessionRattrapage)
                        <button wire:click="appliquerLogiqueStandard('session2')"
                                class="px-4 py-2 text-sm text-orange-700 bg-orange-100 rounded-lg hover:bg-orange-200 dark:text-orange-300 dark:bg-orange-800 dark:hover:bg-orange-700">
                            <em class="mr-1 ni ni-calculator"></em>
                            Calculer Résultats Session 2
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endif

{{-- ✅ MODAL D'EXPORT AVEC PROTECTION CONTRE LES ERREURS --}}
@if($showExportModal ?? false)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="export-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"
                 wire:click="fermerModalExport"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Content --}}
            <div class="inline-block w-full max-w-4xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full dark:bg-blue-900">
                                @if(($exportType ?? 'pdf') === 'pdf')
                                    <em class="text-red-600 ni ni-file-pdf dark:text-red-400"></em>
                                @else
                                    <em class="text-green-600 ni ni-file-excel dark:text-green-400"></em>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" id="export-modal-title">
                                    Configuration Export {{ strtoupper($exportType ?? 'PDF') }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Source : {{ ucfirst($exportData ?? 'simulation') }}
                                    @if(($exportData ?? '') === 'simulation' && !empty($simulationDeliberation))
                                        ({{ ($simulationDeliberation['total_etudiants'] ?? 0) }} étudiants)
                                    @endif
                                </p>
                            </div>
                        </div>
                        <button wire:click="fermerModalExport"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <em class="text-xl ni ni-times"></em>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 overflow-y-auto max-h-96">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                        {{-- ✅ SECTION 1: Sélection des Colonnes --}}
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                <em class="mr-2 ni ni-list"></em>
                                Colonnes à Exporter
                            </h4>

                            <div class="space-y-3">
                                {{-- Rang --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['rang'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.rang"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Rang</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Numérotation des étudiants par ordre de classement</div>
                                    </div>
                                </label>

                                {{-- Nom et Prénom --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['nom_complet'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.nom_complet"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Nom et Prénom</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Nom complet de l'étudiant</div>
                                    </div>
                                </label>

                                {{-- Matricule --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['matricule'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.matricule"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Matricule</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Numéro d'identification de l'étudiant</div>
                                    </div>
                                </label>

                                {{-- Moyenne --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['moyenne'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.moyenne"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Moyenne Générale</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Moyenne générale de l'étudiant (/20)</div>
                                    </div>
                                </label>

                                {{-- Crédits --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['credits'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.credits"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Crédits</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Crédits validés / Total des crédits</div>
                                    </div>
                                </label>

                                {{-- Décision --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['decision'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.decision"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Décision</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Décision académique (Admis, Rattrapage, etc.)</div>
                                    </div>
                                </label>

                                {{-- Niveau (optionnel) --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['niveau'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.niveau"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Niveau</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Niveau d'études (optionnel)</div>
                                    </div>
                                </label>
                            </div>

                            {{-- Actions rapides pour les colonnes --}}
                            <div class="flex gap-2 pt-3 border-t border-gray-200 dark:border-gray-600">
                                <button wire:click="selectionnerToutesColonnes"
                                        class="px-3 py-2 text-xs text-blue-700 bg-blue-100 rounded hover:bg-blue-200 dark:text-blue-300 dark:bg-blue-900/50 dark:hover:bg-blue-900">
                                    Tout sélectionner
                                </button>
                                <button wire:click="deselectionnerToutesColonnes"
                                        class="px-3 py-2 text-xs text-gray-700 bg-gray-100 rounded hover:bg-gray-200 dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600">
                                    Tout désélectionner
                                </button>
                                <button wire:click="resetConfigExport"
                                        class="px-3 py-2 text-xs text-orange-700 bg-orange-100 rounded hover:bg-orange-200 dark:text-orange-300 dark:bg-orange-900/50 dark:hover:bg-orange-900">
                                    Configuration par défaut
                                </button>
                            </div>
                        </div>

                        {{-- ✅ SECTION 2: Filtres et Tri --}}
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                <em class="mr-2 ni ni-filter"></em>
                                Filtres et Tri
                            </h4>

                            {{-- Filtre par décision --}}
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Filtrer par décision
                                </label>
                                <select wire:model="exportConfig.filtres.decision_filter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="tous">Toutes les décisions</option>
                                    <option value="admis">Admis uniquement</option>
                                    <option value="rattrapage">Rattrapage uniquement</option>
                                    <option value="redoublant">Redoublants uniquement</option>
                                    <option value="exclus">Exclus uniquement</option>
                                </select>
                            </div>

                            {{-- Filtre par moyenne --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Moyenne minimum
                                    </label>
                                    <input type="number" step="0.01" min="0" max="20"
                                           wire:model="exportConfig.filtres.moyenne_min"
                                           placeholder="0.00"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Moyenne maximum
                                    </label>
                                    <input type="number" step="0.01" min="0" max="20"
                                           wire:model="exportConfig.filtres.moyenne_max"
                                           placeholder="20.00"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            {{-- Tri --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Trier par
                                    </label>
                                    <select wire:model="exportConfig.tri.champ"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="rang">Rang</option>
                                        <option value="nom_complet">Nom</option>
                                        <option value="moyenne_generale">Moyenne</option>
                                        <option value="credits_valides">Crédits</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Ordre
                                    </label>
                                    <select wire:model="exportConfig.tri.ordre"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="asc">Croissant</option>
                                        <option value="desc">Décroissant</option>
                                    </select>
                                </div>
                            </div>

                            {{-- ✅ APERÇU DES STATISTIQUES AVEC PROTECTION --}}
                            @php
                                // Protéger l'appel à getStatistiquesExportPreview
                                try {
                                    $statsPreview = method_exists($this, 'getStatistiquesExportPreview') ? $this->getStatistiquesExportPreview() : null;
                                } catch (\Exception $e) {
                                    $statsPreview = null;
                                }
                            @endphp
                            @if($statsPreview && is_array($statsPreview))
                                <div class="p-3 bg-gray-100 rounded-lg dark:bg-gray-700">
                                    <h5 class="mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Aperçu des données
                                    </h5>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div>Total initial: {{ ($statsPreview['total_initial'] ?? 0) }}</div>
                                        <div>Après filtres: {{ ($statsPreview['total_filtre'] ?? 0) }}</div>
                                        @if(($statsPreview['total_filtre'] ?? 0) > 0)
                                            <div>Moy. min: {{ number_format(($statsPreview['moyenne_min'] ?? 0), 2) }}</div>
                                            <div>Moy. max: {{ number_format(($statsPreview['moyenne_max'] ?? 0), 2) }}</div>
                                        @endif
                                    </div>
                                    @if(!empty($statsPreview['decisions']) && is_array($statsPreview['decisions']))
                                        <div class="grid grid-cols-2 gap-1 mt-2 text-xs">
                                            @foreach($statsPreview['decisions'] as $decision => $count)
                                                @if($count > 0)
                                                    <div>{{ ucfirst($decision) }}: {{ $count }}</div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Messages d'erreur --}}
                    @if($errors->has('export'))
                        <div class="p-3 mt-4 text-red-700 bg-red-100 border border-red-400 rounded dark:text-red-300 dark:bg-red-900/50 dark:border-red-800">
                            {{ $errors->first('export') }}
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Format: <strong>{{ strtoupper($exportType ?? 'PDF') }}</strong> •
                            Source: <strong>{{ ucfirst($exportData ?? 'simulation') }}</strong>
                            @if(isset($statsPreview) && is_array($statsPreview))
                                • <strong>{{ ($statsPreview['total_filtre'] ?? 0) }}</strong> résultats sélectionnés
                            @endif
                        </div>
                        <div class="flex space-x-3">
                            <button wire:click="fermerModalExport"
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500 dark:hover:bg-gray-500">
                                Annuler
                            </button>
                            <button wire:click="genererExportAvecConfig"
                                    type="button"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                @if(($exportType ?? 'pdf') === 'pdf')
                                    <em class="mr-2 ni ni-file-pdf"></em>
                                @else
                                    <em class="mr-2 ni ni-file-excel"></em>
                                @endif
                                Générer {{ strtoupper($exportType ?? 'PDF') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ✅ SCRIPTS JAVASCRIPT POUR LES INTERACTIONS AVEC PROTECTION --}}
@push('scripts')
<script>
    // ✅ PROTECTION CONTRE LES ERREURS JAVASCRIPT
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier que Livewire est disponible
        if (typeof Livewire === 'undefined') {
            console.warn('Livewire non disponible');
            return;
        }

        // Fonction pour exporter par décision depuis la simulation
        window.exporterParDecisionSimulation = function(decision, format = 'pdf') {
            try {
                // Configurer l'export pour cette décision spécifique
                @this.set('exportConfig.filtres.decision_filter', decision);
                @this.set('exportConfig.tri.champ', 'moyenne_generale');
                @this.set('exportConfig.tri.ordre', 'desc');
                @this.set('exportData', 'simulation');
                @this.set('exportType', format);

                // Générer directement sans modal
                @this.call('genererExportAvecConfig');
            } catch (error) {
                console.error('Erreur lors de l\'export par décision:', error);
                alert('Erreur lors de l\'export. Veuillez réessayer.');
            }
        };

        // Fonction pour export rapide avec choix du format
        window.exportRapideSimulation = function(format, decision = 'tous') {
            try {
                if (decision !== 'tous') {
                    @this.set('exportConfig.filtres.decision_filter', decision);
                }
                @this.set('exportType', format);
                @this.set('exportData', 'simulation');
                @this.call('genererExportAvecConfig');
            } catch (error) {
                console.error('Erreur lors de l\'export rapide:', error);
                alert('Erreur lors de l\'export. Veuillez réessayer.');
            }
        };

        // Fonction pour sélectionner rapidement les colonnes essentielles
        window.selectionnerColonnesEssentielles = function() {
            try {
                @this.set('exportConfig.colonnes', {
                    'rang': true,
                    'nom_complet': true,
                    'matricule': true,
                    'moyenne': true,
                    'credits': true,
                    'decision': true,
                    'niveau': false
                });
            } catch (error) {
                console.error('Erreur lors de la sélection des colonnes:', error);
            }
        };

        // Fonction pour sélectionner uniquement les colonnes minimales
        window.selectionnerColonnesMinimales = function() {
            try {
                @this.set('exportConfig.colonnes', {
                    'rang': true,
                    'nom_complet': true,
                    'decision': true,
                    'matricule': false,
                    'moyenne': false,
                    'credits': false,
                    'niveau': false
                });
            } catch (error) {
                console.error('Erreur lors de la sélection minimale:', error);
            }
        };
    });

    // ✅ ACTUALISER LES STATISTIQUES APRÈS UNE DÉLIBÉRATION AVEC PROTECTION
    document.addEventListener('livewire:updated', function () {
        try {
            // Vérifier que les méthodes existent avant de les appeler
            if (window.livewire && @this && typeof @this.loadResultats === 'function') {
                // Recharger automatiquement après application de délibération
                setTimeout(() => {
                    try {
                        @this.loadResultats();
                    } catch (error) {
                        console.error('Erreur lors du rechargement des résultats:', error);
                    }
                }, 500);
            }
        } catch (error) {
            console.error('Erreur dans livewire:updated:', error);
        }
    });

    // ✅ OBSERVER LES CHANGEMENTS DE SIMULATION AVEC PROTECTION
    document.addEventListener('simulation-applied', function () {
        try {
            // Force le rechargement des données après application de simulation
            if (@this && typeof @this.refreshData === 'function') {
                @this.refreshData();
            }
        } catch (error) {
            console.error('Erreur lors du refresh après simulation:', error);
        }
    });

    // ✅ GESTION DES ERREURS GLOBALES JAVASCRIPT
    window.addEventListener('error', function(e) {
        // Logger les erreurs mais ne pas les afficher à l'utilisateur sauf si critique
        console.error('Erreur JavaScript globale:', e.error);

        // Seulement afficher une alerte pour les erreurs critiques
        if (e.error && e.error.message && e.error.message.includes('critical')) {
            alert('Une erreur critique s\'est produite. Veuillez actualiser la page.');
        }
    });

    // ✅ PROTECTION CONTRE LES ERREURS LIVEWIRE
    document.addEventListener('livewire:error', function (event) {
        console.error('Erreur Livewire:', event.detail);

        // Afficher un message d'erreur utilisateur-friendly
        if (event.detail && event.detail.message) {
            const errorMessage = event.detail.message;

            // Messages d'erreur spécifiques
            if (errorMessage.includes('getStatistiquesExportPreview')) {
                console.warn('Erreur dans les statistiques d\'export, mais continuons...');
                return; // Ne pas afficher cette erreur à l'utilisateur
            }

            if (errorMessage.includes('Method') && errorMessage.includes('does not exist')) {
                alert('Une fonctionnalité n\'est pas encore disponible. Veuillez contacter l\'administrateur.');
                return;
            }

            // Erreur générique
            alert('Une erreur s\'est produite. Veuillez réessayer ou actualiser la page.');
        }
    });
</script>
@endpush

{{-- ✅ STYLES CSS POUR AMÉLIORER L'EXPÉRIENCE UTILISATEUR --}}
@push('styles')
<style>
    /* Protection contre le clignotement lors des rechargements */
    [wire\:loading] {
        opacity: 0.7;
        pointer-events: none;
        transition: opacity 0.2s ease-in-out;
    }

    /* Indicateur de chargement subtil */
    [wire\:loading.delay] {
        opacity: 1;
    }

    /* Amélioration de l'accessibilité pour les boutons désactivés */
    button[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Style pour les éléments en erreur */
    .error-highlight {
        border: 2px solid #ef4444 !important;
        background-color: #fef2f2 !important;
    }

    /* Animation pour les changements de délibération */
    .deliberation-change {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }

    /* Style pour les alertes temporaires */
    .alert-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Protection contre le débordement sur mobile */
    @media (max-width: 768px) {
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }

        .modal-content {
            margin: 10px;
            max-height: calc(100vh - 20px);
        }
    }
</style>
@endpush