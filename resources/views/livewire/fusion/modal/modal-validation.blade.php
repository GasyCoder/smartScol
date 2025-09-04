{{-- Modal Validation spécialisée --}}
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingValidation"
     x-cloak
     role="dialog"
     aria-modal="true">
    <div class="relative w-full max-w-lg p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la validation des résultats
        </h3>
        
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous valider les résultats fusionnés ? Cette action les marquera comme vérifiés et prêts pour la publication ou le transfert.
            </p>

            {{-- Informations contextuelles --}}
            @if($sessionActive)
                <div class="p-3 mt-3 border border-purple-200 rounded-md bg-purple-50 dark:bg-purple-900/20 dark:border-purple-700">
                    <p class="text-sm text-purple-800 dark:text-purple-200">
                        <strong>Étape 3 :</strong> Validation pour {{ $examen->niveau->nom ?? 'Niveau inconnu' }} - Session {{ $sessionActive->type }}
                    </p>
                    <ul class="mt-2 text-xs text-purple-700 list-disc list-inside dark:text-purple-300">
                        <li>Vérification des notes et des associations</li>
                        <li>Calcul des moyennes par étudiant</li>
                        @if($sessionActive->type === 'Rattrapage')
                            <li>Application des meilleures notes entre sessions</li>
                        @endif
                    </ul>
                </div>
            @endif

            <div class="p-3 mt-3 border rounded-md border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700">
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    <strong>Important :</strong> Vérifiez tous les résultats avant de valider.
                </p>
            </div>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingValidation', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            
            <button
                wire:click="validerResultats"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:bg-purple-700 dark:hover:bg-purple-600 disabled:opacity-50">
                Valider
                <span wire:loading wire:target="validerResultats" class="ml-2 animate-spin">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </span>
            </button>
        </div>
    </div>
</div>