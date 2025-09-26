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
            <button wire:click="backToStep('setup')" 
                    class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                ← Configuration
            </button>
        </div>

        <!-- Barre de progression principale -->
        <div class="mb-4">
            <div class="flex items-center justify-between text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <span>Progression: {{ $progressCount }}/{{ $totalManchettesPresentes }}</span>
                <span>{{ $this->pourcentageProgression }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-4 rounded-full transition-all duration-500 ease-out" 
                     style="width: {{ $this->pourcentageProgression }}%">
                    <div class="h-full rounded-full bg-gradient-to-r from-white/20 to-transparent"></div>
                </div>
            </div>
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
            <!-- État de completion -->
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg p-6 text-center">
                <svg class="h-16 w-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-2xl font-bold text-green-800 dark:text-green-300 mb-2">
                    🎉 Saisie terminée !
                </h3>
                <p class="text-green-600 dark:text-green-400 mb-4">
                    {{ $progressCount }} manchette(s) enregistrée(s) avec succès
                </p>
                <button wire:click="backToStep('ec')" 
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center justify-center mx-auto">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Choisir une nouvelle matière
                </button>
            </div>
        @else
            <!-- Interface de saisie -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Zone de saisie principale -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Formulaire matricule -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-6">
                        <div class="mb-6">
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
                        </div>

                        <!-- Affichage des informations de l'étudiant -->
                        @if($etudiantTrouve)
                            <div class="mb-6 p-4 {{ $matriculeExisteDeja || (isset($etudiantTrouve->message_erreur)) ? 'bg-red-50 border-red-200 dark:bg-red-900/30 dark:border-red-700' : 'bg-green-50 border-green-200 dark:bg-green-900/30 dark:border-green-700' }} border rounded-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        @if($matriculeExisteDeja || (isset($etudiantTrouve->message_erreur)))
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
                                        <h4 class="text-sm font-medium {{ ($matriculeExisteDeja || isset($etudiantTrouve->message_erreur)) ? 'text-red-800 dark:text-red-300' : 'text-green-800 dark:text-green-300' }}">
                                            {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenoms }}
                                        </h4>
                                        <p class="text-xs {{ ($matriculeExisteDeja || isset($etudiantTrouve->message_erreur)) ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }} mt-1">
                                            @if(isset($etudiantTrouve->message_erreur))
                                                ⚠️ {{ $etudiantTrouve->message_erreur }}
                                            @elseif($matriculeExisteDeja)
                                                ⚠️ Cet étudiant a déjà une manchette pour cette matière
                                            @else
                                                ✓ Étudiant trouvé - Prêt pour l'enregistrement
                                                @if($sessionType === 'rattrapage')
                                                    <span class="block text-xs text-blue-600 dark:text-blue-400 mt-1">
                                                        📋 Éligible au rattrapage pour cette matière
                                                    </span>
                                                @endif
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @elseif(!empty($matricule) && strlen($matricule) >= 3)
                            <div class="mb-6 p-3 bg-red-50 border border-red-200 dark:bg-red-900/30 dark:border-red-700 rounded-lg">
                                <p class="text-sm text-red-700 dark:text-red-300">
                                    ❌ Aucun étudiant trouvé avec ce matricule dans ce niveau{{ $parcoursSelected ? '/parcours' : '' }}
                                </p>
                            </div>
                        @endif

                        <!-- Conseils d'utilisation -->
                        <div class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                            <p><strong>💡 Conseils:</strong></p>
                            <ul class="mt-1 space-y-1">
                                <li>• Le champ matricule est automatiquement focalisé</li>
                                <li>• La recherche se fait automatiquement après 3 caractères</li>
                                <li>• <strong>Appuyez sur Entrée</strong> pour procéder à la validation complète</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Panneau latéral - Code anonymat -->
                <div class="space-y-6">
                    <!-- Section code anonymat -->
                    @if($etudiantTrouve && !$matriculeExisteDeja && !isset($etudiantTrouve->message_erreur))
                        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-4 flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Code d'anonymat
                            </h4>

                            <!-- Champ code anonymat -->
                            <div class="mb-4">
                                <label for="codeAnonymat" class="block text-xs font-medium text-blue-700 dark:text-blue-300 mb-2">
                                    Code suggéré <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                    wire:model.live="codeAnonymatSaisi" 
                                    id="codeAnonymat"
                                    class="w-full px-3 py-3 text-lg font-mono text-center border {{ $this->codeEstValide ? 'border-green-300 bg-green-50 dark:bg-green-900/20' : 'border-blue-300 bg-blue-50 dark:bg-blue-900/20' }} dark:border-blue-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white"
                                    placeholder="AB1"
                                    autocomplete="off"
                                    style="letter-spacing: 0.1em;"
                                    wire:keydown.enter="validerParEntree">

                                <!-- Validation visuelle en temps réel -->
                                <div class="mt-2 text-xs">
                                    @if(!empty($codeValidationErrors))
                                        @foreach($codeValidationErrors as $error)
                                            <p class="text-red-600 dark:text-red-400 mb-1">❌ {{ $error }}</p>
                                        @endforeach
                                    @elseif(!empty($codeAnonymatSaisi))
                                        <p class="text-green-600 dark:text-green-400">✅ Code valide</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Règles de format -->
                            <div class="mb-4 p-3 bg-white/50 dark:bg-gray-800/50 rounded border">
                                <p class="text-xs font-medium text-blue-800 dark:text-blue-300 mb-2">Format requis :</p>
                                <ul class="text-xs text-blue-700 dark:text-blue-400 space-y-1">
                                    <li>• 2 lettres MAJUSCULES + chiffres</li>
                                    <li>• Exemples: AB1, XY25, ZZ100</li>
                                    <li>• Max {{ $totalManchettesPresentes }} (selon présences)</li>
                                </ul>
                            </div>

                            <!-- Bouton de validation principal -->
                            <button wire:click="validerEtConfirmer" 
                                    wire:loading.attr="disabled"
                                    wire:target="validerEtConfirmer"
                                    class="w-full px-4 py-4 text-lg font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed {{ !$etudiantTrouve || $matriculeExisteDeja || !$this->codeEstValide ? 'bg-gray-300 text-gray-500 dark:bg-gray-600 dark:text-gray-400' : 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-2 focus:ring-blue-500 focus:ring-offset-2' }}"
                                     {{ !$etudiantTrouve || $matriculeExisteDeja || !$this->codeEstValide || isset($etudiantTrouve->message_erreur) ? 'disabled' : '' }}>

                                <span wire:loading.remove wire:target="validerEtConfirmer">
                                    @if(!$etudiantTrouve)
                                        Saisir un matricule valide
                                    @elseif(isset($etudiantTrouve->message_erreur))
                                        Étudiant non éligible
                                    @elseif($matriculeExisteDeja)
                                        Étudiant déjà enregistré
                                    @elseif(!$this->codeEstValide)
                                        Corriger le code anonymat
                                    @else
                                        🏷️ Valider la manchette
                                    @endif  
                                </span>
                                
                                <span wire:loading wire:target="validerEtConfirmer" class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.38 0 0 5.38 0 12h4zm2 5.29l.4.95c.8 1.8.8 1.8.8 1.8z"></path>
                                    </svg>
                                    Validation...
                                </span>
                            </button>
                        </div>
                    @endif

                    <!-- Statistiques de session -->
                    <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            📊 Statistiques de session
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Session:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200 capitalize">{{ $sessionType }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Progression:</span>
                                <span class="font-medium text-blue-600 dark:text-blue-400">{{ $this->pourcentageProgression }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Taux présence:</span>
                                <span class="font-medium text-green-600 dark:text-green-400">{{ $this->pourcentagePresence }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal de confirmation -->
    @if($showConfirmation)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    🔍 Confirmation du code d'anonymat
                </h3>
                
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                        Confirmez-vous que le code d'anonymat est correct ?
                    </p>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Étudiant:</span>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $etudiantTrouve?->nom }} {{ $etudiantTrouve?->prenoms }}</div>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Code anonymat:</span>
                            <div class="font-mono font-bold text-blue-600 dark:text-blue-400 text-lg">{{ $codeAnonymatSaisi }}</div>
                        </div>
                    </div>
                </div>
                  <h3 class="text-center text-lg font-semibold mb-3 text-yellow-500"> {{ Auth::user()->name }}, Vakio tsara sao diso io Code io!</h3>
                <div class="flex space-x-3">
                    <button wire:click="annulerConfirmation" 
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        ❌ Non, modifier
                    </button>
                    <button wire:click="confirmerCodeAnonymat" 
                            wire:loading.attr="disabled"
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50"
                            id="confirm-anonymat-button">
                        <span wire:loading.remove>✅ Oui, confirmer</span>
                        <span wire:loading>⏳ Enregistrement...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        let lastFocusedElement = null;
        let isCodeAnonymatFocused = false;

        // Validation côté client pour le format du code anonymat
        const validateCodeFormat = (code) => {
            const regex = /^[A-Z]{2}[0-9]+$/;
            return regex.test(code);
        };

        // Validation du nombre maximum
        const validateMaxNumber = (code, maxPresences) => {
            const match = code.match(/^[A-Z]{2}([0-9]+)$/);
            if (match) {
                const number = parseInt(match[1]);
                return number >= 1 && number <= maxPresences;
            }
            return false;
        };

        // Traquer le focus sur les champs
        document.addEventListener('focus', (e) => {
            if (e.target.id === 'codeAnonymat') {
                isCodeAnonymatFocused = true;
                lastFocusedElement = e.target;
            } else if (e.target.id === 'matricule') {
                isCodeAnonymatFocused = false;
                lastFocusedElement = e.target;
            }
        }, true);

        document.addEventListener('blur', (e) => {
            if (e.target.id === 'codeAnonymat') {
                setTimeout(() => {
                    if (document.activeElement?.id !== 'codeAnonymat') {
                        isCodeAnonymatFocused = false;
                    }
                }, 100);
            }
        }, true);

        // Mise à jour en temps réel du champ code anonymat
        document.addEventListener('input', (e) => {
            if (e.target.id === 'codeAnonymat') {
                const code = e.target.value.toUpperCase();
                e.target.value = code; // Forcer majuscules
                
                // Validation visuelle immédiate
                const isValidFormat = validateCodeFormat(code);
                if (isValidFormat) {
                    e.target.classList.add('border-green-300', 'bg-green-50');
                    e.target.classList.remove('border-red-300', 'bg-red-50');
                } else if (code.length > 0) {
                    e.target.classList.add('border-red-300', 'bg-red-50');
                    e.target.classList.remove('border-green-300', 'bg-green-50');
                }
            }
        });

        // Gestion intelligente du focus sans vider le champ matricule
        const focusMatriculeInput = () => {
            if (isCodeAnonymatFocused || document.activeElement?.id === 'codeAnonymat') {
                return;
            }
            
            const input = document.getElementById('matricule');
            if (input && document.activeElement !== input) {
                input.focus(); // Ne pas vider le champ ici
            }
        };

        // Focus sur le champ code anonymat
        Livewire.on('focus-code-anonymat', () => {
            const input = document.getElementById('codeAnonymat');
            if (input) {
                input.focus();
            }
        });

        // Focus sur le bouton de confirmation dans la modal
        Livewire.on('modal-opened', () => {
            setTimeout(() => {
                const confirmButton = document.getElementById('confirm-anonymat-button');
                if (confirmButton && !confirmButton.disabled) {
                    confirmButton.focus();
                }
            }, 100);
        });

        // Focus après actions Livewire
        Livewire.on('focus-matricule-input', () => {
            setTimeout(() => {
                if (!isCodeAnonymatFocused) {
                    focusMatriculeInput();
                }
            }, 100);
        });

        // Gestion du vidage explicite du champ matricule
        Livewire.on('matricule-cleared', () => {
            setTimeout(() => {
                const input = document.getElementById('matricule');
                if (input) {
                    input.value = ''; // Vider uniquement sur cet événement
                    if (!isCodeAnonymatFocused) {
                        input.focus();
                    }
                }
            }, 50);
        });

        // Focus initial uniquement lors du chargement initial
        document.addEventListener('livewire:navigated', () => {
            setTimeout(() => {
                if (!isCodeAnonymatFocused && !document.getElementById('matricule')?.value) {
                    focusMatriculeInput();
                }
            }, 100);
        });

        // Supprimer le hook morph.updated pour éviter les réinitialisations pendant la saisie
        // Livewire.hook('morph.updated', () => {
        //     setTimeout(() => {
        //         if (!isCodeAnonymatFocused) {
        //             focusMatriculeInput();
        //         }
        //     }, 50);
        // });

        // Gestion des raccourcis clavier
        document.addEventListener('keydown', (e) => {
            // Échapper pour fermer la modal
            if (e.key === 'Escape' && @json($showConfirmation)) {
                @this.call('annulerConfirmation');
                e.preventDefault();
                return;
            }
            
            // Entrée dans la modal pour confirmer
            if (e.key === 'Enter' && @json($showConfirmation)) {
                e.preventDefault();
                const confirmButton = document.getElementById('confirm-anonymat-button');
                if (confirmButton && !confirmButton.disabled) {
                    confirmButton.click(); // Simuler un clic sur le bouton
                    @this.call('confirmerCodeAnonymat');
                }
                return;
            }
            
            // Ctrl+Enter pour validation rapide
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                @this.call('validerEtConfirmer');
            }
        });

        // Animations de progression et vidage après sauvegarde
        Livewire.on('manchette-saved', () => {
            // Vider le champ matricule et remettre le focus
            setTimeout(() => {
                const inputMatricule = document.getElementById('matricule');
                if (inputMatricule) {
                    inputMatricule.value = ''; // Vider explicitement après sauvegarde
                    if (!isCodeAnonymatFocused) {
                        inputMatricule.focus();
                    }
                }
            }, 100);

            // Animation de la barre de progression
            const progressBar = document.querySelector('.bg-gradient-to-r.from-blue-500');
            if (progressBar) {
                progressBar.classList.add('animate-pulse');
                setTimeout(() => {
                    progressBar.classList.remove('animate-pulse');
                }, 1000);
            }
        });

        // Notification de fin de saisie
        Livewire.on('saisie-terminee', (data) => {
            // Animation de célébration
            const celebration = () => {
                console.log('🎉 Saisie terminée avec succès!', data);
                focusMatriculeInput(); // Remettre le focus pour une nouvelle saisie
            };
            setTimeout(celebration, 500);
        });
    });
</script>
@endpush