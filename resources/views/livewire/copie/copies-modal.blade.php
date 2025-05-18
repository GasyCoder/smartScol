    <!-- Modale de saisie de copie -->
    @if($showCopieModal)
    <div class="fixed inset-0 z-10 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

            <!-- Centrage modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Contenu modal -->
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 dark:bg-gray-800">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                                {{ isset($editingCopieId) ? 'Modifier une note' : 'Saisir une note' }}
                            </h3>

                            <!-- Informations contextuelles -->
                            <div class="p-3 mt-3 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-blue-900 dark:text-blue-200">
                                <div class="mb-1 font-semibold">Informations de saisie:</div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div><span class="font-medium">Salle:</span> {{ $currentSalleName }}</div>
                                    <div><span class="font-medium">Code salle:</span> {{ $selectedSalleCode }}</div>
                                    <div><span class="font-medium">Matière:</span> {{ $currentEcName }}</div>
                                    <div><span class="font-medium">Date:</span> {{ $currentEcDate }}</div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <form wire:submit.prevent="saveCopie">
                                    <div class="space-y-4">
                                     <!-- Code anonymat -->
                                        <div>
                                            <label for="code_anonymat" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Code d'anonymat
                                            </label>
                                            <div class="mt-1">
                                                <input type="text"
                                                wire:model="code_anonymat"
                                                id="code_anonymat"
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                placeholder="Ex: {{ $selectedSalleCode }}1"
                                                autofocus>
                                            </div>
                                            <p class="mt-1 text-xs text-yellow-300">Code suggéré: {{ $code_anonymat }}. Vérifiez qu'il correspond à celui de la copie.</p>
                                            @error('code_anonymat') <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                                        </div>

                                        <!-- Note -->
                                        <div>
                                            <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Note sur 20
                                            </label>
                                            <div class="mt-1">
                                                <input type="number"
                                                    wire:model="note"
                                                    id="note"
                                                    step="0.01"
                                                    min="0"
                                                    max="20"
                                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                    placeholder="Ex: 15.5">
                                            </div>
                                            @error('note') <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                        @if(!isset($editingCopieId))
                                        <button type="button" wire:click="$set('showCopieModal', false)" class="inline-flex items-center justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                            <em class="mr-2 ni ni-cross"></em>
                                            Terminer
                                        </button>
                                        @else
                                        <button type="button" wire:click="$set('showCopieModal', false)" class="inline-flex items-center justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                            <em class="mr-2 ni ni-cross"></em>
                                            Annuler
                                        </button>
                                        @endif

                                        <button type="submit" class="inline-flex items-center justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm
                                            {{ isset($editingCopieId)
                                                ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-800'
                                                : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 dark:bg-primary-700 dark:hover:bg-primary-800'
                                            }}
                                            focus:outline-none focus:ring-2 focus:ring-offset-2 sm:col-start-2 sm:text-sm">

                                            @if(isset($editingCopieId))
                                                <em class="mr-2 ni ni-update"></em>
                                                Mettre à jour
                                            @else
                                                <em class="mr-2 ni ni-save"></em>
                                                Enregistrer
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
    </div>
    @endif
