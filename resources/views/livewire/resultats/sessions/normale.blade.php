{{-- ✅Session Normale --}}
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

                    {{-- ✅ NOUVEAUX BOUTONS D'EXPORT SIMPLES --}}
                    <div class="flex items-center space-x-3">
                        {{-- Bouton Export Excel --}}
                        <button wire:click="exporterExcel" 
                                wire:loading.attr="disabled"
                                class="flex items-center px-4 py-2 text-sm font-medium text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                            <em class="mr-2 ni ni-file-excel" wire:loading.class="animate-spin" wire:target="exporterExcel"></em>
                            <span wire:loading.remove wire:target="exporterExcel">Export tableau Excel</span>
                            <span wire:loading wire:target="exporterExcel">Ecours d'exporter le fichier excel...</span>
                        </button>
                    </div>

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

                {{-- Stats avec mise à jour temps réel --}}
                @if(isset($statistiquesSession1) && !empty($statistiquesSession1))
                    <div class="flex items-center flex-wrap gap-3 text-sm" id="stats-display">
                        {{-- Admis --}}
                        <span class="flex items-center space-x-2 px-3 py-2 font-medium text-green-700 bg-green-100 rounded-full border border-green-200 dark:bg-green-900/50 dark:text-green-300 dark:border-green-700 shadow-sm">
                            <em class="text-sm ni ni-users text-green-600 dark:text-green-400"></em>
                            <span>{{ $statistiquesSession1['admis'] ?? 0 }} admis</span>
                        </span>

                        {{-- Rattrapage --}}
                        <span class="flex items-center space-x-2 px-3 py-2 font-medium text-orange-700 bg-orange-100 rounded-full border border-orange-200 dark:bg-orange-900/50 dark:text-orange-300 dark:border-orange-700 shadow-sm">
                            <em class="text-sm ni ni-book text-orange-600 dark:text-orange-400"></em>
                            <span>{{ $statistiquesSession1['rattrapage'] ?? 0 }} rattrapage</span>
                        </span>

                        {{-- Redoublant --}}
                        @if(isset($statistiquesSession1['redoublant']) && $statistiquesSession1['redoublant'] > 0)
                            <span class="flex items-center space-x-2 px-3 py-2 font-medium text-red-700 bg-red-100 rounded-full border border-red-200 dark:bg-red-900/50 dark:text-red-300 dark:border-red-700 shadow-sm">
                                <em class="text-sm ni ni-reload text-red-600 dark:text-red-400"></em>
                                <span>{{ $statistiquesSession1['redoublant'] }} redoublant</span>
                            </span>
                        @endif

                        {{-- Exclus --}}
                        @if(isset($statistiquesSession1['exclus']) && $statistiquesSession1['exclus'] > 0)
                            <span class="flex items-center space-x-2 px-3 py-2 font-medium text-gray-700 bg-gray-100 rounded-full border border-gray-200 dark:bg-gray-900/50 dark:text-gray-300 dark:border-gray-700 shadow-sm">
                                <em class="text-sm ni ni-cross-circle text-gray-600 dark:text-gray-400"></em>
                                <span>{{ $statistiquesSession1['exclus'] }} exclus</span>
                            </span>
                        @endif

                        {{-- Taux de réussite calculé en temps réel --}}
                        @php
                            $totalEtudiants = $statistiquesSession1['total_etudiants'] ?? count($resultatsSession1);
                            $admis = $statistiquesSession1['admis'] ?? 0;
                            $tauxReussite = $totalEtudiants > 0 ? round(($admis / $totalEtudiants) * 100, 1) : 0;
                        @endphp
                        <span class="flex items-center space-x-2 px-3 py-2 font-medium text-blue-700 bg-blue-100 rounded-full border border-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:border-blue-700 shadow-sm">
                            <em class="text-sm ni ni-bar-chart text-blue-600 dark:text-blue-400"></em>
                            <span>{{ $tauxReussite }}% réussite</span>
                        </span>
                    </div>
                @endif
                </div>
            </div>

                {{-- Conteneur responsive pour le tableau --}}
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full py-2 align-middle">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/20">
                                    {{-- Première ligne d'en-têtes : UE --}}
                                    <tr>
                                        <th rowspan="2" class="sticky left-0 z-10 px-2 sm:px-4 py-2 sm:py-3 text-xs font-medium tracking-wider text-left text-gray-900 uppercase border-b border-r bg-gray-50 dark:bg-gray-900/20 dark:text-gray-100 dark:border-gray-700">
                                            <span class="hidden sm:inline">Ordre</span>
                                            <span class="sm:hidden">#</span>
                                        </th>
                                        <th rowspan="2" class="px-2 sm:px-4 py-2 sm:py-3 text-xs font-medium tracking-wider text-center text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700">
                                            Matricule
                                        </th>
                                        <th rowspan="2" class="px-2 sm:px-4 py-2 sm:py-3 text-xs font-medium tracking-wider text-left text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700">
                                            <span class="hidden md:inline">Nom</span>
                                            <span class="md:hidden">Nom/Prénom</span>
                                        </th>
                                        <th rowspan="2" class="hidden md:table-cell px-2 sm:px-4 py-2 sm:py-3 text-xs font-medium tracking-wider text-left text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700">
                                            Prénom
                                        </th>
                                            
                                        @foreach($uesStructure as $ueStructure)
                                            @php
                                                $ue = $ueStructure['ue'];
                                                $nombreECs = count($ueStructure['ecs']) + 1;
                                                
                                                // Extraire seulement la partie utile du nom
                                                $nomPropre = $ue->nom;
                                                if ($ue->abr && str_starts_with($nomPropre, $ue->abr . '.')) {
                                                    $nomPropre = trim(substr($nomPropre, strlen($ue->abr) + 1));
                                                }
                                            @endphp
                                            
                                            <th colspan="{{ $nombreECs }}" 
                                                class="px-1 sm:px-3 py-1 sm:py-2 text-xs font-bold tracking-wider text-center text-gray-900 uppercase bg-gray-100 border-b border-r dark:text-gray-100 dark:border-gray-700 dark:bg-gray-800">
                                                <div class="flex flex-col items-center">
                                                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ $ue->abr }}</span>
                                                    <span class="hidden sm:block text-xs font-normal text-gray-600 dark:text-gray-400">{{ Str::limit($nomPropre, 15) }}</span>
                                                    <span class="text-xs font-normal text-green-600 dark:text-green-400">({{ $ue->credits }})</span>
                                                </div>
                                            </th>
                                        @endforeach
                                        
                                        <th rowspan="2" class="px-2 sm:px-4 py-2 sm:py-3 text-xs font-medium tracking-wider text-center text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700">
                                            Crédits
                                        </th>
                                       <th rowspan="2" class="px-2 sm:px-4 py-2 sm:py-3 text-xs font-medium tracking-wider text-center text-gray-900 uppercase border-b border-l-2 border-r border-l-gray-400 dark:text-gray-100 dark:border-gray-700 dark:border-l-gray-500">
                                            <div class="flex flex-col items-center">
                                                <span class="hidden sm:inline">Moyenne</span>
                                                <span class="hidden sm:inline">Générale</span>
                                                <span class="sm:hidden">Moy.</span>
                                            </div>
                                        </th>
                                        <th rowspan="2" class="px-2 sm:px-4 py-2 sm:py-3 text-xs font-medium tracking-wider text-center text-gray-900 uppercase border-b dark:text-gray-100 dark:border-gray-700">
                                            Décision
                                        </th>
                                    </tr>
                                    <tr>
                                        @foreach($uesStructure as $ueStructure)
                                            {{-- ECs de cette UE --}}
                                            @foreach($ueStructure['ecs'] as $ecData)
                                                @php
                                                    $ec = $ecData['ec'];
                                                    
                                                    // Extraire seulement la partie utile du nom EC
                                                    $nomEcPropre = trim($ec->nom);
                                                    if ($ec->abr && str_starts_with($nomEcPropre, $ec->abr . '.')) {
                                                        $nomEcPropre = trim(substr($nomEcPropre, strlen($ec->abr) + 1));
                                                    }
                                                @endphp

                                                <th class="px-1 sm:px-2 py-1 sm:py-2 text-xs font-medium tracking-wider text-center text-gray-900 uppercase border-b border-r dark:text-gray-100 dark:border-gray-700"
                                                    title="{{ $ec->abr }}. {{ $nomEcPropre }} - {{ $ec->enseignant }}">
                                                    <div class="flex flex-col">
                                                        <span class="font-bold">{{ $ec->abr }}</span>
                                                        <span class="hidden lg:block text-xs font-normal">{{ Str::limit($nomEcPropre, 15) }}</span>
                                                        @if($ec->enseignant)
                                                            <span class="hidden xl:block text-xs italic text-gray-500">[{{ Str::limit(trim($ec->enseignant), 10) }}]</span>
                                                        @endif
                                                    </div>
                                                </th>
                                            @endforeach
                                            
                                            {{-- Moyenne UE --}}
                                            <th class="px-1 sm:px-2 py-1 sm:py-2 text-xs font-bold tracking-wider text-center text-gray-900 uppercase bg-gray-200 border-b border-r dark:text-gray-100 dark:border-gray-700 dark:bg-gray-700">
                                                <span class="hidden sm:inline">Moy.</span>
                                                <span class="sm:hidden">M</span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    @foreach($resultatsSession1 as $indexEtudiant => $resultat)
                                        <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50" data-etudiant-id="{{ $resultat['etudiant']->id }}">
                                            {{-- Ordre --}}
                                            <td class="sticky left-0 z-10 px-2 sm:px-4 py-2 sm:py-3 text-center border-r whitespace-nowrap bg-white dark:bg-gray-800 dark:border-gray-700">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $indexEtudiant + 1 }}
                                                </div>
                                            </td>
                                            {{-- Matricule --}}
                                            <td class="px-2 sm:px-4 py-2 sm:py-3 text-center border-r whitespace-nowrap dark:border-gray-700">
                                                <div class="font-mono text-xs sm:text-sm text-gray-900 dark:text-gray-100">
                                                    <span class="hidden sm:inline">{{ $resultat['etudiant']->matricule }}</span>
                                                    <span class="sm:hidden">{{ Str::limit($resultat['etudiant']->matricule, 8) }}</span>
                                                </div>
                                            </td>
                                            {{-- Nom (+ Prénom sur mobile) --}}
                                            <td class="px-2 sm:px-4 py-2 sm:py-3 border-r whitespace-nowrap dark:border-gray-700">
                                                <div class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    <div class="md:hidden">
                                                        {{ $resultat['etudiant']->nom }}<br>
                                                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ $resultat['etudiant']->prenom }}</span>
                                                    </div>
                                                    <div class="hidden md:block">
                                                        {{ $resultat['etudiant']->nom }}
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Prénom (masqué sur mobile) --}}
                                            <td class="hidden md:table-cell px-2 sm:px-4 py-2 sm:py-3 border-r whitespace-nowrap dark:border-gray-700">
                                                <div class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100">
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
                                                    <td class="px-1 sm:px-2 py-2 sm:py-3 text-center whitespace-nowrap border-r dark:border-gray-700
                                                        {{ $index === 0 && $ecIndex === 0 ? 'border-l-2 border-l-gray-400 dark:border-l-gray-500' : '' }}">
                                                        @if(isset($resultat['notes'][$ecData['ec']->id]))
                                                            @php
                                                                $note = $resultat['notes'][$ecData['ec']->id]->note;
                                                                $notesUE[] = $note;
                                                                if ($note == 0) $hasNoteZeroInUE = true;
                                                            @endphp
                                                            <span class="text-xs sm:text-sm {{ $note >= 10 ? 'text-green-600 dark:text-green-400 font-semibold' : ($note == 0 ? 'text-red-600 dark:text-red-400 font-bold' : 'text-orange-600 dark:text-orange-400') }}">
                                                                {{ number_format($note, 2) }}
                                                                @if($note == 0)
                                                                    <span class="ml-1" title="Note éliminatoire">⚠️</span>
                                                                @endif
                                                            </span>
                                                        @else
                                                            <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                {{-- ✅ CORRECTION : Moyenne UE TOUJOURS VRAIE --}}
                                                <td class="px-1 sm:px-2 py-2 sm:py-3 text-center border-r whitespace-nowrap dark:border-gray-700">
                                                    @php
                                                        // ✅ CALCUL CORRECT : Vraie moyenne et validation des crédits
                                                        if (!empty($notesUE)) {
                                                            $moyenneUE = array_sum($notesUE) / count($notesUE);
                                                            $moyenneDisplay = number_format($moyenneUE, 2);
                                                            
                                                            // ✅ NOUVEAU : Calculer les crédits validés pour cette UE
                                                            $creditsECIndividuels = 0; // ECs validées individuellement
                                                            $creditsECTotaux = 0;
                                                            $detailsCreditsEC = [];
                                                            
                                                            foreach($ueStructure['ecs'] as $ecData) {
                                                                if(isset($resultat['notes'][$ecData['ec']->id])) {
                                                                    $noteEC = $resultat['notes'][$ecData['ec']->id]->note;
                                                                    $creditsEC = $ecData['ec']->credits ?? 0;
                                                                    
                                                                    // ✅ EC validée individuellement : note >= 10 ET pas éliminatoire
                                                                    $ecValideeIndividuellement = ($noteEC >= 10) && ($noteEC != 0);
                                                                    
                                                                    if ($ecValideeIndividuellement) {
                                                                        $creditsECIndividuels += $creditsEC;
                                                                    }
                                                                    $creditsECTotaux += $creditsEC;
                                                                    
                                                                    $detailsCreditsEC[] = [
                                                                        'nom' => $ecData['ec']->abr,
                                                                        'note' => $noteEC,
                                                                        'credits' => $creditsEC,
                                                                        'validee' => $ecValideeIndividuellement
                                                                    ];
                                                                }
                                                            }
                                                            
                                                            // ✅ RÈGLE ACADÉMIQUE : UE validée = moyenne >= 10 ET pas de note éliminatoire
                                                            $ueValidee = ($moyenneUE >= 10) && (!$hasNoteZeroInUE);
                                                            
                                                            // ✅ CRÉDITS FINAUX : Si UE validée → TOUS les crédits de l'UE (compensation)
                                                            //                     Sinon → Seulement les crédits des ECs validées individuellement
                                                            $creditsUEObtenus = $ueValidee ? $ueStructure['ue']->credits : 0;
                                                            
                                                            // ✅ COULEURS : Basées sur validation de l'UE
                                                            if ($hasNoteZeroInUE) {
                                                                $moyenneClass = 'text-red-600 dark:text-red-400 font-bold';
                                                                $tooltip = 'UE non validée (note éliminatoire) - Crédits: 0/' . $ueStructure['ue']->credits;
                                                            } elseif ($ueValidee) {
                                                                $moyenneClass = 'text-green-600 dark:text-green-400 font-semibold';
                                                                $tooltip = 'UE validée par compensation - Crédits: ' . $creditsUEObtenus . '/' . $ueStructure['ue']->credits;
                                                            } else {
                                                                $moyenneClass = 'text-orange-600 dark:text-orange-400';
                                                                $tooltip = 'UE non validée (moyenne < 10) - Crédits ECs validées: ' . $creditsECIndividuels . '/' . $creditsECTotaux;
                                                            }
                                                        } else {
                                                            $moyenneDisplay = '-';
                                                            $ueValidee = false;
                                                            $moyenneClass = 'text-gray-500 dark:text-gray-400';
                                                            $creditsECIndividuels = 0;
                                                            $creditsECTotaux = 0;
                                                            $creditsUEObtenus = 0;
                                                            $detailsCreditsEC = [];
                                                            $tooltip = 'Aucune note';
                                                        }
                                                    @endphp
                                                    
                                                    <div class="relative flex flex-col items-center justify-center group" title="{{ $tooltip }}">
                                                        {{-- Moyenne UE --}}
                                                        <span class="text-xs sm:text-sm font-bold {{ $moyenneClass }}">
                                                            {{ $moyenneDisplay }}
                                                        </span>
                                                        
                                                        {{-- ✅ AFFICHAGE CRÉDITS CORRIGÉ --}}
                                                        @if(!empty($detailsCreditsEC))
                                                            <div class="mt-1 text-xs">
                                                                {{-- Affichage principal : Crédits UE obtenus --}}
                                                                @if($ueValidee)
                                                                    {{-- UE validée → Afficher crédits complets de l'UE --}}
                                                                    <div class="font-semibold text-green-600 dark:text-green-400">
                                                                        {{ $creditsUEObtenus }}/{{ $ueStructure['ue']->credits }}cr ✓
                                                                    </div>
                                                                @elseif($hasNoteZeroInUE)
                                                                    {{-- Note éliminatoire → Aucun crédit --}}
                                                                    <div class="font-semibold text-red-600 dark:text-red-400">
                                                                        0/{{ $ueStructure['ue']->credits }}cr ✗
                                                                    </div>
                                                                @else
                                                                    {{-- UE non validée → Afficher crédits ECs individuels --}}
                                                                    <div class="font-semibold text-orange-600 dark:text-orange-400">
                                                                        <span title="Crédits ECs validées individuellement">
                                                                            {{ $creditsECIndividuels }}/{{ $creditsECTotaux }}cr*
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            
                                                            {{-- ✅ Tooltip détaillé au survol --}}
                                                            <div class="hidden group-hover:block absolute z-20 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl p-3 mt-1 left-1/2 transform -translate-x-1/2"
                                                                style="min-width: 200px; top: 100%;">
                                                                <div class="font-bold text-gray-900 dark:text-gray-100 mb-2 border-b pb-1">
                                                                    {{ $ueStructure['ue']->abr }} - {{ $ueStructure['ue']->nom }}
                                                                </div>
                                                                
                                                                {{-- Détail par EC --}}
                                                                <div class="space-y-1 mb-2">
                                                                    @foreach($detailsCreditsEC as $detailEC)
                                                                        <div class="flex justify-between items-center text-xs">
                                                                            <span class="text-gray-700 dark:text-gray-300">
                                                                                {{ $detailEC['nom'] }} ({{ number_format($detailEC['note'], 2) }})
                                                                            </span>
                                                                            <span class="font-medium {{ $detailEC['validee'] ? 'text-green-600' : 'text-red-600' }}">
                                                                                {{ $detailEC['credits'] }}cr {{ $detailEC['validee'] ? '✓' : '✗' }}
                                                                            </span>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                                
                                                                {{-- Synthèse --}}
                                                                <div class="border-t pt-2 space-y-1 text-xs">
                                                                    <div class="flex justify-between font-medium text-gray-700 dark:text-gray-300">
                                                                        <span>Moyenne UE :</span>
                                                                        <span class="{{ $moyenneClass }}">{{ $moyenneDisplay }}/20</span>
                                                                    </div>
                                                                    <div class="flex justify-between font-medium">
                                                                        <span class="text-gray-700 dark:text-gray-300">ECs validées :</span>
                                                                        <span>{{ $creditsECIndividuels }}/{{ $creditsECTotaux }}cr</span>
                                                                    </div>
                                                                    <div class="flex justify-between font-bold text-sm border-t pt-1 mt-1">
                                                                        <span class="text-gray-900 dark:text-gray-100">Crédits UE :</span>
                                                                        @if($ueValidee)
                                                                            <span class="text-green-600 dark:text-green-400">
                                                                                {{ $creditsUEObtenus }}/{{ $ueStructure['ue']->credits }}cr ✓
                                                                            </span>
                                                                        @elseif($hasNoteZeroInUE)
                                                                            <span class="text-red-600 dark:text-red-400">
                                                                                0/{{ $ueStructure['ue']->credits }}cr ✗
                                                                            </span>
                                                                        @else
                                                                            <span class="text-orange-600 dark:text-orange-400">
                                                                                0/{{ $ueStructure['ue']->credits }}cr
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    
                                                                    {{-- Explication de la compensation --}}
                                                                    @if($ueValidee && $creditsECIndividuels < $creditsECTotaux)
                                                                        <div class="text-xs text-green-700 dark:text-green-300 italic mt-2 pt-2 border-t">
                                                                            ✓ Compensation : UE validée malgré EC(s) non validée(s)
                                                                        </div>
                                                                    @elseif(!$ueValidee && !$hasNoteZeroInUE && $creditsECIndividuels > 0)
                                                                        <div class="text-xs text-orange-700 dark:text-orange-300 italic mt-2 pt-2 border-t">
                                                                            * Crédits ECs acquis mais UE non validée (moyenne < 10)
                                                                        </div>
                                                                    @elseif($hasNoteZeroInUE)
                                                                        <div class="text-xs text-red-700 dark:text-red-300 italic mt-2 pt-2 border-t">
                                                                            ✗ Note éliminatoire : aucun crédit attribué
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif

                                                        {{-- ✅ Indicateur visuel simple --}}
                                                        @if($hasNoteZeroInUE && !empty($notesUE))
                                                            <span class="text-xs text-red-500 mt-1" title="Note éliminatoire">⚠️</span>
                                                        @elseif($ueValidee)
                                                            <span class="text-xs text-green-500 mt-1" title="UE validée">✓</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endforeach
                                            {{-- Crédits --}}
                                            <td class="px-2 sm:px-4 py-2 sm:py-4 text-center border-r whitespace-nowrap dark:border-gray-700">
                                                <div class="flex flex-col items-center">
                                                    <span class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $resultat['credits_valides'] }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        /{{ $resultat['total_credits'] ?? 60 }}
                                                    </span>
                                                    <div class="w-full bg-gray-200 rounded-full h-1 sm:h-1.5 mt-1 dark:bg-gray-700">
                                                        @php
                                                            $totalCredits = $resultat['total_credits'] ?? 60;
                                                            $pourcentage = $totalCredits > 0 ? ($resultat['credits_valides'] / $totalCredits) * 100 : 0;
                                                        @endphp
                                                        <div class="h-1 sm:h-1.5 rounded-full {{ $pourcentage >= 100 ? 'bg-green-600' : ($pourcentage >= 67 ? 'bg-orange-500' : 'bg-red-600') }}"
                                                            style="width: {{ min($pourcentage, 100) }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            {{-- ✅ CORRECTION : Moyenne générale TOUJOURS VRAIE --}}
                                            <td class="px-2 sm:px-4 py-2 sm:py-3 text-center border-l-2 border-r whitespace-nowrap border-l-gray-400 dark:border-gray-700 dark:border-l-gray-500">
                                                <div class="flex flex-col items-center">
                                                    {{-- ✅ Afficher la vraie moyenne générale --}}
                                                    <span class="text-xs sm:text-sm font-bold {{ $resultat['moyenne_generale'] >= 10 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                                        {{ number_format($resultat['moyenne_generale'], 2) }}
                                                    </span>
                                                    {{-- ✅ Indicateurs visuels améliorés --}}
                                                    @if(isset($resultat['has_note_eliminatoire']) && $resultat['has_note_eliminatoire'])
                                                        <div class="flex items-center mt-1">
                                                            <span class="text-xs text-red-500 mr-1" title="Présence de note(s) éliminatoire(s)">⚠️</span>
                                                            <span class="text-xs text-red-600 dark:text-red-400 font-medium">ÉLIM</span>
                                                        </div>
                                                    @elseif($resultat['moyenne_generale'] >= 10)
                                                        <span class="mt-1 text-xs text-green-500" title="Moyenne suffisante">✅</span>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- Décision avec indicateur jury --}}
                                            <td class="px-2 sm:px-4 py-2 sm:py-3 text-center whitespace-nowrap">
                                                @php
                                                    $decision = $resultat['decision'];
                                                    $juryValidated = $resultat['jury_validated'] ?? false;
                                                    $hasNoteEliminatoire = $resultat['has_note_eliminatoire'] ?? false;

                                                    // Couleurs selon décision
                                                    $baseClass = match($decision) {
                                                        'admis' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                                        'rattrapage' => 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/50 dark:text-orange-300 dark:border-orange-700',
                                                        'redoublant' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                                                        'exclus' => 'bg-red-200 text-red-900 dark:bg-red-900/70 dark:text-red-200',
                                                        default => 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-100'
                                                    };

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

                                                    // Version mobile plus courte
                                                    $decisionMobile = match($decision) {
                                                        'admis' => 'Admis',
                                                        'rattrapage' => 'Rattr.',
                                                        'redoublant' => 'Redoub.',
                                                        'exclus' => 'Exclus',
                                                        default => 'N/D'
                                                    };
                                                @endphp

                                                <div class="flex flex-col items-center space-y-1 sm:space-y-2">
                                                    {{-- Badge principal --}}
                                                    <span class="px-2 sm:px-3 py-1 text-xs rounded-full {{ $finalClass }}">
                                                        <span class="hidden sm:inline">{{ $decisionLibelle }}</span>
                                                        <span class="sm:hidden">{{ $decisionMobile }}</span>
                                                        @if($hasNoteEliminatoire)
                                                            <span class="ml-1" title="Note éliminatoire">⚠️</span>
                                                        @endif
                                                    </span>

                                                    {{-- Indicateur source de la décision --}}
                                                    @if($juryValidated)
                                                        <div class="hidden sm:flex items-center px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/50 dark:text-blue-300">
                                                            <em class="mr-1 ni ni-shield-check"></em>
                                                            <span class="font-semibold">Délibération</span>
                                                        </div>
                                                        <div class="sm:hidden w-2 h-2 bg-blue-500 rounded-full" title="Délibération"></div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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

{{-- ✅ NOTIFICATION D'EXPORT (à ajouter quelque part dans la vue) --}}
@if (session()->has('export_success'))
    <div class="p-4 mb-4 border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700 rounded-xl">
        <div class="flex items-center">
            <em class="mr-3 text-green-600 ni ni-check-circle dark:text-green-400"></em>
            <div>
                <h4 class="text-sm font-medium text-green-900 dark:text-green-100">Export réussi</h4>
                <p class="text-sm text-green-700 dark:text-green-300">{{ session('export_success') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session()->has('export_error'))
    <div class="p-4 mb-4 border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-700 rounded-xl">
        <div class="flex items-center">
            <em class="mr-3 text-red-600 ni ni-times-circle dark:text-red-400"></em>
            <div>
                <h4 class="text-sm font-medium text-red-900 dark:text-red-100">Erreur d'export</h4>
                <p class="text-sm text-red-700 dark:text-red-300">{{ session('export_error') }}</p>
            </div>
        </div>
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
