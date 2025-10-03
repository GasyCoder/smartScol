<div>
        <!-- En-tête simplifié -->
        <div class="bg-white dark:bg-gray-950 shadow-sm rounded-lg p-6 mb-6 border border-gray-200 dark:border-gray-800">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold font-heading text-gray-900 dark:text-white mb-2">
                        👤 Salut, <?php echo e(Auth::user()->name); ?>

                    </h1>
                    <p class="text-xs font-body text-gray-600 dark:text-gray-400">
                        Session <?php echo e(ucfirst($sessionType)); ?> - Vue d'ensemble globale
                    </p>
                </div>
                
                <!-- Bouton actualiser -->
                <div class="mt-4 sm:mt-0">
                    <button wire:click="refreshStats" 
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium font-body transition-colors duration-200 flex items-center shadow-sm"
                            wire:loading.attr="disabled">
                        <!-- État normal -->
                        <span wire:loading.remove class="flex items-center">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0
                                        a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Actualiser
                        </span>
                        <!-- État chargement -->
                        <span wire:loading class="flex items-center">
                            <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" 
                                        stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" 
                                    d="M4 12a8 8 0 018-8V0C5.373 0 
                                        0 5.373 0 12h4zm2 5.291A7.962 
                                        7.962 0 014 12H0c0 3.042 1.135 
                                        5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Chargement...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Section MES STATISTIQUES -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold font-heading text-gray-900 dark:text-white mb-4 flex items-center">
                🎯 Statistiques Personnelles
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- MES Manchettes -->
                <div class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-950 dark:to-primary-900 shadow-sm rounded-lg p-6 border-l-4 border-primary-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary-500 rounded-lg flex items-center justify-center">
                                <span class="text-white text-xl">🏷️</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold font-heading text-primary-700 dark:text-primary-300">
                                <?php echo e(number_format($statsPersonnelles['mes_manchettes'] ?? 0)); ?>

                            </div>
                            <div class="text-sm text-primary-600 dark:text-primary-400 font-medium font-body">
                                Manchettes
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400 font-body">
                                +<?php echo e($statsPersonnelles['mes_manchettes_aujourdhui'] ?? 0); ?> aujourd'hui
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MES Copies -->
                <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-950 dark:to-green-900 shadow-sm rounded-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                <span class="text-white text-xl">📝</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold font-heading text-green-700 dark:text-green-300">
                                <?php echo e(number_format($statsPersonnelles['mes_copies'] ?? 0)); ?>

                            </div>
                            <div class="text-sm text-green-600 dark:text-green-400 font-medium font-body">
                                Copies
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400 font-body">
                                +<?php echo e($statsPersonnelles['mes_copies_aujourdhui'] ?? 0); ?> aujourd'hui
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOTAL Manchettes (Global) -->
                <div class="bg-gradient-to-r from-cyan-50 to-cyan-100 dark:from-cyan-950 dark:to-cyan-900 shadow-sm rounded-lg p-6 border-l-4 border-cyan-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-cyan-500 rounded-lg flex items-center justify-center">
                                <span class="text-white text-xl">📊</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold font-heading text-cyan-700 dark:text-cyan-300">
                                <?php echo e(number_format($statsGlobales['total_manchettes'] ?? 0)); ?>

                            </div>
                            <div class="text-sm text-cyan-600 dark:text-cyan-400 font-medium font-body">
                                Total Manchettes
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400 font-body">
                                +<?php echo e($statsGlobales['manchettes_aujourdhui'] ?? 0); ?> aujourd'hui
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOTAL Copies (Global) -->
                <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-950 dark:to-yellow-900 shadow-sm rounded-lg p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                                <span class="text-white text-xl">📈</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold font-heading text-yellow-700 dark:text-yellow-300">
                                <?php echo e(number_format($statsGlobales['total_copies'] ?? 0)); ?>

                            </div>
                            <div class="text-sm text-yellow-600 dark:text-yellow-400 font-medium font-body">
                                Total Copies
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400 font-body">
                                +<?php echo e($statsGlobales['copies_aujourdhui'] ?? 0); ?> aujourd'hui
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progression globale -->
        <!--[if BLOCK]><![endif]--><?php if(($statsGlobales['total_manchettes'] ?? 0) > 0): ?>
        <div class="bg-white dark:bg-gray-950 shadow-sm rounded-lg p-6 mb-6 border border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-semibold font-heading text-gray-900 dark:text-white mb-4">
                📈 Progression Générale Globale
            </h3>
            <div class="flex justify-between text-sm font-body text-gray-600 dark:text-gray-400 mb-2">
                <span><?php echo e($statsGlobales['total_copies'] ?? 0); ?> copies saisies sur <?php echo e($statsGlobales['total_manchettes'] ?? 0); ?> manchettes</span>
                <span class="font-medium"><?php echo e($statsGlobales['pourcentage_global'] ?? 0); ?>%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-4 mb-2">
                <div class="bg-gradient-to-r from-primary-500 to-green-500 h-4 rounded-full transition-all duration-500" 
                     style="width: <?php echo e($statsGlobales['pourcentage_global'] ?? 0); ?>%"></div>
            </div>
            <div class="text-xs font-body text-gray-500 dark:text-gray-400">
                <?php echo e($statsGlobales['restantes_global'] ?? 0); ?> copies restantes • <?php echo e($statsGlobales['matieres_actives'] ?? 0); ?> matières actives • Moyenne générale: <?php echo e($statsGlobales['moyenne_generale'] ?? 0); ?>/20
            </div>
        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!-- Statistiques détaillées par niveau et Mon activité récente -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            <!-- Vue d'ensemble par niveau -->
            <div class="bg-white dark:bg-gray-950 shadow-sm rounded-lg p-6 border border-gray-200 dark:border-gray-800">
                <h3 class="text-lg font-semibold font-heading text-gray-900 dark:text-white mb-4 flex items-center">
                    🏫 Vue d'Ensemble par Niveau
                </h3>
                
                <!--[if BLOCK]><![endif]--><?php if(count($statsParNiveau) > 0): ?>
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $statsParNiveau; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $niveau): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                            <!-- En-tête niveau -->
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-semibold font-heading text-gray-900 dark:text-white">
                                    <?php echo e($niveau['niveau_abr']); ?> - <?php echo e($niveau['niveau_nom']); ?>

                                </h4>
                                <span class="text-sm px-2 py-1 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded font-body">
                                    <?php echo e($niveau['pourcentage']); ?>%
                                </span>
                            </div>

                            <!-- Stats niveau -->
                            <div class="grid grid-cols-2 gap-4 mb-3 text-sm font-body">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Total général:</span>
                                    <div class="font-medium text-gray-900 dark:text-white"><?php echo e($niveau['total_manchettes']); ?> manchettes, <?php echo e($niveau['total_copies']); ?> copies</div>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Mes contributions:</span>
                                    <div class="font-medium text-primary-600 dark:text-primary-400"><?php echo e($niveau['mes_manchettes']); ?> + <?php echo e($niveau['mes_copies']); ?></div>
                                </div>
                            </div>

                            <!-- Barre progression niveau -->
                            <!--[if BLOCK]><![endif]--><?php if($niveau['total_manchettes'] > 0): ?>
                            <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-2">
                                <div class="bg-primary-500 h-2 rounded-full transition-all duration-300" 
                                     style="width: <?php echo e($niveau['pourcentage']); ?>%"></div>
                            </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <div class="text-gray-400 text-4xl mb-2">🏫</div>
                        <p class="text-gray-500 dark:text-gray-400 font-body">Aucune donnée disponible</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 font-body">
                            Les statistiques apparaîtront une fois les saisies effectuées
                        </p>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <!-- Mon activité récente -->
            <div class="bg-white dark:bg-gray-950 shadow-sm rounded-lg p-6 border border-gray-200 dark:border-gray-800">
                <h3 class="text-lg font-semibold font-heading text-gray-900 dark:text-white mb-4 flex items-center">
                    ⏰ Activité Récente
                </h3>
                
                <!--[if BLOCK]><![endif]--><?php if(count($activiteRecente) > 0): ?>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $activiteRecente; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start space-x-3 p-3 
                            <?php if($activity['color'] === 'blue'): ?> bg-primary-50 dark:bg-primary-950 border-l-4 border-primary-400
                            <?php elseif($activity['color'] === 'green'): ?> bg-green-50 dark:bg-green-950 border-l-4 border-green-400
                            <?php else: ?> bg-gray-50 dark:bg-gray-900 border-l-4 border-gray-400
                            <?php endif; ?>
                            rounded-lg">
                            <div class="flex-shrink-0">
                                <span class="text-lg"><?php echo e($activity['icon']); ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium font-body text-gray-900 dark:text-white">
                                    <?php echo e($activity['message']); ?>

                                </p>
                                <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400 mt-1 font-body">
                                    <!--[if BLOCK]><![endif]--><?php if($activity['etudiant']): ?>
                                        <span><?php echo e($activity['etudiant']); ?></span>
                                        <span>•</span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <span><?php echo e($activity['matiere']); ?></span>
                                    <span>•</span>
                                    <span><?php echo e($activity['niveau']); ?></span>
                                    <span>•</span>
                                    <span><?php echo e($activity['date']->diffForHumans()); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <div class="text-gray-400 text-4xl mb-2">⏰</div>
                        <p class="text-gray-500 dark:text-gray-400 font-body">Aucune activité récente</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 font-body">
                            Vos saisies apparaîtront ici
                        </p>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="bg-white dark:bg-gray-950 shadow-sm rounded-lg p-6 border border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-semibold font-heading text-gray-900 dark:text-white mb-4">🚀 Actions Rapides</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <!-- Saisie Manchettes -->
                <a href="<?php echo e(route('manchettes.saisie')); ?>" 
                   class="flex items-center p-4 border border-primary-200 dark:border-primary-800 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-950 transition-colors group">
                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center mr-3 group-hover:bg-primary-200 dark:group-hover:bg-primary-800">
                        🏷️
                    </div>
                    <div>
                        <div class="font-medium font-heading text-gray-900 dark:text-white">Saisie Manchettes</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 font-body">Créer nouvelles manchettes</div>
                    </div>
                </a>

                <!-- Saisie Copies -->
                <a href="<?php echo e(route('copies.saisie')); ?>" 
                   class="flex items-center p-4 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-50 dark:hover:bg-green-950 transition-colors group">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3 group-hover:bg-green-200 dark:group-hover:bg-green-800">
                        📝
                    </div>
                    <div>
                        <div class="font-medium font-heading text-gray-900 dark:text-white">Saisie Copies</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 font-body">Saisir notes d'examen</div>
                    </div>
                </a>

                <!-- Actualiser -->
                <button wire:click="refreshStats"
                   class="flex items-center p-4 border border-slate-200 dark:border-slate-800 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-950 transition-colors group">
                    <div class="w-10 h-10 bg-slate-100 dark:bg-slate-900 rounded-lg flex items-center justify-center mr-3 group-hover:bg-slate-200 dark:group-hover:bg-slate-800">
                        📊
                    </div>
                    <div>
                        <div class="font-medium font-heading text-gray-900 dark:text-white">Actualiser Stats</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 font-body">Recharger les données</div>
                    </div>
                </button>
            </div>
        </div>
<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('livewire:init', () => {
        // Auto-refresh toutes les 5 minutes
        setInterval(() => {
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('refreshStats');
        }, 300000);
    });
</script>
<?php $__env->stopPush(); ?>
</div><?php /**PATH /var/www/smartScol/resources/views/livewire/secretaire-dashboard.blade.php ENDPATH**/ ?>