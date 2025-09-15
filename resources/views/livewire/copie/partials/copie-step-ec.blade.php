<!-- EC (seulement ceux ayant des manchettes) avec statistiques -->
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            Choisissez EC
            <span class="text-base font-normal text-gray-500 dark:text-gray-400">
                @if($niveauSelected) - {{ $niveauSelected->abr }} @endif
                @if($parcoursSelected) / {{ $parcoursSelected->abr }} @endif
            </span>
        </h2>
        <button wire:click="backToStep('parcours')" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">← Retour</button>
    </div>

    <div class="mb-4">
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" /></svg>
                <input type="search" 
                       wire:model.debounce.500ms="search"
                       placeholder="Rechercher une EC (nom, abr, UE)…"
                       class="w-full pl-10 pr-10 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                @if(!empty($search))
                    <button type="button" wire:click="$set('search','')"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
                @endif
            </div>

            <select onchange="@this.set('perPage', this.value)"
                    class="px-2 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                <option value="12" {{ $perPage == 12 ? 'selected' : '' }}>12</option>
                <option value="24" {{ $perPage == 24 ? 'selected' : '' }}>24</option>
                <option value="48" {{ $perPage == 48 ? 'selected' : '' }}>48</option>
            </select>
        </div>

        @php
            $isPaginator = ($ecs instanceof \Illuminate\Contracts\Pagination\Paginator) || ($ecs instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator);
            $totalCount  = $isPaginator ? $ecs->total() : (is_countable($ecs) ? count($ecs) : 0);
            $pageCount   = $isPaginator ? $ecs->count() : (is_countable($ecs) ? count($ecs) : 0);
        @endphp

        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            @if(!empty($search))
                Résultats pour « {{ $search }} » — {{ $totalCount }} EC(s) avec manchettes
            @else
                {{ $totalCount }} EC(s) avec manchettes
            @endif
        </div>
    </div>

    @if($pageCount > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($ecs as $ec)
                <button wire:click="selectEC({{ $ec->id }})" wire:key="selectEC-{{ $ec->id }}"
                        class="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200 text-left group
                               {{ isset($ec->est_terminee) && $ec->est_terminee ? 'bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-600' : '' }}">
                    
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 {{ isset($ec->est_terminee) && $ec->est_terminee ? 'bg-green-100 dark:bg-green-900/30' : 'bg-blue-100 dark:bg-blue-900/30' }} rounded-lg flex items-center justify-center flex-shrink-0">
                            <span class="{{ isset($ec->est_terminee) && $ec->est_terminee ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }} font-semibold text-sm">{{ $ec->abr }}</span>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-medium text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-300 truncate">
                                        {{ $ec->nom }}
                                    </h3>
                                    @if(!empty($ec->ue))
                                        <p class="text-sm text-gray-500 dark:text-gray-400">UE: {{ $ec->ue->nom }}</p>
                                    @endif
                                </div>
                                
                                <!-- Indicateur de statut -->
                                @if(isset($ec->est_terminee) && $ec->est_terminee)
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Statistiques des manchettes -->
                            @if(isset($ec->total_manchettes))
                                <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-600 dark:text-gray-400">
                                            Copies: {{ $ec->copies_saisies ?? 0 }}/{{ $ec->total_manchettes }}
                                        </span>
                                        <span class="font-semibold {{ isset($ec->est_terminee) && $ec->est_terminee ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}">
                                            {{ $ec->pourcentage ?? 0 }}%
                                        </span>
                                    </div>
                                    
                                    <!-- Barre de progression -->
                                    <div class="mt-1 w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full transition-all duration-300 {{ isset($ec->est_terminee) && $ec->est_terminee ? 'bg-green-500' : 'bg-blue-500' }}" 
                                             style="width: {{ $ec->pourcentage ?? 0 }}%">
                                        </div>
                                    </div>
                                    
                                    @if(isset($ec->restantes) && $ec->restantes > 0)
                                        <div class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                                            {{ $ec->restantes }} restante(s)
                                        </div>
                                    @elseif(isset($ec->est_terminee) && $ec->est_terminee)
                                        <div class="mt-1 text-xs text-green-600 dark:text-green-400 font-medium">
                                            ✓ Terminée
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $ecs->links(data: ['scrollTo' => false]) }}
        </div>
    @endif
</div>