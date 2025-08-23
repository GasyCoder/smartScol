<!-- Vue Principale RENFORCÉE -->
<div>
    <div class="container px-4 py-6 mx-auto">
        <!-- Vérification initiale des données -->

            <!-- En-tête fixe avec titre et actions globales AMÉLIORÉ -->
            <div class="sticky top-0 z-10 px-5 py-4 mb-6 bg-white border-b border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800 rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <!-- Titre principal avec indicateurs de session et sécurité -->
                    <div class="flex flex-wrap items-center gap-3 min-w-0">
                        <!-- Badge de session -->
                        @if($currentSessionType)
                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full whitespace-nowrap
                                {{ $currentSessionType === 'Normale'
                                    ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-200 dark:border-green-700'
                                    : 'bg-orange-100 text-orange-800 border border-orange-200 dark:bg-orange-900 dark:text-orange-200 dark:border-orange-700'
                                }}">
                                Session {{ $currentSessionType }}
                                @if($sessionActive && $sessionActive->date_debut)
                                    <span class="ml-1 text-xs opacity-75">
                                        ({{ \Carbon\Carbon::parse($sessionActive->date_debut)->format('d/m/Y') }})
                                    </span>
                                @endif
                            </span>
                        @endif

                    </div>
                    <!-- Actions globales CORRIGÉES -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Bouton corbeille -->
                        <a href="{{ route('copies.corbeille') }}" 
                           class="inline-flex items-center py-1.5 px-3 text-sm font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 whitespace-nowrap">
                           <svg class="w-4 h-4 mr-1 text-gray-600 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                           </svg>
                            Corbeille
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reste du contenu avec corrections de responsive -->
            <div class="space-y-6">
                <!-- Tableau des copies -->
                <div class="overflow-hidden">
                    @include('livewire.copie.copies-table')
                </div>
            </div>

     
    </div>
</div>