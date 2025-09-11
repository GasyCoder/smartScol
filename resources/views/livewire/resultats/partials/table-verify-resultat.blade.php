{{-- livewire.fusion.partials.table-verify-resultat --}}
<div class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-md dark:bg-gray-900 dark:border-gray-700 transition-all duration-200 hover:shadow-lg">
    <!-- Header amélioré -->
    <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-800 dark:to-blue-900/20 dark:border-gray-600">
        <div class="flex items-center justify-between">
            <h3 class="flex items-center text-sm font-semibold text-gray-900 dark:text-white">
                <em class="mr-2 text-blue-600 icon ni ni-file-docs"></em>
                Résultats des Étudiants
                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                    {{ $totalResultats }} résultats - {{ $pourcentageVerification }}% vérifiés
                </span>
            </h3>
            @if($afficherMoyennesUE)
                <span class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full shadow-sm dark:bg-blue-900 dark:text-blue-200 border border-blue-200 dark:border-blue-700">
                    <em class="mr-1 icon ni ni-bar-chart"></em>Mode UE Activé
                </span>
            @endif
        </div>
    </div>

    <!-- Table responsive améliorée -->
    <div class="overflow-x-auto bg-white dark:bg-gray-900">
        <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 sticky top-0 z-10">
                <tr class="border-b border-gray-200 dark:border-gray-600">
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase tracking-wider dark:text-gray-300">
                        <div class="flex items-center space-x-1">
                            <em class="icon ni ni-hash"></em>
                            <span>N°</span>
                        </div>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase tracking-wider dark:text-gray-300">
                        <button wire:click="toggleOrder('matricule')" 
                                class="group flex items-center space-x-1 hover:text-blue-600 transition-colors duration-200 {{ $orderBy === 'matricule' ? 'text-blue-600' : '' }}">
                            <em class="icon ni ni-card-view"></em>
                            <span>Matricule</span>
                            @if($orderBy === 'matricule')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1 text-blue-500"></em>
                            @endif
                        </button>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase tracking-wider dark:text-gray-300">
                        <button wire:click="toggleOrder('nom')" 
                                class="group flex items-center space-x-1 hover:text-blue-600 transition-colors duration-200 {{ $orderBy === 'nom' ? 'text-blue-600' : '' }}">
                            <em class="icon ni ni-user"></em>
                            <span>Nom</span>
                            @if($orderBy === 'nom')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1 text-blue-500"></em>
                            @endif
                        </button>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase tracking-wider dark:text-gray-300">
                        <button wire:click="toggleOrder('prenom')" 
                                class="group flex items-center space-x-1 hover:text-blue-600 transition-colors duration-200 {{ $orderBy === 'prenom' ? 'text-blue-600' : '' }}">
                            <em class="icon ni ni-user-circle"></em>
                            <span>Prénom</span>
                            @if($orderBy === 'prenom')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1 text-blue-500"></em>
                            @endif
                        </button>
                    </th>
                    @if($afficherMoyennesUE)
                        <th class="px-3 py-2 text-xs font-semibold text-center text-gray-700 uppercase tracking-wider dark:text-gray-300">
                            <div class="flex items-center justify-center space-x-1">
                                <em class="icon ni ni-bar-chart-alt text-blue-600"></em>
                                <span>Moy. UE</span>
                            </div>
                        </th>
                    @endif
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase tracking-wider dark:text-gray-300">
                        <div class="flex items-center space-x-1">
                            <em class="icon ni ni-book"></em>
                            <span>UE / EC</span>
                        </div>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase tracking-wider dark:text-gray-300">
                        <div class="flex items-center space-x-1">
                            <em class="icon ni ni-user-circle-o"></em>
                            <span>Enseignant</span>
                        </div>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold text-center text-gray-700 uppercase tracking-wider dark:text-gray-300">
                        <button wire:click="toggleOrder('note')" 
                                class="group flex items-center justify-center space-x-1 hover:text-blue-600 transition-colors duration-200 {{ $orderBy === 'note' ? 'text-blue-600' : '' }}">
                            <em class="icon ni ni-property"></em>
                            <span>Note/20</span>
                            @if($orderBy === 'note')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1 text-blue-500"></em>
                            @endif
                        </button>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold text-center text-gray-700 uppercase tracking-wider dark:text-gray-300">
                        <div class="flex items-center justify-center space-x-1">
                            <em class="icon ni ni-setting"></em>
                            <span>Actions</span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 dark:bg-gray-900 dark:divide-gray-800">
                @php
                    $resultatsByStudent = collect($resultats)->groupBy('matricule');
                    $index = 0;
                @endphp
                @forelse($resultatsByStudent as $matricule => $resultatGroup)
                    @php
                        $index++;
                        $firstResultat = $resultatGroup->first();
                        $resultatsByUE = $resultatGroup->groupBy('ue_nom');
                        $ueIndex = 0;
                    @endphp
                    
                    <!-- Ligne en-tête étudiant améliorée -->
                    <tr class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 font-medium border-l-4 border-blue-500 hover:from-blue-100 hover:to-indigo-100 dark:hover:from-blue-900/40 dark:hover:to-indigo-900/40 transition-all duration-200">
                        <td class="px-3 py-2">
                            <div class="flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-gradient-to-br from-blue-500 to-blue-600 rounded-full shadow-sm">
                                {{ $index }}
                            </div>
                        </td>
                        <td class="px-3 py-2">
                            <span class="inline-flex items-center px-2 py-1 text-xs font-bold text-blue-800 bg-blue-100 rounded-md shadow-sm dark:bg-blue-900 dark:text-blue-200 border border-blue-200 dark:border-blue-700">
                                <em class="mr-1 icon ni ni-card-view"></em>
                                {{ $matricule }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-sm font-bold text-gray-900 dark:text-gray-100">
                            {{ $firstResultat['nom'] }}
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">
                            {{ $firstResultat['prenom'] }}
                        </td>
                        @if($afficherMoyennesUE)
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-purple-800 bg-purple-100 rounded-full shadow-sm dark:bg-purple-900 dark:text-purple-200">
                                    <em class="mr-1 icon ni ni-users"></em>
                                    {{ count($resultatsByUE) }} UE
                                </span>
                            </td>
                        @endif
                        <td colspan="{{ $afficherMoyennesUE ? 4 : 4 }}" class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex items-center space-x-2">
                                <em class="icon ni ni-list text-blue-600"></em>
                                <strong>Résultats par UE et EC :</strong>
                                <span class="text-xs bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">
                                    {{ count($resultatGroup) }} EC au total
                                </span>
                            </div>
                        </td>
                    </tr>

                    <!-- Lignes UE/EC améliorées -->
                    @foreach($resultatsByUE as $ueNom => $ecGroup)
                        @php 
                            $ueIndex++; 
                            // Calcul de la moyenne UE (selon votre logique originale)
                            $notesUE = $ecGroup->pluck('note')->filter(function($note) {
                                return $note !== null && is_numeric($note);
                            });
                            $moyenneUE = $notesUE->isNotEmpty() ? $notesUE->avg() : null;
                            $hasZero = $ecGroup->contains('note', 0);
                            if ($hasZero) $moyenneUE = 0; // Note éliminatoire
                            
                            $ueCredits = $ecGroup->first()['ue_credits'] ?? 0;
                            $ueValidee = $moyenneUE !== null && $moyenneUE >= 10 && !$hasZero;
                        @endphp
                        
                        @foreach($ecGroup as $indexEC => $resultat)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200 group {{ $resultat['is_checked'] ? 'bg-green-50 dark:bg-green-900/20 border-l-2 border-green-400' : 'border-l-2 border-transparent' }}">
                                <td class="px-3 py-2"></td>
                                <td class="px-3 py-2"></td>
                                <td class="px-3 py-2"></td>
                                <td class="px-3 py-2"></td>
                                
                                @if($afficherMoyennesUE)
                                    <td class="px-3 py-2 text-center">
                                        @if($indexEC === 0 && $moyenneUE !== null)
                                            <div class="flex flex-col items-center space-y-1">
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full shadow-sm {{ $ueValidee ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 border border-red-200 dark:bg-red-900 dark:text-red-200' }}">
                                                    <em class="mr-1 icon ni ni-{{ $ueValidee ? 'check-circle' : 'cross-circle' }}"></em>
                                                    {{ number_format($moyenneUE, 2) }}
                                                </span>
                                                @if($hasZero)
                                                    <span class="text-xs text-red-600 dark:text-red-400 font-medium">
                                                        <em class="icon ni ni-alert-circle"></em> Éliminatoire
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                @endif
                                
                                <!-- UE/EC avec meilleur design -->
                                    <td class="px-3 py-2">
                                    @if($indexEC === 0)
                                        <div class="mb-2 p-2 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <!-- Utiliser l'abréviation UE au lieu de l'index -->
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-bold text-blue-800 bg-blue-100 rounded-md dark:bg-blue-900 dark:text-blue-200">
                                                        {{ $resultat['ue_abr'] ?? 'UE' }}
                                                    </span>
                                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $ueNom ?? 'UE N/A' }}
                                                    </span>
                                                </div>
                                                <span class="inline-flex items-center px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded dark:bg-gray-700 dark:text-gray-400">
                                                    <em class="mr-1 icon ni ni-coins"></em>
                                                    {{ $ueCredits }} crédits
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                     <div class="pl-4 py-1 border-l-2 border-gray-200 dark:border-gray-600 ml-2">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium text-purple-700 bg-purple-100 rounded dark:bg-purple-900 dark:text-purple-200">
                                                EC{{ $indexEC + 1 }}
                                            </span>
                                            <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                                {{ $resultat['matiere'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-3 py-2">
                                    <div class="flex items-center space-x-2">
                                        <em class="icon ni ni-user-circle-o text-gray-400"></em>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $resultat['enseignant'] ?? 'N/A' }}
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Note avec design amélioré -->
                                <td class="px-3 py-2 text-center">
                                    <div class="flex flex-col items-center space-y-1">
                                        @if($editingRow === $resultat['unique_key'])
                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    wire:model.live="newNote"
                                                    step="0.01"
                                                    min="0"
                                                    max="20"
                                                    class="w-20 px-3 py-2 text-sm font-semibold text-center text-gray-900 bg-white border-2 border-blue-300 rounded-lg shadow-sm dark:text-white dark:bg-gray-700 dark:border-blue-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                                    placeholder="{{ number_format($resultat['note'], 2) }}"
                                                    aria-label="Modifier la note pour {{ $resultat['matiere'] }}"
                                                    autofocus
                                                />
                                                @if($newNote && $newNote != $resultat['note'])
                                                    <div class="absolute -right-6 top-1/2 transform -translate-y-1/2">
                                                        @if($newNote >= 0 && $newNote <= 20)
                                                            <em class="text-sm text-green-500 icon ni ni-check-circle"></em>
                                                        @else
                                                            <em class="text-sm text-red-500 icon ni ni-alert-circle"></em>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            @if($newNote && $newNote != $resultat['note'])
                                                <div class="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">
                                                    {{ number_format($resultat['note'], 2) }} → {{ number_format($newNote, 2) }}
                                                </div>
                                            @endif
                                        @else
                                            @if($resultat['note_old'])
                                                <div class="relative group">
                                                    <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold rounded-lg cursor-help shadow-sm transition-all duration-200 hover:shadow-md {{ $resultat['note'] >= 10 ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 border border-red-200 dark:bg-red-900 dark:text-red-200' }}">
                                                        {{ number_format($resultat['note'], 2) }}
                                                        @if($resultat['is_checked'])
                                                            <em class="ml-2 text-blue-600 icon ni ni-check-circle"></em>
                                                        @endif
                                                        <em class="ml-1 text-orange-500 icon ni ni-edit-alt" title="Note modifiée"></em>
                                                    </span>
                                                    <!-- Tooltip amélioré -->
                                                    <div class="absolute z-20 px-3 py-2 mb-2 text-xs text-white transition-opacity duration-300 transform -translate-x-1/2 bg-gray-800 rounded-lg shadow-lg opacity-0 pointer-events-none bottom-full left-1/2 dark:bg-gray-700 group-hover:opacity-100 whitespace-nowrap">
                                                        <div class="font-semibold">Note modifiée</div>
                                                        <div>Ancienne : {{ number_format($resultat['note_old'], 2) }}</div>
                                                        <div>Actuelle : {{ number_format($resultat['note'], 2) }}</div>
                                                        <div class="absolute transform -translate-x-1/2 border-4 border-transparent top-full left-1/2 border-t-gray-800 dark:border-t-gray-700"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md {{ $resultat['note'] >= 10 ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 border border-red-200 dark:bg-red-900 dark:text-red-200' }}">
                                                    {{ number_format($resultat['note'], 2) }}
                                                    @if($resultat['is_checked'])
                                                        <em class="ml-2 text-blue-600 icon ni ni-check-circle"></em>
                                                    @endif
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Actions améliorées -->
                                <td class="px-3 py-2 text-center">
                                    @if($editingRow === $resultat['unique_key'])
                                        <div class="flex items-center justify-center space-x-2">
                                            <button wire:click="saveChanges('{{ $resultat['unique_key'] }}')" 
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200 hover:shadow-md">
                                                <em class="mr-1 icon ni ni-check"></em>
                                                Sauver
                                            </button>
                                            <button wire:click="cancelEditing" 
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all duration-200 hover:shadow-md">
                                                <em class="mr-1 icon ni ni-cross"></em>
                                                Annuler
                                            </button>
                                        </div>
                                    @else
                                        <button wire:click="startEditing('{{ $resultat['unique_key'] }}')" wire:key="edit-{{ $resultat['unique_key'] }}"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 rounded-lg shadow-sm hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 hover:shadow-md dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800">
                                            <em class="mr-1 icon ni ni-edit"></em>
                                            Modifier
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    
                    <!-- Séparateur entre étudiants amélioré -->
                    @if(!$loop->last)
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="{{ $afficherMoyennesUE ? 9 : 8 }}" class="h-3">
                                <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent dark:via-gray-600"></div>
                            </td>
                        </tr>
                    @endif
                    
                @empty
                    <tr>
                        <td colspan="{{ $afficherMoyennesUE ? 9 : 8 }}" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center space-y-4">
                                <div class="p-4 bg-gray-100 rounded-full dark:bg-gray-700">
                                    <em class="text-4xl text-gray-400 icon ni ni-folder-close dark:text-gray-500"></em>
                                </div>
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">Aucun résultat trouvé</h4>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Aucun résultat ne correspond aux critères sélectionnés.
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer avec statistiques amélioré -->
    @if(count($resultats) > 0)
        <div class="px-4 py-3 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-800 dark:to-blue-900/20 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4 text-sm">
                    <div class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                        <em class="icon ni ni-users text-blue-600"></em>
                        <span class="font-medium">{{ count($resultatsByStudent) }} étudiant(s)</span>
                    </div>
                    <div class="w-px h-4 bg-gray-300 dark:bg-gray-600"></div>
                    <div class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                        <em class="icon ni ni-book text-green-600"></em>
                        <span class="font-medium">{{ count($resultats) }} EC</span>
                    </div>
                    @if($statistiquesPresence)
                        <div class="w-px h-4 bg-gray-300 dark:bg-gray-600"></div>
                        <div class="flex items-center space-x-2">
                            <em class="icon ni ni-check-circle text-green-600"></em>
                            <span class="font-medium text-green-700 dark:text-green-400">
                                {{ $statistiquesPresence['taux_presence'] }}% présents
                            </span>
                        </div>
                    @endif
                </div>
                
                @if($afficherMoyennesUE)
                    <div class="flex items-center space-x-2 text-xs">
                        <em class="icon ni ni-info text-blue-600"></em>
                        <span class="text-gray-600 dark:text-gray-400">
                            Moyennes UE calculées avec validation automatique
                        </span>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
/* Animations et transitions améliorées */
.table-row-animation {
    animation: slideInUp 0.3s ease-out forwards;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hover effects optimisés */
.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}

.group:hover .group-hover\:shadow-md {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Amélioration des tooltips */
.tooltip-arrow {
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -4px;
    border-width: 4px 4px 0 4px;
    border-style: solid;
    border-color: #374151 transparent transparent transparent;
}

/* Animation des badges */
.badge-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .8;
    }
}

/* Optimisation pour les écrans tactiles */
@media (hover: none) and (pointer: coarse) {
    .hover\:shadow-md:hover {
        box-shadow: none;
    }
    
    .hover\:scale-105:hover {
        transform: none;
    }
}

/* Amélioration de l'accessibilité */
.focus\:ring-2:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
}

/* Sticky header optimisé */
.sticky {
    position: -webkit-sticky;
    position: sticky;
    backdrop-filter: blur(8px);
    background-color: rgba(249, 250, 251, 0.95);
}

.dark .sticky {
    background-color: rgba(17, 24, 39, 0.95);
}

/* Animation de chargement pour les notes en édition */
.note-editing {
    position: relative;
    overflow: hidden;
}

.note-editing::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration optimisée
    const ANIMATION_CONFIG = {
        duration: 300,
        easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
        stagger: 20
    };

    // Animation d'entrée progressive pour les lignes
    const animateTableRows = () => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(10px)';
            row.style.transition = `opacity ${ANIMATION_CONFIG.duration}ms ${ANIMATION_CONFIG.easing}, transform ${ANIMATION_CONFIG.duration}ms ${ANIMATION_CONFIG.easing}`;
            
            setTimeout(() => {
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
                row.classList.add('table-row-animation');
            }, index * ANIMATION_CONFIG.stagger);
        });
    };

    // Amélioration des tooltips
    const initTooltips = () => {
        const tooltipElements = document.querySelectorAll('[title]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                // Logique de tooltip personnalisée si nécessaire
            });
        });
    };

    // Gestion améliorée du focus pour l'accessibilité
    const manageFocus = () => {
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', function() {
            document.body.classList.remove('keyboard-navigation');
        });
    };

    // Animation des badges de notes
    const animateNoteBadges = () => {
        const badges = document.querySelectorAll('.badge-pulse');
        badges.forEach(badge => {
            badge.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
            });
            
            badge.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    };

    // Optimisation des performances avec Intersection Observer
    const observeTableElements = () => {
        const observerOptions = {
            root: null,
            rootMargin: '50px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    };

    // Amélioration de l'input de note avec validation en temps réel
    const enhanceNoteInputs = () => {
        document.addEventListener('input', function(e) {
            if (e.target.type === 'number' && e.target.hasAttribute('wire:model.live')) {
                const value = parseFloat(e.target.value);
                const parent = e.target.closest('.note-editing');
                
                if (parent) {
                    if (value >= 0 && value <= 20) {
                        parent.classList.remove('border-red-500');
                        parent.classList.add('border-green-500');
                    } else {
                        parent.classList.remove('border-green-500');
                        parent.classList.add('border-red-500');
                    }
                }
            }
        });
    };

    // Gestion des raccourcis clavier
    const handleKeyboardShortcuts = () => {
        document.addEventListener('keydown', function(e) {
            // Échapper pour annuler l'édition
            if (e.key === 'Escape') {
                const cancelButton = document.querySelector('[wire\\:click="cancelEditing"]');
                if (cancelButton) {
                    cancelButton.click();
                }
            }
            
            // Entrée pour sauvegarder (si dans un input de note)
            if (e.key === 'Enter' && e.target.type === 'number') {
                const saveButton = e.target.closest('tr').querySelector('[wire\\:click*="saveChanges"]');
                if (saveButton) {
                    e.preventDefault();
                    saveButton.click();
                }
            }
        });
    };

    // Notification améliorée pour les moyennes UE
    Livewire.on('moyennesUEToggled', (isActivated) => {
        const message = isActivated
            ? '✅ Mode moyennes UE activé - Calculs automatiques des UE'
            : '🔄 Mode moyennes UE désactivé - Affichage simplifié';

        if (typeof toastr !== 'undefined') {
            toastr.info(message, 'Configuration mise à jour', {
                timeOut: 4000,
                progressBar: true,
                positionClass: 'toast-top-right',
                showMethod: 'slideDown',
                hideMethod: 'slideUp'
            });
        }
    });

    // Initialisation de tous les modules
    animateTableRows();
    initTooltips();
    manageFocus();
    animateNoteBadges();
    observeTableElements();
    enhanceNoteInputs();
    handleKeyboardShortcuts();

    // Réinitialisation après les mises à jour Livewire
    document.addEventListener('livewire:morph-updated', function() {
        setTimeout(() => {
            animateTableRows();
            initTooltips();
            animateNoteBadges();
        }, 50);
    });
});

// Nettoyage optimisé lors de la navigation
document.addEventListener('livewire:navigating', () => {
    // Arrêter toutes les animations en cours
    document.querySelectorAll('*').forEach(el => {
        el.style.animation = 'none';
        el.style.transition = 'none';
    });
    
    // Nettoyage des observateurs
    if (window.tableObserver) {
        window.tableObserver.disconnect();
    }
});
</script>
@endpush