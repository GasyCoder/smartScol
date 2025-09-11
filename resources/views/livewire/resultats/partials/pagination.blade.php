@if($totalPages > 1)
    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 sm:px-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Informations et sélecteur de résultats par page -->
            <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <span>
                    Affichage de {{ ($currentPage - 1) * $perPage + 1 }} à {{ min($currentPage * $perPage, $totalResultats) }} sur {{ $totalResultats }} résultats
                </span>
                <select wire:model.live="perPage" class="px-2 py-1 border rounded bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span wire:loading wire:target="nextPage,previousPage,goToPage,perPage" class="flex items-center gap-1 text-blue-600 dark:text-blue-400">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8h8a8 8 0 01-16 0z"></path>
                    </svg>
                    <span>Chargement...</span>
                </span>
            </div>

            <!-- Contrôles de pagination -->
            <div class="flex items-center gap-1">
                <!-- Première page -->
                <button wire:click="goToPage(1)" 
                        wire:loading.attr="disabled"
                        class="px-2 py-1 text-sm font-medium rounded-md border border-gray-300 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed dark:disabled:bg-gray-600 dark:disabled:text-gray-500"
                        {{ $currentPage == 1 ? 'disabled' : '' }}>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Page précédente -->
                <button wire:click="previousPage" 
                        wire:loading.attr="disabled"
                        class="px-2 py-1 text-sm font-medium rounded-md border border-gray-300 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed dark:disabled:bg-gray-600 dark:disabled:text-gray-500"
                        {{ $currentPage == 1 ? 'disabled' : '' }}>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Pages numérotées -->
                @php
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $currentPage + 2);
                @endphp

                @if($start > 1)
                    <button wire:click="goToPage(1)" 
                            wire:loading.attr="disabled"
                            class="px-2 py-1 text-sm font-medium rounded-md border border-gray-300 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200">
                        1
                    </button>
                    @if($start > 2)
                        <span class="px-2 text-gray-500 dark:text-gray-400">...</span>
                    @endif
                @endif

                @for($i = $start; $i <= $end; $i++)
                    <button wire:click="goToPage({{ $i }})" 
                            wire:loading.attr="disabled"
                            class="px-2 py-1 text-sm font-medium rounded-md border transition-colors duration-200 {{ $i == $currentPage ? 'bg-blue-600 text-white border-blue-600 dark:bg-blue-500 dark:border-blue-500' : 'bg-white border-gray-300 hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        {{ $i }}
                    </button>
                @endfor

                @if($end < $totalPages)
                    @if($end < $totalPages - 1)
                        <span class="px-2 text-gray-500 dark:text-gray-400">...</span>
                    @endif
                    <button wire:click="goToPage({{ $totalPages }})" 
                            wire:loading.attr="disabled"
                            class="px-2 py-1 text-sm font-medium rounded-md border border-gray-300 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200">
                        {{ $totalPages }}
                    </button>
                @endif

                <!-- Page suivante -->
                <button wire:click="nextPage" 
                        wire:loading.attr="disabled"
                        class="px-2 py-1 text-sm font-medium rounded-md border border-gray-300 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed dark:disabled:bg-gray-600 dark:disabled:text-gray-500"
                        {{ $currentPage == $totalPages ? 'disabled' : '' }}>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- Dernière page -->
                <button wire:click="goToPage({{ $totalPages }})" 
                        wire:loading.attr="disabled"
                        class="px-2 py-1 text-sm font-medium rounded-md border border-gray-300 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed dark:disabled:bg-gray-600 dark:disabled:text-gray-500"
                        {{ $currentPage == $totalPages ? 'disabled' : '' }}>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7m-8-14l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let isLoading = false;

            // Écouter les événements Livewire
            Livewire.on('paginationStarted', () => {
                isLoading = true;
            });

            Livewire.on('paginationCompleted', () => {
                isLoading = false;
            });

            // Désactiver les clics pendant le chargement
            document.querySelectorAll('[wire\\:click="nextPage"], [wire\\:click="previousPage"], [wire\\:click^="goToPage"]').forEach(button => {
                button.addEventListener('click', (event) => {
                    if (isLoading) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
    @endpush
@endif