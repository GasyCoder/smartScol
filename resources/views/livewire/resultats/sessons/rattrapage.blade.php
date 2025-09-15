{{-- Session 2 (RATTRAPAGE) - MISE À JOUR COMPLÈTE --}}
@if($activeTab === 'session2' && $showSession2)
    <div class="space-y-4">
        @if(empty($resultatsSession2))
            <div class="py-12 text-center">
                <em class="text-6xl text-gray-400 ni ni-file-docs dark:text-gray-600"></em>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
                    Aucun résultat publié pour la session de rattrapage
                </p>
                <p class="text-sm text-gray-400 dark:text-gray-500">
                    Vérifiez vos filtres ou que les résultats ont bien été publiés
                </p>

                {{-- Bouton pour recharger avec détection auto --}}
                <div class="mt-4 space-y-2">
                    <button wire:click="refreshResultats"
                            wire:loading.attr="disabled"
                            class="px-6 py-3 text-sm text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <em class="mr-2 ni ni-reload" wire:loading.class="animate-spin" wire:target="refreshResultats"></em>
                        <span wire:loading.remove wire:target="refreshResultats">Recharger les données</span>
                        <span wire:loading wire:target="refreshResultats">Rechargement...</span>
                    </button>
                    {{-- Auto-refresh si délibération récente --}}
                    @if(isset($deliberationStatus['session2']) && $deliberationStatus['session2'])
                        <script>
                            // Auto-refresh après délibération si pas de données
                            setTimeout(() => {
                                console.log('🔄 Auto-refresh session 2 après délibération détectée');
                                @this.refreshResultats();
                            }, 1000);
                        </script>
                    @endif
                </div>
            </div>
        @else
            <!-- Alerte de consultation -->
            <div class="p-4 border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700 rounded-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <em class="mr-3 text-green-600 ni ni-repeat dark:text-green-400"></em>
                        <div>
                            <h4 class="text-sm font-medium text-green-900 dark:text-green-100">Session 2 - Mode Consultation</h4>
                            <p class="text-sm text-green-700 dark:text-green-300">
                                Les résultats de la session de rattrapage sont publiés.
                                @if(isset($deliberationStatus['session2']['delibere']) && $deliberationStatus['session2']['delibere'])
                                    <span class="font-semibold">Délibération appliquée le {{ $deliberationStatus['session2']['date_deliberation'] ?? 'N/A' }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Badge du statut de délibération --}}
                    @if(isset($deliberationStatus['session2']['delibere']) && $deliberationStatus['session2']['delibere'])
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

            {{-- Timestamp et refresh avec indicateur temps réel --}}
            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center space-x-2">
                        <em class="ni ni-clock"></em>
                        <span>Dernière actualisation :</span>
                        <span class="font-medium" id="last-update-time-s2">
                            {{ now()->format('d/m/Y à H:i:s') }}
                        </span>
                    </div>

                    {{-- Indicateur de fraîcheur des données --}}
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse" title="Données à jour"></div>
                        <span class="text-xs">Données synchronisées</span>
                    </div>
                </div>

                {{-- Boutons d'export et rafraîchissement --}}
                <div class="flex items-center space-x-2">
                    <div class="flex items-center space-x-3">
                        {{-- Bouton Export Excel --}}
                        {{-- <button wire:click="exporterExcel" 
                                wire:loading.attr="disabled"
                                class="flex items-center px-4 py-2 text-sm font-medium text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                            <em class="mr-2 ni ni-file-excel" wire:loading.class="animate-spin" wire:target="exporterExcel"></em>
                            <span wire:loading.remove wire:target="exporterExcel">Export tableau Excel</span>
                            <span wire:loading wire:target="exporterExcel">Export...</span>
                        </button> --}}
                        {{-- Bouton Actualiser --}}
                        <button wire:click="forceReloadData"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50"
                                class="flex items-center px-4 py-2 text-sm text-blue-600 transition-colors bg-blue-100 rounded-lg hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-400 dark:hover:bg-blue-800/50">
                            <em class="mr-2 ni ni-reload" wire:loading.class="animate-spin" wire:target="forceReloadData,refreshResultats"></em>
                            <span wire:loading.remove wire:target="forceReloadData,refreshResultats">Actualiser</span>
                            <span wire:loading wire:target="forceReloadData,refreshResultats">Actualisation...</span>
                        </button>
                    </div>

                    {{-- Indicateur de dernière délibération --}}
                    @if(isset($deliberationStatus['session2']['date_deliberation']))
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Délibéré: {{ \Carbon\Carbon::parse($deliberationStatus['session2']['date_deliberation'])->format('H:i') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Statistiques avec mise à jour en temps réel --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>{{ count($resultatsSession2) }}</strong> étudiant(s) affiché(s)
                    </span>

                    {{-- Stats avec mise à jour temps réel --}}
                    @if(isset($statistiquesSession2) && !empty($statistiquesSession2))
                        <div class="flex items-center space-x-2 text-sm" id="stats-display-s2">
                            <span class="px-2 py-1 font-medium text-green-700 bg-green-100 rounded-full dark:bg-green-900/50 dark:text-green-300">
                                ✅ {{ $statistiquesSession2['admis'] ?? 0 }} admis
                            </span>
                            <span class="px-2 py-1 font-medium text-orange-700 bg-orange-100 rounded-full dark:bg-orange-900/50 dark:text-orange-300">
                                📚 {{ $statistiquesSession2['rattrapage'] ?? 0 }} rattrapage
                            </span>
                            @if(isset($statistiquesSession2['redoublant']) && $statistiquesSession2['redoublant'] > 0)
                                <span class="px-2 py-1 font-medium text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-900/50 dark:text-yellow-300">
                                    🔄 {{ $statistiquesSession2['redoublant'] }} redoublant
                                </span>
                            @endif
                            @if(isset($statistiquesSession2['exclus']) && $statistiquesSession2['exclus'] > 0)
                                <span class="px-2 py-1 font-medium text-red-700 bg-red-100 rounded-full dark:bg-red-900/50 dark:text-red-300">
                                    ❌ {{ $statistiquesSession2['exclus'] }} exclus
                                </span>
                            @endif

                            {{-- Taux de réussite calculé en temps réel --}}
                            @php
                                $totalEtudiants = $statistiquesSession2['total_etudiants'] ?? count($resultatsSession2);
                                $admis = $statistiquesSession2['admis'] ?? 0;
                                $tauxReussite = $totalEtudiants > 0 ? round(($admis / $totalEtudiants) * 100, 1) : 0;
                            @endphp
                            <span class="px-2 py-1 font-medium text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/50 dark:text-blue-300">
                                📊 {{ $tauxReussite }}% réussite
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TABLEAU COMPLET Session 2 avec structure identique à Session 1 --}}
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
                                
                            @foreach($uesStructure as $ueStructure)
                                @php
                                    $ue = $ueStructure['ue'];
                                    $nombreECs = count($ueStructure['ecs']) + 1;
                                    
                                    // Extraire seulement la partie utile du nom (après l'abréviation)
                                    $nomPropre = $ue->nom;
                                    if ($ue->abr && str_starts_with($nomPropre, $ue->abr . '.')) {
                                        $nomPropre = trim(substr($nomPropre, strlen($ue->abr) + 1));
                                    }
                                @endphp
                                
                                <th colspan="{{ $nombreECs }}" 
                                    class="px-3 py-2 text-xs font-bold tracking-wider text-center text-gray-900 uppercase bg-gray-100 border-b border-r dark:text-gray-100 dark:border-gray-700 dark:bg-gray-800">
                                    <div class="flex flex-col items-center">
                                        <span class="font-bold text-green-600 dark:text-green-400">{{ $ue->abr }}</span>
                                        <span class="text-xs font-normal text-gray-600 dark:text-gray-400">{{ $nomPropre }}</span>
                                        <span class="text-xs font-normal text-green-600 dark:text-green-400">({{ $ue->credits }} crédits)</span>
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
                        <tr>
                            @foreach($uesStructure as $ueStructure)
                                {{-- ECs de cette UE --}}
                                @foreach($ueStructure['ecs'] as $ecData)
                                    @php
                                        $ec = $ecData['ec'];
                                        
                                        // Extraire seulement la partie utile du nom EC (après l'abréviation)
                                        $nomEcPropre = trim($ec->nom);
                                        if ($ec->abr && str_starts_with($nomEcPropre, $ec->abr . '.')) {
                                            $nomEcPropre = trim(substr($nomEcPropre, strlen($ec->abr) + 1));
                                        }
                                    @endphp

                                    <th class="px-2 py-2 text-xs font-medium tracking-wider text-center text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700"
                                        title="{{ $ec->abr }}. {{ $nomEcPropre }} - {{ $ec->enseignant }}">
                                        <div class="flex flex-col">
                                            <span class="font-bold">{{ $ec->abr }}</span>
                                            <span class="text-xs font-normal">{{ Str::limit($nomEcPropre, 20) }}</span>
                                            @if($ec->enseignant)
                                                <span class="text-xs italic text-gray-500">[{{ Str::limit(trim($ec->enseignant), 12) }}]</span>
                                            @endif
                                        </div>
                                    </th>
                                @endforeach
                                
                                {{-- Moyenne UE --}}
                                <th class="px-2 py-2 text-xs font-bold tracking-wider text-center text-gray-900 uppercase bg-gray-200 border-b border-r dark:text-gray-100 dark:border-gray-700 dark:bg-gray-700">
                                    <span>Moy.</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($resultatsSession2 as $indexEtudiant => $resultat)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50" data-etudiant-id="{{ $resultat['etudiant']->id }}">
                                {{-- Ordre --}}
                                <td class="px-4 py-3 text-center border-r whitespace-nowrap dark:border-gray-700">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $indexEtudiant + 1 }}
                                    </div>
                                </td>
                                {{-- Matricule --}}
                                <td class="px-4 py-3 text-center border-r whitespace-nowrap dark:border-gray-700">
                                    <div class="font-mono text-sm text-gray-900 dark:text-gray-100">
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
                                                <span class="text-sm {{ $note >= 10 ? 'text-green-600 dark:text-green-400 font-semibold' : ($note == 0 ? 'text-red-600 dark:text-red-400 font-bold' : 'text-orange-600 dark:text-orange-400') }}">
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
                                    {{-- Moyenne UE selon logique médecine --}}
                                    <td class="px-2 py-3 text-center border-r whitespace-nowrap dark:border-gray-700">
                                        @php
                                            // Appliquer la logique académique exacte
                                            if ($hasNoteZeroInUE) {
                                                // UE éliminée à cause d'une note de 0
                                                $moyenneUE = 0;
                                                $moyenneDisplay = '0.00';
                                                $ueValidee = false;
                                                $moyenneClass = 'text-red-600 dark:text-red-400 font-bold';
                                                $statutUE = 'eliminee';
                                            } elseif (!empty($notesUE)) {
                                                // Moyenne UE = somme notes / nombre EC
                                                $moyenneUE = array_sum($notesUE) / count($notesUE);
                                                $ueValidee = $moyenneUE >= 10;
                                                $moyenneDisplay = number_format($moyenneUE, 2);
                                                $moyenneClass = $ueValidee ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-orange-600 dark:text-orange-400';
                                                $statutUE = $ueValidee ? 'validee' : 'non_validee';
                                            } else {
                                                $moyenneUE = 0;
                                                $moyenneDisplay = '-';
                                                $ueValidee = false;
                                                $moyenneClass = 'text-gray-500 dark:text-gray-400';
                                                $statutUE = 'non_disponible';
                                            }
                                        @endphp
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="text-sm font-bold {{ $moyenneClass }}">
                                                {{ $moyenneDisplay }}
                                            </span>
                                        </div>
                                    </td>
                                @endforeach
                                {{-- Moyenne générale --}}
                                <td class="px-4 py-3 text-center border-l-2 border-r whitespace-nowrap border-l-gray-400 dark:border-gray-700 dark:border-l-gray-500">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-bold {{ $resultat['moyenne_generale'] >= 10 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($resultat['moyenne_generale'], 2) }}
                                        </span>
                                        @if(isset($resultat['has_note_eliminatoire']) && $resultat['has_note_eliminatoire'])
                                            <span class="mt-1 text-xs text-red-500" title="Présence de note(s) éliminatoire(s)">⚠️</span>
                                        @endif
                                    </div>
                                </td>
                                {{-- Crédits --}}
                                <td class="px-4 py-4 text-center border-r whitespace-nowrap dark:border-gray-700">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $resultat['credits_valides'] }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            /{{ $resultat['total_credits'] ?? 60 }}
                                        </span>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1 dark:bg-gray-700">
                                            @php
                                                $totalCredits = $resultat['total_credits'] ?? 60;
                                                $pourcentage = $totalCredits > 0 ? ($resultat['credits_valides'] / $totalCredits) * 100 : 0;
                                            @endphp
                                            <div class="h-1.5 rounded-full {{ $pourcentage >= 67 ? 'bg-green-600' : ($pourcentage >= 33 ? 'bg-orange-500' : 'bg-red-600') }}"
                                                style="width: {{ min($pourcentage, 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                
                                {{-- Décision avec indicateur jury --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @php
                                        $decision = $resultat['decision'];
                                        $juryValidated = $resultat['jury_validated'] ?? false;
                                        $hasNoteEliminatoire = $resultat['has_note_eliminatoire'] ?? false;

                                        // Couleurs selon décision avec emphasis si délibéré
                                        $baseClass = match($decision) {
                                            'admis' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                            'rattrapage' => 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/50 dark:text-orange-300 dark:border-orange-700',
                                            'redoublant' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
                                            'exclus' => 'bg-red-200 text-red-900 dark:bg-red-900/70 dark:text-red-200',
                                            default => 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-100'
                                        };

                                        // Ajouter des effets si délibéré
                                        $finalClass = $juryValidated
                                            ? $baseClass . ' ring-2 ring-blue-300 shadow-md'
                                            : $baseClass . ' ';

                                        $decisionLibelle = match($decision) {
                                            'admis' => 'Admis',
                                            'rattrapage' => 'Rattrapage',
                                            'redoublant' => 'Redoublant',
                                            'exclus' => 'Exclus',
                                            default => 'Non définie'
                                        };
                                    @endphp

                                    <div class="flex flex-col items-center space-y-2">
                                        {{-- Badge principal --}}
                                        <span class="px-3 py-1 text-xs rounded-full {{ $finalClass }}">
                                            {{ $decisionLibelle }}
                                            @if($hasNoteEliminatoire)
                                                <span class="ml-1" title="Note éliminatoire">⚠️</span>
                                            @endif
                                        </span>

                                        {{-- Indicateur source de la décision --}}
                                        @if($juryValidated)
                                            <div class="flex items-center px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/50 dark:text-blue-300">
                                                <em class="mr-1 ni ni-shield-check"></em>
                                                <span class="font-semibold">Délibération</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Légende et informations supplémentaires --}}
            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                <h5 class="mb-3 text-sm font-medium text-gray-900 dark:text-gray-100">Légende Session de Rattrapage :</h5>
                <div class="grid grid-cols-2 gap-4 text-xs text-gray-600 md:grid-cols-4 dark:text-gray-400">
                    <div class="flex items-center">
                        <span class="w-3 h-3 mr-2 bg-green-100 border border-green-200 rounded"></span>
                        <span>Note ≥ 10 (Validée)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 mr-2 bg-orange-100 border border-orange-200 rounded"></span>
                        <span>Note < 10 (Non validée)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 mr-2 bg-red-100 border border-red-200 rounded"></span>
                        <span>Note = 0 (Éliminatoire)</span>
                    </div>
                    <div class="flex items-center">
                        <em class="mr-2 text-blue-600 ni ni-shield-check"></em>
                        <span>Validé par jury</span>
                    </div>
                </div>

                {{-- Spécificités Session 2 --}}
                <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                    <h6 class="mb-2 text-xs font-medium text-gray-800 dark:text-gray-200">Session de Rattrapage :</h6>
                    <div class="grid grid-cols-2 gap-4 text-xs text-gray-600 md:grid-cols-4 dark:text-gray-400">
                        <div class="flex items-center">
                            <span class="mr-2 text-green-600">✅</span>
                            <span>≥ 75% crédits = Admis</span>
                        </div>
                        <div class="flex items-center">
                            <span class="mr-2 text-yellow-600">🔄</span>
                            <span>≥ 33% crédits = Redoublant</span>
                        </div>
                        <div class="flex items-center">
                            <span class="mr-2 text-red-600">❌</span>
                            <span>< 33% crédits = Exclusion</span>
                        </div>
                        <div class="flex items-center">
                            <span class="mr-2 text-red-600">⚠️</span>
                            <span>Note 0 = Exclusion</span>
                        </div>
                    </div>
                </div>

                {{-- Légende pour les changements de délibération --}}
                @if(isset($deliberationStatus['session2']['delibere']) && $deliberationStatus['session2']['delibere'])
                    <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                        <h6 class="mb-2 text-xs font-medium text-gray-800 dark:text-gray-200">Délibération Session 2 :</h6>
                        <div class="grid grid-cols-2 gap-4 text-xs text-gray-600 md:grid-cols-3 dark:text-gray-400">
                            <div class="flex items-center">
                                <span class="mr-2 animate-bounce">🔄</span>
                                <span>Décision modifiée</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 mr-2 bg-green-200 border-2 border-green-400 rounded animate-pulse"></span>
                                <span>Promotion validée</span>
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2 text-green-600">✨</span>
                                <span>Validation jury</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif