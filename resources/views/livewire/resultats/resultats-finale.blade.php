<div class="p-6 bg-white rounded-lg shadow-sm dark:bg-gray-800">
    <!-- Header avec indicateur de session active -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Résultats Finaux des Examens
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Gestion des sessions normale et rattrapage avec simulation de délibération
                    </p>
                </div>
            </div>

            <!-- Actions Export & Rafraîchissement -->
            <div class="flex items-center space-x-3">
                <!-- Bouton Rafraîchir -->
                <button wire:click="refreshData"
                        wire:loading.attr="disabled"
                        class="flex items-center px-4 py-2 text-gray-600 transition-all duration-200 border border-gray-200 rounded-lg bg-gray-50 hover:bg-gray-100 hover:border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 disabled:opacity-50">
                    <svg wire:loading.remove class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <div wire:loading class="w-5 h-5 mr-2">
                        <div class="w-4 h-4 border-2 border-gray-300 rounded-full border-t-blue-500 animate-spin"></div>
                    </div>
                    <span class="text-sm font-medium">Actualiser</span>
                </button>

                <!-- Dropdown Export -->
                @if($canExport)
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false"
                                class="flex items-center px-4 py-2 text-white transition-all duration-200 bg-blue-600 rounded-lg hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-sm font-medium">Exporter</span>
                            <svg class="w-4 h-4 ml-2 transition-transform duration-200"
                                :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Menu Export -->
                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 z-50 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg w-72 dark:bg-gray-800 dark:border-gray-700">

                            <!-- Export Session 1 -->
                            <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-center mb-3">
                                    <div class="flex items-center justify-center w-6 h-6 mr-3 bg-blue-500 rounded-md">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Session Normale</span>
                                        @if(!empty($resultatsSession1))
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ count($resultatsSession1) }} étudiants</div>
                                        @endif
                                    </div>
                                </div>
                                @if(!empty($resultatsSession1))
                                    <div class="grid grid-cols-2 gap-2">
                                        <button wire:click="exportResults('session1')" @click="open = false"
                                                class="flex items-center justify-center px-3 py-2 text-sm text-green-700 transition-colors duration-150 rounded bg-green-50 hover:bg-green-100 dark:text-green-400 dark:bg-green-900/20 dark:hover:bg-green-900/40">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"/>
                                            </svg>
                                            Excel
                                        </button>
                                        <button wire:click="exportPDF('session1')" @click="open = false"
                                                class="flex items-center justify-center px-3 py-2 text-sm text-red-700 transition-colors duration-150 rounded bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6"/>
                                            </svg>
                                            PDF
                                        </button>
                                    </div>
                                @else
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Aucun résultat disponible</div>
                                @endif
                            </div>

                            <!-- Export Session 2 -->
                            @if($showSession2)
                                <div class="p-4">
                                    <div class="flex items-center mb-3">
                                        <div class="flex items-center justify-center w-6 h-6 mr-3 bg-green-500 rounded-md">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Session Rattrapage</span>
                                            @if(!empty($resultatsSession2))
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ count($resultatsSession2) }} étudiants</div>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!empty($resultatsSession2))
                                        <div class="grid grid-cols-2 gap-2">
                                            <button wire:click="exportResults('session2')" @click="open = false"
                                                    class="flex items-center justify-center px-3 py-2 text-sm text-green-700 transition-colors duration-150 rounded bg-green-50 hover:bg-green-100 dark:text-green-400 dark:bg-green-900/20 dark:hover:bg-green-900/40">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"/>
                                                </svg>
                                                Excel
                                            </button>
                                            <button wire:click="exportPDF('session2')" @click="open = false"
                                                    class="flex items-center justify-center px-3 py-2 text-sm text-red-700 transition-colors duration-150 rounded bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6"/>
                                                </svg>
                                                PDF
                                            </button>
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Aucun résultat disponible</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Indicateur de session active avec statut -->
        @if($sessionNormale || $sessionRattrapage)
            <div class="p-4 mb-6 border rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Session Normale -->
                        @if($sessionNormale)
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                                <div>
                                    <div class="text-sm font-semibold text-blue-900 dark:text-blue-300">
                                        Session Normale ({{ $sessionNormale->libelle }})
                                    </div>
                                    <div class="text-xs text-blue-700 dark:text-blue-400">
                                        {{ !empty($resultatsSession1) ? count($resultatsSession1) . ' résultats publiés' : 'Aucun résultat publié' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Session Rattrapage -->
                        @if($sessionRattrapage)
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 {{ $showSession2 ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }} rounded-full"></div>
                                <div>
                                    <div class="text-sm font-semibold {{ $showSession2 ? 'text-green-900 dark:text-green-300' : 'text-gray-600 dark:text-gray-400' }}">
                                        Session Rattrapage ({{ $sessionRattrapage->libelle }})
                                    </div>
                                    <div class="text-xs {{ $showSession2 ? 'text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-gray-500' }}">
                                        @if($showSession2)
                                            {{ !empty($resultatsSession2) ? count($resultatsSession2) . ' résultats publiés' : 'Aucun résultat publié' }}
                                        @else
                                            En attente de résultats
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Statistiques rapides -->
                    @if($statistiquesCompletes)
                        <div class="flex items-center space-x-4 text-sm">
                            <div class="text-center">
                                <div class="font-bold text-blue-600 dark:text-blue-400">{{ $statistiquesCompletes['total_inscrits'] }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Inscrits</div>
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-green-600 dark:text-green-400">{{ $statistiquesCompletes['admis_premiere_session'] }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Admis S1</div>
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-orange-600 dark:text-orange-400">{{ $statistiquesCompletes['eligibles_rattrapage'] }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Éligibles S2</div>
                            </div>
                            @if($showSession2)
                                <div class="text-center">
                                    <div class="font-bold text-purple-600 dark:text-purple-400">{{ $statistiquesCompletes['participants_rattrapage'] }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Participants S2</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Filtres avec validation en temps réel -->
        <div class="p-4 border border-gray-200 bg-gray-50 dark:bg-gray-800/50 rounded-xl dark:border-gray-700">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

                <!-- Année Universitaire -->
                <div>
                    <label class="block mb-2 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400">
                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Année Universitaire
                    </label>
                    <select wire:model.live="selectedAnneeUniversitaire"
                            class="w-full px-3 py-2.5 text-sm text-gray-900 transition-all duration-200 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hover:border-gray-400 dark:hover:border-gray-500">
                        <option value="">Sélectionner l'année...</option>
                        @foreach($anneesUniversitaires as $annee)
                            <option value="{{ $annee->id }}">
                                {{ $annee->libelle }}
                                @if($annee->is_active) (Active) @endif
                            </option>
                        @endforeach
                    </select>
                    @if(!$selectedAnneeUniversitaire)
                        <div class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                            <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.232 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            Requis pour afficher les résultats
                        </div>
                    @endif
                </div>

                <!-- Niveau -->
                <div>
                    <label class="block mb-2 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400">
                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        Niveau d'études
                    </label>
                    <select wire:model.live="selectedNiveau"
                            class="w-full px-3 py-2.5 text-sm text-gray-900 transition-all duration-200 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 hover:border-gray-400 dark:hover:border-gray-500">
                        <option value="">Sélectionner le niveau...</option>
                        @foreach($niveaux as $niveau)
                            <option value="{{ $niveau->id }}">{{ $niveau->nom }}</option>
                        @endforeach
                    </select>
                    @if(!$selectedNiveau)
                        <div class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                            <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/>
                            </svg>
                            Requis pour afficher les résultats
                        </div>
                    @endif
                </div>

                <!-- Parcours -->
                <div>
                    <label class="block mb-2 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400">
                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9"/>
                        </svg>
                        Parcours
                    </label>
                    <select wire:model.live="selectedParcours"
                            class="w-full px-3 py-2.5 text-sm text-gray-900 transition-all duration-200 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 hover:border-gray-400 dark:hover:border-gray-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100 dark:disabled:bg-gray-700"
                            @if($parcours->isEmpty()) disabled @endif>
                        <option value="">
                            {{ $parcours->isEmpty() ? 'Aucun parcours disponible' : 'Tous les parcours' }}
                        </option>
                        @foreach($parcours as $parcour)
                            <option value="{{ $parcour->id }}">{{ $parcour->nom }}</option>
                        @endforeach
                    </select>
                    @if($parcours->isEmpty() && $selectedNiveau)
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Ce niveau n'a pas de parcours spécifiques
                        </div>
                    @endif
                </div>

                <!-- État des sessions -->
                <div>
                    <label class="block mb-2 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400">
                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        État des sessions
                    </label>
                    <div class="space-y-2">
                        <!-- Session 1 -->
                        <div class="flex items-center justify-between px-3 py-2 border rounded-lg {{ !empty($resultatsSession1) ? 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700' : 'border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600' }}">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 {{ !empty($resultatsSession1) ? 'bg-green-500' : 'bg-gray-400' }} rounded-full"></div>
                                <span class="text-xs font-medium {{ !empty($resultatsSession1) ? 'text-green-700 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
                                    Session 1
                                </span>
                            </div>
                            <span class="text-xs {{ !empty($resultatsSession1) ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-500' }}">
                                {{ !empty($resultatsSession1) ? count($resultatsSession1) : '0' }}
                            </span>
                        </div>
                        <!-- Session 2 -->
                        <div class="flex items-center justify-between px-3 py-2 border rounded-lg {{ $showSession2 && !empty($resultatsSession2) ? 'border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700' : 'border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600' }}">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 {{ $showSession2 && !empty($resultatsSession2) ? 'bg-blue-500' : 'bg-gray-400' }} rounded-full"></div>
                                <span class="text-xs font-medium {{ $showSession2 && !empty($resultatsSession2) ? 'text-blue-700 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400' }}">
                                    Session 2
                                </span>
                            </div>
                            <span class="text-xs {{ $showSession2 && !empty($resultatsSession2) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-500' }}">
                                {{ $showSession2 && !empty($resultatsSession2) ? count($resultatsSession2) : '0' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages d'erreur globaux -->
            @if($errors->any())
                <div class="p-3 mt-4 text-red-700 bg-red-100 border border-red-400 rounded-lg dark:text-red-300 dark:bg-red-900/50 dark:border-red-800">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">Erreurs de validation :</span>
                    </div>
                    <ul class="mt-2 list-disc ml-7">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
