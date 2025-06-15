{{-- ✅ AJOUT : Indicateur de rechargement après délibération --}}
<div wire:loading.delay wire:target="appliquerDeliberation,annulerDeliberation,actualiserDonneesApresDeliberation,refreshResultats,forceReloadData"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="p-6 bg-white rounded-lg shadow-xl dark:bg-gray-800">
        <div class="flex items-center space-x-3">
            <svg class="w-6 h-6 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Mise à jour des résultats en cours...
            </span>
        </div>
    </div>
</div>

{{-- ✅ AJOUT : Notification de mise à jour réussie --}}
@if (session()->has('deliberation_applied'))
    <div class="p-4 mb-4 border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700 rounded-xl">
        <div class="flex items-center">
            <em class="mr-3 text-green-600 ni ni-check-circle dark:text-green-400"></em>
            <div>
                <h4 class="text-sm font-medium text-green-900 dark:text-green-100">Délibération appliquée</h4>
                <p class="text-sm text-green-700 dark:text-green-300">Les résultats et statistiques ont été mis à jour avec succès.</p>
            </div>
        </div>
    </div>
@endif

{{-- ✅ AJOUT : Détection automatique de mise à jour nécessaire --}}
<div x-data="{
    needsRefresh: false,
    checkForUpdates() {
        this.needsRefresh = true;
        setTimeout(() => {
            $wire.refreshResultats();
            this.needsRefresh = false;
        }, 500);
    }
}"
x-show="needsRefresh"
@resultats-actualises.window="checkForUpdates()"
class="p-3 mb-4 border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700 rounded-xl">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <em class="mr-3 text-blue-600 ni ni-info dark:text-blue-400"></em>
            <span class="text-sm text-blue-700 dark:text-blue-300">Mise à jour des résultats en cours...</span>
        </div>
        <div class="w-6 h-6 border-2 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
    </div>
</div>

{{-- Onglet Session 1 (Consultation uniquement) --}}
@if($activeTab === 'session1')
    <div class="space-y-4">
        @if(empty($resultatsSession1))
            <div class="py-12 text-center">
                <em class="text-6xl text-gray-400 ni ni-file-docs dark:text-gray-600"></em>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
                    Aucun résultat publié pour la 1ère session
                </p>
                <p class="text-sm text-gray-400 dark:text-gray-500">
                    Vérifiez vos filtres ou que les résultats ont bien été publiés
                </p>

                {{-- ✅ AJOUT : Bouton pour recharger avec détection auto --}}
                <div class="mt-4 space-y-2">
                    <button wire:click="refreshResultats"
                            wire:loading.attr="disabled"
                            class="px-6 py-3 text-sm text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <em class="mr-2 ni ni-reload" wire:loading.class="animate-spin" wire:target="refreshResultats"></em>
                        <span wire:loading.remove wire:target="refreshResultats">Recharger les données</span>
                        <span wire:loading wire:target="refreshResultats">Rechargement...</span>
                    </button>

                    {{-- ✅ Auto-refresh si délibération récente --}}
                    @if(isset($deliberationStatus['session1']) && $deliberationStatus['session1'])
                        <script>
                            // Auto-refresh après délibération si pas de données
                            setTimeout(() => {
                                console.log('🔄 Auto-refresh après délibération détectée');
                                @this.refreshResultats();
                            }, 1000);
                        </script>
                    @endif
                </div>
            </div>
        @else
            <!-- Alerte de consultation -->
            <div class="p-4 border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700 rounded-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <em class="mr-3 text-blue-600 ni ni-info dark:text-blue-400"></em>
                        <div>
                            <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">Session 1 - Mode Consultation</h4>
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Les résultats de la session normale sont publiés.
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

            {{-- ✅ AMÉLIORATION : Timestamp et refresh avec indicateur temps réel --}}
            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center space-x-2">
                        <em class="ni ni-clock"></em>
                        <span>Dernière actualisation :</span>
                        <span class="font-medium" id="last-update-time">
                            {{ now()->format('d/m/Y à H:i:s') }}
                        </span>
                    </div>

                    {{-- ✅ Indicateur de fraîcheur des données --}}
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse" title="Données à jour"></div>
                        <span class="text-xs">Données synchronisées</span>
                    </div>
                </div>

                {{-- ✅ AJOUT : Bouton de rafraîchissement avec status --}}
                <div class="flex items-center space-x-2">
                    <button wire:click="forceReloadData"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50"
                            class="flex items-center px-4 py-2 text-sm text-blue-600 transition-colors bg-blue-100 rounded-lg hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-400 dark:hover:bg-blue-800/50">
                        <em class="mr-2 ni ni-reload"
                           wire:loading.class="animate-spin"
                           wire:target="forceReloadData,refreshResultats"></em>
                        <span wire:loading.remove wire:target="forceReloadData,refreshResultats">Actualiser</span>
                        <span wire:loading wire:target="forceReloadData,refreshResultats">Actualisation...</span>
                    </button>

                    {{-- ✅ Indicateur de dernière délibération --}}
                    @if(isset($deliberationStatus['date_deliberation']))
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Délibéré: {{ \Carbon\Carbon::parse($deliberationStatus['date_deliberation'])->format('H:i') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- ✅ AMÉLIORATION : Statistiques avec mise à jour en temps réel --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>{{ count($resultatsSession1) }}</strong> étudiant(s) affiché(s)
                    </span>

                    {{-- ✅ Stats avec mise à jour temps réel --}}
                    @if(isset($statistiquesSession1) && !empty($statistiquesSession1))
                        <div class="flex items-center space-x-2 text-sm" id="stats-display">
                            <span class="px-2 py-1 font-medium text-green-700 bg-green-100 rounded-full dark:bg-green-900/50 dark:text-green-300">
                                ✅ {{ $statistiquesSession1['admis'] ?? 0 }} admis
                            </span>
                            <span class="px-2 py-1 font-medium text-orange-700 bg-orange-100 rounded-full dark:bg-orange-900/50 dark:text-orange-300">
                                📚 {{ $statistiquesSession1['rattrapage'] ?? 0 }} rattrapage
                            </span>
                            @if(isset($statistiquesSession1['redoublant']) && $statistiquesSession1['redoublant'] > 0)
                                <span class="px-2 py-1 font-medium text-red-700 bg-red-100 rounded-full dark:bg-red-900/50 dark:text-red-300">
                                    🔄 {{ $statistiquesSession1['redoublant'] }} redoublant
                                </span>
                            @endif
                            @if(isset($statistiquesSession1['exclus']) && $statistiquesSession1['exclus'] > 0)
                                <span class="px-2 py-1 font-medium text-gray-700 bg-gray-100 rounded-full dark:bg-gray-900/50 dark:text-gray-300">
                                    ❌ {{ $statistiquesSession1['exclus'] }} exclus
                                </span>
                            @endif

                            {{-- ✅ Taux de réussite calculé en temps réel --}}
                            @php
                                $totalEtudiants = $statistiquesSession1['total_etudiants'] ?? count($resultatsSession1);
                                $admis = $statistiquesSession1['admis'] ?? 0;
                                $tauxReussite = $totalEtudiants > 0 ? round(($admis / $totalEtudiants) * 100, 1) : 0;
                            @endphp
                            <span class="px-2 py-1 font-medium text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/50 dark:text-blue-300">
                                📊 {{ $tauxReussite }}% réussite
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ✅ TABLEAU AMÉLIORÉ avec indicateurs temps réel --}}
            <div class="overflow-x-auto" id="resultats-table">
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
                                        <span class="text-xs font-normal">({{ $ueStructure['ue']->credits ?? 0 }} crédits)</span>
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
                        @foreach($resultatsSession1 as $indexEtudiant => $resultat)
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
                                            <div class="mt-1">
                                                @if($ueValidee)
                                                    <span class="px-1 text-xs text-green-500 bg-green-100 rounded dark:bg-green-900/50" title="UE validée">✓</span>
                                                @elseif($hasNoteZeroInUE)
                                                    <span class="px-1 text-xs text-red-500 bg-red-100 rounded dark:bg-red-900/50" title="UE éliminée">✗</span>
                                                @elseif($moyenneDisplay !== '-')
                                                    <span class="px-1 text-xs text-orange-500 bg-orange-100 rounded dark:bg-orange-900/50" title="UE non validée">✗</span>
                                                @endif
                                            </div>
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
                                <td class="px-4 py-3 text-center border-r whitespace-nowrap dark:border-gray-700">
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
                                            <div class="h-1.5 rounded-full {{ $pourcentage >= 100 ? 'bg-green-600' : ($pourcentage >= 67 ? 'bg-orange-500' : 'bg-red-600') }}"
                                                 style="width: {{ min($pourcentage, 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                {{-- ✅ VERSION SIMPLIFIÉE : Décision avec indicateur jury simple --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @php
                                        $decision = $resultat['decision'];
                                        $juryValidated = $resultat['jury_validated'] ?? false;
                                        $hasNoteEliminatoire = $resultat['has_note_eliminatoire'] ?? false;

                                        // Couleurs selon décision avec emphasis si délibéré
                                        $baseClass = match($decision) {
                                            'admis' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/50 dark:text-green-300 dark:border-green-700',
                                            'rattrapage' => 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/50 dark:text-orange-300 dark:border-orange-700',
                                            'redoublant' => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/50 dark:text-red-300 dark:border-red-700',
                                            'exclus' => 'bg-red-200 text-red-900 border-red-300 dark:bg-red-900/70 dark:text-red-200 dark:border-red-600',
                                            default => 'bg-gray-100 text-gray-900 border-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600'
                                        };

                                        // Ajouter des effets si délibéré
                                        $finalClass = $juryValidated
                                            ? $baseClass . ' ring-2 ring-blue-300 shadow-md border-2'
                                            : $baseClass . ' border';

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
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $finalClass }}">
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

                                            {{-- Message spécial pour promotion avec éliminatoire --}}
                                            @if($decision === 'admis' && $hasNoteEliminatoire)
                                                <div class="px-2 py-1 text-xs text-center rounded text-amber-700 bg-amber-100 dark:bg-amber-900/50 dark:text-amber-300">
                                                    <strong>Promotion exceptionnelle</strong>
                                                </div>
                                            @endif
                                        @else
                                            <div class="flex items-center px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-400">
                                                <em class="mr-1 ni ni-cpu"></em>
                                                <span>Automatique</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ✅ AJOUT : Légende et informations supplémentaires --}}
            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                <h5 class="mb-3 text-sm font-medium text-gray-900 dark:text-gray-100">Légende :</h5>
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

                {{-- ✅ Légende spéciale pour les changements de délibération --}}
                @if(isset($deliberationStatus['delibere']) && $deliberationStatus['delibere'])
                    <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                        <h6 class="mb-2 text-xs font-medium text-gray-800 dark:text-gray-200">Délibération :</h6>
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

{{-- ✅ À ajouter en bas de votre vue Blade --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 Livewire refresh listeners initialized');

    // ✅ Écouter les mises à jour après délibération
    Livewire.on('resultatsActualises', (data) => {
        console.log('📊 Résultats actualisés reçus:', data);

        // Mettre à jour le timestamp
        document.getElementById('last-update-time').textContent = new Date().toLocaleString('fr-FR');

        // Mettre à jour les statistiques si disponibles
        if (data.statistiques) {
            updateStatsDisplay(data.statistiques);
        }

        // Force un refresh complet du composant
        setTimeout(() => {
            console.log('🔄 Force Livewire component refresh...');
            @this.$refresh();
        }, 500);

        // Afficher une notification de succès
        showSuccessNotification(data);
    });

    // ✅ Fonction pour mettre à jour l'affichage des statistiques
    function updateStatsDisplay(stats) {
        const statsDisplay = document.getElementById('stats-display');
        if (statsDisplay && stats) {
            console.log('📈 Mise à jour des statistiques:', stats);

            // Animation de mise à jour
            statsDisplay.style.opacity = '0.5';
            setTimeout(() => {
                statsDisplay.style.opacity = '1';
            }, 300);

            // Mettre à jour le taux de réussite
            const tauxReussite = stats.total_etudiants > 0 ?
                Math.round((stats.admis / stats.total_etudiants) * 100) : 0;

            console.log(`📊 Nouveau taux de réussite: ${tauxReussite}%`);
        }
    }

    // ✅ Fonction pour afficher une notification de succès
    function showSuccessNotification(data) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 bg-green-500 text-white p-4 rounded-lg shadow-lg max-w-sm';

        let statsText = '';
        if (data.statistiques) {
            const stats = data.statistiques;
            statsText = `
                <div class="mt-2 text-sm">
                    ✅ ${stats.admis || 0} admis |
                    📚 ${stats.rattrapage || 0} rattrapage |
                    🔄 ${stats.redoublant || 0} redoublant |
                    ❌ ${stats.exclus || 0} exclus
                </div>
            `;
        }

        notification.innerHTML = `
            <div class="flex items-start">
                <div class="mt-1 mr-3">🎉</div>
                <div>
                    <div class="font-bold">Délibération appliquée !</div>
                    <div class="text-sm opacity-90">Les résultats ont été mis à jour avec succès.</div>
                    ${statsText}
                </div>
            </div>
        `;

        document.body.appendChild(notification);

        // Animation d'entrée
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notification.style.transition = 'transform 0.3s ease-out';
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Supprimer la notification après 6 secondes
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 6000);
    }

    // ✅ Auto-refresh intelligent
    function smartAutoRefresh() {
        const hasDeliberation = @json(isset($deliberationStatus['delibere']) && $deliberationStatus['delibere']);
        const resultatsCount = @json(count($resultatsSession1 ?? []));
        const shouldHaveData = @json($selectedNiveau && $selectedAnneeUniversitaire);

        console.log('🔍 Smart refresh check:', { hasDeliberation, resultatsCount, shouldHaveData });

        // Si délibération récente mais pas de données, refresh
        if (hasDeliberation && resultatsCount === 0 && shouldHaveData) {
            console.log('⚡ Auto-refresh déclenché après délibération');
            setTimeout(() => {
                @this.refreshResultats();
            }, 1500);
        }
    }

    // ✅ Hook pour surveiller les changements de données Livewire
    if (window.Livewire) {
        Livewire.hook('message.processed', (message, component) => {
            if (component.fingerprint.name.includes('resultats-finale')) {
                const hasResultats = component.data.resultatsSession1 ? component.data.resultatsSession1.length : 0;
                const hasStats = component.data.statistiquesSession1 ? Object.keys(component.data.statistiquesSession1).length : 0;

                console.log('📡 Livewire message processed:', {
                    component: component.fingerprint.name,
                    hasResultats: hasResultats,
                    hasStats: hasStats,
                    timestamp: new Date().toISOString()
                });

                // Mise à jour du timestamp automatique
                if (hasResultats > 0) {
                    const timestampEl = document.getElementById('last-update-time');
                    if (timestampEl) {
                        timestampEl.textContent = new Date().toLocaleString('fr-FR');
                    }
                }
            }
        });

        // ✅ Écouter les événements de force refresh
        Livewire.on('force-page-refresh', () => {
            console.log('🔄 Force page refresh demandé');
            setTimeout(() => {
                location.reload();
            }, 2000);
        });
    }

    // Vérification initiale
    setTimeout(smartAutoRefresh, 2000);
});

// ✅ Fonctions helper globales pour debug
window.debugDeliberation = function() {
    console.log('🔍 Debug délibération manuel...');
    @this.debugDeliberationData();
};

window.forceRefresh = function() {
    console.log('🔄 Force refresh manuel...');
    @this.forceReloadData();

    setTimeout(() => {
        console.log('🔄 Double refresh sécurité...');
        @this.$refresh();
    }, 1000);
};

// ✅ Surveillance des changements de décision en temps réel
window.watchDecisionChanges = function() {
    const rows = document.querySelectorAll('[data-etudiant-id]');
    console.log(`👀 Surveillance de ${rows.length} étudiants pour changements de décision`);

    rows.forEach(row => {
        const etudiantId = row.getAttribute('data-etudiant-id');
        const decisionCell = row.querySelector('td:last-child');

        if (decisionCell) {
            // Marquer les cellules avec changements
            const hasChange = decisionCell.querySelector('.animate-bounce');
            if (hasChange) {
                console.log(`✨ Changement détecté pour étudiant ${etudiantId}`);
                row.style.backgroundColor = 'rgba(59, 130, 246, 0.1)'; // Highlight subtle
            }
        }
    });
};

// Exécuter la surveillance après chargement
setTimeout(window.watchDecisionChanges, 1000);
</script>
@endpush
