<div class="relative">
    <!-- En-tête fixe -->
    <div class="sticky top-0 z-10 px-5 py-4 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal -->
            <h5 class="text-xl font-medium text-slate-700 dark:text-white">Gestion des examens</h5>

            <!-- Fil d'Ariane et Boutons de navigation -->
            <div class="flex items-center space-x-4">
                <!-- Fil d'Ariane -->
                <div class="items-center hidden text-sm sm:flex">
                    <span class="text-slate-600 dark:text-slate-400">
                        @if($niveauInfo)
                            <span class="font-medium">{{ $niveauInfo['nom'] }} ({{ $niveauInfo['abr'] }})</span>
                        @endif

                        @if($parcoursInfo)
                            <span class="mx-2 text-slate-400">/</span>
                            <span class="font-medium">{{ $parcoursInfo['nom'] }} ({{ $parcoursInfo['abr'] }})</span>
                        @endif

                        @if($step === 'examens')
                            <span class="mx-2 text-slate-400">/</span>
                            <span class="font-medium text-primary-600 dark:text-primary-400">Examens</span>
                        @endif
                    </span>
                </div>

                <!-- Boutons de navigation -->
                <div class="flex items-center space-x-2">
                    <!-- Bouton pour réinitialiser complètement le flux de navigation -->
                    <button wire:click="resetAll" type="button" class="flex items-center text-sm text-gray-500 hover:text-gray-600 bg-gray-50 dark:bg-gray-900/20 dark:hover:bg-gray-900/30 py-1.5 px-3 rounded-md transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Recommencer
                    </button>

                    @if($step === 'parcours')
                    <button wire:click="retourANiveau" type="button" class="flex items-center text-sm text-primary-500 hover:text-primary-600 bg-primary-50 dark:bg-primary-900/20 dark:hover:bg-primary-900/30 py-1.5 px-3 rounded-md transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Retour au niveau
                    </button>
                    @endif

                    @if($step === 'examens')
                    <button wire:click="retourAParcours" type="button" class="flex items-center text-sm text-primary-500 hover:text-primary-600 bg-primary-50 dark:bg-primary-900/20 dark:hover:bg-primary-900/30 py-1.5 px-3 rounded-md transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Retour au parcours
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal avec padding supérieur pour compenser l'en-tête fixe -->
    <div class="px-5 pt-6">
        <!-- Messages de flash -->
        @if (session()->has('message'))
            <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <!-- Section Niveau - Visible uniquement à l'étape niveau -->
        @if($step === 'niveau')
        <div class="[&:not(:last-child)]:pb-7 lg:[&:not(:last-child)]:pb-14">
            <div id="niveau-section" class="pb-5">
                <h5 class="mb-2 text-lg font-medium -tracking-snug text-slate-700 dark:text-white leading-tighter">Choisir le niveau d'étude</h5>
                <p class="mb-5 text-sm leading-6 text-slate-400">
                    Veuillez sélectionner le niveau d'étude pour lequel vous souhaitez gérer les examens.
                </p>
            </div>

            <div class="bg-white border border-gray-300 rounded-lg shadow-sm dark:bg-gray-950 dark:border-gray-900">
                <div class="p-5">
                    <div class="flex justify-center">
                        <div class="w-full max-w-md">
                            <div class="relative mb-5 last:mb-0">
                                <label class="inline-block mb-2 text-sm font-medium text-slate-700 dark:text-white" for="niveau-select">
                                    Niveau d'étude
                                </label>
                                <div class="relative">
                                    <select id="niveau-select" wire:model.live="niveauId" class="js-select block w-full text-sm leading-4.5 pe-10 ps-4 py-1.5 h-10 text-slate-700 dark:text-white placeholder-slate-300 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 outline-none focus:border-primary-500 focus:dark:border-primary-600 focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 disabled:bg-slate-50 disabled:dark:bg-slate-950 disabled:cursor-not-allowed rounded-md transition-all" data-search="true">
                                        <option value="">Sélectionnez un niveau</option>
                                        @foreach($niveaux as $niveau)
                                            <option value="{{ $niveau->id }}">{{ $niveau->nom }} ({{ $niveau->abr }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- card -->
        </div>
        @endif

        <!-- Section Parcours - Visible uniquement à l'étape parcours -->
        @if($step === 'parcours' && $niveauInfo)
        <div class="[&:not(:last-child)]:pb-7 lg:[&:not(:last-child)]:pb-14">
            <div id="parcours-section" class="pb-5">
                <h5 class="mb-2 text-lg font-medium -tracking-snug text-slate-700 dark:text-white leading-tighter">Choisir le parcours</h5>
                <p class="mb-5 text-sm leading-6 text-slate-400">
                    Veuillez sélectionner le parcours pour lequel vous souhaitez gérer les examens.
                </p>
            </div>

            <div class="bg-white border border-gray-300 rounded-lg shadow-sm dark:bg-gray-950 dark:border-gray-900">
                <div class="p-5">
                    <div class="flex justify-center">
                        <div class="w-full max-w-md">
                            @if(count($parcours) > 0)
                                <div class="relative mb-5 last:mb-0">
                                    <label class="inline-block mb-2 text-sm font-medium text-slate-700 dark:text-white" for="parcours-select">
                                        Parcours disponibles
                                    </label>
                                    <div class="relative">
                                        <select id="parcours-select" wire:model.live="parcoursId" class="js-select block w-full text-sm leading-4.5 pe-10 ps-4 py-1.5 h-10 text-slate-700 dark:text-white placeholder-slate-300 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 outline-none focus:border-primary-500 focus:dark:border-primary-600 focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 disabled:bg-slate-50 disabled:dark:bg-slate-950 disabled:cursor-not-allowed rounded-md transition-all" data-search="true">
                                            <option value="">Sélectionnez un parcours</option>
                                            @foreach($parcours as $parcour)
                                                <option value="{{ $parcour->id }}">{{ $parcour->nom }} ({{ $parcour->abr }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                <div class="p-4 text-center rounded-md text-slate-500 bg-slate-50 dark:bg-gray-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto mb-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p>Aucun parcours disponible pour ce niveau d'étude.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div><!-- card -->
        </div>
        @endif

        <!-- Section Examens - Visible uniquement à l'étape examens -->
        @if($step === 'examens' && $niveauInfo && $parcoursInfo)
        <div>
            <div class="flex flex-col mb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Examens</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-400">
                        Gérez vos examens et matières
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 mt-4 sm:mt-0">
                    <button wire:click="exportExamens" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Exporter
                    </button>
                    <a href="{{ route('examens.create', ['niveau' => $niveauInfo['id'], 'parcour' => $parcoursInfo['id']]) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-200 rounded-lg shadow-sm bg-primary-600 hover:bg-primary-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-primary-700 dark:hover:bg-primary-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouveau examen
                    </a>
                </div>
            </div>

            <!-- Filtres additionnels -->
            <div class="p-5 mb-6 bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-950 dark:border-gray-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Filtres de recherche</h3>
                    <button wire:click="resetFilters" type="button" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-500 transition-colors bg-gray-100 rounded-md hover:text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Réinitialiser
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Recherche -->
                    <div>
                        <label for="search" class="block mb-2 text-sm font-medium text-slate-700 dark:text-white">Recherche</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                id="search"
                                wire:model.live="search"
                                class="block w-full py-2.5 pl-10 pr-4 text-sm transition-colors bg-white border border-gray-300 rounded-lg dark:bg-gray-950 dark:border-gray-700 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:border-primary-600 dark:focus:ring-primary-900"
                                placeholder="Rechercher un EC ou une matière..."
                            >
                        </div>
                    </div>

                    <!-- Date de début -->
                    <div>
                        <label for="date_from" class="block mb-2 text-sm font-medium text-slate-700 dark:text-white">Date de début</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input
                                type="date"
                                wire:model.live="date_from"
                                id="date_from"
                                class="block w-full py-2.5 pl-10 pr-4 text-sm transition-colors bg-white border border-gray-300 rounded-lg dark:bg-gray-950 dark:border-gray-700 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:border-primary-600 dark:focus:ring-primary-900"
                            >
                        </div>
                    </div>

                    <!-- Date de fin -->
                    <div>
                        <label for="date_to" class="block mb-2 text-sm font-medium text-slate-700 dark:text-white">Date de fin</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input
                                type="date"
                                wire:model.live="date_to"
                                id="date_to"
                                class="block w-full py-2.5 pl-10 pr-4 text-sm transition-colors bg-white border border-gray-300 rounded-lg dark:bg-gray-950 dark:border-gray-700 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:border-primary-600 dark:focus:ring-primary-900"
                            >
                        </div>
                    </div>
                </div>

                <!-- Affichage des filtres actifs -->
                @if($search || $date_from || $date_to)
                <div class="flex flex-wrap gap-2 pt-3 mt-4 border-t border-gray-200 dark:border-gray-700">
                    @if($search)
                        <div class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-primary-50 text-primary-700 rounded-full dark:bg-primary-900/30 dark:text-primary-400">
                            <span>Recherche : {{ $search }}</span>
                            <button wire:click="$set('search', '')" type="button" class="ml-2 text-primary-500 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    @if($date_from)
                        <div class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-700 rounded-full dark:bg-blue-900/30 dark:text-blue-400">
                            <span>À partir du : {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }}</span>
                            <button wire:click="$set('date_from', '')" type="button" class="ml-2 text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    @if($date_to)
                        <div class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-green-50 text-green-700 rounded-full dark:bg-green-900/30 dark:text-green-400">
                            <span>Jusqu'au : {{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}</span>
                            <button wire:click="$set('date_to', '')" type="button" class="ml-2 text-green-500 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Vue en cartes des examens -->
            <div class="space-y-8">
                @forelse($examens as $examen)
                    <!-- Carte principale de l'examen -->
                    <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-200 shadow-lg rounded-2xl dark:bg-gray-950 dark:border-gray-800 hover:shadow-xl">
                        <!-- En-tête de l'examen -->
                        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center justify-center w-12 h-12 shadow-lg rounded-xl bg-gradient-to-br from-primary-500 to-primary-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                            Session d'examen
                                        </h3>
                                        <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-300">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ $examen->first_date ? $examen->first_date->format('d/m/Y') : 'Non définie' }}
                                            </div>
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $examen->duree }} min ({{ floor($examen->duree / 60) }}h {{ $examen->duree % 60 }}min)
                                            </div>
                                            @if($examen->ecs->count() > 1)
                                                <div class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-amber-100 text-amber-800 rounded-full dark:bg-amber-900/30 dark:text-amber-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    Plusieurs sessions
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions globales de l'examen -->
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('examens.edit', $examen) }}"
                                       class="inline-flex items-center px-3 py-2 text-xs font-medium text-blue-700 transition-all duration-200 bg-blue-100 border border-blue-200 rounded-lg hover:bg-blue-200 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-900/50"
                                       title="Modifier l'examen global">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                        Modifier examen
                                    </a>

                                    <button wire:click="confirmDelete({{ $examen->id }})"
                                            class="inline-flex items-center px-3 py-2 text-xs font-medium text-red-700 transition-all duration-200 bg-red-100 border border-red-200 rounded-lg hover:bg-red-200 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-900/50"
                                            title="Supprimer l'examen">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Supprimer examen
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Contenu principal : UE et EC -->
                        <div class="p-6">
                            @if($examen->ecs->isEmpty())
                                <div class="flex items-center justify-center py-16">
                                    <div class="text-center">
                                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full dark:bg-gray-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Aucun EC associé</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Aucune matière n'est associée à cet examen.
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-6">
                                    @foreach($examen->ecsGroupedByUE as $ueGroup)
                                        <!-- Carte UE -->
                                        <div class="border border-gray-200 rounded-xl dark:border-gray-700 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800/50 dark:to-gray-900/50">
                                            <!-- En-tête UE -->
                                            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-t-xl">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="flex items-center justify-center w-10 h-10 rounded-lg shadow-lg bg-gradient-to-br from-indigo-500 to-purple-600">
                                                            <span class="text-sm font-bold text-white">{{ substr($ueGroup['ue_abr'] ?: $ueGroup['ue_nom'], 0, 2) }}</span>
                                                        </div>
                                                        <div>
                                                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                                                                {{ $ueGroup['ue_abr'] ? $ueGroup['ue_abr'] . ' - ' : '' }}{{ $ueGroup['ue_nom'] }}
                                                            </h4>
                                                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                                                {{ count($ueGroup['ecs']) }} matière(s)
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- Actions UE -->
                                                    <div class="flex items-center space-x-2">
                                                        <button class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition-all duration-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50"
                                                                title="Modifier cette UE">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                            </svg>
                                                            Modifier UE
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Liste des EC de cette UE -->
                                            <div class="p-5">
                                                <div class="grid gap-4 sm:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">
                                                    @foreach($ueGroup['ecs'] as $ec)
                                                        @php
                                                            $dateStr = $ec->pivot->date_specifique
                                                                ? \Carbon\Carbon::parse($ec->pivot->date_specifique)->format('d/m/Y')
                                                                : 'Non définie';
                                                            $timeStr = $ec->pivot->heure_specifique
                                                                ? \Carbon\Carbon::parse($ec->pivot->heure_specifique)->format('H:i')
                                                                : '--:--';
                                                            $salle = $ec->pivot->salle_id
                                                                ? App\Models\Salle::find($ec->pivot->salle_id)
                                                                : null;

                                                            // Statistiques des copies et manchettes
                                                            $copiesStats = $examen->copiesStatusByEc[$ec->id] ?? ['saisies' => 0, 'total' => 0];
                                                            $manchettesStats = $examen->manchettesStatusByEc[$ec->id] ?? ['saisies' => 0, 'total' => 0];

                                                            // Calcul du pourcentage de completion
                                                            $copiesPercent = $copiesStats['total'] > 0 ? round(($copiesStats['saisies'] / $copiesStats['total']) * 100) : 0;
                                                            $manchettesPercent = $manchettesStats['total'] > 0 ? round(($manchettesStats['saisies'] / $manchettesStats['total']) * 100) : 0;
                                                        @endphp

                                                        <!-- Carte EC -->
                                                        <div class="relative p-4 transition-all duration-300 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-900 dark:border-gray-700 hover:shadow-md group">
                                                            <!-- Badge de statut en haut à droite -->
                                                            <div class="absolute top-3 right-3">
                                                                @if($copiesStats['saisies'] == $copiesStats['total'] && $copiesStats['total'] > 0)
                                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-300">
                                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                                        </svg>
                                                                        Complet
                                                                    </span>
                                                                @elseif($copiesStats['saisies'] > 0)
                                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900/30 dark:text-yellow-300">
                                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                        </svg>
                                                                        En cours
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-gray-400">
                                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"></path>
                                                                        </svg>
                                                                        À faire
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            <!-- Contenu principal de l'EC -->
                                                            <div class="pr-16"> <!-- Padding à droite pour éviter le badge -->
                                                                <div class="mb-3">
                                                                    <h5 class="text-base font-bold text-gray-900 transition-colors dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">
                                                                        {{ $ec->abr ? $ec->abr . ' - ' : '' }}{{ $ec->nom }}
                                                                    </h5>
                                                                </div>

                                                                <!-- Informations de planification -->
                                                                <div class="mb-4 space-y-2">
                                                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                        </svg>
                                                                        <span class="font-medium">{{ $dateStr }}</span>
                                                                    </div>
                                                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                        <span class="font-medium">{{ $timeStr }}</span>
                                                                    </div>
                                                                    @if($salle)
                                                                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                                            </svg>
                                                                            <span class="px-2 py-1 text-xs font-medium text-purple-800 bg-purple-100 rounded-md dark:bg-purple-900/30 dark:text-purple-300">{{ $salle->nom }}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                <!-- Statistiques avec barres de progression -->
                                                                <div class="mb-4 space-y-3">
                                                                    <!-- Copies -->
                                                                    <div>
                                                                        <div class="flex items-center justify-between mb-1">
                                                                            <span class="flex items-center text-xs font-medium text-gray-700 dark:text-gray-300">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                                </svg>
                                                                                Copies
                                                                            </span>
                                                                            <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $copiesStats['saisies'] }}/{{ $copiesStats['total'] }}</span>
                                                                        </div>
                                                                        <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                                                                            <div class="h-2 rounded-full transition-all duration-500 {{ $copiesPercent >= 100 ? 'bg-green-500' : ($copiesPercent >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                                                 style="width: {{ $copiesPercent }}%"></div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Manchettes -->
                                                                    <div>
                                                                        <div class="flex items-center justify-between mb-1">
                                                                            <span class="flex items-center text-xs font-medium text-gray-700 dark:text-gray-300">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1.5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                                                                </svg>
                                                                                Manchettes
                                                                            </span>
                                                                            <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $manchettesStats['saisies'] }}/{{ $manchettesStats['total'] }}</span>
                                                                        </div>
                                                                        <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                                                                            <div class="h-2 rounded-full transition-all duration-500 {{ $manchettesPercent >= 100 ? 'bg-green-500' : ($manchettesPercent >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                                                 style="width: {{ $manchettesPercent }}%"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Actions EC -->
                                                                <div class="flex items-center space-x-2">
                                                                    <button class="inline-flex items-center justify-center flex-1 px-3 py-2 text-xs font-medium text-blue-700 transition-all duration-200 bg-blue-100 border border-blue-200 rounded-lg hover:bg-blue-200 hover:text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-900/50"
                                                                            title="Modifier cet EC">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                                        </svg>
                                                                        Modifier
                                                                    </button>

                                                                    <button class="inline-flex items-center justify-center flex-1 px-3 py-2 text-xs font-medium text-red-700 transition-all duration-200 bg-red-100 border border-red-200 rounded-lg hover:bg-red-200 hover:text-red-800 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-900/50"
                                                                            title="Supprimer cet EC">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                        Supprimer
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <!-- État vide amélioré -->
                    <div class="flex items-center justify-center py-24">
                        <div class="max-w-md mx-auto text-center">
                            <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h3 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Aucun examen trouvé</h3>
                            <p class="mb-8 text-lg text-gray-500 dark:text-gray-400">
                                Commencez par créer votre premier examen pour ce parcours.
                            </p>
                            <a href="{{ route('examens.create', ['niveau' => $niveauInfo['id'], 'parcour' => $parcoursInfo['id']]) }}"
                               class="inline-flex items-center px-6 py-3 text-base font-medium text-white transition-all duration-200 transform border border-transparent shadow-lg rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 hover:shadow-xl hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Créer le premier examen
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination améliorée -->
            @if($examens->hasPages())
            <div class="px-6 py-4 mt-8 bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-950 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                        <span>Affichage de {{ $examens->firstItem() ?? 0 }} à {{ $examens->lastItem() ?? 0 }} sur {{ $examens->total() }} résultats</span>
                    </div>
                    <div>
                        {{ $examens->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Modal de confirmation de suppression amélioré -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-gray-900/50 backdrop-blur-sm">
        <div class="w-full max-w-md overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl dark:bg-gray-800">
            <div class="p-6">
                <!-- Icône d'alerte -->
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full dark:bg-red-900/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>

                <h3 class="mb-3 text-xl font-bold text-center text-gray-900 dark:text-white">
                    Confirmer la suppression
                </h3>
                <p class="mb-6 text-sm text-center text-gray-600 dark:text-gray-300">
                    Êtes-vous sûr de vouloir supprimer cet examen ? Cette action est irréversible et supprimera toutes les données associées (copies, manchettes, résultats).
                </p>

                <div class="flex space-x-3">
                    <button wire:click="cancelDelete"
                            class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 transition-all duration-200 bg-gray-100 border border-gray-300 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                        Annuler
                    </button>
                    <button wire:click="deleteExamen"
                            class="flex-1 px-4 py-3 text-sm font-medium text-white transition-all duration-200 transform bg-red-600 border border-transparent rounded-xl hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700 dark:hover:bg-red-800 hover:scale-105">
                        Supprimer définitivement
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Styles CSS additionnels pour les animations -->
    <style>
        /* Animation pour les cartes */
        .group:hover .group-hover\:text-primary-600 {
            transition: color 0.2s ease-in-out;
        }

        /* Animation des barres de progression */
        .progress-bar {
            transition: width 0.5s ease-in-out;
        }

        /* Effet de survol pour les cartes EC */
        .group:hover {
            transform: translateY(-2px);
        }

        /* Animation des boutons */
        button, a {
            transition: all 0.2s ease-in-out;
        }

        /* Gradient pour les badges de statut */
        .status-complete {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .status-progress {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .status-todo {
            background: linear-gradient(135deg, #6b7280, #4b5563);
        }

        /* Animation pour les modales */
        .modal-enter {
            animation: modalEnter 0.3s ease-out;
        }

        @keyframes modalEnter {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Responsive cards */
        @media (max-width: 640px) {
            .grid-cols-responsive {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .grid-cols-responsive {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1025px) {
            .grid-cols-responsive {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Amélioration des ombres */
        .shadow-card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .shadow-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Indicateurs de progression circulaires */
        .progress-circle {
            transition: stroke-dashoffset 0.5s ease-in-out;
        }

        /* Dark mode improvements */
        .dark .gradient-bg {
            background: linear-gradient(135deg, rgba(17, 24, 39, 0.8), rgba(31, 41, 55, 0.8));
        }

        /* Animation de chargement pour les états */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        /* Amélioration des tooltips */
        .tooltip {
            position: relative;
        }

        .tooltip::before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            z-index: 1000;
        }

        .tooltip:hover::before {
            opacity: 1;
        }

        /* Styles pour les états de chargement */
        .loading-state {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Animation pour les notifications */
        .notification-slide-in {
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Styles pour les drag and drop (si nécessaire plus tard) */
        .draggable {
            cursor: move;
        }

        .drag-over {
            border: 2px dashed #3b82f6;
            background-color: rgba(59, 130, 246, 0.1);
        }

        /* Responsive text scaling */
        @media (max-width: 640px) {
            .text-responsive-lg {
                font-size: 1.125rem;
            }
            .text-responsive-base {
                font-size: 0.875rem;
            }
            .text-responsive-sm {
                font-size: 0.75rem;
            }
        }

        /* Print styles pour l'export */
        @media print {
            .no-print {
                display: none !important;
            }

            .print-break {
                page-break-after: always;
            }

            .print-avoid-break {
                page-break-inside: avoid;
            }
        }
    </style>

    <!-- JavaScript pour les interactions avancées -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation des barres de progression au chargement
            function animateProgressBars() {
                const progressBars = document.querySelectorAll('[style*="width:"]');
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                });
            }

            // Initialiser les animations
            animateProgressBars();

            // Réanimer lors des mises à jour Livewire
            document.addEventListener('livewire:update', function() {
                setTimeout(animateProgressBars, 100);
            });

            // Gestion des tooltips personnalisés
            function initTooltips() {
                const tooltipElements = document.querySelectorAll('[data-tooltip]');
                tooltipElements.forEach(element => {
                    element.addEventListener('mouseenter', function() {
                        const tooltip = document.createElement('div');
                        tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg pointer-events-none';
                        tooltip.textContent = this.getAttribute('data-tooltip');
                        tooltip.id = 'custom-tooltip';

                        document.body.appendChild(tooltip);

                        const rect = this.getBoundingClientRect();
                        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                        tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
                    });

                    element.addEventListener('mouseleave', function() {
                        const tooltip = document.getElementById('custom-tooltip');
                        if (tooltip) tooltip.remove();
                    });
                });
            }

            // Gestion du responsive design avancé
            function handleResponsive() {
                const cards = document.querySelectorAll('.grid-cols-responsive');
                const screenWidth = window.innerWidth;

                cards.forEach(grid => {
                    if (screenWidth < 641) {
                        grid.style.gridTemplateColumns = '1fr';
                    } else if (screenWidth < 1025) {
                        grid.style.gridTemplateColumns = 'repeat(2, 1fr)';
                    } else {
                        grid.style.gridTemplateColumns = 'repeat(3, 1fr)';
                    }
                });
            }

            // Animation au scroll
            function animateOnScroll() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }
                    });
                });

                const cards = document.querySelectorAll('.group');
                cards.forEach(card => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
                    observer.observe(card);
                });
            }

            // Gestion des états de chargement
            function handleLoadingStates() {
                document.addEventListener('livewire:load-start', function() {
                    const loadingElements = document.querySelectorAll('.loading-target');
                    loadingElements.forEach(el => el.classList.add('loading-state'));
                });

                document.addEventListener('livewire:load-end', function() {
                    const loadingElements = document.querySelectorAll('.loading-target');
                    loadingElements.forEach(el => el.classList.remove('loading-state'));
                });
            }

            // Initialisation
            initTooltips();
            handleResponsive();
            animateOnScroll();
            handleLoadingStates();

            // Écouter les changements de taille d'écran
            window.addEventListener('resize', handleResponsive);

            // Réinitialiser lors des mises à jour Livewire
            document.addEventListener('livewire:update', function() {
                setTimeout(() => {
                    initTooltips();
                    animateOnScroll();
                }, 100);
            });

            // Gestion des raccourcis clavier
            document.addEventListener('keydown', function(e) {
                // Ctrl + N pour nouveau (si applicable)
                if (e.ctrlKey && e.key === 'n') {
                    e.preventDefault();
                    const newButton = document.querySelector('[href*="create"]');
                    if (newButton) newButton.click();
                }

                // Échapper pour fermer les modales
                if (e.key === 'Escape') {
                    const modal = document.querySelector('.modal-enter');
                    if (modal) {
                        const cancelButton = modal.querySelector('[wire\\:click*="cancel"]');
                        if (cancelButton) cancelButton.click();
                    }
                }
            });

            // Performance: Debounce pour les recherches
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // Appliquer le debounce aux champs de recherche
            const searchInputs = document.querySelectorAll('input[wire\\:model\\.live*="search"]');
            searchInputs.forEach(input => {
                const originalDispatch = input.dispatchEvent;
                let timeoutId;

                input.addEventListener('input', function() {
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(() => {
                        this.dispatchEvent(new Event('input', { bubbles: true }));
                    }, 300);
                });
            });
        });
    </script>
</div>
