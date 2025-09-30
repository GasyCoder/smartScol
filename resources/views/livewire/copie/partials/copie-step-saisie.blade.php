<!-- Saisie des Notes - Version complète avec remplissage automatique -->
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
                                            <h4 class="text-sm font-bold font-heading text-yellow-900 dark:text-yellow-300">Note déjà saisie</h4>
                                            <p class="text-xs font-body text-yellow-800 dark:text-yellow-400 mt-0.5">
                                                @if($etudiantTrouve){{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenoms }} - @endif
                                                Code: <strong>{{ $codeAnonymatCourant->code_complet }}</strong> - Note: <strong>{{ $noteExistante }}/20</strong>
                                            </p>
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
                                            <h4 class="text-sm font-bold font-heading text-red-900 dark:text-red-300">Matricule introuvable</h4>
                                            <p class="text-xs font-body text-red-800 dark:text-red-400 mt-0.5">
                                                Le matricule "{{ $matricule }}" n'a pas de manchette pour cette EC
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
                                                    {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenoms }}
                                                </h4>
                                                <span class="px-2 py-0.5 bg-green-500 text-white text-xxs font-bold font-body rounded-full">Prêt</span>
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
                        <!-- Mode Code Anonymat -->
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

                            <!-- Messages code anonymat -->
                            @if($noteDejaExiste && $codeAnonymatCourant)
                                <div class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 border-l-3 border-yellow-500 rounded-r-lg">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-bold font-heading text-yellow-900 dark:text-yellow-300">Note déjà saisie</h4>
                                            <p class="text-xs font-body text-yellow-800 dark:text-yellow-400 mt-0.5">
                                                Code: <strong>{{ $codeAnonymatCourant->code_complet }}</strong> - Note: <strong>{{ $noteExistante }}/20</strong>
                                            </p>
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
                                            <h4 class="text-sm font-bold font-heading text-red-900 dark:text-red-300">Code introuvable</h4>
                                            <p class="text-xs font-body text-red-800 dark:text-red-400 mt-0.5">
                                                Le code "{{ strtoupper($codeAnonymat) }}" n'existe pas pour cette EC
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
                                                <span class="px-2 py-0.5 bg-green-500 text-white text-xxs font-bold font-body rounded-full">Prêt</span>
                                            </div>
                                            <p class="text-xxs font-body text-green-600 dark:text-green-500 mt-0.5">Prêt pour la saisie</p>
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
<!-- Statistiques avec toggle et bouton de synchronisation -->
@if($progressCount > 0 || $totalCopies > 0)
    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <!-- En-tête avec toggle de synchronisation -->
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold font-heading text-gray-900 dark:text-white flex items-center gap-1.5">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                @if($this->copiesRestantes <= 0 && $totalCopies > 0)
                    <span class="text-green-600 dark:text-green-400">Saisie terminée</span>
                @else
                    Statistiques
                @endif
            </h3>
            <!-- Toggle Switch pour activer le mode synchronisation -->
            @if($this->copiesRestantes > 0)
                <div class="flex items-center gap-3">
                    <label class="flex items-center cursor-pointer group">
                        <span class="mr-3 text-sm font-semibold font-body text-gray-700 dark:text-gray-300 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                            Copies non remises
                        </span>
                        
                        <!-- Toggle Switch -->
                        <div class="relative">
                            <input 
                                type="checkbox" 
                                {{ $modeSync ? 'checked' : '' }}
                                wire:click="toggleModeSync"
                                class="sr-only peer" 
                                id="toggleModeSync"
                            />
                            <div class="w-14 h-7 bg-gray-300 dark:bg-gray-600 rounded-full peer 
                                        peer-checked:bg-gradient-to-r peer-checked:from-orange-500 peer-checked:to-red-500 
                                        peer-focus:ring-4 peer-focus:ring-orange-300 dark:peer-focus:ring-orange-800
                                        transition-all duration-300 shadow-inner">
                            </div>
                            <div class="absolute top-0.5 left-0.5 w-6 h-6 bg-white rounded-full 
                                        {{ $modeSync ? 'translate-x-7' : '' }}
                                        transition-all duration-300 
                                        shadow-md flex items-center justify-center">
                                <svg wire:loading.remove wire:target="toggleModeSync" 
                                    class="w-3 h-3 {{ $modeSync ? 'text-orange-500' : 'text-gray-400' }} transition-colors" 
                                    fill="currentColor" viewBox="0 0 20 20">
                                    @if($modeSync)
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    @else
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    @endif
                                </svg>
                                <svg wire:loading wire:target="toggleModeSync" 
                                    class="w-3 h-3 text-orange-500 animate-spin" 
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <span class="ml-3 text-xs font-medium font-body {{ $modeSync ? 'text-orange-600 dark:text-orange-400' : 'text-gray-500 dark:text-gray-500' }} transition-colors">
                            {{ $modeSync ? 'Activé' : 'Désactivé' }}
                        </span>
                    </label>
                </div>
            @endif
        </div>

        <!-- Bouton de synchronisation (affiché seulement si le toggle est activé) -->
        @if($modeSync && $this->copiesRestantes > 0 && !empty($copiesManquantes))
            <div class="mb-4 p-4 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 border-2 border-orange-300 dark:border-orange-700 rounded-lg animate-fadeIn">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center animate-pulse">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold font-heading text-gray-900 dark:text-white">
                                {{ count($copiesManquantes) }} copie(s) non remise(s) détectée(s)
                            </h4>
                            <p class="text-sm font-body text-gray-600 dark:text-gray-400">
                                Cliquez pour créer automatiquement avec la note 0/20
                            </p>
                        </div>
                    </div>
                    
                    <button 
                        type="button"
                        wire:click="creerCopiesManquantes"
                        wire:loading.attr="disabled"
                        wire:target="creerCopiesManquantes"
                        class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg font-bold font-body text-sm shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2 whitespace-nowrap
                            {{ $enCoursRemplissage ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <svg wire:loading.remove wire:target="creerCopiesManquantes" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <svg wire:loading wire:target="creerCopiesManquantes" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="creerCopiesManquantes">
                            Synchroniser maintenant
                        </span>
                        <span wire:loading wire:target="creerCopiesManquantes">
                            Synchronisation...
                        </span>
                    </button>
                </div>
            </div>
        @elseif($modeSync && $this->copiesRestantes > 0 && empty($copiesManquantes))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border-2 border-green-300 dark:border-green-700 rounded-lg animate-fadeIn">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold font-heading text-green-900 dark:text-green-300">
                            Aucune copie non remise
                        </h4>
                        <p class="text-sm font-body text-green-700 dark:text-green-400">
                            Toutes les copies présentes ont été saisies
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Intervalle des codes anonymat -->
        @if($this->intervalleCodes['min'] && $this->intervalleCodes['max'])
            <div class="mb-3 p-2.5 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                <div class="flex items-center justify-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                    </svg>
                    <span class="text-blue-800 dark:text-blue-300 font-medium font-body">
                        Codes disponibles : 
                        <span class="font-mono font-bold text-blue-600 dark:text-blue-400">
                            {{ $this->intervalleCodes['min'] }}
                        </span>
                        @if($this->intervalleCodes['min'] !== $this->intervalleCodes['max'])
                            <span class="text-blue-500 mx-1">→</span>
                            <span class="font-mono font-bold text-blue-600 dark:text-blue-400">
                                {{ $this->intervalleCodes['max'] }}
                            </span>
                        @endif
                        <span class="text-blue-600 dark:text-blue-400 ml-1">({{ $this->intervalleCodes['total'] }} codes)</span>
                    </span>
                </div>
            </div>
        @endif
        
        <!-- Cartes statistiques (reste identique) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-primary-200 dark:border-primary-800">
                <div class="text-center">
                    <div class="text-xs text-gray-600 dark:text-gray-400 font-medium font-body mb-1">Code salle</div>
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

    <!-- Section Remplissage Automatique -->
    @if($progressCount > 0 && $this->copiesRestantes > 0)
        <div class="px-5 py-4 border-t-2 border-gray-300 dark:border-gray-600 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-bold font-heading text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Remplissage automatique
                </h3>
                
                <button 
                    type="button"
                    wire:click="toggleRemplissageAuto"
                    class="px-4 py-2 rounded-lg font-semibold font-body text-sm transition-all duration-200 flex items-center gap-2
                        {{ $afficherRemplissageAuto 
                            ? 'bg-gray-200 hover:bg-gray-300 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200' 
                            : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg hover:shadow-xl' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($afficherRemplissageAuto)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        @endif
                    </svg>
                    {{ $afficherRemplissageAuto ? 'Annuler' : 'Gérer les copies non remises' }}
                </button>
            </div>

            <p class="text-sm font-body text-gray-600 dark:text-gray-400 mb-3">
                <svg class="w-4 h-4 inline mr-1 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                Créez automatiquement les copies non remises avec la note 0/20 pour accélérer la saisie.
            </p>

            <!-- Panneau de remplissage automatique -->
            @if($afficherRemplissageAuto)
                <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg border-2 border-indigo-300 dark:border-indigo-700 shadow-xl animate-fadeIn">
                    <div class="p-4">
                        <!-- En-tête avec statistiques -->
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold font-heading text-gray-900 dark:text-white">
                                        Analyse des copies
                                    </h4>
                                    <p class="text-xs font-body text-gray-500 dark:text-gray-400">
                                        {{ count($copiesManquantes) }} copie(s) manquante(s) sur {{ $totalCopies }} présent(s)
                                    </p>
                                </div>
                            </div>
                            
                            @if(!empty($copiesManquantes))
                                <button 
                                    type="button"
                                    wire:click="analyserCopiesManquantes"
                                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-semibold font-body transition-all duration-200">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Actualiser
                                </button>
                            @endif
                        </div>

                        @if(empty($copiesManquantes))
                            <!-- Aucune copie manquante -->
                            <div class="py-8 text-center">
                                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h5 class="text-lg font-bold font-heading text-gray-900 dark:text-white mb-1">
                                    Aucune copie manquante
                                </h5>
                                <p class="text-sm font-body text-gray-600 dark:text-gray-400">
                                    Toutes les copies ont déjà été saisies pour cette EC.
                                </p>
                            </div>
                        @else
                            <!-- Liste des copies manquantes -->
                            <div class="space-y-3">
                                <!-- Résumé -->
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <h5 class="text-sm font-bold font-heading text-yellow-900 dark:text-yellow-300">
                                                {{ count($copiesManquantes) }} copie(s) seront créées
                                            </h5>
                                            <p class="text-xs font-body text-yellow-800 dark:text-yellow-400 mt-1">
                                                Chaque copie recevra automatiquement la note <strong>0/20</strong> avec le commentaire "Copie non remise".
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tableau des copies -->
                                <div class="max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-bold font-heading text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    Code
                                                </th>
                                                <th class="px-3 py-2 text-left text-xs font-bold font-heading text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    Matricule
                                                </th>
                                                <th class="px-3 py-2 text-left text-xs font-bold font-heading text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    Étudiant
                                                </th>
                                                <th class="px-3 py-2 text-center text-xs font-bold font-heading text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    Note
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($copiesManquantes as $copie)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                    <td class="px-3 py-2 whitespace-nowrap">
                                                        <span class="text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                            {{ $copie['code_complet'] }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 whitespace-nowrap">
                                                        <span class="text-sm font-body text-gray-900 dark:text-gray-100">
                                                            {{ $copie['matricule'] }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <span class="text-sm font-body text-gray-900 dark:text-gray-100">
                                                            {{ $copie['nom_complet'] }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold font-body bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                            0/20
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <button 
                                        type="button"
                                        wire:click="annulerRemplissageAuto"
                                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg font-semibold font-body text-sm transition-all duration-200">
                                        Annuler
                                    </button>
                                    
                                    <button 
                                        type="button"
                                        wire:click="creerCopiesManquantes"
                                        wire:loading.attr="disabled"
                                        wire:target="creerCopiesManquantes"
                                        class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-bold font-body text-sm shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2
                                            {{ $enCoursRemplissage ? 'opacity-50 cursor-not-allowed' : '' }}">
                                        <svg wire:loading.remove wire:target="creerCopiesManquantes" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <svg wire:loading wire:target="creerCopiesManquantes" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span wire:loading.remove wire:target="creerCopiesManquantes">
                                            Créer {{ count($copiesManquantes) }} copie(s) avec note 0
                                        </span>
                                        <span wire:loading wire:target="creerCopiesManquantes">
                                            Création en cours...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
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