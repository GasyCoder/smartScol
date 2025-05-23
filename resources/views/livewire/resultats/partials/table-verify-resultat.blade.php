<div class="bg-white dark:bg-gray-900 rounded-lg shadow border border-gray-200 dark:border-gray-700 {{ $printMode ? 'border-0 shadow-none' : '' }} overflow-hidden">
    <!-- En-tête compact -->
    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-600">
        <h3 class="flex items-center text-base font-semibold text-gray-900 dark:text-white">
            <em class="mr-2 icon ni ni-file-docs text-primary-600"></em>
            Résultats des Étudiants
        </h3>
    </div>

    <!-- Version mobile : Cards compactes -->
    <div class="lg:hidden">
        @php
            $resultatsByStudent = collect($resultats)->groupBy('etudiant_id');
        @endphp

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($resultatsByStudent as $etudiantId => $resultatGroup)
                @php
                    $firstResultat = $resultatGroup->first();
                @endphp

                <div class="p-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <!-- Info étudiant compact -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900">
                                <span class="text-xs font-semibold text-primary-700 dark:text-primary-300">{{ substr($firstResultat['prenom'], 0, 1) }}{{ substr($firstResultat['nom'], 0, 1) }}</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $firstResultat['nom'] }} {{ $firstResultat['prenom'] }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $firstResultat['matricule'] }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                            {{ count($resultatGroup) }}
                        </span>
                    </div>

                    <!-- Matières compactes -->
                    <div class="space-y-2">
                        @foreach($resultatGroup as $i => $resultat)
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-md p-2 {{ $resultat['is_checked'] ? 'ring-1 ring-green-400 bg-green-50 dark:bg-green-900/20' : '' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h5 class="text-sm font-medium text-gray-900 truncate dark:text-white">{{ $resultat['matiere'] }}</h5>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $resultat['enseignant'] }}</p>
                                    </div>
                                    <div class="ml-2 text-right">
                                        @if($editingRow === $resultat['numero_ordre'] - 1)
                                            <input
                                                type="number"
                                                wire:model.defer="newNote"
                                                step="0.01"
                                                min="0"
                                                max="20"
                                                class="w-16 px-2 py-1 text-xs text-gray-900 border border-gray-300 rounded dark:text-white dark:bg-gray-700 dark:border-gray-600 focus:ring-1 focus:ring-primary-500"
                                            />
                                        @else
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded {{ $resultat['note'] >= 10 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                    {{ number_format($resultat['note'], 2) }}/20
                                                    @if($resultat['is_checked'])
                                                        <em class="ml-1 text-green-600 icon ni ni-check"></em>
                                                    @endif
                                                </span>
                                                @if($resultat['note_old'])
                                                    <div class="mt-1 text-xs text-gray-500">
                                                        Ancienne: {{ number_format($resultat['note_old'], 2) }}/20
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if($editingRow === $resultat['numero_ordre'] - 1)
                                    <div class="flex mt-2 space-x-1">
                                        <button wire:click="saveChanges({{ $resultat['numero_ordre'] - 1 }})" class="flex-1 px-2 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700">
                                            <em class="mr-1 icon ni ni-check"></em>Sauver
                                        </button>
                                        <button wire:click="cancelEditing" class="flex-1 px-2 py-1 text-xs font-medium text-white bg-gray-600 rounded hover:bg-gray-700">
                                            <em class="mr-1 icon ni ni-cross"></em>Annuler
                                        </button>
                                    </div>
                                @else
                                    <div class="mt-2 flex justify-end {{ $printMode ? 'hidden' : '' }}">
                                        <button
                                            wire:click="startEditing({{ $resultat['numero_ordre'] - 1 }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded text-primary-700 bg-primary-100 hover:bg-primary-200"
                                        >
                                            <em class="mr-1 icon ni ni-edit"></em>Modifier
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <em class="block mb-2 text-3xl text-gray-300 icon ni ni-folder-open dark:text-gray-600"></em>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aucun résultat trouvé</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Version desktop : Tableau slim -->
    <div class="hidden overflow-x-auto lg:block">
        <table class="min-w-full">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th scope="col" class="px-4 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                        <span class="flex items-center">
                            <em class="mr-1 text-gray-500 icon ni ni-hash"></em>
                            N°
                        </span>
                    </th>
                    <th scope="col" class="px-4 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                        <button wire:click="toggleOrder('matricule')" class="group flex items-center hover:text-primary-600 transition-colors {{ $orderBy === 'matricule' ? 'text-primary-600' : '' }}">
                            <em class="mr-1 text-gray-500 icon ni ni-id-card group-hover:text-primary-500"></em>
                            Matricule
                            @if($orderBy === 'matricule')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                            @endif
                        </button>
                    </th>
                    <th scope="col" class="px-4 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                        <button wire:click="toggleOrder('nom')" class="group flex items-center hover:text-primary-600 transition-colors {{ $orderBy === 'nom' ? 'text-primary-600' : '' }}">
                            <em class="mr-1 text-gray-500 icon ni ni-user group-hover:text-primary-500"></em>
                            Nom
                            @if($orderBy === 'nom')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                            @endif
                        </button>
                    </th>
                    <th scope="col" class="px-4 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                        <button wire:click="toggleOrder('prenom')" class="group flex items-center hover:text-primary-600 transition-colors {{ $orderBy === 'prenom' ? 'text-primary-600' : '' }}">
                            <em class="mr-1 text-gray-500 icon ni ni-user group-hover:text-primary-500"></em>
                            Prénom
                            @if($orderBy === 'prenom')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                            @endif
                        </button>
                    </th>
                    <th scope="col" class="px-4 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                        <button wire:click="toggleOrder('matiere')" class="group flex items-center hover:text-primary-600 transition-colors {{ $orderBy === 'matiere' ? 'text-primary-600' : '' }}">
                            <em class="mr-1 text-gray-500 icon ni ni-book group-hover:text-primary-500"></em>
                            Matière
                            @if($orderBy === 'matiere')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                            @endif
                        </button>
                    </th>
                    <th scope="col" class="px-4 py-2 text-xs font-semibold text-left text-gray-700 uppercase dark:text-gray-300">
                        <span class="flex items-center">
                            <em class="mr-1 text-gray-500 icon ni ni-user-circle"></em>
                            Enseignant
                        </span>
                    </th>
                    <th scope="col" class="px-4 py-2 text-xs font-semibold text-center text-gray-700 uppercase dark:text-gray-300">
                        <button wire:click="toggleOrder('note')" class="group flex items-center justify-center hover:text-primary-600 transition-colors {{ $orderBy === 'note' ? 'text-primary-600' : '' }}">
                            <em class="mr-1 text-gray-500 icon ni ni-bar-chart group-hover:text-primary-500"></em>
                            Note
                            @if($orderBy === 'note')
                                <em class="icon ni ni-sort-{{ $orderAsc ? 'down' : 'up' }} ml-1"></em>
                            @endif
                        </button>
                    </th>
                    <th scope="col" class="px-4 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase {{ $printMode ? 'hidden' : '' }}">
                        <span class="flex items-center justify-center">
                            <em class="mr-1 text-gray-500 icon ni ni-setting"></em>
                            Actions
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 dark:bg-gray-900 dark:divide-gray-800">
                @php
                    $currentStudentId = null;
                    $index = 0;
                    $resultatsByStudent = collect($resultats)->groupBy('etudiant_id');
                @endphp

                @forelse($resultatsByStudent as $etudiantId => $resultatGroup)
                    @php
                        $index++;
                        $firstResultat = $resultatGroup->first();
                        $rowCount = count($resultatGroup);
                    @endphp

                    @foreach($resultatGroup as $i => $resultat)
                        <tr class="{{ $i === 0 ? 'border-t border-primary-200 dark:border-primary-800' : '' }} {{ $resultat['is_checked'] ? 'bg-green-50 dark:bg-green-900/20' : '' }} hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            @if($i === 0)
                            <td class="px-4 py-2" rowspan="{{ $rowCount }}">
                                <div class="flex items-center justify-center w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900">
                                    <span class="text-xs font-semibold text-primary-700 dark:text-primary-300">{{ $index }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2" rowspan="{{ $rowCount }}">
                                <div class="px-2 py-1 font-mono text-xs text-gray-900 bg-gray-100 rounded dark:text-gray-100 dark:bg-gray-800">{{ $firstResultat['matricule'] }}</div>
                            </td>
                            <td class="px-4 py-2" rowspan="{{ $rowCount }}">
                                <div class="relative group">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate cursor-help max-w-[120px]">
                                        {{ $firstResultat['nom'] }}
                                    </div>
                                    <!-- Tooltip personnalisé avec Tailwind pur -->
                                    <div class="absolute z-50 invisible px-3 py-2 text-xs text-white transform -translate-x-1/2 bg-gray-900 rounded-md shadow-lg group-hover:visible -top-10 left-1/2 whitespace-nowrap">
                                        {{ $firstResultat['nom'] }}
                                        <!-- Petite flèche pointant vers le bas -->
                                        <div class="absolute transform -translate-x-1/2 border-4 border-transparent top-full left-1/2 border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2" rowspan="{{ $rowCount }}">
                                <div class="relative group">
                                    <div class="text-sm text-gray-700 dark:text-gray-300 truncate cursor-help max-w-[100px]">
                                        {{ $firstResultat['prenom'] }}
                                    </div>
                                    <!-- Tooltip personnalisé avec Tailwind pur -->
                                    <div class="absolute z-50 invisible px-3 py-2 text-xs text-white transform -translate-x-1/2 bg-gray-900 rounded-md shadow-lg group-hover:visible -top-10 left-1/2 whitespace-nowrap">
                                        {{ $firstResultat['prenom'] }}
                                        <div class="absolute transform -translate-x-1/2 border-4 border-transparent top-full left-1/2 border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            @endif
                            <td class="px-4 py-2 whitespace-nowrap">
                                <div class="relative flex items-center group">
                                    <!-- Préservation de l'icône visuelle - élément crucial du design -->
                                    <div class="w-1.5 h-1.5 bg-blue-400 rounded-full mr-2 flex-shrink-0"></div>

                                    <!-- Conteneur pour le texte avec troncature -->
                                    <span class="text-sm text-gray-900 dark:text-gray-100 truncate cursor-help max-w-[200px]"
                                        title="{{ $resultat['matiere'] }}">
                                        {{ $resultat['matiere'] }}
                                    </span>

                                    <!-- Tooltip personnalisé qui apparaît au survol -->
                                    <div class="absolute z-50 invisible px-3 py-2 text-xs text-white transform -translate-x-1/2 bg-gray-900 rounded-md shadow-lg group-hover:visible -top-10 left-1/2 whitespace-nowrap">
                                        {{ $resultat['matiere'] }}
                                        <div class="absolute transform -translate-x-1/2 border-4 border-transparent top-full left-1/2 border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <div class="relative flex items-center group">
                                    <!-- L'icône reste toujours visible et fonctionnelle -->
                                    <em class="flex-shrink-0 mr-2 text-gray-400 icon ni ni-user-circle"></em>

                                    <!-- Le texte suit exactement le même pattern que vos matières -->
                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate cursor-help max-w-[180px]"
                                        title="{{ $resultat['enseignant'] }}">
                                        {{ $resultat['enseignant'] }}
                                    </span>

                                    <!-- Tooltip identique pour maintenir la cohérence visuelle -->
                                    <div class="absolute z-50 invisible px-3 py-2 text-xs text-white transform -translate-x-1/2 bg-gray-900 rounded-md shadow-lg group-hover:visible -top-10 left-1/2 whitespace-nowrap">
                                        {{ $resultat['enseignant'] }}
                                        <div class="absolute transform -translate-x-1/2 border-4 border-transparent top-full left-1/2 border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            {{-- Affichage de la note --}}
                            <td class="px-4 py-2 text-center whitespace-nowrap">
                                <div class="flex flex-col items-center space-y-1">
                                    {{-- Mode édition --}}
                                    @if($editingRow === $resultat['numero_ordre'] - 1)
                                        <div class="relative">
                                            <input
                                                type="number"
                                                wire:model.live="newNote"
                                                step="0.01"
                                                min="0"
                                                max="20"
                                                class="w-20 px-2 py-1 text-sm text-gray-900 transition-all duration-200 border rounded border-primary-300 dark:text-white dark:bg-gray-700 dark:border-primary-600 focus:ring-1 focus:ring-primary-500"
                                                placeholder="{{ number_format($resultat['note'], 2) }}"
                                                autofocus
                                            />
                                            {{-- Indicateur de validation en temps réel --}}
                                            @if($newNote && $newNote != $resultat['note'])
                                                <div class="absolute transform -translate-y-1/2 -right-6 top-1/2">
                                                    @if($newNote >= 0 && $newNote <= 20)
                                                        <em class="text-sm text-green-500 icon ni ni-check"></em>
                                                    @else
                                                        <em class="text-sm text-red-500 icon ni ni-alert"></em>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Affichage de la progression du changement --}}
                                        @if($newNote && $newNote != $resultat['note'])
                                            <div class="text-xs font-medium text-blue-600 dark:text-blue-400">
                                                {{ number_format($resultat['note'], 2) }} → {{ number_format($newNote, 2) }}
                                            </div>
                                        @endif

                                    {{-- Mode affichage --}}
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-sm font-semibold rounded transition-all duration-200 {{ $resultat['note'] >= 10 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }} {{ $resultat['is_checked'] ? 'ring-1 ring-green-400 shadow-sm' : '' }}">
                                            {{ number_format($resultat['note'], 2) }}/20
                                            @if($resultat['is_checked'])
                                                <em class="ml-1 text-green-600 icon ni ni-check"></em>
                                            @endif
                                        </span>
                                    @endif

                                    {{-- Affichage de l'ancienne note (vérifié et sécurisé) --}}
                                    @if(isset($resultat['note_old']) && $resultat['note_old'])
                                        <div class="flex items-center space-x-1 text-xs text-gray-500">
                                            <em class="icon ni ni-history text-amber-500"></em>
                                            <span>Ancienne: {{ number_format($resultat['note_old'], 2) }}/20</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-center {{ $printMode ? 'hidden' : '' }}">
                                @if($editingRow === $resultat['numero_ordre'] - 1)
                                    <div class="flex items-center justify-center space-x-1">
                                        <button
                                            wire:click="saveChanges({{ $resultat['numero_ordre'] - 1 }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700">
                                            <em class="icon ni ni-check"></em>
                                        </button>
                                        <button
                                            wire:click="cancelEditing"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">
                                            <em class="icon ni ni-cross"></em>
                                        </button>
                                    </div>
                                @else
                                    <button
                                        wire:click="startEditing({{ $resultat['numero_ordre'] - 1 }})"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-white rounded bg-primary-600 hover:bg-primary-700">
                                        <em class="icon ni ni-edit"></em>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8">
                            <div class="flex flex-col items-center justify-center text-center">
                                <em class="mb-2 text-4xl text-gray-300 icon ni ni-folder-open dark:text-gray-600"></em>
                                <h3 class="mb-1 text-sm font-medium text-gray-900 dark:text-white">Aucun résultat trouvé</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Aucune donnée disponible</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
