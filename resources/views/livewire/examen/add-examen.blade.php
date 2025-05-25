<div class="relative">
    <!-- En-tête fixe -->
    <div class="sticky top-0 z-10 px-5 py-4 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
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
                        @if($currentSession)
                            <span class="mx-2 text-slate-400">/</span>
                            Session:
                            <span class="font-medium text-amber-600 dark:text-amber-400">
                                {{ $currentSession->type }}
                            </span>
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
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm dark:bg-gray-950 dark:border-gray-800">
            <div class="p-5">
                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Champs cachés -->
                        <input type="hidden" wire:model="session_id" id="session_id">
                        <input type="hidden" wire:model="niveau_id">
                        <input type="hidden" wire:model="parcours_id">

                        <!-- Information sur la session -->
                        <div class="p-3 border border-blue-100 rounded-md bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm text-blue-600 dark:text-blue-300">
                                    Session: <strong>{{ $sessionInfo['nom'] ?? 'Non définie' }}</strong>
                                    (du {{ $sessionInfo['date_start'] ?? '' }} au {{ $sessionInfo['date_end'] ?? '' }})
                                </span>
                            </div>
                        </div>

                        <!-- Informations générales -->
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label for="duree" class="block mb-1 text-sm font-medium text-slate-700 dark:text-white">Durée (minutes)</label>
                                <input type="number" wire:model="duree" id="duree" min="15" step="5" class="block w-full text-sm leading-4.5 pe-10 ps-4 py-1.5 h-10 text-slate-700 dark:text-white placeholder-slate-300 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 outline-none focus:border-primary-500 focus:dark:border-primary-600 focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 disabled:bg-slate-50 disabled:dark:bg-slate-950 disabled:cursor-not-allowed rounded-md transition-all">
                                @error('duree') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="note_eliminatoire" class="block mb-1 text-sm font-medium text-slate-700 dark:text-white">Note éliminatoire (optionnel)</label>
                                <input type="number" wire:model="note_eliminatoire" id="note_eliminatoire" min="0" max="20" step="0.01" class="block w-full text-sm leading-4.5 pe-10 ps-4 py-1.5 h-10 text-slate-700 dark:text-white placeholder-slate-300 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 outline-none focus:border-primary-500 focus:dark:border-primary-600 focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 disabled:bg-slate-50 disabled:dark:bg-slate-950 disabled:cursor-not-allowed rounded-md transition-all">
                                <p class="mt-1 text-xs text-slate-400">Uniquement pour les examens de type concours</p>
                                @error('note_eliminatoire') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <!-- Liste des EC groupés par UE -->
                        <div>
                    <div class="mb-4">
                        <!-- En-tête avec label et boutons d'action -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                            <h4 class="text-sm font-medium text-slate-700 dark:text-white">Sélection des matières (EC)</h4>

                            <!-- Boutons d'action -->
                            <div class="flex space-x-2 shrink-0">
                                <button type="button" wire:click="selectAllAvailableEcs" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-md hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400 dark:hover:bg-green-900/40 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Tout sélectionner
                                </button>
                                <button type="button" wire:click="deselectAllEcs" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/40 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Tout désélectionner
                                </button>
                            </div>
                        </div>

                        <!-- Options de configuration (checkboxes) -->
                        <div class="flex flex-wrap gap-x-6 gap-y-2 p-3 mb-3 bg-gray-50 dark:bg-gray-800 rounded-md">
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    wire:click="toggleUseSpecificDates"
                                    @if($useSpecificDates) checked @endif
                                    id="useSpecificDates"
                                    class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 focus:ring-offset-0"
                                >
                                <label for="useSpecificDates" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Dates spécifiques par matière
                                    </span>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    wire:click="toggleUseSpecificSalles"
                                    @if($useSpecificSalles) checked @endif
                                    id="useSpecificSalles"
                                    class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 focus:ring-offset-0"
                                >
                                <label for="useSpecificSalles" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Salles spécifiques par matière
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                            @if($groupedEcs && count($groupedEcs) > 0)
                                <div class="space-y-4">
                                    @foreach($groupedEcs as $ueGroup)
                                        <div class="overflow-hidden border border-gray-200 rounded-md dark:border-gray-700">
                                            <!-- En-tête de l'UE -->
                                            <div class="px-4 py-2 font-medium text-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-200">
                                                {{ $ueGroup['ue']->abr ? $ueGroup['ue']->abr . ' - ' : '' }}{{ $ueGroup['ue']->nom }}
                                            </div>

                                            <!-- Liste des EC de cette UE -->
                                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach($ueGroup['ecs'] as $ec)
                                                    <div class="flex flex-col px-4 py-3 {{ in_array($ec->id, $usedEcIds) ? 'bg-gray-100 dark:bg-gray-800/50' : 'hover:bg-gray-50 dark:hover:bg-gray-800/30' }} md:flex-row md:items-center">
                                                        <div class="flex items-center flex-1">
                                                            @if(!in_array($ec->id, $usedEcIds))
                                                                <input type="checkbox" wire:model.live="selectedEcs" value="{{ $ec->id }}" id="ec_{{ $ec->id }}"
                                                                    class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500">
                                                            @else
                                                                <span class="flex items-center justify-center w-4 h-4 text-xs text-gray-400" title="EC déjà utilisé">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                                                    </svg>
                                                                </span>
                                                            @endif
                                                            <label for="ec_{{ $ec->id }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300 {{ in_array($ec->id, $usedEcIds) ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                                                                {{ $ec->abr ? $ec->abr . ' - ' : '' }}{{ $ec->nom }}
                                                            </label>
                                                        </div>

                                                        @if(!in_array($ec->id, $usedEcIds) && in_array($ec->id, $selectedEcs))
                                                            <div class="flex flex-wrap mt-2 space-x-2 md:mt-0">
                                                                @if($useSpecificDates)
                                                                    <input type="date" wire:model="ecDates.{{ $ec->id }}" class="block w-32 px-2 py-1 text-sm transition-all border border-gray-200 rounded dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                                                    <input type="time" wire:model="ecHours.{{ $ec->id }}" class="block w-24 px-2 py-1 text-sm transition-all border border-gray-200 rounded dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                                                @endif
                                                                @if($useSpecificSalles)
                                                                    <select wire:model="ecSalles.{{ $ec->id }}" class="block w-40 px-2 py-1 text-sm transition-all border border-gray-200 rounded dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                                                        <option value="">-- Salle --</option>
                                                                        @foreach($salles as $salle)
                                                                            <option value="{{ $salle->id }}">{{ $salle->nom }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selectedEcs') <div class="mt-1 text-xs text-red-500">{{ $message }}</div> @enderror
                            @else
                                <div class="p-8 text-center border border-gray-200 rounded-md dark:border-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">Aucun EC disponible</p>
                                    <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Tous les ECs de ce niveau/parcours sont déjà utilisés dans d'autres examens.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Contrôles de soumission -->
                        <div class="flex justify-end mt-6 space-x-3">
                            <a href="{{ route('examens.index', [
                                'niveau' => $niveau_id,
                                'parcours' => $parcours_id,
                                'step' => 'examens'
                            ]) }}"
                            class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                                Annuler
                            </a>
                            <button type="submit" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded bg-primary-600 text-white hover:bg-primary-700 focus:outline-none dark:bg-primary-700 dark:hover:bg-primary-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
