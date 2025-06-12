{{-- Onglet Simulation - MODIFIÉ POUR ÊTRE DISPONIBLE POUR TOUTES LES SESSIONS --}}
@if($activeTab === 'simulation' && (!empty($resultatsSession1) || !empty($resultatsSession2)))
    <div class="space-y-6">
        {{-- Paramètres de simulation --}}
        <div class="p-6 border rounded-lg bg-purple-50 dark:bg-purple-900/20 dark:border-purple-800">
            <h3 class="mb-4 text-lg font-semibold text-purple-900 dark:text-purple-300">
                <em class="mr-2 ni ni-setting"></em>
                Paramètres de Délibération
            </h3>

            {{-- NOUVEAU : Sélection du type de session à simuler --}}
            <div class="mb-6">
                <label class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Session à simuler</label>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    {{-- Option Session 1 si disponible --}}
                    @if(!empty($resultatsSession1))
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ ($simulationParams['session_type'] ?? 'session1') === 'session1' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                            <input type="radio"
                                   wire:model="simulationParams.session_type"
                                   value="session1"
                                   class="w-4 h-4 text-purple-600">
                            <div class="ml-3">
                                <div class="font-medium text-gray-900 dark:text-gray-100">Session 1 (Normale)</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ count($resultatsSession1) }} étudiants • Critères admission directe</div>
                            </div>
                        </label>
                    @endif

                    {{-- Option Session 2 si disponible --}}
                    @if(!empty($resultatsSession2))
                        <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ ($simulationParams['session_type'] ?? 'session1') === 'session2' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                            <input type="radio"
                                   wire:model="simulationParams.session_type"
                                   value="session2"
                                   class="w-4 h-4 text-purple-600">
                            <div class="ml-3">
                                <div class="font-medium text-gray-900 dark:text-gray-100">Session 2 (Rattrapage)</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ count($resultatsSession2) }} étudiants • Critères rattrapage</div>
                            </div>
                        </label>
                    @endif
                </div>
                @error('simulationParams.session_type')
                    <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                @enderror
            </div>

            {{-- PARAMÈTRES SELON LE TYPE DE SESSION SÉLECTIONNÉ --}}
            @if(($simulationParams['session_type'] ?? 'session1') === 'session1')
                {{-- Paramètres pour Session 1 (Normale) --}}
                <div class="p-4 mb-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                    <h4 class="mb-3 font-medium text-blue-900 dark:text-blue-300">
                        <em class="mr-2 ni ni-graduation"></em>
                        Critères Session 1 (Normale)
                    </h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Crédits requis pour admission directe</label>
                            <input type="number" min="40" max="60" step="1"
                                   wire:model="simulationParams.credits_admission_session1"
                                   class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Si ≥ X crédits → Admis, sinon → Rattrapage</p>
                            @error('simulationParams.credits_admission_session1')
                                <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox"
                                   wire:model="simulationParams.appliquer_note_eliminatoire_s1"
                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 dark:border-gray-600">
                            <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                Bloquer admission si note éliminatoire
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Note = 0 empêche admission directe</span>
                            </label>
                        </div>
                    </div>
                </div>

            @elseif(($simulationParams['session_type'] ?? 'session1') === 'session2')
                {{-- Paramètres pour Session 2 (Rattrapage) --}}
                <div class="p-4 mb-4 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                    <h4 class="mb-3 font-medium text-green-900 dark:text-green-300">
                        <em class="mr-2 ni ni-repeat"></em>
                        Critères Session 2 (Rattrapage)
                    </h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Crédits minimum admission</label>
                            <input type="number" min="30" max="60" step="1"
                                   wire:model="simulationParams.credits_admission_session2"
                                   class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Si ≥ X crédits → Admis</p>
                            @error('simulationParams.credits_admission_session2')
                                <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Crédits minimum redoublement</label>
                            <input type="number" min="0" max="40" step="1"
                                   wire:model="simulationParams.credits_redoublement_session2"
                                   class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Si < X crédits → Exclus</p>
                            @error('simulationParams.credits_redoublement_session2')
                                <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox"
                                   wire:model="simulationParams.appliquer_note_eliminatoire_s2"
                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 dark:border-gray-600">
                            <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                Exclusion automatique
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Note = 0 → Exclu</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Bouton simulation et informations --}}
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p><strong>Simulation :</strong> Applique les nouveaux critères de crédits aux résultats existants</p>
                    <p>pour analyser l'impact des modifications des seuils de délibération.</p>
                </div>
                <button wire:click="simulerDeliberation"
                        class="px-6 py-2 text-white transition-colors bg-purple-600 rounded-lg hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-600"
                        @if(empty($simulationParams['session_type'] ?? '')) disabled @endif>
                    <em class="mr-2 ni ni-play"></em>
                    Simuler Délibération
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
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <em class="mr-2 ni ni-bar-chart"></em>
                            Résultats de la Simulation
                        </h3>
                        <span class="px-3 py-1 text-sm font-medium text-purple-700 bg-purple-100 rounded-full dark:text-purple-300 dark:bg-purple-900/50">
                            {{ ucfirst($simulationParams['session_type'] ?? 'session1') }}
                        </span>
                    </div>
                </div>

                {{-- Statistiques de simulation --}}
                <div class="p-6 bg-gray-50 dark:bg-gray-700/50">
                    @php
                        $totalSimulation = count($simulationResults);
                        $changements = collect($simulationResults)->where('changement', true)->count();
                        $admisSimulation = collect($simulationResults)->where('decision_simulee', 'admis')->count();
                        $rattrapageSimulation = collect($simulationResults)->where('decision_simulee', 'rattrapage')->count();
                        $redoublantsSimulation = collect($simulationResults)->where('decision_simulee', 'redoublant')->count();
                        $exclusSimulation = collect($simulationResults)->where('decision_simulee', 'exclus')->count();
                    @endphp

                    <div class="grid grid-cols-2 gap-4 mb-4 md:grid-cols-6">
                        <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalSimulation }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total étudiants</div>
                        </div>
                        <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $changements }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Changements</div>
                        </div>
                        <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $admisSimulation }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Admis</div>
                        </div>

                        {{-- Affichage conditionnel selon le type de session --}}
                        @if(($simulationParams['session_type'] ?? 'session1') === 'session1')
                            <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $rattrapageSimulation }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Rattrapage</div>
                            </div>
                        @else
                            <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $redoublantsSimulation }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Redoublants</div>
                            </div>
                            <div class="p-4 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                <div class="text-2xl font-bold text-red-800 dark:text-red-300">{{ $exclusSimulation }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Exclus</div>
                            </div>
                        @endif
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
                                    Impact
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach($simulationResults as $result)
                                <tr class="{{ $result['changement'] ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $result['etudiant']['nom'] ?? $result['etudiant']->nom }} {{ $result['etudiant']['prenom'] ?? $result['etudiant']->prenom }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $result['etudiant']['matricule'] ?? $result['etudiant']->matricule }}
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
                                        <span class="font-medium">{{ $result['credits_valides'] }}</span>/60
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
            </div>
        @endif
    </div>
@endif

{{-- Messages d'indisponibilité MODIFIÉS --}}
@if($activeTab === 'simulation' && empty($resultatsSession1) && empty($resultatsSession2))
    <div class="py-12 text-center">
        <em class="text-6xl text-purple-400 ni ni-setting dark:text-purple-500"></em>
        <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
            Simulation de délibération non disponible
        </p>
        <p class="text-sm text-gray-400 dark:text-gray-500">
            @if($selectedNiveau && $selectedAnneeUniversitaire)
                Aucun résultat publié pour effectuer une simulation pour ce niveau.
            @else
                Veuillez d'abord sélectionner un niveau et une année universitaire.
            @endif
        </p>
    </div>
@endif 
