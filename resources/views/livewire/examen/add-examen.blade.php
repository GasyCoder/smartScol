<div class="relative">
    <!-- En-tête fixe -->
    <div class="sticky top-0 px-4 py-4 bg-white border-b border-gray-200 shadow-sm z-5 dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal -->
            <h5 class="text-xl font-medium text-slate-700 dark:text-white">Créer un examen</h5>
            @php
                $niveau_id = $this->cleanInputValue($this->niveau_id);
                $parcours_id = $this->cleanInputValue($this->parcours_id);
            @endphp
            
            @if($niveau_id && $parcours_id)
                @if($examenExistant = \App\Models\Examen::where('niveau_id', $niveau_id)->where('parcours_id', $parcours_id)->first())
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i> 
                        Examen existant #{{ $examenExistant->id }} pour 
                        {{ $niveauInfo['nom'] ?? '' }} - {{ $parcoursInfo['nom'] ?? 'Tous parcours' }}
                        <br>
                        <small>{{ $examenExistant->ecs->count() }} EC(s) déjà attaché(s)</small>
                    </div>
                @endif
            @endif
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

                <!-- Bouton de retour -->
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

    <!-- Contenu principal -->
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
                            </div>
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

                        <!-- En-tête avec boutons d'action et contrôles des codes -->
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
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="selectAllAvailableEcs" 
                                        class="inline-flex items-center px-3 py-2 text-xs font-medium text-green-700 transition-colors bg-green-100 border border-green-200 rounded-lg hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 dark:bg-green-900/30 dark:border-green-800 dark:text-green-400 dark:hover:bg-green-900/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Tout sélectionner
                                </button>
                                
                                <button type="button" wire:click="deselectAllEcs" 
                                        class="inline-flex items-center px-3 py-2 text-xs font-medium text-red-700 transition-colors bg-red-100 border border-red-200 rounded-lg hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Tout désélectionner
                                </button>

                                <!-- Boutons pour la gestion des codes -->
                                @if(count($selectedEcs) > 0)
                                    <button type="button" wire:click="genererCodesAutomatiquement" 
                                            class="inline-flex items-center px-3 py-2 text-xs font-medium text-purple-700 transition-colors bg-purple-100 border border-purple-200 rounded-lg hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1 dark:bg-purple-900/30 dark:border-purple-800 dark:text-purple-400 dark:hover:bg-purple-900/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        Générer codes auto
                                    </button>

                                    <button type="button" wire:click="reinitialiserTousLesCodes" 
                                            class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-700 transition-colors bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Réinitialiser codes
                                    </button>
                                @endif
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
                                                                    @if($ec->enseignant)
                                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $ec->enseignant }}</p>
                                                                    @endif
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

                                                        <!-- Configuration salle et code si l'EC est sélectionné -->
                                                        @if(!in_array($ec->id, $usedEcIds) && in_array($ec->id, $selectedEcs))
                                                            <div class="pl-4 border-l-2 ml-7 border-primary-200 dark:border-primary-800">
                                                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                                    <!-- Salle -->
                                                                    <div>
                                                                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                                            </svg>
                                                                            Salle
                                                                        </label>
                                                                        <select
                                                                            wire:model="ecSalles.{{ $ec->id }}"
                                                                            class="block w-full px-2.5 py-1.5 text-xs transition-colors bg-white border border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-200 dark:focus:border-primary-600">
                                                                            <option value="">Aucune salle</option>
                                                                            @foreach($salles as $salle)
                                                                                <option value="{{ $salle->id }}">{{ $salle->nom }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <!-- Code -->
                                                                    <div>
                                                                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                                            </svg>
                                                                            Code personnalisé
                                                                        </label>
                                                                        
                                                                        <div class="flex gap-2">
                                                                            <!-- Champ de saisie -->
                                                                            <div class="flex-1">
                                                                                <input
                                                                                    type="text"
                                                                                    wire:model.blur="ecCodes.{{ $ec->id }}"
                                                                                    placeholder="TA, TB..."
                                                                                    maxlength="3"
                                                                                    style="text-transform: uppercase;"
                                                                                    class="block w-full px-2.5 py-1.5 text-xs transition-colors bg-white border border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-200 dark:focus:border-primary-600"
                                                                                >
                                                                            </div>
                                                                            
                                                                            <!-- Boutons d'action -->
                                                                            <button
                                                                                type="button"
                                                                                wire:click="genererCodePourECSpecifique({{ $ec->id }})"
                                                                                class="px-2 py-1.5 text-xs font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-1 focus:ring-primary-500 transition-colors"
                                                                                title="Générer automatiquement"
                                                                            >
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                                                </svg>
                                                                            </button>
                                                                            
                                                                            <button
                                                                                type="button"
                                                                                wire:click="reinitialiserCodeSpecifique({{ $ec->id }})"
                                                                                class="px-2 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors"
                                                                                title="Réinitialiser"
                                                                            >
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                        
                                                                        <!-- Statut du code -->
                                                                        @if(isset($ecCodes[$ec->id]))
                                                                            <div class="mt-1">
                                                                                @if(!empty($ecCodes[$ec->id]))
                                                                                    @if(!preg_match('/^[A-Z0-9]{2,3}$/i', $ecCodes[$ec->id]))
                                                                                        <p class="text-xs text-orange-600 dark:text-orange-400 flex items-center">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />
                                                                                            </svg>
                                                                                            2-3 caractères requis
                                                                                        </p>
                                                                                    @else
                                                                                        <p class="text-xs text-green-600 dark:text-green-400 flex items-center">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                                            </svg>
                                                                                            Code valide
                                                                                        </p>
                                                                                    @endif
                                                                                @else
                                                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                                        Format: TA, TB, SA...
                                                                                    </p>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>
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

    <!-- Loading overlay -->
    <div wire:loading.flex class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl">
            <div class="flex items-center space-x-3">
                <svg class="animate-spin w-5 h-5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-900 dark:text-white">Création en cours...</span>
            </div>
        </div>
    </div>
</div>