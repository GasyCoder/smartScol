<!-- Vue Principale RENFORCÉE -->
<div>
    <div class="container px-4 py-6 mx-auto">
        <!-- Vérification initiale des données -->
  @unless($session_exam_id && $currentSessionType)
            <div class="mb-4">
                <div class="px-4 py-3 text-red-700 bg-red-100 border-l-4 border-red-500 rounded-r-lg dark:bg-red-900 dark:border-red-600 dark:text-red-200" role="alert">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mt-0.5 mr-2 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium">Erreur de chargement</p>
                            <p class="mt-1 text-sm">Les données de session ne sont pas disponibles. Veuillez actualiser la page ou sélectionner une session.</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- En-tête fixe avec titre et actions globales AMÉLIORÉ -->
            <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800 rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <!-- Titre principal avec indicateurs de session et sécurité -->
                    <div class="flex flex-wrap items-center gap-3 min-w-0">
                        <h2 class="text-xl font-medium text-slate-700 dark:text-white truncate">Saisie des Notes de Copies</h2>
                        
                        <!-- Badge de session -->
                        @if($currentSessionType)
                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full whitespace-nowrap
                                {{ $currentSessionType === 'Normale'
                                    ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200 dark:border-green-700'
                                    : 'bg-orange-100 text-orange-800 border border-orange-200 dark:bg-orange-900 dark:text-orange-200 dark:border-orange-700'
                                }}">
                                Session {{ $currentSessionType }}
                                @if($sessionActive && $sessionActive->date_debut)
                                    <span class="ml-1 text-xs opacity-75">
                                        ({{ \Carbon\Carbon::parse($sessionActive->date_debut)->format('d/m/Y') }})
                                    </span>
                                @endif
                            </span>
                        @endif

                        <!-- Badge de double vérification -->
                        @if($enableDoubleVerification)
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full border border-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:border-blue-700 whitespace-nowrap">
                                <svg class="w-4 h-4 mr-1 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Protection activée
                            </span>
                        @endif
                    </div>

                    <!-- Actions globales CORRIGÉES -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Bouton de configuration de sécurité -->
                        <div class="relative">
                            <button onclick="toggleSecurity()" 
                                    class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 whitespace-nowrap
                                    {{ $enableDoubleVerification ? 'ring-2 ring-blue-500 ring-opacity-50' : '' }}">
                                <svg class="w-4 h-4 mr-1 text-gray-600 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Sécurité
                                @if($enableDoubleVerification)
                                    <span class="ml-1 w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></span>
                                @endif
                            </button>

                            <!-- Menu déroulant de sécurité CORRIGÉ -->
                            <div id="security" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800 z-20 max-w-xs">
                                <div class="py-1">
                                    <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700">
                                        <div class="font-medium truncate">Configuration de sécurité</div>
                                    </div>
                                    
                                    <label class="flex items-start px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 cursor-pointer">
                                        <input type="checkbox" 
                                               wire:model.live="enableDoubleVerification" 
                                               class="w-4 h-4 mt-0.5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 flex-shrink-0">
                                        <div class="ml-3 min-w-0">
                                            <div class="font-medium">Double vérification</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Saisir chaque donnée deux fois</div>
                                        </div>
                                    </label>

                                    <!-- Auto-ouverture modal -->
                                    <label class="flex items-start px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 cursor-pointer">
                                        <input type="checkbox" 
                                            wire:model.live="autoOpenModal" 
                                            class="w-4 h-4 mt-0.5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 flex-shrink-0">
                                        <div class="ml-3 min-w-0">
                                            <div class="font-medium">Auto-ouverture</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Ouvrir automatiquement la saisie</div>
                                        </div>
                                    </label>

                                    <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span>Raccourcis:</span>
                                            <div class="flex items-center gap-1">
                                                <kbd class="px-1 py-0.5 text-xs bg-gray-100 rounded dark:bg-gray-700">Ctrl+R</kbd>
                                                <span class="text-xs">Toggle</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <kbd class="px-1 py-0.5 text-xs bg-gray-100 rounded dark:bg-gray-700">F1</kbd>
                                                <span class="text-xs">Aide</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bouton corbeille -->
                        <a href="{{ route('copies.corbeille') }}" 
                           class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 whitespace-nowrap">
                           <svg class="w-4 h-4 mr-1 text-gray-600 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                           </svg>
                            Corbeille
                        </a>

                        <!-- Bouton aide rapide -->
                        <button onclick="showQuickHelp()" 
                                class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 whitespace-nowrap">
                            <svg class="w-4 h-4 mr-1 text-gray-600 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Aide
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages d'état CORRIGÉS -->
            @if($message)
            <div class="mb-4">
                <div class="{{ $messageType === 'success' ? 'bg-green-100 border-green-500 text-green-700 dark:bg-green-900 dark:border-green-600 dark:text-green-200' :
                             ($messageType === 'warning' ? 'bg-yellow-100 border-yellow-500 text-yellow-700 dark:bg-yellow-900 dark:border-yellow-600 dark:text-yellow-200' :
                             'bg-red-100 border-red-500 text-red-700 dark:bg-red-900 dark:border-red-600 dark:text-red-200') }} px-4 py-3 rounded-r-lg relative border-l-4" role="alert">
                    <div class="flex items-start">
                        @if($messageType === 'success')
                            <svg class="w-5 h-5 mt-0.5 mr-2 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @elseif($messageType === 'warning')
                            <svg class="w-5 h-5 mt-0.5 mr-2 text-yellow-600 dark:text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5 mt-0.5 mr-2 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @endif
                        <div class="flex-1 min-w-0">
                            <span class="block sm:inline break-words">{{ $message }}</span>
                            @if($session_exam_id && $currentSessionType)
                                <div class="mt-1 text-xs opacity-75">
                                    Session actuelle : {{ $currentSessionType }}
                                    @if($enableDoubleVerification)
                                        • Mode sécurisé activé
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Reste du contenu avec corrections de responsive -->
            <div class="space-y-6">
                <!-- Barre de filtres et contexte actuel -->
                <div class="overflow-hidden">
                    @include('livewire.copie.partials.copies-filtre')
                </div>

                <!-- Tableau des copies -->
                <div class="overflow-hidden">
                    @include('livewire.copie.copies-table')
                </div>

                <!-- Section des étudiants sans note -->
                @if($ec_id && $ec_id !== 'all' && !empty($etudiantsSansCopies) && $session_exam_id)
                <div class="border-t border-gray-200 dark:border-gray-700 overflow-hidden rounded-lg">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="flex flex-wrap items-center gap-2 text-base font-medium text-gray-900 dark:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Notes manquantes</span>
                                <span class="text-sm font-semibold bg-yellow-100 text-yellow-800 py-0.5 px-2 rounded-full dark:bg-yellow-900 dark:text-yellow-200 whitespace-nowrap">
                                    {{ count($etudiantsSansCopies) }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    ({{ $currentSessionType }})
                                </span>
                                @if($enableDoubleVerification)
                                    <span class="inline-flex items-center text-xs text-blue-600 dark:text-blue-400 whitespace-nowrap">
                                        <svg class="w-4 h-4 mr-1 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Mode sécurisé
                                    </span>
                                @endif
                            </h3>

                            <!-- Actions pour la saisie anonyme -->
                            <div class="flex flex-wrap items-center gap-2">
                                @if(!$enableDoubleVerification)
                                    <button wire:click="$set('enableDoubleVerification', true)" 
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 rounded hover:bg-blue-200 focus:outline-none dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800 whitespace-nowrap">
                                        <svg class="w-4 h-4 mr-1 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Activer la sécurité
                                    </button>
                                @endif
                                
                                <button wire:click="openCopieModal"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white rounded focus:outline-none whitespace-nowrap
                                        {{ $enableDoubleVerification 
                                            ? 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800' 
                                            : 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800' 
                                        }}">
                                    <svg class="w-4 h-4 mr-1 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Saisir {{ $enableDoubleVerification ? '(Sécurisé)' : 'par code' }}
                                </button>
                            </div>
                        </div>

                        <!-- Indicateur de mode de saisie CORRIGÉ -->
                        <div class="flex flex-wrap items-center justify-between gap-2 mt-3 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex flex-wrap items-center gap-2">
                                <span>Mode actuel:</span>
                                <div class="flex items-center gap-1">
                                    <div class="w-2 h-2 {{ $enableDoubleVerification ? 'bg-blue-500' : 'bg-gray-400' }} rounded-full flex-shrink-0"></div>
                                    <span class="{{ $enableDoubleVerification ? 'text-blue-600 dark:text-blue-400 font-medium' : '' }} whitespace-nowrap">
                                        {{ $enableDoubleVerification ? 'Double vérification activée' : 'Saisie rapide' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2">
                                <span>Raccourcis:</span>
                                <kbd class="px-1 py-0.5 text-xs bg-gray-100 rounded dark:bg-gray-700">Ctrl+R</kbd>
                                <span>Toggle sécurité</span>
                                <kbd class="px-1 py-0.5 text-xs bg-gray-100 rounded dark:bg-gray-700">F2</kbd>
                                <span>OCR</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Modal de saisie de note -->
            @include('livewire.copie.partials.copies-modal')

            <!-- Modal de confirmation de suppression CORRIGÉE -->
            @if($showDeleteModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 p-4">
                <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de suppression</h3>
                    <div class="mb-6 text-gray-700 dark:text-gray-300 break-words">
                        Êtes-vous sûr de vouloir supprimer cette note
                        @if($copieToDelete)
                        <div class="mt-2 p-2 bg-gray-50 dark:bg-gray-700 rounded text-sm">
                            <strong>Code:</strong> {{ $copieToDelete->codeAnonymat->code_complet ?? 'N/A' }}<br>
                            <strong>Note:</strong> {{ number_format($copieToDelete->note, 2) }}/20
                        </div>
                        @endif
                        de la session {{ $currentSessionType }} ? Cette action est réversible (via la corbeille).
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
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
        @endunless
    </div>
</div>