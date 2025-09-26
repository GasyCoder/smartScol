<div>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <!-- En-tête -->
        <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Relevés de Notes
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Générer les relevés de notes des étudiants
                    </p>
                </div>
            </div>
        </div>
        @include('livewire.resultats.partials.releve-compteur')
        <!-- Filtres -->
        <div class="p-6 bg-gray-50 dark:bg-gray-900">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Année Universitaire -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Année Universitaire
                    </label>
                    <select wire:model.live="selectedAnneeUniv" 
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Toutes</option>
                        @foreach($anneesUniv as $annee)
                            <option value="{{ $annee->id }}">{{ $annee->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Session -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Session
                    </label>
                    <select wire:model.live="selectedSession" 
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Sélectionner</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->type }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Niveau -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Niveau
                    </label>
                    <select wire:model.live="selectedNiveau" 
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Tous</option>
                        @foreach($niveaux as $niveau)
                            <option value="{{ $niveau->id }}">{{ $niveau->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Parcours -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Parcours
                    </label>
                    <select wire:model.live="selectedParcours" 
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                            @if($parcours->isEmpty()) disabled @endif>
                        <option value="">Tous</option>
                        @foreach($parcours as $parcour)
                            <option value="{{ $parcour->id }}">{{ $parcour->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Recherche -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Recherche
                    </label>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Nom, prénom, matricule..."
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Messages -->
        @if (session()->has('error'))
            <div class="p-4 bg-red-50 border-l-4 border-red-400">
                <p class="text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        @if (session()->has('success'))
            <div class="p-4 bg-green-50 border-l-4 border-green-400">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Liste des étudiants -->
        <div class="overflow-hidden">
            @if($etudiants->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Étudiant
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Niveau
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Parcours
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach($etudiants as $etudiant)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-indigo-600 dark:text-indigo-300">
                                                        {{ substr($etudiant->prenom, 0, 1) }}{{ substr($etudiant->nom, 0, 1) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $etudiant->nom }} {{ $etudiant->prenom }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $etudiant->matricule }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $etudiant->niveau?->nom ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $etudiant->parcours?->nom ?? 'Aucun' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            @can('releve-note.view-detail')
                                            <button wire:click="voirReleve({{ $etudiant->id }})"
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    @if(!$selectedSession) disabled @endif>
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Voir
                                            </button>
                                            @endcan
                                            @can('releve-note.exportPDF')
                                            <button wire:click="genererPDF({{ $etudiant->id }})"
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                    @if(!$selectedSession) disabled @endif>
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                PDF
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-600">
                    {{ $etudiants->links() }}
                </div>
            @else
                <div class="p-6 text-center">
                   <em class="text-2xl ni ni-users"></em>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun étudiant trouvé</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Ajustez vos filtres pour voir les étudiants disponibles.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Loading indicator -->
    <div wire:loading.flex class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-900 dark:text-white">Génération en cours...</span>
            </div>
        </div>
    </div>
</div>