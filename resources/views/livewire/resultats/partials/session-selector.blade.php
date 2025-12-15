<div class="flex items-center space-x-6">
    <!-- Année Universitaire avec Sélecteur -->
    <div class="min-w-[220px]">
        <label for="annee-select" class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
            Année Universitaire
        </label>
        <select 
            id="annee-select"
            wire:model.live="selectedAnneeUniv" 
            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
            @foreach($anneesUniversitaires as $annee)
                <option value="{{ $annee->id }}">{{ $annee->getLibelleAttribute() }}</option>
            @endforeach
        </select>
    </div>
    
    <!-- Séparateur -->
    <div class="h-12 w-px bg-gray-300 dark:bg-gray-600"></div>
    
    <!-- Session avec Sélecteur -->
    <div class="min-w-[220px]">
        <label for="session-select" class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
            Session d'Examen
        </label>
        <select 
            id="session-select"
            wire:model.live="selectedSession" 
            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm"
            @if($sessions->isEmpty()) disabled @endif>
            @if($sessions->isEmpty())
                <option value="">Aucune session disponible</option>
            @else
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}">
                        Session {{ $session->type }}
                        @if($session->type === 'Normale')
                            📘
                        @elseif($session->type === 'Rattrapage')
                            📙
                        @endif
                    </option>
                @endforeach
            @endif
        </select>
    </div>
    
    <!-- Séparateur -->
    <div class="h-12 w-px bg-gray-300 dark:bg-gray-600"></div>
    
    <!-- Badge visuel de la session active -->
    <div class="flex flex-col items-start">
        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
            Statut Session
        </span>
        @if($sessionType)
            @if($sessionType === 'Normale')
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Première Session
                </span>
            @elseif($sessionType === 'Rattrapage')
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                    </svg>
                    Session Rattrapage
                </span>
            @endif
        @else
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                Aucune session
            </span>
        @endif
    </div>
</div>