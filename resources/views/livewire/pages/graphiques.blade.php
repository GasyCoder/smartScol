{{-- resources/views/livewire/pages/graphiques.blade.php --}}

{{-- Graphique principal - Statistiques des résultats --}}
<div class="col-span-12 2xl:col-span-8">
    <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
        <div class="p-5 sm:p-6">
            <div class="flex items-center justify-between mb-3 gap-x-3">
                <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">
                    Statistiques des résultats – Session normale & Rattrapage
                </h6>
                <div class="relative dropdown">
                    <a href="#" data-offset="0,4" data-placement="bottom-end" data-rtl-placement="bottom-start" 
                       class="dropdown-toggle *:pointer-events-none peer relative inline-flex items-center text-xs font-bold tracking-wide text-slate-400 hover:text-slate-600 [&.show]:text-slate-600 transition-all duration-300">
                        <span>Année Universitaire</span>
                        <em class="text-base leading-4.5 ni ni-chevron-down"></em>
                    </a>
                    <div class="dropdown-menu absolute min-w-[180px] border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 rounded-md shadow hidden peer-[.show]:block z-[1000]">
                        <ul class="py-2">
                            @foreach($anneesUniversitaires as $annee)
                                <li class="group {{ $annee->id == $selectedYear ? 'active' : '' }}">
                                    <a wire:click="changeYear({{ $annee->id }})" 
                                       class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300 cursor-pointer">
                                        <span>{{ $annee->date_start->format('Y') }}-{{ $annee->date_end->format('Y') }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            
            {{-- Légende --}}
            <ul class="flex flex-wrap justify-center mb-3 gap-x-8 gap-y-2">
                <li>
                    <div class="flex items-center text-sm text-slate-400">
                        <span class="h-3 w-3 inline-block rounded-sm me-2 bg-[#6576ff]"></span>
                        <span>Admis – Session normale</span>
                    </div>
                </li>
                <li>
                    <div class="flex items-center text-sm text-slate-400">
                        <span class="h-3 w-3 inline-block rounded-sm me-2 bg-[#eb6459]"></span>
                        <span>Exclus – Session normale</span>
                    </div>
                </li>
                <li>
                    <div class="flex items-center text-sm text-slate-400">
                        <span class="inline-block w-3 h-3 bg-green-500 rounded-sm me-2"></span>
                        <span>Admis – Rattrapage</span>
                    </div>
                </li>
                <li>
                    <div class="flex items-center text-sm text-slate-400">
                        <span class="inline-block w-3 h-3 bg-yellow-400 rounded-sm me-2"></span>
                        <span>Exclus – Rattrapage</span>
                    </div>
                </li>
            </ul>
            
            {{-- Canvas du graphique --}}
            <div class="h-52">
                <canvas class="ecommerce-line-chart" id="salesStatistics"></canvas>
            </div>
            
            {{-- Échelle de dates --}}
            <div class="flex justify-between mt-2 ms-11">
                <div class="text-xs text-slate-400">{{ now()->subDays(29)->format('d M, Y') }}</div>
                <div class="text-xs text-slate-400">{{ now()->format('d M, Y') }}</div>
            </div>

            {{-- Bouton refresh avec état --}}
            <div class="flex justify-center mt-3">
                <button wire:click="refresh" 
                        class="px-4 py-2 text-xs bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200 flex items-center space-x-2"
                        wire:loading.class="opacity-50 cursor-not-allowed">
                    <svg wire:loading.remove wire:target="refresh" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <svg wire:loading wire:target="refresh" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span wire:loading.remove wire:target="refresh">Actualiser</span>
                    <span wire:loading wire:target="refresh">Actualisation...</span>
                </button>
            </div>
        </div>
    </div>
</div>


