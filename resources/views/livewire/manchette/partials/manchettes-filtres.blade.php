<!-- Barre de filtres et contexte actuel - Design amélioré -->
<div id="id" class="mb-6 space-y-4">
    <!-- Filtres actuels / Fil d'Ariane avec badges interactifs -->
    @if($niveau_id || $parcours_id || $salle_id || $ec_id)
    <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-medium text-gray-700 dark:text-white">Filtres actifs</h3>
            <button wire:click="resetFiltres" class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 transition-colors bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <em class="mr-1 text-sm icon ni ni-reload"></em>
                Réinitialiser
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2 p-4">
            @if($niveau_id)
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-blue-800 transition-all duration-200 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                    {{ $niveaux->where('id', $niveau_id)->first()->nom ?? '' }}
                    <button wire:click="clearFilter('niveau_id')" class="ml-1 text-blue-500 transition-opacity opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif

            @if($parcours_id)
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-indigo-800 transition-all duration-200 bg-indigo-100 rounded-full dark:bg-indigo-900 dark:text-indigo-200 hover:bg-indigo-200 dark:hover:bg-indigo-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    {{ $parcours->where('id', $parcours_id)->first()->nom ?? '' }}
                    <button wire:click="clearFilter('parcours_id')" class="ml-1 text-indigo-500 transition-opacity opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif

            @if($salle_id)
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-purple-800 transition-all duration-200 bg-purple-100 rounded-full dark:bg-purple-900 dark:text-purple-200 hover:bg-purple-200 dark:hover:bg-purple-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    @foreach($salles as $salle)
                        @if($salle->id == $salle_id)
                            {{ $salle->nom }}
                            <span class="px-1.5 py-0.5 ml-1 text-xxs bg-purple-200 rounded text-purple-800 dark:bg-purple-800 dark:text-purple-200">{{ $salle->code_base ?? '' }}</span>
                        @endif
                    @endforeach
                    <button wire:click="clearFilter('salle_id')" class="ml-1 text-purple-500 transition-opacity opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif

            @if($examen_id)
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-green-800 transition-all duration-200 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ App\Models\Examen::find($examen_id)->session->type ?? 'Inconnu' }}
                    <button wire:click="clearFilter('examen_id')" class="ml-1 text-green-500 transition-opacity opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif

            @if($ec_id && $ec_id !== 'all')
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium transition-all duration-200 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 hover:bg-amber-200 dark:hover:bg-amber-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    @php
                        $selectedEc = collect($ecs)->firstWhere('id', $ec_id);
                    @endphp
                    {{ $selectedEc->nom ?? '' }}
                    <button wire:click="clearFilter('ec_id')" class="ml-1 transition-opacity opacity-0 text-amber-500 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif

            @if($ec_id === 'all')
            <div class="relative group">
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium transition-all duration-200 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 hover:bg-amber-200 dark:hover:bg-amber-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Toutes les matières
                    <button wire:click="clearFilter('ec_id')" class="ml-1 transition-opacity opacity-0 text-amber-500 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </span>
            </div>
            @endif
        </div>

        <!-- Barre de progression et statistiques pour manchettes -->
        @if($ec_id && $ec_id !== 'all' && $totalEtudiantsCount > 0)
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2">
                    <div class="text-xs font-medium text-gray-700 dark:text-gray-300">Progression</div>
                    <div class="flex items-center gap-2">
                        <div class="w-32 h-2 overflow-hidden bg-gray-200 rounded-full dark:bg-gray-600">
                            <div class="h-full rounded-full transition-all duration-500 ease-out
                                @if(($totalManchettesCount / $totalEtudiantsExpected) * 100 >= 80)
                                    bg-green-500 dark:bg-green-400
                                @elseif(($totalManchettesCount / $totalEtudiantsExpected) * 100 >= 50)
                                    bg-amber-500 dark:bg-amber-400
                                @else
                                    bg-red-500 dark:bg-red-400
                                @endif"
                                style="width: {{ $totalEtudiantsExpected > 0 ? round(($totalManchettesCount / $totalEtudiantsExpected) * 100) : 0 }}%">
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ $totalManchettesCount }}/{{ $totalEtudiantsExpected }}
                            ({{ $totalEtudiantsExpected > 0 ? round(($totalManchettesCount / $totalEtudiantsExpected) * 100) : 0 }}%)
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1 text-xs text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $totalManchettesCount }} manchettes saisies</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $totalEtudiantsExpected - $totalManchettesCount }} en attente</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                        </svg>
                        <span>{{ $userManchettesCount }} par vous</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
    @include('livewire.manchette.partials.selection-filtres')

</div>
