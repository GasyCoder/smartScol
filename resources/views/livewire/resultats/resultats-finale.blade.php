<div class="p-6 bg-white rounded-lg shadow-sm">
    {{-- En-tête avec filtres --}}
    <div class="mb-6">
        <div class="flex flex-col mb-4 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="mb-4 text-2xl font-bold text-gray-900 lg:mb-0">
                Résultats Finaux des Examens
            </h2>
            <div class="flex flex-wrap gap-2">
                @if($canExport)
                    <button wire:click="exportResults('session1')"
                            class="px-4 py-2 text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700">
                        <i class="mr-2 fas fa-file-excel"></i>Excel Session 1
                    </button>
                    <button wire:click="exportPDF('session1')"
                            class="px-4 py-2 text-white transition-colors bg-red-600 rounded-lg hover:bg-red-700">
                        <i class="mr-2 fas fa-file-pdf"></i>PDF Session 1
                    </button>
                    <button wire:click="exportResults('session2')"
                            class="px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                        <i class="mr-2 fas fa-file-excel"></i>Excel Session 2
                    </button>
                    <button wire:click="exportPDF('session2')"
                            class="px-4 py-2 text-white transition-colors bg-purple-600 rounded-lg hover:bg-purple-700">
                        <i class="mr-2 fas fa-file-pdf"></i>PDF Session 2
                    </button>
                @endif
            </div>
        </div>

        {{-- Filtres --}}
        <div class="grid grid-cols-1 gap-4 p-4 rounded-lg md:grid-cols-2 lg:grid-cols-4 bg-gray-50">
            {{-- Année Universitaire --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Année Universitaire</label>
                <select wire:model.live="selectedAnneeUniversitaire"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Sélectionner...</option>
                    @foreach($anneesUniversitaires as $annee)
                        <option value="{{ $annee->id }}">{{ $annee->libelle }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Niveau --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Niveau</label>
                <select wire:model.live="selectedNiveau"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Sélectionner...</option>
                    @foreach($niveaux as $niveau)
                        <option value="{{ $niveau->id }}">{{ $niveau->nom }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Parcours --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Parcours</label>
                <select wire:model.live="selectedParcours"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                        @if($parcours->isEmpty()) disabled @endif>
                    <option value="">
                        {{ $parcours->isEmpty() ? 'Aucun parcours' : 'Tous les parcours' }}
                    </option>
                    @foreach($parcours as $parcour)
                        <option value="{{ $parcour->id }}">{{ $parcour->nom }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Session --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Session Active</label>
                <select wire:model.live="selectedSession"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Sélectionner...</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}"
                                @if($session->is_current) selected @endif>
                            {{ $session->type }} - {{ $session->anneeUniversitaire->libelle ?? '' }}
                            @if($session->is_current) (Active) @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Statistiques rapides --}}
    @if(!empty($resultatsSession1) || !empty($resultatsSession2))
        <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
            {{-- Stats Session 1 --}}
            <div class="p-4 rounded-lg bg-gradient-to-r from-blue-50 to-blue-100">
                <h3 class="mb-3 text-lg font-semibold text-blue-900">Session 1 (Normale)</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-blue-700">Total étudiants:</span>
                        <span class="font-bold">{{ $statistiquesSession1['total_etudiants'] ?? 0 }}</span>
                    </div>
                    <div>
                        <span class="text-green-700">Admis:</span>
                        <span class="font-bold text-green-800">{{ $statistiquesSession1['admis'] ?? 0 }}</span>
                    </div>
                    <div>
                        <span class="text-orange-700">Rattrapage:</span>
                        <span class="font-bold text-orange-800">{{ $statistiquesSession1['rattrapage'] ?? 0 }}</span>
                    </div>
                    <div>
                        <span class="text-blue-700">Taux réussite:</span>
                        <span class="font-bold">{{ $statistiquesSession1['taux_reussite'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>

            {{-- Stats Session 2 --}}
            <div class="p-4 rounded-lg bg-gradient-to-r from-green-50 to-green-100">
                <h3 class="mb-3 text-lg font-semibold text-green-900">Session 2 (Rattrapage)</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-green-700">Total étudiants:</span>
                        <span class="font-bold">{{ $statistiquesSession2['total_etudiants'] ?? 0 }}</span>
                    </div>
                    <div>
                        <span class="text-green-700">Admis:</span>
                        <span class="font-bold text-green-800">{{ $statistiquesSession2['admis'] ?? 0 }}</span>
                    </div>
                    <div>
                        <span class="text-red-700">Redoublants:</span>
                        <span class="font-bold text-red-800">{{ $statistiquesSession2['redoublant'] ?? 0 }}</span>
                    </div>
                    <div>
                        <span class="text-green-700">Taux réussite:</span>
                        <span class="font-bold">{{ $statistiquesSession2['taux_reussite'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Onglets principaux --}}
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex space-x-8">
            <button wire:click="$set('activeTab', 'session1')"
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'session1' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="mr-2 fas fa-graduation-cap"></i>Résultats 1ère Session
                @if(!empty($resultatsSession1))
                    <span class="px-2 py-1 ml-2 text-xs text-blue-800 bg-blue-100 rounded-full">
                        {{ count($resultatsSession1) }}
                    </span>
                @endif
            </button>

            <button wire:click="$set('activeTab', 'session2')"
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'session2' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="mr-2 fas fa-redo"></i>Résultats 2ème Session
                @if(!empty($resultatsSession2))
                    <span class="px-2 py-1 ml-2 text-xs text-green-800 bg-green-100 rounded-full">
                        {{ count($resultatsSession2) }}
                    </span>
                @endif
            </button>

            <button wire:click="$set('activeTab', 'simulation')"
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'simulation' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="mr-2 fas fa-calculator"></i>Simulation Délibération
            </button>
        </nav>
    </div>

    {{-- Contenu des onglets --}}
    <div class="tab-content">
        {{-- Onglet Session 1 --}}
        @if($activeTab === 'session1')
            <div class="space-y-4">
                @if(empty($resultatsSession1))
                    <div class="py-12 text-center">
                        <i class="mb-4 text-4xl text-gray-400 fas fa-search"></i>
                        <p class="text-lg text-gray-500">Aucun résultat publié pour la 1ère session</p>
                        <p class="text-sm text-gray-400">Vérifiez vos filtres ou que les résultats ont bien été publiés</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border-b">
                                        Étudiant
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase border-b">
                                        Matricule
                                    </th>
                                    @foreach($ecs as $ueNom => $ecsUE)
                                        @foreach($ecsUE as $ec)
                                            <th class="px-3 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase border-b">
                                                {{ $ec->abr }}
                                            </th>
                                        @endforeach
                                    @endforeach
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase border-b">
                                        Moyenne
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase border-b">
                                        Crédits
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase border-b">
                                        Décision
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($resultatsSession1 as $resultat)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $resultat['etudiant']->nom }} {{ $resultat['etudiant']->prenom }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                {{ $resultat['etudiant']->matricule }}
                                            </div>
                                        </td>
                                        @foreach($ecs as $ueNom => $ecsUE)
                                            @foreach($ecsUE as $ec)
                                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                                    @if(isset($resultat['notes'][$ec->id]))
                                                        @php
                                                            $note = $resultat['notes'][$ec->id]->note;
                                                            $noteClass = $note == 0 ? 'text-red-600 font-bold' :
                                                                        ($note < 10 ? 'text-orange-600' : 'text-green-600');
                                                        @endphp
                                                        <span class="text-sm {{ $noteClass }}">
                                                            {{ number_format($note, 2) }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-gray-400">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        @endforeach
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <span class="text-sm font-semibold {{ $resultat['moyenne_generale'] >= 10 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ number_format($resultat['moyenne_generale'], 2) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <span class="text-sm">
                                                {{ $resultat['credits_valides'] }}/60
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            @php
                                                $decisionClass = match($resultat['decision']) {
                                                    'admis' => 'bg-green-100 text-green-800',
                                                    'rattrapage' => 'bg-orange-100 text-orange-800',
                                                    'redoublant' => 'bg-red-100 text-red-800',
                                                    'exclus' => 'bg-red-200 text-red-900',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $decisionClass }}">
                                                {{ $resultat['decision_libelle'] }}
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

        {{-- Onglet Session 2 --}}
        @if($activeTab === 'session2')
            <div class="space-y-4">
                @if(empty($resultatsSession2))
                    <div class="py-12 text-center">
                        <i class="mb-4 text-4xl text-gray-400 fas fa-search"></i>
                        <p class="text-lg text-gray-500">Aucun résultat publié pour la 2ème session</p>
                        <p class="text-sm text-gray-400">Vérifiez vos filtres ou que les résultats ont bien été publiés</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                            <thead class="bg-green-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border-b">
                                        Étudiant
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase border-b">
                                        Matricule
                                    </th>
                                    @foreach($ecs as $ueNom => $ecsUE)
                                        @foreach($ecsUE as $ec)
                                            <th class="px-3 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase border-b">
                                                {{ $ec->abr }}
                                            </th>
                                        @endforeach
                                    @endforeach
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase border-b">
                                        Moyenne
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase border-b">
                                        Crédits
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase border-b">
                                        Décision Finale
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($resultatsSession2 as $resultat)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $resultat['etudiant']->nom }} {{ $resultat['etudiant']->prenom }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                {{ $resultat['etudiant']->matricule }}
                                            </div>
                                        </td>
                                        @foreach($ecs as $ueNom => $ecsUE)
                                            @foreach($ecsUE as $ec)
                                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                                    @if(isset($resultat['notes'][$ec->id]))
                                                        @php
                                                            $note = $resultat['notes'][$ec->id]->note;
                                                            $noteClass = $note == 0 ? 'text-red-600 font-bold' :
                                                                        ($note < 10 ? 'text-orange-600' : 'text-green-600');
                                                        @endphp
                                                        <span class="text-sm {{ $noteClass }}">
                                                            {{ number_format($note, 2) }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-gray-400">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        @endforeach
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <span class="text-sm font-semibold {{ $resultat['moyenne_generale'] >= 10 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ number_format($resultat['moyenne_generale'], 2) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <span class="text-sm">
                                                {{ $resultat['credits_valides'] }}/60
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            @php
                                                $decisionClass = match($resultat['decision']) {
                                                    'admis' => 'bg-green-100 text-green-800',
                                                    'rattrapage' => 'bg-orange-100 text-orange-800',
                                                    'redoublant' => 'bg-red-100 text-red-800',
                                                    'exclus' => 'bg-red-200 text-red-900',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $decisionClass }}">
                                                {{ $resultat['decision_libelle'] }}
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

        {{-- Onglet Simulation --}}
        @if($activeTab === 'simulation')
            <div class="space-y-6">
                {{-- Paramètres de simulation --}}
                <div class="p-6 rounded-lg bg-purple-50">
                    <h3 class="mb-4 text-lg font-semibold text-purple-900">
                        <i class="mr-2 fas fa-cogs"></i>Paramètres de Délibération
                    </h3>

                    <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Seuil d'admission</label>
                            <input type="number" step="0.01" min="0" max="20"
                                   wire:model="simulationParams.seuil_admission"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @error('simulationParams.seuil_admission')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Seuil de rachat</label>
                            <input type="number" step="0.01" min="0" max="20"
                                   wire:model="simulationParams.seuil_rachat"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @error('simulationParams.seuil_rachat')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Crédits requis Session 1</label>
                            <input type="number" min="0" max="100"
                                   wire:model="simulationParams.credits_requis_session1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @error('simulationParams.credits_requis_session1')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Crédits requis Session 2</label>
                            <input type="number" min="0" max="100"
                                   wire:model="simulationParams.credits_requis_session2"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @error('simulationParams.credits_requis_session2')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <p><strong>Note:</strong> La simulation applique les nouveaux paramètres aux résultats existants</p>
                            <p>pour voir l'impact des changements de critères de délibération.</p>
                        </div>
                        <button wire:click="simulerDeliberation"
                                class="px-6 py-2 text-white transition-colors bg-purple-600 rounded-lg hover:bg-purple-700">
                            <i class="mr-2 fas fa-play"></i>Lancer la Simulation
                        </button>
                    </div>

                    @error('simulation')
                        <div class="p-3 mt-4 text-red-700 bg-red-100 border border-red-400 rounded">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Résultats de simulation --}}
                @if(!empty($simulationResults))
                    <div class="bg-white border border-gray-200 rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="mr-2 fas fa-chart-line"></i>Résultats de la Simulation
                            </h3>
                        </div>

                        {{-- Statistiques de simulation --}}
                        <div class="p-6 bg-gray-50">
                            @php
                                $totalSimulation = count($simulationResults);
                                $changements = collect($simulationResults)->where('changement', true)->count();
                                $admisSimulation = collect($simulationResults)->where('decision_simulee', 'admis')->count();
                                $redoublantsSimulation = collect($simulationResults)->where('decision_simulee', 'redoublant')->count();
                            @endphp

                            <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-4">
                                <div class="p-4 bg-white border rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ $totalSimulation }}</div>
                                    <div class="text-sm text-gray-600">Total étudiants</div>
                                </div>
                                <div class="p-4 bg-white border rounded-lg">
                                    <div class="text-2xl font-bold text-orange-600">{{ $changements }}</div>
                                    <div class="text-sm text-gray-600">Changements prévus</div>
                                </div>
                                <div class="p-4 bg-white border rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">{{ $admisSimulation }}</div>
                                    <div class="text-sm text-gray-600">Admis (simulation)</div>
                                </div>
                                <div class="p-4 bg-white border rounded-lg">
                                    <div class="text-2xl font-bold text-red-600">{{ $redoublantsSimulation }}</div>
                                    <div class="text-sm text-gray-600">Redoublants (simulation)</div>
                                </div>
                            </div>
                        </div>

                        {{-- Tableau des résultats détaillés --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Étudiant
                                        </th>
                                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                            Moyenne
                                        </th>
                                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                            Crédits
                                        </th>
                                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                            Décision Actuelle
                                        </th>
                                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                            Décision Simulée
                                        </th>
                                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                            Changement
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($simulationResults as $result)
                                        <tr class="{{ $result['changement'] ? 'bg-yellow-50' : '' }}">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $result['etudiant']->nom }} {{ $result['etudiant']->prenom }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $result['etudiant']->matricule }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                <span class="text-sm {{ $result['moyenne_generale'] >= 10 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($result['moyenne_generale'], 2) }}
                                                </span>
                                                @if($result['has_note_eliminatoire'])
                                                    <span class="ml-1 text-red-500" title="Note éliminatoire">⚠️</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                                                {{ $result['credits_valides'] }}/60
                                            </td>
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                @if($result['decision_actuelle'])
                                                    @php
                                                        $decisionClass = match($result['decision_actuelle']) {
                                                            'admis' => 'bg-green-100 text-green-800',
                                                            'rattrapage' => 'bg-orange-100 text-orange-800',
                                                            'redoublant' => 'bg-red-100 text-red-800',
                                                            'exclus' => 'bg-red-200 text-red-900',
                                                            default => 'bg-gray-100 text-gray-800'
                                                        };
                                                    @endphp
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $decisionClass }}">
                                                        {{ ucfirst($result['decision_actuelle']) }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-400">Non définie</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                @php
                                                    $decisionClass = match($result['decision_simulee']) {
                                                        'admis' => 'bg-green-100 text-green-800',
                                                        'rattrapage' => 'bg-orange-100 text-orange-800',
                                                        'redoublant' => 'bg-red-100 text-red-800',
                                                        'exclus' => 'bg-red-200 text-red-900',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                @endphp
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $decisionClass }}">
                                                    {{ ucfirst($result['decision_simulee']) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                @if($result['changement'])
                                                    <span class="text-orange-600">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </span>
                                                @else
                                                    <span class="text-green-600">
                                                        <i class="fas fa-check"></i>
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
    </div>
</div>
@push('scripts')

{{-- Script pour les interactions --}}
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('simulation-complete', () => {
            // Animation ou notification après simulation
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            notification.innerHTML = '<i class="mr-2 fas fa-check"></i>Simulation terminée avec succès !';
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        });

        Livewire.on('export-results', (data) => {
            // Logique d'export
            console.log('Export des résultats:', data);
        });
    });
</script>
@endpush
