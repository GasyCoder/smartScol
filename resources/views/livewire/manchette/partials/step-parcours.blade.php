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
                        <button wire:click="selectParcours({{ $parcour->id }})" wire:key="parcour-{{ $parcour->id }}"
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