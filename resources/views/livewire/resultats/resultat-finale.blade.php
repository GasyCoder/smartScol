<div>
    <div class="space-y-4">
        @if(isset($operationEnCours))
            <div class="p-4 border border-blue-100 rounded-md bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="ml-3">
                        <p class="text-blue-700 dark:text-blue-300">
                            <span class="font-medium">Opération en cours:</span> {{ $operationEnCours->type }}
                        </p>
                        <p class="mt-1 text-sm text-blue-600 dark:text-blue-400">
                            Début: {{ $operationEnCours->started_at ? $operationEnCours->started_at->format('d/m/Y H:i:s') : 'Non démarré' }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Étape actuelle -->
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">État actuel</h3>
            <div class="mt-2">
                @if($statut == 'fusion' || $statut == 'fusion_provisoire')
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100">
                        <svg class="-ml-1 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Résultats provisoires générés
                    </span>
                @elseif($statut == 'validation')
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">
                        <svg class="-ml-1 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Résultats validés
                    </span>
                @elseif($statut == 'publie' || $statut == 'publication')
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100">
                        <svg class="-ml-1 mr-1.5 h-2 w-2 text-blue-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Résultats publiés
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100">
                        <svg class="-ml-1 mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Aucun résultat généré
                    </span>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap gap-4">
            @if($statut == 'fusion' || $statut == 'fusion_provisoire')
                <button wire:click="validerResultats"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70"
                    class="px-4 py-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span wire:loading.remove wire:target="validerResultats">Valider les résultats</span>
                    <span wire:loading wire:target="validerResultats">
                        <svg class="inline-block w-4 h-4 mr-2 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Validation en cours...
                    </span>
                </button>

                <button wire:click="resetFusion"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70"
                    class="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <span wire:loading.remove wire:target="resetFusion">Réinitialiser la fusion</span>
                    <span wire:loading wire:target="resetFusion">
                        <svg class="inline-block w-4 h-4 mr-2 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Réinitialisation en cours...
                    </span>
                </button>
            @endif

            @if($statut == 'validation')
                <button wire:click="publierResultats"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70"
                    class="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <span wire:loading.remove wire:target="publierResultats">Publier les résultats</span>
                    <span wire:loading wire:target="publierResultats">
                        <svg class="inline-block w-4 h-4 mr-2 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Publication en cours...
                    </span>
                </button>

                <button wire:click="resetFusion"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70"
                    class="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <span wire:loading.remove wire:target="resetFusion">Réinitialiser</span>
                    <span wire:loading wire:target="resetFusion">
                        <svg class="inline-block w-4 h-4 mr-2 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Réinitialisation en cours...
                    </span>
                </button>
            @endif

            @if($statut == 'publie' || $statut == 'publication')
                <a href="{{ route('resultats.provisoires', ['examen_id' => $examen_id]) }}"
                   class="inline-flex items-center px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Voir les résultats
                </a>

                <button wire:click="exporterResultats"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70"
                    class="px-4 py-2 text-white bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Exporter (Excel)
                    </span>
                </button>
            @endif
        </div>

        @if($estPACES)
            <div class="p-4 border border-yellow-200 rounded-md bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-800">
                <div class="flex">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            <span class="font-medium">Note:</span> PACES 1ère année est considérée comme un concours sans délibération.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
