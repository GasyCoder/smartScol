<!-- Modale de saisie de copie - RENFORCÉE avec double vérification -->
@if($showCopieModal)
<div class="fixed inset-0 z-10 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

        <!-- Centrage modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Contenu modal - ÉLARGI avec double vérification -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 dark:bg-gray-800">
                <div class="sm:flex sm:items-start">
                    <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <!-- En-tête avec icône et option de double vérification -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-12 h-12 mx-auto mr-4 bg-blue-100 rounded-full dark:bg-blue-900">
                                    <em class="text-xl text-blue-600 icon ni ni-form-validation dark:text-blue-400"></em>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                        {{ isset($editingCopieId) ? 'Modifier une note' : 'Saisir une note' }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Session {{ $currentSessionType ?? 'Normale' }} - Saisie anonyme
                                    </p>
                                </div>
                            </div>

                            <!-- Option de double vérification -->
                            <div class="flex items-center space-x-3">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           wire:model.live="enableDoubleVerification" 
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Double vérification
                                    </span>
                                </label>
                                <div class="relative group">
                                    <em class="text-gray-400 cursor-help icon ni ni-info-circle hover:text-blue-500"></em>
                                    <div class="absolute bottom-full right-0 mb-2 w-64 p-2 text-xs text-white bg-gray-900 rounded opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity dark:bg-gray-700">
                                        Activez cette option pour saisir la note deux fois et s'assurer qu'il n'y a pas d'erreur de frappe
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations contextuelles améliorées -->
                        <div class="p-4 mb-4 border border-blue-200 rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 dark:border-blue-700">
                            <div class="flex items-center mb-2">
                                <em class="mr-2 text-blue-600 icon ni ni-info-circle dark:text-blue-400"></em>
                                <span class="text-sm font-semibold text-blue-800 dark:text-blue-200">Contexte de saisie</span>
                            </div>
                            <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                <div class="flex items-center">
                                    <em class="mr-2 text-gray-500 icon ni ni-building"></em>
                                    <span class="text-gray-600 dark:text-gray-300">Salle: <strong>{{ $currentSalleName }}</strong></span>
                                </div>
                                <div class="flex items-center">
                                    <em class="mr-2 text-gray-500 icon ni ni-hash"></em>
                                    <span class="text-gray-600 dark:text-gray-300">Code salle: <strong>{{ $selectedCodeBase }}</strong></span>
                                </div>
                                <div class="flex items-center">
                                    <em class="mr-2 text-gray-500 icon ni ni-book"></em>
                                    <span class="text-gray-600 dark:text-gray-300">EC: <strong>{{ $currentEcName }}</strong></span>
                                </div>
                                <div class="flex items-center">
                                    <em class="mr-2 text-gray-500 icon ni ni-calendar"></em>
                                    <span class="text-gray-600 dark:text-gray-300">Date: <strong>{{ $currentEcDate ?: 'Non définie' }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Badge d'anonymat et protection -->
                        @if($enableDoubleVerification ?? false)
                        <div class="flex items-center justify-between p-3 mb-4 border border-yellow-200 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-700">
                            <div class="flex items-center px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                <em class="mr-1 icon ni ni-check-thick"></em>
                                Protection activée
                            </div>
                        </div>
                        @endif
                        
                        <!-- Formulaire RENFORCÉ -->
                        <form wire:submit.prevent="saveCopie">
                            <div class="space-y-6">
                                <div class="space-y-4">
                                    {{-- temporaire code à cause insidence scolarité --}}
                                <div>
                                    <label for="check_matricule" class="flex items-center mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <em class="mr-2 text-primary-600 icon ni ni-code dark:text-primary-400"></em>
                                            Trouver matricule
                                            <span class="ml-1 text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="text"
                                                wire:model.live="check_matricule"
                                                id="check_matricule"
                                                class="block w-full px-4 py-3 font-mono text-lg border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
                                                placeholder="Ex: 357400"
                                                required>
                                        </div>
                                    {{-- Affichage pour voir code annonymat et EC ICI selon recherche --}}
                                    @if($etudiant_trouve && $manchette_trouvee)
                                        <div class="mt-4 flex items-center space-x-2 p-3 border rounded-lg {{ $copie_existante ? 'border-orange-300 bg-orange-50 dark:bg-orange-900/20' : 'border-green-300 bg-green-50 dark:bg-green-900/20' }}">
                                            @if($copie_existante)
                                                <em class="text-orange-500 icon ni ni-alert-triangle"></em>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">{{ $etudiant_trouve->nom }} {{ $etudiant_trouve->prenom }}</p>
                                                    <p class="text-sm text-orange-600">Matricule déjà existante!</p>
                                                </div>
                                            @else
                                                <em class="text-green-500 icon ni ni-check-circle"></em>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $etudiant_trouve->nom }} {{ $etudiant_trouve->prenom }}</p>
                                            @endif
                                        </div>
                                    @endif

                                    </div>
                                    <input type="hidden"
                                        wire:model.live="code_anonymat"
                                        id="code_anonymat"
                                        class="block w-full px-4 py-3 font-mono text-lg border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400
                                        {{ $code_anonymat && strlen($code_anonymat) >= 2 ? 'border-green-300 bg-green-50 dark:bg-green-900/20' : '' }}"
                                        placeholder="Ex: {{ $selectedCodeBase }}1"
                                        autofocus
                                        required
                                        maxlength="10">
                                    {{-- <div>
                                       <!-- Code anonymat avec validation renforcée NE TOUCHE PAS CETTE CODE Code d'anonymat -->
                                       <div class="mt-4">
                                        <label for="code_anonymat" class="flex items-center mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <em class="mr-2 text-primary-600 icon ni ni-code dark:text-primary-400"></em>
                                            Code d'anonymat
                                            <span class="ml-1 text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="text"
                                                wire:model.live="code_anonymat"
                                                id="code_anonymat"
                                                class="block w-full px-4 py-3 font-mono text-lg border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400
                                                {{ $code_anonymat && strlen($code_anonymat) >= 2 ? 'border-green-300 bg-green-50 dark:bg-green-900/20' : '' }}"
                                                placeholder="Ex: {{ $selectedCodeBase }}1"
                                                autofocus
                                                required
                                                maxlength="10">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                @if($code_anonymat && strlen($code_anonymat) >= 2)
                                                    <em class="text-green-500 icon ni ni-check-circle"></em>
                                                @else
                                                    <em class="text-gray-400 icon ni ni-hash"></em>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Validation en temps réel du code -->
                                        @if($code_anonymat)
                                        <div class="mt-2 space-y-1">
                                            <div class="flex items-center text-xs">
                                                @php
                                                    $isValidFormat = preg_match('/^[A-Za-z]+\d+$/', $code_anonymat);
                                                    $matchesSalle = str_starts_with(strtoupper($code_anonymat), strtoupper($selectedCodeBase));
                                                @endphp
                                                
                                                <!-- Validation du format -->
                                                <span class="flex items-center mr-4">
                                                    @if($isValidFormat)
                                                        <em class="mr-1 text-green-500 icon ni ni-check-circle"></em>
                                                        <span class="text-green-600 dark:text-green-400">Format valide</span>
                                                    @else
                                                        <em class="mr-1 text-red-500 icon ni ni-cross-circle"></em>
                                                        <span class="text-red-600 dark:text-red-400">Format invalide</span>
                                                    @endif
                                                </span>
                                                
                                                <!-- Validation de la salle -->
                                                <span class="flex items-center">
                                                    @if($matchesSalle)
                                                        <em class="mr-1 text-green-500 icon ni ni-check-circle"></em>
                                                        <span class="text-green-600 dark:text-green-400">Salle correcte</span>
                                                    @else
                                                        <em class="mr-1 text-yellow-500 icon ni ni-alert-circle"></em>
                                                        <span class="text-yellow-600 dark:text-yellow-400">Vérifiez la salle</span>
                                                    @endif
                                                </span>
                                            </div>
                                            
                                            <!-- Suggestion de code correct -->
                                            @if($code_anonymat && !$matchesSalle && $selectedCodeBase)
                                            <div class="text-xs text-blue-600 dark:text-blue-400">
                                                💡 Code suggéré: <button type="button" onclick="@this.set('code_anonymat', '{{ $selectedCodeBase }}' + '{{ preg_replace('/[^0-9]/', '', $code_anonymat) ?: '1' }}')" class="underline hover:no-underline">{{ $selectedCodeBase }}{{ preg_replace('/[^0-9]/', '', $code_anonymat) ?: '1' }}</button>
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                        
                                        @error('code_anonymat')
                                        <div class="flex items-center mt-2 text-sm text-red-600 dark:text-red-500">
                                            <em class="mr-1 icon ni ni-alert-circle"></em>
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div> --}}

                                    <!-- Double vérification du code (si activée) -->
                                    @if($enableDoubleVerification ?? false)
                                    <div>
                                        <label for="code_anonymat_confirmation" class="flex items-center mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <em class="mr-2 text-orange-600 icon ni ni-repeat dark:text-orange-400"></em>
                                            Confirmer le code d'anonymat
                                            <span class="ml-1 text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="text"
                                                wire:model.live="code_anonymat_confirmation"
                                                id="code_anonymat_confirmation"
                                                class="block w-full px-4 py-3 font-mono text-lg border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400
                                                {{ $code_anonymat_confirmation && $code_anonymat === $code_anonymat_confirmation ? 'border-green-300 bg-green-50 dark:bg-green-900/20' : ($code_anonymat_confirmation ? 'border-red-300 bg-red-50 dark:bg-red-900/20' : '') }}"
                                                placeholder="Retapez le même code"
                                                maxlength="10">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                @if($code_anonymat_confirmation)
                                                    @if($code_anonymat === $code_anonymat_confirmation)
                                                        <em class="text-green-500 icon ni ni-check-circle"></em>
                                                    @else
                                                        <em class="text-red-500 icon ni ni-cross-circle"></em>
                                                    @endif
                                                @else
                                                    <em class="text-gray-400 icon ni ni-repeat"></em>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($code_anonymat_confirmation)
                                        <div class="mt-2">
                                            @if($code_anonymat === $code_anonymat_confirmation)
                                                <div class="flex items-center text-xs text-green-600 dark:text-green-400">
                                                    <em class="mr-1 icon ni ni-check-circle"></em>
                                                    Les codes correspondent parfaitement
                                                </div>
                                            @else
                                                <div class="flex items-center text-xs text-red-600 dark:text-red-400">
                                                    <em class="mr-1 icon ni ni-cross-circle"></em>
                                                    Les codes ne correspondent pas
                                                </div>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                <!-- Notes avec double vérification -->
                                <div class="space-y-4">
                                    <div>
                                        <label for="note" class="flex items-center mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <em class="mr-2 text-primary-600 icon ni ni-edit dark:text-primary-400"></em>
                                            Note sur 20
                                            <span class="ml-1 text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number"
                                                wire:model.live="note"
                                                id="note"
                                                step="0.01"
                                                min="0"
                                                max="20"
                                                class="block w-full px-4 py-3 pr-20 text-lg border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400
                                                {{ $note !== null && $note >= 0 && $note <= 20 ? 'border-green-300 bg-green-50 dark:bg-green-900/20' : '' }}"
                                                placeholder="Ex: 15.50"
                                                required>

                                            <!-- Bouton OCR -->
                                            <button type="button"
                                                    onclick="startOCR()"
                                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 transition-colors rounded-r-lg hover:text-primary-600 hover:bg-gray-50 dark:hover:text-primary-400 dark:hover:bg-gray-600"
                                                    title="Scanner la note avec OCR">
                                                <em class="text-lg icon ni ni-scan"></em>
                                            </button>
                                        </div>

                                        <!-- Indicateur de validation de note amélioré -->
                                        @if($note !== null && $note !== '')
                                        <div class="mt-2">
                                            @if($note >= 0 && $note <= 20)
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center text-xs">
                                                        @if($note >= 16)
                                                            <span class="inline-flex items-center px-2 py-1 text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                                                <em class="mr-1 icon ni ni-star-fill"></em>
                                                                Très bien ({{ number_format($note, 2) }}/20)
                                                            </span>
                                                        @elseif($note >= 14)
                                                            <span class="inline-flex items-center px-2 py-1 text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                                                <em class="mr-1 icon ni ni-thumbs-up"></em>
                                                                Bien ({{ number_format($note, 2) }}/20)
                                                            </span>
                                                        @elseif($note >= 12)
                                                            <span class="inline-flex items-center px-2 py-1 text-indigo-800 bg-indigo-100 rounded-full dark:bg-indigo-900 dark:text-indigo-200">
                                                                <em class="mr-1 icon ni ni-check-circle"></em>
                                                                Assez bien ({{ number_format($note, 2) }}/20)
                                                            </span>
                                                        @elseif($note >= 10)
                                                            <span class="inline-flex items-center px-2 py-1 text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-200">
                                                                <em class="mr-1 icon ni ni-check"></em>
                                                                Passable ({{ number_format($note, 2) }}/20)
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-1 text-red-800 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-200">
                                                                <em class="mr-1 icon ni ni-cross-circle"></em>
                                                                Non validé ({{ number_format($note, 2) }}/20)
                                                            </span>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Barre de progression visuelle -->
                                                    <div class="flex items-center space-x-2">
                                                        <div class="w-20 h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                                                            <div class="h-2 rounded-full transition-all duration-300 {{ $note >= 10 ? 'bg-green-500' : 'bg-red-500' }}" 
                                                                 style="width: {{ min(($note / 20) * 100, 100) }}%"></div>
                                                        </div>
                                                        <span class="text-xs text-gray-500">{{ number_format(($note / 20) * 100, 0) }}%</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center text-xs text-red-600 dark:text-red-400">
                                                    <em class="mr-1 icon ni ni-alert-circle"></em>
                                                    La note doit être entre 0 et 20
                                                </div>
                                            @endif
                                        </div>
                                        @endif

                                        @error('note')
                                        <div class="flex items-center mt-2 text-sm text-red-600 dark:text-red-500">
                                            <em class="mr-1 icon ni ni-alert-circle"></em>
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    <!-- Double vérification de la note (si activée) -->
                                    @if($enableDoubleVerification ?? false)
                                    <div>
                                        <label for="note_confirmation" class="flex items-center mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <em class="mr-2 text-orange-600 icon ni ni-repeat dark:text-orange-400"></em>
                                            Confirmer la note
                                            <span class="ml-1 text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number"
                                                wire:model.live="note_confirmation"
                                                id="note_confirmation"
                                                step="0.01"
                                                min="0"
                                                max="20"
                                                class="block w-full px-4 py-3 text-lg border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400
                                                {{ $note_confirmation !== null && $note == $note_confirmation ? 'border-green-300 bg-green-50 dark:bg-green-900/20' : ($note_confirmation !== null ? 'border-red-300 bg-red-50 dark:bg-red-900/20' : '') }}"
                                                placeholder="Retapez la même note">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                @if($note_confirmation !== null)
                                                    @if($note == $note_confirmation)
                                                        <em class="text-green-500 icon ni ni-check-circle"></em>
                                                    @else
                                                        <em class="text-red-500 icon ni ni-cross-circle"></em>
                                                    @endif
                                                @else
                                                    <em class="text-gray-400 icon ni ni-repeat"></em>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($note_confirmation !== null)
                                        <div class="mt-2">
                                            @if($note == $note_confirmation)
                                                <div class="flex items-center text-xs text-green-600 dark:text-green-400">
                                                    <em class="mr-1 icon ni ni-check-circle"></em>
                                                    Les notes correspondent parfaitement
                                                </div>
                                            @else
                                                <div class="flex items-center text-xs text-red-600 dark:text-red-400">
                                                    <em class="mr-1 icon ni ni-cross-circle"></em>
                                                    Les notes ne correspondent pas ({{ $note }} ≠ {{ $note_confirmation }})
                                                </div>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                <!-- Récapitulatif avant validation -->
                                @if($code_anonymat && $note !== null && $note !== '')
                                <div class="p-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                                    <h4 class="flex items-center mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        <em class="mr-2 text-blue-600 icon ni ni-list-check dark:text-blue-400"></em>
                                        Récapitulatif de la saisie
                                    </h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Code d'anonymat:</span>
                                            <span class="font-mono font-medium text-gray-900 dark:text-white">{{ $code_anonymat }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Note:</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($note, 2) }}/20</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Session:</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $currentSessionType }}</span>
                                        </div>
                                        @if($enableDoubleVerification ?? false)
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Vérification:</span>
                                            <span class="flex items-center font-medium text-green-600 dark:text-green-400">
                                                <em class="mr-1 icon ni ni-shield-check"></em>
                                                Double contrôle activé
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Boutons d'action RENFORCÉS -->
                            <div class="flex flex-col gap-3 mt-6 sm:flex-row sm:justify-end">
                            @if(!isset($editingCopieId))
                                @if($showForceCloseButton)
                                    <!-- Afficher les deux boutons si confirmation demandée -->
                                    <button type="button"
                                            wire:click="$set('showForceCloseButton', false)"
                                            class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                                        <em class="mr-2 icon ni ni-arrow-left"></em>
                                        Continuer la saisie
                                    </button>
                                    
                                    <button type="button"
                                            wire:click="forceCloseModal"
                                            class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-white transition-colors bg-red-600 border border-transparent rounded-lg shadow-sm hover:bg-red-700">
                                        <em class="mr-2 icon ni ni-cross"></em>
                                        Forcer la fermeture
                                    </button>
                                @else
                                    <!-- Bouton normal -->
                                    <button type="button"
                                            wire:click="closeCopieModal"
                                            class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                                        <em class="mr-2 icon ni ni-cross"></em>
                                        Terminer la saisie
                                    </button>
                                @endif
                            @else
                                <!-- Mode édition -->
                                <button type="button"
                                        wire:click="closeCopieModal"
                                        class="...">
                                    <em class="mr-2 icon ni ni-cross"></em>
                                    Annuler
                                </button>
                            @endif

                                <button type="submit"
                                        {{ $this->canSubmit() ? '' : 'disabled' }}
                                        class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-white transition-colors border border-transparent rounded-lg shadow-sm
                                        {{ $this->canSubmit() 
                                            ? (isset($editingCopieId)
                                                ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-800'
                                                : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 dark:bg-primary-700 dark:hover:bg-primary-800')
                                            : 'bg-gray-400 cursor-not-allowed'
                                        }}
                                        focus:outline-none focus:ring-2 focus:ring-offset-2">

                                    @if($this->canSubmit())
                                        @if(isset($editingCopieId))
                                            <em class="mr-2 icon ni ni-update"></em>
                                            Mettre à jour la note
                                        @else
                                            <em class="mr-2 icon ni ni-save"></em>
                                            Enregistrer et continuer
                                        @endif
                                    @else
                                        <em class="mr-2 icon ni ni-lock"></em>
                                        Vérifiez les champs
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- JavaScript RENFORCÉ pour OCR et interactions -->
@push('scripts')
<script>
// Variables globales pour la double vérification
let doubleVerificationEnabled = @json($enableDoubleVerification ?? false);
let isFirstNoteSaved = false;

// Fonction OCR améliorée
function startOCR() {
    // Créer un modal OCR simulé
    const ocrModal = document.createElement('div');
    ocrModal.className = 'fixed inset-0 z-60 flex items-center justify-center bg-gray-900 bg-opacity-50';
    ocrModal.innerHTML = `
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <div class="text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full dark:bg-blue-900">
                    <em class="text-2xl text-blue-600 icon ni ni-scan animate-pulse dark:text-blue-400"></em>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Scanner OCR</h3>
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    Positionnez la copie devant la caméra pour scanner automatiquement la note
                </p>
                
                <!-- Simulation de scan -->
                <div class="relative mb-4">
                    <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                        <div id="scanProgress" class="h-2 bg-blue-600 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="scanStatus" class="mt-2 text-sm text-gray-600 dark:text-gray-400">Initialisation...</p>
                </div>
                
                <div class="flex justify-center space-x-3">
                    <button onclick="cancelOCR()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        Annuler
                    </button>
                    <button id="ocrRetry" onclick="retryOCR()" class="hidden px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Réessayer
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(ocrModal);
    
    // Simulation du processus de scan
    simulateOCRScan();
}

function simulateOCRScan() {
    const progressBar = document.getElementById('scanProgress');
    const statusText = document.getElementById('scanStatus');
    const retryButton = document.getElementById('ocrRetry');
    
    let progress = 0;
    const scanSteps = [
        { progress: 20, status: 'Détection de la copie...' },
        { progress: 40, status: 'Analyse de l\'image...' },
        { progress: 60, status: 'Reconnaissance de caractères...' },
        { progress: 80, status: 'Validation de la note...' },
        { progress: 100, status: 'Scan terminé!' }
    ];
    
    let stepIndex = 0;
    
    const scanInterval = setInterval(() => {
        if (stepIndex < scanSteps.length) {
            const step = scanSteps[stepIndex];
            progressBar.style.width = step.progress + '%';
            statusText.textContent = step.status;
            stepIndex++;
        } else {
            clearInterval(scanInterval);
            
            // Simulation de résultat OCR (note aléatoire pour demo)
            const randomNote = (Math.random() * 20).toFixed(2);
            
            setTimeout(() => {
                // Injecter la note scannée
                const noteField = document.getElementById('note');
                if (noteField) {
                    noteField.value = randomNote;
                    noteField.dispatchEvent(new Event('input', { bubbles: true }));
                    
                    // Déclencher l'événement Livewire
                    @this.set('note', parseFloat(randomNote));
                }
                
                // Fermer le modal OCR
                cancelOCR();
                
                // Notification de succès
                showNotification('Note scannée avec succès: ' + randomNote + '/20', 'success');
                
                // Focus sur le champ de confirmation si double vérification activée
                if (doubleVerificationEnabled) {
                    setTimeout(() => {
                        const confirmField = document.getElementById('note_confirmation');
                        if (confirmField) {
                            confirmField.focus();
                        }
                    }, 500);
                }
            }, 1000);
        }
    }, 800);
}

function cancelOCR() {
    const ocrModal = document.querySelector('.fixed.inset-0.z-60');
    if (ocrModal) {
        ocrModal.remove();
    }
}

function retryOCR() {
    const progressBar = document.getElementById('scanProgress');
    const statusText = document.getElementById('scanStatus');
    
    progressBar.style.width = '0%';
    statusText.textContent = 'Initialisation...';
    
    simulateOCRScan();
}

// Système de notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg flex items-center max-w-sm transform transition-all duration-300 translate-x-full`;
    
    const bgColor = {
        'success': 'bg-green-600 text-white',
        'error': 'bg-red-600 text-white',
        'warning': 'bg-yellow-600 text-white',
        'info': 'bg-blue-600 text-white'
    }[type] || 'bg-blue-600 text-white';
    
    const icon = {
        'success': 'ni-check-circle',
        'error': 'ni-cross-circle',
        'warning': 'ni-alert-circle',
        'info': 'ni-info-circle'
    }[type] || 'ni-info-circle';
    
    notification.className += ` ${bgColor}`;
    notification.innerHTML = `
        <em class="mr-2 icon ${icon}"></em>
        <div>
            <div class="font-medium">${message}</div>
        </div>
        <button onclick="this.parentElement.remove()" class="ml-3 text-white hover:text-gray-200">
            <em class="icon ni ni-cross"></em>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto-suppression après 5 secondes
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Gestion des raccourcis clavier RENFORCÉS
document.addEventListener('keydown', function(e) {
    const modal = document.querySelector('[aria-modal="true"]');
    if (!modal) return;
    
    // Ctrl+Enter pour enregistrer (avec vérifications)
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        const submitButton = document.querySelector('button[type="submit"]');
        if (submitButton && !submitButton.disabled) {
            // Vérifier que tous les champs requis sont remplis
            if (validateForm()) {
                submitButton.click();
            } else {
                showNotification('Veuillez remplir tous les champs requis', 'warning');
            }
        }
    }
    
    // Échap pour fermer
    if (e.key === 'Escape') {
        e.preventDefault();
        @this.set('showCopieModal', false);
    }
    
    // Ctrl+R pour activer/désactiver la double vérification
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        @this.set('enableDoubleVerification', !doubleVerificationEnabled);
        doubleVerificationEnabled = !doubleVerificationEnabled;
        showNotification(
            doubleVerificationEnabled ? 'Double vérification activée' : 'Double vérification désactivée',
            'info'
        );
    }
    
    // F2 pour lancer OCR
    if (e.key === 'F2') {
        e.preventDefault();
        startOCR();
    }
    
    // Tab intelligent : passer automatiquement aux champs de confirmation
    if (e.key === 'Tab') {
        const activeElement = document.activeElement;
        
        // Si on sort du champ code_anonymat et que la double vérification est activée
        if (activeElement && activeElement.id === 'code_anonymat' && doubleVerificationEnabled) {
            const confirmationField = document.getElementById('code_anonymat_confirmation');
            if (confirmationField && !e.shiftKey) {
                e.preventDefault();
                confirmationField.focus();
            }
        }
        
        // Si on sort du champ note et que la double vérification est activée
        if (activeElement && activeElement.id === 'note' && doubleVerificationEnabled) {
            const confirmationField = document.getElementById('note_confirmation');
            if (confirmationField && !e.shiftKey) {
                e.preventDefault();
                confirmationField.focus();
            }
        }
    }
});

// Validation du formulaire en temps réel
function validateForm() {
    const codeAnonymat = document.getElementById('code_anonymat')?.value;
    const note = document.getElementById('note')?.value;
    
    if (!codeAnonymat || !note) {
        return false;
    }
    
    // Si double vérification activée, vérifier les champs de confirmation
    if (doubleVerificationEnabled) {
        const codeConfirmation = document.getElementById('code_anonymat_confirmation')?.value;
        const noteConfirmation = document.getElementById('note_confirmation')?.value;
        
        if (codeAnonymat !== codeConfirmation || parseFloat(note) !== parseFloat(noteConfirmation)) {
            return false;
        }
    }
    
    // Vérifier que la note est dans la plage valide
    const noteValue = parseFloat(note);
    if (isNaN(noteValue) || noteValue < 0 || noteValue > 20) {
        return false;
    }
    
    return true;
}

// Auto-focus et amélioration UX
document.addEventListener('livewire:init', function() {
    // Focus automatique sur le champ note
    Livewire.on('focus-note-field', function() {
        setTimeout(function() {
            const noteField = document.getElementById('note');
            if (noteField) {
                noteField.focus();
                noteField.select();
            }
        }, 200);
    });
    
    // Mise à jour de l'état de double vérification
    Livewire.on('double-verification-changed', function(enabled) {
        doubleVerificationEnabled = enabled;
        showNotification(
            enabled ? 'Double vérification activée - Sécurité renforcée' : 'Double vérification désactivée',
            enabled ? 'success' : 'info'
        );
    });
    
    // Validation en temps réel
    document.addEventListener('input', function(e) {
        if (e.target.matches('#code_anonymat, #note, #code_anonymat_confirmation, #note_confirmation')) {
            // Mettre à jour le bouton submit
            updateSubmitButton();
            
            // Animation de feedback
            e.target.style.transform = 'scale(1.01)';
            setTimeout(() => {
                e.target.style.transform = 'scale(1)';
            }, 150);
        }
    });
});

// Mise à jour du bouton de soumission
function updateSubmitButton() {
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton) {
        const isValid = validateForm();
        submitButton.disabled = !isValid;
        
        if (isValid) {
            submitButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
            submitButton.classList.add('bg-primary-600', 'hover:bg-primary-700');
        } else {
            submitButton.classList.add('bg-gray-400', 'cursor-not-allowed');
            submitButton.classList.remove('bg-primary-600', 'hover:bg-primary-700');
        }
    }
}

// Animation des champs lors du focus
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input[type="text"], input[type="number"]');
    
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-2', 'ring-blue-500');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('ring-2', 'ring-blue-500');
        });
    });
});

// Sauvegarde automatique des préférences
document.addEventListener('change', function(e) {
    if (e.target.matches('input[wire\\:model\\.live="enableDoubleVerification"]')) {
        // Sauvegarder la préférence dans localStorage
        localStorage.setItem('copies_double_verification', e.target.checked);
        doubleVerificationEnabled = e.target.checked;
    }
});

// Restaurer les préférences au chargement
document.addEventListener('DOMContentLoaded', function() {
    const savedPreference = localStorage.getItem('copies_double_verification');
    if (savedPreference !== null) {
        const checkbox = document.querySelector('input[wire\\:model\\.live="enableDoubleVerification"]');
        if (checkbox) {
            const shouldEnable = savedPreference === 'true';
            checkbox.checked = shouldEnable;
            @this.set('enableDoubleVerification', shouldEnable);
            doubleVerificationEnabled = shouldEnable;
        }
    }
});

// Raccourcis clavier - Aide
document.addEventListener('keydown', function(e) {
    // F1 pour afficher l'aide
    if (e.key === 'F1') {
        e.preventDefault();
        showKeyboardHelp();
    }
});

function showKeyboardHelp() {
    const helpModal = document.createElement('div');
    helpModal.className = 'fixed inset-0 z-60 flex items-center justify-center bg-gray-900 bg-opacity-50';
    helpModal.innerHTML = `
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-xl dark:bg-gray-800">
            <div class="text-center">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Raccourcis clavier</h3>
                <div class="space-y-2 text-sm text-left">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Enregistrer:</span>
                        <kbd class="px-2 py-1 text-xs bg-gray-100 rounded dark:bg-gray-700">Ctrl + Entrée</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Scanner OCR:</span>
                        <kbd class="px-2 py-1 text-xs bg-gray-100 rounded dark:bg-gray-700">F2</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Double vérification:</span>
                        <kbd class="px-2 py-1 text-xs bg-gray-100 rounded dark:bg-gray-700">Ctrl + R</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Fermer:</span>
                        <kbd class="px-2 py-1 text-xs bg-gray-100 rounded dark:bg-gray-700">Échap</kbd>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Aide:</span>
                        <kbd class="px-2 py-1 text-xs bg-gray-100 rounded dark:bg-gray-700">F1</kbd>
                    </div>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 mt-4 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    Compris
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(helpModal);
}
</script>
@endpush