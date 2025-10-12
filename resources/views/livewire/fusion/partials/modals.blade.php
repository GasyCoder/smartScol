{{-- Modales pour le processus de fusion - Design moderne --}}

{{-- 1. Modal Vérification de cohérence --}}
@if($confirmingVerification)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-60 backdrop-blur-sm animate-fadeIn">
    <div class="relative w-full max-w-md mx-4 transform transition-all animate-slideUp">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            {{-- Header avec gradient --}}
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        Vérification de cohérence
                    </h3>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    Voulez-vous lancer la vérification de cohérence des données ? Cette action analysera les manchettes et copies pour générer un rapport détaillé.
                </p>

                <div class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl dark:from-blue-900/20 dark:to-indigo-900/20 dark:border-blue-800">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold text-white">1</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">
                                Première étape du processus
                            </p>
                            <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">
                                Cette vérification est requise avant de lancer la fusion des données
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3">
                <button wire:click="$set('confirmingVerification', false)"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="verifierCoherence"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Vérifier
                        <span wire:loading wire:target="verifierCoherence">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 2. Modal Fusion des données --}}
@if($confirmingFusion)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-60 backdrop-blur-sm animate-fadeIn">
    <div class="relative w-full max-w-md mx-4 transform transition-all animate-slideUp">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            {{-- Header avec gradient --}}
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        Fusion des données
                    </h3>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    Voulez-vous lancer la fusion des données ? Cette action associera les manchettes aux copies pour générer les résultats provisoires.
                </p>

                <div class="mt-4 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl dark:from-yellow-900/20 dark:to-orange-900/20 dark:border-yellow-800">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold text-white">2</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-200">
                                Deuxième étape du processus
                            </p>
                            <p class="mt-1 text-xs text-yellow-700 dark:text-yellow-300">
                                La fusion associe les manchettes aux copies. Assurez-vous que le rapport de cohérence est validé
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3">
                <button wire:click="$set('confirmingFusion', false)"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="lancerFusion"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg hover:from-yellow-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Lancer la fusion
                        <span wire:loading wire:target="lancerFusion">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 3. Modal Avancer à VERIFY_2 --}}
@if($confirmingVerify2)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-60 backdrop-blur-sm animate-fadeIn">
    <div class="relative w-full max-w-md mx-4 transform transition-all animate-slideUp">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            {{-- Header avec gradient --}}
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        Vérification 2
                    </h3>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    Voulez-vous avancer la fusion à l'étape VÉRIFICATION 2 ? Cette action validera les données associées.
                </p>

                <div class="mt-4 p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl dark:from-amber-900/20 dark:to-orange-900/20 dark:border-amber-800">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">
                                Validation de l'étape 2
                            </p>
                            <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                Assurez-vous que toutes les copies sont vérifiées avant de continuer
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3">
                <button wire:click="$set('confirmingVerify2', false)"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="passerAVerify2"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 rounded-lg hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <span class="flex items-center gap-2">
                        Avancer à l'étape 2
                        <span wire:loading wire:target="passerAVerify2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 4. Modal Avancer à VERIFY_3 --}}
@if($confirmingVerify3)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-60 backdrop-blur-sm animate-fadeIn">
    <div class="relative w-full max-w-md mx-4 transform transition-all animate-slideUp">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            {{-- Header avec gradient --}}
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        Finalisation
                    </h3>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    Voulez-vous finaliser la fusion ? Cette action prépare les résultats pour la validation finale.
                </p>

                <div class="mt-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl dark:from-green-900/20 dark:to-emerald-900/20 dark:border-green-800">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold text-white">3</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-green-900 dark:text-green-200">
                                Dernière étape de fusion
                            </p>
                            <p class="mt-1 text-xs text-green-700 dark:text-green-300">
                                Assurez-vous que toutes les données de la seconde vérification sont correctes
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3">
                <button wire:click="$set('confirmingVerify3', false)"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="passerAVerify3"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Terminer fusion
                        <span wire:loading wire:target="passerAVerify3">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 5. Modal Validation --}}
@if($confirmingValidation)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-60 backdrop-blur-sm animate-fadeIn">
    <div class="relative w-full max-w-lg mx-4 transform transition-all animate-slideUp">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            {{-- Header avec gradient --}}
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        Validation des résultats
                    </h3>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    Voulez-vous valider les résultats fusionnés ? Cette action les marquera comme vérifiés et prêts pour la publication.
                </p>

                @if($sessionActive)
                    <div class="mt-4 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-xl dark:from-purple-900/20 dark:to-indigo-900/20 dark:border-purple-800">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-purple-900 dark:text-purple-200">
                                    Validation pour {{ $examen->niveau->nom ?? 'Niveau inconnu' }}
                                </p>
                                <p class="text-xs text-purple-700 dark:text-purple-300 mt-1">
                                    Session {{ $sessionActive->type }}
                                </p>
                                <ul class="mt-2 space-y-1">
                                    <li class="flex items-center gap-2 text-xs text-purple-700 dark:text-purple-300">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Vérification des notes et associations
                                    </li>
                                    <li class="flex items-center gap-2 text-xs text-purple-700 dark:text-purple-300">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Calcul des moyennes par étudiant
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-4 p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl dark:from-amber-900/20 dark:to-orange-900/20 dark:border-amber-800">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">
                            Vérifiez tous les résultats avant de valider
                        </p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3">
                <button wire:click="$set('confirmingValidation', false)"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="validerResultats"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Valider les résultats
                        <span wire:loading wire:target="validerResultats">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 6. Modal Publication --}}
@if($confirmingPublication)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-60 backdrop-blur-sm animate-fadeIn">
    <div class="relative w-full max-w-lg mx-4 transform transition-all animate-slideUp">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            @php
                $resultatsEnAttente = \App\Models\ResultatFinal::where('examen_id', $examen_id)
                    ->where('session_exam_id', $sessionActive->id)
                    ->where('statut', \App\Models\ResultatFinal::STATUT_EN_ATTENTE)
                    ->exists();
                $estReactivation = $resultatsEnAttente;
                $isConcours = $estPACES;
            @endphp

            {{-- Header avec gradient --}}
            <div class="bg-gradient-to-r {{ $estReactivation ? 'from-blue-500 to-indigo-600' : 'from-green-500 to-emerald-600' }} px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        {{ $estReactivation ? 'Republication' : 'Publication' }} des résultats
                    </h3>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    @if($estReactivation)
                        {{ $isConcours 
                            ? 'Voulez-vous republier et classer les résultats ? Ils seront immédiatement accessibles après recalcul du classement.'
                            : 'Voulez-vous republier les résultats ? Ils seront immédiatement accessibles après recalcul des décisions.' }}
                    @else
                        {{ $isConcours 
                            ? 'Voulez-vous classer et publier les résultats ? Ils seront immédiatement accessibles aux étudiants.'
                            : 'Voulez-vous publier les résultats ? Ils seront immédiatement accessibles aux étudiants.' }}
                    @endif
                </p>

                @if($sessionActive)
                    <div class="mt-4 p-4 bg-gradient-to-r {{ $estReactivation ? 'from-blue-50 to-indigo-50 border-blue-200 dark:from-blue-900/20 dark:to-indigo-900/20 dark:border-blue-800' : 'from-green-50 to-emerald-50 border-green-200 dark:from-green-900/20 dark:to-emerald-900/20 dark:border-green-800' }} border rounded-xl">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 {{ $estReactivation ? 'text-blue-600 dark:text-blue-400' : 'text-green-600 dark:text-green-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold {{ $estReactivation ? 'text-blue-900 dark:text-blue-200' : 'text-green-900 dark:text-green-200' }}">
                                    {{ $estReactivation ? 'Republication' : 'Publication' }} : {{ $examen->niveau->nom ?? 'Niveau inconnu' }}
                                </p>
                                <p class="text-xs {{ $estReactivation ? 'text-blue-700 dark:text-blue-300' : 'text-green-700 dark:text-green-300' }} mt-1">
                                    Session {{ $sessionActive->type }}
                                </p>
                                <ul class="mt-2 space-y-1">
                                    @if($estReactivation)
                                        <li class="flex items-center gap-2 text-xs {{ $estReactivation ? 'text-blue-700 dark:text-blue-300' : 'text-green-700 dark:text-green-300' }}">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Recalcul automatique des moyennes et décisions
                                        </li>
                                        <li class="flex items-center gap-2 text-xs {{ $estReactivation ? 'text-blue-700 dark:text-blue-300' : 'text-green-700 dark:text-green-300' }}">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Conservation de l'historique des modifications
                                        </li>
                                    @else
                                        <li class="flex items-center gap-2 text-xs {{ $estReactivation ? 'text-blue-700 dark:text-blue-300' : 'text-green-700 dark:text-green-300' }}">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Transfert vers la table resultats_finaux
                                        </li>
                                        <li class="flex items-center gap-2 text-xs {{ $estReactivation ? 'text-blue-700 dark:text-blue-300' : 'text-green-700 dark:text-green-300' }}">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Calcul des décisions (admis, rattrapage, exclus)
                                        </li>
                                    @endif
                                    <li class="flex items-center gap-2 text-xs {{ $estReactivation ? 'text-blue-700 dark:text-blue-300' : 'text-green-700 dark:text-green-300' }}">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Génération des hash de vérification
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="mt-4 p-4 bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-xl dark:from-red-900/20 dark:to-orange-900/20 dark:border-red-800">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-red-900 dark:text-red-200">
                            Cette action est irréversible sans annulation complète
                        </p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3">
                <button wire:click="$set('confirmingPublication', false)"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="publierResultats"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r {{ $estReactivation ? 'from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:ring-blue-500' : 'from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:ring-green-500' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        {{ $estReactivation ? 'Republier' : 'Publier' }}
                        <span wire:loading wire:target="publierResultats">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 7. Modal Réinitialisation --}}
@if($confirmingResetFusion)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-60 backdrop-blur-sm animate-fadeIn">
    <div class="relative w-full max-w-md mx-4 transform transition-all animate-slideUp">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            {{-- Header avec gradient --}}
            <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        Réinitialisation
                    </h3>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    Voulez-vous réinitialiser le processus de fusion et de validation ? Cette action supprimera tous les résultats fusionnés et finaux pour cet examen.
                </p>

                <div class="mt-4 p-4 bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-xl dark:from-red-900/20 dark:to-orange-900/20 dark:border-red-800">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-red-900 dark:text-red-200">
                                Action irréversible - Suppression de :
                            </p>
                            <ul class="mt-2 space-y-1">
                                <li class="flex items-center gap-2 text-xs text-red-700 dark:text-red-300">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Résultats de fusion (resultats_fusion)
                                </li>
                                <li class="flex items-center gap-2 text-xs text-red-700 dark:text-red-300">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Résultats finaux (resultats_finaux)
                                </li>
                                <li class="flex items-center gap-2 text-xs text-red-700 dark:text-red-300">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Historique des statuts
                                </li>
                            </ul>
                            <p class="mt-2 text-xs text-green-700 dark:text-green-400 flex items-center gap-2">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Les copies et manchettes originales resteront intactes
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3">
                <button wire:click="$set('confirmingResetFusion', false)"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="resetFusion"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-red-600 to-red-700 rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Réinitialiser
                        <span wire:loading wire:target="resetFusion">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 8. Modal Réactivation --}}
@if($confirmingRevenirValidation)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-60 backdrop-blur-sm animate-fadeIn">
    <div class="relative w-full max-w-md mx-4 transform transition-all animate-slideUp">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            {{-- Header avec gradient --}}
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        Réactivation
                    </h3>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    Voulez-vous réactiver les résultats annulés ? Ils seront remis à l'état "en attente" pour une nouvelle validation et publication.
                </p>

                <div class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl dark:from-blue-900/20 dark:to-indigo-900/20 dark:border-blue-800">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold text-white">3</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">
                                Retour à l'étape de validation
                            </p>
                            <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">
                                Les résultats reviendront à l'étape de validation pour une nouvelle vérification
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3">
                <button wire:click="$set('confirmingRevenirValidation', false)"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="revenirValidation"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Réactiver
                        <span wire:loading wire:target="revenirValidation">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 9. Modal Annulation --}}
@if($confirmingAnnulation)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-60 backdrop-blur-sm animate-fadeIn">
    <div class="relative w-full max-w-lg mx-4 transform transition-all animate-slideUp">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            {{-- Header avec gradient --}}
            <div class="bg-gradient-to-r from-red-500 to-orange-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        Annulation des résultats
                    </h3>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    Voulez-vous annuler les résultats publiés ? Cette action les rendra indisponibles aux étudiants tout en préservant les données pour une éventuelle réactivation.
                </p>

                @if($sessionActive)
                    <div class="mt-4 p-4 bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 rounded-xl dark:from-orange-900/20 dark:to-red-900/20 dark:border-orange-800">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-orange-900 dark:text-orange-200">
                                    Annulation : {{ $examen->niveau->nom ?? 'Niveau inconnu' }}
                                </p>
                                <p class="text-xs text-orange-700 dark:text-orange-300 mt-1">
                                    Session {{ $sessionActive->type }}
                                </p>
                                <ul class="mt-2 space-y-1">
                                    <li class="flex items-center gap-2 text-xs text-orange-700 dark:text-orange-300">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Masquage immédiat des résultats pour les étudiants
                                    </li>
                                    <li class="flex items-center gap-2 text-xs text-orange-700 dark:text-orange-300">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Préservation de toutes les données dans la base
                                    </li>
                                    <li class="flex items-center gap-2 text-xs text-orange-700 dark:text-orange-300">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Possibilité de réactivation ultérieure
                                    </li>
                                    <li class="flex items-center gap-2 text-xs text-orange-700 dark:text-orange-300">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Historique des actions conservé pour audit
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-4 p-4 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl dark:from-red-900/20 dark:to-pink-900/20 dark:border-red-800">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-red-900 dark:text-red-200">
                                Conséquences de l'annulation
                            </p>
                            <ul class="mt-2 space-y-1">
                                <li class="flex items-center gap-2 text-xs text-red-700 dark:text-red-300">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Les étudiants ne pourront plus consulter leurs résultats
                                </li>
                                <li class="flex items-center gap-2 text-xs text-red-700 dark:text-red-300">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Les exports seront temporairement indisponibles
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="motif-annulation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Motif d'annulation (optionnel)
                    </label>
                    <textarea id="motif-annulation"
                              wire:model="motifAnnulation"
                              rows="3"
                              class="block w-full px-4 py-3 text-sm border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                              placeholder="Indiquez la raison de cette annulation..."></textarea>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Ce motif sera conservé dans l'historique pour traçabilité
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3">
                <button wire:click="$set('confirmingAnnulation', false)"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="annulerResultats"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-red-600 to-orange-600 rounded-lg hover:from-red-700 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Confirmer l'annulation
                        <span wire:loading wire:target="annulerResultats">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Animations CSS personnalisées --}}
<style>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}

.animate-slideUp {
    animation: slideUp 0.3s ease-out;
}
</style>