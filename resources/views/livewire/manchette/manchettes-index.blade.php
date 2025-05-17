<div class="container px-4 py-6 mx-auto">
    <!-- En-tête fixe avec titre et actions globales -->
    <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <!-- Titre principal -->
            <h2 class="text-xl font-medium text-slate-700 dark:text-white">Saisie des Manchettes</h2>

            <!-- Actions globales -->
            <div class="flex items-center space-x-2">
                @if($examen_id)
                <button
                    wire:click="openManchetteModal"
                    class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded bg-primary-600 text-white hover:bg-primary-700 focus:outline-none dark:bg-primary-700 dark:hover:bg-primary-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Ajouter une manchette
                </button>
                @endif
                <a href="{{ route('manchettes.corbeille') }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Corbeille
                </a>
                <button wire:click="resetFiltres" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Réinitialiser
                </button>
            </div>
        </div>
    </div>

    <!-- Messages d'état -->
    @if($message)
    <div class="mb-4">
        <div class="{{ $messageType === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700' }} px-4 py-3 rounded relative border-l-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    </div>
    @endif

    <!-- Barre de filtres et contexte actuel -->
    <div class="mb-6">
        <!-- Filtres actuels / Fil d'Ariane -->
        @if($niveau_id || $parcours_id || $salle_id)
        <div class="flex flex-wrap items-center gap-2 p-3 mb-4 rounded-lg bg-gray-50 dark:bg-gray-800">
            <span class="text-sm text-gray-500 dark:text-gray-400">Filtres actifs:</span>

            @if($niveau_id)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                Niveau: {{ $niveaux->where('id', $niveau_id)->first()->nom ?? '' }}
            </span>
            @endif

            @if($parcours_id)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                Parcours: {{ $parcours->where('id', $parcours_id)->first()->nom ?? '' }}
            </span>
            @endif

            @if($salle_id)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                Salle:
                @foreach($salles as $salle)
                    @if($salle->id == $salle_id)
                        {{ $salle->nom }} ({{ $salle->code_base ?? '' }})
                    @endif
                @endforeach
            </span>
            @endif

            @if($examen_id)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                Examen: {{ App\Models\Examen::find($examen_id)->session->type ?? 'Inconnu' }}
            </span>
            @endif

            @if($ec_id)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                Matière: {{ DB::table('ecs')->where('id', $ec_id)->first()->nom ?? '' }}
            </span>
            @endif

        </div>
        @endif

        <!-- Filtres de sélection -->
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm dark:bg-gray-800 sm:rounded-lg dark:border-gray-700">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-900 dark:text-white">Filtres de sélection</h3>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Niveau -->
                    <div>
                        <label for="niveau_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Niveau</label>
                        <select id="niveau_id" wire:model.live="niveau_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Sélectionner</option>
                            @foreach($niveaux as $niveau)
                                <option value="{{ $niveau->id }}">{{ $niveau->abr }} - {{ $niveau->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Parcours -->
                    <div>
                        <label for="parcours_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parcours</label>
                        <select id="parcours_id" wire:model.live="parcours_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" {{ count($parcours) ? '' : 'disabled' }}>
                            <option value="">Sélectionner</option>
                            @foreach($parcours as $parcour)
                                <option value="{{ $parcour->id }}">{{ $parcour->abr }} - {{ $parcour->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Matière (EC) -->
                    <div>
                        <label for="ec_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Matière</label>
                        <select id="ec_id" wire:model.live="ec_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" {{ count($ecs) ? '' : 'disabled' }}>
                            <option value="">Sélectionner</option>
                            @foreach($ecs as $ec)
                                <option value="{{ $ec->id }}">{{ $ec->abr ?? '' }} - {{ $ec->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Salle d'examen -->
                    <div>
                        <label for="salle_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Salle</label>
                        <select id="salle_id" wire:model.live="salle_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" {{ count($salles) ? '' : 'disabled' }}>
                            <option value="">Sélectionner</option>
                            @foreach($salles as $salle)
                                <option value="{{ $salle->id }}">{{ $salle->nom }} ({{ $salle->code_base }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des manchettes -->
    <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
            <div class="px-4 py-3 sm:px-6">
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                    <h3 class="text-base font-medium leading-6 text-gray-900 dark:text-white">
                        @if($examen_id)
                            Manchettes pour l'examen {{ App\Models\Examen::find($examen_id)->session->type ?? 'Inconnu' }}
                            @if($salle_id)
                                - Salle: {{ DB::table('salles')->where('id', $salle_id)->first()->nom ?? 'Inconnue' }}
                            @endif
                        @else
                            Manchettes d'examen
                        @endif
                    </h3>

                    <div class="flex items-center flex-1 max-w-md ml-auto space-x-2">
                        <!-- Barre de recherche -->
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input wire:model.live="search" type="text" class="block w-full py-2 pl-10 pr-3 leading-5 placeholder-gray-500 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Rechercher un code ou matricule...">
                        </div>

                        <!-- Compteur de manchettes -->
                        <div class="text-sm text-gray-500 font-bold dark:text-gray-400 whitespace-nowrap flex gap-3">
                            <span class="bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 px-3 py-1 rounded-full">
                                {{ $manchettes->total() ?? 0 }} total
                            </span>
                            <span class="bg-secondary-100 text-secondary-800 dark:bg-secondary-900 dark:text-secondary-200 px-3 py-1 rounded-full">
                                {{ $userManchettesCount ?? 0 }} par vous
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Code Anonymat
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Étudiant
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Date de saisie/modification
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-300">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700 dark:divide-gray-800">
                    @forelse($manchettes as $manchette)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                @if(is_object($manchette->codeAnonymat) && method_exists($manchette->codeAnonymat, 'getAttribute') && $manchette->codeAnonymat->code_complet)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                        {{ $manchette->codeAnonymat->code_complet }}
                                    </span>
                                @elseif($manchette->code_anonymat_complet)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                        {{ $manchette->code_anonymat_complet }}
                                    </span>
                                @else
                                    <span class="text-red-500 dark:text-red-400">Code manquant</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $manchette->etudiant->nom ?? '' }} {{ $manchette->etudiant->prenom ?? '' }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Matricule: <b>{{ $manchette->etudiant->matricule ?? 'Inconnu' }}</b>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                            <div>Créée: {{ $manchette->created_at->format('d/m/Y à H:i') }}</div>
                            @if($manchette->updated_at && $manchette->updated_at->ne($manchette->created_at))
                                <div class="text-sm text-amber-600 dark:text-amber-400">Modifiée: {{ $manchette->updated_at->format('d/m/Y à H:i') }}</div>
                            @endif
                            <div class="text-sm">Par: {{ $manchette->utilisateurSaisie->name ?? 'Inconnu' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                @can('manchettes.edit')
                                <button wire:click="editManchette({{ $manchette->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Modifier">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                @endcan

                                @can('manchettes.delete')
                                <button wire:click="confirmDelete({{ $manchette->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Supprimer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-sm text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                @if($examen_id)
                                    <p class="mt-1">Aucune manchette saisie pour cet examen.</p>
                                    @can('manchettes.create')
                                    <button
                                        wire:click="openManchetteModal"
                                        class="inline-flex items-center px-2.5 py-1.5 mt-3 border border-transparent text-xs font-medium rounded bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        Ajouter une manchette
                                    </button>
                                    @endcan
                                @else
                                    <p class="mt-1">Veuillez sélectionner un niveau, un parcours et une salle pour voir ou saisir des manchettes.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(method_exists($manchettes, 'hasPages') && $manchettes->hasPages())
        <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6 dark:bg-gray-800 dark:border-gray-700">
            {{ $manchettes->links() }}
        </div>
        @endif
    </div>

    <!-- Modale de saisie de manchette -->
    @if($showManchetteModal)
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
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                                {{ isset($editingManchetteId) ? 'Modifier une manchette' : 'Saisir une manchette' }}
                                @if($selectedSalleCode)
                                    <span class="ml-1 text-sm text-gray-600 dark:text-gray-400">(Salle: {{ $selectedSalleCode }})</span>
                                @endif
                            </h3>
                            <div class="mt-4">
                                <form wire:submit.prevent="saveManchette">
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
                                            @error('code_anonymat') <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                        <!-- Recherche étudiant -->
                                        <div>
                                            <!-- Mode de recherche (matricule/nom) -->
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Rechercher par
                                                </label>
                                            <div class="flex space-x-4 mt-1">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" wire:model.live="searchMode" value="matricule" class="text-primary-600 border-gray-300 focus:ring-primary-500">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Matricule</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" wire:model.live="searchMode" value="nom" class="text-primary-600 border-gray-300 focus:ring-primary-500">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Nom/Prénom</span>
                                                </label>
                                            </div>
                                            <div class="mt-1 relative">
                                                <input type="text"
                                                    wire:model.live="searchQuery"
                                                    id="searchQuery"
                                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                    placeholder="{{ $searchMode === 'matricule' ? 'Saisir un matricule' : 'Saisir un nom' }}"
                                                    autocomplete="off">
                                                <!-- Petit indicateur de mode -->
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    Mode actif : {{ $searchMode === 'matricule' ? 'Recherche par matricule' : 'Recherche par nom/prénom' }}
                                                </div>    
                                                @if($searchQuery)
                                                <button type="button" 
                                                    wire:click="$set('searchQuery', '')" 
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                                @endif
                                                
                                                @error('etudiant_id') <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Résultats de recherche - corrigé -->
                                            @if($searchQuery && strlen($searchQuery) >= 2)
                                                <div class="mt-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm max-h-48 overflow-y-auto">
                                                    @if(count($searchResults) > 0)
                                                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                                            @foreach($searchResults as $etudiant)
                                                            <li class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer" wire:click="selectEtudiant({{ $etudiant->id }})">
                                                                <div class="flex justify-between">
                                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $etudiant->nom }} {{ $etudiant->prenom }}</div>
                                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $etudiant->matricule }}</div>
                                                                </div>
                                                            </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                                            Aucun étudiant trouvé
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <!-- Étudiant sélectionné - section séparée pour plus de clarté -->
                                        @if($etudiant_id && $matricule)
                                        <div class="p-3 mt-3 bg-gray-50 rounded-md border border-gray-200 dark:bg-gray-700 dark:border-gray-600">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        Étudiant sélectionné:
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-300">
                                                        {{ App\Models\Etudiant::find($etudiant_id)->nom ?? '' }} {{ App\Models\Etudiant::find($etudiant_id)->prenom ?? '' }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        Matricule: {{ $matricule }}
                                                    </div>
                                                </div>
                                                <button type="button" wire:click="$set('etudiant_id', null)" class="text-red-600 hover:text-red-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                        <button type="submit" class="inline-flex items-center justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm 
                                            {{ isset($editingManchetteId) 
                                                ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-800' 
                                                : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 dark:bg-primary-700 dark:hover:bg-primary-800' 
                                            }} 
                                            focus:outline-none focus:ring-2 focus:ring-offset-2 sm:col-start-2 sm:text-sm">
                                            
                                            @if(isset($editingManchetteId))
                                                <!-- NioIcon pour mise à jour -->
                                                <em class="ni ni-update mr-2"></em>
                                                Mettre à jour
                                            @else
                                                <!-- NioIcon pour sauvegarde -->
                                                <em class="ni ni-save mr-2"></em>
                                                Enregistrer
                                            @endif
                                        </button>
                                        
                                        <button type="button" wire:click="$set('showManchetteModal', false)" class="inline-flex items-center justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                                            <!-- NioIcon pour annulation -->
                                            <em class="ni ni-cross mr-2"></em>
                                            Annuler
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

    <!-- Modal de confirmation de suppression -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de suppression</h3>
            <p class="mb-6 text-gray-700 dark:text-gray-300">
                Êtes-vous sûr de vouloir supprimer cette manchette
                @if($manchetteToDelete)
                (Code: {{ $manchetteToDelete->codeAnonymat->code_complet ?? 'N/A' }}, 
                Étudiant: {{ $manchetteToDelete->etudiant->matricule ?? 'N/A' }})
                @endif
                ? Cette action est réversible (via la corbeille).
            </p>
            <div class="flex justify-end space-x-3">
                <button wire:click="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button wire:click="deleteManchette" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
    @endif
</div>