{{-- Modales pour le processus de fusion - Sans AlpineJS --}}

{{-- 1. Modal Vérification de cohérence --}}
@if($confirmingVerification)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la vérification de cohérence
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous lancer la vérification de cohérence des données ? Cette action analysera les manchettes et copies pour générer un rapport détaillé.
            </p>
            <div class="p-3 mt-3 border border-blue-200 rounded-md bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>Étape 1 :</strong> Cette vérification est requise avant de lancer la fusion des données.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button wire:click="$set('confirmingVerification', false)"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            <button wire:click="verifierCoherence"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50">
                Vérifier
                <span wire:loading wire:target="verifierCoherence" class="ml-2 animate-spin">⟳</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- 2. Modal Fusion des données --}}
@if($confirmingFusion)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la fusion des données
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous lancer la fusion des données ? Cette action associera les manchettes aux copies pour générer les résultats provisoires.
            </p>
            <div class="p-3 mt-3 border border-yellow-200 rounded-md bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>Étape 2 :</strong> La fusion associe les manchettes aux copies. Assurez-vous que le rapport de cohérence est validé.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button wire:click="$set('confirmingFusion', false)"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            <button wire:click="lancerFusion"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:bg-yellow-700 dark:hover:bg-yellow-600 disabled:opacity-50">
                Lancer la fusion
                <span wire:loading wire:target="lancerFusion" class="ml-2 animate-spin">⟳</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- 3. Modal Avancer à VERIFY_2 --}}
@if($confirmingVerify2)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer l'avancement à VERIFICATION 2
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous avancer la fusion à l'étape VERIFICATION 2 ? Cette action validera les données associées.
            </p>
            <div class="p-3 mt-3 border border-yellow-200 rounded-md bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>Étape 2 (Validation) :</strong> Assurez-vous que toutes les copies sont vérifiées avant de continuer.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button wire:click="$set('confirmingVerify2', false)"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            <button wire:click="passerAVerify2"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:bg-yellow-700 dark:hover:bg-yellow-600 disabled:opacity-50">
                Avancer à VERIFY_2
                <span wire:loading wire:target="passerAVerify2" class="ml-2 animate-spin">⟳</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- 4. Modal Avancer à VERIFY_3 --}}
@if($confirmingVerify3)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer l'avancement à VERIFICATION 3
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous avancer la fusion à l'étape VERIFICATION 3 ? Cette action prépare les résultats pour la vérification finale avant validation.
            </p>
            <div class="p-3 mt-3 border border-yellow-200 rounded-md bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>Étape 3 (Vérification finale) :</strong> Assurez-vous que toutes les données de la seconde vérification sont correctes.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button wire:click="$set('confirmingVerify3', false)"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            <button wire:click="passerAVerify3"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:bg-yellow-700 dark:hover:bg-yellow-600 disabled:opacity-50">
                Avancer à VERIFICATION 3
                <span wire:loading wire:target="passerAVerify3" class="ml-2 animate-spin">⟳</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- 5. Modal Validation --}}
@if($confirmingValidation)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative w-full max-w-lg p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la validation des résultats
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous valider les résultats fusionnés ? Cette action les marquera comme vérifiés et prêts pour la publication.
            </p>
            @if($sessionActive)
                <div class="p-3 mt-3 border border-purple-200 rounded-md bg-purple-50 dark:bg-purple-900/20 dark:border-purple-700">
                    <p class="text-sm text-purple-800 dark:text-purple-200">
                        <strong>Étape 3 :</strong> Validation pour {{ $examen->niveau->nom ?? 'Niveau inconnu' }} - Session {{ $sessionActive->type }}
                    </p>
                    <ul class="mt-2 text-xs text-purple-700 list-disc list-inside dark:text-purple-300">
                        <li>Vérification des notes et des associations</li>
                        <li>Calcul des moyennes par étudiant</li>
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
            <button wire:click="$set('confirmingValidation', false)"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            <button wire:click="validerResultats"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-purple-700 dark:hover:bg-purple-600 disabled:opacity-50">
                Valider
                <span wire:loading wire:target="validerResultats" class="ml-2 animate-spin">⟳</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- 6. Modal Publication --}}
@if($confirmingPublication)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative w-full max-w-lg p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        @php
            $resultatsEnAttente = \App\Models\ResultatFinal::where('examen_id', $examen_id)
                ->where('session_exam_id', $sessionActive->id)
                ->where('statut', \App\Models\ResultatFinal::STATUT_EN_ATTENTE)
                ->exists();
            $estReactivation = $resultatsEnAttente;
            $isConcours = $estPACES;
        @endphp

        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ $estReactivation ? 'Confirmer la republication des résultats' : 'Confirmer la publication des résultats' }}
        </h3>
        
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @if($estReactivation)
                    {{ $isConcours 
                        ? 'Voulez-vous republier et classer les résultats ? Ils seront immédiatement accessibles après recalcul du classement.'
                        : 'Voulez-vous republier les résultats ? Ils seront immédiatement accessibles après recalcul des décisions.' }}
                @else
                    {{ $isConcours 
                        ? 'Voulez-vous classer et publier les résultats ? Ils seront immédiatement accessibles aux étudiants.'
                        : 'Voulez-vous publier les résultats ? Ils seront immédiatement accessibles aux étudiants.' }}
                @endif
            </p>

            @if($sessionActive)
                <div class="p-3 mt-3 border rounded-md {{ $estReactivation ? 'border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700' : 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700' }}">
                    <p class="text-sm {{ $estReactivation ? 'text-blue-800 dark:text-blue-200' : 'text-green-800 dark:text-green-200' }}">
                        <strong>{{ $estReactivation ? 'Republication' : 'Publication' }} :</strong> 
                        {{ $examen->niveau->nom ?? 'Niveau inconnu' }} - Session {{ $sessionActive->type }}
                    </p>
                    <ul class="mt-2 text-xs {{ $estReactivation ? 'text-blue-700 dark:text-blue-300' : 'text-green-700 dark:text-green-300' }} list-disc list-inside">
                        @if($estReactivation)
                            <li>Recalcul automatique des moyennes et décisions</li>
                            <li>Conservation de l'historique des modifications</li>
                        @else
                            <li>Transfert vers la table resultats_finaux</li>
                            <li>Calcul des décisions (admis, rattrapage, exclus)</li>
                        @endif
                        <li>Génération des hash de vérification</li>
                    </ul>
                </div>
            @endif
            
            <div class="p-3 mt-3 border border-red-200 rounded-md bg-red-50 dark:bg-red-900/20 dark:border-red-700">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <strong>Attention :</strong> Cette action est irréversible sans annulation complète.
                </p>
            </div>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button wire:click="$set('confirmingPublication', false)"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            <button wire:click="publierResultats"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 {{ $estReactivation ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-600' }}">
                {{ $estReactivation ? 'Republier les résultats' : 'Publier' }}
                <span wire:loading wire:target="publierResultats" class="ml-2 animate-spin">⟳</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- 7. Modal Réinitialisation --}}
@if($confirmingResetFusion)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la réinitialisation
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous réinitialiser le processus de fusion et de validation ? Cette action supprimera tous les résultats fusionnés et finaux pour cet examen.
            </p>
            <div class="p-3 mt-3 border border-red-200 rounded-md bg-red-50 dark:bg-red-900/20 dark:border-red-700">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <strong>Attention :</strong> Cette action est irréversible et supprimera :
                </p>
                <ul class="mt-2 text-xs text-red-700 list-disc list-inside dark:text-red-300">
                    <li>Résultats de fusion (resultats_fusion)</li>
                    <li>Résultats finaux (resultats_finaux)</li>
                    <li>Historique des statuts</li>
                </ul>
                <p class="mt-2 text-xs text-red-700 dark:text-red-300">
                    Les copies et manchettes originales resteront intactes.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button wire:click="$set('confirmingResetFusion', false)"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            <button wire:click="resetFusion"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-600 disabled:opacity-50">
                Réinitialiser
                <span wire:loading wire:target="resetFusion" class="ml-2 animate-spin">⟳</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- 8. Modal Réactivation --}}
@if($confirmingRevenirValidation)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la réactivation des résultats
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous réactiver les résultats annulés ? Ils seront remis à l'état "en attente" pour une nouvelle validation et publication.
            </p>
            <div class="p-3 mt-3 border border-blue-200 rounded-md bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>Étape 3 :</strong> Les résultats reviendront à l'étape de validation pour une nouvelle vérification.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button wire:click="$set('confirmingRevenirValidation', false)"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            <button wire:click="revenirValidation"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50">
                Réactiver
                <span wire:loading wire:target="revenirValidation" class="ml-2 animate-spin">⟳</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- 9. Modal Annulation --}}
@if($confirmingAnnulation)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50">
    <div class="relative w-full max-w-lg p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer l'annulation des résultats
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous annuler les résultats publiés ? Cette action les rendra indisponibles aux étudiants tout en préservant les données pour une éventuelle réactivation.
            </p>

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

            <div class="p-3 mt-3 border border-red-200 rounded-md bg-red-50 dark:bg-red-900/20 dark:border-red-700">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <strong>Conséquences de l'annulation :</strong>
                </p>
                <ul class="mt-2 text-xs text-red-700 list-disc list-inside dark:text-red-300">
                    <li>Les étudiants ne pourront plus consulter leurs résultats</li>
                    <li>Les exports seront temporairement indisponibles</li>
                    <li>Une notification peut être envoyée aux parties concernées</li>
                </ul>
            </div>

            <div class="mt-4">
                <label for="motif-annulation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Motif d'annulation (optionnel)
                </label>
                <textarea id="motif-annulation"
                          wire:model="motifAnnulation"
                          rows="3"
                          class="block w-full px-3 py-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                          placeholder="Indiquez la raison de cette annulation"></textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Ce motif sera conservé dans l'historique pour traçabilité.
                </p>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <button wire:click="$set('confirmingAnnulation', false)"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            <button wire:click="annulerResultats"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700 dark:hover:bg-red-600 disabled:opacity-50">
                Confirmer l'annulation
                <span wire:loading wire:target="annulerResultats" class="ml-2 animate-spin">⟳</span>
            </button>
        </div>
    </div>
</div>
@endif