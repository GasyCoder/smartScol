<div class="mb-6">
    <!-- Onglets améliorés sans gradients -->
    <div class="inline-flex p-3 space-x-3 border shadow-lg bg-white dark:bg-gray-800 rounded-2xl border-gray-200 dark:border-gray-700">

        {{-- Onglet Session 1 --}}
        @if(!empty($resultatsSession1))
            <button wire:click="setActiveTab('session1')"
                    wire:loading.attr="disabled"
                    wire:target="setActiveTab('session1')"
                    class="group relative flex items-center px-8 py-4 text-sm font-semibold rounded-xl transition-all duration-300 ease-out transform hover:scale-[1.02] hover:shadow-lg disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none {{ $activeTab === 'session1' ? 'bg-blue-600 text-white shadow-xl shadow-blue-500/30 ring-2 ring-blue-300 dark:ring-blue-500' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30' }}">

                <div class="relative flex items-center space-x-3">
                    {{-- Container icône avec animation --}}
                    <div class="relative">
                        {{-- Icône normale --}}
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ $activeTab === 'session1' ? 'bg-white/20' : 'bg-blue-100 dark:bg-blue-900/50 group-hover:bg-blue-200 dark:group-hover:bg-blue-800/70' }} transition-all duration-300"
                            wire:loading.remove wire:target="setActiveTab('session1')">
                            <em class="text-lg ni ni-check-circle {{ $activeTab === 'session1' ? 'text-white' : 'text-blue-600 dark:text-blue-400' }}"></em>
                        </div>
                        
                        {{-- Spinner de chargement --}}
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ $activeTab === 'session1' ? 'bg-white/20' : 'bg-blue-100 dark:bg-blue-900/50' }}"
                            wire:loading wire:target="setActiveTab('session1')">
                            <svg class="animate-spin h-5 w-5 {{ $activeTab === 'session1' ? 'text-white' : 'text-blue-600 dark:text-blue-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base">Session 1</span>
                        <span class="text-xs opacity-80 font-medium">Session Normale</span>
                    </div>

                    {{-- Badge avec meilleur design --}}
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1.5 text-xs font-bold {{ $activeTab === 'session1' ? 'bg-white/25 text-white border border-white/30' : 'bg-blue-100 text-blue-800 border border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700' }} rounded-full">
                            {{ count($resultatsSession1) }} étudiants
                        </span>

                        {{-- Indicateur de statut avec icônes améliorées --}}
                        @if(($deliberationStatus['session1'] ?? false))
                            <div class="flex items-center justify-center w-6 h-6 {{ $activeTab === 'session1' ? 'bg-green-500/30' : 'bg-green-100 dark:bg-green-900/40' }} rounded-full" title="Session délibérée">
                                <em class="text-xs {{ $activeTab === 'session1' ? 'text-green-200' : 'text-green-600 dark:text-green-400' }} ni ni-shield-check"></em>
                            </div>
                        @else
                            <div class="flex items-center justify-center w-6 h-6 {{ $activeTab === 'session1' ? 'bg-orange-500/30' : 'bg-orange-100 dark:bg-orange-900/40' }} rounded-full" title="Session en cours">
                                <em class="text-xs {{ $activeTab === 'session1' ? 'text-orange-200' : 'text-orange-600 dark:text-orange-400' }} ni ni-clock"></em>
                            </div>
                        @endif
                    </div>
                </div>
            </button>
        @endif

        {{-- Onglet Session 2 --}}
        @if($showSession2 && !empty($resultatsSession2))
            <button wire:click="setActiveTab('session2')"
                    wire:loading.attr="disabled"
                    wire:target="setActiveTab('session2')"
                    class="group relative flex items-center px-8 py-4 text-sm font-semibold rounded-xl transition-all duration-300 ease-out transform hover:scale-[1.02] hover:shadow-lg disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none {{ $activeTab === 'session2' ? 'bg-green-600 text-white shadow-xl shadow-green-500/30 ring-2 ring-green-300 dark:ring-green-500' : 'text-gray-700 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30' }}">

                <div class="relative flex items-center space-x-3">
                    {{-- Container icône avec animation --}}
                    <div class="relative">
                        {{-- Icône normale --}}
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ $activeTab === 'session2' ? 'bg-white/20' : 'bg-green-100 dark:bg-green-900/50 group-hover:bg-green-200 dark:group-hover:bg-green-800/70' }} transition-all duration-300"
                            wire:loading.remove wire:target="setActiveTab('session2')">
                            <em class="text-lg ni ni-reload {{ $activeTab === 'session2' ? 'text-white' : 'text-green-600 dark:text-green-400' }}"></em>
                        </div>
                        
                        {{-- Spinner de chargement --}}
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ $activeTab === 'session2' ? 'bg-white/20' : 'bg-green-100 dark:bg-green-900/50' }}"
                            wire:loading wire:target="setActiveTab('session2')">
                            <svg class="animate-spin h-5 w-5 {{ $activeTab === 'session2' ? 'text-white' : 'text-green-600 dark:text-green-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base">Session 2</span>
                        <span class="text-xs opacity-80 font-medium">Rattrapage</span>
                    </div>

                    {{-- Badge avec meilleur design --}}
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1.5 text-xs font-bold {{ $activeTab === 'session2' ? 'bg-white/25 text-white border border-white/30' : 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900/40 dark:text-green-300 dark:border-green-700' }} rounded-full">
                            {{ count($resultatsSession2) }} étudiants
                        </span>

                        {{-- Indicateur de statut --}}
                        @if(($deliberationStatus['session2'] ?? false))
                            <div class="flex items-center justify-center w-6 h-6 {{ $activeTab === 'session2' ? 'bg-green-500/30' : 'bg-green-100 dark:bg-green-900/40' }} rounded-full" title="Session délibérée">
                                <em class="text-xs {{ $activeTab === 'session2' ? 'text-green-200' : 'text-green-600 dark:text-green-400' }} ni ni-shield-check"></em>
                            </div>
                        @else
                            <div class="flex items-center justify-center w-6 h-6 {{ $activeTab === 'session2' ? 'bg-orange-500/30' : 'bg-orange-100 dark:bg-orange-900/40' }} rounded-full" title="Session en attente">
                                <em class="text-xs {{ $activeTab === 'session2' ? 'text-orange-200' : 'text-orange-600 dark:text-orange-400' }} ni ni-clock"></em>
                            </div>
                        @endif
                    </div>
                </div>
            </button>
        @endif

        {{-- Onglet Paramètres/Délibération --}}
        @if(!empty($resultatsSession1) || !empty($resultatsSession2))
            <button wire:click="setActiveTab('simulation')"
                    wire:loading.attr="disabled"
                    wire:target="setActiveTab('simulation')"
                    class="group relative flex items-center px-8 py-4 text-sm font-semibold rounded-xl transition-all duration-300 ease-out transform hover:scale-[1.02] hover:shadow-lg disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none {{ $activeTab === 'simulation' ? 'bg-purple-600 text-white shadow-xl shadow-purple-500/30 ring-2 ring-purple-300 dark:ring-purple-500' : 'text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/30' }}">

                <div class="relative flex items-center space-x-3">
                    {{-- Container icône --}}
                    <div class="relative">
                        {{-- Icône normale --}}
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ $activeTab === 'simulation' ? 'bg-white/20' : 'bg-purple-100 dark:bg-purple-900/50 group-hover:bg-purple-200 dark:group-hover:bg-purple-800/70' }} transition-all duration-300"
                            wire:loading.remove wire:target="setActiveTab('simulation')">
                            <em class="text-lg ni ni-setting {{ $activeTab === 'simulation' ? 'text-white' : 'text-purple-600 dark:text-purple-400' }}"></em>
                        </div>
                        
                        {{-- Spinner de chargement --}}
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg {{ $activeTab === 'simulation' ? 'bg-white/20' : 'bg-purple-100 dark:bg-purple-900/50' }}"
                            wire:loading wire:target="setActiveTab('simulation')">
                            <svg class="animate-spin h-5 w-5 {{ $activeTab === 'simulation' ? 'text-white' : 'text-purple-600 dark:text-purple-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base">Paramètres</span>
                        <span class="text-xs opacity-80 font-medium">Délibération</span>
                    </div>

                    {{-- Badge sessions disponibles --}}
                    <span class="px-3 py-1.5 text-xs font-bold {{ $activeTab === 'simulation' ? 'bg-white/25 text-white border border-white/30' : 'bg-purple-100 text-purple-800 border border-purple-200 dark:bg-purple-900/40 dark:text-purple-300 dark:border-purple-700' }} rounded-full">
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
    
    {{-- Message de chargement global --}}
    <div wire:loading wire:target="setActiveTab" class="mt-6 text-center">
        <div class="inline-flex items-center px-6 py-3 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-xl shadow-lg dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700">
            <div class="relative mr-3">
                <svg class="animate-spin h-5 w-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <span class="font-semibold">Changement d'onglet en cours...</span>
        </div>
    </div>
</div>