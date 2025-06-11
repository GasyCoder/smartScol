<div>
    <div class="container px-4 py-6 mx-auto">
    <!-- En-tête fixe avec titre et actions globales -->
    <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal avec indicateur de session -->
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-medium text-slate-700 dark:text-white">Saisie des Notes de Copies</h2>
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
                <a href="{{ route('copies.corbeille') }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                   <em class="mr-1 text-sm icon ni ni-trash-alt"></em>
                    Corbeille
                </a>
            </div>
        </div>
    </div>

    <!-- Messages d'état -->
    @if($message)
    <div class="mb-4">
        <div class="{{ $messageType === 'success' ? 'bg-green-100 border-green-500 text-green-700 dark:bg-green-900 dark:border-green-600 dark:text-green-200' :
                     ($messageType === 'warning' ? 'bg-yellow-100 border-yellow-500 text-yellow-700 dark:bg-yellow-900 dark:border-yellow-600 dark:text-yellow-200' :
                     'bg-red-100 border-red-500 text-red-700 dark:bg-red-900 dark:border-red-600 dark:text-red-200') }} px-4 py-3 rounded relative border-l-4" role="alert">
            <div class="flex items-start">
                @if($messageType === 'success')
                    <svg class="w-5 h-5 mt-0.5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                @elseif($messageType === 'warning')
                    <svg class="w-5 h-5 mt-0.5 mr-2 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                @else
                    <svg class="w-5 h-5 mt-0.5 mr-2 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @endif
                <div class="flex-1">
                    <span class="block sm:inline">{{ $message }}</span>
                    @if($session_exam_id && $currentSessionType)
                        <div class="mt-1 text-xs opacity-75">
                            Session actuelle : {{ $currentSessionType }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Alerte de session si aucune session active -->
    @if(!$session_exam_id)
    <div class="mb-4">
        <div class="px-4 py-3 text-blue-700 bg-blue-100 border-l-4 border-blue-500 dark:bg-blue-900 dark:border-blue-600 dark:text-blue-200" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mt-0.5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="font-medium">Aucune session d'examen active</p>
                    <p class="mt-1 text-sm">Veuillez activer une session d'examen pour pouvoir saisir des notes.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Barre de filtres et contexte actuel -->
    @include('livewire.copie.partials.copies-filtre')

    <!-- Tableau des copies -->
    @include('livewire.copie.copies-table')

    <!-- Section des étudiants sans note - UI/UX améliorée (ANONYME) -->
    @if($ec_id && $ec_id !== 'all' && !empty($etudiantsSansCopies) && $session_exam_id)
    <div class="border-t border-gray-200 dark:border-gray-700">
        <div class="p-4 bg-gray-50 dark:bg-gray-800">
            <div class="flex flex-col mb-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="flex items-center text-base font-medium text-gray-900 dark:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Notes manquantes
                    <span class="ml-2 text-sm font-semibold bg-yellow-100 text-yellow-800 py-0.5 px-2 rounded-full dark:bg-yellow-900 dark:text-yellow-200">
                        {{ count($etudiantsSansCopies) }}
                    </span>
                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                        ({{ $currentSessionType }})
                    </span>
                </h3>

                <!-- Actions pour la saisie anonyme -->
                <div class="flex items-center mt-2 space-x-2 sm:mt-0">
                    <button wire:click="openCopieModal"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none dark:bg-blue-700 dark:hover:bg-blue-800">
                        <em class="mr-1 text-xs icon ni ni-plus"></em>
                        Saisir par code anonymat
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de saisie de note -->
    @include('livewire.copie.partials.copies-modal')

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de suppression</h3>
            <p class="mb-6 text-gray-700 dark:text-gray-300">
                Êtes-vous sûr de vouloir supprimer cette note
                @if($copieToDelete)
                (Code: {{ $copieToDelete->codeAnonymat->code_complet ?? 'N/A' }},
                Note: {{ number_format($copieToDelete->note, 2) }}/20)
                @endif
                de la session {{ $currentSessionType }} ? Cette action est réversible (via la corbeille).
            </p>
            <div class="flex justify-end space-x-3">
                <button wire:click="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="confirmDeleteCopie" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        // Dropdown de changement de session
        function toggleSessionDropdown() {
            const dropdown = document.getElementById('sessionDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Fermer le dropdown en cliquant ailleurs
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('sessionDropdown');
            const button = event.target.closest('button[onclick="toggleSessionDropdown()"]');

            if (!button && dropdown && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Toggle de la liste des étudiants
        function toggleEtudiantsList() {
            const list = document.getElementById('etudiantsList');
            if (list) {
                list.classList.toggle('hidden');
            }
        }

        document.addEventListener('livewire:init', function() {
            // Focus automatique sur le champ note
            Livewire.on('focus-note-field', function() {
                setTimeout(function() {
                    const noteField = document.getElementById('note');
                    if (noteField) {
                        noteField.focus();
                        noteField.select(); // Sélectionne tout le texte
                    }
                }, 100);
            });

            // Soumettre le formulaire avec la touche Entrée
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && document.getElementById('note') === document.activeElement) {
                    e.preventDefault();
                    // Trouver le bouton de soumission et le cliquer
                    const submitButton = document.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.click();
                    }
                }
            });

            // Fermer automatiquement les alertes après 5 secondes
            setTimeout(function() {
                const alerts = document.querySelectorAll('[role="alert"]');
                alerts.forEach(function(alert) {
                    if (alert.closest('.mb-4')) {
                        alert.closest('.mb-4').style.transition = 'opacity 0.5s ease-out';
                        alert.closest('.mb-4').style.opacity = '0';
                        setTimeout(function() {
                            if (alert.closest('.mb-4')) {
                                alert.closest('.mb-4').remove();
                            }
                        }, 500);
                    }
                });
            }, 5000);
        });

        // Écouter les changements de session via Livewire
        document.addEventListener('livewire:init', function() {
            Livewire.on('session-changed', function(data) {
                // Fermer le dropdown
                const dropdown = document.getElementById('sessionDropdown');
                if (dropdown) {
                    dropdown.classList.add('hidden');
                }

                // Afficher une notification
                if (data && data.sessionType) {
                    // Créer une notification temporaire
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 z-50 px-4 py-2 bg-blue-600 text-white rounded-lg shadow-lg';
                    notification.innerHTML = `Session changée vers : ${data.sessionType}`;
                    document.body.appendChild(notification);

                    setTimeout(function() {
                        notification.remove();
                    }, 3000);
                }
            });
        });
    </script>
    @endpush
</div>
</div>
