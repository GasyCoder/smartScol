    <!-- Filtres de sélection avec animation et interactivité -->
    <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md dark:bg-gray-800 dark:border-gray-700">
        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="flex items-center gap-2 text-base font-medium text-gray-900 dark:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                    </svg>
                    Filtres de sélection
                </h3>
                <div class="flex items-center space-x-3">
                    @if($examen_id && $ec_id)
                    <button
                        wire:click="openManchetteModal"
                        class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded bg-primary-600 text-white hover:bg-primary-700 focus:outline-none dark:bg-primary-700 dark:hover:bg-primary-800"
                    >
                        <em class="mr-1 text-sm icon ni ni-plus-circle"></em>
                        Ajouter une manchette
                    </button>
                    @endif
                    <a href="{{ route('manchettes.index') }}"
                    class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-cyan-300 bg-cyan text-cyan-700 hover:bg-cyan-50 focus:outline-none dark:bg-cyan-800 dark:border-cyan-700 dark:text-cyan-200 dark:hover:bg-cyan-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <em class="mr-1 text-sm icon ni ni-reload-alt"></em>
                        Actualiser
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <!-- Niveau avec icône et animation -->
                <div class="transition-all duration-300 transform hover:scale-[1.02]">
                    <label for="niveau_id" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                        Niveau
                    </label>
                    <div class="relative mt-1 rounded-md">
                        <select
                            id="niveau_id"
                            wire:model.live="niveau_id"
                            class="block w-full py-2 pl-3 pr-10 text-base transition-colors duration-200 border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Sélectionner un niveau</option>
                            @foreach($niveaux as $niveau)
                                <option value="{{ $niveau->id }}">{{ $niveau->abr }} - {{ $niveau->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Parcours avec icône et animation -->
                <div class="transition-all duration-300 transform hover:scale-[1.02]">
                    <label for="parcours_id" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        Parcours
                    </label>
                    <div class="relative mt-1 rounded-md">
                        <select
                            id="parcours_id"
                            wire:model.live="parcours_id"
                            class="block w-full py-2 pl-3 pr-10 text-base transition-colors duration-200 border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ count($parcours) ? '' : 'disabled' }}>
                            <option value="">Sélectionner un parcours</option>
                            @foreach($parcours as $parcour)
                                <option value="{{ $parcour->id }}">{{ $parcour->abr }} - {{ $parcour->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(!count($parcours) && $niveau_id)
                        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Aucun parcours trouvé pour ce niveau</p>
                    @endif
                </div>
                <!-- Matière/EC avec icône et animation -->
                <div class="col-span-6 sm:col-span-2 transition-allmin-w-xl duration-300 transform hover:scale-[1.02]">
                    <div class="relative mb-5 last:mb-0">
                        <label for="ec_id" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Matière
                        </label>
                        <div class="relative mt-1 rounded-md">
                            <select
                                wire:model.live="ec_id"
                                id="default-4-02"
                                class="block w-full py-2 pl-3 pr-10 text-base transition-colors duration-200 border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ count($parcours) ? '' : 'disabled' }}>
                                data-search="true" {{ count($ecs) ? '' : 'disabled' }}>
                                <option value="">Sélectionner une matière</option>
                                <option value="all">Toutes les matières</option>
                                @foreach($ecs as $ec)
                                    <option value="{{ $ec->id }}">
                                        {{ $ec->nom }}
                                        @if(isset($ec->manchettes_count))
                                            ({{ $ec->manchettes_count }}/{{ $totalEtudiantsCount ?? '?' }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if(!count($ecs) && $salle_id)
                            <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Aucune matière trouvée pour cette salle</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
