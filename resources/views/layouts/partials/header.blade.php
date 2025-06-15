<div class="nk-header fixed start-0 w-full h-16 top-0 z-[1021] transition-all duration-300 min-w-[320px]">
    <div class="h-16 border-b bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-900 px-1.5 sm:px-5">
        <div class="container max-w-none">
            <div class="relative flex items-center -mx-1">
                <div class="px-1 me-4 -ms-1.5 xl:hidden">
                    <a href="#" class="sidebar-toggle *:pointer-events-none inline-flex items-center isolate relative h-9 w-9 px-1.5 before:content-[''] before:absolute before:-z-[1] before:h-5 before:w-5 hover:before:h-10 hover:before:w-10 before:rounded-full before:opacity-0 hover:before:opacity-100 before:transition-all before:duration-300 before:-translate-x-1/2  before:-translate-y-1/2 before:top-1/2 before:left-1/2 before:bg-gray-200 dark:before:bg-gray-900">
                        <em class="text-2xl text-slate-600 dark:text-slate-300 ni ni-menu"></em>
                    </a>
                </div>
                <div class="px-1 py-3.5 flex xl:hidden">
                    <a href="{{ url('/') }}" class="relative inline-block transition-opacity duration-300 h-9">
                        <img class="h-full opacity-0 dark:opacity-100" src="{{ asset('images/logo.png') }}" srcset="{{ asset('images/logo.png') }}" alt="{{ site_info('name') }}">
                        <img class="absolute top-0 h-full opacity-100 dark:opacity-0 start-0" src="{{ asset('images/logo.png') }}" srcset="{{ asset('images/logo.png') }}" alt="{{ site_info('name') }}">
                    </a>
                </div>
                <div class="hidden px-1 py-2 xl:block">
                    <a class="flex items-center transition-all duration-300" href="#">
                        <div class="inline-flex flex-shrink-0 w-8">
                            <em class="text-2xl leading-none text-primary-600 ni ni-card-view"></em>
                        </div>
                        <div class="flex items-center max-w-[calc(100%-theme(spacing.8))]">
                            <p class="text-sm text-slate-600 dark:text-slate-300 font-medium text-ellipsis overflow-hidden whitespace-nowrap w-[calc(100%-theme(spacing.8))]">Do you know the latest update of 2022? <span class="font-normal text-slate-400 dark:text-slate-500"> A overview of our is now available on YouTube</span></p>
                            <em class="text-slate-400 ms-1 ni ni-external"></em>
                        </div>
                    </a>
                </div>
                <div class="px-1 py-3.5 ms-auto">
                    <ul class="flex item-center -mx-1.5 sm:-mx-2.5">
                        <li class="dropdown px-1.5 sm:px-2.5 relative inline-flex">
                            <a tabindex="0" href="#" class="dropdown-toggle *:pointer-events-none peer inline-flex items-center group" data-offset="0,10" data-placement="bottom-end"  data-rtl-placement="bottom-start">
                                <div class="flex items-center">
                                    <div class="relative flex items-center justify-center flex-shrink-0 w-8 h-8 text-xs font-medium text-white rounded-full bg-primary-500">
                                        <em class="ni ni-user-alt"></em>
                                    </div>
                                    <div class="hidden md:block ms-4">
                                        <div class="text-xs font-medium leading-none pt-0.5 pb-1.5 text-primary-500 group-hover:text-primary-600">{{ Auth::user()->name }}</div>
                                        <div class="flex items-center text-xs font-bold text-slate-600 dark:text-slate-400">{{ Auth::user()->roles->first()->name ?? 'Utilisateur' }} <em class="text-sm leading-none ms-1 ni ni-chevron-down"></em></div>
                                    </div>
                                </div>
                            </a>
                            <div tabindex="0" class="dropdown-menu clickable absolute max-xs:min-w-[240px] max-xs:max-w-[240px] min-w-[280px] max-w-[280px] border border-t-3 border-gray-200 dark:border-gray-800 border-t-primary-600 dark:border-t-primary-600 bg-white dark:bg-gray-950 rounded shadow hidden peer-[.show]:block z-[1000]">
                                <div class="hidden py-5 border-b border-gray-200 sm:block px-7 bg-slate-50 dark:bg-slate-900 dark:border-gray-800">
                                    <div class="flex items-center">
                                        <div class="relative flex items-center justify-center flex-shrink-0 w-10 h-10 text-sm font-medium text-white rounded-full bg-primary-500">
                                            <span>{{ Auth::user()->initials }}</span>
                                        </div>
                                        <div class="flex flex-col ms-4">
                                            <span class="text-sm font-bold text-slate-700 dark:text-white">{{ Auth::user()->name }}</span>
                                            <span class="mt-1 text-xs text-slate-400">{{ Auth::user()->email }}</span>
                                        </div>
                                    </div>
                                </div>
                                <ul class="py-3">
                                    <li>
                                        <a class="relative px-7 py-2.5 flex items-center rounded-[inherit] text-sm leading-5 font-medium text-slate-600 dark:text-slate-400 hover:text-primary-600 hover:dark:text-primary-600 transition-all duration-300" href="{{ route('setting.user_management') }}">
                                            <em class="text-lg leading-none w-7 ni ni-account-setting-alt"></em>
                                            <span>Sécurité</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="theme-toggle *:pointer-events-none relative px-7 py-2.5 flex items-center rounded-[inherit] text-sm leading-5 font-medium text-slate-600 dark:text-slate-400 hover:text-primary-600 hover:dark:text-primary-600 transition-all duration-300" href="javascript:void(0)">
                                            <div class="flex items-center dark:hidden">
                                                <em class="text-lg leading-none w-7 ni ni-moon"></em>
                                                <span>Dark Mode</span>
                                            </div>
                                            <div class="items-center hidden dark:flex">
                                                <em class="text-lg leading-none w-7 ni ni-sun"></em>
                                                <span>Light Mode</span>
                                            </div>
                                            <div class="relative w-12 h-6 bg-white border-2 border-gray-200 rounded-full ms-auto dark:border-primary-600 dark:bg-primary-600">
                                                <div class="absolute start-0.5 dark:start-6.5 top-0.5 h-4 w-4 rounded-full bg-gray-200 dark:bg-white transition-all duration-300"></div>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="block my-3 border-t border-gray-200 dark:border-gray-800"></li>
                                    <li>
                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault();
                                            this.closest('form').submit();">
                                        </x-dropdown-link>
                                    </form>

                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- container -->
    </div>
</div><!-- header -->
