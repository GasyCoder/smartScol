<!-- resources/views/livewire/resultats/resultats-verification.blade.php -->
<div>
    <div class="{{ $printMode ? 'print-container' : 'container px-4 py-6 mx-auto' }}">
        <!-- En-tête avec titre et actions (masqué en mode impression) -->
        @if(!$printMode)
            <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <!-- Titre principal -->
                    <h2 class="text-xl font-medium text-slate-700 dark:text-white">Vérification des Résultats</h2>

                    <!-- Actions globales -->
                    <div class="flex items-center space-x-2">
                        <button wire:click="togglePrintMode" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded bg-primary-600 text-white hover:bg-primary-700 focus:outline-none">
                            <em class="icon ni ni-printer mr-1.5"></em>
                            Imprimer
                        </button>
                        <a href="{{ route('resultats.fusion') }}" class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none">
                            <em class="icon ni ni-shuffle mr-1.5"></em>
                            Fusion
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filtres (masqués en mode impression) -->
            <div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-sm dark:bg-gray-800 sm:rounded-lg dark:border-gray-700">
                <div class="p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-medium text-gray-900 dark:text-white">Filtres de sélection</h3>
                    </div>

                    <!-- Session active -->
                    @if($sessionActive)
                        <div class="p-3 mb-4 text-sm border border-blue-100 rounded-md bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                            <div class="flex items-start">
                                <em class="icon ni ni-calendar-alt text-blue-400 mt-0.5 flex-shrink-0"></em>
                                <div class="ml-3">
                                    <p class="text-blue-700 dark:text-blue-300">
                                        <span class="font-medium">Session active:</span> {{ $sessionActive->type }} - Année Universitaire {{ $sessionActive->anneeUniversitaire->date_start->format('Y') }}/{{ $sessionActive->anneeUniversitaire->date_end->format('Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- Niveau -->
                        <div>
                            <label for="niveau_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Niveau</label>
                            <select id="niveau_id" wire:model.live="niveau_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Sélectionner un niveau</option>
                                @foreach($niveaux as $niveau)
                                    <option value="{{ $niveau->id }}">{{ $niveau->abr }} - {{ $niveau->nom }}</option>
                                @endforeach
                            </select>
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
                        </div>
                        <!-- Matière (EC) -->
                        <div>
                            <label for="ec_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Matière</label>
                            <select id="ec_id" wire:model.live="ec_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" {{ count($ecs) ? '' : 'disabled' }}>
                                <option value="">Sélectionner une matière</option>
                                @foreach($ecs as $ec)
                                    <option value="{{ $ec->id }}">{{ isset($ec->abr) ? $ec->abr . ' - ' : '' }}{{ $ec->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- SUPPRIMÉ: Le champ Statut n'est plus nécessaire -->
                    </div>
                    <!-- Recherche -->
                    <div class="mt-4">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recherche</label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <em class="text-gray-400 icon ni ni-search"></em>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="search" id="search" class="block w-full py-2 pl-10 pr-3 border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Rechercher par matricule, nom, prénom ou enseignant...">
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- En-tête pour impression -->
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold">FACULTÉ DE MÉDECINE</h1>
                <h2 class="text-xl">Vérification des Résultats</h2>
                <p>
                    @if($sessionActive)
                        Session: {{ $sessionActive->type }} - Année Universitaire {{ $sessionActive->anneeUniversitaire->date_start->format('Y') }}/{{ $sessionActive->anneeUniversitaire->date_end->format('Y') }}
                    @endif
                </p>
                <p>
                    @if($niveau_id)
                        @php
                            $selectedNiveau = $niveaux->firstWhere('id', $niveau_id);
                        @endphp
                        @if($selectedNiveau)
                            Niveau: {{ $selectedNiveau->nom }}
                        @endif
                    @endif
                    @if($parcours_id)
                        @php
                            $selectedParcours = $parcours->firstWhere('id', $parcours_id);
                        @endphp
                        @if($selectedParcours)
                            - Parcours: {{ $selectedParcours->nom }}
                        @endif
                    @endif
                </p>
                <p>
                    @if($ec_id && $ec_id !== 'all')
                        @php
                            $selectedEc = $ecs->firstWhere('id', $ec_id);
                        @endphp
                        @if($selectedEc)
                            Matière: {{ isset($selectedEc->abr) ? $selectedEc->abr . ' - ' : '' }}{{ $selectedEc->nom }}
                        @endif
                    @elseif($ec_id === 'all')
                        Toutes les matières
                    @endif
                </p>
                <p>Date d'impression: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        @endif

        <!-- Tableau des résultats -->
        @if($showVerification)
            @if(count($resultats) > 0)
            @include('livewire.resultats.partials.table-verify-resultat', ['resultats' => $resultats])
            @else
                <div class="p-6 text-center bg-gray-100 rounded-lg dark:bg-gray-700">
                    <em class="mb-2 text-4xl text-gray-300 icon ni ni-folder-open dark:text-gray-600"></em>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Aucun résultat à vérifier</p>
                </div>
            @endif
        @else
            <div class="p-6 text-center bg-gray-100 rounded-lg dark:bg-gray-700">
                <em class="mb-2 text-4xl text-gray-300 icon ni ni-folder-open dark:text-gray-600"></em>
                <p class="text-sm text-gray-600 dark:text-gray-300">Veuillez d'abord effectuer la première étape de la fusion pour voir les résultats à vérifier.</p>
            </div>
        @endif

        @if($printMode)
            <!-- Pied de page en mode impression -->
            <div class="mt-8 text-center">
                <p class="mb-2 text-sm">
                    Signature du responsable : _____________________
                </p>
                <p class="mb-2 text-sm">
                    Date : _____________________
                </p>
                <p class="text-sm italic">
                    NB: Ces résultats sont provisoires et peuvent faire l'objet de modifications.
                </p>
            </div>
        @endif
    </div>

    <!-- Styles pour l'impression -->
    <style>
        @media print {
            body {
                font-size: 12pt;
                color: #000;
                background-color: #fff;
            }

            .print-container {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            table, th, td {
                border: 1px solid #000;
            }

            th, td {
                padding: 6px;
                text-align: left;
            }

            th {
                background-color: #f2f2f2;
            }

            .bg-green-100,
            .bg-red-100,
            .bg-green-50 {
                background-color: transparent !important;
            }

            .text-green-800,
            .text-red-800,
            .text-green-600 {
                font-weight: bold;
            }
        }
    </style>

    <!-- Script pour l'impression -->
    @if($printMode)
        <script>
            document.addEventListener('livewire:initialized', function () {
                window.print();
                window.addEventListener('afterprint', function() {
                    Livewire.dispatch('togglePrintMode');
                });
            });
        </script>
    @endif
</div>
