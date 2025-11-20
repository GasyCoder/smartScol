{{-- step ec avec système de couleurs à 3 états - VERSION CORRIGÉE COMPLÈTE --}}
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 md:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            Choisissez EC
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400 block sm:inline mt-1 sm:mt-0">
                - {{ $niveauSelected->nom }}
                @if($parcoursSelected) / {{ $parcoursSelected->nom }} @endif
            </span>
        </h2>
        <div class="flex items-center gap-2">
            <button wire:click="forceReloadEcs" 
                    class="px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 border border-blue-300 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20"
                    title="Actualiser la liste">
                ↻ Actualiser
            </button>
            <button wire:click="backToStep('parcours')" 
                    class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                ← Retour
            </button>
        </div>
    </div>

    {{-- Barre recherche + perPage --}}
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
                <input type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Rechercher une EC..."
                    class="w-full pl-10 pr-10 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                @if(!empty($search))
                    <button type="button" wire:click="$set('search','')"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
                @endif
            </div>

            <select wire:model.live="perPage"
                class="px-3 py-2.5 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                <option value="12">12</option>
                <option value="24">24</option>
                <option value="30">30</option>
                <option value="48">48</option>
            </select>
        </div>

        @php
            $isPaginator = false;
            $totalCount = 0;
            $pageCount = 0;

            if (isset($ecs)) {
                $isPaginator = ($ecs instanceof \Illuminate\Contracts\Pagination\Paginator) 
                            || ($ecs instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator);

                if ($isPaginator) {
                    $totalCount = $ecs->total();
                    $pageCount = $ecs->count();
                } elseif (is_countable($ecs)) {
                    $totalCount = count($ecs);
                    $pageCount = count($ecs);
                }
            }
            
            $ecProgress = collect();
            if (method_exists($this, 'getEcProgressData')) {
                try {
                    $ecProgress = $this->getEcProgressData() ?? collect();
                } catch (\Exception $e) {
                    $ecProgress = collect();
                }
            }
        @endphp

        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            @if(!empty($search))
                Résultats pour « {{ $search }} » — {{ $totalCount }} EC(s)
            @else
                {{ $totalCount }} EC(s) 
                @if($sessionType === 'rattrapage')
                    @if(!empty($ecsDisponibles))
                        ({{ count($ecsDisponibles) }} disponible(s) en rattrapage)
                    @else
                        (aucune EC à rattraper)
                    @endif
                @else
                    disponibles
                @endif
            @endif
        </div>
    </div>

    {{-- Légende --}}
    <div class="mb-4 flex flex-wrap items-center gap-4 text-sm">
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 rounded bg-blue-500"></div>
            <span class="text-gray-600 dark:text-gray-400">Pas commencé</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 rounded bg-yellow-500"></div>
            <span class="text-gray-600 dark:text-gray-400">En cours</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 rounded bg-green-500"></div>
            <span class="text-gray-600 dark:text-gray-400">Terminé</span>
        </div>
        @if($sessionType === 'rattrapage')
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded bg-gray-400"></div>
                <span class="text-gray-600 dark:text-gray-400">UE validée</span>
            </div>
        @endif
    </div>

    {{-- Panneau rattrapage --}}
    @if($sessionType === 'rattrapage')
        <div class="mb-4 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="h-6 w-6 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div class="flex-1">
                    <h4 class="text-base font-semibold text-orange-800 dark:text-orange-300 mb-2">
                        Session de Rattrapage - Après Délibération
                    </h4>
                    
                    @if(!empty($statistiquesRattrapage) && $statistiquesRattrapage['ecs_concernees'] > 0)
                        <div class="grid grid-cols-3 gap-3 mb-3">
                            <div class="bg-orange-100 dark:bg-orange-900/30 rounded-lg p-3 text-center">
                                <div class="text-lg font-bold text-orange-800 dark:text-orange-300">
                                    {{ $statistiquesRattrapage['etudiants_eligibles'] }}
                                </div>
                                <div class="text-xs text-orange-600 dark:text-orange-400">
                                    Étudiants autorisés
                                </div>
                            </div>
                            <div class="bg-orange-100 dark:bg-orange-900/30 rounded-lg p-3 text-center">
                                <div class="text-lg font-bold text-orange-800 dark:text-orange-300">
                                    {{ count($statistiquesRattrapage['ues_analysees'] ?? []) }}
                                </div>
                                <div class="text-xs text-orange-600 dark:text-orange-400">
                                    UE à rattraper
                                </div>
                            </div>
                            <div class="bg-orange-100 dark:bg-orange-900/30 rounded-lg p-3 text-center">
                                <div class="text-lg font-bold text-orange-800 dark:text-orange-300">
                                    {{ $statistiquesRattrapage['ecs_concernees'] }}
                                </div>
                                <div class="text-xs text-orange-600 dark:text-orange-400">
                                    ECs à rattraper
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-xs italic text-orange-600 dark:text-orange-400">
                            ✅ Effectifs basés sur les décisions de délibération (decision = 'rattrapage')
                        </div>
                    @else
                        <div class="text-sm text-orange-700 dark:text-orange-400">
                            ⚠️ Aucun étudiant n'est autorisé au rattrapage après délibération.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Grille cartes --}}
    @if($pageCount > 0 && isset($ecs))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
            wire:key="ecs-grid-{{ $niveauSelected->id ?? 0 }}-{{ $parcoursSelected->id ?? 0 }}-{{ $examenSelected->id ?? 0 }}-{{ $search ?? '' }}-{{ $perPage ?? 12 }}">
            @foreach($ecs as $ec)
                @php
                    $isDisponibleRattrapage = $sessionType !== 'rattrapage' || in_array($ec->id, $ecsDisponibles ?? []);
                    
                    $etudiantsEligiblesEC = collect();
                    if ($sessionType === 'rattrapage' && $isDisponibleRattrapage) {
                        $etudiantsEligiblesEC = $this->getEtudiantsEligiblesPourEC($ec->id);
                    }
                    
                    $progress = $ecProgress->get($ec->id, ['count' => 0, 'total_prevu' => 0, 'est_termine' => false]);
                    $progressCount = is_array($progress) && isset($progress['count']) ? $progress['count'] : 0;
                    $totalPrevu = is_array($progress) && isset($progress['total_prevu']) ? $progress['total_prevu'] : 0;
                    $estTermine = is_array($progress) && isset($progress['est_termine']) ? $progress['est_termine'] : false;
                    
                    // Calculer présents et absents AVEC VÉRIFICATIONS
                    $presents = 0;
                    $absents = 0;
                    
                    if (isset($this->examenSelected) && $this->examenSelected && isset($ec->id)) {
                        try {
                            $stats = $this->getStatistiquesEC($ec->id);
                            $presents = $stats['presents'] ?? 0;
                            $absents = $stats['absents'] ?? 0;
                        } catch (\Exception $e) {
                            // En cas d'erreur, on continue avec 0
                        }
                    }
                    
                    if (!$isDisponibleRattrapage) {
                        $status = 'disabled';
                    } elseif ($progressCount === 0) {
                        $status = 'none';
                    } elseif ($estTermine) {
                        $status = 'completed';
                    } else {
                        $status = 'progress';
                    }
                @endphp
                
                <button wire:key="ec-{{ $ec->id }}-{{ $parcoursSelected->id ?? 0 }}" 
                        wire:click="selectEC({{ $ec->id }})"
                        {{ !$isDisponibleRattrapage ? 'disabled' : '' }}
                        class="w-full p-4 border rounded-lg transition-all duration-200 text-left group relative
                               @if($status === 'disabled') border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-900/50 opacity-60 cursor-not-allowed
                               @elseif($status === 'completed') border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30
                               @elseif($status === 'progress') border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 hover:bg-yellow-100 dark:hover:bg-yellow-900/30
                               @else border-gray-200 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 @endif">
                    
                    {{-- Badges --}}
                    <div class="absolute top-3 right-3 flex flex-col gap-1.5">
                        @if($status === 'disabled')
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                UE validée
                            </span>
                        @endif
                        
                        @if($progressCount > 0)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                       @if($status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                       @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                                {{ $progressCount }} manchette{{ $progressCount > 1 ? 's' : '' }}
                            </span>
                        @endif
                        
                        @if($sessionType === 'rattrapage' && $isDisponibleRattrapage && $etudiantsEligiblesEC->count() > 0)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                {{ $etudiantsEligiblesEC->count() }} éligible{{ $etudiantsEligiblesEC->count() > 1 ? 's' : '' }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0
                                    @if($status === 'disabled') bg-gray-200 dark:bg-gray-700
                                    @elseif($status === 'completed') bg-green-100 dark:bg-green-900/30
                                    @elseif($status === 'progress') bg-yellow-100 dark:bg-yellow-900/30
                                    @else bg-blue-100 dark:bg-blue-900/30 @endif">
                            <span class="@if($status === 'disabled') text-gray-500 dark:text-gray-400
                                       @elseif($status === 'completed') text-green-600 dark:text-green-400
                                       @elseif($status === 'progress') text-yellow-600 dark:text-yellow-400
                                       @else text-blue-600 dark:text-blue-400 @endif font-semibold text-sm">
                                {{ $ec->abr ?? 'EC' }}
                            </span>
                        </div>

                        <div class="flex-1 min-w-0 pr-20">
                            @php
                                $nomEc = $ec->nom ?? 'Matière sans nom';
                                if(!empty($search)) {
                                    $pattern = '/' . preg_quote($search, '/') . '/i';
                                    $nomEsc = e($nomEc);
                                    $nomHighlighted = preg_replace(
                                        $pattern,
                                        '<mark class="bg-yellow-200 dark:bg-yellow-600 text-gray-900 dark:text-gray-100 rounded px-0.5">$0</mark>',
                                        $nomEsc
                                    );
                                } else {
                                    $nomHighlighted = e($nomEc);
                                }
                            @endphp

                            <h3 class="text-base font-medium mb-1
                                     @if($status === 'disabled') text-gray-500 dark:text-gray-400
                                     @else text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-300 @endif">
                                {!! $nomHighlighted !!}
                            </h3>

                            @if(!empty($ec->ue))
                                <p class="text-sm mb-0.5 @if($status === 'disabled') text-gray-400 dark:text-gray-500 @else text-gray-500 dark:text-gray-400 @endif">
                                    UE: {{ $ec->ue->nom ?? 'N/A' }}
                                </p>
                            @endif
                            
                            {{-- ENSEIGNANT - IMPORTANT --}}
                            @if(!empty($ec->enseignant))
                                <p class="text-sm font-semibold @if($status === 'disabled') text-gray-400 dark:text-gray-500 @else text-blue-600 dark:text-blue-400 @endif">
                                    👨‍🏫 {{ $ec->enseignant }}
                                </p>
                            @endif

                            {{-- PRÉSENTS ET ABSENTS --}}
                            @if($presents > 0 || $absents > 0)
                                <div class="mt-2 flex items-center gap-3 text-xs">
                                    @if($presents > 0)
                                        <span class="flex items-center gap-1 text-green-600 dark:text-green-400 font-medium">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $presents }} présent{{ $presents > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                    @if($absents > 0)
                                        <span class="flex items-center gap-1 text-red-600 dark:text-red-400 font-medium">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $absents }} absent{{ $absents > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Infos rattrapage --}}
                            @if($sessionType === 'rattrapage')
                                @if($status === 'disabled')
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">
                                        UE déjà validée en session normale
                                    </div>
                                @elseif($etudiantsEligiblesEC->count() > 0)
                                    <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                                        {{ $etudiantsEligiblesEC->count() }} étudiant(s) doit/doivent rattraper
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Icône --}}
                        @if($status !== 'disabled')
                            <svg wire:loading.remove wire:target="selectEC({{ $ec->id }})"
                                class="h-5 w-5 text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-400 absolute top-4 right-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <svg wire:loading wire:target="selectEC({{ $ec->id }})"
                                class="h-4 w-4 animate-spin text-blue-500 absolute top-4 right-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.38 0 0 5.38 0 12h4zm2 5.29A8 8 0 014 12H0c0 3.04 1.14 5.82 3 7.94l3-2.65z" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-gray-300 absolute top-4 right-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </div>

                    {{-- Barre de progression --}}
                    @if($progressCount > 0 && $status !== 'disabled')
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center text-sm">
                                <span class="@if($status === 'completed') text-green-600 dark:text-green-400
                                           @else text-yellow-600 dark:text-yellow-400 @endif font-medium">
                                    {{ $progressCount }}/{{ $totalPrevu }} saisie(s)
                                </span>
                                <span class="@if($status === 'completed') text-green-600 dark:text-green-400 font-semibold
                                           @else text-yellow-600 dark:text-yellow-400 @endif text-xs">
                                    @if($status === 'completed') ✅ Terminé @else 🔄 En cours @endif
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="@if($status === 'completed') bg-green-500 @else bg-yellow-500 @endif h-2 rounded-full transition-all duration-300" 
                                    style="width: {{ $totalPrevu > 0 ? min(($progressCount / $totalPrevu) * 100, 100) : 0 }}%">
                                </div>
                            </div>
                            @if($absents > 0)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    + {{ $absents }} absent(s) synchronisé(s)
                                </p>
                            @endif
                        </div>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($isPaginator && isset($ecs))
            <div class="mt-4">
                {{ $ecs->links(data: ['scrollTo' => false]) }}
            </div>
        @endif

        {{-- Statistiques --}}
        @if($ecProgress->isNotEmpty())
            <div class="mt-5 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">📊 Statistiques des ECs</h4>
                @php
                    $totalManchettes = $ecProgress->sum('count') ?? 0;
                    $ecsTerminees = $ecProgress->filter(fn($p) => isset($p['est_termine']) && $p['est_termine'])->count() ?? 0;
                    $ecsEnCours = $ecProgress->filter(fn($p) => isset($p['count']) && $p['count'] > 0 && (!isset($p['est_termine']) || !$p['est_termine']))->count() ?? 0;
                    $ecsPasCommencees = $totalCount - $ecsTerminees - $ecsEnCours;
                    
                    $ecsDisponiblesCount = $sessionType === 'rattrapage' ? count($ecsDisponibles ?? []) : $totalCount;
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm text-blue-600 dark:text-blue-400">
                    <div><strong>Terminées:</strong> {{ $ecsTerminees }} <span class="text-green-600">●</span></div>
                    <div><strong>En cours:</strong> {{ $ecsEnCours }} <span class="text-yellow-600">●</span></div>
                    <div><strong>Non commencées:</strong> {{ $ecsPasCommencees }} <span class="text-blue-600">●</span></div>
                    <div><strong>Total:</strong> {{ $totalManchettes }} manchettes</div>
                </div>
                
                @if($sessionType === 'rattrapage' && !empty($statistiquesRattrapage))
                    <div class="mt-3 pt-3 border-t border-blue-200 dark:border-blue-600 text-sm text-blue-600 dark:text-blue-400">
                        <strong>Rattrapage:</strong>
                        {{ count($statistiquesRattrapage['detail_etudiants'] ?? []) }} étudiant(s) éligible(s),
                        {{ $ecsDisponiblesCount }} EC(s) disponible(s)
                    </div>
                @endif
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune EC disponible</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($sessionType === 'rattrapage')
                    Aucune EC n'est disponible pour le rattrapage.
                @else
                    Aucune EC trouvée pour cette configuration.
                @endif
            </p>
        </div>
    @endif
</div>