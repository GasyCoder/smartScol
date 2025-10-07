{{-- resources/views/livewire/resultats/partials/modal-chargement-simulation.blade.php --}}
<div x-data="{ 
        show: false,
        etape: @entangle('etapeSimulation').live,
        dots: '.',
        dotsInterval: null
    }"
     x-init="
        $watch('etape', value => {
            show = value !== '';
            if (show) {
                dotsInterval = setInterval(() => {
                    dots = dots.length >= 3 ? '.' : dots + '.';
                }, 500);
            } else {
                if (dotsInterval) clearInterval(dotsInterval);
                dots = '.';
            }
        });
     "
     x-show="show"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    {{-- Overlay avec animation --}}
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm"></div>
    
    {{-- Modal --}}
    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-8 overflow-hidden">
            
            {{-- Effet de fond animé --}}
            <div class="absolute inset-0 bg-gradient-to-br from-primary-50 to-blue-50 dark:from-primary-900/10 dark:to-blue-900/10"></div>
            
            {{-- Contenu --}}
            <div class="relative z-10">
                {{-- Spinner élégant --}}
                <div class="flex justify-center mb-6">
                    <div class="relative">
                        {{-- Cercle extérieur rotatif --}}
                        <div class="w-24 h-24 border-4 border-primary-200 dark:border-primary-800 rounded-full animate-spin border-t-primary-600 dark:border-t-primary-400"></div>
                        
                        {{-- Cercle intérieur pulsant --}}
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-blue-500 rounded-full animate-pulse flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Titre --}}
                <h3 class="text-center text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    Simulation en cours
                </h3>
                
                {{-- Étape avec animation --}}
                <div class="text-center mb-6">
                    <p class="text-lg text-primary-600 dark:text-primary-400 font-semibold">
                        <span x-text="etape"></span><span x-text="dots"></span>
                    </p>
                </div>
                
                {{-- Barre de progression indéterminée --}}
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden mb-6">
                    <div class="h-full bg-gradient-to-r from-primary-500 via-blue-500 to-primary-500 animate-progress-indeterminate"></div>
                </div>
                
                {{-- Message info --}}
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
                                Calcul en cours
                            </p>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                Veuillez patienter, cela peut prendre quelques secondes...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CSS pour l'animation de la barre --}}
<style>
@keyframes progress-indeterminate {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(400%);
    }
}

.animate-progress-indeterminate {
    animation: progress-indeterminate 1.5s ease-in-out infinite;
    width: 25%;
}
</style>