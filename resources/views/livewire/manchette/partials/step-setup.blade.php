{{-- vue setup avec calculatrice enveloppes - VERSION CORRIGÉE UE/EC --}}
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
    <!-- En-tête -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-600">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Configuration des présences
            </h2>
            <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 mt-1">
                {{ $ecSelected?->nom ?? 'Matière' }} ({{ $ecSelected?->abr ?? 'N/A' }})
                {{-- ✅ AJOUT : Affichage de l'UE --}}
                @if($ecSelected?->ue)
                    <span class="text-xs text-blue-600 dark:text-blue-400 ml-2">
                        UE: {{ $ecSelected->ue->nom }}
                    </span>
                @endif
            </p>
        </div>
        <button wire:click="backToStep('ec')" 
                class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md">
            ← Retour aux matières
        </button>
    </div>

    <div class="p-6">
        {{-- ✅ MODIFICATION : Information rattrapage spécifique à l'EC/UE --}}
        @if($sessionType === 'rattrapage' && !empty($statistiquesRattrapage))
            @php
                // Calculer les étudiants éligibles pour cette EC spécifique
                $etudiantsEligiblesEC = $this->getEtudiantsEligiblesPourEC($ecSelected->id ?? 0);
                $ueInfo = null;
                
                if ($ecSelected && $ecSelected->ue) {
                    // Chercher les infos de l'UE dans les statistiques
                    foreach ($statistiquesRattrapage['detail_etudiants'] as $etudiantStats) {
                        foreach ($etudiantStats['ues_non_validees'] as $ueData) {
                            if ($ueData['ue_id'] == $ecSelected->ue_id) {
                                $ueInfo = $ueData;
                                break 2;
                            }
                        }
                    }
                }
            @endphp

            <div class="mb-6 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-orange-800 dark:text-orange-300 mb-1">
                            Session de Rattrapage - EC spécifique
                        </h4>
                        <div class="text-xs text-orange-700 dark:text-orange-400 space-y-1">
                            @if($ueInfo)
                                <p>
                                    <strong>UE non validée :</strong> {{ $ueInfo['ue_nom'] ?? 'N/A' }} 
                                    (Moyenne: {{ $ueInfo['moyenne'] ?? 'N/A' }}/20)
                                </p>
                                <p>
                                    <strong>Étudiants concernés :</strong> {{ $etudiantsEligiblesEC->count() }} étudiant(s) 
                                    doivent rattraper cette UE entière
                                </p>
                                @if($ueInfo['nb_ecs'] > 1)
                                    <p class="italic">
                                        Note: Cette UE contient {{ $ueInfo['nb_ecs'] }} EC(s). 
                                        Les étudiants devront rattraper TOUTES les ECs de cette UE.
                                    </p>
                                @endif
                            @else
                                <p>
                                    <strong>{{ $etudiantsEligiblesEC->count() }}</strong> étudiant(s) 
                                    éligible(s) pour cette EC en rattrapage.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Informations contextuelles -->
        <div class="mb-8 grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="text-center">
                <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $totalEtudiantsTheorique }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    {{-- ✅ MODIFICATION : Texte précis selon la session --}}
                    @if($sessionType === 'rattrapage')
                        Éligibles pour cette EC
                    @else
                        Inscrits
                    @endif
                </div>
                {{-- ✅ AJOUT : Détail pour rattrapage --}}
                @if($sessionType === 'rattrapage' && $ecSelected && $ecSelected->ue)
                    <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                        UE: {{ $ecSelected->ue->nom }}
                    </div>
                @endif
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
                        {{-- ✅ MODIFICATION : Message adapté au contexte --}}
                        @if($sessionType === 'rattrapage')
                            Définissez le nombre d'étudiants présents parmi les {{ $totalEtudiantsTheorique }} éligible(s) à cette EC
                        @else
                            Définissez le nombre d'étudiants présents pour commencer la saisie
                        @endif
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
                                    {{-- ✅ MODIFICATION : Indication contextuelle --}}
                                    @if($sessionType === 'rattrapage')
                                        <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                                            (rattrapage EC)
                                        </div>
                                    @endif
                                </div>
                                <div class="text-center p-3 bg-white dark:bg-gray-800 rounded-lg">
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $this->totalAbsents }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{-- ✅ MODIFICATION : Libellé précis --}}
                                        @if($sessionType === 'rattrapage')
                                            Éligibles absents
                                        @else
                                            Absents
                                        @endif
                                    </div>
                                    @if($sessionType === 'rattrapage')
                                        <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                                            (sur {{ $totalEtudiantsTheorique }} éligibles pour cette EC)
                                        </div>
                                    @endif
                                </div>
                                <div class="text-center p-3 bg-white dark:bg-gray-800 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $this->pourcentagePresence }}%</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{-- ✅ MODIFICATION : Libellé contextuel --}}
                                        @if($sessionType === 'rattrapage')
                                            Taux présence rattrapage
                                        @else
                                            Taux présence
                                        @endif
                                    </div>
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
                        {{-- ✅ AJOUT : Contexte rattrapage --}}
                        @if($sessionType === 'rattrapage')
                            <span class="text-sm font-normal text-orange-600 dark:text-orange-400 ml-2">
                                (Rattrapage EC spécifique)
                            </span>
                        @endif
                    </h3>
                    
                    <div class="space-y-6">
                    <!-- Champ de saisie -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="totalManchettesPresentes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{-- ✅ MODIFICATION : Label contextuel --}}
                                @if($sessionType === 'rattrapage')
                                    Nombre d'étudiants présents (sur {{ $totalEtudiantsTheorique }} éligible(s)) <span class="text-red-500">*</span>
                                @else
                                    Nombre d'étudiants présents <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <button wire:click="toggleEnvelopeCalculator" 
                                    class="px-2 py-0.5 text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 underline hover:no-underline">
                                {{ $show_envelope_calculator ? 'Saisie directe' : 'Calculatrice' }}
                            </button>
                        </div>
    
                    <!-- Champ principal - CACHÉ si calculatrice active -->
                    @if(!$show_envelope_calculator)
                        <div class="relative">
                            <input type="number" 
                                wire:model.live="totalManchettesPresentes" 
                                id="totalManchettesPresentes"
                                min="{{ $progressCount }}" 
                                max="{{ $totalEtudiantsTheorique }}"
                                class="w-full px-4 py-3 pr-20 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                placeholder="Ex: 25"
                                wire:keydown.enter="savePresence">
                            
                            <!-- Indicateur ENTRÉE -->
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-xs text-gray-400 dark:text-gray-500 font-mono">
                                ↵ ENTRÉE
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        @if($progressCount > 0)
                            <p>Minimum {{ $progressCount }} (déjà {{ $progressCount }} manchettes saisies)</p>
                        @endif
                        <p>
                            {{-- ✅ MODIFICATION : Message contextuel --}}
                            @if($sessionType === 'rattrapage')
                                Maximum {{ $totalEtudiantsTheorique }} étudiants éligibles pour cette EC
                            @else
                                Maximum {{ $totalEtudiantsTheorique }} étudiants inscrits
                            @endif
                        </p>
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
                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                        (Tapez ENTRÉE pour enregistrer après saisie)
                                    </span>
                                </h4>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                                    <div>
                                        <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Enveloppe 1</label>
                                        <input type="number" 
                                            wire:model.live="enveloppe1" 
                                            min="0" max="100"
                                            class="envelope-input w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-center bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                                            wire:keydown.enter="savePresence">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Enveloppe 2</label>
                                        <input type="number" 
                                            wire:model.live="enveloppe2" 
                                            min="0" max="100"
                                            class="envelope-input w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-center bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                                            wire:keydown.enter="savePresence">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Enveloppe 3</label>
                                        <input type="number" 
                                            wire:model.live="enveloppe3" 
                                            min="0" max="100"
                                            class="envelope-input w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-center bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                                            wire:keydown.enter="savePresence">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Enveloppe 4</label>
                                        <input type="number" 
                                            wire:model.live="enveloppe4" 
                                            min="0" max="100"
                                            class="envelope-input w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-center bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                                            wire:keydown.enter="savePresence">
                                    </div>
                                </div>
                                
                                <!-- Résultat avec indicateur ENTRÉE -->
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
                                            <div class="text-xs text-blue-600 dark:text-blue-400">
                                                Appuyez sur ENTRÉE pour enregistrer
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <span class="text-xs text-gray-400 dark:text-gray-500 font-mono px-2 py-1 bg-white dark:bg-gray-800 rounded border">
                                                ↵ ENTRÉE
                                            </span>
                                            <button wire:click="clearEnvelopes" 
                                                    class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                                Effacer
                                            </button>
                                        </div>
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
                                        <div class="text-gray-600 dark:text-gray-400">
                                            {{-- ✅ MODIFICATION : Libellé selon contexte --}}
                                            @if($sessionType === 'rattrapage')
                                                Éligibles absents
                                            @else
                                                Absents
                                            @endif
                                        </div>
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
                        @php
                            $isFirstTime = $progressCount === 0;
                            $buttonText = $isFirstTime ? 'Commencer' : 'Continuer';
                            $buttonIcon = $isFirstTime ? '🏷️' : '🔄';
                            $progressText = "({$this->getRemainingManchettes()} restante" . ($this->getRemainingManchettes() > 1 ? 's' : '') . ")";
                            
                            // Message contextuel pour rattrapage
                            if ($sessionType === 'rattrapage') {
                                if ($isFirstTime) {
                                    $helpText = "Prêt à commencer la saisie des manchettes de rattrapage pour cette EC";
                                } else {
                                    $helpText = "{$progressCount} manchette" . ($progressCount > 1 ? 's' : '') . " de rattrapage déjà saisie" . ($progressCount > 1 ? 's' : '');
                                }
                            } else {
                                if ($isFirstTime) {
                                    $helpText = "Prêt à commencer la saisie des manchettes";
                                } else {
                                    $helpText = "{$progressCount} manchette" . ($progressCount > 1 ? 's' : '') . " déjà saisie" . ($progressCount > 1 ? 's' : '');
                                }
                            }
                        @endphp

                        <div class="mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {{ $helpText }}
                            </p>
                            @if(!$isFirstTime)
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    En cours: {{ round(($progressCount / $totalManchettesPresentes) * 100) }}%
                                </div>
                            @endif
                        </div>

                        <button wire:click="goToSaisie" 
                                class="px-8 py-4 {{ $isFirstTime ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-lg font-medium rounded-lg shadow-sm transition-colors focus:ring-2 {{ $isFirstTime ? 'focus:ring-green-500' : 'focus:ring-blue-500' }} focus:ring-offset-2">
                            {{ $buttonIcon }} {{ $buttonText }} la saisie {{ $progressText }}
                        </button>

                        @if(!$isFirstTime)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                Vous pouvez reprendre là où vous vous êtes arrêté
                            </p>
                        @endif

                    @else
                        <!-- Saisie terminée -->
                        <div class="text-center py-4">
                            <div class="w-16 h-16 mx-auto mb-4 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-green-800 dark:text-green-300 mb-2">
                                Saisie terminée avec succès !
                            </h3>
                            <p class="text-green-600 dark:text-green-400 mb-4">
                                {{-- ✅ MODIFICATION : Message contextuel --}}
                                @if($sessionType === 'rattrapage')
                                    Toutes les {{ $totalManchettesPresentes }} manchette{{ $totalManchettesPresentes > 1 ? 's' : '' }} de rattrapage ont été saisies pour cette EC
                                @else
                                    Toutes les {{ $totalManchettesPresentes }} manchette{{ $totalManchettesPresentes > 1 ? 's' : '' }} ont été saisies
                                @endif
                            </p>
                            
                            <!-- Actions après completion -->
                            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                                <button wire:click="backToStep('ec')" 
                                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    {{-- ✅ MODIFICATION : Texte contextuel --}}
                                    @if($sessionType === 'rattrapage')
                                        Choisir une autre EC à rattraper
                                    @else
                                        Choisir une autre matière
                                    @endif
                                </button>
                                <button wire:click="backToStep('setup')" 
                                        class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    Modifier la configuration
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    
    // Auto-focus sur le champ nombre de présents quand on entre en mode édition
    Livewire.on('editing-started', () => {
        setTimeout(() => {
            const input = document.getElementById('totalManchettesPresentes');
            if (input) {
                input.focus();
                input.select();
            }
        }, 100);
    });

    // Validation en temps réel avec feedback visuel
    document.addEventListener('input', (e) => {
        if (e.target.id === 'totalManchettesPresentes' || e.target.classList.contains('envelope-input')) {
            const input = e.target;
            const value = parseInt(input.value);
            
            // Reset des classes
            input.classList.remove('border-red-500', 'border-green-500');
            
            if (e.target.id === 'totalManchettesPresentes') {
                const min = parseInt(input.min);
                const max = parseInt(input.max);
                
                if (value < min || value > max || isNaN(value)) {
                    input.classList.add('border-red-500');
                } else {
                    input.classList.add('border-green-500');
                }
            }
        }
    });

    // Fonction pour vérifier si on peut enregistrer
    function canSavePresence() {
        // Récupérer la valeur totale depuis Livewire
        const totalPresents = @this.totalManchettesPresentes;
        const progressCount = @this.progressCount;
        const totalEtudiants = @this.totalEtudiantsTheorique;
        
        return totalPresents >= progressCount && 
               totalPresents <= totalEtudiants && 
               totalPresents > 0;
    }

    // Fonction pour afficher un feedback visuel lors de l'enregistrement
    function showSaveAnimation(success = true) {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white text-sm font-medium transform transition-all duration-300 ${
            success ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.textContent = success ? '✓ Enregistrement en cours...' : '⚠ Valeur incorrecte';
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 2000);
    }

    // AMÉLIORATION: Gestion ENTRÉE pour tous les champs
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const target = e.target;
            
            // Pour le champ principal ou les champs enveloppes
            if (target.id === 'totalManchettesPresentes' || 
                target.classList.contains('envelope-input')) {
                
                e.preventDefault();
                
                // Vérifier si on peut enregistrer
                if (canSavePresence()) {
                    showSaveAnimation(true);
                    
                    // Appeler directement la méthode Livewire
                    @this.call('savePresence');
                } else {
                    showSaveAnimation(false);
                    
                    // Message d'aide
                    const helpMsg = document.createElement('div');
                    helpMsg.className = 'fixed bottom-4 right-4 z-50 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded text-sm max-w-xs';
                    helpMsg.innerHTML = `
                        <strong>Aide:</strong><br>
                        • Minimum: ${@this.progressCount}<br>
                        • Maximum: ${@this.totalEtudiantsTheorique}<br>
                        • Valeur actuelle: ${@this.totalManchettesPresentes || 'vide'}
                    `;
                    document.body.appendChild(helpMsg);
                    
                    setTimeout(() => helpMsg.remove(), 4000);
                }
            }
        }
    });

    // Indication visuelle pour ENTRÉE au survol
    document.addEventListener('focusin', (e) => {
        if (e.target.id === 'totalManchettesPresentes' || 
            e.target.classList.contains('envelope-input')) {
            
            e.target.setAttribute('title', 'Appuyez sur ENTRÉE pour enregistrer');
        }
    });

    // Afficher une notification au premier focus
    let firstFocusShown = false;
    document.addEventListener('focus', (e) => {
        if (!firstFocusShown && 
            (e.target.id === 'totalManchettesPresentes' || 
             e.target.classList.contains('envelope-input'))) {
            
            firstFocusShown = true;
            
            const tip = document.createElement('div');
            tip.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-lg';
            tip.innerHTML = '💡 Astuce: Appuyez sur ENTRÉE pour enregistrer rapidement';
            document.body.appendChild(tip);
            
            setTimeout(() => {
                tip.style.opacity = '0';
                setTimeout(() => tip.remove(), 300);
            }, 3000);
        }
    }, true);

});
</script>
@endpush