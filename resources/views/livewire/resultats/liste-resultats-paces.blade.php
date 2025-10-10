{{-- resources/views/livewire/resultats/liste-resultats-paces.blade.php --}}
<div class="space-y-4">

    {{-- ===== BREADCRUMB (garde ton code) ===== --}}
    <div class="flex items-center gap-2 text-sm">
        <button wire:click="retourSelection" 
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all
                       {{ $etape === 'selection' 
                          ? 'bg-primary-600 text-white shadow-sm' 
                          : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Parcours</span>
        </button>
        
        @if($etape === 'resultats' && $parcoursData)
            <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
            </svg>
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $parcoursData->nom }}</span>
                <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300">
                    {{ $parcoursSlug }}
                </span>
            </div>
        @endif
    </div>

    {{-- ===== SPINNER CENTRÉ (ton composant) ===== --}}
    <x-loading-spinner 
        target="selectionnerParcours" 
        message="Chargement des résultats"
        description="Récupération des données du parcours..." />

    {{-- ===== ÉTAPE SÉLECTION ===== --}}
    @if($etape === 'selection')
        @include('livewire.resultats.partials.selection-parcours-paces')
    @endif

    {{-- ===== ÉTAPE RÉSULTATS ===== --}}
    @if($etape === 'resultats')
        @include('livewire.resultats.partials.affichage-resultats-paces')
    @endif

</div>
