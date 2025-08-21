<!-- Modal de saisie de présence - STYLE IDENTIQUE À VOTRE MODAL MANCHETTE -->
@if($showPresenceModal)
<div class="fixed inset-0 z-20 overflow-y-auto" aria-labelledby="presence-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

        <!-- Centrage modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Contenu modal - MÊME STYLE QUE VOTRE MODAL MANCHETTE -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 dark:bg-gray-800">
                <div class="sm:flex sm:items-start">
                    <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="presence-modal-title">
                            {{ $presenceEnregistree ? 'Modifier les données de présence' : 'Totale absent et présent' }}
                        </h3>
                        <form wire:submit.prevent="savePresence" class="mt-4">
                            <div class="space-y-4">
                                <!-- Total attendu - Information -->
                                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Total d'étudiants attendus :
                                        </span>
                                        <span class="px-3 py-1 text-sm font-bold text-blue-800 bg-blue-200 rounded-full dark:bg-blue-700 dark:text-blue-200">
                                            {{ $totalEtudiantsCount }} étudiant(s)
                                        </span>
                                    </div>
                                </div>

                                <!-- Nombre d'étudiants présents -->
                                <div>
                                    <label for="etudiants_presents" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Totale manchettes <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1">
                                        <input type="number" 
                                               wire:model.live="etudiants_presents"
                                               id="etudiants_presents"
                                               min="0" 
                                               max="{{ $totalEtudiantsCount }}"
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                               placeholder="Nombre de présents"
                                               autofocus>
                                    </div>
                                    @error('etudiants_presents') 
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> 
                                    @enderror
                                </div>

                                <!-- Nombre d'étudiants absents -->
                                <div>
                                    <label for="etudiants_absents" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Nombre d'étudiants absents <span class="text-red-500">auto</span>
                                    </label>
                                    <div class="mt-1">
                                        <input type="number" 
                                               wire:model.live="etudiants_absents"
                                               id="etudiants_absents"
                                               min="0" 
                                               max="{{ $totalEtudiantsCount }}"
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                               placeholder="Nombre d'absents">
                                    </div>
                                    @error('etudiants_absents') 
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> 
                                    @enderror
                                </div>

                                <!-- Calcul automatique du total - MÊME STYLE QUE VOS COMPOSANTS -->
                                @if($etudiants_presents !== null && $etudiants_absents !== null)
                                <div class="p-3 border border-gray-200 rounded-md bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="flex justify-between">
                                                <span>Total saisi :</span>
                                                <span class="font-medium">{{ (int)$etudiants_presents + (int)$etudiants_absents }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Total attendu :</span>
                                                <span class="font-medium">{{ $totalEtudiantsCount }}</span>
                                            </div>
                                        </div>
                                        
                                        @php 
                                            $totalSaisi = (int)$etudiants_presents + (int)$etudiants_absents;
                                            $difference = $totalEtudiantsCount - $totalSaisi;
                                        @endphp
                                        
                                        @if($difference != 0)
                                        <div class="flex justify-between pt-2 mt-2 border-t border-gray-300 dark:border-gray-600 {{ $difference > 0 ? 'text-orange-600' : 'text-red-600' }}">
                                            <span>Différence :</span>
                                            <span class="font-medium">{{ $difference > 0 ? '+' : '' }}{{ $difference }}</span>
                                        </div>
                                        @endif
                                        
                                        @if($etudiants_presents > 0)
                                        <div class="flex justify-between pt-2 mt-2 text-green-600 border-t border-gray-300 dark:border-gray-600">
                                            <span>Taux de présence :</span>
                                            <span class="font-medium">{{ round(((int)$etudiants_presents / ((int)$etudiants_presents + (int)$etudiants_absents)) * 100, 1) }}%</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                <!-- Avertissement si données incohérentes -->
                                @if(session('presence_warning'))
                                <div class="p-3 text-sm text-orange-800 bg-orange-100 rounded-lg dark:bg-orange-900 dark:text-orange-200">
                                    <div class="flex items-center">
                                        <em class="mr-2 ni ni-alert-fill"></em>
                                        {{ session('presence_warning') }}
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Boutons d'action - MÊME STYLE QUE VOTRE MODAL MANCHETTE -->
                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="button"
                                        wire:click="closePresenceModal"
                                        class="inline-flex items-center justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                    <em class="mr-2 ni ni-cross"></em>
                                    Annuler
                                </button>

                                <button type="submit" 
                                        class="inline-flex items-center justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm
                                        {{ $presenceEnregistree 
                                            ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-800'
                                            : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 dark:bg-primary-700 dark:hover:bg-primary-800'
                                        }}
                                        focus:outline-none focus:ring-2 focus:ring-offset-2 sm:col-start-2 sm:text-sm">
                                    
                                    @if($presenceEnregistree)
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
@endif