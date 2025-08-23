<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Saisie des Copies
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Session {{ ucfirst($sessionType) }} - Saisie des notes des étudiants
        </p>
    </div>

    <!-- Messages -->
    @if($message)
        <div class="mb-6 p-4 rounded-lg @if($messageType === 'success') bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/50 dark:border-green-700 dark:text-green-300 @elseif($messageType === 'error') bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/50 dark:border-red-700 dark:text-red-300 @else bg-blue-50 border border-blue-200 text-blue-800 dark:bg-blue-900/50 dark:border-blue-700 dark:text-blue-300 @endif">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    @if($messageType === 'success')
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @elseif($messageType === 'error')
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ $message }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Section de sélection -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Configuration de l'examen
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Niveau -->
            <div>
                <label for="niveau" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Niveau
                </label>
                <select wire:model.live="niveauId" id="niveau" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Sélectionnez un niveau</option>
                    @foreach($niveaux as $niveau)
                        <option value="{{ $niveau->id }}">{{ $niveau->abr }} - {{ $niveau->nom }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Parcours -->
            <div>
                <label for="parcours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Parcours
                </label>
                <select wire:model.live="parcoursId" id="parcours" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    {{ empty($parcours) ? 'disabled' : '' }}>
                    <option value="">Tous les parcours</option>
                    @foreach($parcours as $parcour)
                        <option value="{{ $parcour->id }}">{{ $parcour->abr }} - {{ $parcour->nom }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Examen -->
            <div>
                <label for="examen" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Examen
                </label>
                <select wire:model.live="examenId" id="examen" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    {{ empty($examens) ? 'disabled' : '' }}>
                    <option value="">Sélectionnez un examen</option>
                    @foreach($examens as $examen)
                        <option value="{{ $examen->id }}">
                            {{ $examen->niveau->abr }} 
                            @if($examen->parcours) - {{ $examen->parcours->abr }} @endif
                            ({{ $examen->duree }}min)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Élément Constitutif -->
            <div>
                <label for="ec" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Matière (EC)
                </label>
                <select wire:model.live="ecId" id="ec" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    {{ empty($ecs) ? 'disabled' : '' }}>
                    <option value="">Sélectionnez une matière</option>
                    @foreach($ecs as $ec)
                        <option value="{{ $ec->id }}">{{ $ec->nom }} ({{ $ec->abr }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Interface de saisie (affichée seulement si configuration complète) -->
    @if($showSaisieInterface)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Colonne gauche - Formulaire de saisie -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        Saisie de note
                    </h3>
                    
                    <div class="space-y-6">
                        <!-- Code d'anonymat -->
                        <div>
                            <label for="codeAnonymat" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Code d'anonymat
                            </label>
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="codeAnonymat" id="codeAnonymat" 
                                    placeholder="Ex: TA1, SB23..." 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400">
                                @if($codeAnonymat && strlen($codeAnonymat) >= 2)
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        @if($codeAnonymatTrouve && $manchetteCorrespondante)
                                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Informations étudiant -->
                            @if($manchetteCorrespondante && $manchetteCorrespondante->etudiant)
                                <div class="mt-3 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-md">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                                {{ $manchetteCorrespondante->etudiant->nom }} {{ $manchetteCorrespondante->etudiant->prenom }}
                                            </p>
                                            <p class="text-xs text-green-600 dark:text-green-400">
                                                Matricule: {{ $manchetteCorrespondante->etudiant->matricule }} - Code: {{ $codeAnonymat }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Note -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Note <span class="text-red-500">*</span>
                                </label>
                                <input type="number" wire:model="note" id="note" 
                                    min="0" max="20" step="0.01"
                                    placeholder="0.00" 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Note sur 20 (format: 12.5)
                                </p>
                                @error('note') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Quick note buttons -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Notes rapides
                                </label>
                                <div class="grid grid-cols-4 gap-1">
                                    @foreach([0, 10, 15, 20] as $quickNote)
                                        <button type="button" wire:click="$set('note', {{ $quickNote }})" 
                                            class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 {{ $note == $quickNote ? 'bg-blue-500 text-white' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $quickNote }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Commentaire -->
                        <div>
                            <label for="commentaire" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Commentaire (optionnel)
                            </label>
                            <textarea wire:model="commentaire" id="commentaire" rows="3"
                                placeholder="Commentaire sur la copie..." 
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"></textarea>
                            @error('commentaire') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Boutons d'action -->
                        <div class="flex justify-between items-center">
                            <button type="button" wire:click="resetSaisieForm" 
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Effacer
                            </button>
                            
                            <div class="flex space-x-3">
                                <button type="button" wire:click="marquerToutesVerifiees" 
                                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                    onclick="return confirm('Marquer toutes les copies comme vérifiées ?')">
                                    Tout vérifier
                                </button>
                                
                                <button type="button" wire:click="sauvegarderCopie" 
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    {{ !$codeAnonymatTrouve || !$manchetteCorrespondante || !$note ? 'disabled' : '' }}>
                                    Enregistrer la copie
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne droite - Statistiques et progression -->
            <div class="space-y-6">
                <!-- Statistiques -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Progression
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Compteurs -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ $progressCount }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Copies
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">
                                    {{ $totalCopies }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Total
                                </div>
                            </div>
                        </div>

                        <!-- Barre de progression -->
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                                <span>Avancement</span>
                                <span>{{ $totalCopies > 0 ? round(($progressCount / $totalCopies) * 100, 1) : 0 }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                    style="width: {{ $totalCopies > 0 ? min(($progressCount / $totalCopies) * 100, 100) : 0 }}%"></div>
                            </div>
                        </div>

                        <!-- Statistiques de notes -->
                        @if(count($copiesSaisies) > 0)
                            @php
                                $notes = collect($copiesSaisies)->pluck('note')->filter();
                                $moyenne = $notes->avg();
                                $notesInferieures10 = $notes->filter(fn($note) => $note < 10)->count();
                            @endphp
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="grid grid-cols-2 gap-4 text-center">
                                    <div>
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($moyenne, 2) }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            Moyenne
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-lg font-semibold text-red-600 dark:text-red-400">
                                            {{ $notesInferieures10 }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            < 10/20
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Liste des dernières saisies -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Dernières saisies
                    </h3>
                    
                    @if(!empty($copiesSaisies))
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            @foreach(array_slice($copiesSaisies, 0, 10) as $copie)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $copie['code_complet'] }}
                                            </p>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $copie['note'] >= 10 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                                {{ number_format($copie['note'], 2) }}/20
                                            </span>
                                        </div>
                                        @if($copie['etudiant'])
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                {{ $copie['etudiant']['nom'] }} {{ $copie['etudiant']['prenom'] }}
                                            </p>
                                        @endif
                                        @if($copie['commentaire'])
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 truncate">
                                                {{ $copie['commentaire'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <button wire:click="supprimerCopie({{ $copie['id'] }})" 
                                        class="ml-2 p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette copie ?')">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Aucune copie saisie
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <!-- Message d'instruction -->
        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-blue-500 dark:text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-blue-800 dark:text-blue-300 mb-2">
                Configuration requise
            </h3>
            <p class="text-blue-600 dark:text-blue-400">
                Veuillez sélectionner un niveau, un examen et une matière pour commencer la saisie des copies.
            </p>
            <p class="text-sm text-blue-500 dark:text-blue-300 mt-2">
                <strong>Note:</strong> Les manchettes doivent être saisies avant les copies.
            </p>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Auto-clear messages
    document.addEventListener('livewire:init', () => {
        Livewire.on('clearMessage', (event) => {
            setTimeout(() => {
                Livewire.dispatch('clearMessage');
            }, event.delay || 3000);
        });
    });
    
    // Auto-focus on code input
    document.addEventListener('livewire:navigated', () => {
        const codeInput = document.getElementById('codeAnonymat');
        if (codeInput) {
            codeInput.focus();
        }
    });
</script>
@endpush
</div>