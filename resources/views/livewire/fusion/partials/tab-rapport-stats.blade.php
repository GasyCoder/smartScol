{{-- Onglet Rapport et Statistiques - Version Simplifiée --}}
{{-- Affichage des erreurs de cohérence --}}
@if(isset($rapportCoherence['erreurs_coherence']) && !empty($rapportCoherence['erreurs_coherence']))
    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg dark:bg-red-900/20 dark:border-red-700">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">
                    Données incohérentes détectées
                </h3>
                
                <div class="text-sm text-red-700 dark:text-red-300 space-y-1 mb-3">
                    @foreach($rapportCoherence['erreurs_coherence'] as $erreur)
                        <div class="flex items-start">
                            <span class="mr-2">•</span>
                            <span>{{ $erreur }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center gap-3">
                    <button wire:click="nettoyerDonneesIncoherentes"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="nettoyerDonneesIncoherentes" 
                             class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <svg wire:loading wire:target="nettoyerDonneesIncoherentes"
                             class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="nettoyerDonneesIncoherentes">
                            Nettoyer automatiquement
                        </span>
                        <span wire:loading wire:target="nettoyerDonneesIncoherentes">
                            Nettoyage en cours...
                        </span>
                    </button>

                    <span class="text-xs text-red-600 dark:text-red-400">
                        Cette action supprimera les données invalides
                    </span>
                </div>
            </div>
        </div>
    </div>
@endif

<div id="content-rapport-stats" 
     class="tab-content" 
     style="{{ $activeTab !== 'rapport-stats' ? 'display: none;' : '' }}">

    {{-- État initial - Aucune vérification effectuée --}}
    @if(empty($rapportCoherence) && $statut === 'initial')
        <div class="flex flex-col items-center justify-center py-8 text-center bg-gray-50 rounded-lg dark:bg-gray-800">
            <div class="w-12 h-12 mb-3 text-gray-400 bg-gray-200 rounded-full flex items-center justify-center dark:bg-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            
            <h3 class="mb-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                Aucun rapport disponible
            </h3>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400 max-w-md">
                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                    Lancez la vérification pour analyser les données de rattrapage.
                @else
                    Lancez la vérification pour analyser les manchettes et copies.
                @endif
            </p>
            
            @if($showVerificationButton)
                <button wire:click="confirmVerification"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Générer le rapport
                    <span wire:loading wire:target="confirmVerification" class="ml-2 animate-spin">⟳</span>
                </button>
            @endif
        </div>

    {{-- Rapport de cohérence disponible --}}
    @elseif(!empty($rapportCoherence) && isset($rapportCoherence['stats']))

        @php
            // Infos examen de base
            $niveauNom = $examen->niveau->nom ?? 'N/A';
            $parcoursNom = $examen->parcours->nom ?? 'N/A';
            
            // Stats matières depuis le rapport
            $stats = $rapportCoherence['stats'] ?? ['total' => 0, 'complets' => 0, 'incomplets' => 0];
            $totalMatieres = $stats['total'] ?? $examen->ecs()->count();
            $matieresCompletes = $stats['complets'] ?? 0;
            $matieresIncompletes = $stats['incomplets'] ?? 0;
            
            // Récupérer session ID
            $session = isset($sessionActive) && $sessionActive ? $sessionActive : \App\Models\SessionExam::current()->first();
            $sessionId = $session ? $session->id : null;
            
            // Calcul des manchettes
            $totalManchettesPresentes = 0;
            $totalManchettesAbsentes = 0;
            $totalCopies = 0;
            
            if (!empty($rapportCoherence['data'])) {
                foreach ($rapportCoherence['data'] as $item) {
                    $totalManchettesPresentes += ($item['presents'] ?? 0);
                    $totalManchettesAbsentes += ($item['absents'] ?? 0);
                    $totalCopies += ($item['copies'] ?? 0);
                }
            } else {
                if ($sessionId) {
                    $totalManchettesPresentes = \App\Models\Manchette::where('examen_id', $examen->id)
                        ->where('session_exam_id', $sessionId)
                        ->whereNotNull('code_anonymat_id')
                        ->count();
                    $totalCopies = \App\Models\Copie::where('examen_id', $examen->id)->count();
                }
            }
            
            $totalInscrits = $examen->etudiantsConcernes->count();
            $totalManchettes = $totalManchettesPresentes + $totalManchettesAbsentes;
            
            // Récupérer les stats de présence
            if (!$sessionId) {
                $nbPresents = 0;
                $nbAbsents = 0;
            } else {
                $statsPresence = \App\Models\PresenceExamen::getStatistiquesExamen($examen->id, $sessionId);
                $nbPresents = $statsPresence['presents'];
                $nbAbsents = $totalInscrits - $nbPresents; // Force 519 - 495 = 24
            }
            
            
            // Attendus
            $manchettesAttendues = max(1, $totalInscrits * $totalMatieres);
            
            // Pourcentages
            $pctManchettes = round(($totalManchettes / $manchettesAttendues) * 100, 1);
            $pctManchettes = min(100, max(0, $pctManchettes));
            
            $pctCopies = $totalManchettes > 0 ? round(($totalCopies / $totalManchettes) * 100, 1) : 0;
            $pctCopies = min(100, max(0, $pctCopies));
            
            // Taux de présence
            $pctPresence = $totalInscrits > 0 ? round(($nbPresents / $totalInscrits) * 100, 1) : 0;
            
            // Anomalie copies
            $anomalieCopies = $totalCopies > $totalManchettes;
            $ecartCopies = $anomalieCopies ? ($totalCopies - $totalManchettes) : 0;
            
            // Complétion globale
            $completionRate = $totalMatieres > 0 ? round(($matieresCompletes / $totalMatieres) * 100) : 0;
        @endphp

        <div class="space-y-4">
            {{-- En-tête avec contexte --}}
            <div class="p-4 border rounded-lg {{ $completionRate === 100 ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' : ($completionRate > 0 ? 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800' : 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800') }}">
                
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold {{ $completionRate === 100 ? 'text-green-800 dark:text-green-200' : ($completionRate > 0 ? 'text-yellow-800 dark:text-yellow-200' : 'text-red-800 dark:text-red-200') }}">
                        Rapport de cohérence
                        @if(isset($session) && $session && $session->type === 'Rattrapage')
                            <span class="ml-2 px-2 py-0.5 text-xs font-medium text-orange-800 bg-orange-200 rounded dark:bg-orange-800 dark:text-orange-200">
                                RATTRAPAGE
                            </span>
                        @endif
                    </h3>
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-medium">{{ $niveauNom }}</span>
                        @if($parcoursNom && $parcoursNom !== 'N/A')
                            <span class="mx-1">•</span>
                            <span>{{ $parcoursNom }}</span>
                        @endif
                        <span class="mx-1">•</span>
                        <span>{{ $totalInscrits }} inscrit(s)</span>
                        <span class="mx-1">•</span>
                        <span>{{ $totalMatieres }} matière(s)</span>
                    </div>
                </div>
                
                <div class="text-right">
                    @if(isset($rapportCoherence['last_check']))
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                            {{ $rapportCoherence['last_check'] }}
                        </div>
                    @endif
                    <div class="text-2xl font-bold {{ $completionRate === 100 ? 'text-green-600 dark:text-green-400' : ($completionRate > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                        {{ $completionRate }}%
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $matieresCompletes }}/{{ $totalMatieres }} matières
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Présents</div>
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $nbPresents }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $totalManchettesPresentes }} manchettes</div>
                </div>

                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Absents</div>
                    <div class="text-3xl font-bold {{ $nbAbsents > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">
                        {{ $nbAbsents }}
                    </div>
                </div>

                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Taux de présence</div>
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $pctPresence }}%</div>
                </div>

                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Copies</div>
                    <div class="text-3xl font-bold {{ $pctCopies >= 100 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                        {{ $totalCopies }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">sur {{ $totalManchettes }} ({{ $pctCopies }}%)</div>
                </div>
            </div>

            <div class="mb-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="text-sm text-blue-800 dark:text-blue-300">
                    <strong>📊 Résumé :</strong> 
                    {{ $totalInscrits }} étudiant(s) × {{ $totalMatieres }} matière(s) = 
                    <strong>{{ $manchettesAttendues }}</strong> manchettes attendues • 
                    <strong class="{{ $pctManchettes >= 100 ? 'text-green-600' : 'text-orange-600' }}">
                        {{ $totalManchettes }} saisies ({{ $pctManchettes }}%)
                    </strong>
                </div>
            </div>



                {{-- Métriques matières --}}
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div class="text-center p-2 bg-white dark:bg-gray-800 rounded">
                        <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $matieresCompletes }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Complètes</div>
                    </div>
                    <div class="text-center p-2 bg-white dark:bg-gray-800 rounded">
                        <div class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ $matieresIncompletes }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Incomplètes</div>
                    </div>
                    <div class="text-center p-2 bg-white dark:bg-gray-800 rounded">
                        <div class="text-lg font-bold text-gray-600 dark:text-gray-400">{{ $totalMatieres }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Total EC</div>
                    </div>
                </div>

                {{-- Barre progression globale --}}
                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                    <div class="h-2 rounded-full transition-all {{ $completionRate === 100 ? 'bg-green-500' : ($completionRate > 0 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                         style="width: {{ $completionRate }}%"></div>
                </div>
            </div>

            {{-- Actions rapides --}}
            @if($statut !== 'initial')
                <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        @if($statut === 'verification' && $matieresCompletes > 0)
                            Prêt pour la fusion
                        @elseif($statut === 'verification')
                            Aucune matière complète
                        @elseif($statut === 'fusion')
                            Fusion étape {{ $etapeFusion }}/3
                        @elseif($statut === 'valide')
                            Prêt pour publication
                        @elseif($statut === 'publie')
                            Résultats publiés
                        @elseif($statut === 'annule')
                            Résultats annulés
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if($showVerificationButton)
                            <button wire:click="confirmVerification"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors disabled:opacity-50">
                                Re-vérifier
                            </button>
                        @endif

                        @if($statut === 'publie')
                            <a href="{{ route('resultats.finale') }}" 
                               class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-green-700 bg-green-100 rounded-md hover:bg-green-200 transition-colors">
                                Voir résultats
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ✅ TABLEAU DÉTAILLÉ CONSERVÉ --}}
            @if(isset($rapportCoherence['data']) && !empty($rapportCoherence['data']))
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Détail par matière ({{ count($rapportCoherence['data']) }})
                        </h4>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                        Matière
                                    </th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                        Présents
                                    </th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                        Manchettes
                                    </th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                        Copies
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                        Statut
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @php
                                    $completes = collect($rapportCoherence['data'])->where('complet', true);
                                    $partielles = collect($rapportCoherence['data'])->where('complet', false)->filter(function($item) {
                                        return ($item['manchettes'] ?? 0) > 0 || ($item['copies'] ?? 0) > 0;
                                    });
                                    $vides = collect($rapportCoherence['data'])->where('complet', false)->filter(function($item) {
                                        return ($item['manchettes'] ?? 0) === 0 && ($item['copies'] ?? 0) === 0;
                                    });
                                    $orderedData = $completes->concat($partielles)->concat($vides);
                                @endphp

                                @forelse($orderedData as $rapport)
                                    @php
                                        $presents = $rapport['presents'] ?? 0;
                                        $absents = $rapport['absents'] ?? 0;
                                        $manchettes = $rapport['manchettes'] ?? 0;
                                        $copies = $rapport['copies'] ?? 0;
                                        $pctSync = $rapport['pct_sync'] ?? 0;
                                        $estComplet = $rapport['complet'] ?? false;
                                    @endphp
                                    
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $estComplet ? 'bg-green-50 dark:bg-green-900/10' : '' }}">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $rapport['ec_nom'] ?? 'N/A' }}
                                        </td>
                                        
                                        <td class="px-3 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $presents }}
                                        </td>
                                    
                                        <td class="px-3 py-3 text-center text-sm font-semibold {{ $manchettes >= ($presents + $absents) ? 'text-green-600' : 'text-orange-600' }}">
                                            {{ $manchettes }}
                                        </td>
                                        
                                        <td class="px-3 py-3 text-center text-sm font-semibold {{ $copies >= ($presents + $absents) ? 'text-green-600' : 'text-orange-600' }}">
                                            {{ $copies }}
                                        </td>
                                        
                                        <td class="px-4 py-3 text-center">
                                            @if($estComplet)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-green-800 bg-green-100 rounded dark:bg-green-900 dark:text-green-300">
                                                    ✓ Complet
                                                </span>
                                            @elseif($manchettes > 0 || $copies > 0)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded dark:bg-yellow-900 dark:text-yellow-300">
                                                    ⚠ Partiel
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded dark:bg-gray-700 dark:text-gray-400">
                                                    Vide
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Aucune donnée disponible
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Légende --}}
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            <strong>Complet :</strong> Manchettes ET Copies ≥ (Présents + Absents)
                        </div>
                    </div>
                </div>
            @endif
        </div>

    {{-- État de chargement --}}
    @else
        <div class="flex flex-col items-center justify-center py-6 text-center">
            <div class="w-8 h-8 mb-3 text-gray-400 animate-spin">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
            
            <h3 class="mb-1 text-base font-medium text-gray-900 dark:text-gray-100">
                Chargement...
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Traitement des données en cours
            </p>
        </div>
    @endif
</div>