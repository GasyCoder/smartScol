<div>
    <!-- En-tête fixe -->
    <div class="sticky top-0 z-10 px-5 py-4 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal -->
            <h5 class="text-xl font-medium text-slate-700 dark:text-white">Ajouter un étudiant</h5>

            <!-- Fil d'Ariane et Boutons de navigation -->
            <div class="flex items-center space-x-4">
                <!-- Fil d'Ariane -->
                <div class="items-center hidden text-sm sm:flex">
                    <span class="text-slate-600 dark:text-slate-400">
                        <span class="font-medium">{{ $niveau->nom }} ({{ $niveau->abr }})</span>
                        <span class="mx-2 text-slate-400">/</span>
                        <span class="font-medium">{{ $parcours->nom }} ({{ $parcours->abr }})</span>
                        <span class="mx-2 text-slate-400">/</span>
                        <span class="font-medium text-primary-600 dark:text-primary-400">Ajouter</span>
                    </span>
                </div>

                <!-- Bouton de retour -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('students', ['niveau' => $niveau_id, 'parcours' => $parcours_id, 'step' => 'etudiants']) }}" class="flex items-center text-sm text-primary-500 hover:text-primary-600 bg-primary-50 dark:bg-primary-900/20 dark:hover:bg-primary-900/30 py-1.5 px-3 rounded-md transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages flash/notification -->
    @if (session()->has('success'))
        <div class="px-5 pt-6">
            <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="px-5 pt-6">
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Contenu principal avec padding supérieur pour compenser l'en-tête fixe -->
    <div class="px-5 pt-6">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-950 dark:border-gray-800">
            <div class="p-6">
                <form wire:submit.prevent="save">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <!-- Matricule -->
                            <div class="sm:col-span-3">
                                <label for="matricule" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Matricule *</label>
                                <div class="mt-1">
                                    <input
                                        type="text"
                                        id="matricule"
                                        wire:model="matricule"
                                        class="block w-full rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white @error('matricule') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                                        placeholder="Ex: 1234-ABC"
                                    >
                                </div>
                                @error('matricule')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sexe -->
                            <div class="sm:col-span-3">
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Sexe *</label>
                                <div class="flex space-x-6">
                                    <div class="inline-flex items-center">
                                        <input
                                            type="radio"
                                            id="sexe_m"
                                            name="sexe"
                                            value="M"
                                            wire:model="sexe"
                                            class="w-6 h-6 transition-all duration-300 bg-white border-2 border-gray-300 rounded-full outline-none cursor-pointer peer dark:bg-gray-950 checked:bg-primary-600 checked:dark:bg-primary-600 checked:hover:bg-primary-600 checked:hover:dark:bg-primary-600 checked:focus:bg-primary-600 checked:focus:dark:bg-primary-600 focus:border-primary-600 focus:dark:border-primary-600 focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 focus:ring-0 focus:ring-offset-0 disabled:bg-slate-50 disabled:dark:bg-slate-900 disabled:checked:bg-primary-400 disabled:checked:dark:bg-primary-400 dark:border-gray-900 disabled:cursor-not-allowed"
                                        >
                                        <label
                                            for="sexe_m"
                                            class="text-slate-600 dark:text-slate-400 peer-disabled:text-slate-400 peer-disabled:dark:text-slate-700 text-sm leading-5 pt-0.5 ps-3 cursor-pointer inline-block"
                                        >
                                            Masculin (M)
                                        </label>
                                    </div>
                                    <div class="inline-flex items-center">
                                        <input
                                            type="radio"
                                            id="sexe_f"
                                            name="sexe"
                                            value="F"
                                            wire:model="sexe"
                                            class="w-6 h-6 transition-all duration-300 bg-white border-2 border-gray-300 rounded-full outline-none cursor-pointer peer dark:bg-gray-950 checked:bg-primary-600 checked:dark:bg-primary-600 checked:hover:bg-primary-600 checked:hover:dark:bg-primary-600 checked:focus:bg-primary-600 checked:focus:dark:bg-primary-600 focus:border-primary-600 focus:dark:border-primary-600 focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 focus:ring-0 focus:ring-offset-0 disabled:bg-slate-50 disabled:dark:bg-slate-900 disabled:checked:bg-primary-400 disabled:checked:dark:bg-primary-400 dark:border-gray-900 disabled:cursor-not-allowed"
                                        >
                                        <label
                                            for="sexe_f"
                                            class="text-slate-600 dark:text-slate-400 peer-disabled:text-slate-400 peer-disabled:dark:text-slate-700 text-sm leading-5 pt-0.5 ps-3 cursor-pointer inline-block"
                                        >
                                            Féminin (F)
                                        </label>
                                    </div>
                                </div>
                                @error('sexe')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nom -->
                            <div class="sm:col-span-3">
                                <label for="nom" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom *</label>
                                <div class="mt-1">
                                    <input
                                        type="text"
                                        id="nom"
                                        wire:model="nom"
                                        class="block w-full rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white @error('nom') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                                    >
                                </div>
                                @error('nom')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Prénom -->
                            <div class="sm:col-span-3">
                                <label for="prenom" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prénom</label>
                                <div class="mt-1">
                                    <input
                                        type="text"
                                        id="prenom"
                                        wire:model="prenom"
                                        class="block w-full rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white @error('prenom') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                                    >
                                </div>
                                @error('prenom')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date de naissance -->
                            <div class="sm:col-span-3">
                                <label for="date_naissance" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de naissance</label>
                                <div class="mt-1">
                                    <input
                                        type="text"
                                        id="date_naissance"
                                        wire:model="date_naissance"
                                        class="block w-full rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white @error('date_naissance') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                                        placeholder="JJ/MM/AAAA"
                                    >
                                </div>
                                @error('date_naissance')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format: jour/mois/année (ex: 12/02/1999)</p>
                            </div>

                            <!-- Statut (is_active) -->
                            <div class="sm:col-span-3">
                                <label for="is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Statut</label>
                                <div class="mt-1">
                                    <div class="inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            wire:model="is_active"
                                            id="is_active"
                                            class="peer relative h-6 w-12 bg-white dark:bg-gray-950 checked:bg-primary-600 checked:dark:bg-primary-600 checked:hover:bg-primary-600 checked:hover:dark:bg-primary-600 checked:focus:bg-primary-600 checked:focus:dark:bg-primary-600 focus:border-primary-600 focus:dark:border-primary-600 outline-none focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 focus:ring-0 focus:ring-offset-0 disabled:bg-slate-50 disabled:dark:bg-slate-900 disabled:checked:bg-primary-400 disabled:checked:dark:bg-primary-400 rounded-full transition-all border-2 border-gray-300 dark:border-gray-900 checked:bg-none after:absolute after:transition-all after:duration-300 after:h-4 after:w-4 after:rounded-full after:bg-gray-300 after:top-0.5 after:start-0.5 checked:after:bg-white checked:after:start-6.5 cursor-pointer disabled:cursor-not-allowed"
                                        >
                                        <label
                                            class="text-slate-600 dark:text-slate-400 peer-disabled:text-slate-400 peer-disabled:dark:text-slate-700 text-sm leading-5 pt-0.5 ps-3 cursor-pointer inline-block"
                                            for="is_active"
                                        >
                                            {{ $is_active ? 'Actif' : 'Inactif' }}
                                        </label>
                                    </div>
                                </div>
                                @error('is_active')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Niveau & Parcours (affichage en lecture seule) -->
                            <div class="sm:col-span-6">
                                <div class="p-4 mt-1 rounded-md bg-gray-50 dark:bg-gray-800">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Niveau:</span> {{ $niveau->nom }} ({{ $niveau->abr }})
                                    </p>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Parcours:</span> {{ $parcours->nom }} ({{ $parcours->abr }})
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Champs cachés pour les IDs -->
                        <input type="hidden" wire:model="niveau_id">
                        <input type="hidden" wire:model="parcours_id">

                        <!-- Boutons d'action -->
                        <div class="flex justify-end pt-5 space-x-3">
                            <a
                                href="{{ route('students', ['niveau' => $niveau_id, 'parcours' => $parcours_id, 'step' => 'etudiants']) }}"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                            >
                                Annuler
                            </a>
                            <button
                                type="submit"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-primary-700 dark:hover:bg-primary-800"
                            >
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
