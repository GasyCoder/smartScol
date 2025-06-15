<!-- Header simple et élégant avec délibération -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 bg-blue-500 rounded-lg">
                    <em class="text-white ni ni-graduation"></em>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Résultats Finaux des Examens
                    </h1>
                    <div class="flex items-center mt-1 space-x-2">
                        @if($sessionNormale)
                            @php
                                $deliberationS1 = $deliberationStatus['session1'] ?? false;
                            @endphp
                            <div class="flex items-center px-2 py-1 text-xs font-medium {{ $deliberationS1 ? 'text-green-700 bg-green-100 dark:text-green-300 dark:bg-green-900/30' : 'text-blue-700 bg-blue-100 dark:text-blue-300 dark:bg-blue-900/30' }} rounded-full">
                                <div class="w-2 h-2 mr-1 {{ $deliberationS1 ? 'bg-green-500' : 'bg-blue-500 animate-pulse' }} rounded-full"></div>
                                <span>{{ $sessionNormale->type }} ({{ $sessionNormale->libelle }}) - {{ $deliberationS1 ? 'Délibérée' : 'En cours' }}</span>
                            </div>
                        @endif
                        @if($sessionRattrapage)
                            @php
                                $deliberationS2 = $deliberationStatus['session2'] ?? false;
                            @endphp
                            <div class="flex items-center px-2 py-1 text-xs font-medium {{ $deliberationS2 ? 'text-green-700 bg-green-100 dark:text-green-300 dark:bg-green-900/30' : 'text-orange-700 bg-orange-100 dark:text-orange-300 dark:bg-orange-900/30' }} rounded-full">
                                <div class="w-2 h-2 mr-1 {{ $deliberationS2 ? 'bg-green-500' : 'bg-orange-500' }} rounded-full"></div>
                                <span>{{ $sessionRattrapage->type }} - {{ $deliberationS2 ? 'DÉLIBÉRÉE' : 'EN ATTENTE' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Filtres compacts avec indicateurs de délibération -->
        <div class="grid grid-cols-1 gap-4 p-4 border border-gray-200 md:grid-cols-2 lg:grid-cols-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl dark:border-gray-700">

            <!-- Année Universitaire -->
            <div>
                <label class="block mb-2 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400">
                    Année Universitaire
                </label>
                <select wire:model.live="selectedAnneeUniversitaire"
                        class="w-full px-3 py-2 text-sm text-gray-900 transition-colors duration-200 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Sélectionner...</option>
                    @foreach($anneesUniversitaires as $annee)
                        <option value="{{ $annee->id }}">{{ $annee->libelle }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Niveau -->
            <div>
                <label class="block mb-2 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400">
                    Niveau
                </label>
                <select wire:model.live="selectedNiveau"
                        class="w-full px-3 py-2 text-sm text-gray-900 transition-colors duration-200 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Sélectionner...</option>
                    @foreach($niveaux as $niveau)
                        <option value="{{ $niveau->id }}">{{ $niveau->nom }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Parcours -->
            <div>
                <label class="block mb-2 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400">
                    Parcours
                </label>
                <select wire:model.live="selectedParcours"
                        class="w-full px-3 py-2 text-sm text-gray-900 transition-colors duration-200 bg-white border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        @if($parcours->isEmpty()) disabled @endif>
                    <option value="">
                        {{ $parcours->isEmpty() ? 'Aucun parcours' : 'Tous les parcours' }}
                    </option>
                    @foreach($parcours as $parcour)
                        <option value="{{ $parcour->id }}">{{ $parcour->nom }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Sessions avec état de délibération -->
            <div>
                <label class="block mb-2 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400">
                    État des Sessions
                </label>
                <div class="space-y-1">
                    @if($sessionNormale)
                        @php
                            $deliberationS1 = $deliberationStatus['session1'] ?? false;
                            $statsS1 = $statistiquesDeliberation['session1'] ?? null;
                        @endphp
                        <div class="flex items-center justify-between px-3 py-1.5 border {{ $deliberationS1 ? 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700' : 'border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700' }} rounded-lg">
                            <div class="flex items-center">
                                <div class="w-2 h-2 mr-2 {{ $deliberationS1 ? 'bg-green-500' : 'bg-blue-500 animate-pulse' }} rounded-full"></div>
                                <span class="text-xs font-medium {{ $deliberationS1 ? 'text-green-700 dark:text-green-400' : 'text-blue-700 dark:text-blue-400' }}">
                                    {{ $sessionNormale->type }} - {{ $deliberationS1 ? 'Délibérée' : 'En cours' }}
                                </span>
                            </div>
                            @if($statsS1 && $statsS1['configuration_existante'] && $deliberationS1)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $statsS1['statistiques']['total_valides_jury'] ?? 0 }}/{{ $statsS1['statistiques']['total_etudiants'] ?? 0 }}
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($showSession2)
                        @php
                            $deliberationS2 = $deliberationStatus['session2'] ?? false;
                            $statsS2 = $statistiquesDeliberation['session2'] ?? null;
                        @endphp
                        <div class="flex items-center justify-between px-3 py-1.5 border {{ $deliberationS2 ? 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700' : 'border-orange-200 bg-orange-50 dark:bg-orange-900/20 dark:border-orange-700' }} rounded-lg">
                            <div class="flex items-center">
                                <div class="w-2 h-2 mr-2 {{ $deliberationS2 ? 'bg-green-500' : 'bg-orange-500' }} rounded-full"></div>
                                <span class="text-xs font-medium {{ $deliberationS2 ? 'text-green-700 dark:text-green-400' : 'text-orange-700 dark:text-orange-400' }}">
                                    Rattrapage - {{ $deliberationS2 ? 'DÉLIBÉRÉE' : 'EN ATTENTE' }}
                                </span>
                            </div>
                            @if($statsS2 && $statsS2['configuration_existante'] && $deliberationS2)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $statsS2['statistiques']['total_valides_jury'] ?? 0 }}/{{ $statsS2['statistiques']['total_etudiants'] ?? 0 }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="px-3 py-1.5 bg-gray-100 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Session 2 non disponible</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ✅ NOUVEAU : Bannière d'information délibération --}}
        @if($selectedNiveau && $selectedAnneeUniversitaire)
            @php
                $sessionDeliberee = ($deliberationStatus['session1'] ?? false) || ($deliberationStatus['session2'] ?? false);
                $sessionsEnAttente = (!($deliberationStatus['session1'] ?? false) && !empty($resultatsSession1)) || (!($deliberationStatus['session2'] ?? false) && $showSession2 && !empty($resultatsSession2));
            @endphp
        @endif
    </div>
