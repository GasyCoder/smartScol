<!-- Bouton de présence à ajouter dans vos filtres - MÊME STYLE QUE VOS BOUTONS -->
@if($examen_id && $salle_id && $ec_id && $ec_id !== 'all')
    <div class="flex items-center justify-between p-4 mb-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
        <!-- Section gauche : Bouton de présence -->
        <div class="flex items-center space-x-3">
            <button wire:click="openPresenceModal" 
                    type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2
                    {{ $presenceEnregistree 
                        ? 'text-white bg-green-600 hover:bg-green-700 focus:ring-green-500' 
                        : 'text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' 
                    }}">
                
                @if($presenceEnregistree)
                    <em class="mr-2 ni ni-check-circle"></em>
                    Présence enregistrée
                @else
                    <em class="mr-2 ni ni-users"></em>
                    Saisir présence
                @endif
            </button>

            <!-- Badges de présence - MÊME STYLE QUE VOS BADGES -->
            @if($presenceEnregistree && $presenceData)
                <div class="flex items-center space-x-2 text-xs">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        <em class="mr-1 ni ni-user-check"></em>
                        {{ $presenceData->etudiants_presents }}P
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        <em class="mr-1 ni ni-user-cross"></em>
                        {{ $presenceData->etudiants_absents }}A
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $presenceData->taux_presence >= 75 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                           ($presenceData->taux_presence >= 50 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                        {{ $presenceData->taux_presence }}%
                    </span>
                </div>
            @endif
        </div>

        <!-- Section droite : Bouton manchettes ou statut -->
        <div class="flex items-center space-x-2">
            @if($presenceEnregistree)
                <button wire:click="openManchetteModal" 
                        type="button"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <em class="mr-2 ni ni-save"></em>
                    Saisir manchettes
                </button>
            @else
                <button type="button" disabled
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-200 border border-transparent rounded-md shadow-sm cursor-not-allowed">
                    <em class="mr-2 ni ni-save"></em>
                    Saisir manchettes
                </button>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    (Présence requise)
                </span>
            @endif
        </div>
    </div>

    <!-- Message d'état si présence non enregistrée - MÊME STYLE QUE VOS ALERTES -->
    @if(!$presenceEnregistree)
    <div class="mb-4">
        <div class="flex items-center p-4 border-l-4 border-amber-400 bg-amber-50 dark:bg-amber-900/20">
            <div class="flex">
                <div class="flex-shrink-0">
                    <em class="text-amber-400 ni ni-info"></em>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        <strong>Étape requise :</strong> Veuillez d'abord enregistrer les données de présence avant de pouvoir saisir les manchettes.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
@endif