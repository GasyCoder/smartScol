    <!-- Header simple et élégant -->
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
                            <div class="flex items-center px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:text-green-300 dark:bg-green-900/30">
                                <div class="w-2 h-2 mr-1 bg-green-500 rounded-full animate-pulse"></div>
                                <span>{{ $sessionNormale->type }} ({{ $sessionNormale->libelle }}) - Verrouillée</span>
                            </div>
                        @endif
                        @if($sessionRattrapage)
                            <!-- SESSION RATTRAPAGE - AUSSI VERROUILLÉE -->
                            <div class="flex items-center px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full dark:text-red-300 dark:bg-red-900/30">
                                <div class="w-2 h-2 mr-1 bg-red-500 rounded-full"></div>
                                <span>{{ $sessionRattrapage->type }} - VERROUILLÉE</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dropdown Export -->
            @if($canExport)
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false"
                            class="flex items-center px-4 py-2 transition-all duration-200 border border-gray-200 rounded-lg bg-gray-50 hover:bg-gray-100 hover:border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                        <em class="mr-2 text-gray-600 ni ni-download dark:text-gray-400"></em>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Export</span>
                        <em class="ml-2 text-gray-500 transition-transform duration-200 ni ni-chevron-down dark:text-gray-400" :class="{ 'rotate-180': open }"></em>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 z-50 w-64 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-700">

                        <!-- Session 1 -->
                        <div class="p-3 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center mb-2">
                                <div class="flex items-center justify-center w-5 h-5 mr-2 bg-blue-500 rounded">
                                    <em class="text-xs text-white ni ni-graduation"></em>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Session 1 (Normale)</span>
                                @if(!empty($resultatsSession1))
                                    <span class="ml-auto px-1.5 py-0.5 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full">
                                        {{ count($resultatsSession1) }}
                                    </span>
                                @endif
                            </div>
                            <div class="space-y-1">
                                <button wire:click="exportResults('session1')" @click="open = false"
                                        class="flex items-center w-full px-3 py-2 text-sm text-green-700 transition-colors duration-150 rounded hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20">
                                    <em class="mr-3 ni ni-file-excel"></em>
                                    Télécharger Excel
                                </button>
                                <button wire:click="exportPDF('session1')" @click="open = false"
                                        class="flex items-center w-full px-3 py-2 text-sm text-red-700 transition-colors duration-150 rounded hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <em class="mr-3 ni ni-file-pdf"></em>
                                    Télécharger PDF
                                </button>
                            </div>
                        </div>

                        <!-- Session 2 (si disponible et modifiable) -->
                        @if($showSession2)
                            <div class="p-3">
                                <div class="flex items-center mb-2">
                                    <div class="flex items-center justify-center w-5 h-5 mr-2 bg-green-500 rounded">
                                        <em class="text-xs text-white ni ni-repeat"></em>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Session 2 (Rattrapage)</span>
                                    @if(!empty($resultatsSession2))
                                        <span class="ml-auto px-1.5 py-0.5 text-xs bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full">
                                            {{ count($resultatsSession2) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <button wire:click="exportResults('session2')" @click="open = false"
                                            class="flex items-center w-full px-3 py-2 text-sm text-green-700 transition-colors duration-150 rounded hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20">
                                        <em class="mr-3 ni ni-file-excel"></em>
                                        Télécharger Excel
                                    </button>
                                    <button wire:click="exportPDF('session2')" @click="open = false"
                                            class="flex items-center w-full px-3 py-2 text-sm text-red-700 transition-colors duration-150 rounded hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <em class="mr-3 ni ni-file-pdf"></em>
                                        Télécharger PDF
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Filtres compacts -->
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

            <!-- Sessions avec état -->
            <div>
                <label class="block mb-2 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400">
                    État des Sessions
                </label>
                <div class="space-y-1">
                    @if($sessionNormale)
                        <div class="flex items-center px-3 py-1.5 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-700">
                            <div class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs font-medium text-green-700 dark:text-green-400">
                                {{ $sessionNormale->type }} - Verrouillée
                            </span>
                        </div>
                    @endif
                    @if($showSession2)
                        <!-- SESSION RATTRAPAGE AUSSI VERROUILLÉE -->
                        <div class="flex items-center px-3 py-1.5 border border-red-200 rounded-lg bg-red-50 dark:bg-red-900/20 dark:border-red-700">
                            <div class="w-2 h-2 mr-2 bg-red-500 rounded-full"></div>
                            <span class="text-xs font-medium text-red-700 dark:text-red-400">
                                Rattrapage - VERROUILLÉE
                            </span>
                        </div>
                    @else
                        <div class="px-3 py-1.5 bg-gray-100 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Session 2 non disponible</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
