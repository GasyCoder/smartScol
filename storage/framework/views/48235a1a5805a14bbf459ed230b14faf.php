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
            <a href="<?php echo e(url('/')); ?>"
                class="relative inline-block transition-opacity duration-300 h-9 group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0">
                <img class="h-full opacity-0 dark:opacity-100" src="<?php echo e(asset('images/logo-dark.png')); ?>"
                    srcset="<?php echo e(asset('images/logo-dark.png')); ?>" alt="<?php echo e(site_info('name')); ?>">
                <img class="absolute top-0 h-full opacity-100 dark:opacity-0 start-0"
                    src="<?php echo e(asset('images/logo-dark.png')); ?>" srcset="<?php echo e(asset('images/logo-dark.png')); ?>"
                    alt="<?php echo e(site_info('name')); ?>">
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
                    <li class="nk-menu-item py-0.5<?php echo e(is_route('dashboard') ? ' active' : ''); ?> group/item">
                        <a href="<?php echo e(route('dashboard')); ?>"
                            class="nk-menu-link flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span
                                class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em
                                    class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-home"></em>
                            </span>
                            <span
                                class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500
                                group-hover:text-primary-500">Accueil</span>
                        </a>
                    </li>

                    <!-- SCOLARITÉS - Réservé SUPERADMIN uniquement -->
                    <?php if(auth()->user()->hasRole('superadmin')): ?>
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

                    <li class="nk-menu-item py-0.5<?php echo e(is_route('unite_e') ? ' active' : ''); ?> group/item">
                        <a href="<?php echo e(route('unite_e')); ?>"
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
                    <li class="nk-menu-item py-0.5<?php echo e(is_route('salles.index') ? ' active' : ''); ?> group/item">
                        <a href="<?php echo e(route('salles.index')); ?>"
                            class="nk-menu-link flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span
                                class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em
                                    class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-building"></em>
                            </span>
                            <span
                                class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                            Salles
                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('examens.view')): ?>
                    <li class="nk-menu-item py-0.5<?php echo e(request()->routeIs('examens.*') ? ' active' : ''); ?> group/item">
                        <a href="<?php echo e(route('examens.index')); ?>"
                            class="nk-menu-link flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span
                                class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em
                                    class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-calender-date"></em>
                            </span>
                            <span
                                class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                            Programmes
                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('etudiants.view')): ?>
                    <li class="nk-menu-item py-0.5<?php echo e(is_route('students') ? ' active' : ''); ?> group/item">
                        <a href="<?php echo e(route('students')); ?>"
                            class="nk-menu-link flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span
                                class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em
                                    class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-users"></em>
                            </span>
                            <span
                                class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                            Liste des étudiants
                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if(auth()->user()->canAny(['manchettes.view','manchettes.create','copies.view','copies.create'])): ?>
                    <li class="relative pt-10 px-6 pb-2 border-t border-gray-200 dark:border-gray-700">
                        <h6 class="text-slate-400 dark:text-slate-300 uppercase font-bold text-xs tracking-wide flex items-center">
                            <em class="mr-2 text-xs text-green-500 ni ni-activity"></em>
                            Traitements
                        </h6>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manchettes.view')): ?>
                    <li class="nk-menu-item <?php echo e(request()->routeIs('manchette.index') ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('manchette.index')); ?>"
                        class="nk-menu-link flex items-center py-2.5 ps-6 pe-10 font-bold transition
                                <?php echo e(request()->routeIs('manchette.index') ? 'text-primary-500' : 'text-slate-600 dark:text-slate-500 hover:text-primary-500'); ?>">
                            <span class="w-9 flex-shrink-0 text-slate-400"><em class="text-2xl ni ni-notice"></em></span>
                            <span class="flex-grow">Listes des Manchettes</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if(auth()->user()->hasRole('secretaire')): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manchettes.create')): ?>
                        <li class="nk-menu-item <?php echo e(request()->routeIs('manchettes.saisie') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('manchettes.saisie')); ?>"
                            class="nk-menu-link flex items-center py-2.5 ps-6 pe-10 font-bold transition
                                    <?php echo e(request()->routeIs('manchettes.saisie') ? 'text-primary-500' : 'text-slate-600 dark:text-slate-500 hover:text-primary-500'); ?>">
                                <span class="w-9 flex-shrink-0 text-slate-400"><em class="text-2xl ni ni-task-c"></em></span>
                                <span class="flex-grow">Manchettes</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('copies.create')): ?>
                        <li class="nk-menu-item <?php echo e(request()->routeIs('copies.saisie') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('copies.saisie')); ?>"
                            class="nk-menu-link flex items-center py-2.5 ps-6 pe-10 font-bold transition
                                    <?php echo e(request()->routeIs('copies.saisie') ? 'text-primary-500' : 'text-slate-600 dark:text-slate-500 hover:text-primary-500'); ?>">
                                <span class="w-9 flex-shrink-0 text-slate-400"><em class="text-2xl ni ni-article"></em></span>
                                <span class="flex-grow">Copies/Notes</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('copies.view')): ?>
                    <li class="nk-menu-item <?php echo e(request()->routeIs('copie.index') ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('copie.index')); ?>"
                        class="nk-menu-link flex items-center py-2.5 ps-6 pe-10 font-bold transition
                                <?php echo e(request()->routeIs('copie.index') ? 'text-primary-500' : 'text-slate-600 dark:text-slate-500 hover:text-primary-500'); ?>">
                            <span class="w-9 flex-shrink-0 text-slate-400"><em class="text-2xl ni ni-notes-alt"></em></span>
                            <span class="flex-grow">Listes des Copies</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('resultats.fusion')): ?>
                    <li class="nk-menu-item <?php echo e(request()->routeIs('resultats.fusion', 'resultats.verification') ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('resultats.fusion')); ?>"
                        class="nk-menu-link flex items-center py-2.5 ps-6 pe-10 font-bold transition
                                <?php echo e(request()->routeIs('resultats.fusion', 'resultats.verification') 
                                        ? 'text-primary-500' 
                                        : 'text-slate-600 dark:text-slate-500 hover:text-primary-500'); ?>">
                            <span class="w-9 flex-shrink-0 text-slate-400">
                                <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-shuffle"></em>
                            </span>
                            <span class="flex-grow">Fusion & Vérification</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('resultats.view')): ?>
                        <li class="nk-menu-item <?php echo e(request()->routeIs('resultats.finale') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('resultats.finale')); ?>"
                            class="nk-menu-link flex items-center py-2.5 ps-6 pe-10 font-bold transition
                                    <?php echo e(request()->routeIs('resultats.finale') 
                                            ? 'text-primary-500' 
                                            : 'text-slate-600 dark:text-slate-500 hover:text-primary-500'); ?>">
                                <span class="w-9 flex-shrink-0 text-slate-400">
                                    <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-award"></em>
                                </span>
                                <span class="flex-grow">Résultats finaux</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('releve-note.view')): ?>
                    <li class="nk-menu-item <?php echo e(request()->routeIs('resultats.releve-notes.index') ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('resultats.releve-notes.index')); ?>"
                        class="nk-menu-link flex items-center py-2.5 ps-6 pe-10 font-bold transition
                                <?php echo e(request()->routeIs('resultats.releve-notes.index') 
                                        ? 'text-primary-500' 
                                        : 'text-slate-600 dark:text-slate-500 hover:text-primary-500'); ?>">
                            <span class="w-9 flex-shrink-0 text-slate-400">
                                <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-article"></em>
                            </span>
                            <span class="flex-grow"> Relevé de notes</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <!-- PARAMÈTRAGES - Réservé SUPERADMIN uniquement -->
                    <?php if(auth()->user()->hasRole('superadmin')): ?>
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
                    <li class="nk-menu-item py-0.5 has-sub group/item <?php echo e(is_route('setting.index.*') ? ' active' : ''); ?>">
                        <a href="#" class="nk-menu-link sub nk-menu-toggle flex relative items-center align-middle py-2.5 ps-6 pe-10 font-heading font-bold tracking-snug group">
                            <span class="font-normal tracking-normal w-9 inline-flex flex-grow-0 flex-shrink-0 text-slate-400 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                <em class="text-2xl leading-none text-current transition-all duration-300 icon ni ni-setting-alt"></em>
                            </span>
                            <span class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 flex-grow-1 inline-block whitespace-nowrap transition-all duration-300 text-slate-600 dark:text-slate-500 group-[.active]/item:text-primary-500 group-hover:text-primary-500">
                                PARAMETRES
                            </span>
                            <em class="group-[&.is-compact:not(.has-hover)]/sidebar:opacity-0 text-base leading-none text-slate-400 group-[.active]/item:text-primary-500 absolute end-5 top-1/2 -translate-y-1/2 rtl:-scale-x-100 group-[.active]/item:rotate-90 group-[.active]/item:rtl:-rotate-90 transition-all duration-300 icon ni ni-chevron-right"></em>
                        </a>
                        <ul class="nk-menu-sub mb-1 hidden group-[&.is-compact:not(.has-hover)]/sidebar:!hidden"  <?php echo e(is_route('setting.index.*') ? 'style=display:block' : ''); ?>>
                            <li class="nk-menu-item py-px sub has-sub group/sub1 <?php echo e(is_route('setting.user_management') ? ' active' : ''); ?>">
                                <a href="<?php echo e(route('setting.user_management')); ?>" class="nk-menu-link flex relative items-center align-middle py-1.5 pe-10 ps-[calc(theme(spacing.6)+theme(spacing.9))] font-normal leading-5 text-sm tracking-normal normal-case">
                                    <span class="text-slate-600 dark:text-slate-500 group-[.active]/sub1:text-primary-500 hover:text-primary-500 whitespace-nowrap flex-grow inline-block">
                                         <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-users"></em>
                                       Gestion des utilisateurs
                                    </span>
                                </a>
                            </li>
                            <li class="nk-menu-item py-px sub has-sub group/sub1 <?php echo e(is_route('setting.annee_universite') ? ' active' : ''); ?>">
                                <a href="<?php echo e(route('setting.annee_universite')); ?>" class="nk-menu-link flex relative items-center align-middle py-1.5 pe-10 ps-[calc(theme(spacing.6)+theme(spacing.9))] font-normal leading-5 text-sm tracking-normal normal-case">
                                    <span class="text-slate-600 dark:text-slate-500 group-[.active]/sub1:text-primary-500 hover:text-primary-500 whitespace-nowrap flex-grow inline-block">
                                        <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-calender-date"></em>
                                        Année Universitaire
                                    </span>
                                </a>
                            </li>
                            <li class="nk-menu-item py-px sub has-sub group/sub1 <?php echo e(is_route('setting.session_examen') ? ' active' : ''); ?>">
                                <a href="<?php echo e(route('setting.session_examen')); ?>" class="nk-menu-link flex relative items-center align-middle py-1.5 pe-10 ps-[calc(theme(spacing.6)+theme(spacing.9))] font-normal leading-5 text-sm tracking-normal normal-case">
                                    <span class="text-slate-600 dark:text-slate-500 group-[.active]/sub1:text-primary-500 hover:text-primary-500 whitespace-nowrap flex-grow inline-block">
                                        <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-calendar"></em>
                                        Session d'examen
                                    </span>
                                </a>
                            </li>
                            <li class="nk-menu-item py-px sub has-sub group/sub1 <?php echo e(is_route('setting.roles_permission') ? ' active' : ''); ?>">
                                <a href="<?php echo e(route('setting.roles_permission')); ?>" class="nk-menu-link flex relative items-center align-middle py-1.5 pe-10 ps-[calc(theme(spacing.6)+theme(spacing.9))] font-normal leading-5 text-sm tracking-normal normal-case">
                                    <span class="text-slate-600 dark:text-slate-500 group-[.active]/sub1:text-primary-500 hover:text-primary-500 whitespace-nowrap flex-grow inline-block">
                                        <em class="mr-2 text-lg leading-none text-current transition-all duration-300 icon ni ni-account-setting"></em>
                                        Rôles & Permissions
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div><!-- sidebar -->
<div
    class="sidebar-toggle fixed inset-0 bg-slate-950 bg-opacity-20 z-[1030] opacity-0 invisible peer-[.sidebar-visible]:opacity-100 peer-[.sidebar-visible]:visible xl:!opacity-0 xl:!invisible">
</div>
<?php /**PATH /var/www/smartScol/resources/views/layouts/partials/sidebar.blade.php ENDPATH**/ ?>