{{-- vue setup avec calculatrice enveloppes --}}
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
    <!-- En-tête -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-600">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Configuration des présences
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ $ecSelected?->nom ?? 'Matière' }} ({{ $ecSelected?->abr ?? 'N/A' }})
            </p>
        </div>
        <button wire:click="backToStep('ec')" 
                class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md">
            ← Retour aux matières
        </button>
    </div>

    <div class="p-6">
        <!-- Informations contextuelles -->
        <div class="mb-8 grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="text-center">
                <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $totalEtudiantsTheorique }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Inscrits</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $codeSalle ?? 'N/A' }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Code Salle</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $progressCount }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Saisies faites</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ $this->getRemainingManchettes() }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Restantes</div>
            </div>
        </div>

        <!-- Zone principale de configuration -->
        <div class="space-y-6">
            
            <!-- État : Pas encore configuré -->
            @if(!$hasExistingPresence && !$isEditingPresence)
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        Configuration requise
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Définissez le nombre d'étudiants présents pour commencer la saisie
                    </p>
                    <button wire:click="startEditingPresence" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                        📋 Configurer les présences
                    </button>
                </div>
            @endif

            <!-- État : Configuration existante, affichage -->
            @if($hasExistingPresence && !$isEditingPresence)
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-4">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <h3 class="text-lg font-semibold text-green-800 dark:text-green-300">
                                    Présences configurées
                                </h3>
                            </div>
                            
                            <!-- Statistiques actuelles -->
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="text-center p-3 bg-white dark:bg-gray-800 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $totalManchettesPresentes }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Présents</div>
                                </div>
                                <div class="text-center p-3 bg-white dark:bg-gray-800 rounded-lg">
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $this->totalAbsents }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Absents</div>
                                </div>
                                <div class="text-center p-3 bg-white dark:bg-gray-800 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $this->pourcentagePresence }}%</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Taux présence</div>
                                </div>
                            </div>

                            <!-- Barre de progression si saisie en cours -->
                            @if($progressCount > 0)
                                <div class="mb-4">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600 dark:text-gray-400">Progression saisie</span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ $progressCount }}/{{ $totalManchettesPresentes }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" 
                                             style="width: {{ $totalManchettesPresentes > 0 ? ($progressCount / $totalManchettesPresentes) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="ml-6 flex flex-col space-y-2">
                            <button wire:click="startEditingPresence" 
                                    class="px-4 py-2 text-sm border border-green-600 text-green-600 hover:bg-green-50 dark:border-green-400 dark:text-green-400 dark:hover:bg-green-900/20 rounded-md">
                                ✏️ Modifier
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- État : Mode édition -->
            @if($isEditingPresence)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-300 mb-6">
                        {{ $hasExistingPresence ? 'Modifier les présences' : 'Configurer les présences' }}
                    </h3>
                    
                    <div class="space-y-6">
                    <!-- Champ de saisie -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="totalManchettesPresentes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nombre d'étudiants présents <span class="text-red-500">*</span>
                            </label>
                            <button wire:click="toggleEnvelopeCalculator" 
                                    class="px-2 py-0.5 text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 underline hover:no-underline">
                                {{ $show_envelope_calculator ? 'Saisie directe' : 'Calculatrice' }}
                            </button>
                        </div>
    
                    <!-- Champ principal - CACHÉ si calculatrice active -->
                    @if(!$show_envelope_calculator)
                        <input type="number" 
                            wire:model.live="totalManchettesPresentes" 
                            id="totalManchettesPresentes"
                            min="{{ $progressCount }}" 
                            max="{{ $totalEtudiantsTheorique }}"
                            class="w-full px-4 py-3 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            placeholder="Ex: 25">
                    @endif
                    
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        @if($progressCount > 0)
                            <p>Minimum {{ $progressCount }} (déjà {{ $progressCount }} manchettes saisies)</p>
                        @endif
                        <p>Maximum {{ $totalEtudiantsTheorique }} étudiants inscrits</p>
                    </div>

                    @error('totalManchettesPresentes') 
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                    <!-- Calculatrice par enveloppes -->
                    @if($show_envelope_calculator)
                        <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">
                                Calculatrice enveloppes
                            </h4>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                                <div>
                                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Enveloppe 1</label>
                                    <input type="number" 
                                        wire:model.live="enveloppe1" 
                                        min="0" max="100"
                                        value="{{ $enveloppe1 }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-center bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Enveloppe 2</label>
                                    <input type="number" 
                                        wire:model.live="enveloppe2" 
                                        min="0" max="100"
                                        value="{{ $enveloppe2 }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-center bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Enveloppe 3</label>
                                    <input type="number" 
                                        wire:model.live="enveloppe3" 
                                        min="0" max="100"
                                        value="{{ $enveloppe3 }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-center bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Enveloppe 4</label>
                                    <input type="number" 
                                        wire:model.live="enveloppe4" 
                                        min="0" max="100"
                                        value="{{ $enveloppe4 }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-center bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                </div>
                            </div>
                            
                            <!-- Résultat FINAL comme champ principal -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-blue-800 dark:text-blue-300">Calcul:</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $enveloppe1 ?: '0' }} + {{ $enveloppe2 ?: '0' }} + {{ $enveloppe3 ?: '0' }} + {{ $enveloppe4 ?: '0' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                            Total: {{ (is_numeric($enveloppe1) ? (int)$enveloppe1 : 0) + (is_numeric($enveloppe2) ? (int)$enveloppe2 : 0) + (is_numeric($enveloppe3) ? (int)$enveloppe3 : 0) + (is_numeric($enveloppe4) ? (int)$enveloppe4 : 0) }} étudiants
                                        </div>
                                        <div class="text-xs text-blue-600 dark:text-blue-400">Ce total sera enregistré</div>
                                    </div>
                                    <button wire:click="clearEnvelopes" 
                                            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                        Effacer
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                        <!-- Preview automatique -->
                        @if($totalManchettesPresentes > 0)
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                                <h4 class="text-xs font-medium text-gray-900 dark:text-white mb-2">Aperçu</h4>
                                <div class="flex justify-between text-center text-xs">
                                    <div>
                                        <div class="text-sm font-bold text-green-600 dark:text-green-400">{{ $totalManchettesPresentes }}</div>
                                        <div class="text-gray-600 dark:text-gray-400">Présents</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-red-600 dark:text-red-400">{{ $totalEtudiantsTheorique - $totalManchettesPresentes }}</div>
                                        <div class="text-gray-600 dark:text-gray-400">Absents</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $totalEtudiantsTheorique > 0 ? round(($totalManchettesPresentes / $totalEtudiantsTheorique) * 100, 1) : 0 }}%</div>
                                        <div class="text-gray-600 dark:text-gray-400">Taux</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Boutons d'action -->
                        <div class="flex space-x-4">
                            <button wire:click="savePresence" 
                                    class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                    {{ $totalManchettesPresentes < 1 || $totalManchettesPresentes > $totalEtudiantsTheorique ? 'disabled' : '' }}>
                                Enregistrer
                            </button>
                            <button wire:click="cancelEditingPresence" 
                                    class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Bouton pour aller à la saisie -->
            @if($this->canStartSaisie())
                <div class="text-center py-6 border-t border-gray-200 dark:border-gray-600">
                    @if($this->getRemainingManchettes() > 0)
                        <button wire:click="goToSaisie" 
                                class="px-8 py-4 bg-green-600 hover:bg-green-700 text-white text-lg font-medium rounded-lg shadow-sm">
                            🏷️ Commencer la saisie ({{ $this->getRemainingManchettes() }} restante{{ $this->getRemainingManchettes() > 1 ? 's' : '' }})
                        </button>
                    @else
                        <div class="text-center py-4">
                            <div class="w-16 h-16 mx-auto mb-4 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-green-800 dark:text-green-300 mb-2">
                                ✅ Saisie terminée !
                            </h3>
                            <p class="text-green-600 dark:text-green-400">
                                Toutes les {{ $totalManchettesPresentes }} manchettes ont été saisies
                            </p>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</div>

@push('scripts')
    <script>
// Script à ajouter dans la vue setup pour améliorer l'UX
document.addEventListener('livewire:init', () => {
    
    // Auto-focus sur le champ nombre de présents quand on entre en mode édition
    Livewire.on('editing-started', () => {
        setTimeout(() => {
            const input = document.getElementById('totalManchettesPresentes');
            if (input) {
                input.focus();
                input.select(); // Sélectionner le contenu pour faciliter la modification
            }
        }, 100);
    });

    // Validation en temps réel du champ nombre de présents
    document.addEventListener('input', (e) => {
        if (e.target.id === 'totalManchettesPresentes') {
            const input = e.target;
            const value = parseInt(input.value);
            const min = parseInt(input.min);
            const max = parseInt(input.max);
            
            // Feedback visuel
            input.classList.remove('border-red-500', 'border-green-500');
            
            if (value < min || value > max || isNaN(value)) {
                input.classList.add('border-red-500');
            } else {
                input.classList.add('border-green-500');
            }
        }
    });

    // Raccourci clavier : Entrée pour sauvegarder quand on est en mode édition
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.id === 'totalManchettesPresentes') {
            e.preventDefault();
            const value = parseInt(e.target.value);
            const min = parseInt(e.target.min);
            const max = parseInt(e.target.max);
            
            if (value >= min && value <= max && !isNaN(value)) {
                // Déclencher la sauvegarde via Livewire
                Livewire.dispatch('save-presence-shortcut');
            }
        }
    });

});
</script>
@endpush