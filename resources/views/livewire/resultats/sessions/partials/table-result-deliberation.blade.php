
    {{-- ✅ TABLEAU DES RÉSULTATS DÉTAILLÉS AVEC PROTECTION --}}
    @if(!empty($simulationDeliberation['resultats_detailles']))
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                            Rang
                        </th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                            Étudiant
                        </th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                            Moyenne
                        </th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                            Crédits
                        </th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                            Décision Actuelle
                        </th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                            Décision Simulée
                        </th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                            Impact
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach($simulationDeliberation['resultats_detailles'] as $index => $result)
                        <tr class="{{ ($result['changement'] ?? false) ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                            {{-- Rang --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ ($result['rang'] ?? ($index + 1)) }}
                                </span>
                            </td>

                            {{-- Informations étudiant avec protection --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ ($result['etudiant']['nom'] ?? $result['nom'] ?? 'Nom inconnu') }} {{ ($result['etudiant']['prenom'] ?? $result['prenom'] ?? 'Prénom inconnu') }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ ($result['etudiant']['matricule'] ?? $result['matricule'] ?? 'Matricule inconnu') }}
                                </div>
                            </td>

                            {{-- Moyenne --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="text-sm font-medium {{ (($result['moyenne_generale'] ?? 0) >= 10) ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ number_format(($result['moyenne_generale'] ?? 0), 2) }}
                                </span>
                                @if($result['has_note_eliminatoire'] ?? false)
                                    <span class="ml-1 text-red-500 dark:text-red-400" title="Note éliminatoire">⚠️</span>
                                @endif
                            </td>

                            {{-- Crédits --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ ($result['credits_valides'] ?? $result['total_credits'] ?? 0) }}/60
                                </span>
                            </td>

                            {{-- Décision actuelle --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if(isset($result['decision_actuelle']) && !empty($result['decision_actuelle']))
                                    @php
                                        $decisionClass = match($result['decision_actuelle']) {
                                            'admis' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300',
                                            'rattrapage' => 'bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-300',
                                            'redoublant' => 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300',
                                            'exclus' => 'bg-red-200 dark:bg-red-900/70 text-red-900 dark:text-red-200',
                                            default => 'bg-gray-100 dark:bg-gray-900/50 text-gray-800 dark:text-gray-300'
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $decisionClass }}">
                                        {{ ucfirst($result['decision_actuelle']) }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full dark:bg-gray-900/50 dark:text-gray-300">
                                        Non définie
                                    </span>
                                @endif
                            </td>

                            {{-- Décision simulée --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @php
                                    $decisionSimulee = $result['decision_simulee'] ?? 'non_definie';
                                    $decisionClass = match($decisionSimulee) {
                                        'admis' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300',
                                        'rattrapage' => 'bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-300',
                                        'redoublant' => 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300',
                                        'exclus' => 'bg-red-200 dark:bg-red-900/70 text-red-900 dark:text-red-200',
                                        default => 'bg-gray-100 dark:bg-gray-900/50 text-gray-800 dark:text-gray-300'
                                    };
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $decisionClass }}">
                                    {{ ucfirst($decisionSimulee) }}
                                </span>
                            </td>

                            {{-- Impact --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($result['changement'] ?? false)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full dark:text-orange-300 dark:bg-orange-900/50">
                                        <em class="mr-1 ni ni-alert-circle"></em>
                                        Modifié
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:text-green-300 dark:bg-green-900/50">
                                        <em class="mr-1 ni ni-check-circle"></em>
                                        Identique
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ✅ SECTION EXPORT APRÈS LE TABLEAU --}}
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-medium">📊 Résultats de simulation prêts pour export</p>
                    <p>{{ $totalSimulation }} étudiants • {{ $changements }} changements détectés</p>
                </div>
                <div class="flex space-x-2">
                    {{-- Export rapide colonnes essentielles --}}
                    <button wire:click="exporterAdmisRapide('pdf')"
                            class="px-3 py-2 text-sm text-red-700 bg-red-100 rounded-lg hover:bg-red-200 dark:text-red-300 dark:bg-red-900/50 dark:hover:bg-red-900">
                        <em class="mr-1 ni ni-file-pdf"></em>
                        Export admis en PDF
                    </button>
                    <button wire:click="exporterTousSimulation('excel')"
                            class="px-3 py-2 text-sm text-green-700 bg-green-100 rounded-lg hover:bg-green-200 dark:text-green-300 dark:bg-green-900/50 dark:hover:bg-green-900">
                        <em class="mr-1 ni ni-file-xls"></em>
                        Export en Excel
                    </button>
                    {{-- Export configuré --}}
                    <button wire:click="ouvrirModalExport('pdf', 'simulation')"
                            class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        <em class="mr-2 ni ni-settings"></em>
                        Export Configuré
                    </button>
                </div>
            </div>
        </div>
    @endif