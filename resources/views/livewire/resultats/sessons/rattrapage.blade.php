{{-- Onglet Session 2 (RATTRAPAGE - MODIFICATION LIBRE) --}}
@if($activeTab === 'session2' && $showSession2)
    <div class="space-y-4">
        @if(empty($resultatsSession2))
            <!-- État vide avec possibilité d'ajout libre -->
            <div class="py-12 text-center">
                <em class="text-6xl text-green-400 ni ni-plus-circle dark:text-green-500"></em>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">Session de rattrapage disponible</p>
                <p class="text-sm text-gray-400 dark:text-gray-500">Vous pouvez ajouter de nouvelles notes LIBREMENT sans restriction</p>
                <button wire:click="openAddNoteModal"
                        class="inline-flex items-center px-6 py-3 mt-4 text-sm font-medium text-white bg-green-600 border border-transparent rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <em class="mr-2 ni ni-plus"></em>
                    Commencer la Saisie Libre
                </button>
            </div>
        @else
            <!-- Alerte de session AUSSI verrouillée -->
            <div class="p-4 border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700 rounded-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <em class="mr-3 text-blue-600 ni ni-info dark:text-blue-400"></em>
                        <div>
                            <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">Session 2 - Mode Consultation</h4>
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Les résultats de la session de rattrapage sont publiés et verrouillés. Aucune modification possible.
                                @if(isset($deliberationStatus['delibere']) && $deliberationStatus['delibere'])
                                    <span class="font-semibold">Délibération appliquée le {{ $deliberationStatus['date_deliberation'] ?? 'N/A' }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- ✅ Badge du statut de délibération --}}
                    @if(isset($deliberationStatus['delibere']) && $deliberationStatus['delibere'])
                        <span class="flex items-center px-3 py-1 text-sm text-green-700 bg-green-100 rounded-full dark:bg-green-900/50 dark:text-green-300">
                            <em class="mr-1 ni ni-shield-check"></em>
                            Délibéré
                        </span>
                    @else
                        <span class="flex items-center px-3 py-1 text-sm text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-400">
                            <em class="mr-1 ni ni-cpu"></em>
                            Automatique
                        </span>
                    @endif
                </div>
            </div>

            {{-- Tableau Session 2 avec structure identique à Session 1 --}}
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/20">
                        {{-- Première ligne d'en-têtes : UE --}}
                        <tr>
                            <th rowspan="2" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700">
                                Ordre
                            </th>
                            <th rowspan="2" class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700">
                                Matricule
                            </th>
                            <th rowspan="2" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700">
                                Nom
                            </th>
                            <th rowspan="2" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700">
                                Prénom
                            </th>
                            @foreach($uesStructure as $index => $ueStructure)
                                @php
                                    $colonnesUE = count($ueStructure['ecs']) + 1;
                                @endphp
                                <th colspan="{{ $colonnesUE }}"
                                    class="px-3 py-2 text-xs font-bold tracking-wider text-center text-gray-900 uppercase bg-gray-100 border-b border-r dark:text-gray-100 dark:border-gray-700 dark:bg-gray-800
                                    {{ $index === 0 ? 'border-l-2 border-l-gray-400 dark:border-l-gray-500' : '' }}">
                                    <div class="flex flex-col items-center">
                                        <span class="font-bold">{{ $ueStructure['ue']->abr ?? 'UE' . ($index + 1) }} - {{ $ueStructure['ue']->nom }}</span>
                                        <span class="text-xs font-normal">({{ $ueStructure['ue']->credits ?? 0 }})</span>
                                    </div>
                                </th>
                            @endforeach
                            <th rowspan="2" class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-900 uppercase border-b border-l-2 border-r border-l-gray-400 dark:text-gray-100 dark:border-gray-700 dark:border-l-gray-500">
                                <div class="flex flex-col items-center">
                                    <span>Moyenne</span>
                                    <span>Générale</span>
                                </div>
                            </th>
                            <th rowspan="2" class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700">
                                Crédits
                            </th>
                            <th rowspan="2" class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-900 uppercase border-b dark:text-gray-100 dark:border-gray-700">
                                Décision
                            </th>
                        </tr>

                        {{-- Deuxième ligne d'en-têtes : EC + Moyenne UE --}}
                        <tr>
                            @foreach($uesStructure as $index => $ueStructure)
                                {{-- En-têtes EC de cette UE --}}
                                @foreach($ueStructure['ecs'] as $ecIndex => $ecData)
                                    <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700
                                        {{ $index === 0 && $ecIndex === 0 ? 'border-l-2 border-l-gray-400 dark:border-l-gray-500' : '' }}"
                                        title="{{ $ecData['ec']->nom }}">
                                        {{ $ecData['display_name'] }}
                                    </th>
                                @endforeach
                                {{-- En-tête moyenne UE --}}
                                <th class="px-2 py-2 text-xs font-bold tracking-wider text-center text-gray-900 uppercase bg-gray-200 border-b border-r dark:text-gray-100 dark:border-gray-700 dark:bg-gray-700">
                                    <div class="flex flex-col items-center">
                                        <span class="font-bold">Moyenne</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($resultatsSession2 as $indexEtudiant => $resultat)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                {{-- Ordre --}}
                                <td class="px-4 py-3 text-center border-r whitespace-nowrap dark:border-gray-700">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $indexEtudiant + 1 }}
                                    </div>
                                </td>
                                {{-- Matricule --}}
                                <td class="px-4 py-3 text-center border-r whitespace-nowrap dark:border-gray-700">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $resultat['etudiant']->matricule }}
                                    </div>
                                </td>
                                {{-- Nom --}}
                                <td class="px-4 py-3 border-r whitespace-nowrap dark:border-gray-700">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $resultat['etudiant']->nom }}
                                    </div>
                                </td>
                                {{-- Prénom --}}
                                <td class="px-4 py-3 border-r whitespace-nowrap dark:border-gray-700">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $resultat['etudiant']->prenom }}
                                    </div>
                                </td>
                                @foreach($uesStructure as $index => $ueStructure)
                                    @php
                                        $ueId = $ueStructure['ue']->id;
                                        $notesUE = [];
                                        $hasNoteZeroInUE = false;
                                    @endphp
                                    {{-- Notes EC de cette UE --}}
                                    @foreach($ueStructure['ecs'] as $ecIndex => $ecData)
                                        <td class="px-2 py-3 text-center whitespace-nowrap border-r dark:border-gray-700
                                            {{ $index === 0 && $ecIndex === 0 ? 'border-l-2 border-l-gray-400 dark:border-l-gray-500' : '' }}">
                                            @if(isset($resultat['notes'][$ecData['ec']->id]))
                                                @php
                                                    $note = $resultat['notes'][$ecData['ec']->id]->note;
                                                    $notesUE[] = $note;
                                                    if ($note == 0) $hasNoteZeroInUE = true;
                                                @endphp
                                                <span class="text-sm {{ $note >= 10 ? 'text-green-600 dark:text-green-400' : ($note == 0 ? 'text-red-600 dark:text-red-400 font-bold' : 'text-orange-600 dark:text-orange-400') }}">
                                                    {{ number_format($note, 2) }}
                                                    @if($note == 0)
                                                        <span class="ml-1" title="Note éliminatoire">⚠️</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    {{-- Moyenne UE selon votre logique --}}
                                    <td class="px-2 py-3 text-center border-r whitespace-nowrap dark:border-gray-700">
                                        @php
                                            // Appliquer votre logique académique exacte
                                            if ($hasNoteZeroInUE) {
                                                // UE éliminée à cause d'une note de 0
                                                $moyenneUE = 0;
                                                $moyenneDisplay = '0.00';
                                                $ueValidee = false;
                                                $moyenneClass = 'text-red-600 dark:text-red-400 font-bold';
                                            } elseif (!empty($notesUE)) {
                                                // Moyenne UE = somme notes / nombre EC
                                                $moyenneUE = array_sum($notesUE) / count($notesUE);
                                                $ueValidee = $moyenneUE >= 10;
                                                $moyenneDisplay = number_format($moyenneUE, 2);
                                                $moyenneClass = $ueValidee ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400';
                                            } else {
                                                $moyenneUE = 0;
                                                $moyenneDisplay = '-';
                                                $ueValidee = false;
                                                $moyenneClass = 'text-gray-500 dark:text-gray-400';
                                            }
                                        @endphp
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="text-sm font-bold {{ $moyenneClass }}">
                                                {{ $moyenneDisplay }}
                                            </span>
                                            @if($ueValidee)
                                                <span class="text-xs text-green-500" title="UE validée">✓</span>
                                            @elseif($hasNoteZeroInUE)
                                                <span class="text-xs text-red-500" title="UE éliminée">✗</span>
                                            @else
                                                <span class="text-xs text-orange-500" title="UE non validée">✗</span>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                                {{-- Moyenne générale --}}
                                <td class="px-4 py-3 text-center border-l-2 border-r whitespace-nowrap border-l-gray-400 dark:border-gray-700 dark:border-l-gray-500">
                                    <span class="text-sm font-bold {{ $resultat['moyenne_generale'] >= 10 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ number_format($resultat['moyenne_generale'], 2) }}
                                    </span>
                                </td>
                                {{-- Crédits --}}
                                <td class="px-4 py-3 text-center border-r whitespace-nowrap dark:border-gray-700">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $resultat['credits_valides'] }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            /{{ $resultat['total_credits'] ?? 60 }}
                                        </span>
                                    </div>
                                </td>
                                {{-- Décision selon votre logique --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @php
                                        $decision = $resultat['decision'];
                                        // Couleurs plus douces mais visibles
                                        $decisionClass = match($decision) {
                                            'admis' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300',
                                            'rattrapage' => 'bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-300',
                                            'redoublant' => 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300',
                                            'exclus' => 'bg-red-200 dark:bg-red-900/70 text-red-900 dark:text-red-200',
                                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100'
                                        };
                                        $decisionLibelle = match($decision) {
                                            'admis' => 'Admis',
                                            'rattrapage' => 'Rattrapage',
                                            'redoublant' => 'Redoublant',
                                            'exclus' => 'Exclus',
                                            default => 'Non définie'
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $decisionClass }}">
                                        {{ $decisionLibelle }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
