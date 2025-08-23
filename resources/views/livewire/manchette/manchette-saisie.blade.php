<div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- En-tête -->
    <div class="mb-4">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Saisie des Manchettes
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Session {{ ucfirst($sessionType) }} - Attribution des codes d'anonymat
        </p>
    </div>

    <!-- Breadcrumb de progression -->
    <div class="mb-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
                <li class="flex items-center">
                    <button wire:click="backToStep('niveau')" 
                        class="flex items-center text-sm font-medium {{ $step === 'niveau' ? 'text-blue-600 dark:text-blue-400' : ($niveauSelected ? 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' : 'text-gray-400 dark:text-gray-500') }}">
                        <svg class="flex-shrink-0 h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L9 5.414V17a1 1 0 102 0V5.414l5.293 5.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        Niveau
                    </button>
                </li>
                
                @if($niveauSelected)
                    <li class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <button wire:click="backToStep('parcours')" 
                            class="ml-4 text-sm font-medium {{ $step === 'parcours' ? 'text-blue-600 dark:text-blue-400' : ($parcoursSelected || empty($parcours) ? 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' : 'text-gray-400 dark:text-gray-500') }}">
                            Parcours
                        </button>
                    </li>
                @endif
                
                @if($niveauSelected && ($parcoursSelected || empty($parcours)))
                    <li class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <button wire:click="backToStep('ec')" 
                            class="ml-4 text-sm font-medium {{ $step === 'ec' ? 'text-blue-600 dark:text-blue-400' : ($ecSelected ? 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' : 'text-gray-400 dark:text-gray-500') }}">
                            Matière
                        </button>
                    </li>
                @endif
                
                @if($ecSelected)
                    <li class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-4 text-sm font-medium {{ $step === 'setup' || $step === 'saisie' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' }}">
                            {{ $step === 'saisie' ? 'Saisie' : 'Configuration' }}
                        </span>
                    </li>
                @endif
            </ol>
        </nav>
    </div>

    <!-- Messages -->
    @if($message)
        <div class="mb-4 p-4 rounded-lg @if($messageType === 'success') bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/50 dark:border-green-700 dark:text-green-300 @elseif($messageType === 'error') bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/50 dark:border-red-700 dark:text-red-300 @elseif($messageType === 'warning') bg-yellow-50 border border-yellow-200 text-yellow-800 dark:bg-yellow-900/50 dark:border-yellow-700 dark:text-yellow-300 @else bg-blue-50 border border-blue-200 text-blue-800 dark:bg-blue-900/50 dark:border-blue-700 dark:text-blue-300 @endif">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    @if($messageType === 'success')
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @elseif($messageType === 'error')
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    @elseif($messageType === 'warning')
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ $message }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Contenu principal selon l'étape -->
    @if($step === 'niveau')
        <!-- Sélection Niveau -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                Choisissez le niveau d'études
            </h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($niveaux as $niveau)
                    <button wire:click="selectNiveau({{ $niveau->id }})" 
                        class="p-6 border-2 border-gray-200 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200 text-left group">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-300">
                                    {{ $niveau->abr }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $niveau->nom }}
                                </p>
                                <div class="flex items-center mt-2 space-x-2">
                                    @if($niveau->has_parcours)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            Avec parcours
                                        </span>
                                    @endif
                                    @if($niveau->is_concours)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                            Concours
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <svg class="h-6 w-6 text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

    @elseif($step === 'parcours')
        <!-- Sélection Parcours -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Choisissez le parcours - {{ $niveauSelected->abr }}
                </h2>
                <button wire:click="backToStep('niveau')" wire:key="back-to-niveau" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    ← Retour
                </button>
            </div>
            
            @if(!empty($parcours) && $parcours->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($parcours as $parcour)
                        <button wire:click="selectParcours({{ $parcour->id }})" 
                            class="p-5 border-2 border-gray-200 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200 text-left group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-300">
                                        {{ $parcour->abr }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $parcour->nom }}
                                    </p>
                                </div>
                                <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </button>
                    @endforeach
                </div>
                
                <!-- Option "Tous les parcours" -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="selectParcours()" 
                        class="w-full p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200 text-center group">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300 group-hover:text-blue-700 dark:group-hover:text-blue-300 font-medium">
                                Tous les parcours
                            </span>
                        </div>
                    </button>
                </div>
            @else
                <!-- Pas de parcours spécifiques -->
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Aucun parcours spécifique pour ce niveau
                    </p>
                    <button wire:click="selectParcours()" 
                        class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Continuer sans parcours
                    </button>
                </div>
            @endif
        </div>

    @elseif($step === 'ec')
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Choisissez la matière (EC)
                    <span class="text-base font-normal text-gray-500 dark:text-gray-400">
                        - {{ $niveauSelected->abr }}
                        @if($parcoursSelected) / {{ $parcoursSelected->abr }} @endif
                    </span>
                </h2>
                <button wire:click="backToStep('parcours')" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    ← Retour
                </button>
            </div>

            {{-- (Optionnel) Sélecteur d’examen si plusieurs --}}
            @if(isset($examensList) && $examensList->count() > 1)
            <div class="mb-3">
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Examen</label>
                <select wire:model.live="examen_id" class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                    @foreach($examensList as $ex)
                        <option value="{{ $ex->id }}">{{ $ex->intitule ?? ('Examen #'.$ex->id) }}</option>
                    @endforeach
                </select>
            </div>
            @endif

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
                        <option value="48">48</option>
                    </select>
                </div>

                @php
                    $isPaginator = ($ecs instanceof \Illuminate\Contracts\Pagination\Paginator) 
                                || ($ecs instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator);

                    $totalCount  = $isPaginator ? $ecs->total() : (is_countable($ecs) ? count($ecs) : 0);
                    $pageCount   = $isPaginator ? $ecs->count() : (is_countable($ecs) ? count($ecs) : 0);
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
            @if($pageCount > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"
                    wire:key="ecs-grid-{{ $niveau_id }}-{{ $parcours_id }}-{{ $examen_id }}-{{ $search ?? '' }}-{{ $perPage ?? 12 }}">
                    @foreach($ecs as $ec)
                        <button wire:key="ec-{{ $ec->id }}-{{ $parcours_id }}" wire:click="selectEC({{ $ec->id }})"
                                wire:loading.attr="disabled" wire:target="selectEC({{ $ec->id }})"
                                class="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200 text-left group disabled:opacity-50 disabled:cursor-not-allowed">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <span class="text-blue-600 dark:text-blue-400 font-semibold text-sm">{{ $ec->abr }}</span>
                                </div>

                                <div class="flex-1">
                                    @php
                                        $nomEc = $ec->nom ?? '';
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
                                        <p class="text-sm text-gray-500 dark:text-gray-400">UE: {{ $ec->ue->nom }}</p>
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
                        </button>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $ecs->links(data: ['scrollTo' => false]) }}
                </div>

                {{-- Panel info filtrage --}}
                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">📊 Filtrage des ECs</h4>
                    <div class="text-sm text-blue-600 dark:text-blue-400 space-y-1">
                        <p><strong>Sélection:</strong> {{ $niveauSelected->abr }}{{ $parcoursSelected ? ' / ' . $parcoursSelected->abr : ' (tous parcours)' }}</p>
                        <p><strong>EC(s) trouvée(s):</strong> {{ $totalCount }}</p>
                        <p><strong>Dernière MAJ:</strong> {{ now()->format('H:i:s') }}</p>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        @if(!empty($search))
                            Aucun résultat pour « {{ $search }} »
                        @else
                            Aucune matière programmée pour cet examen
                        @endif
                    </p>
                </div>
            @endif
        </div>





    @elseif($step === 'setup')
        <!-- Configuration des manchettes -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Configuration de la saisie
                </h2>
                <button wire:click="backToStep('ec')" wire:key="back-to-ec"
                    class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    ← Retour
                </button>
            </div>
            
            <!-- Résumé de sélection -->
            <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Sélection actuelle:</h3>
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <div>
                        <p><span class="font-medium">Niveau:</span> {{ $niveauSelected->abr }} - {{ $niveauSelected->nom }}</p>
                        @if($parcoursSelected)
                            <p><span class="font-medium">Parcours:</span> {{ $parcoursSelected->abr }} - {{ $parcoursSelected->nom }}</p>
                        @endif
                        <p><span class="font-medium">Matière:</span> {{ $ecSelected->nom }} ({{ $ecSelected->abr }})</p>
                    </div>
                    <div>
                        <p><span class="font-medium">Code salle:</span> {{ $codeSalle }}</p>
                        <p><span class="font-medium">Total étudiants:</span> {{ $totalEtudiantsTheorique }}</p>
                        @if($progressCount > 0)
                            <p><span class="font-medium">Déjà saisi:</span> {{ $progressCount }} manchettes</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Configuration -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <!-- Nombre de manchettes présentes -->
                    <div>
                        <label for="totalManchettesPresentes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nombre d'étudiants présents <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model.live="totalManchettesPresentes" id="totalManchettesPresentes" 
                            min="1" max="{{ $totalEtudiantsTheorique }}"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Nombre d'étudiants présents à l'examen (max: {{ $totalEtudiantsTheorique }})
                        </p>
                        @error('totalManchettesPresentes') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Statistiques temps réel -->
                    @if($totalManchettesPresentes > 0 && $totalEtudiantsTheorique > 0)
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-3">
                                Calcul automatique
                            </h4>
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                        {{ $totalManchettesPresentes }}
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        Présents
                                    </div>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-red-600 dark:text-red-400">
                                        {{ $this->totalAbsents }}
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        Absents
                                    </div>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                        {{ $this->pourcentagePresence }}%
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        Présence
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Bouton de démarrage -->
                    <div class="pt-4">
                        <button wire:click="startSaisie" 
                            class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ !$totalManchettesPresentes || $totalManchettesPresentes > $totalEtudiantsTheorique ? 'disabled' : '' }}>
                            @if($totalManchettesPresentes > $totalEtudiantsTheorique)
                                Nombre trop élevé
                            @else
                                Commencer la saisie ({{ $totalManchettesPresentes }} manchettes)
                            @endif
                        </button>
                    </div>
                </div>

                <!-- Informations et statistiques -->
                <div class="space-y-6">
                    @if($progressCount > 0)
                        <div class="p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg">
                            <h4 class="text-sm font-medium text-green-800 dark:text-green-300 mb-2">
                                Manchettes déjà saisies
                            </h4>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ $progressCount }}
                            </p>
                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                pour cette matière dans cette session
                            </p>
                            @if($totalManchettesPresentes > 0)
                                <div class="mt-3 w-full bg-green-200 dark:bg-green-800 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" 
                                        style="width: {{ min(($progressCount / $totalManchettesPresentes) * 100, 100) }}%"></div>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>

    @elseif($step === 'saisie')
        <!-- Interface de saisie -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Colonne gauche - Formulaire -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Saisie des manchettes
                        </h3>
                        <button wire:click="backToStep('setup')" 
                            class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                            ← Configuration
                        </button>
                    </div>
                    
                    <!-- Progression -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Progression
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $progressCount }} / {{ $totalManchettesPresentes }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                style="width: {{ $totalManchettesPresentes > 0 ? min(($progressCount / $totalManchettesPresentes) * 100, 100) : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <!-- Prochain code d'anonymat -->
                    @if($progressCount < $totalManchettesPresentes)
                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a1.994 1.994 0 01-1.414.586H7a1 1 0 01-1-1V4a1 1 0 011-1z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
                                        Prochain code d'anonymat:
                                    </p>
                                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                        {{ $prochainCodeAnonymat }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg text-center">
                            <svg class="mx-auto h-8 w-8 text-green-500 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                Toutes les manchettes ont été saisies !
                            </p>
                        </div>
                    @endif
                    
                    @if($progressCount < $totalManchettesPresentes)
                        <!-- Formulaire de saisie -->
                        <div class="space-y-6">
                            <div>
                                <label for="matricule" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Matricule de l'étudiant <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                        wire:model.live.debounce.300ms="matricule" 
                                        wire:keydown.enter="sauvegarderManchette"
                                        id="matricule" 
                                        placeholder="Tapez le matricule puis Entrée..." 
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400">
                                    @if($matricule && strlen($matricule) >= 3)
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            @if($etudiantTrouve)
                                                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Message d'aide -->
                                <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                                    💡 Tapez le matricule puis appuyez sur <kbd class="px-1 py-0.5 bg-gray-200 dark:bg-gray-600 rounded text-xs">Entrée</kbd> pour enregistrer rapidement
                                </p>
                                
                                <!-- Informations étudiant -->
                                @if($etudiantTrouve)
                                    <div class="mt-3 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-md">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                            </svg>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                                    {{ $etudiantTrouve->nom }} {{ $etudiantTrouve->prenom }}
                                                </p>
                                                <p class="text-xs text-green-600 dark:text-green-400">
                                                    Matricule: {{ $etudiantTrouve->matricule }} → Code: {{ $prochainCodeAnonymat }}
                                                </p>
                                            </div>
                                            <div class="text-xs text-green-600 dark:text-green-400 font-medium">
                                                Prêt ! ↵
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex justify-between">
                                <button type="button" wire:click="resetSaisieForm" 
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    Effacer
                                </button>
                                <button type="button" 
                                    wire:click="sauvegarderManchette"
                                    wire:loading.attr="disabled"
                                    wire:target="sauvegarderManchette"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200
                                           {{ $etudiantTrouve ? 'ring-2 ring-blue-300 shadow-lg' : '' }}"
                                    {{ !$etudiantTrouve ? 'disabled' : '' }}>
                                    
                                    <!-- Loading spinner -->
                                    <svg wire:loading wire:target="sauvegarderManchette" 
                                        class="inline h-4 w-4 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    
                                    <span wire:loading.remove wire:target="sauvegarderManchette">
                                        @if($etudiantTrouve)
                                            ✓ Enregistrer → {{ $prochainCodeAnonymat }}
                                        @else
                                            Enregistrer la manchette
                                        @endif
                                    </span>
                                    <span wire:loading wire:target="sauvegarderManchette">
                                        Enregistrement...
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Colonne droite - Statistiques -->
            <div class="space-y-6">
                <!-- Compteurs -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Avancement
                    </h3>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $progressCount }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            sur {{ $totalManchettesPresentes }} manchettes
                        </div>
                        <div class="mt-2 text-lg font-semibold {{ $progressCount >= $totalManchettesPresentes ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
                            {{ $totalManchettesPresentes > 0 ? round(($progressCount / $totalManchettesPresentes) * 100, 1) : 0 }}%
                        </div>
                    </div>
                    
                    <!-- Statistiques détaillées -->
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-3 gap-2 text-center text-xs">
                            <div>
                                <div class="font-semibold text-blue-600 dark:text-blue-400">
                                    {{ $totalEtudiantsTheorique }}
                                </div>
                                <div class="text-gray-500 dark:text-gray-400">
                                    Inscrits
                                </div>
                            </div>
                            <div>
                                <div class="font-semibold text-green-600 dark:text-green-400">
                                    {{ $totalManchettesPresentes }}
                                </div>
                                <div class="text-gray-500 dark:text-gray-400">
                                    Présents
                                </div>
                            </div>
                            <div>
                                <div class="font-semibold text-red-600 dark:text-red-400">
                                    {{ $this->totalAbsents }}
                                </div>
                                <div class="text-gray-500 dark:text-gray-400">
                                    Absents
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dernières saisies -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Dernières saisies
                    </h3>
                    
                    @if(!empty($manchettesSaisies))
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            @foreach(array_slice($manchettesSaisies, 0, 8) as $manchette)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $manchette['code_anonymat']['code_complet'] ?? '' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ $manchette['etudiant']['nom'] ?? '' }} {{ $manchette['etudiant']['prenom'] ?? '' }}
                                        </p>
                                    </div>
                                    <button wire:click="supprimerManchette({{ $manchette['id'] }})" 
                                        class="ml-2 p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        onclick="return confirm('Supprimer cette manchette ?')">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Aucune manchette saisie
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Auto-focus sur le champ matricule en mode saisie
    document.addEventListener('livewire:navigated', () => {
        const matriculeInput = document.getElementById('matricule');
        if (matriculeInput) {
            matriculeInput.focus();
        }
    });

    // Auto-clear messages
    document.addEventListener('livewire:init', () => {
        Livewire.on('clearMessage', (event) => {
            setTimeout(() => {
                @this.clearMessage();
            }, event[0]?.delay || 3000);
        });
    });
</script>
@endpush