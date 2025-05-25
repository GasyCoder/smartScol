<div class="relative">
    <!-- En-tête fixe -->
    <div class="sticky top-0 z-10 px-5 py-4 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal -->
            <h5 class="text-xl font-medium text-slate-700 dark:text-white">Gestion des étudiants</h5>

            <!-- Fil d'Ariane et Boutons de navigation -->
            <div class="flex items-center space-x-4">
                <!-- Fil d'Ariane -->
                <div class="items-center hidden text-sm sm:flex">
                    <span class="text-slate-600 dark:text-slate-400">
                        @if($niveauInfo)
                            <span class="font-medium">{{ $niveauInfo['nom'] }} ({{ $niveauInfo['abr'] }})</span>
                        @endif

                        @if($parcoursInfo)
                            <span class="mx-2 text-slate-400">/</span>
                            <span class="font-medium">{{ $parcoursInfo['nom'] }} ({{ $parcoursInfo['abr'] }})</span>
                        @endif

                        @if($step === 'etudiants')
                            <span class="mx-2 text-slate-400">/</span>
                            <span class="font-medium text-primary-600 dark:text-primary-400">Étudiants</span>
                        @endif
                    </span>
                </div>

                <!-- Boutons de navigation -->
                <div class="flex items-center space-x-2">
                    @if($step === 'parcours')
                    <button wire:click="retourANiveau" type="button" class="flex items-center text-sm text-primary-500 hover:text-primary-600 bg-primary-50 dark:bg-primary-900/20 dark:hover:bg-primary-900/30 py-1.5 px-3 rounded-md transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Retour au niveau
                    </button>
                    @endif

                    @if($step === 'etudiants')
                    <button wire:click="retourAParcours" type="button" class="flex items-center text-sm text-primary-500 hover:text-primary-600 bg-primary-50 dark:bg-primary-900/20 dark:hover:bg-primary-900/30 py-1.5 px-3 rounded-md transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Retour au parcours
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal avec padding supérieur pour compenser l'en-tête fixe -->
    <div class="px-5 pt-6">
        <!-- Section Niveau - Visible uniquement à l'étape niveau -->
        @if($step === 'niveau')
        <div class="[&:not(:last-child)]:pb-7 lg:[&:not(:last-child)]:pb-14">
            <div id="niveau-section" class="pb-5">
                <h5 class="mb-2 text-lg font-medium -tracking-snug text-slate-700 dark:text-white leading-tighter">Choisir le niveau d'étude</h5>
                <p class="mb-5 text-sm leading-6 text-slate-400">
                    Veuillez sélectionner le niveau d'étude pour lequel vous souhaitez gérer les étudiants.
                </p>
            </div>

            <div class="bg-white border border-gray-300 rounded-lg shadow-sm dark:bg-gray-950 dark:border-gray-900">
                <div class="p-5">
                    <div class="flex justify-center">
                        <div class="w-full max-w-md">
                            <div class="relative mb-5 last:mb-0">
                                <label class="inline-block mb-2 text-sm font-medium text-slate-700 dark:text-white" for="niveau-select">
                                    Niveau d'étude
                                </label>
                                <div class="relative">
                                    <select id="niveau-select" wire:model.live="niveauId" class="js-select block w-full text-sm leading-4.5 pe-10 ps-4 py-1.5 h-10 text-slate-700 dark:text-white placeholder-slate-300 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 outline-none focus:border-primary-500 focus:dark:border-primary-600 focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 disabled:bg-slate-50 disabled:dark:bg-slate-950 disabled:cursor-not-allowed rounded-md transition-all" data-search="true">
                                        <option value="">Sélectionnez un niveau</option>
                                        @foreach($niveaux as $niveau)
                                            <option value="{{ $niveau->id }}">{{ $niveau->nom }} ({{ $niveau->abr }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- card -->
        </div>
        @endif

        <!-- Section Parcours - Visible uniquement à l'étape parcours -->
        @if($step === 'parcours' && $niveauInfo)
        <div class="[&:not(:last-child)]:pb-7 lg:[&:not(:last-child)]:pb-14">
            <div id="parcours-section" class="pb-5">
                <h5 class="mb-2 text-lg font-medium -tracking-snug text-slate-700 dark:text-white leading-tighter">Choisir le parcours</h5>
                <p class="mb-5 text-sm leading-6 text-slate-400">
                    Veuillez sélectionner le parcours pour lequel vous souhaitez gérer les étudiants.
                </p>
            </div>

            <div class="bg-white border border-gray-300 rounded-lg shadow-sm dark:bg-gray-950 dark:border-gray-900">
                <div class="p-5">
                    <div class="flex justify-center">
                        <div class="w-full max-w-md">
                            @if(count($parcours) > 0)
                                <div class="relative mb-5 last:mb-0">
                                    <label class="inline-block mb-2 text-sm font-medium text-slate-700 dark:text-white" for="parcours-select">
                                        Parcours disponibles
                                    </label>
                                    <div class="relative">
                                        <select id="parcours-select" wire:model.live="parcoursId" class="js-select block w-full text-sm leading-4.5 pe-10 ps-4 py-1.5 h-10 text-slate-700 dark:text-white placeholder-slate-300 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 outline-none focus:border-primary-500 focus:dark:border-primary-600 focus:outline-offset-0 focus:outline-primary-200 focus:dark:outline-primary-950 disabled:bg-slate-50 disabled:dark:bg-slate-950 disabled:cursor-not-allowed rounded-md transition-all" data-search="true">
                                            <option value="">Sélectionnez un parcours</option>
                                            @foreach($parcours as $parcour)
                                                <option value="{{ $parcour->id }}">{{ $parcour->nom }} ({{ $parcour->abr }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                <div class="p-4 text-center rounded-md text-slate-500 bg-slate-50 dark:bg-gray-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto mb-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p>Aucun parcours disponible pour ce niveau d'étude.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div><!-- card -->
        </div>
        @endif

        <!-- Section Étudiants - Visible uniquement à l'étape étudiants -->
        @if($step === 'etudiants' && $niveauInfo && $parcoursInfo)
        <div>
            <div class="flex flex-col mb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="mt-1 text-sm font-bold leading-6 text-slate-400">
                        Liste des étudiants pour {{ $niveauInfo['nom'] }} - {{ $parcoursInfo['nom'] }} -
                        <span class="text-primary-600 dark:text-primary-400">{{ $this->getEtudiantsCount() }} étudiant(s)</span>
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 mt-4 sm:mt-0">
                    <div class="relative">
                        <input type="file" wire:model="importFile" id="importFile" class="sr-only" accept=".csv,.txt,.xlsx,.xls" />
                        <button onclick="document.getElementById('importFile').click()" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Importer
                        </button>
                        @if($importFile)
                        <button wire:click="importEtudiants" class="ml-1 inline-flex items-center py-1.5 px-3 text-sm font-medium rounded bg-primary-600 text-white hover:bg-primary-700 focus:outline-none dark:bg-primary-700 dark:hover:bg-primary-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Confirmer
                        </button>
                        @endif
                    </div>
                    @role('superadmin')
                    <button wire:click="downloadTemplate" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Télécharger le modèle
                    </button>
                    @endrole
                    <button wire:click="exportEtudiants" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Exporter
                    </button>
                    <a href="{{ route('add_etudiant', ['niveau' => $niveauInfo['id'], 'parcour' => $parcoursInfo['id']]) }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded bg-primary-600 text-white hover:bg-primary-700 focus:outline-none dark:bg-primary-700 dark:hover:bg-primary-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajouter
                    </a>
                </div>
            </div>

            <!-- Barre de recherche -->
            <div class="mb-4">
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" class="block w-full py-2 pl-10 pr-4 text-sm bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-800 dark:text-white focus:border-primary-500 focus:ring-0 dark:focus:border-primary-600" placeholder="Rechercher un étudiant...">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Tableau des étudiants -->
            <div class="overflow-x-auto bg-white border border-gray-300 rounded-lg shadow-sm dark:bg-gray-950 dark:border-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900">
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400" wire:click="sortBy('matricule')">
                                <div class="flex items-center space-x-1">
                                    <span>Matricule</span>
                                    @if($sortField === 'matricule')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            @if($sortDirection === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400" wire:click="sortBy('nom')">
                                <div class="flex items-center space-x-1">
                                    <span>Nom</span>
                                    @if($sortField === 'nom')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            @if($sortDirection === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400" wire:click="sortBy('prenom')">
                                <div class="flex items-center space-x-1">
                                    <span>Prénom</span>
                                    @if($sortField === 'prenom')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            @if($sortDirection === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                Sexe
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                Date de naissance
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                               Actif
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-950 dark:divide-gray-800">
                        @forelse($etudiants as $etudiant)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $etudiant->matricule }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap dark:text-gray-300">
                                    {{ $etudiant->nom }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap dark:text-gray-300">
                                    {{ $etudiant->prenom }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap dark:text-gray-300">
                                    {{ $etudiant->sexe }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap dark:text-gray-300">
                                    {{ $etudiant->date_naissance ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full {{ $etudiant->is_active ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                        {{ $etudiant->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('edit_etudiant', ['etudiant' => $etudiant->id]) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <button wire:click="confirmDelete({{ $etudiant->id }})"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <p class="mt-2 text-sm font-medium">Aucun étudiant trouvé</p>
                                    <p class="mt-1 text-sm">Ajoutez un nouvel étudiant pour ce parcours.</p>
                                    <a href="{{ route('add_etudiant', ['niveau' => $niveauInfo['id'], 'parcour' => $parcoursInfo['id']]) }}" class="inline-flex items-center px-3 py-2 mt-4 text-sm font-medium text-white rounded-md bg-primary-600 hover:bg-primary-700 focus:outline-none dark:bg-primary-700 dark:hover:bg-primary-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Ajouter un étudiant
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-4 py-3 bg-white border-t border-gray-200 dark:bg-gray-950 dark:border-gray-800 sm:px-6">
                    {{ $etudiants->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de suppression</h3>
            <p class="mb-6 text-gray-700 dark:text-gray-300">Êtes-vous sûr de vouloir supprimer cette étudiant ? Cette action est irréversible.</p>
            <div class="flex justify-end space-x-3">
                <button wire:click="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="deleteEtudiant" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
