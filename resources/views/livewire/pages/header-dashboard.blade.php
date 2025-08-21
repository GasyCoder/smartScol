<div class="relative flex items-center justify-between pb-5 md:pb-7">
    <div>
        <h3 class="mb-2 text-2xl font-bold tracking-tight font-heading lg:text-3xl leading-tighter text-slate-700 dark:text-white">Tableau de bord - SmartScol</h3>
        <p class="text-slate-400">Suivi global des inscriptions, résultats et statistiques des étudiants.</p>
    </div>
    <div>
        <button data-target="#pageOptions" class="class-toggle sm:hidden *:pointer-events-none -me-2 inline-flex items-center justify-center isolate relative h-9 w-9 px-1.5 before:content-[''] before:absolute before:-z-[1] before:h-5 before:w-5 hover:before:h-10 hover:before:w-10 [&.active]:before:h-10 [&.active]:before:w-10 before:rounded-full before:opacity-0 hover:before:opacity-100 [&.active]:before:opacity-100 before:transition-all before:duration-300 before:-translate-x-1/2  before:-translate-y-1/2 before:top-1/2 before:left-1/2 before:bg-gray-200 dark:before:bg-gray-900">
            <em class="text-xl text-slate-600 dark:text-slate-300 ni ni-more-v"></em>
        </button>
        <div id="pageOptions" class="absolute sm:relative start-0 end-0 top-full -mx-3.5 bg-white dark:bg-gray-950 sm:bg-transparent sm:dark:bg-transparent max-sm:shadow max-sm:dark:shadow-gray-800 opacity-0 invisible sm:opacity-100 sm:visible [&.active]:opacity-100 [&.active]:visible z-[1000]">
            <ul class="flex items-center gap-4 px-3.5 py-5 sm:py-0">
                <li>
                    <div class="relative dropdown">
                        <a href="#" data-offset="0,4" data-placement="bottom-end" data-rtl-placement="bottom-start" class="dropdown-toggle *:pointer-events-none peer relative inline-flex items-center text-center align-middle text-sm font-bold leading-4.5 rounded px-5 py-2 tracking-wide border border-gray-300 dark:border-gray-900 text-slate-600 dark:text-slate-200 bg-white dark:bg-gray-900 hover:bg-slate-600 [&.show]:bg-slate-600 hover:dark:bg-gray-800 [&.show]:dark:bg-gray-800 hover:text-white [&.show]:text-white hover:dark:text-white [&.show]:dark:text-white hover:border-slate-600 hover:dark:border-gray-800 [&.show]:dark:border-gray-800 active:bg-slate-700 active:text-white active:border-slate-600 transition-all duration-300">
                            <em class="text-xl leading-4.5 me-3 hidden sm:inline ni ni-calender-date"></em>
                            <span class="me-4"><span class="hidden md:inline">Derniers</span> 30 Jours</span>
                            <em class="text-xl leading-4.5 rtl:-scale-x-100 ni ni-chevron-right"></em>
                        </a>
                        <div class="dropdown-menu absolute min-w-[180px] border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 rounded-md shadow hidden peer-[.show]:block z-[1000]">
                            <ul class="py-2">
                                <li><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>Derniers 30 Jours</span></a></li>
                                <li><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>6 Derniers Mois</span></a></li>
                                <li><a class="relative px-5 py-2.5 flex items-center rounded-[inherit] text-xs leading-5 font-medium text-slate-600 dark:text-slate-300 hover:text-primary-600 hover:bg-slate-50 hover:dark:bg-gray-900 transition-all duration-300" href="#"><span>12 Derniers Mois</span></a></li>
                            </ul>
                        </div>
                    </div>
                </li>
                <li class="ms-auto">
                    <a href="#" class="relative inline-flex items-center text-center align-middle text-sm font-bold leading-4.5 rounded px-5 py-2 tracking-wide border border-primary-600 text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 transition-all duration-300">
                        <em class="text-xl leading-4.5 ni ni-reports"></em><span class="ms-3">Rapports</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div><!-- block head -->
