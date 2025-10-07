{{-- resources/views/livewire/resultats/partials/tableaux-decisions-paces.blade.php --}}
<div class="space-y-6">
    {{-- ADMIS --}}
    @if(!empty($resultatsGroupes['admis']))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-green-600 dark:text-green-400">
                    ✅ ADMIS ({{ count($resultatsGroupes['admis']) }})
                    @if($simulationActive)
                        <span class="ml-2 text-sm px-2 py-1 bg-blue-100 text-blue-800 rounded">Simulation</span>
                    @endif
                </h3>
                <div class="flex gap-2">
                    <button wire:click="exporterPDF('admis')" 
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                        📄 PDF
                    </button>
                    <button wire:click="exporterExcel('admis')" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                        📊 Excel
                    </button>
                </div>
            </div>
            @include('livewire.resultats.partials.tableau-resultats-paces', [
                'resultats' => $resultatsGroupes['admis'],
                'couleur' => 'green',
                'uesStructure' => $uesStructure
            ])
        </div>
    @endif

    {{-- REDOUBLANTS --}}
    @if(!empty($resultatsGroupes['redoublant']))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-orange-600 dark:text-orange-400">
                    🔄 REDOUBLANTS ({{ count($resultatsGroupes['redoublant']) }})
                    @if($simulationActive)
                        <span class="ml-2 text-sm px-2 py-1 bg-blue-100 text-blue-800 rounded">Simulation</span>
                    @endif
                </h3>
                <div class="flex gap-2">
                    <button wire:click="exporterPDF('redoublant')" 
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                        📄 PDF
                    </button>
                    <button wire:click="exporterExcel('redoublant')" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                        📊 Excel
                    </button>
                </div>
            </div>
            @include('livewire.resultats.partials.tableau-resultats-paces', [
                'resultats' => $resultatsGroupes['redoublant'],
                'couleur' => 'orange',
                'uesStructure' => $uesStructure
            ])
        </div>
    @endif

    {{-- EXCLUS --}}
    @if(!empty($resultatsGroupes['exclus']))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-red-600 dark:text-red-400">
                    ❌ EXCLUS ({{ count($resultatsGroupes['exclus']) }})
                    @if($simulationActive)
                        <span class="ml-2 text-sm px-2 py-1 bg-blue-100 text-blue-800 rounded">Simulation</span>
                    @endif
                </h3>
                <div class="flex gap-2">
                    <button wire:click="exporterPDF('exclus')" 
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                        📄 PDF
                    </button>
                    <button wire:click="exporterExcel('exclus')" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                        📊 Excel
                    </button>
                </div>
            </div>
            @include('livewire.resultats.partials.tableau-resultats-paces', [
                'resultats' => $resultatsGroupes['exclus'],
                'couleur' => 'red',
                'uesStructure' => $uesStructure
            ])
        </div>
    @endif
</div>