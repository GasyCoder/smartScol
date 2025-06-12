@if(!empty($resultatsSession1) || (!empty($resultatsSession2) && $showSession2))
        <div class="mb-6">
            <div class="grid grid-cols-1 {{ $showSession2 ? 'lg:grid-cols-2' : '' }} gap-6">

                <!-- Stats Session 1 -->
                <div class="p-4 bg-white border border-gray-200 dark:bg-gray-800 rounded-xl dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center justify-center w-6 h-6 bg-blue-500 rounded-md">
                                <em class="text-white ni ni-graduation"></em>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Session 1</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">(Normale)</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                {{ $statistiquesSession1['taux_reussite'] ?? 0 }}%
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">réussite</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mb-3">
                        <div class="p-2 text-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                {{ $statistiquesSession1['total_etudiants'] ?? 0 }}
                            </div>
                            <div class="text-xs text-blue-600 dark:text-blue-400">Total</div>
                        </div>
                        <div class="p-2 text-center rounded-lg bg-green-50 dark:bg-green-900/20">
                            <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                {{ $statistiquesSession1['admis'] ?? 0 }}
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400">Admis</div>
                        </div>
                        <div class="p-2 text-center rounded-lg bg-orange-50 dark:bg-orange-900/20">
                            <div class="text-lg font-bold text-orange-600 dark:text-orange-400">
                                {{ $statistiquesSession1['rattrapage'] ?? 0 }}
                            </div>
                            <div class="text-xs text-orange-600 dark:text-orange-400">Rattrapage</div>
                        </div>
                    </div>

                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                        <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-1000 ease-out"
                            style="width: {{ $statistiquesSession1['taux_reussite'] ?? 0 }}%"></div>
                    </div>
                </div>

                <!-- Stats Session 2 -->
                @if($showSession2)
                    <div class="p-4 bg-white border border-gray-200 dark:bg-gray-800 rounded-xl dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center justify-center w-6 h-6 bg-green-500 rounded-md">
                                    <em class="text-white ni ni-repeat"></em>
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Session 2</h3>
                                <span class="text-xs text-gray-500 dark:text-gray-400">(Rattrapage)</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                    {{ $statistiquesSession2['taux_reussite'] ?? 0 }}%
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">réussite</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-2 mb-3">
                            <div class="p-2 text-center rounded-lg bg-green-50 dark:bg-green-900/20">
                                <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                    {{ $statistiquesSession2['total_etudiants'] ?? 0 }}
                                </div>
                                <div class="text-xs text-green-600 dark:text-green-400">Total</div>
                            </div>
                            <div class="p-2 text-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                                <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $statistiquesSession2['admis'] ?? 0 }}
                                </div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400">Admis</div>
                            </div>
                            <div class="p-2 text-center rounded-lg bg-red-50 dark:bg-red-900/20">
                                <div class="text-lg font-bold text-red-600 dark:text-red-400">
                                    {{ $statistiquesSession2['redoublant'] ?? 0 }}
                                </div>
                                <div class="text-xs text-red-600 dark:text-red-400">Redoublants</div>
                            </div>
                            <div class="p-2 text-center rounded-lg bg-gray-50 dark:bg-gray-900/20">
                                <div class="text-lg font-bold text-gray-600 dark:text-gray-400">
                                    {{ $statistiquesSession2['exclus'] ?? 0 }}
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Exclus</div>
                            </div>
                        </div>

                        <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                            <div class="bg-green-500 h-1.5 rounded-full transition-all duration-1000 ease-out"
                                style="width: {{ $statistiquesSession2['taux_reussite'] ?? 0 }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
