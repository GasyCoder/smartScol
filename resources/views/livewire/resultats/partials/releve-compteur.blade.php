        <!-- Compteurs Dashboard Contextualisé -->
    @if($selectedSession)
    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <!-- Titre contextuel -->
        <div class="mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Statistiques 
                @if($selectedNiveau)
                    - {{ $niveaux->find($selectedNiveau)?->nom }}
                    @if($selectedParcours)
                        ({{ $parcours->find($selectedParcours)?->nom }})
                    @endif
                @endif
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Session : {{ $sessions->find($selectedSession)?->type }}
                @if($search)
                    | Recherche : "{{ $search }}"
                @endif
            </p>
        </div>
    
    <!-- Compteurs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Votre code de compteurs existant reste identique -->
        <!-- Total Étudiants -->
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-md">
                   <em class="text-xl ni ni-users"></em>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-300">Total Étudiants</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $statistiques['total'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Admis -->
        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-800 rounded-md">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-600 dark:text-green-300">Admis</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $statistiques['admis'] ?? 0 }}</p>
                    <p class="text-xs text-green-500 dark:text-green-400">{{ $statistiques['pourcentage_admis'] ?? 0 }}%</p>
                </div>
            </div>
        </div>

        <!-- Rattrapage -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-800 rounded-md">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-600 dark:text-yellow-300">Rattrapage</p>
                    <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $statistiques['rattrapage'] ?? 0 }}</p>
                    <p class="text-xs text-yellow-500 dark:text-yellow-400">{{ $statistiques['pourcentage_rattrapage'] ?? 0 }}%</p>
                </div>
            </div>
        </div>

        <!-- Autres -->
        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 dark:bg-red-800 rounded-md">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-600 dark:text-red-300">Autres</p>
                    <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ $statistiques['autres'] ?? 0 }}</p>
                    <p class="text-xs text-red-500 dark:text-red-400">{{ $statistiques['pourcentage_autres'] ?? 0 }}%</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif