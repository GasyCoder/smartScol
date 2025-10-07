{{-- resources/views/livewire/resultats/partials/affichage-resultats-paces.blade.php --}}
<div class="space-y-6">
    
    {{-- ✅ Loading UNIQUEMENT pour exports et application (pas pour simulation) --}}
    <div wire:loading.flex wire:target="exporterPDF,exporterExcel,simulerDeliberation,appliquerDeliberation,annulerSimulation" 
         class="fixed inset-0 bg-slate-900/80 dark:bg-black/85 backdrop-blur-sm z-[9999] items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl px-8 py-6 min-w-[320px] border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <svg class="animate-spin h-10 w-10 text-primary-600 dark:text-primary-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">Traitement en cours...</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Veuillez patienter</div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- ===== DASHBOARD STATISTIQUES ===== --}}
    @include('livewire.resultats.partials.dashboard-statistiques-paces')

    {{-- ===== PARAMÈTRES SIMULATION ===== --}}
    @include('livewire.resultats.partials.parametre-simulation')

    {{-- ===== FILTRES DÉCISION ===== --}}
    @include('livewire.resultats.partials.filtres-decisions')
    
    
    {{-- ===== TABLEAU DES RÉSULTATS ===== --}}
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 {{ $simulationEnCours ? 'ring-4 ring-amber-400 ring-opacity-50' : '' }}">
        @php
            $config = match($filtreDecision) {
                'admis' => ['titre' => 'ADMIS', 'couleur' => 'green', 'icone' => '✓'],
                'redoublant' => ['titre' => 'AUTORISÉS À REDOUBLER', 'couleur' => 'orange', 'icone' => '↻'],
                'exclus' => ['titre' => 'EXCLUS', 'couleur' => 'red', 'icone' => '✗'],
                default => ['titre' => 'Tous les résultats', 'couleur' => 'blue', 'icone' => '📋']
            };
        @endphp

        {{-- En-tête --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-2">
            <div class="flex items-center gap-3">
                <div class="text-4xl">{{ $config['icone'] }}</div>
                <div>
                    <h3 class="text-2xl font-bold text-{{ $config['couleur'] }}-600 dark:text-{{ $config['couleur'] }}-400">
                        {{ $config['titre'] }}
                        @if($simulationEnCours)
                            <span class="text-lg text-amber-600 dark:text-amber-400 ml-2 animate-pulse">
                                (Simulé ⚡)
                            </span>
                        @endif
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $this->resultats_pagines->total() }} étudiant(s)
                    </p>
                </div>
            </div>

            {{-- Boutons export --}}
            <div class="flex gap-2">
                <button wire:click="exporterPDF('{{ $filtreDecision }}')" 
                        {{ $simulationEnCours ? 'disabled' : '' }}
                        class="flex items-center gap-2 px-4 py-2 rounded-lg transition shadow-sm text-sm font-medium
                               {{ $simulationEnCours ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700 text-white' }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                    </svg>
                    PDF
                </button>
                <button wire:click="exporterExcel('{{ $filtreDecision }}')" 
                        {{ $simulationEnCours ? 'disabled' : '' }}
                        class="flex items-center gap-2 px-4 py-2 rounded-lg transition shadow-sm text-sm font-medium
                               {{ $simulationEnCours ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700 text-white' }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Excel
                </button>
            </div>
        </div>

        {{-- Contrôles pagination --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Afficher :
                </label>
                <select wire:model.live="perPage" 
                        class="rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-600 dark:text-gray-400">par page</span>
            </div>

            <div class="text-sm text-gray-600 dark:text-gray-400">
                @if($this->resultats_pagines->total() > 0)
                    <span class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $this->infos_pagination['de'] }} - {{ $this->infos_pagination['a'] }}
                    </span>
                    sur
                    <span class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $this->infos_pagination['total'] }}
                    </span>
                    résultats
                @endif
            </div>
        </div>

        {{-- Tableau ou message vide --}}
        @if($this->resultats_pagines->total() > 0)
            @include('livewire.resultats.partials.tableau-resultats-paces', [
                'resultats' => $this->resultats_pagines->items(),
                'couleur' => $config['couleur'],
                'uesStructure' => $uesStructure
            ])
            
            {{-- Pagination --}}
            <div class="mt-6">
                {{ $this->resultats_pagines->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xl font-medium text-gray-500 dark:text-gray-400">Aucun résultat dans cette catégorie</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">
                    Essayez de modifier les paramètres de simulation ou de sélectionner un autre filtre
                </p>
            </div>
        @endif
    </div>
</div>

{{-- CSS pour l'animation pulse-slow --}}
<style>
@keyframes pulse-slow {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .85;
    }
}

.animate-pulse-slow {
    animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>