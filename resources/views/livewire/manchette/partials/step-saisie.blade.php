{{-- vue pour saisie manchette --}}
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            Saisie des manchettes 
            <span class="text-base font-normal text-gray-500 dark:text-gray-400">
                {{ $ecSelected?->abr }}.{{ $ecSelected?->nom }} 
                @if($niveauSelected) - {{ $niveauSelected->nom }}@endif
                @if($parcoursSelected) ({{ $parcoursSelected->nom }})@endif
            </span>
        </h2>
        <button wire:click="backToStep('setup')" 
                class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
            ← Configuration
        </button>
    </div>

    <!-- Informations contextuelles -->
    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 text-sm">
            <div>
                <span class="text-gray-600 dark:text-gray-400">Code:</span>
                <div class="font-mono font-semibold text-blue-600 dark:text-blue-400">{{ $codeSalle }}</div>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Nbre présents:</span>
                <div class="font-semibold text-blue-600 dark:text-blue-400">{{ $totalManchettesPresentes }}</div>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Nbre absents:</span>
                <div class="font-semibold text-red-600 dark:text-red-400">{{ $this->totalAbsents }}</div>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Restantes:</span>
                <div class="font-semibold text-orange-600 dark:text-orange-400">{{ $this->manchettesRestantes }}</div>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Total inscrit:</span>
                <div class="font-semibold text-blue-600 dark:text-blue-400">{{ $totalEtudiantsTheorique }}</div>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Enseignant:</span>
                <div class="font-semibold text-xs text-blue-600 dark:text-blue-400">{{ $ecSelected ? ($ecSelected->enseignant ?? 'Non défini') : 'Non sélectionné' }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Zone de saisie principale -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Formulaire de saisie du matricule -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-6">
                <div class="mb-4">
                    <label for="matricule" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Matricule étudiant <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           wire:model.live="matricule" 
                           id="matricule"
                           class="w-full px-4 py-4 text-lg font-mono border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           placeholder="Saisir le matricule de l'étudiant..."
                           autocomplete="off"
                           autofocus
                           wire:keydown.enter="validerParEntree">
                    
                    <!-- Affichage des informations de l'étudiant -->
                    @if($etudiantTrouve)
                        <div class="mt-3 p-4 {{ $matriculeExisteDeja ? 'bg-red-50 border-red-200 dark:bg-red-900/30 dark:border-red-700' : 'bg-green-50 border-green-200 dark:bg-green-900/30 dark:border-green-700' }} border rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    @if($matriculeExisteDeja)
                                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium {{ $matriculeExisteDeja ? 'text-red-800 dark:text-red-300' : 'text-green-800 dark:text-green-300' }}">
                                        {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenoms }}
                                    </h4>
                                    <p class="text-xs {{ $matriculeExisteDeja ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }} mt-1">
                                        @if($matriculeExisteDeja)
                                            ⚠️ Cet étudiant a déjà une manchette pour cette matière
                                        @else
                                            ✓ Étudiant trouvé - Prêt pour l'enregistrement
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif(!empty($matricule) && strlen($matricule) >= 3)
                        <div class="mt-3 p-3 bg-red-50 border border-red-200 dark:bg-red-900/30 dark:border-red-700 rounded-lg">
                            <p class="text-sm text-red-700 dark:text-red-300">
                                ❌ Aucun étudiant trouvé avec ce matricule
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Bouton d'enregistrement -->
                <button wire:click="sauvegarderManchette" 
                        wire:loading.attr="disabled"
                        wire:target="sauvegarderManchette"
                        class="w-full px-6 py-4 text-lg font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed {{ !$etudiantTrouve || $matriculeExisteDeja ? 'bg-gray-300 text-gray-500 dark:bg-gray-600 dark:text-gray-400' : 'bg-green-600 hover:bg-green-700 text-white focus:ring-2 focus:ring-green-500 focus:ring-offset-2' }}"
                        {{ !$etudiantTrouve || $matriculeExisteDeja ? 'disabled' : '' }}>
                    
                    <span wire:loading.remove wire:target="sauvegarderManchette">
                        @if(!$etudiantTrouve)
                            Saisir un matricule valide
                        @elseif($matriculeExisteDeja)
                            Étudiant déjà enregistré
                        @else
                            🏷️ Enregistrer la manchette {{ $prochainCodeAnonymat }}
                        @endif
                    </span>
                    
                    <span wire:loading wire:target="sauvegarderManchette" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.38 0 0 5.38 0 12h4zm2 5.29l.4.95c.8 1.8.8 1.8.8 1.8z"></path>
                        </svg>
                        Enregistrement...
                    </span>
                </button>
            </div>

            <!-- Raccourcis clavier -->
            <div class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                <p><strong>💡 Conseils:</strong></p>
                <ul class="mt-1 space-y-1">
                    <li>• Le champ matricule est automatiquement focalisé</li>
                    <li>• La recherche se fait automatiquement après 3 caractères</li>
                    <li>• <strong>Appuyez sur Entrée</strong> pour enregistrer quand l'étudiant est trouvé</li>
                    <li>• Ou utilisez le bouton "Enregistrer" ci-dessus</li>
                </ul>
            </div>
        </div>

        <!-- Panneau latéral - Statistiques et progression -->
        <div class="space-y-6">
            <!-- Progression globale -->
            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-3 flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12z" clip-rule="evenodd"/>
                    </svg>
                    Progression
                </h4>
                
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                    {{ $progressCount }}/{{ $totalManchettesPresentes }}
                </div>
                
                <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-3 mb-2">
                    <div class="bg-blue-600 dark:bg-blue-500 h-3 rounded-full transition-all duration-300" 
                         style="width: {{ $totalManchettesPresentes > 0 ? round(($progressCount / $totalManchettesPresentes) * 100) : 0 }}%">
                    </div>
                </div>
                
                <p class="text-sm text-blue-600 dark:text-blue-400">
                    {{ $totalManchettesPresentes > 0 ? round(($progressCount / $totalManchettesPresentes) * 100, 1) : 0 }}% complété
                </p>
            </div>

            <!-- État de completion -->
            @if($progressCount >= $totalManchettesPresentes)
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg p-4">
                    <div class="text-center mb-4">
                        <svg class="h-12 w-12 text-green-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h4 class="text-lg font-semibold text-green-800 dark:text-green-300">
                            🎉 Saisie terminée !
                        </h4>
                        <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                            {{ $progressCount }} manchette(s) enregistrée(s) avec succès
                        </p>
                    </div>
                    <div class="space-y-2">
                        <!-- Bouton pour choisir une nouvelle EC -->
                        <button wire:click="backToStep('ec')" 
                                class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center justify-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Choisir une nouvelle matière
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-focus sur le champ matricule
    document.addEventListener('livewire:navigated', () => {
        const matriculeInput = document.getElementById('matricule');
        if (matriculeInput) {
            matriculeInput.focus();
        }
    });
    
    // Focus automatique après render et après saisie réussie
    Livewire.hook('morph.updated', ({ component, cleanup }) => {
        const matriculeInput = document.getElementById('matricule');
        if (matriculeInput) {
            // Remettre le focus après chaque mise à jour
            setTimeout(() => {
                matriculeInput.focus();
            }, 100);
        }
    });

    // Focus après message de succès (saisie réussie)
    document.addEventListener('livewire:init', () => {
        Livewire.on('manchette-saved', () => {
            setTimeout(() => {
                const matriculeInput = document.getElementById('matricule');
                if (matriculeInput) {
                    matriculeInput.focus();
                    matriculeInput.select(); // Sélectionner le contenu pour faciliter la nouvelle saisie
                }
            }, 200);
        });
    });
</script>
@endpush