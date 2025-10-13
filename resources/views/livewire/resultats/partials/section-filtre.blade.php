{{-- livewire.resultats.partials.section-filtre --}}
@if($examen && $sessionActive)
    <div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-sm dark:bg-gray-800 sm:rounded-lg dark:border-gray-700">
        <div class="p-4 sm:p-6">
            <!-- Filtres de sélection -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-medium text-gray-900 dark:text-white">
                    Filtres de sélection
                    @if($totalResultats > 0)
                        <span class="ml-2 text-sm font-normal text-gray-600 dark:text-gray-400">
                            ({{ number_format($totalResultats) }} résultat(s) trouvé(s))
                        </span>
                    @endif
                </h3>
                <div class="flex items-center space-x-3">
                    <button
                        wire:click="resetToExamenValues"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors duration-200"
                        wire:loading.attr="disabled"
                        wire:target="resetToExamenValues">
                        <em class="mr-2 icon ni ni-reload"></em>
                        Réinitialiser
                        <span wire:loading wire:target="resetToExamenValues" class="ml-2 animate-spin icon ni ni-loader"></span>
                    </button>
                    <button wire:click="toggleInfosPresence" 
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-200">
                        <em class="mr-2 icon ni ni-{{ $afficherInfosPresence ? 'eye-off' : 'eye' }}"></em>
                        Présence
                    </button>
                </div>
            </div>

            <!-- Session active -->
            <div class="p-3 mb-4 text-sm border border-blue-100 rounded-md bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                <div class="flex items-start">
                    <em class="icon ni ni-calendar-alt text-blue-400 mt-0.5 flex-shrink-0"></em>
                    <div class="ml-3">
                        <p class="text-blue-700 dark:text-blue-300">
                            <span class="font-medium">Session active :</span> {{ $sessionActive->type }} - Année Universitaire {{ $sessionActive->anneeUniversitaire->date_start->format('Y') }}/{{ $sessionActive->anneeUniversitaire->date_end->format('Y') }}
                        </p>
                        <p class="text-blue-700 dark:text-blue-300">
                            <span class="font-medium">Examen :</span> {{ $examen->nom }} (Niveau : {{ $examen->niveau->nom ?? 'N/A' }} | Parcours : {{ $examen->parcours->nom ?? 'N/A' }})
                        </p>
                    </div>
                </div>
            </div>

            @if($statistiquesPresence && $afficherInfosPresence)
                <div class="p-3 mb-4 text-sm border border-green-100 rounded-md bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <em class="icon ni ni-users text-green-400 mt-0.5 flex-shrink-0"></em>
                            <div class="ml-3">
                                <p class="text-green-700 dark:text-green-300">
                                    <span class="font-medium">Présence :</span> 
                                    {{ $statistiquesPresence['etudiants_presents'] }}/{{ $statistiquesPresence['total_inscrits'] }} présents 
                                    ({{ $statistiquesPresence['taux_presence'] }}%)
                                </p>
                            </div>
                        </div>
                        <button wire:click="toggleInfosPresence" class="text-green-600 hover:text-green-800 transition-colors duration-200">
                            <em class="icon ni ni-eye-off"></em>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Filtres principaux -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Niveau -->
                <div>
                    <label for="niveau_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Niveau</label>
                    <select id="niveau_id" 
                            wire:model.blur="niveau_id" 
                            wire:loading.attr="disabled"
                            wire:target="updatedNiveauId"
                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 transition-colors duration-200">
                        <option value="">Sélectionner un niveau</option>
                        @foreach($niveaux as $niveau)
                            <option value="{{ $niveau->id }}">{{ $niveau->abr }} - {{ $niveau->nom }}</option>
                        @endforeach
                    </select>
                    <div wire:loading wire:target="updatedNiveauId" class="mt-1 text-xs text-blue-600">
                        <em class="animate-spin icon ni ni-loader"></em>
                        Chargement parcours...
                    </div>
                </div>

                <!-- Parcours -->
                <div>
                    <label for="parcours_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parcours</label>
                    <select id="parcours_id" 
                            wire:model.blur="parcours_id"
                            wire:loading.attr="disabled"
                            wire:target="updatedParcoursId"
                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 transition-colors duration-200" 
                            {{ count($parcours) ? '' : 'disabled' }}>
                        <option value="">Sélectionner un parcours</option>
                        @foreach($parcours as $parcour)
                            <option value="{{ $parcour->id }}">{{ $parcour->abr }} - {{ $parcour->nom }}</option>
                        @endforeach
                    </select>
                    <div wire:loading wire:target="updatedParcoursId" class="mt-1 text-xs text-blue-600">
                        <em class="animate-spin icon ni ni-loader"></em>
                        Chargement ECs...
                    </div>
                </div>

                <!-- Ajoutez ce style dans votre layout ou composant -->
                <style>
                    option[data-has-teacher]::after {
                        font-weight: 600;
                    }
                </style>

                <!-- EC -->
                <div>
                    <label for="ec_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Élément Constitutif (EC)
                        @if($ec_id)
                            <span class="ml-1 text-xs text-green-600 font-medium">✓ Filtré</span>
                        @endif
                    </label>

                    <select id="ec_id"
                            wire:key="select-ec"
                            wire:model.live.debounce.120ms="ec_id"
                            x-on:change="onEcChange($event)"
                            wire:loading.attr="disabled"
                            wire:target="updatedEcId"
                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 transition-colors duration-200"
                            {{ count($ecs) ? '' : 'disabled' }}>
                        <option value="">Tous les ECs</option>
                        @foreach($ecs as $ec)
                            <option value="{{ $ec->id }}" {{ $ec->enseignant ? 'data-has-teacher' : '' }}>
                                {{ isset($ec->abr) ? $ec->abr . ' - ' : '' }}{{ $ec->nom }}
                                @if($ec->enseignant)
                                    ({{ $ec->enseignant }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>


                <!-- Enseignant -->
                <div>
                    <label for="enseignant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Enseignant
                        @if($enseignant_id)
                            <span class="ml-1 text-xs text-green-600 font-medium">✓ Filtré</span>
                        @endif
                    </label>
                    <select id="enseignant_id" 
                            wire:model.live="enseignant_id"
                            wire:loading.attr="disabled"
                            wire:target="updatedEnseignantId"
                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 transition-colors duration-200" 
                            {{ count($enseignants) ? '' : 'disabled' }}>
                        <option value="">Tous les enseignants</option>
                        @foreach($enseignants as $enseignant)
                            <option value="{{ $enseignant['nom'] }}">{{ $enseignant['nom'] }}</option>
                        @endforeach
                    </select>
                    <div wire:loading wire:target="updatedEnseignantId" class="mt-1 text-xs text-blue-600">
                        <em class="animate-spin icon ni ni-loader"></em>
                        Filtrage par enseignant...
                    </div>
                </div>
            </div>

            <!-- Section de recherche simplifiée -->
            <div class="mt-6 p-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Recherche d'étudiants</h4>
                
                <div class="grid grid-cols-1">
                    <!-- Champ de recherche -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Recherche
                            @if($search)
                                <span class="ml-1 text-xs text-green-600 font-medium">✓ Actif</span>
                            @endif
                        </label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <em class="text-gray-400 icon ni ni-search"></em>
                            </div>
                            <input type="text" 
                                   wire:model.live="search" 
                                   id="search" 
                                   wire:loading.attr="disabled"
                                   wire:target="updatedSearch"
                                   class="block w-full py-2 pl-10 pr-10 border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 transition-colors duration-200" 
                                   placeholder="Matricule, nom, prénom..." />
                            
                            <!-- Clear search button -->
                            @if($search)
                                <button wire:click="$set('search', '')" 
                                        class="absolute inset-y-0 right-8 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                                        type="button">
                                    <em class="icon ni ni-cross"></em>
                                </button>
                            @endif
                            
                            <!-- Loading indicator -->
                            <div wire:loading wire:target="updatedSearch" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <em class="text-blue-400 animate-spin icon ni ni-loader"></em>
                            </div>
                        </div>
                        
                        <!-- Aide contextuelle -->
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Recherchez dans le matricule, nom ou prénom
                        </p>
                    </div>
                </div>
            </div>

            <!-- Indicateurs de filtres actifs -->
            @if($ec_id || $enseignant_id || $search)
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md dark:bg-blue-900/20 dark:border-blue-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <em class="text-blue-500 icon ni ni-filter"></em>
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Filtres actifs :</span>
                            
                            <div class="flex flex-wrap gap-2">
                                @if($ec_id)
                                    @php $selectedEc = $ecs->firstWhere('id', $ec_id); @endphp
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-800 dark:text-blue-200">
                                        EC: {{ $selectedEc->abr ?? $selectedEc->nom ?? 'N/A' }}
                                        <button wire:click="$set('ec_id', null)" class="ml-1 text-blue-600 hover:text-blue-800">
                                            <em class="icon ni ni-cross"></em>
                                        </button>
                                    </span>
                                @endif
                                
                                @if($enseignant_id)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full dark:bg-green-800 dark:text-green-200">
                                        Enseignant: {{ $enseignant_id }}
                                        <button wire:click="$set('enseignant_id', null)" class="ml-1 text-green-600 hover:text-green-800">
                                            <em class="icon ni ni-cross"></em>
                                        </button>
                                    </span>
                                @endif
                                
                                @if($search)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-purple-800 bg-purple-100 rounded-full dark:bg-purple-800 dark:text-purple-200">
                                        Recherche: "{{ $search }}"
                                        <button wire:click="$set('search', '')" class="ml-1 text-purple-600 hover:text-purple-800">
                                            <em class="icon ni ni-cross"></em>
                                        </button>
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <button wire:click="resetToExamenValues" 
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">
                            Tout effacer
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="p-6 text-center bg-blue-100 rounded-lg">
        <em class="mb-4 text-4xl text-blue-400 icon ni ni-alert"></em>
        <p class="text-sm text-blue-600 dark:text-blue-300">
            {{ !$examen ? 'Examen non trouvé.' : 'Aucune session active trouvée. Veuillez configurer une session active.' }}
        </p>
    </div>
@endif