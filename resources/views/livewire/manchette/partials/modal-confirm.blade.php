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
                            type="button"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        ❌ Non, modifier
                    </button>
                    <button wire:click="confirmerCodeAnonymat" 
                            wire:loading.attr="disabled"
                            type="button"
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50"
                            id="confirm-anonymat-button"
                            autofocus>
                        <span wire:loading.remove>✅ Oui, confirmer</span>
                        <span wire:loading>⏳ Enregistrement...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
<script>
    // Fonction pour initialiser tous les event listeners
    function initializeEventListeners() {
        let lastFocusedElement = null;
        let isCodeAnonymatFocused = false;

        // Supprimer les anciens listeners pour éviter les doublons
        document.removeEventListener('keydown', handleKeyDown);
        document.removeEventListener('focus', handleFocus, true);
        document.removeEventListener('blur', handleBlur, true);
        document.removeEventListener('input', handleInput);

        // Validation côté client pour le format du code anonymat
        const validateCodeFormat = (code) => {
            const regex = /^[A-Z]{2}[0-9]+$/;
            return regex.test(code);
        };

        // Gestion du focus
        function handleFocus(e) {
            if (e.target.id === 'codeAnonymat') {
                isCodeAnonymatFocused = true;
                lastFocusedElement = e.target;
            } else if (e.target.id === 'matricule') {
                isCodeAnonymatFocused = false;
                lastFocusedElement = e.target;
            }
        }

        function handleBlur(e) {
            if (e.target.id === 'codeAnonymat') {
                setTimeout(() => {
                    if (document.activeElement?.id !== 'codeAnonymat') {
                        isCodeAnonymatFocused = false;
                    }
                }, 100);
            }
        }

        // Mise à jour en temps réel du champ code anonymat
        function handleInput(e) {
            if (e.target.id === 'codeAnonymat') {
                const code = e.target.value.toUpperCase();
                e.target.value = code;
                
                const isValidFormat = validateCodeFormat(code);
                if (isValidFormat) {
                    e.target.classList.add('border-green-300', 'bg-green-50');
                    e.target.classList.remove('border-red-300', 'bg-red-50');
                } else if (code.length > 0) {
                    e.target.classList.add('border-red-300', 'bg-red-50');
                    e.target.classList.remove('border-green-300', 'bg-green-50');
                }
            }
        }

        // Gestion des raccourcis clavier - FONCTION NOMMÉE
        function handleKeyDown(e) {
            // Vérifier si la modal est ouverte
            const modalVisible = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
            
            // Échapper pour fermer la modal
            if (e.key === 'Escape' && modalVisible) {
                e.preventDefault();
                window.Livewire.find(@js($this->getId())).call('annulerConfirmation');
                return;
            }
            
            // ENTRÉE dans la modal pour confirmer
            if (e.key === 'Enter' && modalVisible) {
                e.preventDefault();
                const confirmButton = document.getElementById('confirm-anonymat-button');
                if (confirmButton && !confirmButton.disabled) {
                    confirmButton.click();
                }
                return;
            }
            
            // Ctrl+Enter pour validation rapide (hors modal)
            if (e.ctrlKey && e.key === 'Enter' && !modalVisible) {
                e.preventDefault();
                window.Livewire.find(@js($this->getId())).call('validerEtConfirmer');
            }
        }

        // Gestion intelligente du focus
        const focusMatriculeInput = () => {
            if (isCodeAnonymatFocused || document.activeElement?.id === 'codeAnonymat') {
                return;
            }
            
            const input = document.getElementById('matricule');
            if (input && document.activeElement !== input) {
                input.focus();
            }
        };

        // Attacher les event listeners
        document.addEventListener('keydown', handleKeyDown);
        document.addEventListener('focus', handleFocus, true);
        document.addEventListener('blur', handleBlur, true);
        document.addEventListener('input', handleInput);

        // Event listeners Livewire
        Livewire.on('focus-code-anonymat', () => {
            const input = document.getElementById('codeAnonymat');
            if (input) {
                input.focus();
            }
        });

        Livewire.on('modal-opened', () => {
            setTimeout(() => {
                const confirmButton = document.getElementById('confirm-anonymat-button');
                if (confirmButton && !confirmButton.disabled) {
                    confirmButton.focus();
                }
            }, 200);
        });

        Livewire.on('focus-matricule-input', () => {
            setTimeout(() => {
                if (!isCodeAnonymatFocused) {
                    focusMatriculeInput();
                }
            }, 100);
        });

        Livewire.on('matricule-cleared', () => {
            setTimeout(() => {
                const input = document.getElementById('matricule');
                if (input) {
                    input.value = '';
                    if (!isCodeAnonymatFocused) {
                        input.focus();
                    }
                }
            }, 50);
        });

        Livewire.on('manchette-saved', () => {
            setTimeout(() => {
                const inputMatricule = document.getElementById('matricule');
                if (inputMatricule) {
                    inputMatricule.value = '';
                    if (!isCodeAnonymatFocused) {
                        inputMatricule.focus();
                    }
                }
            }, 100);

            const progressBar = document.querySelector('.bg-gradient-to-r.from-blue-500');
            if (progressBar) {
                progressBar.classList.add('animate-pulse');
                setTimeout(() => {
                    progressBar.classList.remove('animate-pulse');
                }, 1000);
            }
        });

        Livewire.on('saisie-terminee', (data) => {
            const celebration = () => {
                console.log('🎉 Saisie terminée avec succès!', data);
                focusMatriculeInput();
            };
            setTimeout(celebration, 500);
        });

        // Listener pour la synchronisation des absents
        Livewire.on('manchettes-absents-synchronisees', (data) => {
            console.log('🎯 Synchronisation des absents réussie', data);
            
            // Animation de succès
            const progressBar = document.querySelector('.bg-gradient-to-r.from-blue-500');
            if (progressBar) {
                progressBar.style.width = '100%';
                progressBar.classList.add('animate-pulse');
                setTimeout(() => {
                    progressBar.classList.remove('animate-pulse');
                }, 2000);
            }
        });

        // Focus initial
        setTimeout(() => {
            if (!isCodeAnonymatFocused && !document.getElementById('matricule')?.value) {
                focusMatriculeInput();
            }
        }, 100);
    }

    // Initialiser lors du chargement initial
    document.addEventListener('livewire:init', initializeEventListeners);

    // IMPORTANT: Réinitialiser lors des navigations Livewire
    document.addEventListener('livewire:navigated', initializeEventListeners);

    // Réinitialiser après les mises à jour de composants
    Livewire.hook('morph.updated', ({ el, component }) => {
        // Seulement pour ce composant spécifique
        if (component.fingerprint.name === 'manchette.manchette-saisie') {
            setTimeout(initializeEventListeners, 50);
        }
    });
</script>
@endpush