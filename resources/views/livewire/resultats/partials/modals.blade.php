<!-- Modales pour le processus en 4 étapes de gestion des examens -->

<!-- 1. Modal Vérification de cohérence -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingVerification"
      x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="verification-modal-title">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 id="verification-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la vérification de cohérence
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous lancer la vérification de cohérence des données ? Cette action analysera les manchettes et copies pour générer un rapport détaillé.
            </p>
            <div class="p-3 mt-3 border border-blue-200 rounded-md bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <em class="mr-1 icon ni ni-info"></em>
                    <strong>Étape 1 :</strong> Cette vérification est requise avant de lancer la fusion des données.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingVerification', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50"
                aria-label="Annuler la vérification"
            >
                Annuler
            </button>
            <button
                wire:click="verifierCoherence"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50"
                aria-label="Lancer la vérification de cohérence"
            >
                Vérifier
                <span wire:loading wire:target="verifierCoherence" class="ml-2 animate-spin icon ni ni-loader"></span>
            </button>
        </div>
    </div>
</div>

<!-- 2. Modal Fusion des données -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingFusion"
     x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="fusion-modal-title">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 id="fusion-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la fusion des données
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous lancer la fusion des données ? Cette action associera les manchettes aux copies pour générer les résultats provisoires.
            </p>
            <div class="p-3 mt-3 border border-yellow-200 rounded-md bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <em class="mr-1 icon ni ni-info"></em>
                    <strong>Étape 2 :</strong> La fusion associe les manchettes aux copies. Assurez-vous que le rapport de cohérence est validé.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingFusion', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50"
                aria-label="Annuler la fusion"
            >
                Annuler
            </button>
            <button
                wire:click="lancerFusion"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:bg-yellow-700 dark:hover:bg-yellow-600 disabled:opacity-50"
                aria-label="Lancer la fusion"
            >
                Lancer la fusion
                <span wire:loading wire:target="lancerFusion" class="ml-2 animate-spin icon ni ni-loader"></span>
            </button>
        </div>
    </div>
</div>

<!-- 2b. Modal Avancer à VERIFY_2 -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingVerify2"
      x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="verify2-modal-title">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 id="verify2-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer l'avancement à VERIFY_2
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous avancer la fusion à l'étape VERIFY_2 ? Cette action validera les données associées et préparera les résultats pour la délibération.
            </p>
            <div class="p-3 mt-3 border border-yellow-200 rounded-md bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <em class="mr-1 icon ni ni-info"></em>
                    <strong>Étape 2 (Validation) :</strong> Assurez-vous que toutes les copies sont vérifiées avant de continuer.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingVerify2', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50"
                aria-label="Annuler l'avancement"
            >
                Annuler
            </button>
            <button
                wire:click="passerAVerify2"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:bg-yellow-700 dark:hover:bg-yellow-600 disabled:opacity-50"
                aria-label="Avancer à VERIFY_2"
            >
                Avancer à VERIFY_2
                <span wire:loading wire:target="passerAVerify2" class="ml-2 animate-spin icon ni ni-loader"></span>
            </button>
        </div>
    </div>
</div>

<!-- 3b Modal Avancer à VERIFY_3 -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingVerify3"
     x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="verify3-modal-title">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 id="verify3-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer l'avancement à VERIFY_3
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous avancer la fusion à l'étape VERIFY_3 ? Cette action prépare les résultats pour la vérification finale avant validation.
            </p>
            <div class="p-3 mt-3 border border-yellow-200 rounded-md bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <em class="mr-1 icon ni ni-info"></em>
                    <strong>Étape 3 (Vérification finale) :</strong> Assurez-vous que toutes les données de la seconde vérification sont correctes.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingVerify3', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50"
                aria-label="Annuler l'avancement"
            >
                Annuler
            </button>
            <button
                wire:click="passerAVerify3"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:bg-yellow-700 dark:hover:bg-yellow-600 disabled:opacity-50"
                aria-label="Avancer à VERIFY_3"
            >
                Avancer à VERIFY_3
                <span wire:loading wire:target="passerAVerify3" class="ml-2 animate-spin icon ni ni-loader"></span>
            </button>
        </div>
    </div>
</div>

<!-- 3. Modal Vérification et Validation -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingValidation"
      x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="validation-modal-title">
    <div class="relative w-full max-w-lg p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 id="validation-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la validation des résultats
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous valider les résultats fusionnés ? Cette action les marquera comme vérifiés et prêts pour la publication ou le transfert.
            </p>
            @php
                $contexteExamen = isset($contexteExamen) ? $contexteExamen : (isset($this->contexteExamen) ? $this->contexteExamen : null);
            @endphp
            @if($contexteExamen)
                @php
                    $requiresDeliberation = $contexteExamen['requires_deliberation'] ?? false;
                    $isConcours = $contexteExamen['is_concours'] ?? false;
                    $sessionType = $contexteExamen['session_type'] ?? 'Inconnu';
                    $niveauNom = isset($contexteExamen['niveau']) && isset($contexteExamen['niveau']->nom) ? $contexteExamen['niveau']->nom : 'Inconnu';
                @endphp
                <div class="p-3 mt-3 border border-purple-200 rounded-md bg-purple-50 dark:bg-purple-900/20 dark:border-purple-700">
                    <p class="text-sm text-purple-800 dark:text-purple-200">
                        <em class="mr-1 icon ni ni-info"></em>
                        <strong>Étape 3 :</strong> Validation pour {{ $niveauNom }} - Session {{ $sessionType }}
                    </p>
                    <ul class="mt-2 text-xs text-purple-700 list-disc list-inside dark:text-purple-300">
                        <li>Vérification des notes et des associations</li>
                        <li>Calcul des moyennes par étudiant</li>
                        @if($requiresDeliberation)
                            <li>Préparation pour la délibération (session de rattrapage)</li>
                        @elseif($isConcours)
                            <li>Préparation du classement pour le concours</li>
                        @else
                            <li>Préparation pour la publication directe</li>
                        @endif
                    </ul>
                </div>
            @else
                <div class="p-3 mt-3 border border-yellow-200 rounded-md bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <em class="mr-1 icon ni ni-alert"></em>
                        <strong>Avertissement :</strong> Contexte de l'examen non disponible. Veuillez vérifier les paramètres de l'examen.
                    </p>
                </div>
            @endif
            <div class="p-3 mt-3 border rounded-md border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700">
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    <em class="mr-1 icon ni ni-alert"></em>
                    <strong>Important :</strong> Vérifiez tous les résultats avant de valider.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingValidation', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50"
                aria-label="Annuler la validation"
            >
                Annuler
            </button>
            <button
                wire:click="validerResultats"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-purple-700 dark:hover:bg-purple-600 disabled:opacity-50"
                aria-label="Valider les résultats"
            >
                Valider
                <span wire:loading wire:target="validerResultats" class="ml-2 animate-spin icon ni ni-loader"></span>
            </button>
        </div>
    </div>
</div>

<!-- 4. Modal Publication ou transfert -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingPublication"
      x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="publication-modal-title">
    <div class="relative w-full max-w-lg p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 id="publication-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la publication ou le transfert
        </h3>
        <div class="mb-6">
            @php
                $contexteExamen = isset($contexteExamen) ? $contexteExamen : (isset($this->contexteExamen) ? $this->contexteExamen : null);
                $requiresDeliberation = $contexteExamen && isset($contexteExamen['requires_deliberation']) ? $contexteExamen['requires_deliberation'] : false;
                $isConcours = $contexteExamen && isset($contexteExamen['is_concours']) ? $contexteExamen['is_concours'] : false;
            @endphp
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @if($requiresDeliberation)
                    Voulez-vous transférer les résultats pour délibération ? Ils seront marqués comme "en attente" jusqu'à la délibération finale.
                @elseif($isConcours)
                    Voulez-vous classer et publier les résultats ? Ils seront immédiatement accessibles aux étudiants.
                @else
                    Voulez-vous publier les résultats ? Ils seront immédiatement accessibles aux étudiants.
                @endif
            </p>
            @if($contexteExamen)
                @php
                    $sessionType = $contexteExamen['session_type'] ?? 'Inconnu';
                    $niveauNom = isset($contexteExamen['niveau']) && isset($contexteExamen['niveau']->nom) ? $contexteExamen['niveau']->nom : 'Inconnu';
                @endphp
                <div class="p-3 mt-3 border border-green-200 rounded-md bg-green-50 dark:bg-green-900/20 dark:border-green-700">
                    <p class="text-sm text-green-800 dark:text-green-200">
                        <em class="mr-1 icon ni ni-info"></em>
                        <strong>Étape 4 :</strong> Publication pour {{ $niveauNom }} - Session {{ $sessionType }}
                    </p>
                    <ul class="mt-2 text-xs text-green-700 list-disc list-inside dark:text-green-300">
                        <li>Transfert vers la table `resultats_finaux`</li>
                        <li>Calcul des décisions (admis, rattrapage, exclus)</li>
                        @if($requiresDeliberation)
                            <li>Programmation d'une délibération</li>
                        @elseif($isConcours)
                            <li>Établissement du classement</li>
                        @else
                            <li>Publication immédiate</li>
                        @endif
                        <li>Génération des hash de vérification</li>
                    </ul>
                </div>
            @else
                <div class="p-3 mt-3 border border-yellow-200 rounded-md bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <em class="mr-1 icon ni ni-alert"></em>
                        <strong>Avertissement :</strong> Contexte de l'examen non disponible. Veuillez vérifier les paramètres de l'examen.
                    </p>
                </div>
            @endif
            <div class="p-3 mt-3 border border-red-200 rounded-md bg-red-50 dark:bg-red-900/20 dark:border-red-700">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <em class="mr-1 icon ni ni-alert"></em>
                    <strong>Attention :</strong> Cette action est irréversible sans annulation complète.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingPublication', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50"
                aria-label="Annuler la publication"
            >
                Annuler
            </button>
            <button
                wire:click="publierResultats"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white {{ $requiresDeliberation ? 'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500' : ($isConcours ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500') }} rounded-md dark:{{ $requiresDeliberation ? 'bg-purple-700 hover:bg-purple-600' : ($isConcours ? 'bg-blue-700 hover:bg-blue-600' : 'bg-green-700 hover:bg-green-600') }} disabled:opacity-50"
                aria-label="Publier ou transférer les résultats"
            >
                @if($requiresDeliberation)
                    Transférer pour délibération
                @elseif($isConcours)
                    Classer et publier
                @else
                    Publier
                @endif
                <span wire:loading wire:target="publierResultats" class="ml-2 animate-spin icon ni ni-loader"></span>
            </button>
        </div>
    </div>
</div>

<!-- 5. Modal Réinitialisation -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingResetFusion"
      x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="reset-fusion-modal-title">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 id="reset-fusion-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la réinitialisation
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous réinitialiser le processus de fusion et de validation ? Cette action supprimera tous les résultats fusionnés et finaux pour cet examen.
            </p>
            <div class="p-3 mt-3 border border-red-200 rounded-md bg-red-50 dark:bg-red-900/20 dark:border-red-700">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <em class="mr-1 icon ni ni-alert"></em>
                    <strong>Attention :</strong> Cette action est irréversible et supprimera :
                </p>
                <ul class="mt-2 text-xs text-red-700 list-disc list-inside dark:text-red-300">
                    <li>Résultats de fusion (`resultats_fusion`)</li>
                    <li>Résultats finaux (`resultats_finaux`)</li>
                    <li>Délibérations non validées</li>
                    <li>Historique des statuts</li>
                </ul>
                <p class="mt-2 text-xs text-red-700 dark:text-red-300">
                    Les copies et manchettes originales resteront intactes.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingResetFusion', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50"
                aria-label="Annuler la réinitialisation"
            >
                Annuler
            </button>
            <button
                wire:click="resetFusion"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-600 disabled:opacity-50"
                aria-label="Réinitialiser le processus"
            >
                Réinitialiser
                <span wire:loading wire:target="resetFusion" class="ml-2 animate-spin icon ni ni-loader"></span>
            </button>
        </div>
    </div>
</div>

<!-- 6. Modal Annulation -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingResetFusion"
     x-cloak
     x-init="console.log('confirmingResetFusion:', $wire.confirmingResetFusion)"
     role="dialog"
     aria-modal="true"
     aria-labelledby="reset-fusion-modal-title">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 id="reset-fusion-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la réinitialisation
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous réinitialiser le processus de fusion et de validation ? Cette action supprimera tous les résultats fusionnés et finaux pour cet examen.
            </p>
            <div class="p-3 mt-3 border border-red-200 rounded-md bg-red-50 dark:bg-red-900/20 dark:border-red-700">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <em class="mr-1 icon ni ni-alert"></em>
                    <strong>Attention :</strong> Cette action est irréversible et supprimera :
                </p>
                <ul class="mt-2 text-xs text-red-700 list-disc list-inside dark:text-red-300">
                    <li>Résultats de fusion (`resultats_fusion`)</li>
                    <li>Résultats finaux (`resultats_finaux`)</li>
                    <li>Délibérations non validées</li>
                </ul>
                <p class="mt-2 text-xs text-red-700 dark:text-red-300">
                    Les copies et manchettes originales resteront intactes.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingResetFusion', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50"
                aria-label="Annuler la réinitialisation"
            >
                Annuler
            </button>
            <button
                wire:click="resetFusion"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-600 disabled:opacity-50"
                aria-label="Réinitialiser le processus"
            >
                Réinitialiser
                <span wire:loading wire:target="resetFusion" class="ml-2 animate-spin icon ni ni-loader"></span>
            </button>
        </div>
    </div>
</div>

<!-- 7. Modal Réactivation -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingRevenirValidation"
     x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="revenir-validation-modal-title">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 id="revenir-validation-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer la réactivation des résultats
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous réactiver les résultats annulés ? Ils seront remis à l'état "en attente" pour une nouvelle validation et publication.
            </p>
            <div class="p-3 mt-3 border border-blue-200 rounded-md bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <em class="mr-1 icon ni ni-info"></em>
                    <strong>Étape 3 :</strong> Les résultats reviendront à l'étape de validation pour une nouvelle vérification.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingRevenirValidation', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50"
                aria-label="Annuler la réactivation"
            >
                Annuler
            </button>
            <button
                wire:click="revenirValidation"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50"
                aria-label="Réactiver les résultats"
            >
                Réactiver
                <span wire:loading wire:target="revenirValidation" class="ml-2 animate-spin icon ni ni-loader"></span>
            </button>
        </div>
    </div>
</div>

<!-- 8. Modal Annulation des résultats publiés -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingAnnulation"
     x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="annulation-modal-title">
    <div class="relative w-full max-w-lg p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 id="annulation-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer l'annulation des résultats
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous annuler les résultats publiés ? Cette action les rendra indisponibles aux étudiants tout en préservant les données pour une éventuelle réactivation.
            </p>
            
            @php
                $contexteExamen = isset($contexteExamen) ? $contexteExamen : (isset($this->contexteExamen) ? $this->contexteExamen : null);
            @endphp
            @if($contexteExamen)
                @php
                    $sessionType = $contexteExamen['session_type'] ?? 'Inconnu';
                    $niveauNom = isset($contexteExamen['niveau']) && isset($contexteExamen['niveau']->nom) ? $contexteExamen['niveau']->nom : 'Inconnu';
                @endphp
                <div class="p-3 mt-3 border border-orange-200 rounded-md bg-orange-50 dark:bg-orange-900/20 dark:border-orange-700">
                    <p class="text-sm text-orange-800 dark:text-orange-200">
                        <em class="mr-1 icon ni ni-info"></em>
                        <strong>Annulation pour :</strong> {{ $niveauNom }} - Session {{ $sessionType }}
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
                    <em class="mr-1 icon ni ni-alert"></em>
                    <strong>Conséquences de l'annulation :</strong>
                </p>
                <ul class="mt-2 text-xs text-red-700 list-disc list-inside dark:text-red-300">
                    <li>Les étudiants ne pourront plus consulter leurs résultats</li>
                    <li>Les exports seront temporairement indisponibles</li>
                    <li>Une notification peut être envoyée aux parties concernées</li>
                    <li>L'action sera tracée dans l'historique du système</li>
                </ul>
            </div>
            
            <!-- Zone de saisie optionnelle pour le motif d'annulation -->
            <div class="mt-4">
                <label for="motif-annulation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Motif d'annulation (optionnel)
                </label>
                <textarea
                    id="motif-annulation"
                    wire:model="motifAnnulation"
                    rows="3"
                    class="block w-full px-3 py-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                    placeholder="Indiquez la raison de cette annulation (erreur de calcul, contestation, etc.)"
                ></textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Ce motif sera conservé dans l'historique pour traçabilité.
                </p>
            </div>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingAnnulation', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50"
                aria-label="Annuler l'opération"
            >
                Annuler
            </button>
            <button
                wire:click="annulerResultats"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-600 disabled:opacity-50"
                aria-label="Confirmer l'annulation des résultats"
            >
                Confirmer l'annulation
                <span wire:loading wire:target="annulerResultats" class="ml-2 animate-spin icon ni ni-loader"></span>
            </button>
        </div>
    </div>
</div>

<!-- 9. Modal Exportation -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.confirmingExport"
     x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="export-modal-title">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 id="export-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            Confirmer l'exportation des résultats
        </h3>
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Voulez-vous exporter les résultats ? Cette action générera un fichier contenant les données des résultats.
            </p>
            <div class="p-3 mt-3 border border-blue-200 rounded-md bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <em class="mr-1 icon ni ni-info"></em>
                    <strong>Note :</strong> Assurez-vous que les résultats sont validés avant l'exportation.
                </p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('confirmingExport', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50"
                aria-label="Annuler l'exportation"
            >
                Annuler
            </button>
            <button
                wire:click="exporterResultats"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50"
                aria-label="Exporter les résultats"
            >
                Exporter
                <span wire:loading wire:target="exporterResultats" class="ml-2 animate-spin icon ni ni-loader"></span>
            </button>
        </div>
    </div>
</div>
