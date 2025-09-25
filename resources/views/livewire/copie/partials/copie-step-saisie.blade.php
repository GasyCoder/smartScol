<!-- Saisie des Notes - Version compacte -->
<div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
    <!-- En-tête -->
    <div class="px-5 py-3 bg-gradient-to-r from-primary-600 to-primary-700 dark:from-primary-700 dark:to-primary-800">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <h2 class="text-lg font-bold font-heading text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Saisie des Notes
                    @if($ecSelected)
                        <span class="text-primary-200 text-sm font-body">{{ $ecSelected->nom }}</span>
                    @endif
                </h2>
                <div class="flex items-center gap-3 mt-1 text-xs font-body text-primary-100">
                    @if($niveauSelected)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                            </svg>
                            {{ $niveauSelected->abr }}
                        </span>
                    @endif
                    @if($parcoursSelected)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ $parcoursSelected->abr }}
                        </span>
                    @endif
                    <span class="flex items-center gap-1 bg-primary-500/30 px-2 py-0.5 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        Code: <strong class="font-heading">{{ $this->codeSalle }}</strong>
                    </span>
                </div>
            </div>
            <button wire:click="backToStep('ec')" 
                    class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-all duration-200 flex items-center gap-1.5 border border-white/20 font-body text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Changer EC
            </button>
        </div>
    </div>

    <!-- Formulaire de saisie -->
    <div class="p-5">
        @if($this->copiesRestantes <= 0 && $totalCopies > 0)
            <!-- Message succès complet -->
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border-2 border-green-500 dark:border-green-700 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold font-heading text-green-800 dark:text-green-300">
                            Saisie terminée !
                        </h3>
                        <p class="text-sm font-body text-green-700 dark:text-green-400 mt-0.5">
                            Toutes les notes ont été saisies avec succès. Aucune saisie disponible.
                        </p>
                    </div>
                </div>
            </div>

        @elseif($totalCopies == 0)
            <!-- Message aucune copie -->
            <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-500 dark:border-yellow-700 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold font-heading text-yellow-800 dark:text-yellow-300">
                            Aucune copie disponible
                        </h3>
                        <p class="text-sm font-body text-yellow-700 dark:text-yellow-400 mt-0.5">
                            Aucune manchette n'a été créée pour cette EC.
                        </p>
                    </div>
                </div>
            </div>

        @else
            <!-- Formulaire actif -->
            <div class="max-w-4xl mx-auto">
                <!-- Barre de progression -->
                @if($totalCopies > 0)
                    <div class="mb-4 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-primary-600 dark:bg-primary-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm font-semibold font-heading text-primary-900 dark:text-primary-100">Progression</span>
                                    <p class="text-xxs font-body text-primary-600 dark:text-primary-300">{{ $progressCount }} / {{ $totalCopies }} copies</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold font-heading text-primary-600 dark:text-primary-400">{{ number_format($this->pourcentageProgression, 1) }}%</span>
                            </div>
                        </div>
                        <div class="relative w-full bg-primary-200 dark:bg-primary-800 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-primary-600 dark:bg-primary-500 h-full rounded-full transition-all duration-500" 
                                 style="width: {{ $this->pourcentageProgression }}%"></div>
                        </div>
                        <div class="mt-1 flex justify-between text-xxs font-body text-primary-600 dark:text-primary-400">
                            <span>{{ $progressCount }} saisies</span>
                            <span>{{ $this->copiesRestantes }} restantes</span>
                        </div>
                    </div>
                @endif

                <!-- ÉTAPE 1: Champ d'identification -->
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700 mb-4">
                    @if($is_active)
                        <!-- Mode Matricule -->
                        <div class="space-y-3">
                            <label for="matricule" class="flex items-center gap-1.5 text-sm font-semibold font-heading text-gray-900 dark:text-white">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                                Matricule de l'étudiant
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="matricule"
                                    wire:model.live="matricule"
                                    placeholder="Saisir le matricule..."
                                    class="w-full px-4 py-2.5 text-base font-medium font-body rounded-lg border-2 transition-all duration-200
                                    @if($etudiantTrouve && $codeAnonymatCourant && !$noteDejaExiste) 
                                        border-green-500 bg-green-50 dark:bg-green-900/20
                                    @elseif($noteDejaExiste) 
                                        border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20
                                    @elseif($matricule && strlen($matricule) >= 3 && !$etudiantTrouve) 
                                        border-red-500 bg-red-50 dark:bg-red-900/20
                                    @else 
                                        border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-2 focus:ring-primary-500
                                    @endif
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                    placeholder:text-gray-400"/>
                                
                                <!-- Icône de statut -->
                                @if($etudiantTrouve && $codeAnonymatCourant && !$noteDejaExiste)
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @elseif($noteDejaExiste || ($matricule && strlen($matricule) >= 3 && !$etudiantTrouve))
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            @error('matricule')
                                <p class="mt-1.5 text-xs font-body text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror

                            <!-- Messages de statut matricule -->
                            @if($noteDejaExiste && $codeAnonymatCourant)
                                <div class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 border-l-3 border-yellow-500 rounded-r-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-bold font-heading text-yellow-900 dark:text-yellow-300">Matricule déjà existant</h4>
                                            <p class="text-xs font-body text-yellow-800 dark:text-yellow-400 mt-0.5">
                                                @if($etudiantTrouve){{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenom }} - @endif
                                                Code: <strong>{{ $codeAnonymatCourant->code_complet }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($etudiantTrouve && !$codeAnonymatCourant && !$noteDejaExiste)
                                <div class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border-l-3 border-red-500 rounded-r-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-bold font-heading text-red-900 dark:text-red-300">Matricule sans manchette</h4>
                                            <p class="text-xs font-body text-red-800 dark:text-red-400 mt-0.5">{{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenom }}</p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($matricule && strlen($matricule) >= 3 && !$etudiantTrouve && !$noteDejaExiste)
                                <div class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border-l-3 border-red-500 rounded-r-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-bold font-heading text-red-900 dark:text-red-300">Matricule non valide</h4>
                                            <p class="text-xs font-body text-red-800 dark:text-red-400 mt-0.5">
                                                Le matricule "{{ $matricule }}" n'est pas trouvé pour ce niveau
                                                @if($parcoursSelected) / {{ $parcoursSelected->abr }}@endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($etudiantTrouve && $codeAnonymatCourant && !$noteDejaExiste)
                                <div class="mt-2 p-3 bg-green-50 dark:bg-green-900/20 border-l-3 border-green-500 rounded-r-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <h4 class="text-sm font-bold font-heading text-green-900 dark:text-green-300">
                                                    {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenom }}
                                                </h4>
                                                <span class="px-2 py-0.5 bg-green-500 text-white text-xxs font-bold font-body rounded-full">✓ Prêt</span>
                                            </div>
                                            <p class="text-xs font-body text-green-800 dark:text-green-400 mt-0.5">
                                                Code: <strong>{{ $codeAnonymatCourant->code_complet }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                    @else
                        <!-- Mode Code Anonymat (même structure compacte) -->
                        <div class="space-y-3">
                            <label for="codeAnonymat" class="flex items-center gap-1.5 text-sm font-semibold font-heading text-gray-900 dark:text-white">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                                Code Anonymat
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="codeAnonymat"
                                    wire:model.live="codeAnonymat"
                                    placeholder="Ex: TA1, SB15..."
                                    style="text-transform: uppercase"
                                    class="w-full px-4 py-2.5 text-base font-medium font-body rounded-lg border-2 transition-all duration-200 
                                    @if($codeAnonymatCourant && !$noteDejaExiste) 
                                        border-green-500 bg-green-50 dark:bg-green-900/20
                                    @elseif($noteDejaExiste) 
                                        border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20
                                    @elseif($codeAnonymat && strlen($codeAnonymat) >= 2 && !$codeAnonymatCourant) 
                                        border-red-500 bg-red-50 dark:bg-red-900/20
                                    @else 
                                        border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-2 focus:ring-primary-500
                                    @endif
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                    placeholder:text-gray-400"/>
                                
                                @if($codeAnonymatCourant && $manchetteCorrespondante && !$noteDejaExiste)
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @elseif($noteDejaExiste || ($codeAnonymat && strlen($codeAnonymat) >= 2 && !$codeAnonymatCourant))
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            @error('codeAnonymat')
                                <p class="mt-1.5 text-xs font-body text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror

                            <!-- Messages code anonymat (même structure que matricule) -->
                            @if($noteDejaExiste && $codeAnonymatCourant)
                                <div class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 border-l-3 border-yellow-500 rounded-r-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-bold font-heading text-yellow-900 dark:text-yellow-300">Code anonymat déjà existant</h4>
                                            <p class="text-xs font-body text-yellow-800 dark:text-yellow-400 mt-0.5">
                                                Code: <strong>{{ $codeAnonymatCourant->code_complet }}</strong>
                                                @if($etudiantTrouve) - {{ $etudiantTrouve->matricule }}@endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($codeAnonymatCourant && !$manchetteCorrespondante && !$noteDejaExiste)
                                <div class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border-l-3 border-red-500 rounded-r-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-bold font-heading text-red-900 dark:text-red-300">Code anonymat sans manchette</h4>
                                            <p class="text-xs font-body text-red-800 dark:text-red-400 mt-0.5">Code: {{ $codeAnonymatCourant->code_complet }}</p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($codeAnonymat && strlen($codeAnonymat) >= 2 && !$codeAnonymatCourant && !$noteDejaExiste)
                                <div class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border-l-3 border-red-500 rounded-r-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-bold font-heading text-red-900 dark:text-red-300">Code anonymat non valide</h4>
                                            <p class="text-xs font-body text-red-800 dark:text-red-400 mt-0.5">
                                                Le code "{{ strtoupper($codeAnonymat) }}" n'est pas trouvé.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @elseif($codeAnonymatCourant && $manchetteCorrespondante && !$noteDejaExiste)
                                <div class="mt-2 p-3 bg-green-50 dark:bg-green-900/20 border-l-3 border-green-500 rounded-r-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <h4 class="text-sm font-bold font-heading text-green-900 dark:text-green-300">Code {{ $codeAnonymatCourant->code_complet }}</h4>
                                                <span class="px-2 py-0.5 bg-green-500 text-white text-xxs font-bold font-body rounded-full">✓ Prêt</span>
                                            </div>
                                            <p class="text-xxs font-body text-green-600 dark:text-green-500 mt-0.5">Aucune note enregistrée - Prêt pour la saisie</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- ÉTAPE 2: Champ Note -->
                @if($afficherChampNote && !$noteDejaExiste)
                    <div class="bg-primary-50 dark:bg-primary-900/20 p-4 rounded-lg border-2 border-primary-300 dark:border-primary-700">
                        <label for="note" class="flex items-center gap-1.5 text-sm font-semibold font-heading text-gray-900 dark:text-white mb-3">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Note sur 20
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
                                class="flex-1 px-4 py-2.5 text-lg font-bold font-heading rounded-lg border-2 transition-all duration-200
                                    @error('note') border-red-500 bg-red-50 dark:bg-red-900/20
                                    @else border-primary-300 dark:border-primary-600 bg-white dark:bg-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500 @endif
                                    text-gray-900 dark:text-white"
                            />
                            <button 
                                type="button"
                                wire:click="sauvegarderCopie"
                                @if(!$this->boutonActive) disabled @endif
                                class="px-6 py-2.5 rounded-lg font-bold font-heading text-sm transition-all duration-200
                                    {{ $this->boutonActive 
                                        ? 'bg-green-500 hover:bg-green-600 text-white shadow-lg' 
                                        : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed opacity-60' }}">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Enregistrer
                                </span>
                            </button>
                        </div>
                        @error('note')
                            <p class="mt-2 text-xs font-body text-red-600 dark:text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror

                        @if($peutEnregistrer)
                            <div class="mt-2 flex items-center gap-1.5 text-xs font-body text-primary-700 dark:text-primary-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Appuyez sur <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 rounded border border-primary-300 font-mono text-xxs">Entrée</kbd> pour enregistrer
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Statistiques -->
    @if($progressCount > 0 || $totalCopies > 0)
        <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <h3 class="text-base font-bold font-heading text-gray-900 dark:text-white mb-3 flex items-center gap-1.5">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                @if($this->copiesRestantes <= 0 && $totalCopies > 0)
                    <span class="text-green-600 dark:text-green-400">Saisie terminée</span>
                @else
                    Statistiques
                @endif
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-primary-200 dark:border-primary-800">
                    <div class="text-center">
                         <div class="text-xs text-gray-600 dark:text-gray-400 font-medium font-body mt-0.5">Code salle</div>
                        <div class="text-xl font-bold font-heading text-primary-600 dark:text-primary-400">{{ $this->codeSalle }}</div>
                        @if($ecSelected && !empty($ecSelected->enseignant))
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-bold mt-1 truncate">{{ $ecSelected->enseignant }}</div>
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="text-center">
                        <div class="text-xl font-bold font-heading text-green-600 dark:text-green-400">{{ $totalCopies }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 font-medium font-body mt-0.5">Présents</div>
                        <div class="text-xxs text-red-600 dark:text-red-400 mt-1 font-semibold font-body">{{ $this->totalAbsents }} absents</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-slate-200 dark:border-slate-800">
                    <div class="text-center">
                        <div class="text-xl font-bold font-heading text-slate-600 dark:text-slate-400">{{ $progressCount }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 font-medium font-body mt-0.5">Saisies</div>
                        <div class="text-xxs text-yellow-600 dark:text-yellow-400 mt-1 font-semibold font-body">{{ $this->copiesRestantes }} restantes</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-cyan-200 dark:border-cyan-800">
                    <div class="text-center">
                        <div class="text-xl font-bold font-heading {{ $this->copiesRestantes <= 0 && $totalCopies > 0 ? 'text-green-600 dark:text-green-400' : 'text-cyan-600 dark:text-cyan-400' }}">
                            {{ round($this->pourcentageProgression, 1) }}%
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 font-medium font-body mt-0.5">Complété</div>
                        @if($moyenneGenerale > 0)
                            <div class="text-xxs text-gray-600 dark:text-gray-400 mt-1 font-semibold font-body">Moy: {{ $moyenneGenerale }}/20</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
@keyframes fadeIn {
    from { 
        opacity: 0; 
        transform: translateY(-10px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

.animate-fadeIn {
    animation: fadeIn 0.4s ease-out;
}
</style>
@endpush
