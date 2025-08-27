{{-- step ec corrigé --}}
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            Choisissez la matière (EC)
            <span class="text-base font-normal text-gray-500 dark:text-gray-400">
                - {{ $niveauSelected->abr }}
                @if($parcoursSelected) / {{ $parcoursSelected->abr }} @endif
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

    {{-- Grille cartes --}}
    @if($pageCount > 0 && isset($ecs))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"
            wire:key="ecs-grid-{{ $niveau_id }}-{{ $parcours_id }}-{{ $examen_id }}-{{ $search ?? '' }}-{{ $perPage ?? 12 }}">
            @foreach($ecs as $ec)
                @php
                    $progress = $ecProgress->get($ec->id, ['count' => 0]);
                    $hasProgress = is_array($progress) && isset($progress['count']) && $progress['count'] > 0;
                    $progressCount = $hasProgress ? $progress['count'] : 0;
                @endphp
                
                <button wire:key="ec-{{ $ec->id }}-{{ $parcours_id }}" wire:click="selectEC({{ $ec->id }})"
                        class="w-full p-4 border rounded-lg transition-all duration-200 text-left group disabled:opacity-50 disabled:cursor-not-allowed relative
                               {{ $hasProgress 
                                   ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30' 
                                   : 'border-gray-200 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20' }}">
                    
                    <!-- Badge de progression -->
                    @if($hasProgress)
                        <div class="absolute top-2 right-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                {{ $progressCount }} manchette{{ $progressCount > 1 ? 's' : '' }}
                            </span>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                    {{ $hasProgress 
                                        ? 'bg-green-100 dark:bg-green-900/30' 
                                        : 'bg-blue-100 dark:bg-blue-900/30' }}">
                            <span class="{{ $hasProgress 
                                        ? 'text-green-600 dark:text-green-400' 
                                        : 'text-blue-600 dark:text-blue-400' }} font-semibold text-sm">
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

                    @php
                        $progress = $ecProgress->get($ec->id, ['count' => 0, 'total_prevu' => 0, 'est_termine' => false]);
                        $hasProgress = is_array($progress) && isset($progress['count']) && $progress['count'] > 0;
                        $progressCount = $hasProgress ? $progress['count'] : 0;
                        $totalPrevu = $hasProgress ? ($progress['total_prevu'] ?? 0) : 0;
                        $estTermine = $hasProgress ? ($progress['est_termine'] ?? false) : false;
                    @endphp

                    <!-- Barre de progression -->
                    @if($hasProgress)
                        <div class="mt-3 space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="{{ $estTermine ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}">
                                    {{ $progressCount }}/{{ $totalPrevu }} saisie(s)
                                </span>
                                <span class="{{ $estTermine ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ $estTermine ? '✅ Terminé' : '🔄 En cours' }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="{{ $estTermine ? 'bg-green-500' : 'bg-blue-500' }} h-1.5 rounded-full" 
                                    style="width: {{ $totalPrevu > 0 ? min(($progressCount / $totalPrevu) * 100, 100) : 0 }}%">
                                </div>
                            </div>
                            
                            @if($estTermine)
                                <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                                    ✅ Saisie complétée
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
                <div class="text-sm text-blue-600 dark:text-blue-400 space-y-1">
                    @php
                        $totalManchettes = $ecProgress->sum('count') ?? 0;
                        $ecsAvecProgres = $ecProgress->filter(fn($p) => isset($p['count']) && $p['count'] > 0)->count() ?? 0;
                    @endphp
                    <p><strong>ECs avec progression:</strong> {{ $ecsAvecProgres }} / {{ $totalCount }}</p>
                    <p><strong>Total manchettes saisies:</strong> {{ $totalManchettes }}</p>
                    <p><strong>Dernière MAJ:</strong> {{ now()->format('H:i:s') }}</p>
                </div>
            </div>
        @endif
    @endif
</div>