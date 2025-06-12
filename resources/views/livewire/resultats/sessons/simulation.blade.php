        {{-- Onglet Simulation --}}
        @if($activeTab === 'simulation' && $showSession2)
            <div class="space-y-6">
                {{-- Paramètres de simulation --}}
                <div class="p-6 border rounded-lg bg-purple-50 dark:bg-purple-900/20 dark:border-purple-800">
                    <h3 class="mb-4 text-lg font-semibold text-purple-900 dark:text-purple-300">
                        <em class="mr-2 ni ni-setting"></em>
                        Paramètres de Délibération (Session 2)
                    </h3>

                    <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Crédits requis Session 2</label>
                            <input type="number" min="0" max="60"
                                   wire:model="simulationParams.credits_requis_session2"
                                   class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400">
                            @error('simulationParams.credits_requis_session2')
                                <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Note éliminatoire</label>
                            <div class="flex items-center">
                                <input type="checkbox"
                                       wire:model="simulationParams.appliquer_note_eliminatoire"
                                       class="w-4 h-4 text-purple-600 border-gray-300 rounded dark:text-purple-400 focus:ring-purple-500 dark:focus:ring-purple-400 dark:border-gray-600">
                                <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Appliquer règle note = 0</label>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p><strong>Note:</strong> La simulation applique les nouveaux paramètres aux résultats existants</p>
                            <p>pour voir l'impact des changements de critères de délibération.</p>
                        </div>
                        <button wire:click="simulerDeliberation"
                                class="px-6 py-2 text-white transition-colors bg-purple-600 rounded-lg hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-600">
                            <em class="mr-2 ni ni-play"></em>
                            Lancer la Simulation
                        </button>
                    </div>

                    @error('simulation')
                        <div class="p-3 mt-4 text-red-700 bg-red-100 border border-red-400 rounded dark:text-red-300 dark:bg-red-900/50 dark:border-red-800">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Résultats de simulation --}}
                @if(!empty($simulationResults))
                    <div class="bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <em class="mr-2 ni ni-bar-chart"></em>
                                Résultats de la Simulation
                            </h3>
                        </div>

                        {{-- Statistiques de simulation --}}
                        <div class="p-6 bg-gray-50 dark:bg-gray-700/50">
                            @php
                                $totalSimulation = count($simulationResults);
                                $changements = collect($simulationResults)->where('changement', true)->count();
                                $admisSimulation = collect($simulationResults)->where('decision_simulee', 'admis')->count();
                                $redoublantsSimulation = collect($simulationResults)->where('decision_simulee', 'redoublant')->count();
                                $exclusSimulation = collect($simulationResults)->where('decision_simulee', 'exclus')->count();
                            @endphp

                            <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-5">
                                <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalSimulation }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Total étudiants</div>
                                </div>
                                <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $changements }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Changements prévus</div>
                                </div>
                                <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $admisSimulation }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Admis (simulation)</div>
                                </div>
                                <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $redoublantsSimulation }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Redoublants (simulation)</div>
                                </div>
                                <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                    <div class="text-2xl font-bold text-red-800 dark:text-red-300">{{ $exclusSimulation }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Exclus (simulation)</div>
                                </div>
                            </div>
                        </div>

                        {{-- Tableau des résultats détaillés --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
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
                                            Changement
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    @foreach($simulationResults as $result)
                                        <tr class="{{ $result['changement'] ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $result['etudiant']->nom }} {{ $result['etudiant']->prenom }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $result['etudiant']->matricule }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                <span class="text-sm {{ $result['moyenne_generale'] >= 10 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ number_format($result['moyenne_generale'], 2) }}
                                                </span>
                                                @if($result['has_note_eliminatoire'])
                                                    <span class="ml-1 text-red-500 dark:text-red-400" title="Note éliminatoire">⚠️</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center text-gray-900 whitespace-nowrap dark:text-gray-100">
                                                {{ $result['credits_valides'] }}/60
                                            </td>
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                @if($result['decision_actuelle'])
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
                                                    <span class="text-xs text-gray-400 dark:text-gray-600">Non définie</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                @php
                                                    $decisionClass = match($result['decision_simulee']) {
                                                        'admis' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300',
                                                        'rattrapage' => 'bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-300',
                                                        'redoublant' => 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300',
                                                        'exclus' => 'bg-red-200 dark:bg-red-900/70 text-red-900 dark:text-red-200',
                                                        default => 'bg-gray-100 dark:bg-gray-900/50 text-gray-800 dark:text-gray-300'
                                                    };
                                                @endphp
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $decisionClass }}">
                                                    {{ ucfirst($result['decision_simulee']) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                @if($result['changement'])
                                                    <span class="text-orange-600 dark:text-orange-400">
                                                        <em class="ni ni-alert-circle"></em>
                                                    </span>
                                                @else
                                                    <span class="text-green-600 dark:text-green-400">
                                                        <em class="ni ni-check-circle"></em>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Messages d'indisponibilité --}}
        @if(($activeTab === 'session2' && !$showSession2) || ($activeTab === 'simulation' && !$showSession2))
            <div class="py-12 text-center">
                <em class="text-6xl text-blue-400 ni ni-info dark:text-blue-500"></em>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
                    {{ $activeTab === 'session2' ? 'Session 2 non disponible' : 'Simulation non disponible' }}
                </p>
                <p class="text-sm text-gray-400 dark:text-gray-500">
                    @if($selectedNiveau && $selectedAnneeUniversitaire)
                        {{ $activeTab === 'session2' ?
                           'Aucun résultat publié pour la session de rattrapage de ce niveau.' :
                           'La simulation de délibération nécessite des résultats de session 2.' }}
                    @else
                        Veuillez d'abord sélectionner un niveau et une année universitaire.
                    @endif
                </p>
            </div>
        @endif
