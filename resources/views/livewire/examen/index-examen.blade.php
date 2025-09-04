<div>
{{-- Header Navigation avec boutons de retour TOUJOURS VISIBLES --}}
<div class="mb-6">
    <div class="flex items-center justify-between">
        {{-- Fil d'Ariane à gauche --}}
        <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
            @if($step !== 'niveau')
                <em class="ni ni-bold-right text-xs"></em>
                <span class="text-gray-900 dark:text-white font-medium">{{ $niveauInfo['nom'] ?? 'Niveau' }}</span>
            @endif
            @if($step === 'examens')
                <em class="ni ni-bold-right text-xs"></em>
                <span class="text-gray-900 dark:text-white font-medium">{{ $parcoursInfo['nom'] ?? 'Parcours' }}</span>
            @endif
        </div>

        {{-- Boutons de navigation à droite - TOUJOURS VISIBLES quand on est dans examens --}}
        @if($step === 'examens')
        <div class="flex items-center space-x-3">
            <button wire:click="$set('step', 'parcours')" 
                    class="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 transition-colors border border-blue-300 dark:border-blue-600"
                    title="Changer de parcours">
                <em class="ni ni-bold-left mr-2"></em>
                Changer parcours
            </button>
            
            <button wire:click="$set('step', 'niveau')" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors border border-gray-300 dark:border-gray-600"
                    title="Changer de niveau">
                <em class="ni ni-bold-left mr-2"></em>
                Changer niveau
            </button>
        </div>
        @endif
    </div>
</div>

{{-- Étape 1: Sélection du niveau --}}
@if($step === 'niveau')
<div>
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Gestion des Examens</h2>
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
<div>
    <div class="flex items-center justify-between mb-8">
        <div class="text-center flex-1">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Sélection du Parcours</h2>
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

{{-- Étape 3: Liste des examens --}}
@if($step === 'examens')
{{-- En-tête avec statistiques --}}
@if(isset($stats))
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Examens - {{ $niveauInfo['nom'] }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $parcoursInfo['nom'] }}</p>
        </div>
        
<!-- Boutons d'export à ajouter dans la section des filtres (après les boutons existants) -->
<div class="flex space-x-3">
    @if($hasFilters)
        <button wire:click="resetFilters" 
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Réinitialiser
        </button>
    @endif

    <!-- NOUVEAU: Dropdown pour les exports -->
    <div class="relative" x-data="{ openExport: false }">
        <button @click="openExport = !openExport" 
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                :class="{ 'ring-2 ring-green-500': openExport }">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exporter
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <!-- Menu dropdown -->
        <div x-show="openExport" 
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-1 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-1 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             @click.away="openExport = false"
             class="absolute right-0 z-50 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            
            <div class="p-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Choisir le format d'export</h3>
                
                <!-- Info sur le filtrage -->
                @if($hasFilters)
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-start">
                            <svg class="w-4 h-4 mt-0.5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-xs text-blue-700 dark:text-blue-300">
                                <strong>Filtres actifs :</strong>
                                @if($search)
                                    <br>• Recherche: "{{ $search }}"
                                @endif
                                @if($enseignant_filter)
                                    <br>• Enseignant: "{{ $enseignant_filter }}"
                                @endif
                                @if($date_from || $date_to)
                                    <br>• Période: {{ $date_from ?: '...' }} → {{ $date_to ?: '...' }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Boutons d'export Excel -->
                <div class="space-y-2 mb-4">
                    <h4 class="text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wide">Excel (.xlsx)</h4>
                    
                    <button wire:click="exportExamens('excel', 'all')" 
                            @click="openExport = false"
                            class="w-full flex items-center px-3 py-2 text-sm text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors dark:bg-green-900/20 dark:text-green-300 dark:hover:bg-green-900/30">
                        <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 00-2 2v10z"/>
                        </svg>
                        <div class="text-left">
                            <div class="font-medium">
                                @if($hasFilters)
                                    Examens filtrés
                                @else
                                    Tous les examens
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Planning détaillé avec toutes les informations</div>
                        </div>
                    </button>

                    @if($enseignant_filter)
                        <button wire:click="exportExamens('excel', 'enseignant')" 
                                @click="openExport = false"
                                class="w-full flex items-center px-3 py-2 text-sm text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors dark:bg-green-900/20 dark:text-green-300 dark:hover:bg-green-900/30">
                            <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <div class="text-left">
                                <div class="font-medium">{{ $enseignant_filter }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Planning pour cet enseignant uniquement</div>
                            </div>
                        </button>
                    @endif
                </div>

                <!-- Boutons d'export PDF -->
                <div class="space-y-2">
                    <h4 class="text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wide">PDF</h4>
                    
                    <button wire:click="exportExamens('pdf', 'all')" 
                            @click="openExport = false"
                            class="w-full flex items-center px-3 py-2 text-sm text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30">
                        <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <div class="text-left">
                            <div class="font-medium">
                                @if($hasFilters)
                                    Examens filtrés
                                @else
                                    Tous les examens
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Planning imprimable avec calendrier</div>
                        </div>
                    </button>

                    @if($enseignant_filter)
                        <button wire:click="exportExamens('pdf', 'enseignant')" 
                                @click="openExport = false"
                                class="w-full flex items-center px-3 py-2 text-sm text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30">
                            <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <div class="text-left">
                                <div class="font-medium">{{ $enseignant_filter }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Planning personnel imprimable</div>
                            </div>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <a href="{{ route('examens.create', ['niveau' => $niveauInfo['id'], 'parcour' => $parcoursInfo['id']]) }}" 
       class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvel examen
    </a>
</div>
</div>
    
{{-- Statistiques (sans copies/manchettes) --}}
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center">
        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_examens'] ?? 0 }}</div>
        <div class="text-xs text-gray-600 dark:text-gray-400">Examens</div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center">
        <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['total_ues'] ?? 0 }}</div>
        <div class="text-xs text-gray-600 dark:text-gray-400">UE</div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center">
        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['total_ecs'] ?? 0 }}</div>
        <div class="text-xs text-gray-600 dark:text-gray-400">EC</div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center">
        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['enseignants_uniques'] ?? 0 }}</div>
        <div class="text-xs text-gray-600 dark:text-gray-400">Enseignants</div>
    </div>

    {{-- 👇 Remplacement par Total crédits (UE) --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center">
        <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
            {{ number_format((float)($stats['total_credits_ues'] ?? 0), 2, ',', ' ') }}
        </div>
        <div class="text-xs text-gray-600 dark:text-gray-400">Total crédits (UE)</div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center">
        <div class="text-2xl font-bold {{ ($stats['taux_planification'] ?? 0) >= 80 ? 'text-green-600 dark:text-green-400' : (($stats['taux_planification'] ?? 0) >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
            {{ $stats['taux_planification'] ?? 0 }}%
        </div>
        <div class="text-xs text-gray-600 dark:text-gray-400">Planification EC</div>
    </div>
</div>

</div>
@endif

{{-- Filtres --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {{-- Recherche --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Recherche</label>
            <div class="relative">
                <input type="text" 
                       wire:model.live.debounce.300ms="search"
                       placeholder="Nom, abréviation, enseignant..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        {{-- Filtre enseignant --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Enseignant</label>
            <select wire:model.live="enseignant_filter"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Tous les enseignants</option>
                @foreach($enseignants as $enseignant)
                    <option value="{{ $enseignant }}">{{ $enseignant }}</option>
                @endforeach
            </select>
        </div>

        {{-- Date début --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date début</label>
            <input type="date" 
                   wire:model.live="date_from"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        {{-- Date fin --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date fin</label>
            <input type="date" 
                   wire:model.live="date_to"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
    </div>
</div>

{{-- Liste des examens --}}
<div class="space-y-6">
    @forelse($examens as $examen)
        @if($examen->ecsGroupedByUE->isEmpty())
            {{-- Message quand aucune matière ne correspond aux filtres --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aucune matière trouvée</h3>
                <p class="text-gray-500 dark:text-gray-400">
                    @if($enseignant_filter)
                        Aucune matière trouvée pour l'enseignant "{{ $enseignant_filter }}".
                    @elseif($search)
                        Aucune matière ne correspond à votre recherche "{{ $search }}".
                    @else
                        Aucune matière ne correspond aux critères de filtrage.
                    @endif
                </p>
            </div>
        @else
            {{-- Affichage normal des examens avec matières filtrées --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                @foreach($examen->ecsGroupedByUE as $ueGroup)
                    {{-- En-tête UE --}}
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                                    <span class="text-xs font-bold text-white">{{ substr($ueGroup['ue_abr'] ?: $ueGroup['ue_nom'], 0, 2) }}</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $ueGroup['ue_abr'] ? $ueGroup['ue_abr'] . ' - ' : '' }}{{ $ueGroup['ue_nom'] }}
                                        <span class="text-xs text-gray-500">(Examen #{{ $examen->id }})</span>
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ count($ueGroup['ecs']) }} matière(s) | Durée: {{ $examen->duree }}min
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                @if($enseignant_filter || $search)
                                    <div class="text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">
                                        @if($enseignant_filter)
                                            Filtré par enseignant
                                        @elseif($search)
                                            Résultat de recherche
                                        @endif
                                    </div>
                                @endif
                                
                                {{-- Actions de l'examen --}}
                                <div class="flex space-x-2">
                                    <a href="{{ route('examens.edit', ['examen' => $examen->id]) }}" 
                                       class="px-3 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded hover:bg-blue-200 transition-colors"
                                       title="Modifier tout l'examen">
                                        <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Modifier
                                    </a>
                                    <button wire:click="confirmDelete({{ $examen->id }})" 
                                            class="px-3 py-1 text-xs font-medium text-red-700 bg-red-100 rounded hover:bg-red-200 transition-colors"
                                            title="Supprimer tout l'examen">
                                        <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Liste des ECs --}}
                    <div class="p-6">
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($ueGroup['ecs'] as $ec)
                                @php
                                    $shouldShow = true;
                                    
                                    // Si filtre enseignant actif, vérifier que l'EC appartient à cet enseignant
                                    if(!empty($enseignant_filter) && $ec->enseignant !== $enseignant_filter) {
                                        $shouldShow = false;
                                    }
                                    
                                    // Si recherche active, vérifier que l'EC correspond à la recherche
                                    if(!empty($search) && $shouldShow) {
                                        $searchTerm = strtolower(trim($search));
                                        $nomMatch = str_contains(strtolower($ec->nom), $searchTerm);
                                        $abrMatch = str_contains(strtolower($ec->abr ?? ''), $searchTerm);
                                        $ensMatch = str_contains(strtolower($ec->enseignant ?? ''), $searchTerm);
                                        
                                        if(!($nomMatch || $abrMatch || $ensMatch)) {
                                            $shouldShow = false;
                                        }
                                    }
                                @endphp

                                @if($shouldShow)
                                    @php
                                        $dateStr = $ec->pivot->date_specifique 
                                            ? \Carbon\Carbon::parse($ec->pivot->date_specifique)->format('d/m/Y') 
                                            : 'Non définie';
                                        $timeStr = $ec->pivot->heure_specifique 
                                            ? \Carbon\Carbon::parse($ec->pivot->heure_specifique)->format('H:i') 
                                            : '--:--';
                                        $salle = $ec->pivot->salle_id 
                                            ? App\Models\Salle::find($ec->pivot->salle_id) 
                                            : null;

                                        $copiesStats = $examen->copiesStatusByEc[$ec->id] ?? ['saisies' => 0, 'total' => 0];
                                        $manchettesStats = $examen->manchettesStatusByEc[$ec->id] ?? ['saisies' => 0, 'total' => 0];
                                        $copiesPercent = $copiesStats['total'] > 0 ? round(($copiesStats['saisies'] / $copiesStats['total']) * 100) : 0;
                                        $manchettesPercent = $manchettesStats['total'] > 0 ? round(($manchettesStats['saisies'] / $manchettesStats['total']) * 100) : 0;
                                    @endphp

                                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4 relative
                                        @if($enseignant_filter && $ec->enseignant === $enseignant_filter)
                                            ring-2 ring-blue-500 ring-opacity-50
                                        @endif
                                    ">
                                        {{-- Badge de statut --}}
                                        <div class="absolute top-3 right-3">
                                            @if($ec->pivot->code_base)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                                                    {{ $ec->pivot->code_base }}
                                                </span>
                                            @elseif($copiesStats['saisies'] == $copiesStats['total'] && $copiesStats['total'] > 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                    Complet
                                                </span>
                                            @elseif($copiesStats['saisies'] > 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                                    En cours
                                                </span>
                                            @endif
                                        </div>

                                        <div class="pr-16">
                                            {{-- Titre EC --}}
                                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">
                                                {{ $ec->abr ? $ec->abr . ' - ' : '' }}{{ $ec->nom }}
                                            </h4>

                                            {{-- Informations de planning --}}
                                            <div class="space-y-2 mb-4 text-sm text-gray-600 dark:text-gray-400">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span>{{ $dateStr }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span>{{ $timeStr }}</span>
                                                </div>
                                                @if($salle)
                                                <div class="flex items-center">
                                                    <em class="ni ni-building mr-2 text-purple-500"></em>
                                                    <span>{{ $salle->nom }}</span>
                                                </div>
                                                @endif
                                                <div class="flex items-center
                                                    @if($enseignant_filter && $ec->enseignant === $enseignant_filter)
                                                        text-blue-600 dark:text-blue-400 font-medium
                                                    @endif
                                                ">
                                                    <em class="ni ni-single-02 mr-2 text-indigo-500"></em>
                                                    <span>{{ $ec->enseignant ?: 'Non assigné' }}</span>
                                                    @if($enseignant_filter && $ec->enseignant === $enseignant_filter)
                                                        <em class="ni ni-check-bold ml-2 text-blue-500"></em>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Barres de progression --}}
                                            <div class="space-y-3 mb-4">
                                                {{-- Manchettes --}}
                                                <div>
                                                    <div class="flex justify-between text-xs mb-1">
                                                        <span class="text-gray-600 dark:text-gray-400">Manchettes</span>
                                                        <span class="font-medium">{{ $manchettesStats['saisies'] }}/{{ $manchettesStats['total'] }}</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                                        <div class="h-2 rounded-full {{ $manchettesPercent >= 100 ? 'bg-green-500' : ($manchettesPercent >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                                             style="width: {{ $manchettesPercent }}%"></div>
                                                    </div>
                                                </div>
                                                {{-- Copies --}}
                                                <div>
                                                    <div class="flex justify-between text-xs mb-1">
                                                        <span class="text-gray-600 dark:text-gray-400">Copies</span>
                                                        <span class="font-medium">{{ $copiesStats['saisies'] }}/{{ $copiesStats['total'] }}</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                                        <div class="h-2 rounded-full {{ $copiesPercent >= 100 ? 'bg-green-500' : ($copiesPercent >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                                             style="width: {{ $copiesPercent }}%"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Actions EC individuelles --}}
                                            <div class="flex space-x-2">
                                                <button wire:click="editEC({{ $examen->id }}, {{ $ec->id }})" 
                                                        class="flex-1 px-3 py-2 text-xs font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 transition-colors">
                                                    <em class="ni ni-settings mr-1"></em>
                                                    Modifier
                                                </button>
                                                <button wire:click="confirmDeleteEC({{ $examen->id }}, {{ $ec->id }})" 
                                                        class="flex-1 px-3 py-2 text-xs font-medium text-red-700 bg-red-100 rounded-lg hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50 transition-colors">
                                                    <em class="ni ni-fat-remove mr-1"></em>
                                                    Supprimer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        
                        {{-- Si aucune EC visible dans cette UE après filtrage --}}
                        @php
                            $visibleEcs = 0;
                            foreach($ueGroup['ecs'] as $ec) {
                                $shouldShow = true;
                                if(!empty($enseignant_filter) && $ec->enseignant !== $enseignant_filter) {
                                    $shouldShow = false;
                                }
                                if(!empty($search) && $shouldShow) {
                                    $searchTerm = strtolower(trim($search));
                                    $nomMatch = str_contains(strtolower($ec->nom), $searchTerm);
                                    $abrMatch = str_contains(strtolower($ec->abr ?? ''), $searchTerm);
                                    $ensMatch = str_contains(strtolower($ec->enseignant ?? ''), $searchTerm);
                                    if(!($nomMatch || $abrMatch || $ensMatch)) {
                                        $shouldShow = false;
                                    }
                                }
                                if($shouldShow) $visibleEcs++;
                            }
                        @endphp
                        
                        @if($visibleEcs === 0)
                            <div class="text-center py-8">
                                <p class="text-gray-500 dark:text-gray-400">Aucune matière visible dans cette UE avec les filtres appliqués</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @empty
        {{-- État vide --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <em class="ni ni-archive-2 text-6xl text-gray-400 mb-6 block"></em>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Aucun examen trouvé</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-8">Commencez par créer votre premier examen pour ce parcours.</p>
            <a href="{{ route('examens.create', ['niveau' => $niveauInfo['id'], 'parcour' => $parcoursInfo['id']]) }}" 
               class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                <em class="ni ni-fat-add mr-2"></em>
                Créer le premier examen
            </a>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($examens->hasPages())
<div class="mt-6">
    {{ $examens->links() }}
</div>
@endif

@endif

{{-- Modal d'édition EC --}}
@if($showEditECModal)
<div class="fixed inset-0 bg-gray-900/50 flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <em class="ni ni-settings mr-2 text-blue-500"></em>
                Modifier la matière
            </h3>
            @if($editingEC)
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $editingEC->abr ? $editingEC->abr . ' - ' : '' }}{{ $editingEC->nom }}
                </p>
            @endif
        </div>

        <div class="space-y-4">
            {{-- Date --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <em class="ni ni-calendar-grid-58 mr-1"></em>
                    Date *
                </label>
                <input type="date" 
                       wire:model="editingECData.date_specifique"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('editingECData.date_specifique')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Heure --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <em class="ni ni-time-alarm mr-1"></em>
                    Heure *
                </label>
                <input type="time" 
                       wire:model="editingECData.heure_specifique"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('editingECData.heure_specifique')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Salle --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <em class="ni ni-building mr-1"></em>
                    Salle
                </label>
                <select wire:model="editingECData.salle_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Aucune salle</option>
                    @foreach($salles as $salle)
                        <option value="{{ $salle->id }}">{{ $salle->nom }}</option>
                    @endforeach
                </select>
                @error('editingECData.salle_id')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Code --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <em class="ni ni-tag mr-1"></em>
                    Code
                </label>
                <input type="text" 
                       wire:model="editingECData.code_base"
                       maxlength="10"
                       placeholder="Ex: TA, TB..."
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('editingECData.code_base')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex space-x-3 mt-6">
            <button wire:click="closeEditECModal" 
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                <em class="ni ni-bold-left mr-1"></em>
                Annuler
            </button>
            <button wire:click="saveEC" 
                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                <em class="ni ni-check-bold mr-1"></em>
                Enregistrer
            </button>
        </div>
    </div>
</div>
@endif

{{-- Modal de suppression EC --}}
@if($showDeleteECModal)
<div class="fixed inset-0 bg-gray-900/50 flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                <em class="ni ni-fat-remove text-3xl text-red-600 dark:text-red-400"></em>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Supprimer la matière</h3>
            @if($ecToDelete)
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Êtes-vous sûr de vouloir supprimer la matière "<strong>{{ $ecToDelete->nom }}</strong>" de cet examen ?
                    <br><span class="text-sm text-red-600">Cette action est irréversible.</span>
                </p>
            @endif
            <div class="flex space-x-3">
                <button wire:click="closeDeleteECModal" 
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    <em class="ni ni-bold-left mr-1"></em>
                    Annuler
                </button>
                <button wire:click="deleteEC" 
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                    <em class="ni ni-fat-remove mr-1"></em>
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal suppression examen complet --}}
@if($showDeleteModal)
<div class="fixed inset-0 bg-gray-900/50 flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                <em class="ni ni-fat-remove text-3xl text-red-600 dark:text-red-400"></em>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Supprimer l'examen</h3>
            @if($examenToDelete)
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Êtes-vous sûr de vouloir supprimer l'examen "<strong>Examen #{{ $examenToDelete->id }}</strong>" ?
                    <br>
                    <span class="text-sm text-red-600">Cette action supprimera définitivement :</span>
                    <br>
                    <span class="text-xs text-gray-500">
                        - Toutes les matières de cet examen<br>
                        - Les codes d'anonymat générés<br>
                        - Les plannings associés
                    </span>
                    <br><br>
                    <span class="text-sm font-semibold text-red-600">Cette action est irréversible.</span>
                </p>
            @endif
            <div class="flex space-x-3">
                <button wire:click="cancelDelete" 
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    <em class="ni ni-bold-left mr-1"></em>
                    Annuler
                </button>
                <button wire:click="deleteExamen" 
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                    <em class="ni ni-fat-remove mr-1"></em>
                    Supprimer définitivement
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</div>