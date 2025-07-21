{{-- Vue principale - Votre structure exacte avec modifications pour simulation --}}
<div class="p-6 bg-white rounded-lg shadow-sm dark:bg-gray-800">
    <!-- Header result -->
    @include('livewire.resultats.sessons.header-result')

    <!-- Statistiques compactes -->
    @include('livewire.resultats.sessons.statistique-result')

    <!-- Onglets modernes améliorés -->
    <div class="mb-6">
        <div class="inline-flex p-2 space-x-2 border shadow-inner bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800/80 dark:to-gray-700/80 rounded-2xl border-gray-200/50 dark:border-gray-600/50">

            <!-- Onglet Session 1 amélioré -->
            <button wire:click="$set('activeTab', 'session1')"
                    class="group relative flex items-center px-6 py-3.5 text-sm font-medium rounded-xl transition-all duration-300 transform hover:scale-[1.02] {{ $activeTab === 'session1' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-lg shadow-blue-500/20 ring-2 ring-blue-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80' }}">

                <!-- Indicateur actif animé -->
                @if($activeTab === 'session1')
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 rounded-xl"></div>
                @endif

                <div class="relative flex items-center">
                    <div class="mr-3 p-1.5 rounded-lg {{ $activeTab === 'session1' ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-gray-600 group-hover:bg-gray-200 dark:group-hover:bg-gray-500' }} transition-colors">
                        <em class="text-lg ni ni-graduation"></em>
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="font-semibold">Session 1</span>
                        <span class="text-xs opacity-75">Session Normale</span>
                    </div>

                    @if(!empty($resultatsSession1))
                        <span class="ml-3 px-2.5 py-1 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full font-medium border border-blue-200 dark:border-blue-700">
                            {{ count($resultatsSession1) }}
                        </span>
                    @endif

                    <!-- Indicateur de verrouillage stylisé -->
                    <div class="p-1 ml-2 bg-red-100 rounded-full dark:bg-red-900/30" title="Session verrouillée">
                        <em class="text-xs text-red-500 ni ni-lock dark:text-red-400"></em>
                    </div>
                </div>
            </button>

            <!-- Onglet Session 2 amélioré -->
            @if($showSession2)
                <button wire:click="$set('activeTab', 'session2')"
                        class="group relative flex items-center px-6 py-3.5 text-sm font-medium rounded-xl transition-all duration-300 transform hover:scale-[1.02] {{ $activeTab === 'session2' ? 'bg-white dark:bg-gray-700 text-green-600 dark:text-green-400 shadow-lg shadow-green-500/20 ring-2 ring-green-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80' }}">

                    @if($activeTab === 'session2')
                        <div class="absolute inset-0 bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-xl"></div>
                    @endif

                    <div class="relative flex items-center">
                        <div class="mr-3 p-1.5 rounded-lg {{ $activeTab === 'session2' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-gray-100 dark:bg-gray-600 group-hover:bg-gray-200 dark:group-hover:bg-gray-500' }} transition-colors">
                            <em class="text-lg ni ni-repeat"></em>
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="font-semibold">Session 2</span>
                            <span class="text-xs opacity-75">Rattrapage</span>
                        </div>

                        @if(!empty($resultatsSession2))
                            <span class="ml-3 px-2.5 py-1 text-xs bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full font-medium border border-green-200 dark:border-green-700">
                                {{ count($resultatsSession2) }}
                            </span>
                        @endif

                        <!-- Indicateur de verrouillage pour session 2 -->
                        <div class="p-1 ml-2 bg-red-100 rounded-full dark:bg-red-900/30" title="Session verrouillée">
                            <em class="text-xs text-red-500 ni ni-lock dark:text-red-400"></em>
                        </div>
                    </div>
                </button>
            @endif

            <!-- Onglet Simulation amélioré - MAINTENANT DISPONIBLE SI AU MOINS UNE SESSION A DES RÉSULTATS -->
            @if(!empty($resultatsSession1) || !empty($resultatsSession2))
                <button wire:click="$set('activeTab', 'simulation')"
                        class="group relative flex items-center px-6 py-3.5 text-sm font-medium rounded-xl transition-all duration-300 transform hover:scale-[1.02] {{ $activeTab === 'simulation' ? 'bg-white dark:bg-gray-700 text-purple-600 dark:text-purple-400 shadow-lg shadow-purple-500/20 ring-2 ring-purple-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80' }}">

                    @if($activeTab === 'simulation')
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-violet-500/10 rounded-xl"></div>
                    @endif

                    <div class="relative flex items-center">
                        <div class="mr-3 p-1.5 rounded-lg {{ $activeTab === 'simulation' ? 'bg-purple-100 dark:bg-purple-900/30' : 'bg-gray-100 dark:bg-gray-600 group-hover:bg-gray-200 dark:group-hover:bg-gray-500' }} transition-colors">
                            <em class="text-lg ni ni-setting"></em>
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="font-semibold">Simulation</span>
                            <span class="text-xs opacity-75">Délibération</span>
                        </div>

                        <!-- Badge indiquant les sessions disponibles pour simulation -->
                        <span class="ml-3 px-2.5 py-1 text-xs bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded-full font-medium border border-purple-200 dark:border-purple-700">
                            @if(!empty($resultatsSession1) && !empty($resultatsSession2))
                                S1+S2
                            @elseif(!empty($resultatsSession1))
                                S1
                            @else
                                S2
                            @endif
                        </span>
                    </div>
                </button>
            @endif
        </div>
    </div>

    {{-- Contenu des onglets --}}
    <div class="tab-content">
        <div class="animate-fadeIn">
            @include('livewire.resultats.sessons.normale')
            @include('livewire.resultats.sessons.rattrapage')
            @include('livewire.resultats.sessons.simulation')  <!-- déliberation -->
        </div>
    </div>
</div>

