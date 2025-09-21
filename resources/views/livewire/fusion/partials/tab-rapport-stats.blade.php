{{-- Onglet Rapport et Statistiques - UI/UX Optimisée --}}
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
            $stats = $rapportCoherence['stats'];
            $totalMatieres = $stats['total'] ?? 0;
            $matieresCompletes = $stats['complets'] ?? 0;
            $matieresIncompletes = $stats['incomplets'] ?? 0;
            $completionRate = $totalMatieres > 0 ? round(($matieresCompletes / $totalMatieres) * 100) : 0;
        @endphp

        <div class="space-y-4">
            {{-- Résumé compact avec métriques --}}
            <div class="p-4 border rounded-lg {{ $completionRate === 100 ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' : ($completionRate > 0 ? 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800' : 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800') }}">
                {{-- Header avec titre et pourcentage --}}
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold {{ $completionRate === 100 ? 'text-green-800 dark:text-green-200' : ($completionRate > 0 ? 'text-yellow-800 dark:text-yellow-200' : 'text-red-800 dark:text-red-200') }}">
                        Rapport de cohérence
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            <span class="ml-2 px-2 py-0.5 text-xs font-medium text-orange-800 bg-orange-200 rounded dark:bg-orange-800 dark:text-orange-200">
                                RATTRAPAGE
                            </span>
                        @endif
                    </h3>
                    
                    <div class="flex items-center space-x-4">
                        @if(isset($rapportCoherence['last_check']))
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $rapportCoherence['last_check'] }}
                            </span>
                        @endif
                        <div class="text-right">
                            <div class="text-xl font-bold {{ $completionRate === 100 ? 'text-green-600 dark:text-green-400' : ($completionRate > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ $completionRate }}%
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Métriques en ligne compactes --}}
              <div class="grid grid-cols-3 gap-4 mb-3 md:grid-cols-5">
                    <div class="text-center">
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $totalMatieres }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Total</div>
                    </div>
        
                    <div class="text-center">
                        <div class="text-lg font-semibold text-green-600 dark:text-green-400">{{ $matieresCompletes }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                Fusionnées
                            @else
                                Complètes
                            @endif
                        </div>
                    </div>
    
                    <div class="text-center">
                        <div class="text-lg font-semibold text-orange-600 dark:text-orange-400">{{ $matieresIncompletes }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                Partielles
                            @else
                                Incomplètes
                            @endif
                        </div>
                    </div>

                    <div class="text-center">
                        <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ $resultatsStats['etudiants'] ?? 0 }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Étudiants</div>
                    </div>

                    <div class="text-center">
                        <div class="text-lg font-semibold {{ $statut === 'publie' ? 'text-green-600' : 'text-gray-600' }} dark:text-gray-400">
                            {{ ucfirst($statut) }}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Statut</div>
                    </div>
                </div>

                {{-- Barre de progression compacte --}}
                <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                    <div class="h-1.5 rounded-full transition-all duration-300 {{ $completionRate === 100 ? 'bg-green-500 dark:bg-green-400' : ($completionRate > 0 ? 'bg-yellow-500 dark:bg-yellow-400' : 'bg-red-500 dark:bg-red-400') }}" 
                         style="width: {{ $completionRate }}%"></div>
                </div>
            </div>

            {{-- Actions rapides dans le header --}}
            @if($statut !== 'initial')
                <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        @if($statut === 'verification' && $matieresCompletes > 0)
                            ✅ Prêt pour la fusion
                        @elseif($statut === 'verification')
                            ⚠️ Aucune matière complète
                        @elseif($statut === 'fusion')
                            🔄 Fusion étape {{ $etapeFusion }}/3
                        @elseif($statut === 'valide')
                            ✅ Prêt pour publication
                        @elseif($statut === 'publie')
                            🚀 Résultats publiés
                        @elseif($statut === 'annule')
                            ❌ Résultats annulés
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if($showVerificationButton)
                            <button wire:click="confirmVerification"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors disabled:opacity-50">
                                🔍 Re-vérifier
                            </button>
                        @endif

                        @if($statut === 'publie')
                            <a href="{{ route('resultats.finale') }}" 
                               class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-green-700 bg-green-100 rounded-md hover:bg-green-200 transition-colors">
                                👁️ Voir résultats
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Tableau détaillé optimisé --}}
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
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                        Matière
                                    </th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                                        M
                                    </th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                                        C
                                    </th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                                        P
                                    </th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                        Statut
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @php
                                    $completes = collect($rapportCoherence['data'])->where('complet', true);
                                    $partielles = collect($rapportCoherence['data'])->where('complet', false)->where(function($item) {
                                        return ($item['manchettes_count'] ?? 0) > 0 || ($item['copies_count'] ?? 0) > 0;
                                    });
                                    $vides = collect($rapportCoherence['data'])->where('complet', false)->where(function($item) {
                                        return ($item['manchettes_count'] ?? 0) === 0 && ($item['copies_count'] ?? 0) === 0;
                                    });
                                    $orderedData = $completes->concat($partielles)->concat($vides);
                                @endphp

                                @forelse($orderedData as $rapport)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $rapport['complet'] ? 'bg-green-50 dark:bg-green-900/10' : '' }}">
                                        <td class="px-4 py-2">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $rapport['ec_nom'] ?? 'N/A' }}
                                            </div>
                                        </td>
                                            
                                        <td class="px-3 py-2 text-center text-sm {{ ($rapport['manchettes_count'] ?? 0) > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }} dark:text-gray-100">
                                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                @if(isset($rapport['type_fusion']) && $rapport['type_fusion'] === 'recuperation_auto')
                                                    {{-- AFFICHAGE SPÉCIAL pour récupération automatique --}}
                                                    <span class="px-1 py-0.5 text-xs bg-blue-100 text-blue-800 rounded dark:bg-blue-900 dark:text-blue-200">
                                                        AUTO
                                                    </span>
                                                @else
                                                    {{-- Affichage normal pour fusion avec rattrapage --}}
                                                    {{ $rapport['manchettes_count'] ?? 0 }}
                                                @endif
                                            @else
                                                {{ $rapport['manchettes_count'] ?? 0 }}
                                            @endif
                                        </td>
                                                                                
                                        <td class="px-3 py-2 text-center text-sm {{ ($rapport['copies_count'] ?? 0) > 0 ? 'text-blue-600 font-semibold' : 'text-gray-400' }} dark:text-gray-100">
                                            {{ $rapport['copies_count'] ?? 0 }}
                                        </td>

                                        <td class="px-3 py-2 text-center text-sm {{ ($rapport['etudiants_presents'] ?? 0) > 0 ? 'text-purple-600 font-semibold' : 'text-gray-400' }} dark:text-gray-100">
                                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                                @if(isset($rapport['type_fusion']) && $rapport['type_fusion'] === 'recuperation_auto')
                                                    {{-- Pour récupération auto, afficher le nombre de notes récupérées --}}
                                                    {{ $rapport['notes_recuperees_auto'] ?? $rapport['total_etudiants'] ?? 0 }}
                                                @else
                                                    {{-- Pour fusion normale avec rattrapage --}}
                                                    {{ $rapport['total_etudiants'] ?? $rapport['etudiants_presents'] ?? 0 }}
                                                @endif
                                            @else
                                                {{-- Session normale : toujours afficher etudiants_presents --}}
                                                {{ $rapport['etudiants_presents'] ?? $rapport['total_etudiants'] ?? 0 }}
                                            @endif
                                            
                                        </td>

                                        <td class="px-4 py-2 text-center">
                                            @if($rapport['complet'] ?? false)
                                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-green-800 bg-green-100 rounded dark:bg-green-900 dark:text-green-300">
                                                    ✓
                                                </span>
                                            @elseif(($rapport['manchettes_count'] ?? 0) > 0 || ($rapport['copies_count'] ?? 0) > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-yellow-800 bg-yellow-100 rounded dark:bg-yellow-900 dark:text-yellow-300">
                                                    ⚠
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-gray-500 bg-gray-100 rounded dark:bg-gray-700 dark:text-gray-400">
                                                    -
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

                    {{-- Légende compacte --}}
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                        <div class="flex flex-wrap gap-4 text-xs text-gray-600 dark:text-gray-400">
                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                <span><strong>M:</strong> Notes normales / AUTO = récupération auto</span>
                                <span><strong>C:</strong> Copies rattrapage</span>
                                <span><strong>P:</strong> Notes fusionnées</span>
                            @else
                                <span><strong>M:</strong> Manchettes</span>
                                <span><strong>C:</strong> Copies</span>
                                <span><strong>P:</strong> Présents</span>
                            @endif
                            <span class="ml-auto">{{ $matieresCompletes }} complète(s) sur {{ $totalMatieres }}</span>
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