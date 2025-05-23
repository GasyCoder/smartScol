<!-- Onglet: Rapport et Statistiques -->
<div id="content-rapport-stats" class="tab-content" x-show="$wire.activeTab === 'rapport-stats'" style="{{ $activeTab !== 'rapport-stats' ? 'display: none;' : '' }}">

    <!-- Statistiques de base -->
    @if($resultatsStats && isset($resultatsStats['totalMatieres']) && $resultatsStats['totalMatieres'] > 0)
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            <div class="p-4 border rounded-lg bg-primary-50 dark:bg-primary-900/10 dark:border-primary-800">
                <div class="text-sm font-medium text-primary-800 dark:text-primary-300">Total matières fusionnées</div>
                <div class="mt-1 text-3xl font-semibold text-primary-600 dark:text-primary-200">{{ $resultatsStats['totalMatieres'] ?? 0 }}</div>
            </div>
            <div class="p-4 border rounded-lg bg-green-50 dark:bg-green-900/10 dark:border-green-800">
                <div class="text-sm font-medium text-green-800 dark:text-green-300">Étudiants</div>
                <div class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-200">{{ $resultatsStats['etudiants'] ?? 0 }}</div>
            </div>
            <div class="p-4 border rounded-lg bg-yellow-50 dark:bg-yellow-900/10 dark:border-yellow-800">
                <div class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Taux d'admission</div>
                <div class="mt-1 text-3xl font-semibold text-yellow-600 dark:text-yellow-200">{{ $resultatsStats['passRate'] ?? 0 }}%</div>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center p-8 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <p class="mt-4 text-center text-gray-600 dark:text-gray-400">Aucune statistique disponible. Veuillez d'abord générer des résultats en effectuant la fusion des données.</p>
        </div>
    @endif

    <!-- Rapport de cohérence -->
    @if(!empty($rapportCoherence))
        <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-medium text-gray-800 dark:text-gray-200">Rapport de cohérence des données</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Vérification de la correspondance entre les manchettes d'anonymat et les copies corrigées pour chaque matière.
                </p>
            </div>
            <div class="p-4 sm:p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    Matière
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    Manchettes
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    Copies
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    Notes
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    État
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    Problèmes détectés
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach($rapportCoherence as $rapport)
                                <tr class="{{ $rapport['complet'] ? 'hover:bg-gray-50 dark:hover:bg-gray-700' : 'bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20' }}">
                                    <!-- Nom de la matière -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $rapport['ec_nom'] ?? 'Matière inconnue' }}
                                        </div>
                                        @if(isset($rapport['ec_abr']) && $rapport['ec_abr'] !== 'N/A')
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $rapport['ec_abr'] }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Manchettes -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            <span class="font-medium">{{ $rapport['manchettes_count'] ?? 0 }}</span>
                                            <span class="text-gray-500">/ {{ $rapport['total_etudiants'] ?? 0 }}</span>
                                        </div>
                                        @if(isset($rapport['manchettes_count']) && isset($rapport['total_etudiants']))
                                            @php
                                                $pourcentageManchettes = $rapport['total_etudiants'] > 0 ? round(($rapport['manchettes_count'] / $rapport['total_etudiants']) * 100, 1) : 0;
                                            @endphp
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $pourcentageManchettes }}%
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Copies -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            <span class="font-medium">{{ $rapport['copies_count'] ?? 0 }}</span>
                                            @if(isset($rapport['codes_count']))
                                                <span class="text-gray-500">/ {{ $rapport['codes_count'] }}</span>
                                            @endif
                                        </div>
                                        @if(isset($rapport['copies_count']) && isset($rapport['codes_count']))
                                            @php
                                                $pourcentageCopies = $rapport['codes_count'] > 0 ? round(($rapport['copies_count'] / $rapport['codes_count']) * 100, 1) : 0;
                                            @endphp
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $pourcentageCopies }}%
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Notes attribuées -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            <span class="font-medium">{{ $rapport['etudiants_avec_note'] ?? 0 }}</span>
                                            <span class="text-gray-500">/ {{ $rapport['copies_count'] ?? 0 }}</span>
                                        </div>
                                        @if(isset($rapport['etudiants_avec_note']) && isset($rapport['copies_count']))
                                            @php
                                                $pourcentageNotes = $rapport['copies_count'] > 0 ? round(($rapport['etudiants_avec_note'] / $rapport['copies_count']) * 100, 1) : 0;
                                            @endphp
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $pourcentageNotes }}%
                                            </div>
                                        @endif
                                    </td>

                                    <!-- État de cohérence -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($rapport['complet'] ?? false)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Complet
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                Incomplet
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Problèmes détectés -->
                                    <td class="px-6 py-4">
                                        <div class="max-w-xs text-sm text-gray-500 dark:text-gray-400">
                                            @if($rapport['complet'] ?? false)
                                                <div class="flex items-center text-green-600 dark:text-green-400">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="text-xs">Aucun problème détecté</span>
                                                </div>
                                            @else
                                                <!-- Codes sans manchettes -->
                                                @if(isset($rapport['codes_sans_manchettes']['count']) && $rapport['codes_sans_manchettes']['count'] > 0)
                                                    <div class="mb-2 text-red-600 dark:text-red-400">
                                                        <div class="mb-1 text-xs font-medium">
                                                            {{ $rapport['codes_sans_manchettes']['count'] }} code(s) sans manchette :
                                                        </div>
                                                        <div class="px-2 py-1 font-mono text-xs rounded bg-red-50 dark:bg-red-900/20">
                                                            {{ implode(', ', array_slice($rapport['codes_sans_manchettes']['codes'] ?? [], 0, 3)) }}
                                                            @if(count($rapport['codes_sans_manchettes']['codes'] ?? []) > 3)
                                                                <span class="text-red-400">... et {{ count($rapport['codes_sans_manchettes']['codes']) - 3 }} autres</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Codes sans copies -->
                                                @if(isset($rapport['codes_sans_copies']['count']) && $rapport['codes_sans_copies']['count'] > 0)
                                                    <div class="mb-2 text-red-600 dark:text-red-400">
                                                        <div class="mb-1 text-xs font-medium">
                                                            {{ $rapport['codes_sans_copies']['count'] }} code(s) sans copie :
                                                        </div>
                                                        <div class="px-2 py-1 font-mono text-xs rounded bg-red-50 dark:bg-red-900/20">
                                                            {{ implode(', ', array_slice($rapport['codes_sans_copies']['codes'] ?? [], 0, 3)) }}
                                                            @if(count($rapport['codes_sans_copies']['codes'] ?? []) > 3)
                                                                <span class="text-red-400">... et {{ count($rapport['codes_sans_copies']['codes']) - 3 }} autres</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Étudiants sans manchette -->
                                                @if(isset($rapport['etudiants_sans_manchette']) && $rapport['etudiants_sans_manchette'] > 0)
                                                    <div class="mb-2 text-orange-600 dark:text-orange-400">
                                                        <div class="text-xs">
                                                            {{ $rapport['etudiants_sans_manchette'] }} étudiant(s) sans manchette pour cette matière
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Message générique si aucun problème spécifique n'est identifié -->
                                                @if(
                                                    (!isset($rapport['codes_sans_manchettes']['count']) || $rapport['codes_sans_manchettes']['count'] === 0) &&
                                                    (!isset($rapport['codes_sans_copies']['count']) || $rapport['codes_sans_copies']['count'] === 0) &&
                                                    (!isset($rapport['etudiants_sans_manchette']) || $rapport['etudiants_sans_manchette'] === 0)
                                                )
                                                    <div class="text-yellow-600 dark:text-yellow-400">
                                                        <div class="text-xs">
                                                            Discordance entre les données :
                                                            manchettes ({{ $rapport['manchettes_count'] ?? 0 }})
                                                            et copies ({{ $rapport['copies_count'] ?? 0 }})
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Résumé du rapport -->
                @if(count($rapportCoherence) > 0)
                    @php
                        $totalMatieres = count($rapportCoherence);
                        $matieresCompletes = collect($rapportCoherence)->where('complet', true)->count();
                        $matieresIncompletes = $totalMatieres - $matieresCompletes;
                        $pourcentageCompletude = $totalMatieres > 0 ? round(($matieresCompletes / $totalMatieres) * 100, 1) : 0;
                    @endphp
                    <div class="p-4 mt-6 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <h4 class="mb-2 text-sm font-medium text-gray-800 dark:text-gray-200">Résumé de la vérification</h4>
                        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
                            <div>
                                <div class="text-gray-600 dark:text-gray-400">Total matières</div>
                                <div class="font-semibold">{{ $totalMatieres }}</div>
                            </div>
                            <div>
                                <div class="text-green-600 dark:text-green-400">Complètes</div>
                                <div class="font-semibold text-green-600 dark:text-green-400">{{ $matieresCompletes }}</div>
                            </div>
                            <div>
                                <div class="text-red-600 dark:text-red-400">Incomplètes</div>
                                <div class="font-semibold text-red-600 dark:text-red-400">{{ $matieresIncompletes }}</div>
                            </div>
                            <div>
                                <div class="text-blue-600 dark:text-blue-400">Taux de complétude</div>
                                <div class="font-semibold text-blue-600 dark:text-blue-400">{{ $pourcentageCompletude }}%</div>
                            </div>
                        </div>

                        @if($pourcentageCompletude < 100)
                            <div class="p-3 mt-3 border border-yellow-200 rounded-md bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-800">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="text-sm text-yellow-800 dark:text-yellow-200">
                                        <strong>Attention :</strong> Certaines matières présentent des incohérences dans les données.
                                        Vérifiez les codes d'anonymat et assurez-vous que toutes les copies ont été importées correctement.
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-3 mt-3 border border-green-200 rounded-md bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="text-sm text-green-800 dark:text-green-200">
                                        <strong>Parfait !</strong> Toutes les matières sont complètes et prêtes pour la fusion.
                                        Vous pouvez procéder à la fusion des données en toute sécurité.
                                    </div>
                                </div>
                            </div>

                            <!-- Bouton "Voir les résultats à vérifier" -->
                            @if($showVerificationButton && $examen_id)
                                <div class="mt-4">
                                    <a
                                        href="{{ route('resultats.verification', ['examenId' => $examen_id]) }}"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Voir les résultats à vérifier
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="p-6 text-center bg-gray-100 rounded-lg dark:bg-gray-700">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mb-2 text-lg font-medium text-gray-800 dark:text-gray-200">Aucun rapport de cohérence disponible</h3>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                Pour générer un rapport de cohérence, vous devez d'abord effectuer une vérification des données.
            </p>
            <button
                wire:click="confirmVerification"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Vérifier la cohérence
            </button>
        </div>
    @endif
</div>
