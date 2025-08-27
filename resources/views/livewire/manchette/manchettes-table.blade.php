        <!-- Tableau -->
        <div class="bg-white shadow rounded-lg overflow-hidden dark:bg-gray-800">
            <!-- Header du tableau -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 dark:bg-gray-900 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Liste des manchettes
                        @if($manchettes->total() > 0)
                            <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ $manchettes->total() }})</span>
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
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
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
                                    <button wire:click="editManchette({{ $manchette->id }})"
                                            class="p-1 text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        <em class="ni ni-edit"></em>
                                    </button>
                                    @endcan 
                                    @can('manchettes.delete')
                                    <button wire:click="confirmDelete({{ $manchette->id }})" 
                                        wire:key="manchette-;{{ $manchette->id }}"
                                            class="p-1 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        <em class="ni ni-trash"></em>
                                    </button>
                                    @endcan 
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
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