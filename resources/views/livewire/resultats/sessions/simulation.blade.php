{{-- simulation.blade.php --}}
@if($activeTab === 'simulation' && (!empty($resultatsSession1) || !empty($resultatsSession2)))
    <div class="space-y-6">
        {{-- ✅ STATUT DE DÉLIBÉRATION AVEC DERNIÈRES CONFIGURATIONS --}}
         {{-- @include('livewire.resultats.sessions.partials.statut-deliberation') --}}

        {{-- ✅ PARAMÈTRES DE SIMULATION DÉLIBÉRATION --}}
        @include('livewire.resultats.sessions.partials.parametres-simulation')
        
        {{-- ✅ RÉSULTATS DE SIMULATION AVEC PROTECTION CONTRE LES ERREURS --}}
        @include('livewire.resultats.sessions.partials.resultats-simulation')

    </div>
@endif

{{-- ✅ MODAL D'EXPORT AVEC PROTECTION CONTRE LES ERREURS --}}
@include('livewire.resultats.sessions.partials.modal-export')

{{-- Modal de Confirmation Délibération --}}
@if($showConfirmationModal ?? false)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black">
        {{-- Overlay --}}
        <div class="relative w-full max-w-lg p-6">
            <div wire:click="fermerConfirmationModal" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

            {{-- Modal --}}
            <div class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                
                {{-- Icône d'avertissement --}}
                <div class="sm:flex sm:items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-orange-100 rounded-full sm:mx-0 sm:h-10 sm:w-10 dark:bg-orange-900/30">
                        <em class="text-orange-600 dark:text-orange-400 ni ni-alert-c text-xl"></em>
                    </div>
                    
                    {{-- Contenu du modal --}}
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title">
                            Confirmer l'application de la délibération
                        </h3>
                        
                        <div class="mt-3">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Êtes-vous sûr de vouloir appliquer cette délibération ? Cette action va :
                            </p>
                            
                            <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                                <li class="flex items-center">
                                    <em class="mr-2 text-orange-500 ni ni-check-circle"></em>
                                    Mettre à jour les décisions de {{ $simulationDeliberation['statistiques']['changements'] ?? 0 }} étudiants
                                </li>
                                <li class="flex items-center">
                                    <em class="mr-2 text-orange-500 ni ni-shield-check"></em>
                                    Marquer la session comme délibérée
                                </li>
                                <li class="flex items-center">
                                    <em class="mr-2 text-red-500 ni ni-alert-circle"></em>
                                    <strong>Cette action est irréversible</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Boutons d'action --}}
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    {{-- Bouton Confirmer avec Loading --}}
                    <button wire:click="appliquerDeliberation" 
                            wire:loading.attr="disabled"
                            wire:target="appliquerDeliberation"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-60 disabled:cursor-not-allowed transition-all">
                        
                        {{-- Icône par défaut --}}
                        <em class="mr-2 ni ni-check-circle" wire:loading.remove wire:target="appliquerDeliberation"></em>
                        
                        {{-- Icône loading --}}
                        <em class="mr-2 text-lg ni ni-reload animate-spin" wire:loading wire:target="appliquerDeliberation"></em>
                        
                        {{-- Texte --}}
                        <span wire:loading.remove wire:target="appliquerDeliberation">Confirmer l'application</span>
                        <span wire:loading wire:target="appliquerDeliberation">Application en cours...</span>
                    </button>

                    {{-- Bouton Annuler --}}
                    <button wire:click="fermerConfirmationModal" 
                            wire:loading.attr="disabled"
                            wire:target="appliquerDeliberation"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto sm:text-sm disabled:opacity-60 transition-all">
                        <em class="mr-2 ni ni-cross"></em>
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ✅ SCRIPTS JAVASCRIPT POUR LES INTERACTIONS AVEC PROTECTION --}}
@push('scripts')
<script>
    // ✅ PROTECTION CONTRE LES ERREURS JAVASCRIPT
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier que Livewire est disponible
        if (typeof Livewire === 'undefined') {
            console.warn('Livewire non disponible');
            return;
        }

        // Fonction pour exporter par décision depuis la simulation
        window.exporterParDecisionSimulation = function(decision, format = 'pdf') {
            try {
                // Configurer l'export pour cette décision spécifique
                @this.set('exportConfig.filtres.decision_filter', decision);
                @this.set('exportConfig.tri.champ', 'moyenne_generale');
                @this.set('exportConfig.tri.ordre', 'desc');
                @this.set('exportData', 'simulation');
                @this.set('exportType', format);

                // Générer directement sans modal
                @this.call('genererExportAvecConfig');
            } catch (error) {
                console.error('Erreur lors de l\'export par décision:', error);
                alert('Erreur lors de l\'export. Veuillez réessayer.');
            }
        };

        // Fonction pour export rapide avec choix du format
        window.exportRapideSimulation = function(format, decision = 'tous') {
            try {
                if (decision !== 'tous') {
                    @this.set('exportConfig.filtres.decision_filter', decision);
                }
                @this.set('exportType', format);
                @this.set('exportData', 'simulation');
                @this.call('genererExportAvecConfig');
            } catch (error) {
                console.error('Erreur lors de l\'export rapide:', error);
                alert('Erreur lors de l\'export. Veuillez réessayer.');
            }
        };

        // Fonction pour sélectionner rapidement les colonnes essentielles
        window.selectionnerColonnesEssentielles = function() {
            try {
                @this.set('exportConfig.colonnes', {
                    'rang': true,
                    'nom_complet': true,
                    'matricule': true,
                    'moyenne': true,
                    'credits': true,
                    'decision': true,
                    'niveau': false
                });
            } catch (error) {
                console.error('Erreur lors de la sélection des colonnes:', error);
            }
        };

        // Fonction pour sélectionner uniquement les colonnes minimales
        window.selectionnerColonnesMinimales = function() {
            try {
                @this.set('exportConfig.colonnes', {
                    'rang': true,
                    'nom_complet': true,
                    'decision': true,
                    'matricule': false,
                    'moyenne': false,
                    'credits': false,
                    'niveau': false
                });
            } catch (error) {
                console.error('Erreur lors de la sélection minimale:', error);
            }
        };
    });

    // ✅ ACTUALISER LES STATISTIQUES APRÈS UNE DÉLIBÉRATION AVEC PROTECTION
    document.addEventListener('livewire:updated', function () {
        try {
            // Vérifier que les méthodes existent avant de les appeler
            if (window.livewire && @this && typeof @this.loadResultats === 'function') {
                // Recharger automatiquement après application de délibération
                setTimeout(() => {
                    try {
                        @this.loadResultats();
                    } catch (error) {
                        console.error('Erreur lors du rechargement des résultats:', error);
                    }
                }, 500);
            }
        } catch (error) {
            console.error('Erreur dans livewire:updated:', error);
        }
    });

    // ✅ OBSERVER LES CHANGEMENTS DE SIMULATION AVEC PROTECTION
    document.addEventListener('simulation-applied', function () {
        try {
            // Force le rechargement des données après application de simulation
            if (@this && typeof @this.refreshData === 'function') {
                @this.refreshData();
            }
        } catch (error) {
            console.error('Erreur lors du refresh après simulation:', error);
        }
    });

    // ✅ GESTION DES ERREURS GLOBALES JAVASCRIPT
    window.addEventListener('error', function(e) {
        // Logger les erreurs mais ne pas les afficher à l'utilisateur sauf si critique
        console.error('Erreur JavaScript globale:', e.error);

        // Seulement afficher une alerte pour les erreurs critiques
        if (e.error && e.error.message && e.error.message.includes('critical')) {
            alert('Une erreur critique s\'est produite. Veuillez actualiser la page.');
        }
    });

    // ✅ PROTECTION CONTRE LES ERREURS LIVEWIRE
    document.addEventListener('livewire:error', function (event) {
        console.error('Erreur Livewire:', event.detail);

        // Afficher un message d'erreur utilisateur-friendly
        if (event.detail && event.detail.message) {
            const errorMessage = event.detail.message;

            // Messages d'erreur spécifiques
            if (errorMessage.includes('getStatistiquesExportPreview')) {
                console.warn('Erreur dans les statistiques d\'export, mais continuons...');
                return; // Ne pas afficher cette erreur à l'utilisateur
            }

            if (errorMessage.includes('Method') && errorMessage.includes('does not exist')) {
                alert('Une fonctionnalité n\'est pas encore disponible. Veuillez contacter l\'administrateur.');
                return;
            }

            // Erreur générique
            alert('Une erreur s\'est produite. Veuillez réessayer ou actualiser la page.');
        }
    });
</script>
@endpush

{{-- ✅ STYLES CSS POUR AMÉLIORER L'EXPÉRIENCE UTILISATEUR --}}
@push('styles')
<style>
    /* Protection contre le clignotement lors des rechargements */
    [wire\:loading] {
        opacity: 0.7;
        pointer-events: none;
        transition: opacity 0.2s ease-in-out;
    }

    /* Indicateur de chargement subtil */
    [wire\:loading.delay] {
        opacity: 1;
    }

    /* Amélioration de l'accessibilité pour les boutons désactivés */
    button[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Style pour les éléments en erreur */
    .error-highlight {
        border: 2px solid #ef4444 !important;
        background-color: #fef2f2 !important;
    }

    /* Animation pour les changements de délibération */
    .deliberation-change {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }

    /* Style pour les alertes temporaires */
    .alert-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }

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

    /* Protection contre le débordement sur mobile */
    @media (max-width: 768px) {
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }

        .modal-content {
            margin: 10px;
            max-height: calc(100vh - 20px);
        }
    }
</style>
@endpush