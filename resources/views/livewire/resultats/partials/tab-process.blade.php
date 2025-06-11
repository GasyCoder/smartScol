{{--  resultats.partials.tab-process --}}
<div id="content-process" class="tab-content" x-show="$wire.activeTab === 'process'" style="{{ $activeTab !== 'process' ? 'display: none;' : '' }}">
    @include('livewire.resultats.partials.etapes')

    <!-- Guide d'étapes adapté selon la session -->
    <div class="p-4 mt-6 border rounded-lg
        @if($sessionActive && $sessionActive->type === 'Rattrapage')
            bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800
        @else
            bg-gray-50 border-gray-200 dark:bg-gray-700 dark:border-gray-600
        @endif">
        <div class="flex items-center justify-between mb-2">
            <h4 class="text-base font-medium
                @if($sessionActive && $sessionActive->type === 'Rattrapage')
                    text-blue-900 dark:text-blue-200
                @else
                    text-gray-900 dark:text-white
                @endif">
                Guide d'étapes
            </h4>

            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-300">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Session de rattrapage
                </span>
            @endif
        </div>

        <div class="text-sm
            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                text-blue-700 dark:text-blue-300
            @else
                text-gray-600 dark:text-gray-300
            @endif">
            @if($statut === 'initial')
                <div class="flex items-start">
                    <svg class="w-5 h-5 mt-0.5 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="ml-3">
                        <p><strong>Étape à suivre: Vérification de cohérence</strong><br>
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Vérifiez la cohérence des données de rattrapage. Seuls les étudiants éligibles sont concernés.
                            @php
                                $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
                                $compteursDonnees = $this->getCompteursDonneesSession();
                            @endphp
                            @if($compteursDonnees['manchettes'] == 0 && $etudiantsEligibles->count() > 0)
                                <br><span class="font-medium text-orange-600 dark:text-orange-400">
                                    ⚠️ {{ $etudiantsEligibles->count() }} étudiant(s) éligible(s) détecté(s) mais aucune donnée initialisée.
                                </span>
                            @endif
                        @else
                            Commencez par vérifier la cohérence entre les manchettes et les copies pour tous les étudiants.
                        @endif
                        </p>
                    </div>
                </div>
            @elseif($statut === 'verification')
                <div class="flex items-start">
                    <svg class="w-5 h-5 mt-0.5 text-yellow-500 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="ml-3">
                        <p><strong>Étape actuelle: Vérification de cohérence</strong><br>
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Vérifiez le rapport de cohérence des données de rattrapage puis lancez la première fusion.
                            Les notes de rattrapage peuvent remplacer ou compléter celles de la session normale.
                        @else
                            Vérifiez le rapport de cohérence puis lancez la première fusion.
                        @endif
                        </p>
                    </div>
                </div>
            @elseif($statut === 'fusion')
                <div class="flex items-start">
                    <svg class="w-5 h-5 mt-0.5 text-yellow-500 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                    </svg>
                    <div class="ml-3">
                        <p><strong>Étape actuelle: Fusion des données (étape {{ $etapeFusion }}/3)</strong>
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            <span class="text-orange-600 dark:text-orange-400"> - Session de rattrapage</span>
                        @endif
                        <br>
                        @if($etapeFusion === 1)
                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                Les manchettes et copies de rattrapage ont été associées. Lancez la deuxième fusion.
                            @else
                                Les manchettes et copies ont été associées. Lancez la deuxième fusion pour calculer les moyennes.
                            @endif
                        @elseif($etapeFusion === 2)
                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                Les moyennes de rattrapage ont été calculées. Lancez la dernière fusion.
                            @else
                                Les moyennes ont été calculées. Lancez la dernière fusion pour finaliser les résultats.
                            @endif
                        @elseif($etapeFusion === 3)
                            @if($sessionActive && $sessionActive->type === 'Rattrapage')
                                La fusion de rattrapage est complète. Les meilleures notes entre session normale et rattrapage seront considérées.
                            @else
                                La fusion est complète. Vous pouvez maintenant vérifier et valider les résultats.
                            @endif
                        @else
                            La fusion n'a pas encore commencé. Lancez la première fusion.
                        @endif
                        </p>
                    </div>
                </div>
            @elseif($statut === 'validation' || $statut === 'valide')
                <div class="flex items-start">
                    <svg class="w-5 h-5 mt-0.5 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="ml-3">
                        <p><strong>Étape actuelle: Validation des résultats</strong>
                        <br>
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Les résultats de rattrapage ont été validés. La publication appliquera automatiquement les meilleures notes entre les deux sessions.
                        @else
                            Les résultats ont été validés. Vous pouvez maintenant les publier.
                            @if($estPACES)
                                <br><strong>Note:</strong> PACES 1ère année est un concours.
                            @endif
                        @endif
                        </p>
                    </div>
                </div>
            @elseif($statut === 'publie')
                <div class="flex items-start">
                    <svg class="w-5 h-5 mt-0.5 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <div class="ml-3">
                        <p><strong>Étape terminée: <span class="text-green-800 dark:text-green-400">Résultats publiés</span></strong>
                        <br>
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Les résultats de rattrapage ont été publiés avec succès. Les décisions finales prennent en compte les meilleures performances entre les deux sessions.
                        @else
                            Les résultats ont été publiés avec succès et sont maintenant accessibles.
                        @endif
                        </p>
                    </div>
                </div>
            @elseif($statut === 'annule')
                <div class="flex items-start">
                    <svg class="w-5 h-5 mt-0.5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="ml-3">
                        <p><strong>Statut: <span class="text-red-800 dark:text-red-400">Résultats annulés</span></strong>
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            <span class="text-orange-600 dark:text-orange-400"> - Session de rattrapage</span>
                        @endif
                        <br>
                        @if($sessionActive && $sessionActive->type === 'Rattrapage')
                            Les résultats de rattrapage ont été annulés. Vous pouvez les réactiver ou recommencer le processus.
                        @else
                            Les résultats ont été annulés. Vous pouvez les réactiver ou recommencer le processus.
                        @endif
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Informations spécifiques au rattrapage -->
        @if($sessionActive && $sessionActive->type === 'Rattrapage')
            <div class="p-3 mt-4 bg-orange-100 border border-orange-200 rounded-md dark:bg-orange-900/30 dark:border-orange-700">
                <div class="flex items-start">
                    <svg class="w-4 h-4 mt-0.5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="ml-2 text-xs text-orange-700 dark:text-orange-300">
                        <p><strong>Spécificités du rattrapage :</strong></p>
                        <ul class="mt-1 space-y-1 list-disc list-inside">
                            <li>Seuls les étudiants ayant échoué en session normale peuvent participer</li>
                            <li>Les meilleures notes entre session normale et rattrapage seront retenues</li>
                            <li>Les décisions finales remplacent celles de la session normale</li>
                            <li>Une délibération peut être requise selon le règlement</li>
                        </ul>

                        @if($examen)
                            @php
                                $etudiantsEligibles = $this->getEtudiantsEligiblesRattrapage();
                                $compteursDonnees = $this->getCompteursDonneesSession();
                            @endphp
                            <div class="pt-2 mt-2 border-t border-orange-200 dark:border-orange-700">
                                <p class="font-medium">État actuel :</p>
                                <div class="grid grid-cols-2 gap-2 mt-1 text-xs">
                                    <div>Étudiants éligibles: {{ $etudiantsEligibles->count() }}</div>
                                    <div>Manchettes créées: {{ $compteursDonnees['manchettes'] }}</div>
                                    <div>Copies saisies: {{ $compteursDonnees['copies'] }}</div>
                                    <div>Matières: {{ $compteursDonnees['ecs'] }}</div>
                                </div>

                                @if($etudiantsEligibles->count() == 0 && $compteursDonnees['manchettes'] > 0)
                                    <div class="mt-2 text-orange-600 dark:text-orange-400">
                                        <button wire:click="diagnosticEligiblesRattrapage"
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-orange-800 bg-orange-200 border border-orange-300 rounded hover:bg-orange-300 focus:outline-none dark:bg-orange-800 dark:text-orange-100 dark:border-orange-700 dark:hover:bg-orange-700">
                                            🔍 Diagnostiquer le problème des éligibles
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@include('livewire.resultats.partials.modals')
