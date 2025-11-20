{{-- Vue améliorée pour saisie manchette avec code anonymat --}}
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
    <!-- Barre de progression en haut -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 p-6 rounded-t-lg border-b border-gray-200 dark:border-gray-600">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
            🏷️ Saisie des manchettes 
            <span class="ml-3 text-base font-normal text-gray-500 dark:text-gray-400">
                {{ $ecSelected?->abr }}.{{ $ecSelected?->nom }} 
                @if($niveauSelected) - {{ $niveauSelected->nom }}@endif
                @if($parcoursSelected) ({{ $parcoursSelected->nom }})@endif
            </span>
        </h2>
        
        <div class="flex items-center gap-2">
            {{-- Bouton Actualiser --}}
            <button wire:click="actualiserSaisie" 
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 dark:text-blue-300 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 rounded-lg transition-colors disabled:opacity-50">
                <svg wire:loading.remove wire:target="actualiserSaisie" class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <svg wire:loading wire:target="actualiserSaisie" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="actualiserSaisie">Actualiser</span>
                <span wire:loading wire:target="actualiserSaisie">Chargement...</span>
            </button>

            {{-- Bouton Configuration --}}
            <button wire:click="backToStep('setup')" 
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-800 bg-gray-50 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Configuration
            </button>
        </div>
    </div>
        <!-- Barre de progression avec présents (vert) et absents (orange) -->
        <div class="mb-4">
            <!-- Présents en VERT -->
            <div class="mb-2">
                <div class="flex items-center justify-between text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <span class="text-green-700 dark:text-green-400">Présents: {{ min($progressCount - $this->nombreManchettesAbsentes, $totalManchettesPresentes) }}/{{ $totalManchettesPresentes }}</span>
                    <span class="text-green-600 dark:text-green-400">{{ min(100, $this->pourcentageProgression) }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-2.5 rounded-full transition-all duration-500 ease-out" 
                        style="width: {{ min(100, $this->pourcentageProgression) }}%">
                        <div class="h-full rounded-full bg-gradient-to-r from-white/20 to-transparent"></div>
                    </div>
                </div>
            </div>
            
            <!-- Absents en ORANGE -->
            @if($this->nombreManchettesAbsentes > 0)
                <div class="mb-1">
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span class="text-orange-700 dark:text-orange-400">Absents: {{ $this->nombreManchettesAbsentes }}/{{ $this->totalAbsents }}</span>
                        <span class="text-orange-600 dark:text-orange-400">
                            {{ $this->totalAbsents > 0 ? round(($this->nombreManchettesAbsentes / $this->totalAbsents) * 100) : 0 }}%
                        </span>
                    </div>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-400 to-orange-600 h-2 rounded-full transition-all duration-500 ease-out" 
                        style="width: {{ $this->totalAbsents > 0 ? min(100, round(($this->nombreManchettesAbsentes / $this->totalAbsents) * 100)) : 0 }}%">
                    </div>
                </div>
            @endif
        </div>

        <!-- Statistiques en ligne -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 text-sm">
            <div class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-3 text-center">
                <div class="font-bold text-blue-600 dark:text-blue-400 text-lg">{{ $codeSalle }}</div>
                <div class="text-gray-600 dark:text-gray-400 text-xs">Code</div>
            </div>
            <div class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-3 text-center">
                <div class="font-bold text-green-600 dark:text-green-400 text-lg">{{ $totalManchettesPresentes }}</div>
                <div class="text-gray-600 dark:text-gray-400 text-xs">Présents</div>
            </div>
            <div class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-3 text-center">
                <div class="font-bold text-red-500 dark:text-red-400 text-lg">{{ $this->totalAbsents }}</div>
                <div class="text-gray-600 dark:text-gray-400 text-xs">Absents</div>
            </div>
            <div class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-3 text-center">
                <div class="font-bold text-orange-600 dark:text-orange-400 text-lg">{{ $this->manchettesRestantes }}</div>
                <div class="text-gray-600 dark:text-gray-400 text-xs">Restantes</div>
            </div>
            <div class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-3 text-center">
                <div class="font-bold text-blue-600 dark:text-blue-400 text-lg">{{ $totalEtudiantsTheorique }}</div>
                <div class="text-gray-600 dark:text-gray-400 text-xs">Total inscrit</div>
            </div>
            <div class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-3 text-center">
                <div class="font-bold text-purple-600 dark:text-purple-400 text-xs">{{ $ecSelected ? ($ecSelected->enseignant ?? 'Non défini') : 'Non sélectionné' }}</div>
                <div class="text-gray-600 dark:text-gray-400 text-xs">Enseignant</div>
            </div>
        </div>
    </div>

    <!-- Corps principal -->
    <div class="p-6">
    @if($progressCount >= $totalManchettesPresentes)
        <!-- État de completion - Version optimisée -->
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg p-4 md:p-6">
            <div class="text-center mb-4">
                <svg class="h-12 w-12 text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-lg md:text-xl font-bold text-green-800 dark:text-green-300 mb-1">
                    Saisie des présents terminée
                </h3>
                <p class="text-sm text-green-600 dark:text-green-400">
                    {{ $progressCount }} manchette(s) enregistrée(s)
                </p>
                
                @if($this->nombreManchettesAbsentes > 0)
                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-2">
                        {{ $this->nombreManchettesAbsentes }} manchette(s) d'absents synchronisée(s)
                    </p>
                @endif
            </div>
            <!-- Section synchronisation des absents -->
                @if($this->canSynchroniserAbsents)
                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-300 dark:border-blue-600 rounded-lg p-4 mb-4">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-3 mb-3">
                        <svg class="h-5 w-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-1">
                                Synchronisation des absents
                            </h4>
                            
                            <!-- Afficher la progression -->
                            @if($this->nombreManchettesAbsentes > 0)
                                <div class="mb-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-600 rounded">
                                    <p class="text-xs font-medium text-yellow-800 dark:text-yellow-300">
                                        ⚠️ Synchronisation en cours : {{ $this->nombreManchettesAbsentes }}/{{ $this->totalAbsents }} absents traités
                                    </p>
                                </div>
                            @endif
                            
                            <p class="text-xs text-blue-700 dark:text-blue-400 mb-2">
                                <strong>{{ $this->totalAbsents - $this->nombreManchettesAbsentes }} étudiant(s) absent(s)</strong> restant(s) à synchroniser.
                            </p>
                            <div class="bg-white/50 dark:bg-gray-800/50 rounded p-2 text-xs text-blue-600 dark:text-blue-400">
                                <p class="font-medium mb-1">Ce qui sera fait :</p>
                                <ul class="space-y-0.5 ml-4 list-disc">
                                    <li>{{ $this->totalAbsents - $this->nombreManchettesAbsentes }} code(s) d'anonymat (marqués absents)</li>
                                    <li>{{ $this->totalAbsents - $this->nombreManchettesAbsentes }} manchette(s) pour les étudiants absents</li>
                                    <li>Numérotation automatique continue</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <button wire:click="synchroniserManchettesAbsents" 
                            wire:loading.attr="disabled"
                            wire:target="synchroniserManchettesAbsents"
                            class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center relative">
                        
                        <span wire:loading.remove wire:target="synchroniserManchettesAbsents" class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Synchroniser les {{ $this->totalAbsents - $this->nombreManchettesAbsentes }} absent(s) restant(s)
                        </span>
                        
                        <!-- Indicateur de chargement amélioré -->
                        <span wire:loading wire:target="synchroniserManchettesAbsents" class="flex flex-col items-center">
                            <span class="flex items-center mb-1">
                                <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Synchronisation en cours...
                            </span>
                            <span class="text-xs opacity-75">Cela peut prendre jusqu'à 2 minutes</span>
                        </span>
                    </button>

                    <!-- Message d'avertissement -->
                    <div wire:loading wire:target="synchroniserManchettesAbsents" 
                        class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-600 rounded-lg">
                        <p class="text-xs text-yellow-800 dark:text-yellow-300 flex items-center">
                            <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Veuillez patienter, ne fermez pas cette page...
                        </p>
                    </div>
                        </div>
                    @elseif($this->synchronisationComplete)
                        <!-- Message de confirmation de synchronisation complète -->
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-600 rounded-lg p-3 mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                    ✅ Tous les absents ont été synchronisés ({{ $this->nombreManchettesAbsentes }}/{{ $this->totalAbsents }})
                                </p>
                            </div>
                        </div>
                    @endif

                        <!-- Boutons d'action -->
                        <div class="space-y-3">
                            @if($this->canSynchroniserAbsents)
                                <!-- Alerte obligatoire -->
                                <div class="p-3 bg-red-50 dark:bg-red-900/20 border-2 border-red-500 dark:border-red-700 rounded-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-bold text-red-900 dark:text-red-300">
                                                Action obligatoire
                                            </h4>
                                            <p class="text-xs text-red-800 dark:text-red-400 mt-0.5">
                                                Vous devez synchroniser les <strong>{{ $this->totalAbsents }} absent(s)</strong> avant de continuer.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button wire:click="backToStep('ec')"
                                        @if(!$this->peutQuitterEtape) disabled @endif
                                        class="px-4 py-2.5 text-sm rounded-lg transition-colors focus:ring-2 focus:ring-offset-2 flex items-center justify-center
                                            {{ $this->peutQuitterEtape 
                                                ? 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500' 
                                                : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed opacity-60' }}">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Nouvelle matière
                                    @if(!$this->peutQuitterEtape)
                                        <svg class="h-4 w-4 ml-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </button>

                                <button wire:click="backToStep('setup')"
                                        @if(!$this->peutQuitterEtape) disabled @endif
                                        class="px-4 py-2.5 text-sm rounded-lg transition-colors focus:ring-2 focus:ring-offset-2 flex items-center justify-center
                                            {{ $this->peutQuitterEtape 
                                                ? 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500' 
                                                : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed opacity-60' }}">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Configuration
                                    @if(!$this->peutQuitterEtape)
                                        <svg class="h-4 w-4 ml-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </button>
                            </div>
                            
                            @if(!$this->peutQuitterEtape)
                                <p class="text-xs text-center text-gray-600 dark:text-gray-400">
                                    🔒 Synchronisez d'abord les absents pour débloquer ces options
                                </p>
                            @endif
                        </div>
                    </div>
                @else
                <!-- Interface de saisie - Version optimisée -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Zone de saisie principale -->
                    <div class="lg:col-span-2 space-y-4">
                        <!-- Formulaire matricule -->
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <div class="mb-4">
                                <label for="matricule" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Matricule étudiant <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                    wire:model.live="matricule" 
                                    id="matricule"
                                    class="w-full px-3 py-3 text-base font-mono border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Saisir le matricule..."
                                    autocomplete="off"
                                    autofocus
                                    wire:keydown.enter="validerParEntree">
                            </div>

                            <!-- Affichage étudiant trouvé -->
                            @if($etudiantTrouve)
                                <div class="mb-4 p-3 {{ $matriculeExisteDeja || (isset($etudiantTrouve->message_erreur)) ? 'bg-red-50 border-red-200 dark:bg-red-900/30 dark:border-red-700' : 'bg-green-50 border-green-200 dark:bg-green-900/30 dark:border-green-700' }} border rounded-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="h-4 w-4 {{ ($matriculeExisteDeja || isset($etudiantTrouve->message_erreur)) ? 'text-red-400' : 'text-green-400' }} flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            @if($matriculeExisteDeja || isset($etudiantTrouve->message_erreur))
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            @else
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            @endif
                                        </svg>
                                        <div>
                                            <h4 class="text-xl font-bold {{ ($matriculeExisteDeja || isset($etudiantTrouve->message_erreur)) ? 'text-red-800 dark:text-red-300' : 'text-green-800 dark:text-green-300' }}">
                                                {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenom }}
                                            </h4>
                                            <p class="text-xs {{ ($matriculeExisteDeja || isset($etudiantTrouve->message_erreur)) ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }} mt-0.5">
                                                @if(isset($etudiantTrouve->message_erreur))
                                                    {{ $etudiantTrouve->message_erreur }}
                                                @elseif($matriculeExisteDeja)
                                                    Déjà enregistré pour cette matière
                                                @else
                                                    Prêt pour l'enregistrement
                                                    @if($sessionType === 'rattrapage')
                                                        <span class="block mt-0.5 text-blue-600 dark:text-blue-400">
                                                            Éligible au rattrapage
                                                        </span>
                                                    @endif
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @elseif(!empty($matricule) && strlen($matricule) >= 3)
                                <div class="mb-4 p-3 bg-red-50 border border-red-200 dark:bg-red-900/30 dark:border-red-700 rounded-lg">
                                    <p class="text-xs text-red-700 dark:text-red-300">
                                        Aucun étudiant trouvé avec ce matricule
                                    </p>
                                </div>
                            @endif

                            <!-- Conseils -->
                            <div class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-2.5 rounded-lg">
                                <p class="font-medium mb-1">Conseils :</p>
                                <ul class="space-y-0.5 ml-4 list-disc">
                                    <li>Recherche automatique après 3 caractères</li>
                                    <li>Appuyez sur Entrée pour valider</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Panneau latéral - Code anonymat -->
                    <div class="space-y-4">
                        @if($etudiantTrouve && !$matriculeExisteDeja && !isset($etudiantTrouve->message_erreur))
                            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-3 flex items-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Code d'anonymat
                                </h4>

                                <!-- Champ code -->
                                <div class="mb-3">
                                    <label for="codeAnonymat" class="block text-xs font-medium text-blue-700 dark:text-blue-300 mb-1.5">
                                        Code suggéré <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                        wire:model.live="codeAnonymatSaisi" 
                                        id="codeAnonymat"
                                        class="w-full px-3 text-2xl py-2.5 font-bold text-center border {{ $this->codeEstValide ? 'border-green-300 bg-green-50 dark:bg-green-900/20' : 'border-blue-300 bg-blue-50 dark:bg-blue-900/20' }} dark:border-blue-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white"
                                        placeholder="AB1"
                                        autocomplete="off"
                                        style="letter-spacing: 0.1em;"
                                        wire:keydown.enter="validerParEntree">

                                    <!-- Validation -->
                                    <div class="mt-1.5 text-xs">
                                        @if(!empty($codeValidationErrors))
                                            @foreach($codeValidationErrors as $error)
                                                <p class="text-red-600 dark:text-red-400 mb-0.5">{{ $error }}</p>
                                            @endforeach
                                        @elseif(!empty($codeAnonymatSaisi))
                                            <p class="text-green-600 dark:text-green-400 font-bold">Code valide</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Format -->
                                {{-- <div class="mb-3 p-2 bg-white/50 dark:bg-gray-800/50 rounded border text-xs">
                                    <p class="font-medium text-blue-800 dark:text-blue-300 mb-1">Format :</p>
                                    <ul class="text-blue-700 dark:text-blue-400 space-y-0.5 ml-4 list-disc">
                                        <li>2 lettres MAJUSCULES + chiffres</li>
                                        <li>Ex: AB1, XY25, ZZ100</li>
                                        <li>Max {{ $totalManchettesPresentes }}</li>
                                    </ul>
                                </div> --}}

                                <!-- Bouton validation -->
                                <button wire:click="validerEtConfirmer" 
                                        wire:loading.attr="disabled"
                                        wire:target="validerEtConfirmer"
                                        class="w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed {{ !$etudiantTrouve || $matriculeExisteDeja || !$this->codeEstValide ? 'bg-gray-300 text-gray-500 dark:bg-gray-600 dark:text-gray-400' : 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-2 focus:ring-blue-500 focus:ring-offset-2' }}"
                                        {{ !$etudiantTrouve || $matriculeExisteDeja || !$this->codeEstValide || isset($etudiantTrouve->message_erreur) ? 'disabled' : '' }}>

                                    <span wire:loading.remove wire:target="validerEtConfirmer">
                                        @if(!$etudiantTrouve)
                                            Saisir un matricule valide
                                        @elseif(isset($etudiantTrouve->message_erreur))
                                            Étudiant non éligible
                                        @elseif($matriculeExisteDeja)
                                            Déjà enregistré
                                        @elseif(!$this->codeEstValide)
                                            Corriger le code
                                        @else
                                            Valider la manchette
                                        @endif  
                                    </span>
                                    
                                    <span wire:loading wire:target="validerEtConfirmer" class="flex items-center justify-center">
                                        <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.38 0 0 5.38 0 12h4zm2 5.29l.4.95c.8 1.8.8 1.8.8 1.8z"></path>
                                        </svg>
                                        Validation...
                                    </span>
                                </button>
                            </div>
                        @endif

                        <!-- Statistiques -->
                        <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                            <h4 class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Statistiques de session
                            </h4>
                            <div class="space-y-1.5 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Session:</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200 capitalize">{{ $sessionType }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Progression:</span>
                                    <span class="font-medium text-blue-600 dark:text-blue-400">{{ $this->pourcentageProgression }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Présence:</span>
                                    <span class="font-medium text-green-600 dark:text-green-400">{{ $this->pourcentagePresence }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                </div>
</div>
@include('livewire.manchette.partials.modal-confirm')
