<!-- Onglet 3: Statistiques des résultats -->
<div id="content-stats" class="tab-content" x-show="$wire.activeTab === 'stats'" style="{{ $activeTab !== 'stats' ? 'display: none;' : '' }}">
    @if($resultatsStats && $totalResultats > 0)
    <!-- Statistiques générales -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="p-4 border rounded-lg bg-primary-50 dark:bg-primary-900/10 dark:border-primary-800">
            <div class="text-sm font-medium text-primary-800 dark:text-primary-300">Total résultats</div>
            <div class="mt-1 text-3xl font-semibold text-primary-600 dark:text-primary-200">{{ $resultatsStats['total'] }}</div>
        </div>

        <div class="p-4 border rounded-lg bg-green-50 dark:bg-green-900/10 dark:border-green-800">
            <div class="text-sm font-medium text-green-800 dark:text-green-300">Taux de réussite</div>
            <div class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-200">{{ $resultatsStats['passRate'] }}%</div>
        </div>

        <div class="p-4 border rounded-lg bg-yellow-50 dark:bg-yellow-900/10 dark:border-yellow-800">
            <div class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Moyenne</div>
            <div class="mt-1 text-3xl font-semibold text-yellow-600 dark:text-yellow-200">{{ $resultatsStats['notes']['moyenne'] }}</div>
        </div>

        <div class="p-4 border rounded-lg bg-cyan-50 dark:bg-cyan-900/10 dark:border-cyan-800">
            <div class="text-sm font-medium text-cyan-800 dark:text-cyan-300">Min / Max</div>
            <div class="mt-1 text-3xl font-semibold text-cyan-600 dark:text-cyan-200">
                {{ $resultatsStats['notes']['min'] }} / {{ $resultatsStats['notes']['max'] }}
            </div>
        </div>
    </div>

    <!-- Distribution des notes -->
    <div class="mt-6 overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-medium text-gray-800 dark:text-gray-200">Distribution des notes</h3>
        </div>
        <div class="p-4">
            <div class="h-64">
                <!-- Utilisation de l'ID qui sera utilisé par la fonction Bar de Dashwin -->
                <canvas id="distributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Ajoutez optionnellement un graphique circulaire pour la répartition réussite/échec -->
    <div class="mt-6 overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-medium text-gray-800 dark:text-gray-200">Taux de réussite</h3>
        </div>
        <div class="p-4">
            <div class="flex justify-center">
                <div class="w-64 h-64">
                    <canvas id="resultatsStatus"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition par matières -->
    <div class="mt-6 overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-medium text-gray-800 dark:text-gray-200">Performances par matière</h3>
        </div>
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Matière
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Moyenne
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                % Réussite
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Min/Max
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse($resultatsParMatiere as $matiereId => $stats)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $stats['ec_nom'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $stats['ec_abr'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $stats['moyenne'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $stats['passRate'] }}%
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $stats['min'] }} / {{ $stats['max'] }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-gray-400">
                                Aucune donnée disponible
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Actions sur les résultats -->
    <div class="flex flex-wrap gap-2 mt-6">
        <a
            href="{{ route('resultats.provisoires', ['examen_id' => $examen_id]) }}"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Voir en détail
        </a>

        <button
            wire:click="exporterResultats"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Exporter en Excel
        </button>

        <button
            onclick="window.print()"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimer
        </button>
    </div>
    @else
    <div class="flex flex-col items-center justify-center p-8">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <p class="mt-4 text-gray-600 dark:text-gray-400">Aucune statistique disponible. Veuillez d'abord générer des résultats.</p>
    </div>
    @endif
</div>
