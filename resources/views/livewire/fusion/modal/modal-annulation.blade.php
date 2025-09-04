{{-- Modal Annulation spécialisée --}}
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingAnnulation"
     x-cloak
     role="dialog"
     aria-modal="true">
    <div class="relative w-full max-w-lg p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer l'annulation des résultats
        </h3>
        
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous annuler les résultats publiés ? Cette action les rendra indisponibles aux étudiants tout en préservant les données pour une éventuelle réactivation.
            </p>

            {{-- Informations contextuelles --}}
            @if($sessionActive)
                <div class="p-3 mt-3 border border-orange-200 rounded-md bg-orange-50 dark:bg-orange-900/20 dark:border-orange-700">
                    <p class="text-sm text-orange-800 dark:text-orange-200">
                        <strong>Annulation pour :</strong> {{ $examen->niveau->nom ?? 'Niveau inconnu' }} - Session {{ $sessionActive->type }}
                    </p>
                    <ul class="mt-2 text-xs text-orange-700 list-disc list-inside dark:text-orange-300">
                        <li>Masquage immédiat des résultats pour les étudiants</li>
                        <li>Préservation de toutes les données dans la base</li>
                        <li>Possibilité de réactivation ultérieure</li>
                        <li>Historique des actions conservé pour audit</li>
                    </ul>
                </div>
            @endif

            {{-- Conséquences --}}
            <div class="p-3 mt-3 border border-red-200 rounded-md bg-red-50 dark:bg-red-900/20 dark:border-red-700">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <strong>Conséquences de l'annulation :</strong>
                </p>
                <ul class="mt-2 text-xs text-red-700 list-disc list-inside dark:text-red-300">
                    <li>Les étudiants ne pourront plus consulter leurs résultats</li>
                    <li>Les exports seront temporairement indisponibles</li>
                    <li>Une notification peut être envoyée aux parties concernées</li>
                    <li>L'action sera tracée dans l'historique du système</li>
                </ul>
            </div>

            {{-- Saisie du motif --}}
            <div class="mt-4">
                <label for="motif-annulation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Motif d'annulation (optionnel)
                </label>
                <textarea
                    id="motif-annulation"
                    wire:model="motifAnnulation"
                    rows="3"
                    class="block w-full px-3 py-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                    placeholder="Indiquez la raison de cette annulation (erreur de calcul, contestation, etc.)"></textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Ce motif sera conservé dans l'historique pour traçabilité.
                </p>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingAnnulation', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            
            <button
                wire:click="annulerResultats"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700 dark:hover:bg-red-600 disabled:opacity-50">
                Confirmer l'annulation
                <span wire:loading wire:target="annulerResultats" class="ml-2 animate-spin">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </span>
            </button>
        </div>
    </div>
</div>