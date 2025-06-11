<div class="relative">
    <!-- En-tête fixe -->
    <div class="sticky top-0 px-4 py-4 bg-white border-b border-gray-200 shadow-sm z-5 dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal -->
            <h5 class="text-xl font-medium text-slate-700 dark:text-white">Ajouter un nouvel examen</h5>
            <!-- Fil d'Ariane et Boutons de navigation -->
            <div class="flex items-center space-x-4">
                <div class="items-center hidden text-sm sm:flex">
                    <span class="text-slate-600 dark:text-slate-400">
                        @if($niveauInfo)
                            <span class="font-medium">{{ $niveauInfo['nom'] }} ({{ $niveauInfo['abr'] }})</span>
                        @endif

                        @if($parcoursInfo)
                            <span class="mx-2 text-slate-400">/</span>
                            <span class="font-medium">{{ $parcoursInfo['nom'] }} ({{ $parcoursInfo['abr'] }})</span>
                        @endif
                    </span>
                </div>

                <!-- Bouton de retour avec conservation du contexte -->
                <a href="{{ route('examens.index', [
                    'niveau' => $niveau_id,
                    'parcours' => $parcours_id,
                    'step' => 'examens'
                ]) }}"
                class="flex items-center text-sm text-primary-500 hover:text-primary-600 bg-primary-50 dark:bg-primary-900/20 dark:hover:bg-primary-900/30 py-1.5 px-3 rounded-md transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Retour aux examens
                </a>
            </div>
        </div>
    </div>

    <!-- Contenu principal avec padding supérieur pour compenser l'en-tête fixe -->
    <div class="px-5 pt-6">
        <!-- Formulaire -->
        <div class="bg-white border border-gray-300 shadow-sm rounded-xl dark:bg-gray-950 dark:border-gray-800">
            <div class="p-6">
                <form wire:submit.prevent="save" class="space-y-8">
                    <!-- Champs cachés -->
                    <input type="hidden" wire:model="niveau_id">
                    <input type="hidden" wire:model="parcours_id">

                    <!-- Section Paramètres de l'examen -->
                    <div class="space-y-6">
                        <div class="pb-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <div class="flex items-center justify-center w-8 h-8 mr-3 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                Paramètres de l'examen
                            </h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Configurez la durée et les critères de notation de l'examen
                            </p>
                        </div>

                        <!-- Informations générales -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="duree" class="block text-sm font-semibold text-slate-700 dark:text-white">
                                    Durée de l'examen
                                </label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        wire:model="duree"
                                        id="duree"
                                        min="15"
                                        step="5"
                                        class="block w-full px-4 py-3 text-sm transition-colors bg-white border border-gray-300 rounded-lg dark:bg-gray-950 dark:border-gray-700 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:border-primary-600 dark:focus:ring-primary-900"
                                        placeholder="120"
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">min</span>
                                    </div>
                                </div>
                                @error('duree')
                                    <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Durée recommandée : entre 60 et 180 minutes
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label for="note_eliminatoire" class="block text-sm font-semibold text-slate-700 dark:text-white">
                                    Note éliminatoire (optionnel)
                                </label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        wire:model="note_eliminatoire"
                                        id="note_eliminatoire"
                                        min="0"
                                        max="20"
                                        step="0.01"
                                        class="block w-full px-4 py-3 text-sm transition-colors bg-white border border-gray-300 rounded-lg dark:bg-gray-950 dark:border-gray-700 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-200 dark:focus:border-primary-600 dark:focus:ring-primary-900"
                                        placeholder="10.00"
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">/20</span>
                                    </div>
                                </div>
                                @error('note_eliminatoire')
                                    <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Note minimum requise pour valider l'examen
                                </p>
                            </div>
                        </div>

                        <!-- Section Dates et salles par défaut -->
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                            <h4 class="flex items-center mb-4 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Planification par défaut
                            </h4>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label for="date_defaut" class="block mb-2 text-xs font-medium text-gray-600 dark:text-gray-400">
                                        Date par défaut
                                    </label>
                                    <input
                                        type="date"
                                        wire:model.live="date_defaut"
                                        id="date_defaut"
                                        class="block w-full px-3 py-2 text-sm transition-colors bg-white border border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-200 dark:focus:border-primary-600"
                                    >
                                    @error('date_defaut')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="heure_defaut" class="block mb-2 text-xs font-medium text-gray-600 dark:text-gray-400">
                                        Heure par défaut
                                    </label>
                                    <input
                                        type="time"
                                        wire:model.live="heure_defaut"
                                        id="heure_defaut"
                                        class="block w-full px-3 py-2 text-sm transition-colors bg-white border border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-200 dark:focus:border-primary-600"
                                    >
                                    @error('heure_defaut')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="salle_defaut" class="block mb-2 text-xs font-medium text-gray-600 dark:text-gray-400">
                                        Salle par défaut
                                    </label>
                                    <select
                                        wire:model.live="salle_defaut"
                                        id="salle_defaut"
                                        class="block w-full px-3 py-2 text-sm transition-colors bg-white border border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-200 dark:focus:border-primary-600"
                                    >
                                        <option value="">Sélectionner une salle</option>
                                        @foreach($salles as $salle)
                                            <option value="{{ $salle->id }}">{{ $salle->nom }}</option>
                                        @endforeach
                                    </select>
                                    @error('salle_defaut')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            @if(count($selectedEcs) > 0)
                                <div class="mt-3">
                                    <button type="button" wire:click="copyDateTimeSalleToAllEcs" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 border border-blue-200 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-colors dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-900/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        Appliquer à toutes les matières sélectionnées
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Section Sélection des matières -->
                    <div class="space-y-6">
                        <div class="pb-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <div class="flex items-center justify-center w-8 h-8 mr-3 rounded-lg bg-gradient-to-br from-green-500 to-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                </div>
                                Sélection des matières (EC)
                            </h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Choisissez les éléments constitutifs à inclure dans cet examen
                            </p>
                        </div>

                        <!-- En-tête avec boutons d'action -->
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <!-- Compteur de sélection -->
                            <div class="flex items-center space-x-2">
                                <div class="px-3 py-1.5 bg-primary-100 text-primary-800 rounded-full text-xs font-medium dark:bg-primary-900/30 dark:text-primary-400">
                                    {{ count($selectedEcs) }} matière(s) sélectionnée(s)
                                </div>
                                @if(count($usedEcIds) > 0)
                                    <div class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium dark:bg-gray-800 dark:text-gray-400">
                                        {{ count($usedEcIds) }} matière(s) déjà utilisée(s)
                                    </div>
                                @endif
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex space-x-2">
                                <button type="button" wire:click="selectAllAvailableEcs" class="inline-flex items-center px-3 py-2 text-xs font-medium text-green-700 transition-colors bg-green-100 border border-green-200 rounded-lg hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 dark:bg-green-900/30 dark:border-green-800 dark:text-green-400 dark:hover:bg-green-900/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Tout sélectionner
                                </button>
                                <button type="button" wire:click="deselectAllEcs" class="inline-flex items-center px-3 py-2 text-xs font-medium text-red-700 transition-colors bg-red-100 border border-red-200 rounded-lg hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Tout désélectionner
                                </button>
                            </div>
                        </div>

                        <!-- Options de configuration avancées -->
                        <div class="p-4 border rounded-lg bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-800">
                            <h4 class="flex items-center mb-3 text-sm font-semibold text-amber-800 dark:text-amber-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Options de planification avancée
                            </h4>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="flex items-start space-x-3">
                                    <div class="flex items-center h-5">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleUseSpecificDates"
                                            @if($useSpecificDates) checked @endif
                                            id="useSpecificDates"
                                            class="w-4 h-4 bg-white rounded text-amber-600 border-amber-300 focus:ring-amber-500 focus:ring-2"
                                        >
                                    </div>
                                    <div class="text-sm">
                                        <label for="useSpecificDates" class="font-medium text-amber-800 dark:text-amber-300">
                                            Dates et heures spécifiques par matière
                                        </label>
                                        <p class="mt-1 text-xs text-amber-700 dark:text-amber-400">
                                            Permettre de définir des créneaux différents pour chaque matière
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-3">
                                    <div class="flex items-center h-5">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleUseSpecificSalles"
                                            @if($useSpecificSalles) checked @endif
                                            id="useSpecificSalles"
                                            class="w-4 h-4 bg-white rounded text-amber-600 border-amber-300 focus:ring-amber-500 focus:ring-2"
                                        >
                                    </div>
                                    <div class="text-sm">
                                        <label for="useSpecificSalles" class="font-medium text-amber-800 dark:text-amber-300">
                                            Salles spécifiques par matière
                                        </label>
                                        <p class="mt-1 text-xs text-amber-700 dark:text-amber-400">
                                            Attribuer une salle différente à chaque matière
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des EC groupés par UE -->
                        @if($groupedEcs && count($groupedEcs) > 0)
                            <div class="space-y-4">
                                @foreach($groupedEcs as $ueGroup)
                                    <div class="overflow-hidden border border-gray-200 shadow-sm rounded-xl dark:border-gray-700">
                                        <!-- En-tête de l'UE -->
                                        <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 dark:border-gray-700">
                                            <div class="flex items-center">
                                                <div class="flex items-center justify-center w-8 h-8 mr-3 rounded-lg bg-gradient-to-br from-primary-500 to-primary-600">
                                                    <span class="text-xs font-bold text-white">{{ substr($ueGroup['ue']->abr ?: $ueGroup['ue']->nom, 0, 2) }}</span>
                                                </div>
                                                <div>
                                                    <h4 class="font-semibold text-gray-900 dark:text-white">
                                                        {{ $ueGroup['ue']->abr ? $ueGroup['ue']->abr . ' - ' : '' }}{{ $ueGroup['ue']->nom }}
                                                    </h4>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ count($ueGroup['ecs']) }} matière(s) disponible(s)
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Liste des EC de cette UE -->
                                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($ueGroup['ecs'] as $ec)
                                                <div class="px-4 py-4 {{ in_array($ec->id, $usedEcIds) ? 'bg-gray-50 dark:bg-gray-800/50' : 'hover:bg-gray-50 dark:hover:bg-gray-800/30' }} transition-colors">
                                                    <div class="flex flex-col space-y-3">
                                                        <!-- Ligne principale avec checkbox et nom -->
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center flex-1">
                                                                @if(!in_array($ec->id, $usedEcIds))
                                                                    <input
                                                                        type="checkbox"
                                                                        wire:model.live="selectedEcs"
                                                                        value="{{ $ec->id }}"
                                                                        id="ec_{{ $ec->id }}"
                                                                        class="w-4 h-4 bg-white border-gray-300 rounded text-primary-600 focus:ring-primary-500 focus:ring-2"
                                                                    >
                                                                @else
                                                                    <div class="flex items-center justify-center w-4 h-4">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                                                        </svg>
                                                                    </div>
                                                                @endif

                                                                <div class="flex-1 ml-3">
                                                                    <label for="ec_{{ $ec->id }}" class="block font-medium {{ in_array($ec->id, $usedEcIds) ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-900 dark:text-white cursor-pointer' }}">
                                                                        {{ $ec->abr ? $ec->abr . ' - ' : '' }}{{ $ec->nom }}
                                                                    </label>
                                                                    @if(in_array($ec->id, $usedEcIds))
                                                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                                            Cette matière est déjà utilisée dans un autre examen
                                                                        </p>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            @if(!in_array($ec->id, $usedEcIds) && in_array($ec->id, $selectedEcs))
                                                                <div class="ml-4">
                                                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full dark:bg-green-900/30 dark:text-green-400">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                        </svg>
                                                                        Sélectionnée
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Paramètres spécifiques si l'EC est sélectionné -->
                                                        @if(!in_array($ec->id, $usedEcIds) && in_array($ec->id, $selectedEcs))
                                                            <div class="pl-4 border-l-2 ml-7 border-primary-200 dark:border-primary-800">
                                                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                                                    @if($useSpecificDates)
                                                                        <div>
                                                                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                                Date spécifique
                                                                            </label>
                                                                            <input
                                                                                type="date"
                                                                                wire:model="ecDates.{{ $ec->id }}"
                                                                                class="block w-full px-2.5 py-1.5 text-xs transition-colors bg-white border border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-200 dark:focus:border-primary-600"
                                                                            >
                                                                        </div>
                                                                        <div>
                                                                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                                Heure spécifique
                                                                            </label>
                                                                            <input
                                                                                type="time"
                                                                                wire:model="ecHours.{{ $ec->id }}"
                                                                                class="block w-full px-2.5 py-1.5 text-xs transition-colors bg-white border border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-200 dark:focus:border-primary-600"
                                                                            >
                                                                        </div>
                                                                    @endif
                                                                    @if($useSpecificSalles)
                                                                        <div>
                                                                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                                Salle spécifique
                                                                            </label>
                                                                            <select
                                                                                wire:model="ecSalles.{{ $ec->id }}"
                                                                                class="block w-full px-2.5 py-1.5 text-xs transition-colors bg-white border border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-200 dark:focus:border-primary-600"
                                                                            >
                                                                                <option value="">Choisir une salle</option>
                                                                                @foreach($salles as $salle)
                                                                                    <option value="{{ $salle->id }}">{{ $salle->nom }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('selectedEcs')
                                <div class="p-3 text-sm text-red-700 bg-red-100 border border-red-200 rounded-lg dark:bg-red-900/30 dark:border-red-800 dark:text-red-400">
                                    {{ $message }}
                                </div>
                            @enderror
                        @else
                            <div class="p-12 text-center border-2 border-gray-300 border-dashed rounded-xl dark:border-gray-700">
                                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full dark:bg-gray-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Aucune matière disponible</h3>
                                <p class="max-w-md mx-auto text-sm text-gray-500 dark:text-gray-400">
                                    Toutes les matières (EC) de ce niveau/parcours sont déjà utilisées dans d'autres examens.
                                    Veuillez vérifier la configuration ou contacter l'administrateur.
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Contrôles de soumission -->
                    <div class="flex flex-col gap-4 pt-6 border-t border-gray-200 sm:flex-row sm:justify-between sm:items-center dark:border-gray-700">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            @if(count($selectedEcs) > 0)
                                <span class="font-medium text-primary-600 dark:text-primary-400">
                                    {{ count($selectedEcs) }} matière(s) sélectionnée(s)
                                </span>
                                pour cet examen
                            @else
                                Veuillez sélectionner au moins une matière pour continuer
                            @endif
                        </div>

                        <div class="flex space-x-3">
                            <a href="{{ route('examens.index', [
                                'niveau' => $niveau_id,
                                'parcours' => $parcours_id,
                                'step' => 'examens'
                            ]) }}"
                            class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Annuler
                            </a>
                            <button
                                type="submit"
                                @if(count($selectedEcs) === 0) disabled @endif
                                class="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-lg shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed dark:bg-primary-700 dark:hover:bg-primary-800"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Créer l'examen
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
