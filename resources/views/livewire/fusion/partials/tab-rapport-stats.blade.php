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
        // Infos examen
        $niveauNom = $examen->niveau->nom ?? 'N/A';
        $parcoursNom = $examen->parcours->nom ?? 'N/A';
        
        // ✅ UTILISER LA MÉTHODE HELPER DU MODÈLE
        $statsPresence = \App\Models\PresenceExamen::getStatistiquesExamen(
            $examen->id, 
            $sessionActive->id
        );
        
        $nbPresents = $statsPresence['presents'];
        $nbAbsents = $statsPresence['absents'];
        $totalInscrits = $statsPresence['total_attendu'];
        
        // Données saisies (somme sur toutes les matières)
        $totalManchettesPresentes = 0;
        $totalManchettesAbsentes = 0;
        $totalCopies = 0;
        
        foreach($rapportCoherence['data'] as $item) {
            $totalManchettesPresentes += ($item['manchettes_presentes'] ?? 0);
            $totalManchettesAbsentes += ($item['manchettes_absentes'] ?? 0);
            $totalCopies += ($item['copies_count'] ?? 0);
        }
        
        // Stats matières
        $stats = $rapportCoherence['stats'];
        $totalMatieres = $stats['total'] ?? 0;
        $matieresCompletes = $stats['complets'] ?? 0;
        $matieresIncompletes = $stats['incomplets'] ?? 0;
        
        // Calcul attendus
        $manchettesAttendues = $nbPresents * $totalMatieres;
        $copiesAttendues = $nbPresents * $totalMatieres;
        $absentsSyncAttendus = $nbAbsents * $totalMatieres;
        
        // Pourcentages
        $pctManchettes = $manchettesAttendues > 0 
            ? round(($totalManchettesPresentes / $manchettesAttendues) * 100, 1) 
            : 0;
        $pctCopies = $copiesAttendues > 0 
            ? round(($totalCopies / $copiesAttendues) * 100, 1) 
            : 0;
        $pctAbsents = $absentsSyncAttendus > 0 
            ? round(($totalManchettesAbsentes / $absentsSyncAttendus) * 100, 1) 
            : 100;
        
        // Pourcentage de complétion global (matières complètes)
        $completionRate = $totalMatieres > 0 
            ? round(($matieresCompletes / $totalMatieres) * 100) 
            : 0;
    @endphp

        <div class="space-y-4">
            
            {{-- En-tête avec contexte --}}
            <div class="p-4 border rounded-lg {{ $completionRate === 100 ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' : ($completionRate > 0 ? 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800' : 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800') }}">
                
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold {{ $completionRate === 100 ? 'text-green-800 dark:text-green-200' : ($completionRate > 0 ? 'text-yellow-800 dark:text-yellow-200' : 'text-red-800 dark:text-red-200') }}">
                            Rapport de cohérence
                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
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

                {{-- Statistiques présents/absents --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    
                    {{-- PRÉSENTS --}}
                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Présents</span>
                            <span class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $nbPresents }}</span>
                        </div>
                        
                        <div class="space-y-2">
                            {{-- Manchettes --}}
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">Manchettes</span>
                                    <span class="font-semibold {{ $pctManchettes >= 100 ? 'text-green-600' : 'text-orange-600' }}">
                                        {{ $totalManchettesPresentes }}/{{ $manchettesAttendues }} ({{ $pctManchettes }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                    <div class="h-1.5 rounded-full {{ $pctManchettes >= 100 ? 'bg-green-500' : 'bg-orange-500' }}" 
                                         style="width: {{ min($pctManchettes, 100) }}%"></div>
                                </div>
                            </div>
                            
                            {{-- Copies --}}
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">Copies</span>
                                    <span class="font-semibold {{ $pctCopies >= 100 ? 'text-green-600' : 'text-orange-600' }}">
                                        {{ $totalCopies }}/{{ $copiesAttendues }} ({{ $pctCopies }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                    <div class="h-1.5 rounded-full {{ $pctCopies >= 100 ? 'bg-green-500' : 'bg-orange-500' }}" 
                                         style="width: {{ min($pctCopies, 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ABSENTS --}}
                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Absents</span>
                            <span class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $nbAbsents }}</span>
                        </div>
                        
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600 dark:text-gray-400">Synchronisés</span>
                                <span class="font-semibold {{ $pctAbsents >= 100 ? 'text-green-600' : 'text-orange-600' }}">
                                    {{ $totalManchettesAbsentes }}/{{ $absentsSyncAttendus }} ({{ $pctAbsents }}%)
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                <div class="h-1.5 rounded-full {{ $pctAbsents >= 100 ? 'bg-green-500' : 'bg-orange-500' }}" 
                                     style="width: {{ min($pctAbsents, 100) }}%"></div>
                            </div>
                            @if($nbAbsents === 0)
                                <div class="text-xs text-green-600 dark:text-green-400 mt-1">Aucun absent</div>
                            @endif
                        </div>
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

            {{-- Tableau détaillé par matière --}}
            @if(isset($rapportCoherence['data']) && !empty($rapportCoherence['data']))
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Détail par matière ({{ count($rapportCoherence['data']) }})
                        </h4>
                        
                        {{-- Debug en mode développement --}}
                        @if(config('app.debug'))
                            <div class="mt-2 text-xs text-gray-500">
                                @php
                                    $debugComplets = collect($rapportCoherence['data'])->where('complet', true)->count();
                                    $debugTotal = count($rapportCoherence['data']);
                                @endphp
                                <span class="bg-yellow-100 px-2 py-1 rounded">
                                    DEBUG: {{ $debugComplets }}/{{ $debugTotal }} matières complètes détectées
                                </span>
                            </div>
                        @endif
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
                                        Absents
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
                                        
                                        <td class="px-3 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $absents }}
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
                                                    ✓ Complet {{ $pctSync }}%
                                                </span>
                                            @elseif($manchettes > 0 || $copies > 0)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded dark:bg-yellow-900 dark:text-yellow-300">
                                                    ⚠ Partiel {{ $pctSync }}%
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
                                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Aucune donnée disponible
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Légende simplifiée --}}
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            <strong>Complet :</strong> Manchettes ET Copies ≥ (Présents + Absents)
                        </div>
                    </div>

                    {{-- Légende améliorée --}}
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                        <div class="flex flex-wrap gap-4 text-xs text-gray-600 dark:text-gray-400">
                            <span><strong>Inscrits:</strong> Total (Présents + Absents)</span>
                            <span><strong>Manchettes:</strong> Total manchettes saisies</span>
                            <span><strong>Copies:</strong> Total copies saisies</span>
                            <span><strong>P:</strong> Présents</span>
                            <span><strong>A:</strong> Absents</span>
                            <span class="text-orange-600 dark:text-orange-400">
                                ⚠ Les absents doivent aussi avoir manchette ET copie
                            </span>
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