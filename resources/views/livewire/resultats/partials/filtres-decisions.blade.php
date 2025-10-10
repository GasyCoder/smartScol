<div class="flex flex-wrap items-center gap-2">
    @php
        $btnBase = 'px-3 py-1.5 rounded-lg text-sm font-medium border transition';
        $totalResultats = ($statistiquesDetailes['admis'] ?? 0) + 
        ($statistiquesDetailes['redoublant_autorises'] ?? 0) + 
        ($statistiquesDetailes['exclus'] ?? 0);
    @endphp
    <button wire:click="changerFiltre('tous')" 
            wire:loading.attr="disabled"
            class="{{ $btnBase }} {{ $filtreDecision==='tous' ? 'bg-primary-600 text-white shadow-md dark:bg-gray-100 dark:text-gray-900 border-gray-900 dark:border-gray-100' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
        Tous <span class="font-bold">{{ $totalResultats }}</span>
        {{-- Loading spinner à droite --}}
        <svg wire:loading wire:target="changerFiltre('tous')" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </button>
    <button wire:click="changerFiltre('admis')" 
            wire:loading.attr="disabled"
            class="{{ $btnBase }} {{ $filtreDecision==='admis' ? 'bg-green-600 text-white border-green-600' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
        Admis  <span class="font-bold">{{ $statistiquesDetailes['admis'] ?? 0 }}</span>
        {{-- Loading spinner à droite --}}
        <svg wire:loading wire:target="changerFiltre('admis')" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </button>
    <button wire:click="changerFiltre('redoublant')" 
            wire:loading.attr="disabled"
            class="{{ $btnBase }} {{ $filtreDecision==='redoublant' ? 'bg-orange-500 text-white border-orange-500' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
        Redoublants <span class="font-bold">{{ $statistiquesDetailes['redoublant_autorises'] ?? 0 }}</span>
        {{-- Loading spinner à droite --}}
        <svg wire:loading wire:target="changerFiltre('redoublant')" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </button>
    <button wire:click="changerFiltre('exclus')" 
            wire:loading.attr="disabled"
            class="{{ $btnBase }} {{ $filtreDecision==='exclus' ? 'bg-red-600 text-white border-red-600' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
        Exclus <span class="font-bold">{{ $statistiquesDetailes['exclus'] ?? 0 }}</span>
        {{-- Loading spinner à droite --}}
        <svg wire:loading wire:target="changerFiltre('exclus')" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </button>
</div>