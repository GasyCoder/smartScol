{{-- livewire.fusion.partials.table-verify-resultat - Version refonte --}}
<div class="overflow-hidden bg-white border border-gray-200 rounded-2xl shadow-xl dark:bg-gray-900 dark:border-gray-700 transition-all duration-300 hover:shadow-2xl">
    <!-- Header modernisé -->
    <div class="px-6 py-4 border-b border-gray-300 bg-slate-50 dark:bg-gray-800 dark:border-gray-600">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-primary-100 rounded-lg dark:bg-primary-900">
                    <em class="text-lg text-primary-600 icon ni ni-file-docs dark:text-primary-400"></em>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Résultats des Étudiants
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $totalResultats }} résultats • {{ $pourcentageVerification }}% vérifiés
                    </p>
                </div>
            </div>
            @if($afficherMoyennesUE)
                <div class="flex items-center px-4 py-2 bg-primary-500 rounded-xl shadow-lg text-white">
                    <em class="mr-2 icon ni ni-bar-chart"></em>
                    <span class="font-semibold">Mode UE Activé</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Container principal -->
    <div class="overflow-x-auto bg-white dark:bg-gray-900">
        <div class="min-w-full">
            @php
                $resultatsByStudent = collect($resultats)->groupBy('matricule');
                $index = 0;
            @endphp
            
            @forelse($resultatsByStudent as $matricule => $resultatGroup)
                @php
                    $index++;
                    $firstResultat = $resultatGroup->first();
                    $resultatsByUE = $resultatGroup->groupBy('ue_nom');
                @endphp
                
                <!-- Carte Étudiant -->
                <div class="mx-6 mt-6 mb-4 overflow-hidden bg-slate-50 border border-slate-200 rounded-2xl shadow-lg dark:bg-slate-800 dark:border-slate-700 hover:shadow-xl transition-all duration-300">
                    <!-- En-tête étudiant -->
                    <div class="px-6 py-4 dark:bg-gray-800 bg-primary-600 dark:text-yellow-400 text-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <!-- Icône utilisateur au lieu du numéro -->
                                <div class="flex items-center justify-center w-10 h-10 bg-white bg-opacity-20 rounded-full backdrop-blur-sm">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold">{{ $firstResultat['prenom'] }} {{ $firstResultat['nom'] }}</h4>
                                    <p class="text-blue-100 font-bold">N° matricule: <u>{{ $matricule }}</u></p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($afficherMoyennesUE)
                                    <div class="px-3 py-1 bg-white bg-opacity-20 rounded-lg backdrop-blur-sm">
                                        <span class="text-sm font-medium">{{ count($resultatsByUE) }} UE</span>
                                    </div>
                                @endif
                                <p class="mt-1 text-sm text-blue-100">{{ count($resultatGroup) }} EC au total</p>
                            </div>
                        </div>
                    </div>

                    <!-- Résultats par UE -->
                    <div class="p-6 space-y-6">
                        @foreach($resultatsByUE as $ueNom => $ecGroup)
                            @php 
                                // Calcul de la moyenne UE
                                $notesUE = $ecGroup->pluck('note')->filter(function($note) {
                                    return $note !== null && is_numeric($note);
                                });
                                $moyenneUE = $notesUE->isNotEmpty() ? $notesUE->avg() : null;
                                $hasZero = $ecGroup->contains('note', 0);
                                if ($hasZero) $moyenneUE = 0;
                                
                                $ueCredits = $ecGroup->first()['ue_credits'] ?? 0;
                                $ueValidee = $moyenneUE !== null && $moyenneUE >= 10 && !$hasZero;
                            @endphp
                            
                            <!-- En-tête UE -->
                            <div class="p-4 bg-slate-100 border border-slate-200 rounded-xl dark:bg-slate-800 dark:border-slate-600">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="px-3 py-1 text-sm font-bold text-primary-800 bg-primary-100 rounded-lg dark:bg-primary-900 dark:text-primary-200">
                                            {{ $ecGroup->first()['ue_abr'] ?? 'UE' }}
                                        </div>
                                        <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $ueNom ?? 'UE N/A' }}
                                        </h5>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        @if($afficherMoyennesUE && $moyenneUE !== null)
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm text-gray-600 dark:text-gray-400">Moyenne UE:</span>
                                                <span class="px-3 py-1 text-sm font-bold rounded-lg shadow-sm {{ $ueValidee ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 border border-red-200 dark:bg-red-900 dark:text-red-200' }}">
                                                    {{ number_format($moyenneUE, 2) }}/20
                                                    @if($hasZero)
                                                        <span class="ml-1 text-xs">(Éliminatoire)</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                        <div class="px-3 py-1 text-sm text-gray-600 bg-gray-100 rounded-lg dark:bg-gray-700 dark:text-gray-400">
                                            {{ $ueCredits }} crédits
                                        </div>
                                    </div>
                                </div>

                                <!-- Liste des EC -->
                                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    @foreach($ecGroup as $indexEC => $resultat)
                                        <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-600 hover:shadow-md transition-all duration-200 {{ $resultat['is_checked'] ? 'ring-2 ring-green-400 bg-green-50 dark:bg-green-900/20' : '' }}">
                                            <!-- En-tête EC -->
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-2 mb-2">
                                                        <span class="px-2 py-1 text-xs font-medium text-purple-700 bg-purple-100 rounded-md dark:bg-purple-900 dark:text-purple-200">
                                                            EC{{ $indexEC + 1 }}
                                                        </span>
                                                        @if($resultat['is_checked'])
                                                            <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-md dark:bg-green-900 dark:text-green-200">
                                                                ✓ Vérifié
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <h6 class="font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                                                        {{ $resultat['matiere'] }}
                                                    </h6>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                        {{ $resultat['enseignant'] ?? 'Enseignant non défini' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Note et Actions -->
                                            <div class="space-y-3">
                                                <!-- Zone de note -->
                                                @if($editingRow === $resultat['unique_key'])
                                                    <!-- Mode édition -->
                                                    <div class="p-3 bg-primary-50 border border-primary-200 rounded-lg dark:bg-primary-900/20 dark:border-primary-700">
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                            Modifier la note
                                                        </label>
                                                        <div class="flex items-center space-x-2">
                                                            <input
                                                                type="number"
                                                                wire:model.live="newNote"
                                                                step="0.01"
                                                                min="0"
                                                                max="20"
                                                                class="flex-1 px-3 py-2 text-sm font-semibold text-center text-gray-900 bg-white border-2 border-blue-300 rounded-lg shadow-sm dark:text-white dark:bg-gray-700 dark:border-blue-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                                                placeholder="{{ number_format($resultat['note'], 2) }}"
                                                                autofocus
                                                            />
                                                            @if($newNote && $newNote != $resultat['note'])
                                                                <div class="flex items-center">
                                                                    @if($newNote >= 0 && $newNote <= 20)
                                                                        <em class="text-green-500 icon ni ni-check-circle"></em>
                                                                    @else
                                                                        <em class="text-red-500 icon ni ni-alert-circle"></em>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                        @if($newNote && $newNote != $resultat['note'])
                                                            <div class="mt-2 text-xs text-primary-600 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30 px-2 py-1 rounded">
                                                                {{ number_format($resultat['note'], 2) }} → {{ number_format($newNote, 2) }}
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- Boutons d'action -->
                                                        <div class="flex items-center justify-end space-x-2 mt-3">
                                                            <button wire:click="saveChanges('{{ $resultat['unique_key'] }}')" 
                                                                    class="px-4 py-2 text-xs font-medium text-white bg-green-600 rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200">
                                                                <em class="mr-1 icon ni ni-check"></em>
                                                                Sauver
                                                            </button>
                                                            <button wire:click="cancelEditing" 
                                                                    class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-200 rounded-lg shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                                                                <em class="mr-1 icon ni ni-cross"></em>
                                                                Annuler
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                {{-- Affichage normal --}}
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <span class="text-sm text-gray-600 dark:text-gray-400">Note:</span>
                                                        <div class="relative">
                                                            @if(is_null($resultat['note']))
                                                                {{-- Pas de note pour cet EC dans cette session --}}
                                                                <span class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-xl shadow-sm bg-gray-100 text-gray-500 border border-dashed border-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600">
                                                                    —
                                                                </span>
                                                            @else
                                                                @if($resultat['note_old'])
                                                                    {{-- Note modifiée --}}
                                                                    <span class="inline-flex items-center px-3 py-2 text-lg font-bold rounded-xl cursor-help shadow-sm transition-all duration-200 hover:shadow-md {{ $resultat['note'] >= 10 ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 border border-red-200 dark:bg-red-900 dark:text-red-200' }}">
                                                                        {{ number_format($resultat['note'], 2) }}
                                                                        <em class="ml-2 text-orange-500 icon ni ni-edit-alt" title="Note modifiée"></em>
                                                                    </span>
                                                                    <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                                                                        Ancienne: {{ number_format($resultat['note_old'], 2) }}
                                                                    </span>
                                                                @else
                                                                    {{-- Note simple --}}
                                                                    <span class="inline-flex items-center px-3 py-2 text-lg font-bold rounded-xl shadow-sm transition-all duration-200 hover:shadow-md {{ $resultat['note'] >= 10 ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 border border-red-200 dark:bg-red-900 dark:text-red-200' }}">
                                                                        {{ number_format($resultat['note'], 2) }}
                                                                    </span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if(!is_null($resultat['note']))
                                                        <!-- Bouton modifier (seulement si une note existe) -->
                                                        <button wire:click="startEditing('{{ $resultat['unique_key'] }}')" 
                                                                wire:key="edit-{{ $resultat['unique_key'] }}"
                                                                class="px-3 py-2 text-xs font-medium text-primary-700 bg-primary-100 rounded-lg shadow-sm hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all duration-200 dark:bg-primary-900 dark:text-primary-200 dark:hover:bg-primary-800">
                                                            <em class="mr-1 icon ni ni-edit"></em>
                                                            Modifier
                                                        </button>
                                                    @endif
                                                </div>
                                                @endif

                                                <!-- Informations d'audit modernisées -->
                                                @if((!empty($resultat['saisie_par']) && $resultat['saisie_par'] !== 'Inconnu') || (!empty($resultat['modifie_par']) && $resultat['modifie_par'] !== 'Inconnu'))
                                                    <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-600">
                                                        <div class="space-y-2 text-xs">
                                                            @if(!empty($resultat['saisie_par']) && $resultat['saisie_par'] !== 'Inconnu')
                                                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg dark:bg-gray-800">
                                                                    <div class="flex items-center space-x-2">
                                                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $resultat['saisie_par'] }}</span>
                                                                    </div>
                                                                    @if($resultat['created_at'])
                                                                        <span class="text-gray-500 dark:text-gray-400">{{ $resultat['created_at']->diffForHumans() }}</span>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                            
                                                            @if(!empty($resultat['modifie_par']) && $resultat['modifie_par'] !== 'Inconnu' && $resultat['saisie_par'] !== $resultat['modifie_par'])
                                                                <div class="flex items-center justify-between p-2 bg-orange-50 rounded-lg dark:bg-orange-900/20">
                                                                    <div class="flex items-center space-x-2">
                                                                        <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                                                        <span class="font-medium text-orange-700 dark:text-orange-400">{{ $resultat['modifie_par'] }}</span>
                                                                    </div>
                                                                    @if($resultat['updated_at'])
                                                                        <span class="text-orange-600 dark:text-orange-400">{{ $resultat['updated_at']->diffForHumans() }}</span>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
            @empty
                <!-- État vide modernisé -->
                <div class="flex flex-col items-center justify-center py-16 px-6">
                    <div class="p-6 bg-gray-100 rounded-full dark:bg-gray-700 mb-6">
                        <em class="text-6xl text-gray-400 icon ni ni-folder-close dark:text-gray-500"></em>
                    </div>
                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Aucun résultat trouvé</h4>
                    <p class="text-gray-600 dark:text-gray-400 text-center max-w-md">
                        Aucun résultat ne correspond aux critères sélectionnés. Essayez de modifier vos filtres ou de vérifier les données d'examen.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Footer statistiques modernisé -->
    @if(count($resultats) > 0)
        <div class="px-6 py-4 border-t border-gray-200 bg-slate-50 dark:bg-gray-800 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-primary-100 rounded-lg dark:bg-primary-900">
                            <em class="text-primary-600 icon ni ni-users dark:text-primary-400"></em>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ count($resultatsByStudent) }} Étudiants</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Total inscrits</p>
                        </div>
                    </div>
                    
                    <div class="w-px h-12 bg-gray-300 dark:bg-gray-600"></div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-green-100 rounded-lg dark:bg-green-900">
                            <em class="text-green-600 icon ni ni-book dark:text-green-400"></em>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ count($resultats) }} EC</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Éléments constitutifs</p>
                        </div>
                    </div>
                    
                    @if($statistiquesPresence)
                        <div class="w-px h-12 bg-gray-300 dark:bg-gray-600"></div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-cyan-100 rounded-lg dark:bg-cyan-900">
                                <em class="text-cyan-600 icon ni ni-check-circle dark:text-cyan-400"></em>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-cyan-700 dark:text-cyan-400">{{ $statistiquesPresence['taux_presence'] }}% Présents</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Taux de présence</p>
                            </div>
                        </div>
                    @endif
                </div>
                
                @if($afficherMoyennesUE)
                    <div class="flex items-center space-x-2 px-4 py-2 bg-primary-100 rounded-xl dark:bg-primary-900">
                        <em class="text-primary-600 icon ni ni-info dark:text-primary-400"></em>
                        <span class="text-sm text-primary-800 dark:text-primary-300 font-medium">
                            Moyennes UE calculées automatiquement
                        </span>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
/* Animations et transitions améliorées */
.table-row-animation {
    animation: slideInUp 0.4s ease-out forwards;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hover effects pour les cartes */
.hover\:scale-102:hover {
    transform: scale(1.02);
}

/* Animation des tooltips */
.tooltip:hover .tooltip-content {
    opacity: 1;
    visibility: visible;
    transform: translateY(-5px);
}

.tooltip-content {
    opacity: 0;
    visibility: hidden;
    transform: translateY(0);
    transition: all 0.3s ease;
}

/* Amélioration des focus states */
.focus\:ring-2:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
}

/* Backdrop blur pour les éléments flottants */
.backdrop-blur-sm {
    backdrop-filter: blur(4px);
}

/* Animation pour les badges de vérification */
.verification-badge {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .8;
    }
}

/* Responsive improvements */
@media (max-width: 768px) {
    .grid-cols-3 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    
    .grid-cols-2 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}

/* Dark mode improvements */
@media (prefers-color-scheme: dark) {
    .shadow-xl {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
    }
}

/* Amélioration de l'accessibilité */
@media (prefers-reduced-motion: reduce) {
    .transition-all,
    .table-row-animation {
        transition: none;
        animation: none;
    }
}

/* Custom scrollbar */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Animation d'entrée pour les cartes */
.student-card {
    animation: fadeInUp 0.6s ease-out forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Amélioration des inputs en édition */
.editing-input {
    background: #f8fafc;
    border: 2px solid #6576ff;
    box-shadow: 0 0 0 3px rgba(101, 118, 255, 0.1);
}

/* Amélioration des états de validation */
.valid-input {
    border-color: #1ee0ac;
    box-shadow: 0 0 0 3px rgba(30, 224, 172, 0.1);
}

.invalid-input {
    border-color: #e85347;
    box-shadow: 0 0 0 3px rgba(232, 83, 71, 0.1);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration des animations
    const ANIMATION_CONFIG = {
        duration: 400,
        easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
        stagger: 100
    };

    // Animation d'entrée pour les cartes étudiants
    const animateStudentCards = () => {
        const cards = document.querySelectorAll('.student-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `opacity ${ANIMATION_CONFIG.duration}ms ${ANIMATION_CONFIG.easing}, transform ${ANIMATION_CONFIG.duration}ms ${ANIMATION_CONFIG.easing}`;
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
                card.classList.add('student-card');
            }, index * ANIMATION_CONFIG.stagger);
        });
    };

    // Gestion améliorée des tooltips
    const initTooltips = () => {
        const tooltipTriggers = document.querySelectorAll('[title]');
        tooltipTriggers.forEach(trigger => {
            trigger.addEventListener('mouseenter', function() {
                this.setAttribute('data-tooltip', this.getAttribute('title'));
                this.removeAttribute('title');
            });
            
            trigger.addEventListener('mouseleave', function() {
                const tooltip = this.getAttribute('data-tooltip');
                if (tooltip) {
                    this.setAttribute('title', tooltip);
                    this.removeAttribute('data-tooltip');
                }
            });
        });
    };

    // Amélioration des inputs en édition
    const enhanceEditingInputs = () => {
        document.addEventListener('input', function(e) {
            if (e.target.type === 'number' && e.target.hasAttribute('wire:model.live')) {
                const value = parseFloat(e.target.value);
                
                // Supprimer les classes précédentes
                e.target.classList.remove('valid-input', 'invalid-input', 'editing-input');
                
                // Ajouter la classe d'édition
                e.target.classList.add('editing-input');
                
                // Validation
                if (value >= 0 && value <= 20) {
                    e.target.classList.add('valid-input');
                } else if (e.target.value !== '') {
                    e.target.classList.add('invalid-input');
                }
            }
        });
    };

    // Gestion des raccourcis clavier améliorée
    const handleKeyboardShortcuts = () => {
        document.addEventListener('keydown', function(e) {
            // Échapper pour annuler l'édition
            if (e.key === 'Escape') {
                const cancelButton = document.querySelector('[wire\\:click="cancelEditing"]');
                if (cancelButton) {
                    cancelButton.click();
                }
            }
            
            // Entrée pour sauvegarder
            if (e.key === 'Enter' && e.target.type === 'number') {
                const saveButton = e.target.closest('.space-y-3').querySelector('[wire\\:click*="saveChanges"]');
                if (saveButton) {
                    e.preventDefault();
                    saveButton.click();
                }
            }
            
            // Ctrl+E pour mode édition rapide
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                const editButtons = document.querySelectorAll('[wire\\:click*="startEditing"]:not(.hidden)');
                if (editButtons.length > 0) {
                    editButtons[0].click();
                }
            }
        });
    };

    // Animation de confirmation pour les actions
    const animateConfirmations = () => {
        document.addEventListener('click', function(e) {
            if (e.target.matches('[wire\\:click*="saveChanges"]')) {
                // Animation de succès
                e.target.style.transform = 'scale(0.95)';
                e.target.style.transition = 'transform 0.1s ease';
                
                setTimeout(() => {
                    e.target.style.transform = 'scale(1)';
                }, 100);
            }
        });
    };

    // Observation des mutations pour réappliquer les animations
    const observeChanges = () => {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Réappliquer les animations pour les nouveaux éléments
                    setTimeout(() => {
                        initTooltips();
                        enhanceEditingInputs();
                    }, 100);
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    };

    // Gestion de l'état de chargement
    const handleLoadingStates = () => {
        document.addEventListener('livewire:loading', function() {
            const buttons = document.querySelectorAll('button[wire\\:click]');
            buttons.forEach(button => {
                button.disabled = true;
                button.style.opacity = '0.6';
            });
        });

        document.addEventListener('livewire:loaded', function() {
            const buttons = document.querySelectorAll('button[wire\\:click]');
            buttons.forEach(button => {
                button.disabled = false;
                button.style.opacity = '1';
            });
        });
    };

    // Notification d'état pour les moyennes UE
    Livewire.on('moyennesUEToggled', (isActivated) => {
        const message = isActivated
            ? 'Mode moyennes UE activé - Calculs automatiques des UE'
            : 'Mode moyennes UE désactivé - Affichage simplifié';

        // Création d'une notification personnalisée
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 p-4 bg-blue-500 text-white rounded-lg shadow-lg transform translate-x-full transition-transform duration-300';
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <em class="icon ni ni-info-circle"></em>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(full)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 4000);
    });

    // Initialisation
    animateStudentCards();
    initTooltips();
    enhanceEditingInputs();
    handleKeyboardShortcuts();
    animateConfirmations();
    observeChanges();
    handleLoadingStates();

    // Réinitialisation après les mises à jour Livewire
    document.addEventListener('livewire:morph-updated', function() {
        setTimeout(() => {
            animateStudentCards();
            initTooltips();
            enhanceEditingInputs();
        }, 100);
    });
});

// Nettoyage lors de la navigation
document.addEventListener('livewire:navigating', () => {
    // Arrêter toutes les animations
    document.querySelectorAll('*').forEach(el => {
        el.style.animation = 'none';
        el.style.transition = 'none';
    });
});
</script>
@endpush