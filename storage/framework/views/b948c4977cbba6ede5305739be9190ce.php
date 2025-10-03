<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" id="pageroot" class="<?php echo e(dark_mode() ? 'dark' : ''); ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="author" content="BEZARA Florent">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="Logiciel de faculté de médecine de l'Université de Mahajanga - SmartScol">
        <link rel="icon" type="image/png" href="<?php echo e(asset('images/favicon/favicon-96x96.png')); ?>" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('images/favicon/favicon.svg')); ?>" />
        <link rel="shortcut icon" href="<?php echo e(asset('images/favicon/favicon.ico')); ?>" />
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('images/favicon/apple-touch-icon.png')); ?>" />
        <link rel="manifest" href="<?php echo e(asset('images/favicon/site.webmanifest')); ?>" />
        <title><?php if(isset($title)): ?> <?php echo e($title); ?> | <?php endif; ?><?php echo e(config('app.desc')); ?></title>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="bg-gray-50 font-body text-sm leading-relaxed text-slate-600 dark:text-slate-300 dark:bg-gray-1000 font-normal min-w-[320px]" dir="<?php echo e(gcs('direction', 'ltr')); ?>">
        <div class="overflow-hidden nk-app-root">
            <div class="nk-main">
                <?php echo $__env->make('layouts.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="nk-wrap xl:ps-72 [&>.nk-header]:xl:start-72 [&>.nk-header]:xl:w-[calc(100%-theme(spacing.72))] peer-[&.is-compact:not(.has-hover)]:xl:ps-[74px] peer-[&.is-compact:not(.has-hover)]:[&>.nk-header]:xl:start-[74px] peer-[&.is-compact:not(.has-hover)]:[&>.nk-header]:xl:w-[calc(100%-74px)] flex flex-col min-h-screen transition-all duration-300">

                    <?php echo $__env->make('layouts.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <div id="pagecontent" class="nk-content mt-16  px-1.5 sm:px-5 py-6 sm:py-8">
                        <div class="container <?php echo e(isset($container) ? '' : ' max-w-none'); ?>">
                             <?php echo e($slot); ?>

                        </div>
                    </div><!-- content -->

                    <?php echo $__env->make('layouts.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                </div>
            </div>
        </div><!-- root -->
        <?php echo $__env->yieldPushContent('modals'); ?>
        <?php echo $__env->make('layouts.partials.off-canvas', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <!-- JavaScript -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/dashwin/js/scripts.js']); ?>
        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
<?php /**PATH /var/www/smartScol/resources/views/layouts/app.blade.php ENDPATH**/ ?>