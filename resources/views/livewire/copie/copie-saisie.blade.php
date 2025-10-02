{{-- Vue blade principale --}}
<div class="mx-auto px-4 sm:px-6 lg:px-8 py-4">
    <!-- En-tête -->
    <div class="mb-2">
        <h1 class="text-3xl font-bold font-heading text-gray-900 dark:text-white">
            Saisie des Copies
        </h1>
        <p class="mt-2 text-sm font-body text-gray-600 dark:text-gray-400">
            Session {{ ucfirst($sessionType) }} - Saisie des notes
        </p>
    </div>

    <!-- Breadcrumb -->
    <div class="mb-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
                <li class="flex items-center">
                    <button wire:click="backToStep('niveau')"
                        class="flex items-center text-sm font-medium font-body {{ $step === 'niveau' ? 'text-primary-600 dark:text-primary-400' : ($niveauSelected ? 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' : 'text-gray-400 dark:text-gray-500') }} transition-colors duration-200">
                        <svg class="flex-shrink-0 h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L9 5.414V17a1 1 0 102 0V5.414l5.293 5.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        Niveau
                    </button>
                </li>

                @if($niveauSelected)
                    <li class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <button wire:click="backToStep('parcours')"
                            class="ml-4 text-sm font-medium font-body {{ $step === 'parcours' ? 'text-primary-600 dark:text-primary-400' : ($parcoursSelected || (!$niveauSelected || empty($parcours)) ? 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' : 'text-gray-400 dark:text-gray-500') }} transition-colors duration-200">
                            Parcours
                        </button>
                    </li>
                @endif

                @if($niveauSelected && ($parcoursSelected || (!$niveauSelected || empty($parcours))))
                    <li class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <button wire:click="backToStep('ec')"
                            class="ml-4 text-sm font-medium font-body {{ $step === 'ec' ? 'text-primary-600 dark:text-primary-400' : ($ecSelected ? 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' : 'text-gray-400 dark:text-gray-500') }} transition-colors duration-200">
                            EC
                        </button>
                    </li>
                @endif

                @if($ecSelected)
                    <li class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-4 text-sm font-medium font-body {{ $step === 'saisie' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500' }}">
                            Saisie
                        </span>
                    </li>
                @endif
            </ol>
        </nav>
    </div>

    <!-- Messages -->
    @if($message)
        <div class="mb-4 p-4 rounded-lg border-l-4
            @if($messageType === 'success') bg-green-50 border-green-400 text-green-800 dark:bg-green-900/50 dark:text-green-300
            @elseif($messageType === 'error') bg-red-50 border-red-400 text-red-800 dark:bg-red-900/50 dark:text-red-300
            @elseif($messageType === 'warning') bg-yellow-50 border-yellow-400 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300
            @else bg-primary-50 border-primary-400 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300 @endif">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($messageType === 'success')
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @elseif($messageType === 'error')
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    @elseif($messageType === 'warning')
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium font-body">{{ $message }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Contenu principal selon l'étape -->
    <div class="bg-white dark:bg-gray-950 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800">
        @if($step === 'niveau')
            @include('livewire.copie.partials.copie-step-niveau')
        @elseif($step === 'parcours')
            @include('livewire.copie.partials.copie-step-parcours')
        @elseif($step === 'ec')
            @include('livewire.copie.partials.copie-step-ec')
        @elseif($step === 'saisie')
            @include('livewire.copie.partials.copie-step-saisie')
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Variables globales
    let verificationInterval = null;
    let isSubmitting = false;

    document.addEventListener('livewire:init', () => {
        // GESTION DES ÉVÉNEMENTS LIVEWIRE

        // Focus events avec vidage des champs
        Livewire.on('focusMatricule', () => {
            setTimeout(() => {
                const field = document.getElementById('matricule');
                if (field) {
                    field.value = ''; // Vider le champ
                    field.focus();
                    field.select();
                }
            }, 150);
        });

        Livewire.on('focusCodeAnonymat', () => {
            setTimeout(() => {
                const field = document.getElementById('codeAnonymat');
                if (field) {
                    field.value = ''; // Vider le champ
                    field.focus();
                    field.select();
                }
            }, 150);
        });

        // ✅ NOUVEAU : Event pour vider et focus sur numeroCode
        Livewire.on('focusNumeroCode', () => {
            setTimeout(() => {
                const field = document.getElementById('numeroCode');
                if (field) {
                    field.value = ''; // Vider le champ
                    field.focus();
                    field.select();
                }
            }, 150);
        });

        Livewire.on('focusNote', () => {
            setTimeout(() => focusField('note'), 100);
        });

        // Auto-clear messages
        Livewire.on('clearMessage', (event) => {
            setTimeout(() => {
                @this.clearMessage();
            }, event[0]?.delay || 3000);
        });

        // VÉRIFICATION PÉRIODIQUE - Démarrer la vérification quand un étudiant est trouvé
        Livewire.on('etudiantTrouve', () => {
            startVerificationPeriodique();
        });

        // Reset form + focus
        Livewire.on('resetForm', () => {
            stopVerificationPeriodique();
            // Vider explicitement tous les champs
            setTimeout(() => {
                const matricule = document.getElementById('matricule');
                const codeAnonymat = document.getElementById('codeAnonymat');
                const numeroCode = document.getElementById('numeroCode'); // ✅ AJOUTÉ
                const note = document.getElementById('note');
                
                if (matricule) matricule.value = '';
                if (codeAnonymat) codeAnonymat.value = '';
                if (numeroCode) numeroCode.value = ''; // ✅ AJOUTÉ
                if (note) note.value = '';
            }, 50);
        });

        // Après sauvegarde : vider et refocus
        Livewire.on('copieSauvegardee', () => {
            stopVerificationPeriodique();
            
            setTimeout(() => {
                // Vider tous les champs
                const matricule = document.getElementById('matricule');
                const codeAnonymat = document.getElementById('codeAnonymat');
                const numeroCode = document.getElementById('numeroCode'); // ✅ AJOUTÉ
                const note = document.getElementById('note');
                
                if (matricule) matricule.value = '';
                if (codeAnonymat) codeAnonymat.value = '';
                if (numeroCode) numeroCode.value = ''; // ✅ AJOUTÉ - IMPORTANT!
                if (note) note.value = '';
                
                // Focus sur le bon champ selon le mode
                if (@this.is_active) {
                    setTimeout(() => {
                        if (matricule) {
                            matricule.focus();
                            matricule.select();
                        }
                    }, 100);
                } else {
                    setTimeout(() => {
                        // ✅ MODIFIÉ : Focus sur numeroCode au lieu de codeAnonymat
                        if (numeroCode) {
                            numeroCode.focus();
                            numeroCode.select();
                        }
                    }, 100);
                }
            }, 100);
        });

        // Arrêter la vérification quand copie existe déjà
        Livewire.on('copieDejaExistante', () => {
            stopVerificationPeriodique();
        });

        // HOOKS POUR PRÉVENIR DOUBLE SOUMISSION
        Livewire.hook('morph.updating', () => {
            isSubmitting = true;
        });
        
        Livewire.hook('morph.updated', () => {
            setTimeout(() => {
                isSubmitting = false;
            }, 100);
        });

        // Focus initial selon le mode
        setTimeout(() => {
            if (@this.is_active) {
                focusField('matricule');
            } else {
                focusField('numeroCode'); // ✅ MODIFIÉ : numeroCode au lieu de codeAnonymat
            }
        }, 500);
    });

    // FONCTIONS UTILITAIRES

    function focusField(fieldId) {
        const field = document.getElementById(fieldId);
        if (field && field.offsetParent !== null) {
            field.focus();
            if (fieldId === 'matricule' || fieldId === 'codeAnonymat' || fieldId === 'numeroCode') { // ✅ AJOUTÉ numeroCode
                field.select();
            }
        }
    }

    // VÉRIFICATION PÉRIODIQUE POUR DÉTECTER SI UNE COPIE A ÉTÉ SAISIE PAR QUELQU'UN D'AUTRE

    function startVerificationPeriodique() {
        // Nettoyer l'ancien interval s'il existe
        if (verificationInterval) {
            clearInterval(verificationInterval);
        }

        // Vérifier toutes les 15 secondes
        verificationInterval = setInterval(async () => {
            // Seulement si on a un étudiant trouvé et qu'on n'est pas en train de sauvegarder
            if (@this.etudiantTrouve && !isSubmitting) {
                try {
                    const result = await @this.call('verifierSiCopieExiste');
                    if (result && result.existe) {
                        stopVerificationPeriodique();
                        // Déclencher une nouvelle recherche pour mettre à jour l'affichage
                        if (@this.is_active) {
                            await @this.call('rechercherParMatricule');
                        } else {
                            await @this.call('rechercherCodeAnonymat');
                        }
                    }
                } catch (error) {
                    console.warn('Erreur lors de la vérification périodique:', error);
                    stopVerificationPeriodique();
                }
            }
        }, 15000);
    }

    function stopVerificationPeriodique() {
        if (verificationInterval) {
            clearInterval(verificationInterval);
            verificationInterval = null;
        }
    }

    // GESTION DES TOUCHES ET RACCOURCIS CLAVIER

    // Gestion ENTER pour saisie rapide
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.ctrlKey && !e.altKey && !e.shiftKey && !isSubmitting) {
            const target = e.target;
            
            // ✅ AJOUTÉ : Gérer numeroCode aussi
            if (target.id === 'matricule' || target.id === 'codeAnonymat' || target.id === 'numeroCode' || target.id === 'note') {
                e.preventDefault();
                
                // SEULEMENT enregistrer si tout est prêt
                if (@this.peutEnregistrer) {
                    @this.sauvegarderCopie();
                } 
                // Si identifiant et étudiant trouvé, aller au champ note
                else if ((target.id === 'matricule' || target.id === 'codeAnonymat' || target.id === 'numeroCode') && @this.etudiantTrouve && @this.afficherChampNote) {
                    focusField('note');
                }
            }
        }
    });

    // NETTOYAGE LORS DE LA FERMETURE DE LA PAGE

    window.addEventListener('beforeunload', () => {
        stopVerificationPeriodique();
    });

    document.addEventListener('livewire:navigating', () => {
        stopVerificationPeriodique();
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopVerificationPeriodique();
        } else if (@this.etudiantTrouve && !isSubmitting) {
            startVerificationPeriodique();
        }
    });

</script>
@endpush