<button <?php echo e($attributes->merge(['type' => 'submit', 'class' => 'relative w-full flex items-center justify-center text-center align-middle text-base font-bold leading-4.5 rounded-md px-6 py-3 tracking-wide border border-primary-600 text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 transition-all duration-300'])); ?>>
    <?php echo e($slot); ?>

</button>
<?php /**PATH /var/www/smartScol/resources/views/components/primary-button.blade.php ENDPATH**/ ?>