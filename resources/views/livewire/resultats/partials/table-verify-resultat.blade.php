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
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">N°</th>
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                        <button wire:click="toggleOrder('matricule')" class="group flex items-center hover:text-blue-600 {{ $orderBy === 'matricule' ? 'text-blue-600' : '' }}">
                            Matricule
                            @if($orderBy === 'matricule')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                            @endif
                        </button>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                        <button wire:click="toggleOrder('nom')" class="group flex items-center hover:text-blue-600 {{ $orderBy === 'nom' ? 'text-blue-600' : '' }}">
                            Nom
                            @if($orderBy === 'nom')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                            @endif
                        </button>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                        <button wire:click="toggleOrder('prenom')" class="group flex items-center hover:text-blue-600 {{ $orderBy === 'prenom' ? 'text-blue-600' : '' }}">
                            Prénom
                            @if($orderBy === 'prenom')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                            @endif
                        </button>
                    </th>
                    @if($afficherMoyennesUE)
                        <th class="px-3 py-2 text-xs font-semibold text-center text-gray-700 uppercase dark:text-gray-300">Moy. UE</th>
                    @endif
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">UE / EC</th>
                    <th class="px-3 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">Enseignant</th>
                    <th class="px-3 py-2 text-xs font-semibold text-center text-gray-700 uppercase dark:text-gray-300">
                        <button wire:click="toggleOrder('note')" class="group flex items-center justify-center hover:text-blue-600 {{ $orderBy === 'note' ? 'text-blue-600' : '' }}">
                            Note/20
                            @if($orderBy === 'note')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                            @endif
                        </button>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold text-center text-gray-700 uppercase dark:text-gray-300">Actions</th>
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
                        $rowCount = $resultatGroup->count();
                        $moyenneGenerale = $afficherMoyennesUE ? ($firstResultat['moyenne_generale'] ?? $resultatGroup->where('note', '!=', null)->pluck('note')->avg() ?? null) : null;
                    @endphp
                    @foreach($resultatsByUE as $ueNom => $ecGroup)
                        @php
                            $ueRowCount = $ecGroup->count();
                            $ueData = $afficherMoyennesUE && isset($firstResultat['moyennes_ue_etudiant'][$ueNom]) ? $firstResultat['moyennes_ue_etudiant'][$ueNom] : null;
                        @endphp
                        @foreach($ecGroup as $indexEC => $resultat)
                            <tr class="{{ $loop->parent->first && $indexEC === 0 ? 'border-t border-blue-200 dark:border-blue-800' : '' }} {{ $resultat['is_checked'] ? 'bg-blue-50 dark:bg-blue-900/20' : '' }} hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200 animate-fade-in">
                                @if($loop->parent->first && $indexEC === 0)
                                    <td class="px-3 py-2" rowspan="{{ $rowCount }}">
                                        <div class="flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full dark:bg-blue-900">
                                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">{{ $index }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2" rowspan="{{ $rowCount }}">
                                        <div class="px-1.5 py-0.5 font-mono text-xs text-gray-900 bg-gray-100 rounded dark:text-gray-100 dark:bg-gray-800">{{ $matricule }}</div>
                                    </td>
                                    <td class="px-3 py-2" rowspan="{{ $rowCount }}">
                                        <span class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ $firstResultat['nom'] }}</span>
                                    </td>
                                    <td class="px-3 py-2" rowspan="{{ $rowCount }}">
                                        <span class="text-xs text-gray-700 dark:text-gray-300">{{ $firstResultat['prenom'] }}</span>
                                    </td>
                                    @if($afficherMoyennesUE)
                                        <td class="px-3 py-2 text-center" rowspan="{{ $rowCount }}">
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded {{ $moyenneGenerale >= 10 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                {{ $moyenneGenerale !== null ? number_format($moyenneGenerale, 2) : 'N/A' }}
                                            </span>
                                        </td>
                                    @endif
                                @endif
                                <td class="px-3 py-2">
                                    @if($indexEC === 0)
                                        @php
                                            $ueInfo = $ecGroup->first();
                                            $ueCredits = $ueInfo['ue_credits'] ?? 0;
                                            $ueAbr = $ueInfo['ue_abr'] ?? 'UE';
                                        @endphp
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $ueAbr }}. {{ $ueNom ?? 'UE N/A' }}
                                            <span class="text-xs text-gray-500 dark:text-gray-400">({{ $ueCredits }})</span>
                                        </div>
                                    @endif
                                    <div class="pl-4 text-xs text-gray-600 dark:text-gray-400">
                                        {{ $indexEC === 0 ? '- ' : '- ' }}EC{{ $indexEC + 1 }}. {{ $resultat['matiere'] }}
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ $resultat['enseignant'] ?? 'N/A' }}</span>
                                </td>
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
                                                    class="w-16 px-1.5 py-0.5 text-xs text-gray-900 border border-gray-300 rounded dark:text-white dark:bg-gray-700 dark:border-gray-600 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
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
                                                <div class="text-xs text-blue-600 dark:text-blue-400">{{ number_format($resultat['note'], 2) }} → {{ number_format($newNote, 2) }}</div>
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
                                <td class="px-3 py-2 text-center">
                                    @if($editingRow === $resultat['unique_key'])
                                        <div class="flex items-center space-x-1">
                                            <button wire:click="saveChanges('{{ $resultat['unique_key'] }}')" class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 transition-all duration-200" aria-label="Enregistrer">
                                                <em class="icon ni ni-check"></em>
                                            </button>
                                            <button wire:click="cancelEditing" class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-white bg-gray-600 rounded hover:bg-gray-700 transition-all duration-200" aria-label="Annuler">
                                                <em class="icon ni ni-cross"></em>
                                            </button>
                                        </div>
                                    @else
                                        <button wire:click="startEditing('{{ $resultat['unique_key'] }}')" class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded hover:bg-blue-200 dark:text-blue-300 dark:bg-blue-900 dark:hover:bg-blue-800 transition-all duration-200" aria-label="Modifier la note pour {{ $resultat['matiere'] }}">
                                            <em class="icon ni ni-edit"></em>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if($afficherMoyennesUE && $ueData)
                            <tr class="bg-blue-50 dark:bg-blue-900/20">
                                <td colspan="{{ $afficherMoyennesUE ? 12 : 10 }}" class="px-3 py-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-blue-800 dark:text-blue-300">{{ $ueNom }} - Récapitulatif</span>
                                        <div class="flex items-center space-x-1">
                                            <span class="px-1.5 py-0.5 rounded {{ $ueData['validee'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                {{ number_format($ueData['moyenne'], 2) }}
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400">({{ $ueData['credits_obtenus'] }}/{{ $ueData['credit'] }})</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    @if($afficherMoyennesUE && isset($firstResultat['moyennes_ue_etudiant']) && !empty($firstResultat['moyennes_ue_etudiant']))
                        @php
                            $moyennesUE = $firstResultat['moyennes_ue_etudiant'];
                            $totalCreditsObtenus = collect($moyennesUE)->sum('credits_obtenus');
                        @endphp
                        <tr class="border-b-2 border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20">
                            <td colspan="{{ $afficherMoyennesUE ? 12 : 10 }}" class="px-3 py-2 text-xs">
                                <div>
                                    <strong class="text-blue-800 dark:text-blue-300">Récapitulatif - {{ $firstResultat['prenom'] }} {{ $firstResultat['nom'] }}</strong>
                                    <div class="grid grid-cols-2 gap-1 mt-1 md:grid-cols-3">
                                        @foreach($moyennesUE as $donneesUE)
                                            <div class="flex items-center justify-between px-1.5 py-0.5 bg-white border rounded dark:bg-gray-800 dark:border-gray-600">
                                                <span class="text-gray-700 truncate dark:text-gray-300">{{ $donneesUE['nom'] }}</span>
                                                <div class="flex items-center space-x-1">
                                                    <span class="px-1 rounded {{ $donneesUE['validee'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                        {{ number_format($donneesUE['moyenne'], 2) }}
                                                    </span>
                                                    <span class="text-gray-500 dark:text-gray-400">({{ $donneesUE['credits_obtenus'] }}/{{ $donneesUE['credit'] }})</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-1 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded {{ $totalCreditsObtenus >= 60 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($totalCreditsObtenus >= 40 ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                            Total : {{ $totalCreditsObtenus }}/60 crédits -
                                            {{ $totalCreditsObtenus >= 60 ? '✓ Admis' : ($totalCreditsObtenus >= 40 ? '⚠ Délibération' : '✗ Redoublement') }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ $afficherMoyennesUE ? 12 : 10 }}" class="px-3 py-6 text-center">
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
            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                <div class="flex items-center space-x-1">
                    <em class="icon ni ni-users"></em>
                    <span>{{ count($resultatsByStudent) }} étudiant(s)</span>
                    <span class="text-gray-400">•</span>
                    <span>{{ count($resultats) }} EC</span>
                </div>
                @if($afficherMoyennesUE)
                    <div class="flex items-center space-x-1">
                        <em class="text-blue-500 icon ni ni-bar-chart"></em>
                        <span class="font-medium text-blue-600 dark:text-blue-400">Mode UE activé</span>
                    </div>
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
