<!-- ÉTAPE 1: Champ d'identification (Matricule OU Code Anonymat) -->
@if($is_active)
    <!-- Mode avec vérification matricule -->
    <div class="mb-6">
        <label for="matricule" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Matricule de l'étudiant
        </label>
        <input 
            type="text" 
            id="matricule"
            wire:model.live="matricule"
            placeholder="Saisir le matricule..."
            class="w-full px-4 py-3 text-lg rounded-lg border transition-colors
            @if($etudiantTrouve && $codeAnonymatCourant && !$noteDejaExiste) border-green-500 bg-green-50 dark:bg-green-900/20
            @elseif($noteDejaExiste) border-orange-500 bg-orange-50 dark:bg-orange-900/20
            @elseif($matricule && strlen($matricule) >= 3 && !$etudiantTrouve) border-red-500 bg-red-50 dark:bg-red-900/20
            @else border-gray-300 dark:border-gray-600 @endif
            bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
            focus:ring-2 focus:ring-blue-500 focus:border-blue-500"/>
        
        @error('matricule')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror

        <!-- CAS 1: Note déjà saisie -->
        @if($noteDejaExiste && $codeAnonymatCourant)
            <div class="mt-2 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-orange-800 dark:text-orange-300">
                            Matricule déjà existant
                        </h4>
                        <p class="text-sm text-orange-700 dark:text-orange-400 mt-1">
                            @if($etudiantTrouve)
                                {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenoms }} - 
                            @endif
                            Code: <span class="font-semibold">{{ $codeAnonymatCourant->code_complet }}</span>
                        </p>
                        <p class="text-xs text-orange-600 dark:text-orange-500 mt-1">
                            Ce matricule a déjà une note saisie. Choisissez un autre matricule.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- CAS 2: Étudiant trouvé MAIS pas de manchette -->
        @if($etudiantTrouve && !$codeAnonymatCourant && !$noteDejaExiste)
            <div class="mt-2 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-red-800 dark:text-red-300">
                            Matricule sans manchette
                        </h4>
                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                            {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenoms }}
                        </p>
                        <p class="text-xs text-red-600 dark:text-red-500 mt-1">
                            Ce matricule n'a pas encore de manchette pour cette matière. Contactez l'administration.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- CAS 3: Matricule non trouvé (pas dans ce niveau/parcours) -->
        @if($matricule && strlen($matricule) >= 3 && !$etudiantTrouve && !$noteDejaExiste)
            <div class="mt-2 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-red-800 dark:text-red-300">
                            Matricule non valide
                        </h4>
                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                            Le matricule "{{ $matricule }}" n'est pas trouvé.
                        </p>
                        <p class="text-xs text-red-600 dark:text-red-500 mt-1">
                            Ce matricule n'est pas validé pour ce niveau
                            @if($parcoursSelected) / {{ $parcoursSelected->abr }}@endif
                            ou n'existe pas dans le système.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- CAS 4: Étudiant trouvé ET prêt à noter (SUCCESS) -->
        @if($etudiantTrouve && $codeAnonymatCourant && !$noteDejaExiste)
            <div class="mt-2 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="flex justify-between items-start">
                            <h4 class="text-sm font-semibold text-green-800 dark:text-green-300">
                                {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenoms }}
                            </h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-300">
                                Prêt
                            </span>
                        </div>
                        <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                            Code: <span class="font-semibold">{{ $codeAnonymatCourant->code_complet }}</span>
                        </p>
                        <p class="text-xs text-green-600 dark:text-green-500 mt-1">
                            Aucune note enregistrée - Prêt pour la saisie
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@else
    <!-- Mode saisie directe par code anonymat -->
    <div class="mb-6">
        <label for="codeAnonymat" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Code Anonymat
        </label>
        <input 
            type="text" 
            id="codeAnonymat"
            wire:model.live="codeAnonymat"
            placeholder="Ex: TA1, SB15..."
            style="text-transform: uppercase"
            class="w-full px-4 py-3 text-lg rounded-lg border transition-colors
            @if($codeAnonymatCourant && !$noteDejaExiste) border-green-500 bg-green-50 dark:bg-green-900/20
            @elseif($noteDejaExiste) border-orange-500 bg-orange-50 dark:bg-orange-900/20
            @elseif($codeAnonymat && strlen($codeAnonymat) >= 2 && !$codeAnonymatCourant) border-red-500 bg-red-50 dark:bg-red-900/20
            @else border-gray-300 dark:border-gray-600 @endif
            bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
            focus:ring-2 focus:ring-blue-500 focus:border-blue-500"/>
        
        @error('codeAnonymat')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror

        <!-- CAS 1: Code avec note déjà saisie -->
        @if($noteDejaExiste && $codeAnonymatCourant)
            <div class="mt-2 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-orange-800 dark:text-orange-300">
                            Code anonymat déjà existant
                        </h4>
                        <p class="text-sm text-orange-700 dark:text-orange-400 mt-1">
                            Code: <span class="font-semibold">{{ $codeAnonymatCourant->code_complet }}</span>
                            @if($etudiantTrouve) - {{ $etudiantTrouve->matricule }}@endif
                        </p>
                        <p class="text-xs text-orange-600 dark:text-orange-500 mt-1">
                            Ce code a déjà une note saisie. Choisissez un autre code.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- CAS 2: Code trouvé MAIS pas de manchette -->
        @if($codeAnonymatCourant && !$manchetteCorrespondante && !$noteDejaExiste)
            <div class="mt-2 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-red-800 dark:text-red-300">
                            Code anonymat sans manchette
                        </h4>
                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                            Code: {{ $codeAnonymatCourant->code_complet }}
                        </p>
                        <p class="text-xs text-red-600 dark:text-red-500 mt-1">
                            Ce code n'a pas encore de manchette associée. Contactez l'administration.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- CAS 3: Code non trouvé ou invalide -->
        @if($codeAnonymat && strlen($codeAnonymat) >= 2 && !$codeAnonymatCourant && !$noteDejaExiste)
            <div class="mt-2 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-semibold text-red-800 dark:text-red-300">
                            Code anonymat non valide
                        </h4>
                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                            Le code "{{ strtoupper($codeAnonymat) }}" n'est pas trouvé.
                        </p>
                        <p class="text-xs text-red-600 dark:text-red-500 mt-1">
                            Ce code n'est pas validé pour cette matière. Veuillez réessayer.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- CAS 4: Code trouvé ET prêt à noter (SUCCESS) -->
        @if($codeAnonymatCourant && $manchetteCorrespondante && !$noteDejaExiste)
            <div class="mt-2 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="flex justify-between items-start">
                            <h4 class="text-sm font-semibold text-green-800 dark:text-green-300">
                                Code {{ $codeAnonymatCourant->code_complet }}
                            </h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-300">
                                Prêt
                            </span>
                        </div>
                        @if($etudiantTrouve)
                            <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                                {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenoms }} - {{ $etudiantTrouve->matricule }}
                            </p>
                        @endif
                        <p class="text-xs text-green-600 dark:text-green-500 mt-1">
                            Aucune note enregistrée - Prêt pour la saisie
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif

<!-- ÉTAPE 2: Champ Note (avec bouton désactivé par défaut) -->
@if($afficherChampNote && !$noteDejaExiste)
    <div class="mb-6 animate-fadeIn">
        <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Note (/20)
        </label>
        <div class="flex items-center gap-3">
            <input 
                type="number" 
                id="note"
                wire:model.live="note"
                placeholder="0.00"
                min="0" 
                max="20" 
                step="0.25"
                class="flex-1 px-4 py-3 text-lg rounded-lg border transition-colors
                    @error('note') border-red-500 bg-red-50 dark:bg-red-900/20 
                    @else border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 @endif
                    text-gray-900 dark:text-gray-100
                    focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <button 
                type="button"
                wire:click="sauvegarderCopie"
                @if(!$this->boutonActive) disabled @endif
                class="px-6 py-3 rounded-lg font-semibold text-white transition-all duration-200
                    {{ $this->boutonActive 
                        ? 'bg-green-600 hover:bg-green-700 shadow-lg transform hover:scale-105 cursor-pointer' 
                        : 'bg-gray-400 cursor-not-allowed opacity-60' }}">
                Enregistrer
            </button>
        </div>
        @error('note')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    @if($peutEnregistrer)
        <p class="mt-2 text-sm text-green-600 dark:text-green-400">
            Appuyez sur <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded border">Entrée</kbd> pour enregistrer rapidement
        </p>
    @endif
@endif