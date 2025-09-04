{{-- Modal de base réutilisable --}}
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
     x-show="$wire.{{ $show }}"
     x-cloak
     role="dialog"
     aria-modal="true">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ $title }}
        </h3>
        
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ $message }}
            </p>
            
            @if(isset($infoMessage))
                <div class="p-3 mt-3 border rounded-md {{ $infoClass }}">
                    <p class="text-sm">
                        {!! $infoMessage !!}
                    </p>
                </div>
            @endif
        </div>
        
        <div class="flex justify-end space-x-3">
            <button
                wire:click="$set('{{ $show }}', false)"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 disabled:opacity-50">
                Annuler
            </button>
            
            <button
                wire:click="{{ $confirmMethod }}"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 {{ $confirmClass }}">
                {{ $confirmText }}
                <span wire:loading wire:target="{{ $confirmMethod }}" class="ml-2 animate-spin">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </span>
            </button>
        </div>
    </div>
</div>