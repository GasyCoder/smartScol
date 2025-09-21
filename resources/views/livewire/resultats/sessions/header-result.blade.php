{{-- Header Navigation avec boutons de retour --}}
<div class="mb-6">
    <div class="flex items-center justify-between">
        {{-- Fil d'Ariane à gauche --}}
        <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
            <span class="text-gray-900 dark:text-white font-medium">Résultats Finaux des Examens</span>
            @if($selectedNiveau)
                <em class="ni ni-bold-right text-xs"></em>
                <span class="text-gray-900 dark:text-white font-medium">
                    {{ $niveaux->where('id', $selectedNiveau)->first()?->nom ?? 'Niveau' }}
                </span>
            @endif
            @if($selectedParcours)
                <em class="ni ni-bold-right text-xs"></em>
                <span class="text-gray-900 dark:text-white font-medium">
                    {{ $parcours->where('id', $selectedParcours)->first()?->nom ?? 'Parcours' }}
                </span>
            @endif
        </div>

        {{-- Boutons de navigation à droite --}}
        @if($selectedNiveau)
        <div class="flex items-center space-x-3">
            @if($selectedParcours)
                <button wire:click="selectParcours(null)" 
                        class="px-4 py-2 text-sm font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 dark:bg-green-900/30 dark:text-green-300 dark:hover:bg-green-900/50 transition-colors border border-green-300 dark:border-green-600"
                        title="Changer de parcours">
                    <em class="ni ni-bold-left mr-2"></em>
                    Changer parcours
                </button>
            @endif
            
            <button wire:click="selectNiveau(null)" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors border border-gray-300 dark:border-gray-600"
                    title="Changer de niveau">
                <em class="ni ni-bold-left mr-2"></em>
                Changer niveau
            </button>
        </div>
        @endif
    </div>
</div>

{{-- Filtres principaux en format cards --}}
<div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/80 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-6">
    
    {{-- Année Universitaire (reste en select) --}}
    <div class="mb-6">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
            <em class="ni ni-calendar-grid-58 mr-2"></em>
            Année Universitaire
        </label>
        <select wire:model.defer="selectedAnneeUniversitaire"
                wire:change="$refresh"
                class="w-full max-w-xs px-4 py-3 text-sm text-gray-900 transition-all duration-200 bg-white border-2 border-gray-200 rounded-xl dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hover:border-gray-300 dark:hover:border-gray-500">
            <option value="">Choisir l'année...</option>
            @foreach($anneesUniversitaires as $annee)
                <option value="{{ $annee->id }}">{{ $annee->libelle }}</option>
            @endforeach
        </select>
    </div>

    {{-- Sélection du niveau --}}
    @if($etape_actuelle === 'niveau')
    <div>
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Sélection du Niveau</h2>
            <p class="text-gray-600 dark:text-gray-400">Choisissez un niveau d'études pour continuer</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 max-w-4xl mx-auto">
            @forelse($niveaux as $niveau)
                <button wire:click="selectNiveau({{ $niveau->id }})" 
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

    {{-- Sélection du parcours --}}
    @if($etape_actuelle === 'parcours')
    <div>
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Sélection du Parcours</h2>
            <p class="text-gray-600 dark:text-gray-400">
                Niveau: <span class="font-semibold">{{ $nom_niveau_selectionne }}</span>
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 max-w-4xl mx-auto">
            @foreach($parcours as $parcour)
                <button wire:click="selectParcours({{ $parcour->id }})" 
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
            @endforeach
        </div>
    </div>
    @endif

    {{-- État final: Filtres sélectionnés + Bouton charger --}}
    @if($etape_actuelle === 'pret_charger')
    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Filtres Sélectionnés</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Configuration actuelle pour charger les résultats</p>
            </div>
        </div>

        {{-- Résumé des sélections --}}
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 mb-6">
            {{-- Niveau sélectionné --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <span class="text-sm font-bold text-white">
                            {{ substr($niveaux->where('id', $selectedNiveau)->first()?->abr ?: $niveaux->where('id', $selectedNiveau)->first()?->nom, 0, 2) }}
                        </span>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-blue-900 dark:text-blue-100">Niveau</div>
                        <div class="text-sm text-blue-700 dark:text-blue-300">
                            {{ $nom_niveau_selectionne }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Parcours sélectionné --}}
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                        <span class="text-sm font-bold text-white">
                            @if($selectedParcours && $parcours->where('id', $selectedParcours)->isNotEmpty())
                                @php
                                    $parcoursTrouve = $parcours->where('id', $selectedParcours)->first();
                                    $abrParcours = $parcoursTrouve?->abr ?: $parcoursTrouve?->nom;
                                @endphp
                                {{ substr($abrParcours, 0, 2) }}
                            @else
                                TO
                            @endif
                        </span>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-green-900 dark:text-green-100">Parcours</div>
                        <div class="text-sm text-green-700 dark:text-green-300">
                            @if($selectedParcours && $parcours->where('id', $selectedParcours)->isNotEmpty())
                                {{ $parcours->where('id', $selectedParcours)->first()?->nom }}
                            @else
                                Tous les parcours
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Année universitaire --}}
            @if($selectedAnneeUniversitaire)
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                        <em class="text-white text-sm ni ni-calendar-alt text-xl"></em>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-purple-900 dark:text-purple-100">Année</div>
                        <div class="text-sm text-purple-700 dark:text-purple-300">
                            {{ $nom_annee_selectionnee }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        {{-- Bouton charger les résultats --}}
        <div class="text-center">
            <button wire:click="chargerResultats" 
                    wire:loading.attr="disabled"
                    wire:target="chargerResultats"
                    class="inline-flex items-center px-8 py-4 text-base font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    @if(!$peut_charger_resultats) disabled @endif>
                
                {{-- Icône normale --}}
                <em class="mr-3 text-xl ni ni-refresh" wire:loading.remove wire:target="chargerResultats"></em>
                
                {{-- Spinner de chargement --}}
                <svg wire:loading wire:target="chargerResultats" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                
                {{-- Texte du bouton --}}
                <span wire:loading.remove wire:target="chargerResultats">Charger les résultats</span>
                <span wire:loading wire:target="chargerResultats">Chargement en cours...</span>
            </button>
            
            {{-- Message d'information --}}
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400" wire:loading.remove wire:target="chargerResultats">
                Cliquez pour charger les données avec les filtres sélectionnés
            </p>
            <br>
            {{-- Message de chargement --}}
            <p class="mt-2 text-sm text-blue-600 dark:text-blue-400" wire:loading wire:target="chargerResultats">
                <span class="inline-flex items-center">
                    <svg class="animate-pulse w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    Chargement des résultats en cours, veuillez patienter...
                </span>
            </p>
        </div>
    </div>
    @endif

    {{-- Sessions avec état de délibération - SEULEMENT SI ON A CHARGÉ LES RÉSULTATS --}}
    @if($dois_afficher_resultats)
    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
            <em class="ni ni-activity mr-2"></em>
            État des Sessions
        </label>
        <div class="grid gap-4 md:grid-cols-2">
            @if($sessionNormale)
                @php
                    $deliberationS1 = $deliberationStatus['session1'] ?? false;
                    $statsS1 = $statistiquesDeliberation['session1'] ?? null;
                @endphp
                <div class="flex items-center justify-between p-4 border-2 rounded-xl {{ $deliberationS1 ? 'border-green-300 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 dark:border-green-600' : 'border-blue-300 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 dark:border-blue-600' }}">
                    <div class="flex items-center">
                        <div class="w-3 h-3 mr-3 {{ $deliberationS1 ? 'bg-green-500' : 'bg-blue-500 animate-pulse' }} rounded-full"></div>
                        <div>
                            <div class="text-sm font-medium {{ $deliberationS1 ? 'text-green-800 dark:text-green-300' : 'text-blue-800 dark:text-blue-300' }}">
                                {{ $sessionNormale->type }}
                            </div>
                            <div class="text-xs {{ $deliberationS1 ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}">
                                {{ $deliberationS1 ? 'Délibérée' : 'En cours' }}
                            </div>
                        </div>
                    </div>
                    @if($statsS1 && isset($statsS1['configuration_existante']) && $statsS1['configuration_existante'] && $deliberationS1)
                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ $statsS1['statistiques']['total_valides_jury'] ?? 0 }}/{{ $statsS1['statistiques']['total_etudiants'] ?? 0 }}
                        </div>
                    @endif
                </div>
            @endif

            @if($showSession2)
                @php
                    $deliberationS2 = $deliberationStatus['session2'] ?? false;
                    $statsS2 = $statistiquesDeliberation['session2'] ?? null;
                @endphp
                <div class="flex items-center justify-between p-4 border-2 rounded-xl {{ $deliberationS2 ? 'border-green-300 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 dark:border-green-600' : 'border-orange-300 bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 dark:border-orange-600' }}">
                    <div class="flex items-center">
                        <div class="w-3 h-3 mr-3 {{ $deliberationS2 ? 'bg-green-500' : 'bg-orange-500' }} rounded-full"></div>
                        <div>
                            <div class="text-sm font-medium {{ $deliberationS2 ? 'text-green-800 dark:text-green-300' : 'text-orange-800 dark:text-orange-300' }}">
                                Rattrapage
                            </div>
                            <div class="text-xs {{ $deliberationS2 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                {{ $deliberationS2 ? 'DÉLIBÉRÉE' : 'EN ATTENTE' }}
                            </div>
                        </div>
                    </div>
                    @if($statsS2 && isset($statsS2['configuration_existante']) && $statsS2['configuration_existante'] && $deliberationS2)
                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400">
                            {{ $statsS2['statistiques']['total_valides_jury'] ?? 0 }}/{{ $statsS2['statistiques']['total_etudiants'] ?? 0 }}
                        </div>
                    @endif
                </div>
            @else
                <div class="p-4 border-2 border-gray-200 bg-gray-50 rounded-xl dark:bg-gray-700 dark:border-gray-600">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Session 2 non disponible</div>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>