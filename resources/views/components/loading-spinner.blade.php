{{-- oading-spinner  --}}
@props([
    'target' => null,
    'message' => 'Chargement en cours',
    'description' => 'Veuillez patienter...',
])

<div {{ $attributes->merge(['class' => 'fixed top-0 left-0 right-0 bottom-0 bg-slate-900/60 dark:bg-black/70 backdrop-blur-md z-50']) }}
     @if($target) wire:loading wire:target="{{ $target }}" @else wire:loading @endif>
    
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
        <div class="text-center">
            {{-- Spinner circulaire --}}
            <div class="relative inline-block mb-6">
                <svg class="animate-spin h-32 w-32 text-primary-600 dark:text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                
                {{-- Icône au centre --}}
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                    <svg class="w-12 h-12 text-primary-700 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>

            {{-- Message --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl px-8 py-6 border border-gray-200 dark:border-gray-700 w-80 mx-auto">
                <div class="flex items-center justify-center gap-2 mb-3">
                    <div class="flex gap-1">
                        <span class="w-2 h-2 bg-primary-600 dark:bg-primary-500 rounded-full animate-bounce"></span>
                        <span class="w-2 h-2 bg-primary-600 dark:bg-primary-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
                        <span class="w-2 h-2 bg-primary-600 dark:bg-primary-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                    </div>
                </div>
                
                <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                    {{ $message }}
                </p>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    {{ $description }}
                </p>
                
                {{-- Barre de progression --}}
                <div class="mt-4 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-primary-500 to-primary-600 rounded-full animate-pulse"></div>
                </div>
            </div>
        </div>
    </div>
</div>