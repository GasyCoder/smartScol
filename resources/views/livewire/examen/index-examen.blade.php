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
                    <p class="mt-1 text-sm font-bold leading-6 text-slate-400">
                        Liste des examens pour la session
                        @if($currentSession)
                            <span class="font-medium text-amber-600 dark:text-amber-400">
                                {{ $currentSession->type }}
                            </span>
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 mt-4 sm:mt-0">
                    <button wire:click="exportExamens" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Exporter
                    </button>
                    <a href="{{ route('examens.create', ['niveau' => $niveauInfo['id'], 'parcour' => $parcoursInfo['id']]) }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded bg-primary-600 text-white hover:bg-primary-700 focus:outline-none dark:bg-primary-700 dark:hover:bg-primary-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajouter
                    </a>
                </div>
            </div>

            <!-- Filtres additionnels -->
            <div class="p-4 mb-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-950 dark:border-gray-800">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtres</h3>
                    <button wire:click="resetFilters" type="button" class="text-xs text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Réinitialiser
                        </div>
                    </button>
                </div>

             <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Recherche -->
                <div>
                    <label for="search" class="block mb-1 text-sm font-medium text-slate-700 dark:text-white">Recherche</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="search"
                            wire:model.live="search"
                            class="block w-full py-1.5 pl-14 pr-4 text-sm bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-800 dark:text-white focus:border-primary-500 focus:ring-0 dark:focus:border-primary-600"
                            placeholder="EC, matière ou code..."
                        >
                    </div>
                </div>

                <!-- Date de début -->
                <div>
                    <label for="date_from" class="block mb-1 text-sm font-medium text-slate-700 dark:text-white">Date de début</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input
                            type="date"
                            wire:model.live="date_from"
                            id="date_from"
                            class="block w-full md:w-48 py-1.5 pl-10 pr-4 text-sm bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-800 dark:text-white focus:border-primary-500 focus:ring-0 dark:focus:border-primary-600"
                        >
                    </div>
                </div>

                <!-- Date de fin -->
                <div>
                    <label for="date_to" class="block mb-1 text-sm font-medium text-slate-700 dark:text-white">Date de fin</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input
                            type="date"
                            wire:model.live="date_to"
                            id="date_to"
                            class="block w-full md:w-48 py-1.5 pl-10 pr-4 text-sm bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-800 dark:text-white focus:border-primary-500 focus:ring-0 dark:focus:border-primary-600"
                        >
                    </div>
                </div>

                <!-- Sélecteur de session -->
                <div>
                    <label for="session_id" class="block mb-1 text-sm font-medium text-slate-700 dark:text-white">Session</label>
                    <select
                        wire:model.live="session_id"
                        id="session_id"
                        class="block w-full md:w-48 py-1.5 px-3 text-sm bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-800 dark:text-white focus:border-primary-500 focus:ring-0 dark:focus:border-primary-600">
                        <option value="">Toutes les sessions</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

                <!-- Affichage des filtres actifs -->
                <div class="flex flex-wrap gap-2 mt-3">
                    @if($search)
                        <div class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 rounded-full text-primary-700 dark:bg-gray-800 dark:text-primary-400">
                            <span>Recherche : {{ $search }}</span>
                            <button wire:click="$set('search', '')" type="button" class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    @if($date_from)
                        <div class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 rounded-full text-primary-700 dark:bg-gray-800 dark:text-primary-400">
                            <span>À partir du : {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }}</span>
                            <button wire:click="$set('date_from', '')" type="button" class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    @if($date_to)
                        <div class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 rounded-full text-primary-700 dark:bg-gray-800 dark:text-primary-400">
                            <span>Jusqu'au : {{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}</span>
                            <button wire:click="$set('date_to', '')" type="button" class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tableau des examens -->
            <div class="overflow-x-auto bg-white border border-gray-300 rounded-lg shadow-sm dark:bg-gray-950 dark:border-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900">
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                EC/Matière
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                Session
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400" wire:click="sortBy('date')">
                                <div class="flex items-center space-x-1">
                                    <span>Date</span>
                                    @if($sortField === 'date')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            @if($sortDirection === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-950 dark:divide-gray-800">
                        @forelse($examens as $examen)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    @if($examen->ecs->isEmpty())
                                        <span class="text-gray-400">Aucun EC associé</span>
                                    @else
                                        @foreach($examen->ecsGroupedByUE as $ueGroup)
                                            <div class="mb-3">
                                                <!-- Titre de l'UE en majuscules et avec style amélioré -->
                                                <div class="px-2 py-1 text-sm font-bold text-gray-700 bg-gray-100 rounded dark:text-gray-200 dark:bg-gray-800">
                                                    {{ strtoupper($ueGroup['ue_abr'] ? $ueGroup['ue_abr'] . ' - ' : '') }}{{ strtoupper($ueGroup['ue_nom']) }}
                                                </div>

                                                <!-- Liste des EC avec dates, heures et salles spécifiques -->
                                                <div class="pl-2 mt-1 space-y-2">
                                                    @foreach($ueGroup['ecs'] as $ec)
                                                        @php
                                                            // Récupérer la date et l'heure spécifiques pour cet EC
                                                            $dateStr = $ec->pivot->date_specifique
                                                                ? \Carbon\Carbon::parse($ec->pivot->date_specifique)->format('d/m/Y')
                                                                : 'Non définie';
                                                            $timeStr = $ec->pivot->heure_specifique
                                                                ? \Carbon\Carbon::parse($ec->pivot->heure_specifique)->format('H:i')
                                                                : '--:--';
                                                            // Récupérer la salle
                                                            $salle = $ec->pivot->salle_id
                                                                ? App\Models\Salle::find($ec->pivot->salle_id)
                                                                : null;
                                                        @endphp
                                                        <div class="flex flex-col">
                                                            <div class="flex items-center justify-between">
                                                                <div class="font-medium">
                                                                    {{ $ec->abr ? $ec->abr . ' - ' : '' }}{{ $ec->nom }}
                                                                </div>

                                                                <!-- Statut des copies/manchettes avec couleurs améliorées -->
                                                                <div class="flex space-x-1.5 ml-2">
                                                                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                                        C:{{ isset($examen->copiesStatusByEc[$ec->id]) ? $examen->copiesStatusByEc[$ec->id]['saisies'] : 0 }}/{{ isset($examen->copiesStatusByEc[$ec->id]) ? $examen->copiesStatusByEc[$ec->id]['total'] : 0 }}
                                                                    </span>
                                                                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                                        M:{{ isset($examen->manchettesStatusByEc[$ec->id]) ? $examen->manchettesStatusByEc[$ec->id]['saisies'] : 0 }}/{{ isset($examen->manchettesStatusByEc[$ec->id]) ? $examen->manchettesStatusByEc[$ec->id]['total'] : 0 }}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <!-- Date, heure et salle spécifiques pour cet EC -->
                                                            <div class="flex flex-wrap mt-0.5 text-xs text-gray-500 italic">
                                                                <span>{{ $dateStr }} à {{ $timeStr }}</span>
                                                                @if($salle)
                                                                    <span class="ml-2 px-1.5 py-0.5 bg-green-100 text-green-800 rounded dark:bg-green-900 dark:text-green-200 not-italic">
                                                                        Salle: {{ $salle->nom }}
                                                                    </span>
                                                                @else
                                                                    <span class="ml-2 px-1.5 py-0.5 bg-gray-100 text-gray-700 rounded dark:bg-gray-800 dark:text-gray-400 not-italic">
                                                                        Aucune salle
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap dark:text-gray-300">
                                    {{ $examen->session->type }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <!-- Afficher la date et la durée globale de l'examen -->
                                    <div class="font-medium">{{ $examen->first_date ? $examen->first_date->format('d/m/Y') : 'Non définie' }}</div>
                                    <div class="text-xs text-gray-500">Durée: {{ $examen->duree }} min</div>

                                    <!-- Indiquer s'il y a plusieurs dates -->
                                    @if($examen->ecs->count() > 1)
                                        <div class="mt-1 px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded-full inline-block dark:bg-blue-900/50 dark:text-blue-300">
                                            Plusieurs dates
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('examens.edit', $examen) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <button wire:click="confirmDelete({{ $examen->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p class="mt-2 text-sm font-medium">Aucun examen trouvé</p>
                                    <p class="mt-1 text-sm">Créez un nouvel examen pour ce parcours.</p>
                                    <a href="{{ route('examens.create', ['niveau' => $niveauInfo['id'], 'parcour' => $parcoursInfo['id']]) }}" class="inline-flex items-center px-3 py-2 mt-4 text-sm font-medium text-white rounded-md bg-primary-600 hover:bg-primary-700 focus:outline-none dark:bg-primary-700 dark:hover:bg-primary-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Créer un examen
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-4 py-3 bg-white border-t border-gray-200 dark:bg-gray-950 dark:border-gray-800 sm:px-6">
                    {{ $examens->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de suppression</h3>
            <p class="mb-6 text-gray-700 dark:text-gray-300">Êtes-vous sûr de vouloir supprimer cet examen ? Cette action est irréversible.</p>
            <div class="flex justify-end space-x-3">
                <button wire:click="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="deleteExamen" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
