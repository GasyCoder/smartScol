<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="px-3 sm:px-3 lg:px-4">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Modifier l'examen</h1>
                    
                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-medium">{{ $niveauInfo['nom'] }}</span>
                        <span>/</span>
                        <span class="font-medium">{{ $parcoursInfo['nom'] }}</span>
                        <span>/</span>
                        <span class="font-medium text-blue-600 dark:text-blue-400">Modifier examen</span>
                    </nav>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex items-center space-x-3">
                    <button wire:click="retourIndex" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Retour
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-4 sm:px-6 lg:px-8 py-8">

            <!-- Informations générales -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations générales</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Durée -->
                    <div>
                        <label for="duree" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Durée (en minutes) *
                        </label>
                        <input type="number" 
                               id="duree"
                               wire:model="duree"
                               min="30" 
                               max="300"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('duree')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Note éliminatoire -->
                    <div>
                        <label for="note_eliminatoire" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Note éliminatoire (optionnel)
                        </label>
                        <input type="number" 
                               id="note_eliminatoire"
                               wire:model="note_eliminatoire"
                               step="0.1"
                               min="0" 
                               max="20"
                               placeholder="Ex: 8.0"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('note_eliminatoire')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Matières de l'examen -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Matières de l'examen ({{ count($ecs_data) }})</h2>
                    <button wire:click="verifierConflits" 
                            class="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Vérifier les conflits
                    </button>
                </div>

                @if(count($ecs_data) > 0)
                    <div class="space-y-4">
                        @foreach($ecs_data as $index => $ec)
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="font-medium text-gray-900 dark:text-white">
                                            {{ $ec['ec_abr'] ? $ec['ec_abr'] . ' - ' : '' }}{{ $ec['ec_nom'] }}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            UE: {{ $ec['ue_nom'] }} | Enseignant: {{ $ec['enseignant'] ?: 'Non assigné' }}
                                        </p>
                                    </div>
                                    <button wire:click="removeEC({{ $index }})" 
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <!-- Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date *</label>
                                        <input type="date" 
                                               wire:model="ecs_data.{{ $index }}.date_specifique"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        @error("ecs_data.{$index}.date_specifique")
                                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                        
                                        {{-- Avertissement si date passée --}}
                                        @if(!empty($ec['date_specifique']) && \Carbon\Carbon::parse($ec['date_specifique'])->isPast())
                                            <p class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                                                ⚠️ Cette date est dans le passé
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Heure -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Heure *</label>
                                        <input type="time" 
                                               wire:model="ecs_data.{{ $index }}.heure_specifique"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        @error("ecs_data.{$index}.heure_specifique")
                                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Salle -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salle</label>
                                        <select wire:model="ecs_data.{{ $index }}.salle_id"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Aucune salle</option>
                                            @foreach($salles as $salle)
                                                <option value="{{ $salle->id }}">{{ $salle->nom }}</option>
                                            @endforeach
                                        </select>
                                        @error("ecs_data.{$index}.salle_id")
                                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Code base -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                                        <input type="text" 
                                               wire:model="ecs_data.{{ $index }}.code_base"
                                               maxlength="10"
                                               placeholder="Ex: TA, TB..."
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        @error("ecs_data.{$index}.code_base")
                                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">Aucune matière ajoutée. Utilisez les sections ci-dessous pour ajouter des matières.</p>
                    </div>
                @endif
            </div>

            <!-- Ajouter des matières -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ajouter des matières</h2>
                
                @foreach($ues as $ue)
                    <div class="mb-6">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-3">
                            {{ $ue->abr ? $ue->abr . ' - ' : '' }}{{ $ue->nom }}
                        </h3>
                        
                        @if($ue->ecs->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($ue->ecs as $ec)
                                    @php
                                        $isAdded = collect($ecs_data)->where('ec_id', $ec->id)->first();
                                    @endphp
                                    
                                    <button wire:click="addEC({{ $ec->id }})"
                                            @if($isAdded) disabled @endif
                                            class="p-3 text-left border rounded-lg transition-colors
                                                @if($isAdded) 
                                                    bg-green-50 border-green-200 text-green-800 cursor-not-allowed
                                                    dark:bg-green-900/20 dark:border-green-800 dark:text-green-200
                                                @else 
                                                    bg-gray-50 border-gray-200 hover:bg-blue-50 hover:border-blue-300
                                                    dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-blue-900/20 dark:hover:border-blue-600
                                                @endif">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h4 class="font-medium">{{ $ec->abr ? $ec->abr . ' - ' : '' }}{{ $ec->nom }}</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                    Enseignant: {{ $ec->enseignant ?: 'Non assigné' }}
                                                </p>
                                            </div>
                                            @if($isAdded)
                                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 italic">Aucune matière disponible dans cette UE.</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Conflits de salles -->
            @if($showConflits && count($conflits) > 0)
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6 dark:bg-red-900/20 dark:border-red-800">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">Conflits de salles détectés</h3>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($conflits as $conflit)
                            <div class="bg-white dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-red-800 dark:text-red-200">{{ $conflit['ec_nom'] }}</h4>
                                        <p class="text-sm text-red-600 dark:text-red-300 mt-1">
                                            Salle {{ $conflit['salle_nom'] }} le {{ \Carbon\Carbon::parse($conflit['date'])->format('d/m/Y') }} à {{ $conflit['heure'] }}
                                        </p>
                                        @if($conflit['type'] === 'interne')
                                            <p class="text-xs text-red-500 dark:text-red-400 mt-1">
                                                Conflit avec: {{ $conflit['conflit_avec'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($conflit['type'] === 'existant') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200
                                        @else bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-200 @endif">
                                        {{ $conflit['type'] === 'existant' ? 'Examen existant' : 'Conflit interne' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($showConflits && count($conflits) === 0)
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6 dark:bg-green-900/20 dark:border-green-800">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">Aucun conflit détecté</h3>
                    </div>
                    <p class="text-sm text-green-600 dark:text-green-300 mt-2">
                        Toutes les salles sont disponibles aux créneaux sélectionnés. Vous pouvez enregistrer l'examen.
                    </p>
                </div>
            @endif

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex justify-between items-center">
                    <button wire:click="retourIndex" 
                            class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Annuler
                    </button>
                    
                    <div class="flex space-x-3">
                        <button wire:click="verifierConflits" 
                                class="px-6 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors">
                            Vérifier les conflits
                        </button>
                        
                        <button wire:click="save" 
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                            <span wire:loading.remove>
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Enregistrer les modifications
                            </span>
                            <span wire:loading>
                                <svg class="w-4 h-4 mr-2 inline animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Enregistrement...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
    </div>
</div>

@push('scripts')
    <!-- Script pour redirection uniquement -->
<script>
    document.addEventListener('livewire:initialized', function () {
        Livewire.on('redirect', function (data) {
            const url = data[0].url;
            const delay = data[0].delay || 0;
            
            setTimeout(function() {
                window.location.href = url;
            }, delay);
        });
    });
</script>
@endpush