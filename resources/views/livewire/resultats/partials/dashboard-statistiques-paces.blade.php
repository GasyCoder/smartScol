{{-- ===== DASHBOARD STATISTIQUES OPTIMISÉ ===== --}}
<div class="space-y-4">
    {{-- Première ligne : Statistiques générales COMPACTE --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        {{-- Total Inscrits --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md p-3 text-white hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium opacity-80 mb-1">Inscrits</div>
                    <div class="text-2xl font-bold">{{ $statistiquesDetailes['total_inscrits'] ?? 0 }}</div>
                </div>
                <svg class="w-8 h-8 opacity-30" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                </svg>
            </div>
        </div>

        {{-- Présents --}}
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-md p-3 text-white hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium opacity-80 mb-1">Présents</div>
                    <div class="text-2xl font-bold">{{ $statistiquesDetailes['total_presents'] ?? 0 }}</div>
                    <div class="text-xs opacity-80 mt-0.5">{{ $statistiquesDetailes['taux_presence'] ?? 0 }}%</div>
                </div>
                <svg class="w-8 h-8 opacity-30" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>

        {{-- Absents --}}
        <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg shadow-md p-3 text-white hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium opacity-80 mb-1">Absents</div>
                    <div class="text-2xl font-bold">{{ $statistiquesDetailes['total_absents'] ?? 0 }}</div>
                </div>
                <svg class="w-8 h-8 opacity-30" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>

        {{-- Redoublants --}}
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-md p-3 text-white hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium opacity-80 mb-1">Redoublants</div>
                    <div class="text-2xl font-bold">{{ $statistiquesDetailes['etudiants_redoublants'] ?? 0 }}</div>
                    <div class="text-xs opacity-70 mt-0.5">≤ 38999</div>
                </div>
                <svg class="w-8 h-8 opacity-30" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>

        {{-- Nouveaux --}}
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-md p-3 text-white hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium opacity-80 mb-1">Nouveaux</div>
                    <div class="text-2xl font-bold">{{ $statistiquesDetailes['etudiants_nouveaux'] ?? 0 }}</div>
                    <div class="text-xs opacity-70 mt-0.5">≥ 39000</div>
                </div>
                <svg class="w-8 h-8 opacity-30" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Deuxième ligne : Résultats par décision + Répartition --}}
    <div class="rounded-lg shadow-md p-4">
        {{-- Cartes décisions avec pourcentages --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
            @php
                $totalInscrits = max($statistiquesDetailes['total_inscrits'] ?? 1, 1);
                $pctAdmis = (($statistiquesDetailes['admis'] ?? 0) / $totalInscrits) * 100;
                $pctRedoublant = (($statistiquesDetailes['redoublant_autorises'] ?? 0) / $totalInscrits) * 100;
                $pctExclus = (($statistiquesDetailes['exclus'] ?? 0) / $totalInscrits) * 100;
            @endphp

            {{-- Admis --}}
            <div class="group bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/10 rounded-lg hover:shadow-md transition-all p-3 border-l-4 border-green-500 cursor-pointer"
                wire:click="changerFiltre('admis')">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">ADMIS</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ $statistiquesDetailes['admis'] ?? 0 }}
                            </span>
                            <span class="text-sm font-bold text-green-600 dark:text-green-400 opacity-75">
                                ({{ round($pctAdmis, 1) }}%)
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Sur {{ $statistiquesDetailes['total_inscrits'] ?? 0 }} inscrits
                        </div>
                    </div>
                    <svg class="w-10 h-10 text-green-200 dark:text-green-900/30 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            {{-- Redoublants --}}
            <div class="group bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-900/10 rounded-lg hover:shadow-md transition-all p-3 border-l-4 border-orange-500 cursor-pointer"
                wire:click="changerFiltre('redoublant')">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">REDOUBLANTS</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                {{ $statistiquesDetailes['redoublant_autorises'] ?? 0 }}
                            </span>
                            <span class="text-sm font-bold text-orange-600 dark:text-orange-400 opacity-75">
                                ({{ round($pctRedoublant, 1) }}%)
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Autorisés à redoubler
                        </div>
                    </div>
                    <svg class="w-10 h-10 text-orange-200 dark:text-orange-900/30 group-hover:rotate-180 transition-transform duration-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            {{-- Exclus --}}
            <div class="group bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/10 rounded-lg hover:shadow-md transition-all p-3 border-l-4 border-red-500 cursor-pointer"
                wire:click="changerFiltre('exclus')">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">EXCLUS</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-red-600 dark:text-red-400">
                                {{ $statistiquesDetailes['exclus'] ?? 0 }}
                            </span>
                            <span class="text-sm font-bold text-red-600 dark:text-red-400 opacity-75">
                                ({{ round($pctExclus, 1) }}%)
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Non admis
                        </div>
                    </div>
                    <svg class="w-10 h-10 text-red-200 dark:text-red-900/30 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Barre de répartition compacte --}}
        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Répartition globale</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Sur {{ $statistiquesDetailes['total_inscrits'] ?? 0 }} inscrits
                </span>
            </div>
            <div class="flex h-2 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 shadow-inner">
                @php
                    $totalInscrits = max($statistiquesDetailes['total_inscrits'] ?? 1, 1);
                    $pctAdmis = (($statistiquesDetailes['admis'] ?? 0) / $totalInscrits) * 100;
                    $pctRedoublant = (($statistiquesDetailes['redoublant_autorises'] ?? 0) / $totalInscrits) * 100;
                    $pctExclus = (($statistiquesDetailes['exclus'] ?? 0) / $totalInscrits) * 100;
                    $pctAbsents = (($statistiquesDetailes['total_absents'] ?? 0) / $totalInscrits) * 100;
                @endphp
                
                {{-- Admis --}}
                <div class="bg-green-500 hover:bg-green-600 transition-all cursor-pointer" 
                    style="width: {{ $pctAdmis }}%"
                    title="Admis: {{ $statistiquesDetailes['admis'] ?? 0 }} ({{ round($pctAdmis, 1) }}%)"
                    wire:click="changerFiltre('admis')"></div>
                
                {{-- Redoublants --}}
                <div class="bg-orange-500 hover:bg-orange-600 transition-all cursor-pointer" 
                    style="width: {{ $pctRedoublant }}%"
                    title="Redoublants: {{ $statistiquesDetailes['redoublant_autorises'] ?? 0 }} ({{ round($pctRedoublant, 1) }}%)"
                    wire:click="changerFiltre('redoublant')"></div>
                
                {{-- Exclus --}}
                <div class="bg-red-500 hover:bg-red-600 transition-all cursor-pointer" 
                    style="width: {{ $pctExclus }}%"
                    title="Exclus: {{ $statistiquesDetailes['exclus'] ?? 0 }} ({{ round($pctExclus, 1) }}%)"
                    wire:click="changerFiltre('exclus')"></div>
                
                {{-- Absents (en gris) --}}
                <div class="bg-gray-400 hover:bg-gray-500 transition-all cursor-default" 
                    style="width: {{ $pctAbsents }}%"
                    title="Absents: {{ $statistiquesDetailes['total_absents'] ?? 0 }} ({{ round($pctAbsents, 1) }}%)"></div>
            </div>
            <div class="flex items-center justify-center gap-3 mt-1.5 flex-wrap">
                <button wire:click="changerFiltre('admis')" class="flex items-center gap-1 text-xs hover:opacity-75 transition">
                    <div class="w-2.5 h-2.5 rounded-sm bg-green-500"></div>
                    <span class="text-gray-600 dark:text-gray-400 font-medium">{{ round($pctAdmis, 1) }}%</span>
                </button>
                <button wire:click="changerFiltre('redoublant')" class="flex items-center gap-1 text-xs hover:opacity-75 transition">
                    <div class="w-2.5 h-2.5 rounded-sm bg-orange-500"></div>
                    <span class="text-gray-600 dark:text-gray-400 font-medium">{{ round($pctRedoublant, 1) }}%</span>
                </button>
                <button wire:click="changerFiltre('exclus')" class="flex items-center gap-1 text-xs hover:opacity-75 transition">
                    <div class="w-2.5 h-2.5 rounded-sm bg-red-500"></div>
                    <span class="text-gray-600 dark:text-gray-400 font-medium">{{ round($pctExclus, 1) }}%</span>
                </button>
                <span class="flex items-center gap-1 text-xs">
                    <div class="w-2.5 h-2.5 rounded-sm bg-gray-400"></div>
                    <span class="text-gray-500 dark:text-gray-400">{{ round($pctAbsents, 1) }}% absents</span>
                </span>
            </div>
        </div>
        
    </div>
</div>