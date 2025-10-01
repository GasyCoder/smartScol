<!-- Session active -->
@if($sessionActive)
    <div class="mb-6 overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="p-4 border-b
            @if($sessionActive->type === 'Normale')
                border-blue-100 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800
            @else
                border-blue-100 bg-orange-50 dark:bg-blue-900/20 dark:border-blue-800
            @endif">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-medium
                        @if($sessionActive->type === 'Normale')
                            text-blue-800 dark:text-blue-200
                        @else
                            text-blue-800 dark:text-blue-200
                        @endif">
                        Session {{ $sessionActive->type }}
                        @if($sessionActive->type === 'Rattrapage')
                            <svg class="inline w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        @endif
                    </h3>
                    <p class="mt-1 text-sm
                        @if($sessionActive->type === 'Normale')
                            text-blue-600 dark:text-blue-300
                        @else
                            text-blue-600 dark:text-blue-300
                        @endif">
                        Année Universitaire {{ $sessionActive->anneeUniversitaire->date_start->format('Y') }}/{{ $sessionActive->anneeUniversitaire->date_end->format('Y') }}
                        @if($sessionActive->type === 'Rattrapage')
                            - Étudiants éligibles uniquement
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-medium text-gray-800 dark:text-gray-200">
                    @if($sessionActive->type === 'Rattrapage')
                        Choisir l'examen de rattrapage :
                    @else
                        Choisir options :
                    @endif
                </h3>
                @if($niveau_id || $parcours_id)
                    <button
                        wire:click="reinitialiserFiltres"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
                    >
                        <em class="mr-1 icon ni ni-reload-alt"></em>
                        Réinitialiser les filtres
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <!-- Niveau -->
                <div>
                    <label for="niveau_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Niveau d'études</label>
                    <select id="niveau_id" wire:model.live="niveau_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Sélectionner un niveau</option>
                        @foreach($niveaux as $niveau)
                            <option value="{{ $niveau->id }}">{{ $niveau->abr }} - {{ $niveau->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Parcours -->
                <div>
                    <label for="parcours_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parcours</label>
                    <select id="parcours_id" wire:model.live="parcours_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" {{ count($parcours) ? '' : 'disabled' }}>
                        <option value="">Sélectionner un parcours</option>
                        @foreach($parcours as $parcour)
                            <option value="{{ $parcour->id }}">{{ $parcour->abr }} - {{ $parcour->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Informations sur l'examen sélectionné -->
            @if($examen)
            @elseif($niveau_id && $parcours_id)
                <div class="p-3 mt-4 text-sm border border-red-100 rounded-md bg-red-50 dark:bg-red-900/20 dark:border-red-800">
                    <div class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-red-700 dark:text-red-300">
                                <span class="font-medium">Attention :</span>
                                @if($sessionActive->type === 'Rattrapage')
                                    Aucun examen avec des étudiants éligibles au rattrapage n'est trouvé pour ce niveau/parcours.
                                    Vérifiez que les résultats de la session normale sont publiés et qu'il y a des étudiants en échec.
                                @else
                                    Aucun examen n'est configuré pour ce niveau/parcours dans la session normale.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@else
    <!-- Pas de session active -->
    <div class="p-4 mb-6 border border-red-200 rounded-md bg-red-50 dark:bg-red-900/30 dark:border-red-800">
        <div class="flex">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-400 dark:text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Aucune session active</h3>
                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                    <p>Veuillez configurer une session active dans les paramètres du système pour pouvoir traiter les résultats d'examen.</p>
                </div>
            </div>
        </div>
    </div>
@endif
