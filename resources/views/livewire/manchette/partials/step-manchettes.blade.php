<!-- Étape Manchettes -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <!-- Colonne principale - Formulaire de saisie -->
    <div class="xl:col-span-2">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a1.994 1.994 0 01-1.414.586H7a1 1 0 01-1-1V4a1 1 0 011-1z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Saisie des Manchettes
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Attribution des codes d'anonymat aux étudiants
                        </p>
                    </div>
                </div>
                
                <!-- Bouton retour -->
                <button wire:click="goToStep('setup')" 
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    ← Configuration
                </button>
            </div>
            
            <!-- Barre de progression -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Progression des manchettes
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $progressManchettes }} / {{ $totalManchettesPresentes }}
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500 ease-out" 
                        style="width: {{ $this->pourcentageManchettes }}%">
                    </div>
                </div>
                <div class="flex justify-between items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span>0%</span>
                    <span class="font-medium">{{ $this->pourcentageManchettes }}% complété</span>
                    <span>100%</span>
                </div>
            </div>
            
            @if($progressManchettes >= $totalManchettesPresentes)
                <!-- Toutes les manchettes saisies -->
                <div class="text-center py-8">
                    <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4 pulse-success">
                        <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-green-800 dark:text-green-300 mb-2">
                        Toutes les manchettes ont été saisies !
                    </h3>
                    <p class="text-sm text-green-600 dark:text-green-400 mb-6">
                        {{ $progressManchettes }} codes d'anonymat ont été attribués avec succès.
                    </p>
                    
                    <!-- Bouton vers les copies -->
                    <button wire:click="goToStep('copies')" 
                        class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 transform hover:scale-105 focus:ring-4 focus:ring-green-200 dark:focus:ring-green-800">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                        </svg>
                        Passer aux copies
                    </button>
                </div>
            @else
                <!-- Informations du prochain code -->
                <div class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-700 rounded-xl">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a1.994 1.994 0 01-1.414.586H7a1 1 0 01-1-1V4a1 1 0 011-1z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-blue-800 dark:text-blue-300 mb-1">
                                Prochain code d'anonymat
                            </h4>
                            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                {{ $prochainCodeAnonymat }}
                            </div>
                            <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                                Séquence n°{{ $prochaineSequence }} - Salle {{ $codeSalle }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ $progressManchettes + 1 }}
                            </div>
                            <div class="text-xs text-blue-500 dark:text-blue-400">
                                sur {{ $totalManchettesPresentes }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de saisie -->
                <div class="space-y-6">
                    <div>
                        <label for="matricule" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Matricule de l'étudiant 
                                <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        
                        <div class="relative">
                            <input type="text" 
                                wire:model.live.debounce.300ms="matricule" 
                                wire:keydown.enter="sauvegarderManchette"
                                id="matricule" 
                                placeholder="Saisissez le matricule et appuyez sur Entrée..." 
                                autocomplete="off"
                                class="w-full px-4 py-4 text-lg border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200">
                                
                            <!-- Indicateur de validation -->
                            @if($matricule && strlen($matricule) >= 3)
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    @if($etudiantTrouve)
                                        <div class="flex items-center">
                                            <svg class="h-6 w-6 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm font-medium text-green-600 dark:text-green-400">↵</span>
                                        </div>
                                    @else
                                        <svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <!-- Instructions -->
                        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="text-sm text-blue-700 dark:text-blue-300">
                                    <p class="font-medium mb-1">Mode de saisie rapide :</p>
                                    <p>Tapez le matricule puis appuyez sur <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">Entrée</kbd> pour enregistrer automatiquement.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations étudiant trouvé -->
                        @if($etudiantTrouve)
                            <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-xl slide-in-right">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-green-100 dark:bg-green-800/50 rounded-full flex items-center justify-center mr-4">
                                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-semibold text-green-800 dark:text-green-300">
                                                {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenom }}
                                            </h4>
                                            <p class="text-sm text-green-600 dark:text-green-400">
                                                Matricule: {{ $etudiantTrouve->matricule }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                            → {{ $prochainCodeAnonymat }}
                                        </div>
                                        <div class="text-xs text-green-500 dark:text-green-400">
                                            Code d'anonymat
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex justify-between items-center pt-6">
                        <button type="button" wire:click="resetSaisieForm" 
                            class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Effacer
                        </button>
                        
                        <button type="button" 
                            wire:click="sauvegarderManchette"
                            wire:loading.attr="disabled"
                            wire:target="sauvegarderManchette"
                            class="px-8 py-3 text-white text-base font-medium rounded-lg shadow-lg transition-all duration-200 transform focus:ring-4 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed
                                   {{ $etudiantTrouve 
                                        ? 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 hover:scale-105 ring-2 ring-green-300 focus:ring-green-200' 
                                        : 'bg-gray-400 cursor-not-allowed' }}"
                            {{ !$etudiantTrouve ? 'disabled' : '' }}>
                            
                            <!-- Loading spinner -->
                            <svg wire:loading wire:target="sauvegarderManchette" 
                                class="inline w-5 h-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            
                            <span wire:loading.remove wire:target="sauvegarderManchette">
                                @if($etudiantTrouve)
                                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Enregistrer → {{ $prochainCodeAnonymat }}
                                @else
                                    Enregistrer la manchette
                                @endif
                            </span>
                            <span wire:loading wire:target="sauvegarderManchette">
                                Enregistrement...
                            </span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Colonne droite - Statistiques et historique -->
    <div class="space-y-6">
        <!-- Statistiques en temps réel -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Statistiques
            </h3>
            
            <!-- Compteur principal -->
            <div class="text-center mb-6 p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl">
                <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                    {{ $progressManchettes }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    manchettes saisies sur {{ $totalManchettesPresentes }}
                </div>
                <div class="mt-2 text-2xl font-semibold text-blue-500 dark:text-blue-400">
                    {{ $this->pourcentageManchettes }}%
                </div>
            </div>
            
            <!-- Détail des statistiques -->
            <div class="grid grid-cols-1 gap-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total étudiants</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $totalEtudiantsTheorique }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <span class="text-sm text-green-700 dark:text-green-400">Présents</span>
                    <span class="font-semibold text-green-800 dark:text-green-300">{{ $totalManchettesPresentes }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <span class="text-sm text-red-700 dark:text-red-400">Absents</span>
                    <span class="font-semibold text-red-800 dark:text-red-300">{{ $this->totalAbsents }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <span class="text-sm text-blue-700 dark:text-blue-400">Restant à saisir</span>
                    <span class="font-semibold text-blue-800 dark:text-blue-300">{{ max(0, $totalManchettesPresentes - $progressManchettes) }}</span>
                </div>
            </div>
        </div>

        <!-- Historique des saisies -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Dernières saisies
            </h3>
            
            @if(!empty($manchettesSaisies))
                <div class="space-y-2 max-h-80 overflow-y-auto custom-scrollbar">
                    @foreach(array_slice($manchettesSaisies, 0, 10) as $manchette)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-blue-600 dark:text-blue-400 font-medium text-xs">
                                            {{ $loop->iteration }}
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $manchette['code_anonymat']['code_complet'] ?? '' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ $manchette['etudiant']['nom'] ?? '' }} {{ $manchette['etudiant']['prenom'] ?? '' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <button wire:click="supprimerManchette({{ $manchette['id'] }})" 
                                class="ml-3 p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                onclick="return confirm('Supprimer cette manchette et sa copie associée ?')"
                                title="Supprimer">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zM6 5v6h8V5H6z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Aucune manchette saisie
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        Les manchettes apparaîtront ici au fur et à mesure
                    </p>
                </div>
            @endif
        </div>

        <!-- Informations contextuelles -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Contexte de l'examen
            </h3>
            
            <div class="space-y-3 text-sm">
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Niveau:</span>
                    <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $niveauSelected->abr }} - {{ $niveauSelected->nom }}</span>
                </div>
                @if($parcoursSelected)
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Parcours:</span>
                        <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $parcoursSelected->abr }} - {{ $parcoursSelected->nom }}</span>
                    </div>
                @endif
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Matière:</span>
                    <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $ecSelected->nom }} ({{ $ecSelected->abr }})</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Code salle:</span>
                    <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $codeSalle }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Session:</span>
                    <span class="text-gray-600 dark:text-gray-400 ml-2">{{ ucfirst($sessionType) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>