{{-- Vue avec la vraie logique correcte --}}
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
                    
                    {{-- Statistiques correctes --}}
                    @if($sessionInfo['show_both'] && $sessionStats)
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full dark:bg-purple-900 dark:text-purple-200">
                                Étudiants rattrapage: {{ $sessionStats['etudiants_eligibles'] }}
                            </span>
                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full dark:bg-green-900 dark:text-green-200">
                                ECs validées: {{ $sessionStats['normale'] }}
                            </span>
                            <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full dark:bg-orange-900 dark:text-orange-200">
                                Rattrapage: {{ $sessionStats['rattrapage'] }}
                            </span>
                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                Total: {{ $sessionStats['total'] }}
                            </span>
                        </div>
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    
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
                    
                    <!-- Secrétaire -->
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
                    
                    {{-- Filtre par type de session (corrigé) --}}
                    @if($sessionInfo['show_both'])
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">Session</label>
                        <select wire:model.live="sessionFilter" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="all">Toutes les sessions</option>
                            <option value="normale">ECs validées uniquement</option>
                            <option value="rattrapage">Rattrapage uniquement</option>
                        </select>
                    </div>
                    @endif
                    
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

        <!-- Tableau avec logique correcte -->
        <div class="bg-white shadow rounded-lg overflow-hidden dark:bg-gray-800">
            <!-- Header du tableau -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 dark:bg-gray-900 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Liste des manchettes
                        @if($manchettes->total() > 0)
                            <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ $manchettes->total() }})</span>
                        @endif
                        
                        {{-- Indicateur corrigé --}}
                        @if($sessionInfo['show_both'])
                            <span class="ml-3 text-sm text-gray-600 dark:text-gray-300">
                                - Affichage: 
                                @if($sessionFilter === 'normale')
                                    <span class="font-semibold text-green-600 dark:text-green-400">ECs validées uniquement</span>
                                @elseif($sessionFilter === 'rattrapage')
                                    <span class="font-semibold text-orange-600 dark:text-orange-400">Rattrapage uniquement</span>
                                @else
                                    <span class="font-semibold text-blue-600 dark:text-blue-400">ECs validées + Rattrapage</span>
                                @endif
                            </span>
                        @endif
                    </h3>
                    <div class="flex items-center space-x-2">
                        <select wire:model.live="perPage" class="text-sm rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Corps du tableau -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th wire:click="sortBy('code_anonymat_id')" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                <div class="flex items-center space-x-1">
                                    <span>Code</span>
                                    @if($sortField === 'code_anonymat_id')
                                        <em class="ni ni-{{ $sortDirection === 'asc' ? 'bold-up' : 'bold-down' }} text-blue-500"></em>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('etudiant_id')" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                <div class="flex items-center space-x-1">
                                    <span>Étudiant</span>
                                    @if($sortField === 'etudiant_id')
                                        <em class="ni ni-{{ $sortDirection === 'asc' ? 'bold-up' : 'bold-down' }} text-blue-500"></em>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">ECS</th>
                            
                            {{-- Colonne Session (avec vraie logique) --}}
                            @if($sessionInfo['show_both'])
                            <th wire:click="sortBy('session_exam_id')" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                <div class="flex items-center space-x-1">
                                    <span>Session</span>
                                    @if($sortField === 'session_exam_id')
                                        <em class="ni ni-{{ $sortDirection === 'asc' ? 'bold-up' : 'bold-down' }} text-blue-500"></em>
                                    @endif
                                </div>
                            </th>
                            @endif
                            
                            <th wire:click="sortBy('saisie_par')" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                <div class="flex items-center space-x-1">
                                    <span>Saisi par</span>
                                    @if($sortField === 'saisie_par')
                                        <em class="ni ni-{{ $sortDirection === 'asc' ? 'bold-up' : 'bold-down' }} text-blue-500"></em>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('created_at')" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                <div class="flex items-center space-x-1">
                                    <span>Date</span>
                                    @if($sortField === 'created_at')
                                        <em class="ni ni-{{ $sortDirection === 'asc' ? 'bold-up' : 'bold-down' }} text-blue-500"></em>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700 dark:divide-gray-600">
                        @forelse($manchettes as $manchette)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-600 
                            {{-- Couleur selon session avec vraie logique --}}
                            @if($sessionInfo['show_both'])
                                {{ $manchette->sessionExam->type === 'Normale' 
                                    ? 'bg-green-50 dark:bg-green-900/10 hover:bg-green-100 dark:hover:bg-green-800/20' 
                                    : 'bg-orange-50 dark:bg-orange-900/10 hover:bg-orange-100 dark:hover:bg-orange-800/20' 
                                }}
                            @endif
                        ">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                    {{ $manchette->codeAnonymat->code_complet ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center mr-3 dark:bg-gray-600">
                                        <em class="ni ni-user text-gray-500 dark:text-gray-300"></em>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $manchette->etudiant->nom ?? '' }} {{ $manchette->etudiant->prenom ?? '' }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $manchette->etudiant->matricule ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $manchette->codeAnonymat->ec->nom ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $manchette->codeAnonymat->ec->ue->abr ?? '' }}</div>
                            </td>
                            
                            {{-- Colonne Session (avec vraie logique) --}}
                            @if($sessionInfo['show_both'])
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $manchette->sessionExam->type === 'Normale' 
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                        : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' 
                                    }}">
                                    {{ $manchette->sessionExam->type === 'Normale' ? 'V' : 'R' }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1 dark:text-gray-400">
                                    {{ $manchette->sessionExam->type === 'Normale' ? 'Validée' : 'Rattrapage' }}
                                </div>
                            </td>
                            @endif
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-6 w-6 rounded-full bg-green-100 flex items-center justify-center mr-2 dark:bg-green-900">
                                        <em class="ni ni-user-check text-green-600 text-xs dark:text-green-300"></em>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $manchette->utilisateurSaisie->name ?? 'Inconnu' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            Secrétaire
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $manchette->created_at->format('d/m/Y H:i') }}</div>
                                @if($manchette->updated_at && $manchette->updated_at->ne($manchette->created_at))
                                    <div class="text-xs text-orange-500 dark:text-orange-400">
                                        Modifié le {{ $manchette->updated_at->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    @can('manchettes.edit')
                                    <button wire:click="editManchette({{ $manchette->id }})" wire:key="manchetteEdit-{{ $manchette->id }}"
                                            class="p-1 text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="Modifier">
                                        <em class="ni ni-edit"></em>
                                    </button>
                                    @endcan 
                                    @can('manchettes.delete')
                                    {{-- Logique de suppression corrigée : désactiver pour session normale --}}
                                    @if(!$sessionInfo['show_both'] || $manchette->sessionExam->type === 'Rattrapage')
                                        <button wire:click="confirmDelete({{ $manchette->id }})" 
                                            wire:key="manchetteDelete-{{ $manchette->id }}"
                                                class="p-1 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="Supprimer">
                                            <em class="ni ni-trash"></em>
                                        </button>
                                    @else
                                        <span class="p-1 text-gray-400 cursor-not-allowed" title="Suppression non autorisée (EC validée)">
                                            <em class="ni ni-lock"></em>
                                        </span>
                                    @endif
                                    @endcan 
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $sessionInfo['show_both'] ? '7' : '6' }}" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <em class="ni ni-file-docs text-4xl text-gray-300 mb-4 dark:text-gray-600"></em>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2 dark:text-white">Aucune manchette</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @if($search)
                                            Aucun résultat pour "{{ $search }}"
                                        @else
                                            Sélectionnez des critères pour afficher les manchettes
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($manchettes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $manchettes->links() }}
            </div>
            @endif
        </div>
        
        {{-- Informations contextuelles avec logique correcte --}}
        @if($sessionInfo['show_both'])
        <div class="mt-4 bg-white rounded-lg shadow p-4 dark:bg-gray-800">
            @if($manchettes->count() > 0)
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-2 dark:text-white">Légende:</h4>
                <div class="flex items-center space-x-6 text-sm">
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-green-100 rounded border dark:bg-green-900/20"></div>
                        <span class="text-gray-600 dark:text-gray-300">ECs validées (UE validée en session normale)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-orange-100 rounded border dark:bg-orange-900/20"></div>
                        <span class="text-gray-600 dark:text-gray-300">ECs en rattrapage (UE non validée)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <em class="ni ni-lock text-gray-400"></em>
                        <span class="text-gray-500 dark:text-gray-400">Actions limitées sur ECs validées</span>
                    </div>
                </div>
            </div>
            @else
                <div class="text-center py-4">
                    <em class="ni ni-check-circle text-4xl text-green-400 mb-2"></em>
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">Aucun étudiant en rattrapage</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Tous les étudiants concernés ont été validés en session normale.
                    </p>
                </div>
            @endif
        </div>
        @endif
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
                        <br><span class="text-xs text-gray-400 mt-1">
                            (Session {{ $manchetteToDelete->sessionExam->type ?? 'inconnue' }})
                        </span>
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