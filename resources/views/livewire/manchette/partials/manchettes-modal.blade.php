<!-- Modale de saisie de manchette - VERSION AMÉLIORÉE -->
@if($showManchetteModal)
<div class="fixed inset-0 z-10 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

        <!-- Centrage modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Contenu modal - OPTIMISÉ -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 dark:bg-gray-800">
                <div class="sm:flex sm:items-start">
                    <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                            {{ isset($editingManchetteId) ? 'Modifier une manchette' : 'Saisir une manchette' }}
                        </h3>

                        <!-- Informations contextuelles + Données de présence -->
                        <div class="p-3 mt-3 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-blue-900 dark:text-blue-200">
                            <div class="mb-1 font-semibold">Informations de saisie:</div>
                            <div class="grid grid-cols-3 gap-2">
                                <div><span class="font-medium">Salle:</span> {{ $currentSalleName }}</div>
                                <div><span class="font-medium">Code salle:</span> {{ $selectedSalleCode }}</div>
                                <div><span class="font-medium">Matière:</span> {{ $currentEcName }}</div>
                                @if($currentEcDate)
                                <div><span class="font-medium">Date:</span> {{ $currentEcDate }}</div>
                                @endif
                                @if($currentEcHeure)
                                <div><span class="font-medium">Heure:</span> {{ $currentEcHeure }}</div>
                                @endif
                                <!-- NOUVEAU : Affichage des données de présence -->
                                @if($presenceData)
                                <div class="col-span-3 mt-2 pt-2 border-t border-blue-300">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <span class="font-medium">📊 Présence :</span>
                                            <span class="px-2 py-1 bg-green-200 text-green-800 rounded text-xs font-medium">
                                                {{ $presenceData->etudiants_presents }} présent(s)
                                            </span>
                                            <span class="px-2 py-1 bg-red-200 text-red-800 rounded text-xs font-medium">
                                                {{ $presenceData->etudiants_absents }} absent(s)
                                            </span>
                                            @php 
                                                $manchettesSaisies = count($etudiantsAvecManchettes ?? []);
                                                $manchettesRestantes = $presenceData->etudiants_presents - $manchettesSaisies;
                                            @endphp
                                            <span class="px-2 py-1 bg-blue-200 text-blue-800 rounded text-xs font-medium">
                                                {{ $manchettesRestantes }} manchette(s) restante(s)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Formulaire principal - LAYOUT AMÉLIORÉ -->
                        <form wire:submit.prevent="saveManchette" class="mt-4">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                
                                <!-- Colonne gauche : Saisie -->
                                <div class="space-y-4">
                                    <!-- Code anonymat avec suggestion -->
                                    <div>
                                        {{-- <label for="code_anonymat" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Code d'anonymat
                                        </label>
                                        <div class="mt-1 relative">
                                            <input type="text"
                                                wire:model="code_anonymat"
                                                id="code_fake"
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white pr-20"
                                                placeholder="">
                                        </div> --}}
                                        <div class="mt-1 relative">
                                            <input type="hidden"
                                                wire:model="code_anonymat"
                                                id="code_anonymat"
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white pr-20"
                                                placeholder="Ex: {{ $selectedSalleCode }}1"
                                                autofocus>
                                            <!-- Badge du code généré -->
                                            {{-- <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded dark:bg-gray-600 dark:text-gray-300">
                                                    Auto
                                                </span>
                                            </div> --}}
                                        </div>
                                        @error('code_anonymat') <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Recherche étudiant améliorée -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            🔍 Rechercher un étudiant présent
                                        </label>
                                        
                                        <!-- Mode de recherche avec design amélioré -->
                                        <div class="flex mt-2 p-1 bg-gray-100 rounded-lg dark:bg-gray-700">
                                            <label class="flex-1 inline-flex items-center justify-center cursor-pointer">
                                                <input type="radio" wire:model.live="searchMode" value="matricule" class="sr-only">
                                                <span class="w-full px-3 py-2 text-sm text-center rounded-md transition-colors duration-200
                                                    {{ $searchMode === 'matricule' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-600 dark:text-white' : 'text-gray-700 dark:text-gray-300' }}">
                                                    📋 Matricule
                                                </span>
                                            </label>
                                            <label class="flex-1 inline-flex items-center justify-center cursor-pointer">
                                                <input type="radio" wire:model.live="searchMode" value="nom" class="sr-only">
                                                <span class="w-full px-3 py-2 text-sm text-center rounded-md transition-colors duration-200
                                                    {{ $searchMode === 'nom' ? 'bg-white text-primary-600 shadow-sm dark:bg-gray-600 dark:text-white' : 'text-gray-700 dark:text-gray-300' }}">
                                                    👤 Nom/Prénom
                                                </span>
                                            </label>
                                        </div>

                                        <!-- Champ de recherche principal -->
                                        <div class="relative mt-2">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                <em class="text-gray-400 ni ni-search"></em>
                                            </div>
                                            <input type="text"
                                                wire:model.live="searchQuery"
                                                wire:keydown.enter="handleEnterKey"
                                                id="searchQuery"
                                                class="block w-full pl-10 pr-10 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                placeholder="{{ $searchMode === 'matricule' ? 'Ex: 37014 (tapez 5 caractères)' : 'Ex: Dupont Martin' }}"
                                                autocomplete="off"
                                                maxlength="{{ $searchMode === 'matricule' ? 5 : 50 }}">
                                            
                                            @if($searchQuery)
                                            <button type="button"
                                                wire:click="$set('searchQuery', '')"
                                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-500">
                                                <em class="ni ni-cross"></em>
                                            </button>
                                            @endif
                                        </div>

                                        <!-- Indicateur de recherche -->
                                        <div class="mt-1 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ $searchMode === 'matricule' ? 'Recherche par matricule' : 'Recherche par nom/prénom' }}</span>
                                            @if($searchQuery && strlen($searchQuery) >= 2)
                                                <span class="text-blue-600">{{ count($searchResults ?? []) }} résultat(s)</span>
                                            @endif
                                        </div>

                                        @error('etudiant_id') <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror

                                        <!-- Résultats de recherche améliorés -->
                                        @if($searchQuery && strlen($searchQuery) >= 2)
                                            <div class="mt-2 overflow-y-auto bg-white border border-gray-300 rounded-md shadow-sm dark:bg-gray-800 dark:border-gray-600 max-h-48">
                                                @if(count($searchResults) > 0)
                                                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                                        @foreach($searchResults as $etudiant)
                                                        <li class="px-4 py-3 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-150" 
                                                            wire:click="selectEtudiant({{ $etudiant->id }})">
                                                            <div class="flex items-center justify-between">
                                                                <div class="flex items-center space-x-3">
                                                                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                                        <span class="text-xs font-medium text-blue-600">{{ substr($etudiant->prenom, 0, 1) }}{{ substr($etudiant->nom, 0, 1) }}</span>
                                                                    </div>
                                                                    <div>
                                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                                            {{ $etudiant->nom }} {{ $etudiant->prenom }}
                                                                        </div>
                                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                            Matricule: {{ $etudiant->matricule }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <em class="text-blue-600 ni ni-arrow-right"></em>
                                                            </div>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <div class="px-4 py-6 text-center">
                                                        <em class="text-gray-400 text-2xl ni ni-search-alt"></em>
                                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Aucun étudiant présent trouvé</p>
                                                        <p class="text-xs text-gray-400">Vérifiez l'orthographe ou essayez un autre terme</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Étudiant sélectionné -->
                                    @if($etudiant_id && $matricule)
                                    <div class="p-4 border-2 border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                    <em class="text-green-600 ni ni-check-circle animate-pulse"></em>
                                                </div>
                                                <div>
                                                    <div class="text-sm text-green-800 dark:text-green-300">
                                                        {{ App\Models\Etudiant::find($etudiant_id)->nom ?? '' }} {{ App\Models\Etudiant::find($etudiant_id)->prenom ?? '' }}
                                                    </div>
                                                    <div class="text-lg text-green-700 dark:text-green-400">
                                                        📋 Matricule: {{ $matricule }}
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" 
                                                wire:click="resetEtudiantSelection" 
                                                class="p-2 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-full transition-colors duration-200">
                                                <em class="ni ni-cross"></em>
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Colonne droite : Informations et raccourcis -->
                                <div class="space-y-4">
                                    @if(!isset($editingManchetteId))
                                        <!-- Progression des manchettes avec état terminé -->
                                        @if($presenceData)
                                        <div class="p-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                                📊 Progression des manchettes
                                            </h4>
                                            
                                            @php 
                                                $manchettesSaisies = count($etudiantsAvecManchettes ?? []);
                                                $totalPresents = $presenceData->etudiants_presents;
                                                $pourcentageProgress = $totalPresents > 0 ? round(($manchettesSaisies / $totalPresents) * 100) : 0;
                                                $isTerminee = $manchettesSaisies >= $totalPresents;
                                            @endphp
                                            
                                            <!-- NOUVEAU : Affichage différent si terminé -->
                                            @if($isTerminee)
                                                <div class="text-center space-y-3">
                                                    <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center">
                                                        <em class="text-2xl text-green-600 ni ni-check-circle-fill"></em>
                                                    </div>
                                                    <div>
                                                        <div class="text-lg font-semibold text-green-800 dark:text-green-200">
                                                            🎉 Saisie terminée !
                                                        </div>
                                                        <div class="text-sm text-green-700 dark:text-green-300">
                                                            Toutes les manchettes ont été saisies
                                                        </div>
                                                        <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                                                            {{ $totalPresents }}/{{ $totalPresents }} étudiants présents
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Bouton de fermeture automatique -->
                                                    <button type="button"
                                                            wire:click="forceCloseModal"
                                                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        <em class="mr-2 ni ni-check"></em>
                                                        Fermer et terminer
                                                    </button>
                                                </div>
                                            @else
                                                <!-- Affichage normal en cours de saisie -->
                                                <div class="space-y-3">
                                                    <div class="flex justify-between text-sm">
                                                        <span class="text-gray-600 dark:text-gray-400">Manchettes saisies</span>
                                                        <span class="font-medium">{{ $manchettesSaisies }}/{{ $totalPresents }}</span>
                                                    </div>
                                                    
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" 
                                                            style="width: {{ $pourcentageProgress }}%"></div>
                                                    </div>
                                                    
                                                    <div class="flex justify-between text-xs text-gray-500">
                                                        <span>{{ $pourcentageProgress }}% complété</span>
                                                        @php $restantes = $totalPresents - $manchettesSaisies; @endphp
                                                        @if($restantes > 0)
                                                            <span class="{{ $restantes <= 3 ? 'text-orange-600 font-medium' : '' }}">
                                                                {{ $restantes }} restante{{ $restantes > 1 ? 's' : '' }}
                                                                @if($restantes <= 3) 🎯 @endif
                                                            </span>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Message d'encouragement -->
                                                    @if($restantes <= 5 && $restantes > 0)
                                                        <div class="mt-2 p-2 bg-orange-50 border border-orange-200 rounded text-xs text-orange-800 text-center">
                                                            @if($restantes == 1)
                                                                🚀 Plus qu'une seule manchette !
                                                            @elseif($restantes <= 3)
                                                                🎯 Encore {{ $restantes }} manchettes, vous y êtes presque !
                                                            @else
                                                                💪 Plus que {{ $restantes }} manchettes !
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        @endif

                                        <!-- NOUVEAU : Actions rapides quand proche de la fin -->
                                        @if($presenceData && !$isTerminee)
                                            @php $restantes = $totalPresents - $manchettesSaisies; @endphp
                                            @if($restantes <= 10 && count($etudiantsSansManchette ?? []) > 0)
                                                <div class="p-3 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                                                    <h5 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                                                        ⚡ Actions rapides
                                                    </h5>
                                                    <div class="space-y-2">
                                                        @if(count($etudiantsSansManchette) > 0)
                                                            <button type="button"
                                                                    wire:click="selectFirstStudent"
                                                                    class="w-full text-left px-3 py-2 text-xs bg-white border border-blue-300 rounded hover:bg-blue-50 transition-colors">
                                                                👆 Sélectionner le premier étudiant
                                                            </button>
                                                        @endif
                                                        @if(count($etudiantsSansManchette) > 1)
                                                            <button type="button"
                                                                    wire:click="selectRandomStudent"
                                                                    class="w-full text-left px-3 py-2 text-xs bg-white border border-blue-300 rounded hover:bg-blue-50 transition-colors">
                                                                🎲 Sélectionner un étudiant au hasard
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                    @else
                                        <!-- Mode modification - inchangé -->
                                        <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-800">
                                            <div class="flex items-center">
                                                <em class="mr-2 text-yellow-600 ni ni-edit dark:text-yellow-400"></em>
                                                <span class="text-sm text-yellow-800 dark:text-yellow-200">Mode modification</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Boutons d'action améliorés -->
                            <div class="mt-6 flex flex-col sm:flex-row gap-3 sm:justify-end">
                                @if(!isset($editingManchetteId))
                                    <button type="button"
                                            wire:click="closeModalWithConfirmation"
                                            class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                        <em class="mr-2 ni ni-cross"></em>
                                        Terminer la saisie
                                    </button>
                                @else
                                    <button type="button"
                                            wire:click="forceCloseModal"
                                            class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                        <em class="mr-2 ni ni-cross"></em>
                                        Annuler
                                    </button>
                                @endif

                                <button type="submit" 
                                        class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm
                                        {{ isset($editingManchetteId)
                                            ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
                                            : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500'
                                        }}
                                        focus:outline-none focus:ring-2 focus:ring-offset-2">

                                    @if(isset($editingManchetteId))
                                        <em class="mr-2 ni ni-update"></em>
                                        Mettre à jour
                                    @else
                                        <em class="mr-2 ni ni-save"></em>
                                        Enregistrer et continuer
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
