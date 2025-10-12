{{-- resources/views/livewire/resultats/partials/filtres-decisions.blade.php --}}

<div class="flex flex-wrap items-center gap-2" wire:key="filtres-{{ $resultatsVersion ?? 'default' }}">
    @php
        $btnBase = 'px-3 py-1.5 rounded-lg text-sm font-medium border transition-all duration-200 flex items-center gap-2';
        
        // ✅ Stats : simulation si active, sinon DB
        $stats = $statistiquesDetailes ?? [];
        $totalResultats = ($stats['admis'] ?? 0) + 
                         ($stats['redoublant_autorises'] ?? 0) + 
                         ($stats['exclus'] ?? 0);
        
        // ✅ Filtre actif avec fallback
        $filtreActif = $filtreDecision ?? 'tous';
    @endphp
    
    {{-- Bouton TOUS --}}
    <button 
        wire:click="changerFiltre('tous')" 
        class="{{ $btnBase }} {{ $filtreActif === 'tous' 
            ? 'bg-primary-600 text-white shadow-md dark:bg-gray-100 dark:text-gray-900 border-gray-900 dark:border-gray-100 ring-2 ring-primary-400 dark:ring-gray-400' 
            : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
    >
        <span>Tous</span>
        <span class="font-bold px-1.5 py-0.5 rounded {{ $filtreActif === 'tous' ? 'bg-white/20' : 'bg-gray-100 dark:bg-gray-700' }}">
            {{ $totalResultats }}
        </span>
    </button>
    
    {{-- Bouton ADMIS --}}
    <button 
        wire:click="changerFiltre('admis')" 
        class="{{ $btnBase }} {{ $filtreActif === 'admis' 
            ? 'bg-green-600 text-white border-green-600 shadow-md ring-2 ring-green-400' 
            : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
    >
        <span>Admis</span>
        <span class="font-bold px-1.5 py-0.5 rounded {{ $filtreActif === 'admis' ? 'bg-white/20' : 'bg-gray-100 dark:bg-gray-700' }}">
            {{ $stats['admis'] ?? 0 }}
        </span>
    </button>
    
    {{-- Bouton REDOUBLANTS --}}
    <button 
        wire:click="changerFiltre('redoublant')" 
        class="{{ $btnBase }} {{ $filtreActif === 'redoublant' 
            ? 'bg-orange-500 text-white border-orange-500 shadow-md ring-2 ring-orange-400' 
            : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
    >
        <span>Redoublants</span>
        <span class="font-bold px-1.5 py-0.5 rounded {{ $filtreActif === 'redoublant' ? 'bg-white/20' : 'bg-gray-100 dark:bg-gray-700' }}">
            {{ $stats['redoublant_autorises'] ?? 0 }}
        </span>
    </button>
    
    {{-- Bouton EXCLUS --}}
    <button 
        wire:click="changerFiltre('exclus')" 
        class="{{ $btnBase }} {{ $filtreActif === 'exclus' 
            ? 'bg-red-600 text-white border-red-600 shadow-md ring-2 ring-red-400' 
            : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
    >
        <span>Exclus</span>
        <span class="font-bold px-1.5 py-0.5 rounded {{ $filtreActif === 'exclus' ? 'bg-white/20' : 'bg-gray-100 dark:bg-gray-700' }}">
            {{ $stats['exclus'] ?? 0 }}
        </span>
    </button>
</div>