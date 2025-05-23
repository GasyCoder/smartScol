<!-- Modal de confirmation pour la vérification -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50" x-show="$wire.confirmingVerification" style="display: none;">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <!-- Titre de la modale -->
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la vérification de cohérence
        </h3>

        <!-- Contenu de la modale -->
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Êtes-vous sûr de vouloir lancer la vérification de cohérence ? Cette action analysera les données et générera un rapport.
            </p>
        </div>

        <!-- Pied de page avec boutons -->
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingVerification', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
            >
                Annuler
            </button>
            <button
                wire:click="verifierCoherence"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600"
            >
                Confirmer
            </button>
        </div>
    </div>
</div>

<!-- Modal de confirmation pour la fusion -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50" x-show="$wire.confirmingFusion" style="display: none;">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <!-- Titre de la modale -->
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la fusion
        </h3>

        <!-- Contenu de la modale -->
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @if($statut === 'verification')
                    Êtes-vous sûr de vouloir lancer la première fusion ? Cette action associera les manchettes aux copies.
                @elseif($statut === 'fusion' && $etapeFusion === 1)
                    Êtes-vous sûr de vouloir lancer la deuxième fusion ? Cette action calculera les moyennes.
                @elseif($statut === 'fusion' && $etapeFusion === 2)
                    Êtes-vous sûr de vouloir lancer la dernière fusion ? Cette action finalisera les résultats.
                @else
                    Êtes-vous sûr de vouloir refusionner les données ? Cela réinitialisera les étapes précédentes.
                @endif
            </p>
        </div>

        <!-- Pied de page avec boutons -->
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingFusion', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
            >
                Annuler
            </button>
            <button
                wire:click="fusionner"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600"
            >
                Confirmer
            </button>
        </div>
    </div>
</div>

<!-- Modal de confirmation pour la réinitialisation -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50" x-show="$wire.confirmingReset" style="display: none;">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <!-- Titre de la modale -->
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la réinitialisation
        </h3>

        <!-- Contenu de la modale -->
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Êtes-vous sûr de vouloir réinitialiser la fusion ? Cette action supprimera tous les résultats de cet examen.
            </p>
            <div class="p-3 mt-3 border border-red-200 rounded-md bg-red-50 dark:bg-red-900/20 dark:border-red-700">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <strong>Attention :</strong> Cette action est irréversible et supprimera tous les résultats provisoires et publiés.
                </p>
            </div>
        </div>

        <!-- Pied de page avec boutons -->
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingReset', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
            >
                Annuler
            </button>
            <button
                wire:click="resetExam"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-600"
            >
                Confirmer la réinitialisation
            </button>
        </div>
    </div>
</div>

<!-- ✅ MODAL CORRIGÉE : Publication unifiée (validation + publication simultanées) -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50" x-show="$wire.confirmingValidation" style="display: none;">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <!-- Titre de la modale -->
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            @if($statut === 'annule')
                Confirmer la republication des résultats
            @else
                Confirmer la validation et publication
            @endif
        </h3>

        <!-- Contenu de la modale -->
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @if($statut === 'annule')
                    Êtes-vous sûr de vouloir republier les résultats ? Cela rendra les résultats à nouveau accessibles aux étudiants.
                @else
                    Êtes-vous sûr de vouloir valider et publier les résultats ? Cette action unique les rendra immédiatement accessibles aux étudiants.
                @endif
            </p>

            @if($statut !== 'annule')
            <div class="p-3 mt-3 border border-green-200 rounded-md bg-green-50 dark:bg-green-900/20 dark:border-green-700">
                <p class="text-sm text-green-800 dark:text-green-200">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <strong>Nouvelle logique :</strong> La validation et la publication se font maintenant en une seule étape pour simplifier le processus.
                </p>
            </div>
            @endif
        </div>

        <!-- Pied de page avec boutons -->
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingValidation', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
            >
                Annuler
            </button>
            <button
                wire:click="validerResultats"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-600"
            >
                @if($statut === 'annule')
                    Republier les résultats
                @else
                    Valider et publier
                @endif
            </button>
        </div>
    </div>
</div>

<!-- ✅ MODAL SUPPRIMÉE : Modal de publication séparée -->
<!-- Cette modal n'est plus nécessaire car la publication est intégrée dans la validation -->

<!-- Modal de confirmation pour l'annulation -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50" x-show="$wire.confirmingAnnulation" style="display: none;">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <!-- Titre de la modale -->
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer l'annulation des résultats
        </h3>

        <!-- Contenu de la modale -->
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Êtes-vous sûr de vouloir annuler les résultats publiés ? Cette action les marquera comme annulés et les rendra inaccessibles aux étudiants.
            </p>
            <div class="p-3 mt-3 border rounded-md border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700">
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Les résultats annulés peuvent être républiés ou réactivés plus tard.
                </p>
            </div>
        </div>

        <!-- Pied de page avec boutons -->
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingAnnulation', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
            >
                Annuler
            </button>
            <button
                wire:click="annulerResultats"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-600"
            >
                Confirmer l'annulation
            </button>
        </div>
    </div>
</div>

<!-- ✅ MODAL CORRIGÉE : Retour à l'état provisoire (anciennement "revenir à la validation") -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50" x-show="$wire.confirmingRevenirValidation" style="display: none;">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <!-- Titre de la modale -->
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la réactivation des résultats
        </h3>

        <!-- Contenu de la modale -->
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Êtes-vous sûr de vouloir réactiver les résultats annulés ? Cela les remettra à l'état provisoire, permettant une nouvelle validation.
            </p>
            <div class="p-3 mt-3 border border-blue-200 rounded-md bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <strong>Nouvelle logique :</strong> Les résultats reviendront à l'état provisoire et pourront être revalidés puis republiés.
                </p>
            </div>
        </div>

        <!-- Pied de page avec boutons -->
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingRevenirValidation', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
            >
                Annuler
            </button>
            <button
                wire:click="revenirValidation"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600"
            >
                Réactiver les résultats
            </button>
        </div>
    </div>
</div>
