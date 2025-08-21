<div
    class="nk-sidebar group/sidebar peer dark fixed w-72 [&.is-compact:not(.has-hover)]:w-[74px] min-h-screen max-h-screen overflow-hidden h-full start-0 top-0 z-[1031] transition-[transform,width] duration-300 -translate-x-full rtl:translate-x-full xl:translate-x-0 xl:rtl:translate-x-0 [&.sidebar-visible]:translate-x-0">
    <div
        class="flex items-center h-16 min-w-full px-6 py-3 overflow-hidden bg-white border-b border-gray-200 w-72 border-e dark:bg-gray-950 dark:border-gray-900">
        <div class="-ms-1 me-4">
            <div class="hidden xl:block">
                <a href="#"
                    class="sidebar-compact-toggle *:pointer-events-none inline-flex items-center isolate relative h-9 w-9 px-1.5 before:content-[''] before:absolute before:-z-[1] before:h-5 before:w-5 hover:before:h-10 hover:before:w-10 before:rounded-full before:opacity-0 hover:before:opacity-100 before:transition-all before:duration-300 before:-translate-x-1/2  before:-translate-y-1/2 before:top-1/2 before:left-1/2 before:bg-gray-200 dark:before:bg-gray-900">
                    <em class="text-2xl text-slate-600 dark:text-slate-300 ni ni-menu"></em>
                </a>
            </div>
            <div class="xl:hidden">
                <a href="#"
                    class="sidebar-toggle *:pointer-events-none inline-flex items-center isolate relative h-9 w-9 px-1.5 before:content-[''] before:absolute before:-z-[1] before:h-5 before:w-5 hover:before:h-10 hover:before:w-10 before:rounded-full before:opacity-0 hover:before:opacity-100 before:transition-all before:duration-300 before:-translate-x-1/2  before:-translate-y-1/2 before:top-1/2 before:left-1/2 before:bg-gray-200 dark:before:bg-gray-900">
                    <em class="text-2xl text-slate-600 dark:text-slate-300 rtl:-scale-x-100 ni ni-arrow-left"></em>
                </a>
            </div>
        </div>
        <div class="relative flex flex-shrink-0">
            <a href="{{ url('/') }}"
                class="relative inline-block transition-opacity duration-300 h-9 group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0">
                <img class="h-full opacity-0 dark:opacity-100" src="{{ asset('images/logo-dark.png') }}"
                    srcset="{{ asset('images/logo-dark.png') }}" alt="{{ site_info('name') }}">
                <img class="absolute top-0 h-full opacity-100 dark:opacity-0 start-0"
                    src="{{ asset('images/logo-dark.png') }}" srcset="{{ asset('images/logo-dark.png') }}"
                    alt="{{ site_info('name') }}">
            </a>
        </div>
    </div>
    <div
        class="relative w-full max-h-full overflow-hidden bg-white border-gray-200 nk-sidebar-body dark:bg-gray-950 border-e dark:border-gray-900">
        <div class="flex flex-col w-full h-[calc(100vh-theme(spacing.16))]">
            <div class="h-full pt-4 pb-10" data-simplebar>

                <ul class="nk-menu">
                    <!-- TABLEAU DE BORD - Accessible à tous -->
                    <li
                        class="relative first:pt-1 pt-10 pb-2 px-6 before:absolute before:h-px before:w-full before:start-0 before:top-1/2 before:bg-gray-200 dark:before:bg-gray-900 first:before:hidden before:opacity-0 group-[&.is-compact:not(.has-hover)]/sidebar:before:opacity-100">
                        <h6
                            class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 text-slate-400 dark:text-slate-300 whitespace-nowrap uppercase font-bold text-xs tracking-relaxed leading-tight">
                            Tableau de bord</h6>
                    </li><!-- menu-heading -->
                    <li class="nk-menu-item py-0.5{{ is_route('dashboard') ? ' active' : '' }} group/item">
                        <a href="{{ route('dashboard') }}"
                            class="nk-menu-link flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span
                                class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em
                                    class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-home"></em>
                            </span>
                            <span
                                class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500
                                group-hover:text-primary-500">ACCUEIL</span>
                        </a>
                    </li>

                    <!-- SCOLARITÉS - Réservé SUPERADMIN uniquement -->
                    @if(auth()->user()->hasRole('superadmin'))
                    <li
                        class="relative first:pt-1 pt-10 pb-2 px-6 before:absolute before:h-px before:w-full before:start-0 before:top-1/2 before:bg-gray-200 dark:before:bg-gray-900 first:before:hidden before:opacity-0 group-[&.is-compact:not(.has-hover)]/sidebar:before:opacity-100">
                        <h6
                            class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 text-slate-400 dark:text-slate-300 whitespace-nowrap uppercase font-bold text-xs tracking-relaxed leading-tight">
                            <span class="inline-flex items-center">
                                <em class="mr-2 text-xs text-red-500 ni ni-shield-check"></em>
                                Scolarités
                            </span>
                        </h6>
                    </li>

                    <li class="nk-menu-item py-0.5{{ is_route('unite_e') ? ' active' : '' }} group/item">
                        <a href="{{ route('unite_e') }}"
                            class="nk-menu-link flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span
                                class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em
                                    class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-tile-thumb"></em>
                            </span>
                            <span
                                class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                            UE/EC
                            </span>
                        </a>
                    </li>

                    <li class="nk-menu-item py-0.5{{ is_route('students') ? ' active' : '' }} group/item">
                        <a href="{{ route('students') }}"
                            class="nk-menu-link flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span
                                class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em
                                    class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-users"></em>
                            </span>
                            <span
                                class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                            ETUDIANTS
                            </span>
                        </a>
                    </li>

                    <li class="nk-menu-item py-0.5{{ is_route('salles.index') ? ' active' : '' }} group/item">
                        <a href="{{ route('salles.index') }}"
                            class="nk-menu-link flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span
                                class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em
                                    class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-building"></em>
                            </span>
                            <span
                                class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                            SALLES
                            </span>
                        </a>
                    </li>

                    <li class="nk-menu-item py-0.5{{ request()->routeIs('examens.*') ? ' active' : '' }} group/item">
                        <a href="{{ route('examens.index') }}"
                            class="nk-menu-link flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span
                                class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em
                                    class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-calender-date"></em>
                            </span>
                            <span
                                class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                            PROGRAMMES
                            </span>
                        </a>
                    </li>
                    @endif

                    <!-- TRAITEMENTS - Accessible aux : superadmin, secretaire -->
                    @if(auth()->user()->hasAnyRole(['superadmin', 'secretaire']))
                    <li
                        class="relative first:pt-1 pt-10 pb-2 px-6 before:absolute before:h-px before:w-full before:start-0 before:top-1/2 before:bg-gray-200 dark:before:bg-gray-900 first:before:hidden before:opacity-0 group-[&.is-compact:not(.has-hover)]/sidebar:before:opacity-100">
                        <h6
                            class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 text-slate-400 dark:text-slate-300 whitespace-nowrap uppercase font-bold text-xs tracking-relaxed leading-tight">
                            <span class="inline-flex items-center">
                                <em class="mr-2 text-xs text-green-500 ni ni-activity"></em>
                                Traitements
                            </span>
                        </h6>
                    </li>

                    <li class="nk-menu-item py-0.5{{ request()->routeIs('manchettes.*') ? ' active' : '' }} group/item">
                        <a href="{{ route('manchettes.index') }}"
                            class="nk-menu-link flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span
                                class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em
                                    class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-notice"></em>
                            </span>
                            <span
                                class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                            MANCHETTES
                            </span>
                        </a>
                    </li>

                    <li class="nk-menu-item py-0.5{{ request()->routeIs('copies.*') ? ' active' : '' }} group/item">
                        <a href="{{ route('copies.index') }}"
                            class="nk-menu-link flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span
                                class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em
                                    class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-notes-alt"></em>
                            </span>
                            <span
                                class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                            COPIES/NOTES
                            </span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasAnyRole(['superadmin', 'enseignant']))
                    <li class="nk-menu-item py-0.5 has-sub group/item {{ is_route('resultats.index.*') ? ' active' : '' }}">
                        <a href="#" class="nk-menu-link sub nk-menu-toggle flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-award"></em>
                            </span>
                            <span class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                RESULTATS
                            </span>
                            <em class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 text-base leading-none text-slate-400 group-[.active]/item:text-primary-500 absolute end-5 top-1/2 -translate-y-1/2 rtl:-scale-x-100 group-[.active]/item:rotate-90 group-[.active]/item:rtl:-rotate-90 transition-all duration-300 icon ni ni-chevron-right"></em>
                        </a>
                        <ul class="nk-menu-sub mb-1 hidden group-[&.is-compact:not(.has-hover)]/sidebar:!hidden"  {{ is_route('resultats.index.*') ? 'style=display:block' : '' }}>
                            <li class="nk-menu-item py-px sub has-sub group/sub1 {{ is_route('resultats.fusion') ? ' active' : '' }}">
                                <a href="{{ route('resultats.fusion') }}" class="nk-menu-link flex relative items-center align-middle py-1.5 pe-10 ps-[calc(theme(spacing.6)+theme(spacing.9))] font-normal leading-5 text-sm tracking-normal normal-case">
                                    <span class="text-slate-600 dark:text-slate-500 group-[.active]/sub1:text-primary-500 hover:text-primary-500 whitespace-nowrap flex-grow inline-block">
                                         <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-shuffle"></em>
                                        Fussion & Vérification
                                    </span>
                                </a>
                            </li>
                            <li class="nk-menu-item py-px sub has-sub group/sub1 {{ is_route('resultats.finale') ? ' active' : '' }}">
                                <a href="{{ route('resultats.finale') }}" class="nk-menu-link flex relative items-center align-middle py-1.5 pe-10 ps-[calc(theme(spacing.6)+theme(spacing.9))] font-normal leading-5 text-sm tracking-normal normal-case">
                                    <span class="text-slate-600 dark:text-slate-500 group-[.active]/sub1:text-primary-500 hover:text-primary-500 whitespace-nowrap flex-grow inline-block">
                                        <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-list-check"></em>
                                        Résultats finaux
                                    </span>
                                </a>
                            </li>
                            @endif 
                            @if(auth()->user()->hasAnyRole(['superadmin']))
                             <li class="nk-menu-item py-px sub has-sub group/sub1 {{ is_route('resultats.releve_note') ? ' active' : '' }}">
                                <a href="{{ route('resultats.releve_note') }}" class="nk-menu-link flex relative items-center align-middle py-1.5 pe-10 ps-[calc(theme(spacing.6)+theme(spacing.9))] font-normal leading-5 text-sm tracking-normal normal-case">
                                    <span class="text-slate-600 dark:text-slate-500 group-[.active]/sub1:text-primary-500 hover:text-primary-500 whitespace-nowrap flex-grow inline-block">
                                        <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-article"></em>
                                        Relevé de notes
                                    </span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>

                    <!-- PARAMÈTRAGES - Réservé SUPERADMIN uniquement -->
                    @if(auth()->user()->hasRole('superadmin'))
                    <li
                        class="relative first:pt-1 pt-10 pb-2 px-6 before:absolute before:h-px before:w-full before:start-0 before:top-1/2 before:bg-gray-200 dark:before:bg-gray-900 first:before:hidden before:opacity-0 group-[&.is-compact:not(.has-hover)]/sidebar:before:opacity-100">
                        <h6
                            class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 text-slate-400 dark:text-slate-300 whitespace-nowrap uppercase font-bold text-xs tracking-relaxed leading-tight">
                            <span class="inline-flex items-center">
                                <em class="mr-2 text-xs text-purple-500 ni ni-lock"></em>
                                Paramètrages
                            </span>
                        </h6>
                    </li>
                   <li class="nk-menu-item py-0.5 has-sub group/item {{ is_route('setting.index.*') ? ' active' : '' }}">
                        <a href="#" class="nk-menu-link sub nk-menu-toggle flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-setting-alt"></em>
                            </span>
                            <span class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                PARAMETRES
                            </span>
                            <em class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 text-base leading-none text-slate-400 group-[.active]/item:text-primary-500 absolute end-5 top-1/2 -translate-y-1/2 rtl:-scale-x-100 group-[.active]/item:rotate-90 group-[.active]/item:rtl:-rotate-90 transition-all duration-300 icon ni ni-chevron-right"></em>
                        </a>
                        <ul class="nk-menu-sub mb-1 hidden group-[&.is-compact:not(.has-hover)]/sidebar:!hidden"  {{ is_route('setting.index.*') ? 'style=display:block' : '' }}>
                            <li class="nk-menu-item py-px sub has-sub group/sub1 {{ is_route('setting.user_management') ? ' active' : '' }}">
                                <a href="{{ route('setting.user_management') }}" class="nk-menu-link flex relative items-center align-middle py-1.5 pe-10 ps-[calc(theme(spacing.6)+theme(spacing.9))] font-normal leading-5 text-sm tracking-normal normal-case">
                                    <span class="text-slate-600 dark:text-slate-500 group-[.active]/sub1:text-primary-500 hover:text-primary-500 whitespace-nowrap flex-grow inline-block">
                                         <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-users"></em>
                                       Gestion des utilisateurs
                                    </span>
                                </a>
                            </li>
                            <li class="nk-menu-item py-px sub has-sub group/sub1 {{ is_route('setting.annee_universite') ? ' active' : '' }}">
                                <a href="{{ route('setting.annee_universite') }}" class="nk-menu-link flex relative items-center align-middle py-1.5 pe-10 ps-[calc(theme(spacing.6)+theme(spacing.9))] font-normal leading-5 text-sm tracking-normal normal-case">
                                    <span class="text-slate-600 dark:text-slate-500 group-[.active]/sub1:text-primary-500 hover:text-primary-500 whitespace-nowrap flex-grow inline-block">
                                        <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-calender-date"></em>
                                        Année Universitaire
                                    </span>
                                </a>
                            </li>
                            <li class="nk-menu-item py-px sub has-sub group/sub1 {{ is_route('setting.session_examen') ? ' active' : '' }}">
                                <a href="{{ route('setting.session_examen') }}" class="nk-menu-link flex relative items-center align-middle py-1.5 pe-10 ps-[calc(theme(spacing.6)+theme(spacing.9))] font-normal leading-5 text-sm tracking-normal normal-case">
                                    <span class="text-slate-600 dark:text-slate-500 group-[.active]/sub1:text-primary-500 hover:text-primary-500 whitespace-nowrap flex-grow inline-block">
                                        <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-calendar"></em>
                                        Session d'examen
                                    </span>
                                </a>
                            </li>
                            <li class="nk-menu-item py-px sub has-sub group/sub1 {{ is_route('setting.roles_permission') ? ' active' : '' }}">
                                <a href="{{ route('setting.roles_permission') }}" class="nk-menu-link flex relative items-center align-middle py-1.5 pe-10 ps-[calc(theme(spacing.6)+theme(spacing.9))] font-normal leading-5 text-sm tracking-normal normal-case">
                                    <span class="text-slate-600 dark:text-slate-500 group-[.active]/sub1:text-primary-500 hover:text-primary-500 whitespace-nowrap flex-grow inline-block">
                                        <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-account-setting"></em>
                                        Rôles & Permissions
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                </ul>

            </div>
        </div>
    </div>
</div><!-- sidebar -->
<div
    class="sidebar-toggle fixed inset-0 bg-slate-950 bg-opacity-20 z-[1030] opacity-0 invisible peer-[.sidebar-visible]:opacity-100 peer-[.sidebar-visible]:visible xl:!opacity-0 xl:!invisible">
</div>
