<div>
    <div class="max-w-4xl py-6 mx-auto">
        <!-- En-tête -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-medium text-gray-900 dark:text-white">Modifier une unité d'enseignement</h1>
            </div>
            <div>
                <button wire:click="cancel" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 -ml-1 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Retour
                </button>
            </div>
        </div>

        <!-- Messages de confirmation ou d'erreur -->
        @if (session()->has('success'))
            <div class="p-4 mb-6 text-green-700 bg-green-100 border border-green-200 rounded-md dark:bg-green-900/30 dark:border-green-800 dark:text-green-400">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 mb-6 text-red-700 bg-red-100 border border-red-200 rounded-md dark:bg-red-900/30 dark:border-red-800 dark:text-red-400">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Formulaire de modification d'UE -->
        <div class="overflow-hidden bg-white rounded-lg shadow-sm dark:bg-gray-900">
            <!-- Fil d'Ariane -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ route('unite_e') }}" class="hover:text-primary-600 dark:hover:text-primary-400">Unités d'enseignement</a>
                    <svg class="w-4 h-4 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-yellow-700">{{ $niveau->nom }}</span>
                    <svg class="w-4 h-4 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-yellow-700">{{ $parcours->nom }}</span>
                    <svg class="w-4 h-4 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Modifier UE</span>
                </div>
            </div>

            <!-- Contenu du formulaire -->
            <div class="px-6 py-5">
                <form wire:submit.prevent="save">
                    <!-- Section UE -->
                    <div class="mb-8">
                        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Informations de l'unité d'enseignement</h3>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <!-- Abréviation UE -->
                            <div class="sm:col-span-1">
                                <label for="ueAbr" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Abréviation <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model="ueAbr" id="ueAbr" placeholder="Ex: UE1" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                </div>
                                @error('ueAbr') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nom UE -->
                            <div class="sm:col-span-4">
                                <label for="ueNom" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nom <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model="ueNom" id="ueNom" placeholder="Ex: Médecine humaine" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                </div>
                                @error('ueNom') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div class="sm:col-span-1">
                                <label for="ueCredits" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Crédits <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model="ueCredits" id="ueCredits" placeholder="Nombre de crédits" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                </div>
                                @error('ueCredits') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                        </div>
                    </div>

                    <!-- Section EC -->
                    <div class="pt-6 mt-8 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Éléments constitutifs (EC)</h3>
                        </div>

                        <!-- EC existants -->
                        @if(count($existingEcs) > 0)
                            <div class="mb-6">
                                <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">EC existants</h4>

                                @foreach($existingEcs as $index => $ec)
                                    <div class="p-4 mb-4 border border-gray-200 rounded-lg dark:border-gray-700">
                                        <div class="flex items-center justify-between mb-3">
                                            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">EC existant {{ $index + 1 }}</h5>
                                            <button type="button" wire:click="removeExistingEC({{ $index }})" class="text-red-500 hover:text-red-700 dark:hover:text-red-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-12">
                                            <!-- ID caché -->
                                            <input type="hidden" wire:model="existingEcs.{{ $index }}.id">

                                            <!-- Abréviation EC -->
                                            <div class="sm:col-span-2">
                                                <label for="ec-abr-{{ $index }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Abréviation <span class="text-red-500">*</span>
                                                </label>
                                                <div class="mt-1">
                                                    <input type="text" wire:model="existingEcs.{{ $index }}.abr" id="ec-abr-{{ $index }}" placeholder="Ex: EC1" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                                </div>
                                                @error('existingEcs.'.$index.'.abr') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>

                                            <!-- Nom EC -->
                                            <div class="sm:col-span-5">
                                                <label for="ec-nom-{{ $index }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Nom <span class="text-red-500">*</span>
                                                </label>
                                                <div class="mt-1">
                                                    <input type="text" wire:model="existingEcs.{{ $index }}.nom" id="ec-nom-{{ $index }}" placeholder="Ex: Anatomie" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                                </div>
                                                @error('existingEcs.'.$index.'.nom') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>

                                            <!-- Enseignant -->
                                            <div class="sm:col-span-5">
                                                <label for="ec-enseignant-{{ $index }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Enseignant <span class="text-red-500">*</span>
                                                </label>
                                                <div class="mt-1">
                                                    <input type="text" wire:model="existingEcs.{{ $index }}.enseignant" id="ec-enseignant-{{ $index }}" placeholder="Prof RAKOTO" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                                </div>
                                                @error('existingEcs.'.$index.'.enseignant') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Nouveaux EC -->
                        @if(count($newEcs) > 0)
                            <div class="mb-6">
                                <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Nouveaux EC</h4>

                                @foreach($newEcs as $index => $ec)
                                    <div class="p-4 mb-4 border border-gray-200 rounded-lg dark:border-gray-700">
                                        <div class="flex items-center justify-between mb-3">
                                            <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Nouvel EC {{ $index + 1 }}</h5>
                                            <button type="button" wire:click="removeNewEC({{ $index }})" class="text-red-500 hover:text-red-700 dark:hover:text-red-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-12">
                                            <!-- Abréviation EC -->
                                            <div class="sm:col-span-2">
                                                <label for="new-ec-abr-{{ $index }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Abréviation <span class="text-red-500">*</span>
                                                </label>
                                                <div class="mt-1">
                                                    <input type="text" wire:model="newEcs.{{ $index }}.abr" id="new-ec-abr-{{ $index }}" placeholder="Ex: EC1" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                                </div>
                                                @error('newEcs.'.$index.'.abr') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>

                                            <!-- Nom EC -->
                                            <div class="sm:col-span-5">
                                                <label for="new-ec-nom-{{ $index }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Nom <span class="text-red-500">*</span>
                                                </label>
                                                <div class="mt-1">
                                                    <input type="text" wire:model="newEcs.{{ $index }}.nom" id="new-ec-nom-{{ $index }}" placeholder="Ex: Anatomie" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                                </div>
                                                @error('newEcs.'.$index.'.nom') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>

                                            <!-- Enseignant -->
                                            <div class="sm:col-span-5">
                                                <label for="new-ec-enseignant-{{ $index }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Enseignant <span class="text-red-500">*</span>
                                                </label>
                                                <div class="mt-1">
                                                    <input type="text" wire:model="newEcs.{{ $index }}.enseignant" id="new-ec-enseignant-{{ $index }}" placeholder="Prof RAKOTO" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                                </div>
                                                @error('newEcs.'.$index.'.enseignant') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Bouton pour ajouter un EC -->
                        <div class="flex justify-center mt-4">
                            <button type="button" wire:click="addNewEC" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 -ml-1 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Ajouter un nouvel EC
                            </button>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex justify-end mt-8 space-x-3">
                        <button type="button" wire:click="cancel" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                            Annuler
                        </button>
                        <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none dark:bg-green-700 dark:hover:bg-green-800">
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    @push('scripts')
    <script>
        // Variable pour suivre si le hash a été traité
        let hashProcessed = false;

        // Fonction pour traiter le hash de l'URL
        function processUrlHash() {
            if (hashProcessed) return; // Éviter le traitement multiple

            const hash = window.location.hash.substring(1);
            if (!hash) return;

            const parts = hash.split('-');
            const step = parts[0];

            // Si nous avons accès à l'instance Livewire
            if (typeof Livewire !== 'undefined') {
                console.log('Traitement du hash:', hash);

                if (step === 'ue' && parts.length > 2) {
                    const niveauId = parseInt(parts[1]);
                    const parcoursId = parseInt(parts[2]);

                    console.log('Navigation vers UE:', {niveauId, parcoursId});

                    // Marquer comme traité
                    hashProcessed = true;

                    // Naviguer vers l'étape UE immédiatement
                    Livewire.dispatch('changeStep', {
                        step: 'ue',
                        niveauId: niveauId,
                        parcoursId: parcoursId
                    });
                }
                else if (step === 'parcours' && parts.length > 1) {
                    const niveauId = parseInt(parts[1]);

                    console.log('Navigation vers Parcours:', {niveauId});

                    // Marquer comme traité
                    hashProcessed = true;

                    // Naviguer vers l'étape parcours
                    Livewire.dispatch('changeStep', {
                        step: 'parcours',
                        niveauId: niveauId
                    });
                }
            } else {
                // Réessayer plus tard si Livewire n'est pas encore disponible
                setTimeout(processUrlHash, 100);
            }
        }

        // Écoute pour l'initialisation de Livewire (priorité élevée)
        document.addEventListener('livewire:initialized', function() {
            console.log('Livewire initialisé, traitement du hash...');

            // Traiter le hash dès que Livewire est initialisé
            processUrlHash();

            // Configurer l'écouteur pour les mises à jour d'URL
            Livewire.on('updateUrlHash', function(data) {
                const step = data.step;
                const niveauId = data.niveauId;
                const parcoursId = data.parcoursId;

                if (step === 'niveau') {
                    history.pushState(null, null, window.location.pathname);
                }
                else if (step === 'parcours' && niveauId) {
                    history.pushState(null, null, window.location.pathname + '#' + step + '-' + niveauId);
                }
                else if (step === 'ue' && niveauId && parcoursId) {
                    history.pushState(null, null, window.location.pathname + '#' + step + '-' + niveauId + '-' + parcoursId);
                }
            });
        }, { once: true }); // S'assurer que l'événement n'est traité qu'une fois

        // Essayer de traiter le hash dès que le DOM est chargé
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM chargé, tentative de traitement du hash...');
            processUrlHash();
        });

        // Tenter de traiter le hash après un court délai (filet de sécurité)
        setTimeout(function() {
            console.log('Délai écoulé, tentative de traitement du hash...');
            processUrlHash();
        }, 300);

        // Gérer les boutons avant/arrière du navigateur
        window.addEventListener('popstate', function() {
            console.log('Navigation popstate détectée');
            hashProcessed = false; // Réinitialiser pour permettre un nouveau traitement
            processUrlHash();
        });
    </script>
    @endpush
