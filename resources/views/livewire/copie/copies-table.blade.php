<!-- Tableau des copies - Design amélioré avec support des sessions -->
<div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">

    @include('livewire.copie.partials.table-header')

    <!-- Corps du tableau amélioré -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <!-- En-têtes avec tri interactif -->
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" wire:click="sortBy('code_anonymat')">
                        <div class="flex items-center">
                            Code Anonymat
                            @if($sortField === 'code_anonymat')
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" wire:click="sortBy('ec_id')">
                        <div class="flex items-center">
                            ECS
                            @if($sortField === 'ec_id')
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>
                    <!-- NOUVELLE COLONNE : Session -->
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" wire:click="sortBy('session_exam_id')">
                        <div class="flex items-center">
                            Session
                            @if($sortField === 'session_exam_id')
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" wire:click="sortBy('note')">
                        <div class="flex items-center">
                            Note
                            @if($sortField === 'note')
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" wire:click="sortBy('created_at')">
                        <div class="flex items-center">
                            Date de saisie
                            @if($sortField === 'created_at')
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($sortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700 dark:divide-gray-800">
                @forelse($copies as $copie)
                <tr class="transition-colors duration-150 ease-in-out hover:bg-gray-50 dark:hover:bg-gray-600">
                    <td class="px-6 py-3 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            @if(is_object($copie->codeAnonymat) && $copie->codeAnonymat->code_complet)
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                    {{ $copie->codeAnonymat->code_complet }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Code manquant
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $copie->ec->nom ?? 'Matière non spécifiée' }}
                        </div>
                        @if($copie->ec && $copie->ec->code)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $copie->ec->code }}
                            </div>
                        @endif
                    </td>
                    <!-- NOUVELLE COLONNE : Affichage de la session -->
                    <td class="px-6 py-3 whitespace-nowrap">
                        @if($copie->sessionExam)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $copie->sessionExam->type === 'Normale'
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                    : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
                                }}">
                                <span class="w-1.5 h-1.5 mr-1.5 rounded-full
                                    {{ $copie->sessionExam->type === 'Normale' ? 'bg-green-500' : 'bg-orange-500' }}"></span>
                                {{ $copie->sessionExam->type }}
                            </span>
                            @if($copie->sessionExam->date_debut)
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($copie->sessionExam->date_debut)->format('d/m/Y') }}
                                </div>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded-full dark:bg-gray-800 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Non définie
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap">
                        <div class="relative group">
                            <span class="px-2.5 py-1 rounded-md text-sm font-medium inline-flex items-center transition-all duration-150
                                @if($copie->note >= 10)
                                    bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @else
                                    bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @endif">
                                @if($copie->note >= 10)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @endif
                                {{ number_format($copie->note, 2) }} / 20
                            </span>
                            <!-- Tooltip avec plus d'informations -->
                            <div class="absolute z-10 invisible px-3 py-2 text-xs font-medium text-white transition-opacity transform -translate-x-1/2 bg-gray-900 rounded-lg shadow-sm opacity-0 group-hover:opacity-100 group-hover:visible -top-12 left-1/2 min-w-max">
                                <div class="text-center">
                                    @if($copie->note >= 16)
                                        <div class="font-bold text-green-300">Très bien</div>
                                    @elseif($copie->note >= 14)
                                        <div class="font-bold text-green-300">Bien</div>
                                    @elseif($copie->note >= 12)
                                        <div class="font-bold text-blue-300">Assez bien</div>
                                    @elseif($copie->note >= 10)
                                        <div class="font-bold text-yellow-300">Passable</div>
                                    @else
                                        <div class="font-bold text-red-300">Non validé</div>
                                    @endif
                                    <div class="mt-1 text-xs">{{ $copie->sessionExam->type ?? 'Session inconnue' }}</div>
                                </div>
                                <div class="absolute bottom-0 w-2 h-2 -mb-1 transform rotate-45 -translate-x-1/2 bg-gray-900 left-1/2"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-300">
                        <div class="flex flex-col">
                            <div class="flex items-center">
                                <i class="mr-1 text-xs text-gray-400 ni ni-calendar"></i>
                                {{ $copie->created_at->format('d/m/Y à H:i') }}
                            </div>
                            @if($copie->updated_at && $copie->updated_at->ne($copie->created_at))
                                <div class="flex items-center mt-1 text-amber-600 dark:text-amber-400">
                                    <i class="mr-1 text-xs ni ni-edit"></i>
                                    Modifié le {{ $copie->updated_at->format('d/m/Y à H:i') }}
                                </div>
                            @endif
                            <div class="flex items-center mt-1">
                                <i class="mr-1 text-xs text-gray-400 ni ni-user"></i>
                                {{ $copie->utilisateurSaisie->name ?? 'Utilisateur inconnu' }}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-3 text-sm font-medium text-right whitespace-nowrap">
                        <div class="flex items-center justify-end space-x-3">
                            <!-- Indicateur de session actuelle/différente -->
                            @if($copie->session_exam_id != $session_exam_id && $session_exam_id)
                                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium text-yellow-800 bg-yellow-100 rounded dark:bg-yellow-900 dark:text-yellow-200"
                                      title="Cette copie appartient à une autre session">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </span>
                            @endif

                            @can('copies.edit')
                            <button
                                wire:click="editCopie({{ $copie->id }})"
                                @if($copie->session_exam_id != $session_exam_id && $session_exam_id)
                                    disabled
                                    class="inline-flex items-center p-1.5 text-gray-400 rounded-md cursor-not-allowed opacity-50"
                                    title="Impossible de modifier : copie d'une autre session"
                                @else
                                    class="inline-flex items-center p-1.5 text-indigo-600 rounded-md hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30 transition-colors"
                                    title="Modifier cette copie"
                                @endif
                                >
                                <em class="text-lg icon ni ni-edit"></em>
                            </button>
                            @endcan

                            @can('copies.delete')
                            <button
                                wire:click="confirmDelete({{ $copie->id }})"
                                @if($copie->session_exam_id != $session_exam_id && $session_exam_id)
                                    disabled
                                    class="inline-flex items-center p-1.5 text-gray-400 rounded-md cursor-not-allowed opacity-50"
                                    title="Impossible de supprimer : copie d'une autre session"
                                @else
                                    class="inline-flex items-center p-1.5 text-red-600 rounded-md hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors"
                                    title="Supprimer cette copie"
                                @endif
                                >
                                <em class="text-lg icon ni ni-delete-fill"></em>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-sm text-center text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mb-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            @if(!$session_exam_id)
                                <p class="mb-4 text-lg font-medium">Aucune session d'examen sélectionnée</p>
                                <p class="max-w-md mb-6 text-sm text-center text-gray-500 dark:text-gray-400">
                                    Veuillez sélectionner une session d'examen active pour voir et saisir des notes.
                                </p>
                            @elseif($examen_id && $ec_id)
                                <p class="mb-4 text-lg font-medium">Aucune note saisie pour cette matière</p>
                                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                                    Session actuelle : <span class="font-medium">{{ $currentSessionType }}</span>
                                </div>
                                <p class="max-w-md mb-6 text-sm text-center text-gray-500 dark:text-gray-400">
                                    Vous pouvez commencer à saisir les notes des étudiants pour cette matière dans la session {{ $currentSessionType }}.
                                </p>
                                @can('copies.create')
                                <button
                                    wire:click="openCopieModal"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors rounded-md bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-primary-700 dark:hover:bg-primary-800">
                                   <em class="mr-2 text-lg icon ni ni-plus-circle"></em>
                                    Ajouter une note ({{ $currentSessionType }})
                                </button>
                                @endcan
                            @else
                                <p class="mb-4 text-lg font-medium">Aucune donnée à afficher</p>
                                <p class="max-w-md mb-6 text-sm text-center text-gray-500 dark:text-gray-400">
                                    Veuillez sélectionner un niveau, un parcours, une salle et une matière pour voir ou saisir des notes.
                                </p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination améliorée avec informations de session -->
    @if(method_exists($copies, 'hasPages') && $copies->hasPages())
    <div class="px-4 py-3 bg-white border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col items-center justify-between gap-3 sm:flex-row">
            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                Affichage de {{ $copies->firstItem() }} à {{ $copies->lastItem() }}
                sur {{ $copies->total() }} notes
                @if($currentSessionType)
                    <span class="ml-2 text-xs">(Session {{ $currentSessionType }})</span>
                @endif
            </div>

            <div class="inline-flex items-center">
                {{ $copies->links() }}
            </div>

            <div class="flex items-center">
                <label for="perPage" class="mr-2 text-sm text-gray-500 dark:text-gray-400">Par page:</label>
                <select wire:model.live="perPage" id="perPage"
                        class="py-1 pl-2 pr-8 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>
    @endif

</div>