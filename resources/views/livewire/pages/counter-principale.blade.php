{{-- livewire.pages.counter-principale - 5 cartes avec indicateurs délibération --}}
<div class="col-span-12 sm:col-span-6 lg:col-span-4 xl:col-span-2 2xl:col-span-2">
    <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
        <div class="p-4 sm:p-5">
            <h6 class="text-sm font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">
                Étudiants inscrits
            </h6>
            <div class="flex items-center justify-between my-2">
                <div class="text-2xl font-medium lg:text-3xl text-slate-700 dark:text-white">
                    {{ number_format($totalEtudiants) }}
                </div>
                <div class="w-20 h-8">
                    <canvas class="ecommerce-line-chart-small" id="chartEtudiantsInscrits"></canvas>
                </div>
            </div>
            <div class="text-xs">
                @if($progressionEtudiants >= 0)
                    <span class="text-green-600">
                        <em class="icon ni ni-arrow-long-up"></em>+{{ $progressionEtudiants }}%
                    </span>
                @else
                    <span class="text-red-600">
                        <em class="icon ni ni-arrow-long-down"></em>{{ $progressionEtudiants }}%
                    </span>
                @endif
                <span> cette année</span>
            </div>
        </div>
    </div>
</div><!-- col -->

<div class="col-span-12 sm:col-span-6 lg:col-span-6 xl:col-span-3 2xl:col-span-3">
    <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
        <div class="p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <h6 class="text-sm font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">
                    1er session
                </h6>
                @if(isset($sessionDeliberee) && $sessionDeliberee)
                    <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full dark:bg-green-900/20 dark:text-green-300">
                        Après délibération
                    </span>
                @else
                    <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/20 dark:text-blue-300">
                        Automatique
                    </span>
                @endif
            </div>
            <div class="flex items-center justify-between my-2">
                <div class="text-2xl font-medium lg:text-3xl text-slate-700 dark:text-white">
                    {{ number_format($etudiantsAdmis) }}
                </div>
                <div class="w-20 h-8">
                    <canvas class="ecommerce-line-chart-small" id="chartEtudiantsAdmis"></canvas>
                </div>
            </div>
            <div class="text-xs">
                @if($progressionAdmis >= 0)
                    <span class="text-green-600">
                        <em class="icon ni ni-arrow-long-up"></em>+{{ $progressionAdmis }}%
                    </span>
                @else
                    <span class="text-red-600">
                        <em class="icon ni ni-arrow-long-down"></em>{{ $progressionAdmis }}%
                    </span>
                @endif
                <span> vs. session précédente</span>
            </div>
        </div>
    </div>
</div><!-- col -->

<div class="col-span-12 sm:col-span-6 lg:col-span-6 xl:col-span-3 2xl:col-span-3">
    <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
        <div class="p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <h6 class="text-sm font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">
                    Rattrapage
                </h6>
                @if(isset($sessionDeliberee) && $sessionDeliberee)
                    <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full dark:bg-green-900/20 dark:text-green-300">
                        Après délibération
                    </span>
                @else
                    <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/20 dark:text-blue-300">
                        Automatique
                    </span>
                @endif
            </div>
            <div class="flex items-center justify-between my-2">
                <div class="text-2xl font-medium lg:text-3xl text-slate-700 dark:text-white">
                    {{ number_format($rattrapage) }}
                </div>
                <div class="w-20 h-8">
                    <canvas class="ecommerce-line-chart-small" id="chartRattrapage"></canvas>
                </div>
            </div>
            <div class="text-xs">
                @if(isset($progressionRattrapage))
                    @if($progressionRattrapage >= 0)
                        <span class="text-orange-600">
                            <em class="icon ni ni-arrow-long-up"></em>+{{ $progressionRattrapage }}%
                        </span>
                        <span> vs. session précédente</span>
                    @else
                        <span class="text-green-600">
                            <em class="icon ni ni-arrow-long-down"></em>{{ abs($progressionRattrapage) }}%
                        </span>
                        <span> vs. session précédente</span>
                    @endif
                @else
                    <span class="text-orange-600">
                        <em class="icon ni ni-alert"></em>{{ number_format(($rattrapage / max($totalEtudiants, 1)) * 100, 1) }}%
                    </span>
                    <span> des étudiants</span>
                @endif
            </div>
        </div>
    </div>
</div><!-- col -->

<div class="col-span-12 sm:col-span-6 lg:col-span-4 xl:col-span-2 2xl:col-span-2">
    <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
        <div class="p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <h6 class="text-sm font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">
                    Redoublants
                </h6>
                @if(isset($sessionDeliberee) && $sessionDeliberee)
                    <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full dark:bg-green-900/20 dark:text-green-300">
                        Après délibération
                    </span>
                @else
                    <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/20 dark:text-blue-300">
                        Automatique
                    </span>
                @endif
            </div>
            <div class="flex items-center justify-between my-2">
                <div class="text-2xl font-medium lg:text-3xl text-slate-700 dark:text-white">
                    {{ number_format($redoublants) }}
                </div>
                <div class="w-20 h-8">
                    <canvas class="ecommerce-line-chart-small" id="chartRedoublants"></canvas>
                </div>
            </div>
            <div class="text-xs">
                @if(isset($progressionRedoublants))
                    @if($progressionRedoublants >= 0)
                        <span class="text-red-600">
                            <em class="icon ni ni-arrow-long-up"></em>+{{ $progressionRedoublants }}%
                        </span>
                    @else
                        <span class="text-green-600">
                            <em class="icon ni ni-arrow-long-down"></em>{{ abs($progressionRedoublants) }}%
                        </span>
                    @endif
                    <span> vs. session précédente</span>
                @else
                    <span class="text-purple-600">
                        <em class="icon ni ni-info"></em>{{ number_format(($redoublants / max($totalEtudiants, 1)) * 100, 1) }}%
                    </span>
                    <span> des étudiants</span>
                @endif
            </div>
        </div>
    </div>
</div><!-- col -->


<div class="col-span-12 sm:col-span-6 lg:col-span-4 xl:col-span-2 2xl:col-span-2">
    <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
        <div class="p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <h6 class="text-sm font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">
                    Étudiants exclus
                </h6>
                @if(isset($sessionDeliberee) && $sessionDeliberee)
                    <span class="px-2 py-1 text-xs text-green-700 bg-green-100 rounded-full dark:bg-green-900/20 dark:text-green-300">
                        Après délibération
                    </span>
                @else
                    <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/20 dark:text-blue-300">
                        Automatique
                    </span>
                @endif
            </div>
            <div class="flex items-center justify-between my-2">
                <div class="text-2xl font-medium lg:text-3xl text-slate-700 dark:text-white">
                    {{ number_format($exclus) }}
                </div>
                <div class="w-20 h-8">
                    <canvas class="ecommerce-line-chart-small" id="chartExclus"></canvas>
                </div>
            </div>
            <div class="text-xs">
                @if($progressionExclus >= 0)
                    <span class="text-red-600">
                        <em class="icon ni ni-arrow-long-up"></em>+{{ $progressionExclus }}%
                    </span>
                @else
                    <span class="text-green-600">
                        <em class="icon ni ni-arrow-long-down"></em>{{ abs($progressionExclus) }}%
                    </span>
                @endif
                <span> vs. session passée</span>
            </div>
        </div>
    </div>
</div><!-- col -->
