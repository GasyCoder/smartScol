{{-- Vue principale --}}
<div class="container px-4 py-6 mx-auto">
    <!-- En-tête fixe avec titre et actions globales -->
    <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal avec informations de session -->
             <div class="flex items-center space-x-3">
                <h2 class="text-xl font-medium text-slate-700 dark:text-white">Saisie des Manchettes</h2>
                <!-- NOUVEAU : Affichage des informations de session -->
                @if($currentSessionType)
                    <span class="px-3 py-1 text-sm font-medium rounded-full
                        {{ $currentSessionType === 'Normale'
                            ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200 dark:border-green-700'
                            : 'bg-orange-100 text-orange-800 border border-orange-200 dark:bg-orange-900 dark:text-orange-200 dark:border-orange-700'
                        }}">
                        Session {{ $currentSessionType }}
                        @if($session_exam_id)
                            @php
                                $sessionInfo = App\Models\SessionExam::find($session_exam_id);
                            @endphp
                            @if($sessionInfo && $sessionInfo->date_debut)
                                <span class="ml-1 text-xs opacity-75">
                                    ({{ \Carbon\Carbon::parse($sessionInfo->date_debut)->format('d/m/Y') }})
                                </span>
                            @endif
                        @endif
                    </span>
                @endif
            </div>
            <!-- Actions globales -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('manchettes.corbeille') }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                    <em class="mr-1 icon ni ni-trash-alt"></em>
                    Corbeille
                </a>
            </div>
        </div>
    </div>

    <!-- NOUVEAU : Alerte d'information de session -->
    @if(isset($sessionInfo) && is_array($sessionInfo) && ($sessionInfo['message'] ?? ''))
    <div class="mb-4">
        <div class="flex items-center p-4 rounded-lg
            {{ ($sessionInfo['can_add'] ?? false) ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800' : 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800' }}">
            <div class="flex-shrink-0">
                @if($sessionInfo['can_add'] ?? false)
                    <em class="text-blue-400 icon ni ni-info-circle"></em>
                @else
                    <em class="text-red-400 icon ni ni-alert-triangle"></em>
                @endif
            </div>
            <div class="ml-3">
                <p class="text-sm
                    {{ ($sessionInfo['can_add'] ?? false) ? 'text-blue-800 dark:text-blue-200' : 'text-red-800 dark:text-red-200' }}">
                    {{ $sessionInfo['message'] }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Messages d'état -->
    @if($message)
    <div class="mb-4">
        <div class="{{ $messageType === 'success' ? 'bg-green-100 border-green-500 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-200' : 'bg-red-100 border-red-500 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-200' }} px-4 py-3 rounded relative border-l-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    </div>
    @endif

    <!-- NOUVEAU : Alerte si aucune session active -->
    @if(isset($sessionInfo) && is_array($sessionInfo) && !($sessionInfo['can_add'] ?? true))
    <div class="mb-6">
        <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <em class="text-yellow-400 icon ni ni-alert-triangle"></em>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Saisie des manchettes indisponible
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>{{ $sessionInfo['message'] ?? 'Aucune session active' }}</p>
                        <p class="mt-1">Veuillez contacter l'administrateur pour activer une session d'examen.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtres de recherche -->
    @include('livewire.manchette.partials.manchettes-filtres')

    <!-- Liste des manchettes -->
    @include('livewire.manchette.manchettes-table')

    <!-- Modal de saisie de note -->
    @include('livewire.manchette.partials.manchettes-modal')

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de suppression</h3>
            <p class="mb-6 text-gray-700 dark:text-gray-300">
                Êtes-vous sûr de vouloir supprimer cette manchette
                @if($manchetteToDelete)
                (Code: {{ $manchetteToDelete->codeAnonymat->code_complet ?? 'N/A' }},
                Étudiant: {{ $manchetteToDelete->etudiant->matricule ?? 'N/A' }})
                <!-- NOUVEAU : Afficher la session de la manchette -->
                @if(isset($manchetteToDelete->sessionExam))
                de la session {{ $manchetteToDelete->sessionExam->type ?? 'Inconnue' }}
                @endif
                @endif
                ? Cette action est réversible (via la corbeille).
            </p>
            <div class="flex justify-end space-x-3">
                <button wire:click="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="deleteManchette" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
    @endif





@push('styles')
<style>
/* Animation pour la liste des étudiants */
.student-item {
    transition: all 0.2s ease-in-out;
}

.student-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Effet de pulsation pour les étudiants récemment ajoutés */
@keyframes pulse-success {
    0% {
        background-color: rgb(34, 197, 94);
        transform: scale(1);
    }
    50% {
        background-color: rgb(22, 163, 74);
        transform: scale(1.02);
    }
    100% {
        background-color: rgb(34, 197, 94);
        transform: scale(1);
    }
}

.manchette-success {
    animation: pulse-success 0.6s ease-in-out;
}

/* Styles pour le compteur d'étudiants restants */
.students-counter {
    background: linear-gradient(135deg, #3B82F6, #1D4ED8);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

/* Effet hover pour les cartes d'étudiants */
.student-card:hover {
    background: linear-gradient(135deg, #EBF8FF, #DBEAFE);
    border-color: #3B82F6;
}

.dark .student-card:hover {
    background: linear-gradient(135deg, #1E3A8A, #1E40AF);
    border-color: #60A5FA;
}

/* Styles pour le mode sombre */
.dark .students-counter {
    background: linear-gradient(135deg, #1E40AF, #1E3A8A);
    box-shadow: 0 2px 4px rgba(30, 64, 175, 0.3);
}

/* Animation de focus pour les champs de saisie */
input:focus {
    transition: all 0.2s ease-in-out;
    transform: scale(1.01);
}

/* Styles pour les tooltips */
.tooltip {
    position: relative;
}

.tooltip:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    white-space: nowrap;
    z-index: 100;
    opacity: 0;
    animation: fadeIn 0.3s ease-in-out forwards;
}

@keyframes fadeIn {
    to {
        opacity: 1;
    }
}

/* Indicateur de progression */
.progress-indicator {
    height: 4px;
    background: #E5E7EB;
    border-radius: 2px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #10B981, #059669);
    transition: width 0.5s ease-in-out;
    border-radius: 2px;
}

/* Styles pour les raccourcis clavier */
.keyboard-shortcut {
    display: inline-flex;
    align-items: center;
    background: #F3F4F6;
    border: 1px solid #D1D5DB;
    border-radius: 4px;
    padding: 2px 6px;
    font-family: 'Courier New', monospace;
    font-size: 0.75rem;
    color: #6B7280;
}

.dark .keyboard-shortcut {
    background: #374151;
    border-color: #4B5563;
    color: #9CA3AF;
}

/* Animation pour les notifications de succès */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.success-notification {
    animation: slideInRight 0.3s ease-out;
}

/* Styles responsive pour mobile */
@media (max-width: 768px) {
    .student-item {
        padding: 1rem;
    }

    .grid.lg\\:grid-cols-2 {
        grid-template-columns: 1fr;
    }

    .order-1.lg\\:order-2 {
        order: 1;
        margin-bottom: 1rem;
    }

    .order-2.lg\\:order-1 {
        order: 2;
    }
}

/* Indicateur de chargement */
.loading-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush



@push('scripts')
<script>
document.addEventListener('livewire:load', function () {
    // Focus automatique sur le champ de recherche après sauvegarde
    window.livewire.on('focus-search-field', function () {
        setTimeout(function() {
            const searchField = document.getElementById('searchQuery');
            if (searchField) {
                searchField.focus();
                searchField.select();
            }
        }, 200);
    });

    // Focus après sélection rapide d'un étudiant
    window.livewire.on('etudiant-selected-quick', function () {
        setTimeout(function() {
            const submitButton = document.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.focus();
            }
        }, 100);
    });

    // Confirmation avant fermeture si des étudiants restent - CORRIGÉ pour éviter le refresh
    window.livewire.on('confirm-close-modal', function (data) {
        if (confirm(data.message)) {
            // Utiliser Livewire pour fermer au lieu d'un refresh
            @this.call('forceCloseModal');
        }
    });

    // Empêcher le refresh de page sur tous les boutons
    document.addEventListener('click', function(e) {
        const button = e.target.closest('button[wire\\:click]');
        if (button && !button.hasAttribute('type')) {
            // Ajouter type="button" pour éviter la soumission de formulaire
            button.setAttribute('type', 'button');
        }

        // Empêcher spécifiquement les boutons de fermeture de modal
        if (button && (
            button.hasAttribute('wire:click.prevent') ||
            button.textContent.includes('Terminer') ||
            button.textContent.includes('Annuler')
        )) {
            e.preventDefault();
        }
    });

    // Raccourcis clavier pour améliorer l'UX
    document.addEventListener('keydown', function(e) {
        // Échapper pour fermer la modal avec confirmation
        if (e.key === 'Escape') {
            const modal = document.querySelector('[aria-modal="true"]');
            if (modal) {
                e.preventDefault();
                @this.call('closeModalWithConfirmation');
            }
        }

        // Ctrl+Enter pour sauvegarder rapidement
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const modal = document.querySelector('[aria-modal="true"]');
            if (modal) {
                e.preventDefault();
                const submitButton = document.querySelector('button[type="submit"]');
                if (submitButton && !submitButton.disabled) {
                    submitButton.click();
                }
            }
        }
    });

    // Écouter les changements de session
    window.livewire.on('session-changed', function (data) {
        console.log('Session changée:', data);
    });

    // Animation de succès pour la liste des étudiants
    window.livewire.on('manchette-saved-success', function() {
        // Effet visuel de succès
        const studentList = document.querySelector('.space-y-2.overflow-y-auto');
        if (studentList) {
            studentList.style.transform = 'scale(0.98)';
            studentList.style.transition = 'transform 0.2s ease-in-out';
            setTimeout(() => {
                studentList.style.transform = 'scale(1)';
            }, 200);
        }
    });
});

// Support pour Livewire v3 si nécessaire
document.addEventListener('livewire:initialized', function () {
    // Mêmes événements pour Livewire v3
    Livewire.on('focus-search-field', function () {
        setTimeout(function() {
            const searchField = document.getElementById('searchQuery');
            if (searchField) {
                searchField.focus();
                searchField.select();
            }
        }, 200);
    });

    Livewire.on('etudiant-selected-quick', function () {
        setTimeout(function() {
            const submitButton = document.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.focus();
            }
        }, 100);
    });

    Livewire.on('confirm-close-modal', function (data) {
        if (confirm(data[0].message)) {
            // Utiliser la nouvelle méthode Livewire v3
            Livewire.dispatch('forceCloseModal');
        }
    });
});

// Empêcher tous les refresh de page non désirés
document.addEventListener('DOMContentLoaded', function() {
    // Intercepter tous les clics sur les boutons wire:click
    document.addEventListener('click', function(e) {
        const element = e.target.closest('[wire\\:click]');
        if (element && !element.closest('form')) {
            e.preventDefault();
        }
    });

    // Empêcher la soumission accidentelle de formulaires
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.hasAttribute('wire:submit.prevent')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush

</div>
