{{-- Vue simplifiée des manchettes --}}
<div>
    <!-- Header -->
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Liste des Manchettes</h1>
                    @if($currentSessionType)
                        <span class="px-3 py-1 text-sm font-medium rounded-full
                            {{ $currentSessionType === 'Normale'
                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
                            }}">
                            Session {{ $currentSessionType }}
                        </span>
                    @endif
                </div>
                <div class="flex items-center space-x-3">
                    <button wire:click="resetFilters" 
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        <em class="mr-2 ni ni-reload"></em>
                        Réinitialiser
                    </button>
                    <a href="{{ route('manchettes.corbeille') }}" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        <em class="mr-2 ni ni-trash-alt"></em>
                        Corbeille
                    </a>
                </div>
            </div>
        </div>

    <!-- Contenu principal -->
    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Filtres compacts -->
        <div class="bg-white rounded-lg shadow mb-6 dark:bg-gray-800">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 dark:text-white">Filtres</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    
                    <!-- Niveau -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">Niveau</label>
                        <select wire:model.live="niveau_id" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Tous les niveaux</option>
                            @foreach($niveaux as $niveau)
                                <option value="{{ $niveau->id }}">{{ $niveau->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Parcours -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">Parcours</label>
                        <select wire:model.live="parcours_id" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                {{ (is_object($parcours) && $parcours->isEmpty()) || (is_array($parcours) && empty($parcours)) ? 'disabled' : '' }}>
                            <option value="">Tous les parcours</option>
                            @if((is_object($parcours) && $parcours->isNotEmpty()) || (is_array($parcours) && !empty($parcours)))
                                @foreach($parcours as $parcour)
                                    <option value="{{ $parcour->id }}">{{ $parcour->nom }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Matière -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">ECS</label>
                        <select wire:model.live="ec_id" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                {{ (is_object($ecs) && $ecs->isEmpty()) || (is_array($ecs) && empty($ecs)) ? 'disabled' : '' }}>
                            <option value="">Toutes les ECS</option>
                            @if((is_object($ecs) && $ecs->isNotEmpty()) || (is_array($ecs) && !empty($ecs)))
                                @foreach($ecs as $ec)
                                    <option value="{{ $ec->id }}">{{ $ec->nom }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <!-- NOUVEAU : Filtre Secrétaire -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">Secrétaire</label>
                        <select wire:model.live="saisie_par" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Tous les secrétaires</option>
                            @foreach($secretaires as $secretaire)
                                <option value="{{ $secretaire->id }}">{{ $secretaire->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Recherche -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">Recherche</label>
                        <div class="relative">
                            <input wire:model.live.debounce.300ms="search" 
                                   type="text" 
                                   placeholder="Code, nom, matricule..."
                                   class="w-full pl-10 pr-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                <em class="ni ni-search text-gray-400"></em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('livewire.manchette.manchettes-table')
    </div>

    <!-- Modal Modification -->
    @if($showEditModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-50" wire:click="cancelEdit"></div>
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full relative dark:bg-gray-800">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Modifier la manchette</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">Code d'anonymat</label>
                            <input wire:model="code_anonymat" type="text" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('code_anonymat') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">Étudiant ID</label>
                            <input wire:model="etudiant_id" type="number" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('etudiant_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button wire:click="cancelEdit" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-700">
                            Annuler
                        </button>
                        <button wire:click="updateManchette" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Suppression -->
    @if($showDeleteModal && $manchetteToDelete)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-50" wire:click="cancelDelete"></div>
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full relative dark:bg-gray-800">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0 w-10 h-10 mx-auto bg-red-100 rounded-full flex items-center justify-center dark:bg-red-900">
                            <em class="ni ni-alert-fill text-red-600 dark:text-red-400"></em>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Confirmer la suppression</h3>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-500 mb-6 dark:text-gray-400">
                        Supprimer la manchette <strong>{{ $manchetteToDelete->codeAnonymat->code_complet }}</strong> 
                        de <strong>{{ $manchetteToDelete->etudiant->nom }} {{ $manchetteToDelete->etudiant->prenom }}</strong> ?
                    </p>
                    
                    <div class="flex justify-end space-x-3">
                        <button wire:click="cancelDelete" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-700">
                            Annuler
                        </button>
                        <button wire:click="deleteManchette" 
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>