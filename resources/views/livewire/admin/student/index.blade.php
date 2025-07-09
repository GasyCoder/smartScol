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
        {{-- Étape 1: Sélection du niveau --}}
        @if($step === 'niveau')
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Choisir le niveau d'étude</h2>
                <p class="text-gray-600 dark:text-gray-400">Commencez par sélectionner un niveau d'études</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 max-w-4xl mx-auto">
                @forelse($niveaux as $niveau)
                    <button wire:click="$set('niveauId', {{ $niveau->id }})" 
                            class="group p-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center group-hover:bg-blue-700 transition-colors">
                                <span class="text-lg font-bold text-white">{{ substr($niveau->abr ?: $niveau->nom, 0, 2) }}</span>
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $niveau->nom }}</h3>
                                @if($niveau->abr)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $niveau->abr }}</p>
                                @endif
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.168 18.477 18.582 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aucun niveau disponible</h3>
                        <p class="text-gray-600 dark:text-gray-400">Contactez l'administrateur pour configurer les niveaux.</p>
                    </div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- Étape 2: Sélection du parcours --}}
        @if($step === 'parcours')
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-8">
                <div class="text-center flex-1">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Choisir le Parcours</h2>
                    <p class="text-gray-600 dark:text-gray-400">Niveau: <span class="font-semibold">{{ $niveauInfo['nom'] }}</span></p>
                </div>
                
                {{-- Bouton retour au niveau --}}
                <button wire:click="$set('step', 'niveau')" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors"
                        title="Retour à la sélection du niveau">
                    <em class="ni ni-bold-left mr-2"></em>
                    Changer de niveau
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 max-w-4xl mx-auto">
                @forelse($parcours as $parcour)
                    <button wire:click="$set('parcoursId', {{ $parcour->id }})" 
                            class="group p-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all duration-200">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center group-hover:bg-green-700 transition-colors">
                                <span class="text-lg font-bold text-white">{{ substr($parcour->abr ?: $parcour->nom, 0, 2) }}</span>
                            </div>
                            <div class="text-left">
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $parcour->nom }}</h3>
                                @if($parcour->abr)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $parcour->abr }}</p>
                                @endif
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aucun parcours disponible</h3>
                        <p class="text-gray-600 dark:text-gray-400">Aucun parcours configuré pour ce niveau.</p>
                    </div>
                @endforelse
            </div>
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
