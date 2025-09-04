<div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-900 dark:border-gray-700">
    <!-- Header -->
    <div class="px-3 py-2 border-b border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-600">
        <div class="flex items-center justify-between">
            <h3 class="flex items-center text-sm font-semibold text-gray-900 dark:text-white">
                <em class="mr-1.5 text-blue-600 icon ni ni-file-docs"></em>
                Résultats des Étudiants
                <span class="ml-1.5 text-xs text-gray-500 dark:text-gray-400">
                    ({{ $totalResultats }} résultats - {{ $pourcentageVerification }}% vérifiés)
                </span>
            </h3>
            @if($afficherMoyennesUE)
                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                    <em class="mr-1 icon ni ni-bar-chart"></em>Mode UE
                </span>
            @endif
        </div>
    </div>

    <!-- Desktop Table -->
<div class="overflow-x-auto">
    <table class="min-w-full text-xs">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="px-2 py-1 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">N°</th>
                <th class="px-2 py-1 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                    <button wire:click="toggleOrder('matricule')" class="group flex items-center hover:text-blue-600 {{ $orderBy === 'matricule' ? 'text-blue-600' : '' }}">
                        Matricule
                        @if($orderBy === 'matricule')
                            <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                        @endif
                    </button>
                </th>
                <th class="px-2 py-1 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                    <button wire:click="toggleOrder('nom')" class="group flex items-center hover:text-blue-600 {{ $orderBy === 'nom' ? 'text-blue-600' : '' }}">
                        Nom
                        @if($orderBy === 'nom')
                            <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                        @endif
                    </button>
                </th>
                <th class="px-2 py-1 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                    <button wire:click="toggleOrder('prenom')" class="group flex items-center hover:text-blue-600 {{ $orderBy === 'prenom' ? 'text-blue-600' : '' }}">
                        Prénom
                        @if($orderBy === 'prenom')
                            <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                        @endif
                    </button>
                </th>
                @if($afficherMoyennesUE)
                    <th class="px-2 py-1 text-xs font-semibold text-center text-gray-700 uppercase dark:text-gray-300">Moy.UE</th>
                @endif
                <th class="px-2 py-1 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">UE / EC</th>
                <th class="px-2 py-1 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">Enseignant</th>
                <th class="px-2 py-1 text-xs font-semibold text-center text-gray-700 uppercase dark:text-gray-300">
                    <button wire:click="toggleOrder('note')" class="group flex items-center justify-center hover:text-blue-600 {{ $orderBy === 'note' ? 'text-blue-600' : '' }}">
                        Note/20
                        @if($orderBy === 'note')
                            <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                        @endif
                    </button>
                </th>
                <th class="px-2 py-1 text-xs font-semibold text-center text-gray-700 uppercase dark:text-gray-300">Actions</th>
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
                    $moyenneGenerale = $afficherMoyennesUE ? ($firstResultat['moyenne_generale'] ?? $resultatGroup->where('note', '!=', null)->pluck('note')->avg() ?? null) : null;
                    $ueIndex = 0;
                @endphp
                
                <!-- ✅ LIGNE COMPACTE ÉTUDIANT -->
                <tr class="bg-blue-50 dark:bg-blue-900/20 font-medium">
                    <td class="px-2 py-1">
                        <div class="flex items-center justify-center w-5 h-5 text-xs bg-blue-100 rounded-full dark:bg-blue-900">
                            {{ $index }}
                        </div>
                    </td>
                    <td class="px-2 py-1">
                        <span class="font-bold text-xs bg-gray-100 px-1 rounded dark:bg-gray-800">{{ $matricule }}</span>
                    </td>
                    <td class="px-2 py-1 text-xs font-semibold text-gray-900 dark:text-gray-100">
                        {{ $firstResultat['nom'] }}
                    </td>
                    <td class="px-2 py-1 text-xs text-gray-700 dark:text-gray-300">
                        {{ $firstResultat['prenom'] }}
                    </td>
                    @if($afficherMoyennesUE)
                        <td class="px-2 py-1 text-center">
                            <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded {{ $moyenneGenerale >= 10 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $moyenneGenerale !== null ? number_format($moyenneGenerale, 2) : 'N/A' }}
                            </span>
                        </td>
                    @endif
                    <td colspan="{{ $afficherMoyennesUE ? 4 : 4 }}" class="px-2 py-1 text-xs text-gray-600">
                        <strong>Résultats par UE et EC :</strong>
                    </td>
                </tr>

                <!-- ✅ LIGNES COMPACTES UE/EC -->
                @foreach($resultatsByUE as $ueNom => $ecGroup)
                    @php 
                        $ueIndex++; 
                        $moyenneUE = $ecGroup->avg('note');
                        $hasZero = $ecGroup->contains('note', 0);
                        if ($hasZero) $moyenneUE = 0;
                    @endphp
                    
                    @foreach($ecGroup as $indexEC => $resultat)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ $resultat['is_checked'] ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                            <td class="px-2 py-1"></td>
                            <td class="px-2 py-1"></td>
                            <td class="px-2 py-1"></td>
                            <td class="px-2 py-1"></td>
                            @if($afficherMoyennesUE)
                                <td class="px-2 py-1 text-center">
                                    @if($indexEC === 0)
                                        <span class="inline-flex items-center px-1 py-0.5 text-xs font-medium rounded {{ $moyenneUE >= 10 ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800' }}">
                                            {{ number_format($moyenneUE, 2) }}
                                        </span>
                                    @endif
                                </td>
                            @endif
                            
                            <!-- ✅ UE/EC COMPACT -->
                            <td class="px-2 py-1 text-xs">
                                @if($indexEC === 0)
                                    <div class="font-medium text-gray-900 dark:text-gray-100 mb-1">
                                        <strong>UE{{ $ueIndex }}.</strong> {{ $ueNom ?? 'UE N/A' }}
                                        <span class="text-xs text-gray-500">({{ $resultat['ue_credits'] ?? 0 }})</span>
                                    </div>
                                @endif
                                <div class="pl-3 text-xs text-gray-600 dark:text-gray-400">
                                    <strong>EC{{ $indexEC + 1 }}.</strong>{{ $resultat['matiere'] }}
                                </div>
                            </td>
                            
                            <td class="px-2 py-1 text-xs text-gray-700 dark:text-gray-300">
                                {{ $resultat['enseignant'] ?? 'N/A' }}
                            </td>
                            
                            <!-- ✅ NOTE COMPACT -->
                            <td class="px-3 py-2 text-center">
                                <div class="flex flex-col items-center space-y-0.5">
                                    @if($editingRow === $resultat['unique_key'])
                                        <div class="relative">
                                            <input
                                                type="number"
                                                wire:model.live="newNote"
                                                step="0.01"
                                                min="0"
                                                max="20"
                                                class="px-2 py-1 text-lg text-gray-900 border border-gray-300 rounded dark:text-white dark:bg-gray-700 dark:border-gray-600 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="{{ number_format($resultat['note'], 2) }}"
                                                aria-label="Modifier la note pour {{ $resultat['matiere'] }}"
                                                autofocus
                                            />
                                            @if($newNote && $newNote != $resultat['note'])
                                                <div class="absolute transform -translate-y-1/2 -right-4 top-1/2">
                                                    @if($newNote >= 0 && $newNote <= 20)
                                                        <em class="text-xs text-blue-500 icon ni ni-check"></em>
                                                    @else
                                                        <em class="text-xs text-red-500 icon ni ni-alert"></em>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        @if($newNote && $newNote != $resultat['note'])
                                            <div class="text-lg text-blue-600 dark:text-blue-400">{{ number_format($resultat['note'], 2) }} → {{ number_format($newNote, 2) }}</div>
                                        @endif
                                    @else
                                        @if($resultat['note_old'])
                                            <div class="relative group">
                                                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded cursor-help {{ $resultat['note'] >= 10 ? ' text-green-800  dark:text-green-200' : 'text-red-800 dark:text-red-200' }} {{ $resultat['is_checked'] ? '' : '' }}">
                                                    {{ number_format($resultat['note'], 2) }}
                                                    @if($resultat['is_checked'])
                                                        <em class="ml-1 text-blue-600 icon ni ni-done"></em>
                                                    @endif
                                                </span>
                                                <!-- Tooltip -->
                                                <div class="absolute z-10 px-2 py-1 mb-2 text-xs text-white transition-opacity duration-200 transform -translate-x-1/2 bg-gray-800 rounded shadow-lg opacity-0 pointer-events-none bottom-full left-1/2 dark:bg-gray-700 group-hover:opacity-100 whitespace-nowrap">
                                                    Ancienne note : {{ number_format($resultat['note_old'], 2) }}
                                                    <div class="absolute transform -translate-x-1/2 border-4 border-transparent top-full left-1/2 border-t-gray-800 dark:border-t-gray-700"></div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded {{ $resultat['note'] >= 10 ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }} {{ $resultat['is_checked'] ? '' : '' }}">
                                                {{ number_format($resultat['note'], 2) }}
                                                @if($resultat['is_checked'])
                                                    <em class="ml-1 text-blue-600 icon ni ni-done"></em>
                                                @endif
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            
                            <!-- ✅ ACTIONS COMPACT -->
                            <td class="px-2 py-1 text-center">
                                @if($editingRow === $resultat['unique_key'])
                                    <div class="flex items-center space-x-1">
                                        <button wire:click="saveChanges('{{ $resultat['unique_key'] }}')" class="px-1.5 py-0.5 text-xs text-white bg-green-600 rounded hover:bg-green-700">
                                            <em class="icon ni ni-check"></em>
                                        </button>
                                        <button wire:click="cancelEditing" class="px-1.5 py-0.5 text-xs text-white bg-red-600 rounded hover:bg-red-700">
                                            <em class="icon ni ni-cross"></em>
                                        </button>
                                    </div>
                                @else
                                    <button wire:click="startEditing('{{ $resultat['unique_key'] }}')" class="px-1.5 py-0.5 text-xs text-blue-700 bg-blue-100 rounded hover:bg-blue-200">
                                        <em class="icon ni ni-edit"></em>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                
                <!-- ✅ SÉPARATEUR ÉTUDIANT -->
                @if(!$loop->last)
                    <tr><td colspan="{{ $afficherMoyennesUE ? 9 : 8 }}" class="h-2 bg-gray-100 dark:bg-gray-700"></td></tr>
                @endif
                
            @empty
                <tr>
                    <td colspan="{{ $afficherMoyennesUE ? 9 : 8 }}" class="px-3 py-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <em class="mb-2 text-3xl text-gray-300 icon ni ni-folder-close dark:text-gray-600"></em>
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-300">Aucun résultat trouvé</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <!-- Footer -->
    @if(count($resultats) > 0)
        <div class="px-3 py-2 border-t border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-600">
            <div class="flex items-center space-x-1">
                <em class="icon ni ni-users"></em>
                <span>{{ count($resultatsByStudent) }} étudiant(s)</span>
                <span class="text-gray-400">•</span>
                <span>{{ count($resultats) }} EC</span>
                @if($statistiquesPresence)
                    <span class="text-gray-400">•</span>
                    <span class="text-green-600">{{ $statistiquesPresence['taux_presence'] }}% présents</span>
                @endif
            </div>
        </div>
    @endif
</div>
@push('styles')
<style>
/* CSS pour le switch moyennes UE */
.dot {
    transition: transform 0.2s ease-in-out;
}

#switch-moyennes-ue:focus + div {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Animation des bordures de tableau */
.table-hover tr:hover {
    background-color: rgba(59, 130, 246, 0.05);
}

/* Amélioration de la lisibilité */
.table-striped tbody tr:nth-child(even) {
    background-color: rgba(0, 0, 0, 0.02);
}

.dark .table-striped tbody tr:nth-child(even) {
    background-color: rgba(255, 255, 255, 0.02);
}

/* Animation de la barre de progression */
.progress-bar {
    transition: width 0.6s ease;
}

/* Styles pour les badges de notes */
.note-badge {
    transition: all 0.2s ease;
}

.note-badge:hover {
    transform: scale(1.05);
}
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation fluide du switch
    const switchElement = document.getElementById('switch-moyennes-ue');
    if (switchElement) {
        switchElement.addEventListener('change', function() {
            // Petit feedback visuel
            const container = this.parentElement;
            container.style.transform = 'scale(0.98)';
            setTimeout(() => {
                container.style.transform = 'scale(1)';
            }, 100);
        });
    }

    // Animation des lignes de tableau
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.02}s`;
        row.classList.add('animate-fade-in');
    });

    // Notification toast lors du changement de mode
    Livewire.on('moyennesUEToggled', (isActivated) => {
        const message = isActivated
            ? 'Mode moyennes UE activé - Les exports incluront les calculs UE'
            : 'Mode moyennes UE désactivé - Exports simples sans calculs';

        // Toast notification si disponible
        if (typeof toastr !== 'undefined') {
            toastr.info(message);
        }
    });
});

// CSS pour l'animation fade-in
const style = document.createElement('style');
style.textContent = `
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}
`;
document.head.appendChild(style);
</script>
@endpush
