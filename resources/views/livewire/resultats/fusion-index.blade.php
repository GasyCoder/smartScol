<div>
    <div class="container px-4 py-6 mx-auto">
        <!-- En-tête avec titre et actions principales -->
        <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-medium text-slate-700 dark:text-white">Fusion des Notes</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Générateur de résultats à partir des manchettes et copies</p>
                </div>

                <div class="flex items-center space-x-2">
                    <a href="{{ route('resultats.provisoires') }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                        <em class="icon ni ni-list-check mr-1.5"></em>
                        Voir les Résultats
                    </a>
                </div>
            </div>
        </div>

        <!-- Visualisation du processus -->
        <div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-sm dark:bg-gray-800 sm:rounded-lg dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Processus de gestion des résultats
                </h3>
            </div>
            <div class="p-4">
                <div class="relative">
                    <!-- Ligne de progression -->
                    <div class="absolute top-5 w-full h-0.5 bg-gray-200 dark:bg-gray-700"></div>

                    <!-- Étapes du processus -->
                    <div class="relative flex justify-between">
                        <div class="text-center">
                            <div class="flex items-center justify-center w-10 h-10 mx-auto mb-2 text-white bg-green-500 rounded-full dark:bg-green-600">
                                <em class="icon ni ni-cards"></em>
                            </div>
                            <div class="text-xs font-medium text-green-500 dark:text-green-400">Manchettes</div>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center w-10 h-10 mx-auto mb-2 text-white bg-green-500 rounded-full dark:bg-green-600">
                                <em class="icon ni ni-files"></em>
                            </div>
                            <div class="text-xs font-medium text-green-500 dark:text-green-400">Copies</div>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center w-10 h-10 mx-auto mb-2 text-white bg-blue-500 rounded-full dark:bg-blue-600">
                               <em class="icon ni ni-exchange"></em>
                            </div>
                            <div class="text-xs font-medium text-blue-500 dark:text-blue-400">Fusion</div>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center w-10 h-10 mx-auto mb-2 text-gray-600 bg-gray-300 rounded-full dark:bg-gray-700 dark:text-gray-400">
                                <em class="icon ni ni-check-circle"></em>
                            </div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Vérification</div>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center w-10 h-10 mx-auto mb-2 text-gray-600 bg-gray-300 rounded-full dark:bg-gray-700 dark:text-gray-400">
                                <em class="icon ni ni-check-thick"></em>
                            </div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Validation</div>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center w-10 h-10 mx-auto mb-2 text-gray-600 bg-gray-300 rounded-full dark:bg-gray-700 dark:text-gray-400">
                                <em class="icon ni ni-upload"></em>
                            </div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Publication</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages d'alerte -->
        @if($message)
        <div class="mb-6">
            <div class="{{ $messageType === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700' }} px-4 py-3 rounded relative border-l-4" role="alert">
                <span class="block sm:inline">{{ $message }}</span>
            </div>
        </div>
        @endif

        <!-- Session active (afficher automatiquement, pas de sélection) -->
        @if($sessionActive)
        <div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-sm dark:bg-gray-800 sm:rounded-lg dark:border-gray-700">
            <div class="p-4 border-b border-blue-100 bg-blue-50 dark:bg-blue-900 dark:border-blue-800">
                <h3 class="text-base font-medium text-blue-800 dark:text-blue-100">Session active</h3>
                <p class="mt-1 text-sm text-blue-600 dark:text-blue-300">
                    {{ $sessionActive->type }} - Année Universitaire {{ $sessionActive->anneeUniversitaire->date_start->format('Y') }}/{{ $sessionActive->anneeUniversitaire->date_end->format('Y') }}
                </p>
            </div>

            <div class="p-4 sm:p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Niveau -->
                    <div>
                        <label for="niveau_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Niveau d'études</label>
                        <select id="niveau_id" wire:model.live="niveau_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Sélectionner un niveau</option>
                            @foreach($niveaux as $niveau)
                                <option value="{{ $niveau->id }}">{{ $niveau->abr }} - {{ $niveau->nom }}</option>
                            @endforeach
                        </select>
                        @if($niveau_id)
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Niveau sélectionné : {{ $niveaux->firstWhere('id', $niveau_id)->nom }}
                            </div>
                        @endif
                    </div>

                    <!-- Parcours -->
                    <div>
                        <label for="parcours_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parcours</label>
                        <select id="parcours_id" wire:model.live="parcours_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" {{ count($parcours) ? '' : 'disabled' }}>
                            <option value="">Sélectionner un parcours</option>
                            @foreach($parcours as $parcour)
                                <option value="{{ $parcour->id }}">{{ $parcour->abr }} - {{ $parcour->nom }}</option>
                            @endforeach
                        </select>
                        @if($parcours_id && count($parcours))
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Parcours sélectionné : {{ $parcours->firstWhere('id', $parcours_id)->nom }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Information sur l'examen automatiquement sélectionné (si niveau et parcours sont choisis) -->
                @if($niveau_id && $parcours_id && $examen)
                <div class="p-3 mt-4 text-sm border border-green-100 rounded-md bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                    <div class="flex items-start">
                        <em class="icon ni ni-info text-green-400 mt-0.5 flex-shrink-0"></em>
                        <div class="ml-3">
                            <p class="text-green-700 dark:text-green-300">
                                <span class="font-medium">Examen sélectionné:</span> {{ $examen->session->type }} - {{ $examen->session->anneeUniversitaire->date_start->format('Y') }}/{{ $examen->session->anneeUniversitaire->date_end->format('Y') }}
                            </p>
                        </div>
                    </div>
                </div>
                @elseif($niveau_id && $parcours_id && !$examen)
                <div class="p-3 mt-4 text-sm border border-red-100 rounded-md bg-red-50 dark:bg-red-900/20 dark:border-red-800">
                    <div class="flex items-start">
                        <em class="icon ni ni-alert-circle text-red-400 mt-0.5 flex-shrink-0"></em>
                        <div class="ml-3">
                            <p class="text-red-700 dark:text-red-300">
                                <span class="font-medium">Attention:</span> Aucun examen n'est configuré pour ce niveau/parcours dans la session active. Veuillez contacter l'administrateur.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="p-4 mb-6 border border-red-200 rounded-md bg-red-50 dark:bg-red-900/30 dark:border-red-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <em class="text-red-400 icon ni ni-cross-circle"></em>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Aucune session active</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <p>Veuillez configurer une session active dans les paramètres du système.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Actions pour la fusion (uniquement si examen sélectionné) -->
        @if($examen_id)
        <div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-sm dark:bg-gray-800 sm:rounded-lg dark:border-gray-700">
            <div class="p-4 border-b border-gray-100 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                <h3 class="flex items-center text-base font-medium text-gray-900 dark:text-white">
                    <span class="flex items-center justify-center w-6 h-6 mr-2 text-xs text-white bg-blue-500 rounded-full">1</span>
                    Vérification et préparation
                </h3>
            </div>

            <div class="p-4 sm:p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="p-4 border rounded-lg bg-blue-50 dark:bg-gray-700 dark:border-gray-600">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <em class="text-2xl text-blue-500 icon ni ni-analyze"></em>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">Vérification de cohérence</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Vérifiez la cohérence entre les manchettes et les copies avant de procéder à la fusion.
                                </p>
                                <div class="mt-3">
                                    <button
                                        wire:click="verifierCoherence"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        <em class="mr-2 -ml-1 icon ni ni-check-circle"></em>
                                        Vérifier la cohérence
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border rounded-lg bg-green-50 dark:bg-gray-700 dark:border-gray-600">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <em class="text-2xl text-green-500 icon ni ni-puzzle"></em>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">Fusion des données</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Fusionnez les manchettes et les copies pour générer les résultats provisoires.
                                </p>
                                <div class="mt-3">
                                    <button
                                        wire:click="confirmerFusion"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    >
                                        <em class="mr-2 -ml-1 icon ni ni-cards-shuffle"></em>
                                        Fusionner les données
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-b border-gray-100 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                <h3 class="flex items-center text-base font-medium text-gray-900 dark:text-white">
                    <span class="flex items-center justify-center w-6 h-6 mr-2 text-xs text-white bg-blue-500 rounded-full">2</span>
                    Actions après fusion
                </h3>
            </div>

            <div class="p-4 sm:p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="p-4 border rounded-lg bg-purple-50 dark:bg-gray-700 dark:border-gray-600">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <em class="text-2xl text-purple-500 icon ni ni-check-thick"></em>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">Validation des résultats</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Après vérification manuelle, validez les résultats pour finaliser le processus.
                                </p>
                                <div class="mt-3">
                                    <button
                                        wire:click="validerResultats"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                                    >
                                        <em class="mr-2 -ml-1 icon ni ni-check-thick"></em>
                                        Valider les résultats
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border rounded-lg bg-amber-50 dark:bg-gray-700 dark:border-gray-600">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <em class="text-2xl icon ni ni-bar-chart text-amber-500"></em>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">Statistiques</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Consultez les statistiques des résultats générés pour l'analyse et la prise de décision.
                                </p>
                                <div class="mt-3">
                                    <button
                                        wire:click="calculerStatistiques"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500"
                                    >
                                        <em class="mr-2 -ml-1 icon ni ni-bar-chart"></em>
                                        Voir les statistiques
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Affichage des résultats de la fusion si disponible -->
        @if($resultatFusion)
        <div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-sm dark:bg-gray-800 sm:rounded-lg dark:border-gray-700">
            <div class="p-4 border-b border-green-100 bg-green-50 dark:bg-green-900 dark:border-green-800">
                <h3 class="text-base font-medium text-green-800 dark:text-green-100">Résultats de la fusion</h3>
                <p class="mt-1 text-sm text-green-600 dark:text-green-300">
                    La fusion des données a été effectuée. Veuillez vérifier les résultats ci-dessous.
                </p>
            </div>

            <div class="p-4 sm:p-6">
                <!-- Statistiques en cards -->
                <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-4">
                    <div class="p-4 bg-blue-100 rounded-lg dark:bg-blue-800">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <em class="text-xl text-blue-600 icon ni ni-cards dark:text-blue-300"></em>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-100">Manchettes</h4>
                                <p class="text-lg font-semibold text-blue-900 dark:text-white">
                                    {{ $resultatFusion['statistiques']['total_manchettes'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-green-100 rounded-lg dark:bg-green-800">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <em class="text-xl text-green-600 icon ni ni-files dark:text-green-300"></em>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-green-800 dark:text-green-100">Copies</h4>
                                <p class="text-lg font-semibold text-green-900 dark:text-white">
                                    {{ $resultatFusion['statistiques']['total_copies'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-purple-100 rounded-lg dark:bg-purple-800">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <em class="text-xl text-purple-600 icon ni ni-list-check dark:text-purple-300"></em>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-purple-800 dark:text-purple-100">Résultats générés</h4>
                                <p class="text-lg font-semibold text-purple-900 dark:text-white">
                                    {{ $resultatFusion['statistiques']['resultats_generes'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-lg {{ count($resultatFusion['erreurs']) > 0 ? 'bg-red-100 dark:bg-red-800' : 'bg-green-100 dark:bg-green-800' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <em class="icon ni {{ count($resultatFusion['erreurs']) > 0 ? 'ni-alert-circle text-red-600 dark:text-red-300' : 'ni-check-circle text-green-600 dark:text-green-300' }} text-xl"></em>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium {{ count($resultatFusion['erreurs']) > 0 ? 'text-red-800 dark:text-red-100' : 'text-green-800 dark:text-green-100' }}">Erreurs</h4>
                                <p class="text-lg font-semibold {{ count($resultatFusion['erreurs']) > 0 ? 'text-red-900 dark:text-white' : 'text-green-900 dark:text-white' }}">
                                    {{ count($resultatFusion['erreurs']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des erreurs si présentes -->
                @if(count($resultatFusion['erreurs']) > 0)
                <div class="mt-6">
                    <div class="flex items-center mb-3">
                        <em class="text-xl text-red-500 icon ni ni-alert-circle"></em>
                        <h4 class="ml-2 text-lg font-medium text-red-700 dark:text-red-400">Erreurs détectées</h4>
                    </div>
                    <div class="mt-2 overflow-y-auto border border-red-200 rounded-lg max-h-80 dark:border-red-700">
                        <ul class="divide-y divide-red-200 dark:divide-red-700">
                            @foreach($resultatFusion['erreurs'] as $erreur)
                            <li class="p-3 bg-red-50 dark:bg-red-900/30">
                                <div class="flex items-start">
                                    <em class="icon ni ni-alert-circle mt-0.5 text-red-500"></em>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-800 dark:text-red-200">{{ $erreur['message'] }}</p>
                                        <span class="text-xs text-red-600 dark:text-red-400">
                                            Type: {{ $erreur['type'] }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mt-4">
                        <button
                            wire:click="afficherResolutionErreurs"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            <em class="mr-2 icon ni ni-setting"></em>
                            Résoudre les erreurs
                        </button>
                    </div>
                </div>
                @endif

                <!-- Actions après fusion -->
                <div class="flex justify-end mt-6 space-x-3">
                    <button
                        wire:click="imprimer"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <em class="mr-2 icon ni ni-printer"></em>
                        Imprimer pour vérification
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Modales pour les différentes actions -->
        @include('livewire.resultats.partials.modals')
    </div>
</div>
