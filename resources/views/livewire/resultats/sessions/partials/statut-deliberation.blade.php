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