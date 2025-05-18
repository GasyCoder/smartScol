{{-- Modal de vérification de cohérence --}}
@if($showCoherenceModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6 dark:bg-gray-800">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                        Rapport de cohérence des données
                    </h3>
                    <button type="button" class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:hover:text-gray-300" wire:click="$set('showCoherenceModal', false)">
                        <span class="sr-only">Fermer</span>
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Matière</th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-200">Manchettes</th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-200">Copies</th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-200">Étudiants sans manchette</th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-200">État</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @foreach($rapportCoherence as $rapport)
                                <tr>
                                    <td class="px-3 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $rapport['ec_nom'] }}</td>
                                    <td class="px-3 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">{{ $rapport['manchettes_count'] }} / {{ $rapport['total_etudiants'] }}</td>
                                    <td class="px-3 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">{{ $rapport['copies_count'] }}</td>
                                    <td class="px-3 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">{{ $rapport['etudiants_sans_manchette'] }}</td>
                                    <td class="px-3 py-4 text-sm text-center text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        @if($rapport['complet'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Complet
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                                <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Incomplet
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Détails des problèmes si incomplet --}}
                    @php
                        $hasIncomplete = collect($rapportCoherence)->contains(function ($rapport) {
                            return !$rapport['complet'];
                        });
                    @endphp

                    @if($hasIncomplete)
                    <div class="p-4 mt-4 rounded-md bg-yellow-50 dark:bg-yellow-900/30">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Attention</h3>
                                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                    <p>
                                        Certaines matières présentent des incohérences. Il est recommandé de vérifier les listes de manchettes et de copies pour ces matières avant de procéder à la fusion.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="mt-5 sm:mt-6">
                <button
                    wire:click="$set('showCoherenceModal', false)"
                    type="button"
                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm dark:bg-indigo-500 dark:hover:bg-indigo-600"
                >
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal de confirmation de fusion --}}
@if($confirmingFusion)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 dark:bg-gray-800">
            <div>
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 rounded-full dark:bg-yellow-900">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                        Confirmer la fusion
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Êtes-vous sûr de vouloir fusionner les copies et les manchettes pour cet examen ? Cette action va générer ou mettre à jour les résultats provisoires.
                        </p>
                        <div class="p-3 mt-4 text-left rounded-md bg-blue-50 dark:bg-blue-900/30">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                <span class="font-semibold">Note :</span> Une fois la fusion effectuée, vous devrez imprimer les résultats pour vérification avec les copies et manchettes physiques.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button
                    wire:click="fusionner"
                    type="button"
                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:col-start-2 sm:text-sm dark:bg-green-500 dark:hover:bg-green-600"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="fusionner">Confirmer</span>
                    <span wire:loading wire:target="fusionner">Traitement...</span>
                </button>
                <button
                    wire:click="$set('confirmingFusion', false)"
                    type="button"
                    class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                >
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal de résultats de fusion --}}
@if($showFusionResultModal && $resultatFusion)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6 dark:bg-gray-800">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                        Résultats de la fusion
                    </h3>
                    <button type="button" class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:hover:text-gray-300" wire:click="$set('showFusionResultModal', false)">
                        <span class="sr-only">Fermer</span>
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4">
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div class="p-4 bg-blue-100 rounded-lg dark:bg-blue-800">
                            <div class="text-sm font-medium text-blue-800 dark:text-blue-200">Manchettes</div>
                            <div class="mt-1 text-2xl font-bold text-blue-900 dark:text-white">{{ $resultatFusion['statistiques']['total_manchettes'] }}</div>
                        </div>
                        <div class="p-4 bg-green-100 rounded-lg dark:bg-green-800">
                            <div class="text-sm font-medium text-green-800 dark:text-green-200">Copies</div>
                            <div class="mt-1 text-2xl font-bold text-green-900 dark:text-white">{{ $resultatFusion['statistiques']['total_copies'] }}</div>
                        </div>
                        <div class="p-4 bg-purple-100 rounded-lg dark:bg-purple-800">
                            <div class="text-sm font-medium text-purple-800 dark:text-purple-200">Résultats générés</div>
                            <div class="mt-1 text-2xl font-bold text-purple-900 dark:text-white">{{ $resultatFusion['statistiques']['resultats_generes'] }}</div>
                        </div>
                        <div class="p-4 rounded-lg {{ count($resultatFusion['erreurs']) > 0 ? 'bg-red-100 dark:bg-red-800' : 'bg-green-100 dark:bg-green-800' }}">
                            <div class="font-medium {{ count($resultatFusion['erreurs']) > 0 ? 'text-red-800 dark:text-red-200' : 'text-green-800 dark:text-green-200' }} text-sm">Erreurs</div>
                            <div class="mt-1 font-bold text-2xl {{ count($resultatFusion['erreurs']) > 0 ? 'text-red-900 dark:text-white' : 'text-green-900 dark:text-white' }}">{{ count($resultatFusion['erreurs']) }}</div>
                        </div>
                    </div>

                    @if(count($resultatFusion['erreurs']) > 0)
                    <div class="mt-6">
                        <h4 class="flex items-center text-base font-medium text-red-700 dark:text-red-400">
                            <svg class="w-5 h-5 mr-2 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Erreurs détectées
                        </h4>
                        <div class="mt-2 overflow-y-auto border border-red-200 rounded-lg max-h-60 dark:border-red-700">
                            <ul class="divide-y divide-red-200 dark:divide-red-700">
                                @foreach($resultatFusion['erreurs'] as $erreur)
                                <li class="p-3 bg-red-50 dark:bg-red-900/30">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">{{ $erreur['message'] }}</h3>
                                            <div class="mt-1 text-xs text-red-700 dark:text-red-300">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200">
                                                    {{ $erreur['type'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="mt-4">
                            <button
                                wire:click="afficherResolutionErreurs"
                                type="button"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-500 dark:hover:bg-red-600"
                            >
                                <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                </svg>
                                Résoudre les erreurs
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="p-4 mt-6 rounded-md bg-green-50 dark:bg-green-900/30">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Fusion réussie</h3>
                                <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                    <p>Toutes les données ont été fusionnées avec succès. Vous pouvez maintenant procéder à la vérification des résultats.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="flex justify-end mt-5 space-x-3 sm:mt-6">
                <button
                    wire:click="$set('showFusionResultModal', false)"
                    type="button"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                >
                    Fermer
                </button>
                <a href="{{ route('resultats.provisoires') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600">
                    <em class="icon ni ni-list-check mr-1.5"></em>
                    Voir les résultats provisoires
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal de résolution d'erreurs --}}
@if($showResolutionModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6 dark:bg-gray-800">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                        Résolution des erreurs
                    </h3>
                    <button type="button" class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:hover:text-gray-300" wire:click="$set('showResolutionModal', false)">
                        <span class="sr-only">Fermer</span>
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Choisissez comment résoudre chaque erreur détectée lors de la fusion des données.
                    </p>

                    <div class="mt-4 overflow-y-auto max-h-96">
                        @if(!empty($resolutions))
                            <div class="space-y-4">
                                @foreach($resolutions as $index => $resolution)
                                <div class="p-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 pt-0.5">
                                            <span class="inline-flex items-center justify-center w-8 h-8 text-red-500 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-200">
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="w-full ml-3">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $resolution['message'] }}</h4>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Type d'erreur: <span class="font-semibold">{{ $resolution['type'] }}</span>
                                            </p>

                                            <div class="mt-4">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Action à effectuer
                                                </label>
                                                <select
                                                    wire:model.live="resolutions.{{ $index }}.action"
                                                    class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                                >
                                                    <option value="ignorer_erreur">Ignorer cette erreur</option>
                                                    @if($resolution['type'] === 'manchette_sans_copie' || $resolution['type'] === 'copie_sans_manchette' || $resolution['type'] === 'ec_mismatch')
                                                    <option value="associer_manchette_copie">Associer manchette et copie</option>
                                                    @endif
                                                </select>

                                                @if($resolution['action'] === 'associer_manchette_copie')
                                                <div class="grid grid-cols-2 gap-4 mt-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                            Manchette
                                                        </label>
                                                        <select
                                                            wire:model.live="resolutions.{{ $index }}.manchette_id"
                                                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                                        >
                                                            <option value="">Sélectionner une manchette</option>
                                                            <!-- Options pour les manchettes disponibles devraient être générées ici -->
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                            Copie
                                                        </label>
                                                        <select
                                                            wire:model.live="resolutions.{{ $index }}.copie_id"
                                                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                                        >
                                                            <option value="">Sélectionner une copie</option>
                                                            <!-- Options pour les copies disponibles devraient être générées ici -->
                                                        </select>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 rounded-md bg-yellow-50 dark:bg-yellow-900/30">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Aucune erreur à résoudre</h3>
                                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                            <p>
                                                Toutes les erreurs ont déjà été résolues ou il n'y a pas d'erreurs à traiter.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-5 space-x-3 sm:mt-6">
                <button
                    wire:click="$set('showResolutionModal', false)"
                    type="button"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                >
                    Annuler
                </button>
                <button
                    wire:click="appliquerResolutions"
                    type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="appliquerResolutions">Appliquer les résolutions</span>
                    <span wire:loading wire:target="appliquerResolutions">Traitement...</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif


