<!-- Confirmation Modal for Fusion -->
@if($confirmingFusion)
    <div class="fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300 bg-black bg-opacity-60">
        <div class="w-full max-w-md p-6 transition-all duration-300 transform scale-100 bg-white shadow-2xl dark:bg-gray-800 rounded-xl">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                @if($statut === 'verification')
                    Confirmer la première fusion
                @elseif($statut === 'fusion')
                    @if($etapeFusion == 1)
                        Confirmer la deuxième fusion
                    @elseif($etapeFusion == 2)
                        Confirmer la fusion finale
                    @else
                        Confirmer la refusion
                    @endif
                @else
                    Confirmer la fusion
                @endif
            </h4>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                @if($statut === 'verification')
                    Êtes-vous sûr de vouloir démarrer la fusion des données ? Cette action va associer les manchettes et les notes.
                @elseif($statut === 'fusion')
                    @if($etapeFusion == 1)
                        Êtes-vous sûr de vouloir procéder à la deuxième fusion ? Cette action calculera les moyennes par UE.
                    @elseif($etapeFusion == 2)
                        Êtes-vous sûr de vouloir procéder à la fusion finale ? Cette action consolidera les résultats.
                    @else
                        Êtes-vous sûr de vouloir refusionner les données ? Cela peut modifier les résultats existants.
                    @endif
                @else
                    Êtes-vous sûr de vouloir fusionner les données ? Cela peut écraser les résultats existants.
                @endif
            </p>
            <div class="flex justify-end mt-6 space-x-3">
                <button
                    wire:click="$set('confirmingFusion', false)"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-gray-500"
                >
                    Annuler
                </button>
                <button
                    wire:click="fusionner({{ $statut === 'fusion' && $etapeFusion > 2 ? 'true' : 'false' }})"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-yellow-500 border border-transparent rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Confirmer
                </button>
            </div>
        </div>
    </div>
@endif

<!-- Confirmation Modal for Reset -->
@if($confirmingReset)
    <div class="fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300 bg-black bg-opacity-60">
        <div class="w-full max-w-md p-6 transition-all duration-300 transform scale-100 bg-white shadow-2xl dark:bg-gray-800 rounded-xl">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Confirmer la réinitialisation</h4>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Êtes-vous sûr de vouloir réinitialiser la fusion ? Cette action supprimera tous les résultats provisoires et validés.
            </p>
            <div class="flex justify-end mt-6 space-x-3">
                <button
                    wire:click="$set('confirmingReset', false)"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-gray-500"
                >
                    Annuler
                </button>
                <button
                    wire:click="resterFusion"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-red-600 border border-transparent rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700 dark:hover:bg-red-800 dark:focus:ring-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Confirmer
                </button>
            </div>
        </div>
    </div>
@endif
