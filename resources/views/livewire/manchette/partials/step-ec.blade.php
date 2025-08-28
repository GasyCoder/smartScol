{{-- step ec avec système de couleurs à 3 états --}}
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            Choisissez EC
            <span class="text-base font-normal text-gray-500 dark:text-gray-400">
                - {{ $niveauSelected->nom }}
                @if($parcoursSelected) / {{ $parcoursSelected->nom }} @endif
            </span>
        </h2>
        <div class="flex items-center gap-2">
            <button wire:click="forceReloadEcs" 
                    class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 border border-blue-300 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20"
                    title="Actualiser la liste">
                ↻ Actualiser
            </button>
            <button wire:click="backToStep('parcours')" 
                    class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                ← Retour
            </button>
        </div>
    </div>

    {{-- Barre recherche + perPage --}}
    <div class="mb-4">
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
                <input type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Rechercher une EC (nom, abr, UE)…"
                    class="w-full pl-10 pr-10 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                @if(!empty($search))
                    <button type="button" wire:click="$set('search','')"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
                @endif
            </div>

            <select wire:model.live="perPage"
                class="px-2 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                <option value="12">12</option>
                <option value="24">24</option>
                <option value="30">30</option>
                <option value="48">48</option>
            </select>
        </div>

        @php
            // CORRECTION: Vérifications de type plus robustes
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
            
            // Récupérer les statistiques de progression - CORRECTION avec vérification
            $ecProgress = collect();
            if (method_exists($this, 'getEcProgressData')) {
                try {
                    $ecProgress = $this->getEcProgressData() ?? collect();
                } catch (\Exception $e) {
                    // En cas d'erreur, on continue avec une collection vide
                    $ecProgress = collect();
                }
            }
        @endphp

        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            @if(!empty($search))
                Résultats pour « {{ $search }} » — {{ $totalCount }} EC(s)
            @else
                {{ $totalCount }} EC(s) disponibles
            @endif
        </div>
    </div>

    {{-- Légende des couleurs --}}
    <div class="mb-4 flex items-center gap-4 text-xs">
        <div class="flex items-center gap-1">
            <div class="w-3 h-3 rounded bg-blue-500"></div>
            <span class="text-gray-600 dark:text-gray-400">Pas commencé</span>
        </div>
        <div class="flex items-center gap-1">
            <div class="w-3 h-3 rounded bg-yellow-500"></div>
            <span class="text-gray-600 dark:text-gray-400">En cours</span>
        </div>
        <div class="flex items-center gap-1">
            <div class="w-3 h-3 rounded bg-green-500"></div>
            <span class="text-gray-600 dark:text-gray-400">Terminé</span>
        </div>
    </div>

    {{-- Grille cartes --}}
    @if($pageCount > 0 && isset($ecs))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"
            wire:key="ecs-grid-{{ $niveau_id }}-{{ $parcours_id }}-{{ $examen_id }}-{{ $search ?? '' }}-{{ $perPage ?? 12 }}">
            @foreach($ecs as $ec)
                @php
                    // Récupérer les données de progression pour cette EC
                    $progress = $ecProgress->get($ec->id, ['count' => 0, 'total_prevu' => 0, 'est_termine' => false]);
                    $progressCount = is_array($progress) && isset($progress['count']) ? $progress['count'] : 0;
                    $totalPrevu = is_array($progress) && isset($progress['total_prevu']) ? $progress['total_prevu'] : 0;
                    $estTermine = is_array($progress) && isset($progress['est_termine']) ? $progress['est_termine'] : false;
                    
                    // Déterminer l'état : 'none', 'progress', ou 'completed'
                    if ($progressCount === 0) {
                        $status = 'none'; // Pas commencé
                    } elseif ($estTermine) {
                        $status = 'completed'; // Terminé
                    } else {
                        $status = 'progress'; // En cours
                    }
                @endphp
                
                <button wire:key="ec-{{ $ec->id }}-{{ $parcours_id }}" wire:click="selectEC({{ $ec->id }})"
                        class="w-full p-4 border rounded-lg transition-all duration-200 text-left group disabled:opacity-50 disabled:cursor-not-allowed relative
                               @if($status === 'completed') border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30
                               @elseif($status === 'progress') border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 hover:bg-yellow-100 dark:hover:bg-yellow-900/30
                               @else border-gray-200 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 @endif">
                    
                    <!-- Badge de progression -->
                    @if($progressCount > 0)
                        <div class="absolute top-2 right-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                       @if($status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                       @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                                {{ $progressCount }} manchette{{ $progressCount > 1 ? 's' : '' }}
                            </span>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                    @if($status === 'completed') bg-green-100 dark:bg-green-900/30
                                    @elseif($status === 'progress') bg-yellow-100 dark:bg-yellow-900/30
                                    @else bg-blue-100 dark:bg-blue-900/30 @endif">
                            <span class="@if($status === 'completed') text-green-600 dark:text-green-400
                                       @elseif($status === 'progress') text-yellow-600 dark:text-yellow-400
                                       @else text-blue-600 dark:text-blue-400 @endif font-semibold text-sm">
                                {{ $ec->abr ?? 'EC' }}
                            </span>
                        </div>

                        <div class="flex-1">
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

                            <h3 class="text-base font-medium text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-300">
                                {!! $nomHighlighted !!}
                            </h3>

                            @if(!empty($ec->ue))
                                <p class="text-sm text-gray-500 dark:text-gray-400">UE: {{ $ec->ue->nom ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-bold">Enseignant:{{ $ec->enseignant ?? 'Non défini' }}</p>
                            @endif

                            <div class="flex items-center gap-2 mt-1">
                                @if(isset($ec->niveau_id))
                                    <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs rounded">N:{{ $ec->niveau_id }}</span>
                                @endif
                                @if(isset($ec->parcours_id))
                                    <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs rounded">P:{{ $ec->parcours_id }}</span>
                                @endif
                            </div>
                        </div>

                        <svg wire:loading.remove wire:target="selectEC({{ $ec->id }})"
                            class="h-5 w-5 text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <svg wire:loading wire:target="selectEC({{ $ec->id }})"
                            class="h-4 w-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.38 0 0 5.38 0 12h4zm2 5.29A8 8 0 014 12H0c0 3.04 1.14 5.82 3 7.94l3-2.65z" />
                        </svg>
                    </div>

                    <!-- Barre de progression et statut -->
                    @if($progressCount > 0)
                        <div class="mt-3 space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="@if($status === 'completed') text-green-600 dark:text-green-400
                                           @else text-yellow-600 dark:text-yellow-400 @endif">
                                    {{ $progressCount }}/{{ $totalPrevu }} saisie(s)
                                </span>
                                <span class="@if($status === 'completed') text-green-600 dark:text-green-400 font-medium
                                           @else text-yellow-600 dark:text-yellow-400 @endif">
                                    @if($status === 'completed')
                                        ✅ Terminé
                                    @else
                                        🔄 En cours
                                    @endif
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="@if($status === 'completed') bg-green-500 @else bg-yellow-500 @endif h-1.5 rounded-full" 
                                    style="width: {{ $totalPrevu > 0 ? min(($progressCount / $totalPrevu) * 100, 100) : 0 }}%">
                                </div>
                            </div>
                            
                            @if($status === 'completed')
                                <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                                    ✅ Saisie complétée
                                </div>
                            @else
                                <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                    🔄 Saisie en progression
                                </div>
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

        {{-- Panel info filtrage avec statistiques --}}
        @if($ecProgress->isNotEmpty())
            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">📊 Statistiques des ECs</h4>
                <div class="text-sm text-blue-600 dark:text-blue-400">
                    @php
                        $totalManchettes = $ecProgress->sum('count') ?? 0;
                        $ecsTerminees = $ecProgress->filter(fn($p) => isset($p['est_termine']) && $p['est_termine'])->count() ?? 0;
                        $ecsEnCours = $ecProgress->filter(fn($p) => isset($p['count']) && $p['count'] > 0 && (!isset($p['est_termine']) || !$p['est_termine']))->count() ?? 0;
                        $ecsPasCommencees = $totalCount - $ecsTerminees - $ecsEnCours;
                    @endphp
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span><strong>Terminées:</strong> {{ $ecsTerminees }} <span class="text-green-600">●</span></span>
                        <span class="text-gray-400">•</span>
                        <span><strong>En cours:</strong> {{ $ecsEnCours }} <span class="text-yellow-600">●</span></span>
                        <span class="text-gray-400">•</span>
                        <span><strong>Pas commencées:</strong> {{ $ecsPasCommencees }} <span class="text-blue-600">●</span></span>
                        <span class="text-gray-400">•</span>
                        <span><strong>Total manchettes:</strong> {{ $totalManchettes }}</span>
                        <span class="text-gray-400">•</span>
                        <span><strong>MAJ:</strong> {{ now()->format('H:i:s') }}</span>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>