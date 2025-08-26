<!-- Saisie des Notes -->
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
    <!-- En-tête -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Saisie des Notes@if($ecSelected) - {{ $ecSelected->nom }}@endif
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    @if($niveauSelected){{ $niveauSelected->abr }}@endif
                    @if($parcoursSelected) / {{ $parcoursSelected->abr }}@endif
                    - Code salle: {{ $this->codeSalle }}
                </p>
            </div>
            <button wire:click="backToStep('ec')" 
                    class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 border rounded-lg">
                ← Changer de matière
            </button>
        </div>
    </div>

<!-- Formulaire de saisie -->
    <div class="p-6 saisie-form">
        <!-- Message si terminé -->
        @if($this->copiesRestantes <= 0 && $totalCopies > 0)
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-8 w-8 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-green-800 dark:text-green-300">
                            🎉 Saisie terminée !
                        </h3>
                        <p class="text-sm text-green-700 dark:text-green-400">
                            Toutes les notes ont été saisies avec succès. Aucune saisie disponible.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($totalCopies == 0)
            <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-8 w-8 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-300">
                            Aucune copie disponible
                        </h3>
                        <p class="text-sm text-yellow-700 dark:text-yellow-400">
                            Aucune manchette n'a été créée pour cette matière.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <!-- Formulaire actif -->
            <div class="max-w-2xl">
                <!-- Barre de progression -->
                @if($totalCopies > 0)
                    <div class="mb-6 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Progression</span>
                            <span class="text-sm text-blue-600 dark:text-blue-400">{{ $progressCount }}/{{ $totalCopies }} copies</span>
                        </div>
                        <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $this->pourcentageProgression }}%"></div>
                        </div>
                    </div>
                @endif

                <!-- ÉTAPE 1: Champ Matricule -->
                <div class="mb-6">
                    <label for="matricule" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Matricule de l'étudiant
                    </label>
                    <input 
                        type="text" 
                        id="matricule"
                        wire:model.live.debounce.500ms="matricule"
                        placeholder="Saisir le matricule..."
                        class="w-full px-4 py-3 text-lg rounded-lg border transition-colors
                               @if($etudiantTrouve && !$noteDejaExiste) border-green-500 bg-green-50 dark:bg-green-900/20
                               @elseif($etudiantTrouve && $noteDejaExiste) border-orange-500 bg-orange-50 dark:bg-orange-900/20
                               @elseif($matricule && strlen($matricule) >= 3 && !$etudiantTrouve) border-red-500 bg-red-50 dark:bg-red-900/20
                               @else border-gray-300 dark:border-gray-600 @endif
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    @error('matricule')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <!-- CAS 1: Étudiant trouvé ET note déjà saisie -->
                    @if($etudiantTrouve && $noteDejaExiste)
                        <div class="mt-2 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                   ⚠️
                                </div>

                                <div class="ml-3 flex-1">
                                    <div class="flex justify-between items-start">
                                        <h4 class="text-sm font-medium text-orange-800 dark:text-orange-300">
                                            {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenom }}
                                        </h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-800/30 dark:text-orange-300">
                                            Déjà noté
                                        </span>
                                    </div>

                                    <div class="mt-2 text-sm text-orange-700 dark:text-orange-400">
                                        @if($codeAnonymatCourant)
                                            <p class="mb-1">
                                                <span class="font-semibold">Matricule:</span> 
                                                <span class="font-semibold">{{ $etudiantTrouve->matricule }}</span>
                                            </p>
                                        @endif
                                        <p class="text-xs italic mt-1 text-orange-600 dark:text-orange-400">
                                            Ce matricule a déjà une note. Choisissez un autre matricule.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- CAS 2: Étudiant trouvé ET pas de note (prêt à noter) -->
                    @if($etudiantTrouve && !$noteDejaExiste)
                        <div class="mt-2 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>

                                <div class="ml-3 flex-1">
                                    <div class="flex justify-between items-start">
                                        <h4 class="text-sm font-medium text-green-800 dark:text-green-300">
                                            {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenoms }}
                                        </h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-300">
                                            Prêt à noter
                                        </span>
                                    </div>

                                    <div class="mt-2 text-sm text-green-700 dark:text-green-400">
                                        @if($codeAnonymatCourant)
                                            <p class="mb-1">
                                                <span class="font-semibold">Matricule:</span> 
                                                <span class="font-semibold">{{ $etudiantTrouve->matricule }}</span>
                                            </p>
                                        @endif
                                        <p class="text-xs italic">✅ Aucune note enregistrée - Prêt pour la saisie</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- ÉTAPE 2: Champ Note (affiché seulement si étudiant trouvé ET pas de note existante) -->
                @if($afficherChampNote && !$noteDejaExiste)
                        <!-- Note Input -->
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
                                        @error('note') border-red-500 bg-red-50 dark:bg-red-900/20 @else border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 @endif
                                        text-gray-900 dark:text-gray-100
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                                <button 
                                    type="button"
                                    wire:click="sauvegarderCopie"
                                    class="px-6 py-3 rounded-lg font-semibold text-white transition-all duration-200
                                        {{ $this->boutonActive ? 'bg-green-600 hover:bg-green-700 shadow-lg transform hover:scale-105' : 'bg-gray-400 cursor-not-allowed' }}"
                                    {{ $this->boutonActive ? '' : 'disabled' }}>
                                    Enregistrer
                                </button>
                            </div>
                            @error('note')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($peutEnregistrer)
                            <p class="mt-2 text-sm text-green-600 dark:text-green-400">
                                💡 Appuyez sur <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded border">Entrée</kbd> pour enregistrer rapidement
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>


    <!-- Statistiques simplifiées -->
    @if($progressCount > 0 || $totalCopies > 0)
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                @if($this->copiesRestantes <= 0 && $totalCopies > 0)
                    <span class="text-green-600 dark:text-green-400">✅ Saisie terminée</span>
                @else
                    Informations de saisie
                @endif
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Info EC -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="text-center">
                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $this->codeSalle }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Code salle</div>
                        @if($ecSelected)
                            @if(!empty($ecSelected->enseignant))
                                <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">{{ $ecSelected->enseignant }}</div>
                            @elseif(!empty($ecSelected->responsable))
                                <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">{{ $ecSelected->responsable }}</div>
                            @elseif($ecSelected->ue && !empty($ecSelected->ue->coordinateur))
                                <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">{{ $ecSelected->ue->coordinateur }}</div>
                            @else
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $ecSelected->abr }}</div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Présents/Absents -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="text-center">
                        <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ $totalCopies }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Présents</div>
                        <div class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $this->totalAbsents }} absents</div>
                    </div>
                </div>

                <!-- Progression saisie -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="text-center">
                        <div class="text-lg font-semibold text-green-600 dark:text-green-400">{{ $progressCount }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Notes saisies</div>
                        <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">{{ $this->copiesRestantes }} restantes</div>
                    </div>
                </div>

                <!-- Pourcentage -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="text-center">
                        <div class="text-lg font-semibold {{ $this->copiesRestantes <= 0 && $totalCopies > 0 ? 'text-green-600 dark:text-green-400' : 'text-purple-600 dark:text-purple-400' }}">
                            {{ round($this->pourcentageProgression, 1) }}%
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Complété</div>
                        @if($moyenneGenerale > 0)
                            <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">Moy: {{ $moyenneGenerale }}/20</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>