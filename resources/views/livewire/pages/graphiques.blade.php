<div class="col-span-12 2xl:col-span-6">
    <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
        <div class="p-5 sm:p-6">
            <div class="flex items-center justify-between mb-3 gap-x-3">
                <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">Statistiques des résultats – Session normale & Rattrapage</h6>
                <div class="relative dropdown">
                    <a href="#" data-offset="0,4" data-placement="bottom-end" data-rtl-placement="bottom-start" class="dropdown-toggle *:pointer-events-none peer relative inline-flex items-center text-xs font-bold tracking-wide text-slate-400 hover:text-slate-600 [&.show]:text-slate-600 transition-all duration-300">
                        <span>Hebdomadaire</span>
                        <em class="text-base leading-4.5 ni ni-chevron-down"></em>
                    </a>
                    <div class="dropdown-menu absolute min-w-[180px] border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 rounded-md shadow hidden peer-[.show]:block z-[1000]">
                        <ul class="py-2">
                            <li class="group"><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Quotidien</span></a></li>
                            <li class="group active"><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Hebdomadaire</span></a></li>
                            <li class="group"><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Mensuel</span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
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
            <div class="h-52">
                <canvas class="ecommerce-line-chart" id="salesStatistics"></canvas>
            </div>
            <div class="flex justify-between mt-2 ms-11">
                <div class="text-xs text-slate-400">01 Jan, 2025</div>
                <div class="text-xs text-slate-400">30 Jan, 2025</div>
            </div>
        </div>
    </div>
</div>




<div class="col-span-12 md:col-span-6 2xl:col-span-3">
    <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900">
        <div class="p-5 sm:p-6">
            <h6 class="mb-6 text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">Répartition des Étudiants</h6>
            <div class="h-44">
                <canvas class="ecommerce-doughnut-chart" id="orderStatistics"></canvas>
            </div>
            <ul class="flex flex-wrap justify-center mt-5 gap-x-8 gap-y-2">
                <li>
                    <div class="flex items-center text-sm text-slate-400">
                        <span class="h-3 w-3 inline-block rounded-sm me-2 bg-[#816bff]"></span>
                        <span>Admis</span>
                    </div>
                </li>
                <li>
                    <div class="flex items-center text-sm text-slate-400">
                        <span class="h-3 w-3 inline-block rounded-sm me-2 bg-[#13c9f2]"></span>
                        <span>Redoublants</span>
                    </div>
                </li>
                <li>
                    <div class="flex items-center text-sm text-slate-400">
                        <span class="h-3 w-3 inline-block rounded-sm me-2 bg-[#ff82b7]"></span>
                        <span>Exclus</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div><!-- col -->

