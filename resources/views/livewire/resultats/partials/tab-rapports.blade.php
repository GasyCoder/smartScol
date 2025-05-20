    <!-- Onglet 2: rapport détails -->
    <div id="content-rapport" class="tab-content" style="{{ $activeTab !== 'rapport' ? 'display: none;' : '' }}">
        @if(count($rapportCoherence) > 0)
        <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-medium text-gray-800 dark:text-gray-200">
                    Rapport détaillé de vérification
                </h3>
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
                                    État
                                </th>
                                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                    Problèmes
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach($rapportCoherence as $rapport)
                            <tr class="{{ $rapport['complet'] ? '' : 'bg-red-50 dark:bg-red-900/10' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $rapport['ec_nom'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $rapport['ec_abr'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $rapport['manchettes_count'] }}/{{ $rapport['total_etudiants'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $rapport['copies_count'] }}/{{ $rapport['codes_count'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 text-xs font-semibold leading-5 {{ $rapport['complet'] ? 'text-green-800 bg-green-100 dark:bg-green-800/30 dark:text-green-200' : 'text-red-800 bg-red-100 dark:bg-red-800/30 dark:text-red-200' }} rounded-full">
                                        {{ $rapport['complet'] ? 'Complet' : 'Incomplet' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        @if(!$rapport['complet'])
                                            @if($rapport['codes_sans_manchettes']['count'] > 0)
                                                <div class="mb-1 text-red-600 dark:text-red-400">• Codes sans manchettes: {{ implode(', ', $rapport['codes_sans_manchettes']['codes']) }}</div>
                                            @endif
                                            @if($rapport['codes_sans_copies']['count'] > 0)
                                                <div class="mb-1 text-red-600 dark:text-red-400">• Codes sans copies: {{ implode(', ', $rapport['codes_sans_copies']['codes']) }}</div>
                                            @endif
                                            @if($rapport['etudiants_sans_manchette'] > 0)
                                                <div class="text-red-600 dark:text-red-400">• {{ $rapport['etudiants_sans_manchette'] }} étudiants sans manchette</div>
                                            @endif
                                        @else
                                            <span class="text-green-600 dark:text-green-400">Aucun problème détecté</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
