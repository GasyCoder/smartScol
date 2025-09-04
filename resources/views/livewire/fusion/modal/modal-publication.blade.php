{{-- Modal Publication spécialisée --}}
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingPublication"
     x-cloak
     role="dialog"
     aria-modal="true">
    <div class="relative w-full max-w-lg p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        @php
            $resultatsEnAttente = \App\Models\ResultatFinal::where('examen_id', $examen_id)
                ->where('session_exam_id', $sessionActive->id)
                ->where('statut', \App\Models\ResultatFinal::STATUT_EN_ATTENTE)
                ->exists();
            
            $estReactivation = $resultatsEnAttente;
            $isConcours = $estPACES; // Simplification basée sur PACES
            $isRattrapage = $sessionActive && $sessionActive->type === 'Rattrapage';
        @endphp

        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ $estReactivation ? 'Confirmer la republication des résultats' : 'Confirmer la publication des résultats' }}
        </h3>
        
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @if($estReactivation)
                    {{ $isConcours 
                        ? 'Voulez-vous republier et classer les résultats ? Ils seront immédiatement accessibles aux étudiants après recalcul du classement.'
                        : 'Voulez-vous republier les résultats ? Ils seront immédiatement accessibles aux étudiants après recalcul des décisions.' }}
                @else
                    {{ $isConcours 
                        ? 'Voulez-vous classer et publier les résultats ? Ils seront immédiatement accessibles aux étudiants.'
                        : 'Voulez-vous publier les résultats ? Ils seront immédiatement accessibles aux étudiants.' }}
                @endif
            </p>

            {{-- Informations contextuelles --}}
            @if($sessionActive)
                <div class="p-3 mt-3 border rounded-md {{ $estReactivation ? 'border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700' : 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700' }}">
                    <p class="text-sm {{ $estReactivation ? 'text-blue-800 dark:text-blue-200' : 'text-green-800 dark:text-green-200' }}">
                        <strong>{{ $estReactivation ? 'Republication' : 'Publication' }} :</strong> 
                        {{ $examen->niveau->nom ?? 'Niveau inconnu' }} - Session {{ $sessionActive->type }}
                    </p>
                    
                    <ul class="mt-2 text-xs {{ $estReactivation ? 'text-blue-700 dark:text-blue-300' : 'text-green-700 dark:text-green-300' }} list-disc list-inside">
                        @if($estReactivation)
                            <li>Recalcul automatique des moyennes et décisions</li>
                            <li>Mise à jour des statuts depuis "en_attente" vers "publié"</li>
                            <li>Conservation de l'historique des modifications</li>
                        @else
                            <li>Transfert vers la table `resultats_finaux`</li>
                            <li>Calcul des décisions (admis, rattrapage, exclus)</li>
                        @endif
                        
                        @if($isConcours)
                            <li>{{ $estReactivation ? 'Mise à jour du' : 'Établissement du' }} classement</li>
                        @endif
                        
                        @if($isRattrapage)
                            <li>Application automatique des meilleures notes entre sessions</li>
                        @endif
                        
                        <li>Génération des hash de vérification</li>
                    </ul>
                </div>
            @endif

            {{-- Avertissement --}}
            @if($estReactivation)
                <div class="p-3 mt-3 border rounded-md border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700">
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        <strong>Republication après réactivation :</strong> Les résultats précédemment annulés seront republiés avec recalcul des décisions.
                    </p>
                </div>
            @else
                <div class="p-3 mt-3 border border-red-200 rounded-md bg-red-50 dark:bg-red-900/20 dark:border-red-700">
                    <p class="text-sm text-red-800 dark:text-red-200">
                        <strong>Attention :</strong> Cette action est irréversible sans annulation complète.
                    </p>
                </div>
            @endif
        </div>
        
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingPublication', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            
            <button
                wire:click="publierResultats"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 {{ $estReactivation ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600' : ($isConcours ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-600') }}">
                @if($estReactivation)
                    {{ $isConcours ? 'Republier et classer' : 'Republier les résultats' }}
                @else
                    {{ $isConcours ? 'Classer et publier' : 'Publier' }}
                @endif
                
                <span wire:loading wire:target="publierResultats" class="ml-2 animate-spin">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </span>
            </button>
        </div>
    </div>
</div>