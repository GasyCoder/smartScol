{{-- resources/views/livewire/resultats/resultats-verification.blade.php --}}
<div>
    <div class="container px-4 py-6 mx-auto">
        @include('livewire.resultats.partials.section-filtre')

        <!-- Messages pour les résultats -->
        @if($noExamenFound)
            <div class="p-6 text-center bg-red-100 rounded-lg dark:bg-red-800/20">
                <em class="mb-4 text-4xl text-red-400 icon ni ni-alert"></em>
                <p class="text-sm text-red-600 dark:text-red-400">
                    Aucun examen trouvé pour le niveau et le parcours sélectionnés dans la session active.
                </p>
            </div>
        @elseif($showVerification)
            <!-- Info de pagination et performance en haut -->
            @if($paginationInfo['has_pagination'])
                <div class="mb-4 p-3 from-blue-50 to-indigo-50 border border-blue-200 rounded-lg shadow-sm dark:from-blue-900/20 dark:to-indigo-900/20 dark:border-blue-700">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center space-x-4">
                            <div class="text-blue-700 dark:text-blue-300">
                                <em class="mr-1 icon ni ni-list-index"></em>
                                Affichage {{ $paginationInfo['from'] }} à {{ $paginationInfo['to'] }} sur {{ number_format($paginationInfo['total']) }}
                            </div>
                            <!-- Indicateur de performance -->
                            <div class="text-xs text-green-600 dark:text-green-400">
                                <em class="mr-1 icon ni ni-activity-round"></em>
                                {{ round(memory_get_usage(true) / 1024 / 1024, 1) }}MB utilisé
                            </div>
                        </div>
                        
                        <!-- Sélecteur de taille de page amélioré -->
                        <div class="flex items-center space-x-3">
                            <label class="text-xs font-medium text-blue-600 dark:text-blue-400">Résultats par page:</label>
                            <select wire:model.live="perPage" 
                                    class="text-xs bg-white border-blue-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:border-blue-600 dark:text-white">
                                <option value="25">25</option>
                                <option value="50">50 (recommandé)</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Section principale : Contrôles et résultats améliorés -->
            <div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-lg dark:bg-gray-800 sm:rounded-xl dark:border-gray-800">
                <!-- En-tête avec contrôles améliorés -->
                <div class="px-6 py-4 border-b border-gray-200 from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <em class="mr-2 text-blue-600 icon ni ni-check-square"></em>
                                Vérification des Résultats
                                @if($paginationInfo['has_pagination'])
                                    <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                                        (Page {{ $paginationInfo['current_page'] }}/{{ $paginationInfo['max_pages'] }})
                                    </span>
                                @endif
                            </h3>
                            <div class="flex items-center mt-1 space-x-4 text-sm">
                                <div class="text-gray-600 dark:text-gray-400">
                                    <em class="mr-1 icon ni ni-files"></em>
                                    {{ number_format($totalResultats ?? count($resultats)) }} résultat(s) total
                                    @if($paginationInfo['has_pagination'])
                                        - {{ count($resultats) }} affichés
                                    @endif
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                        <em class="mr-1 icon ni ni-check-circle"></em>
                                        {{ $resultatsVerifies ?? collect($resultats)->where('is_checked', true)->count() }} vérifiés
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-orange-800 bg-orange-100 rounded-full dark:bg-orange-900 dark:text-orange-200">
                                        <em class="mr-1 icon ni ni-clock"></em>
                                        {{ $resultatsNonVerifies ?? collect($resultats)->where('is_checked', false)->count() }} en attente
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Contrôles à droite améliorés -->
                        <div class="flex items-center space-x-4">
                            <!-- Switch moyennes UE stylisé -->
                            <div class="flex items-center px-4 py-2 space-x-3 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-600">
                                <label for="switch-moyennes-ue" class="text-sm font-medium text-gray-700 cursor-pointer dark:text-gray-300">
                                    <em class="mr-1 icon ni ni-bar-chart"></em>
                                    Moyennes UE
                                </label>
                                <div class="relative">
                                    <input
                                        type="checkbox"
                                        id="switch-moyennes-ue"
                                        wire:model.live="afficherMoyennesUE"
                                        class="sr-only"
                                        {{ $afficherMoyennesUE ? 'checked' : '' }}
                                    >
                                    <div class="block w-12 h-6 rounded-full cursor-pointer transition-all duration-300 {{ $afficherMoyennesUE ? 'bg-blue-600 shadow-md' : 'bg-gray-400' }}"
                                        wire:click="toggleMoyennesUE">
                                    </div>
                                    <div class="dot absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition-all duration-300 shadow-sm {{ $afficherMoyennesUE ? 'transform translate-x-6' : '' }}">
                                    </div>
                                </div>
                                <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $afficherMoyennesUE ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ $afficherMoyennesUE ? 'ON' : 'OFF' }}
                                </span>
                            </div>

                            <!-- Avertissement pour gros exports stylisé -->
                            @if($totalResultats > 1000)
                                <div class="flex items-center px-3 py-2 space-x-2 bg-amber-50 border border-amber-200 rounded-lg shadow-sm dark:bg-amber-900/20 dark:border-amber-700">
                                    <em class="text-amber-600 icon ni ni-alert-circle dark:text-amber-400"></em>
                                    <span class="text-xs font-medium text-amber-700 dark:text-amber-400">
                                        Export volumineux ({{ number_format($totalResultats) }} résultats)
                                    </span>
                                </div>
                            @endif

                            <!-- Bouton d'export Excel uniquement -->
                            <div class="flex space-x-2">
                                <button wire:click="exportExcel"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-200 bg-green-600 rounded-lg shadow-md hover:bg-green-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 {{ $totalResultats > 5000 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        wire:loading.attr="disabled"
                                        {{ $totalResultats > 5000 ? 'disabled title="Trop de résultats - Filtrez pour réduire la taille"' : '' }}>
                                    <em class="mr-2 icon ni ni-file-xls"></em>
                                    Export Excel
                                    @if($totalResultats > 1000)
                                        <span class="ml-1 text-xs opacity-75">({{ number_format($totalResultats) }})</span>
                                    @endif
                                    <span wire:loading wire:target="exportExcel" class="ml-2 animate-spin icon ni ni-loader"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Info sur le mode moyennes UE améliorée -->
                    @if($afficherMoyennesUE)
                        <div class="p-3 mt-4 text-sm text-blue-700 bg-blue-50 border border-blue-200 rounded-lg shadow-sm dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800">
                            <div class="flex items-start space-x-2">
                                <em class="mt-0.5 icon ni ni-info-fill"></em>
                                <div>
                                    <strong>Mode AVEC moyennes UE activé :</strong>
                                    <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                                        Les exports incluront les moyennes par UE et récapitulatif des crédits.
                                        Calculs optimisés en lot pour de meilleures performances.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Section des résultats avec votre vue partielle améliorée -->
                @if(count($resultats) > 0)
                    <!-- Inclusion de votre vue partielle existante améliorée -->
                    <div class="bg-white dark:bg-gray-800">
                        @include('livewire.resultats.partials.table-verify-resultat')
                    </div>
                @else
                    <!-- Message vide stylisé -->
                    <div class="p-12 text-center bg-gray-50 dark:bg-gray-800">
                        <div class="flex flex-col items-center space-y-4">
                            <div class="p-4 bg-gray-100 rounded-full dark:bg-gray-700">
                                <em class="text-4xl text-gray-400 icon ni ni-folder-close dark:text-gray-500"></em>
                            </div>
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">Aucun résultat trouvé</h4>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Aucun résultat à vérifier pour les critères sélectionnés.
                                </p>
                            </div>
                            @if($search || $ec_id || $enseignant_id)
                                <button wire:click="resetToExamenValues" 
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 dark:bg-blue-900/20 dark:border-blue-700 dark:text-blue-400">
                                    <em class="mr-2 icon ni ni-reload"></em>
                                    Réinitialiser les filtres
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Pagination améliorée -->
                @if($paginationInfo['has_pagination'])
                    <div class="px-6 py-4 border-t border-gray-200 from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <!-- Info pagination stylisée -->
                            <div class="flex items-center space-x-4 text-sm text-gray-700 dark:text-gray-300">
                                <div class="flex items-center space-x-2">
                                    <em class="icon ni ni-eye"></em>
                                    <span>Affichage de <span class="font-semibold">{{ $paginationInfo['from'] }}</span>
                                    à <span class="font-semibold">{{ $paginationInfo['to'] }}</span>
                                    sur <span class="font-semibold text-blue-600">{{ number_format($paginationInfo['total']) }}</span> résultats</span>
                                </div>
                                @if($paginationInfo['total'] > 1000)
                                    <div class="text-xs text-amber-600 dark:text-amber-400">
                                        <em class="mr-1 icon ni ni-info"></em>
                                        Volume important - Pagination optimisée
                                    </div>
                                @endif
                            </div>

                            <!-- Navigation pagination stylisée -->
                            <div class="flex items-center space-x-1">
                                <!-- Page précédente -->
                                <button wire:click="previousPage"
                                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 focus:z-10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                        {{ $paginationInfo['current_page'] <= 1 ? 'disabled' : '' }}>
                                    <em class="mr-1 icon ni ni-chevron-left"></em>
                                    Précédent
                                </button>

                                <!-- Numéros de pages -->
                                @php
                                    $start = max(1, $paginationInfo['current_page'] - 2);
                                    $end = min($paginationInfo['max_pages'], $paginationInfo['current_page'] + 2);
                                @endphp

                                @if($start > 1)
                                    <button wire:click="gotoPage(1)"
                                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:z-10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                        1
                                    </button>
                                    @if($start > 2)
                                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">...</span>
                                    @endif
                                @endif

                                @for($i = $start; $i <= $end; $i++)
                                    <button wire:click="gotoPage({{ $i }})"
                                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium border focus:z-10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                            {{ $i === $paginationInfo['current_page'] 
                                                ? 'z-10 bg-blue-600 border-blue-600 text-white shadow-sm' 
                                                : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                        {{ $i }}
                                    </button>
                                @endfor

                                @if($end < $paginationInfo['max_pages'])
                                    @if($end < $paginationInfo['max_pages'] - 1)
                                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">...</span>
                                    @endif
                                    <button wire:click="gotoPage({{ $paginationInfo['max_pages'] }})"
                                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:z-10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                        {{ $paginationInfo['max_pages'] }}
                                    </button>
                                @endif

                                <!-- Page suivante -->
                                <button wire:click="nextPage"
                                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-50 focus:z-10 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                        {{ $paginationInfo['current_page'] >= $paginationInfo['max_pages'] ? 'disabled' : '' }}>
                                    Suivant
                                    <em class="ml-1 icon ni ni-chevron-right"></em>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions en bas améliorées -->
                @if(count($resultats) > 0)
                    <div class="px-6 py-4 border-t border-gray-200 from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <!-- Progression stylisée -->
                            <div class="flex items-center space-x-6">
                                <div class="flex items-center space-x-3 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-center space-x-2">
                                        <em class="text-blue-600 icon ni ni-activity dark:text-blue-400"></em>
                                        <span class="font-medium">Progression :</span>
                                        <span class="font-bold text-lg text-blue-600 dark:text-blue-400">
                                            {{ $pourcentageVerification ?? (count($resultats) > 0 ? round((collect($resultats)->where('is_checked', true)->count() / count($resultats)) * 100) : 0) }}%
                                        </span>
                                    </div>
                                </div>
                                <!-- Barre de progression améliorée -->
                                <div class="flex items-center space-x-3">
                                    <div class="w-40 h-3 bg-gray-200 rounded-full shadow-inner dark:bg-gray-600">
                                        <div class="h-3 transition-all duration-500 ease-out from-blue-500 to-blue-600 rounded-full shadow-sm" 
                                             style="width: {{ $pourcentageVerification ?? (count($resultats) > 0 ? round((collect($resultats)->where('is_checked', true)->count() / count($resultats)) * 100) : 0) }}%"></div>
                                    </div>
                                    @if($paginationInfo['has_pagination'])
                                        <span class="text-xs text-gray-500 dark:text-gray-400">(Page actuelle)</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Bouton de vérification en masse amélioré -->
                            @php
                                $nonVerifies = $resultatsNonVerifies ?? collect($resultats)->where('is_checked', false)->count();
                            @endphp
                            @if($nonVerifies > 0)
                                <button wire:click="marquerTousVerifies"
                                        class="inline-flex items-center px-6 py-3 text-sm font-semibold text-white transition-all duration-200 from-blue-600 to-blue-700 rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105"
                                        wire:loading.attr="disabled">
                                    <em class="mr-2 icon ni ni-check-thick"></em>
                                    @if($paginationInfo['has_pagination'])
                                        Marquer cette page comme vérifiée
                                        <span class="ml-2 text-xs bg-blue-500 px-2 py-1 rounded-full">{{ collect($resultats)->where('is_checked', false)->count() }}</span>
                                    @else
                                        Marquer tous comme vérifiés
                                        <span class="ml-2 text-xs bg-blue-500 px-2 py-1 rounded-full">{{ $nonVerifies }}</span>
                                    @endif
                                    <span wire:loading wire:target="marquerTousVerifies" class="ml-2 animate-spin icon ni ni-loader"></span>
                                </button>
                            @else
                                <div class="inline-flex items-center px-6 py-3 text-sm font-semibold text-green-700 from-green-100 to-green-50 border border-green-200 rounded-lg shadow-sm dark:from-green-900/20 dark:to-green-800/20 dark:text-green-200 dark:border-green-700">
                                    <em class="mr-2 text-green-600 icon ni ni-check-circle-fill dark:text-green-400"></em>
                                    @if($paginationInfo['has_pagination'])
                                        Cette page est entièrement vérifiée
                                    @else
                                        Tous les résultats sont vérifiés
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @else
            <!-- Message d'erreur stylisé -->
            <div class="p-8 text-center bg-red-50 border border-red-200 rounded-lg shadow-sm dark:bg-red-900/20 dark:border-red-700">
                <div class="flex flex-col items-center space-y-4">
                    <div class="p-3 bg-red-100 rounded-full dark:bg-red-900/50">
                        <em class="text-4xl text-red-500 icon ni ni-alert dark:text-red-400"></em>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-red-800 dark:text-red-200">Action requise</h4>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">
                            Veuillez d'abord effectuer la première étape de la fusion pour voir les résultats à vérifier.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Monitoring performance en mode debug -->
        @if(config('app.debug'))
            <div class="mt-6 p-4 bg-gray-100 border border-gray-200 rounded-lg text-xs text-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="flex items-center space-x-2">
                        <em class="icon ni ni-cpu"></em>
                        <div>
                            <div class="font-semibold">Mémoire utilisée</div>
                            <div class="text-blue-600">{{ round(memory_get_usage(true) / 1024 / 1024, 2) }}MB</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <em class="icon ni ni-growth"></em>
                        <div>
                            <div class="font-semibold">Pic mémoire</div>
                            <div class="text-orange-600">{{ round(memory_get_peak_usage(true) / 1024 / 1024, 2) }}MB</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <em class="icon ni ni-list"></em>
                        <div>
                            <div class="font-semibold">Résultats chargés</div>
                            <div class="text-green-600">{{ count($resultats) }} / {{ $totalResultats }}</div>
                        </div>
                    </div>
                    @if($paginationInfo['has_pagination'])
                        <div class="flex items-center space-x-2">
                            <em class="icon ni ni-copy"></em>
                            <div>
                                <div class="font-semibold">Pagination</div>
                                <div class="text-purple-600">Page {{ $paginationInfo['current_page'] }}/{{ $paginationInfo['max_pages'] }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Indicateur de chargement global amélioré -->
        <div wire:loading.flex 
             wire:target="exportExcel,marquerTousVerifies,afficherMoyennesUE,gotoPage,nextPage,previousPage,updatedPerPage,toggleOrder,startEditing,saveChanges,resetToExamenValues" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm">
            <div class="p-8 bg-white rounded-xl shadow-2xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col items-center space-y-4">
                    <div class="relative">
                        <em class="text-4xl text-blue-600 animate-spin icon ni ni-loader"></em>
                        <div class="absolute inset-0 bg-blue-200 rounded-full animate-ping opacity-20"></div>
                    </div>
                    <div class="text-center">
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">Exportation en cours...</span>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Veuillez patienter pendant l'export de fichier Excel.
                        </p>
                    </div>
                    <!-- Indicateur spécifique selon l'action -->
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        <span wire:loading.inline wire:target="exportExcel">Génération de l'export Excel...</span>
                        <span wire:loading.inline wire:target="marquerTousVerifies">Vérification en masse...</span>
                        <span wire:loading.inline wire:target="gotoPage,nextPage,previousPage">Chargement de la page...</span>
                        <span wire:loading.inline wire:target="saveChanges">Sauvegarde de la note...</span>
                        <span wire:loading.inline wire:target="updatedPerPage">Mise à jour de la pagination...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Styles CSS optimisés -->
    <style>
    /* Transition fluide pour le switch moyennes UE */
    .dot {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #switch-moyennes-ue:focus + div {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Optimisation des animations */
    .table-hover tr:hover {
        background-color: rgba(59, 130, 246, 0.04);
        transform: translateX(2px);
        transition: all 0.2s ease;
    }

    /* Animation de la barre de progression améliorée */
    .progress-bar {
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: width;
    }

    /* Styles pour les badges optimisés */
    .badge-hover {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform;
    }

    .badge-hover:hover {
        transform: scale(1.03);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Animation d'entrée pour les éléments */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.4s ease-out forwards;
    }

    /* Amélioration des boutons */
    .btn-enhanced {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform, box-shadow;
    }

    .btn-enhanced:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    /* Optimisation pour les écrans tactiles */
    @media (hover: none) and (pointer: coarse) {
        .btn-enhanced:hover {
            transform: none;
        }
        
        .table-hover tr:hover {
            transform: none;
        }
    }

    /* Optimisation GPU */
    .gpu-optimized {
        transform: translateZ(0);
        will-change: transform;
    }
    </style>

    <!-- Scripts JavaScript optimisés et corrigés -->
    <script>
    // Variables globales pour le nettoyage
    window.resultatsVerificationObservers = [];
    window.resultatsVerificationTimeouts = [];

    document.addEventListener('DOMContentLoaded', function() {
        // Configuration des optimisations
        const ANIMATION_DELAY = 20;
        const THROTTLE_DELAY = 100;

        // Fonctions utilitaires (définies en premier)
        function throttle(func, delay) {
            let timeoutId;
            let lastExecTime = 0;
            return function (...args) {
                const currentTime = Date.now();
                
                if (currentTime - lastExecTime > delay) {
                    func.apply(this, args);
                    lastExecTime = currentTime;
                } else {
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(() => {
                        func.apply(this, args);
                        lastExecTime = Date.now();
                    }, delay - (currentTime - lastExecTime));
                }
            };
        }

        function debounce(func, delay) {
            let timeoutId;
            return function (...args) {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => func.apply(this, args), delay);
            };
        }

        // Custom setTimeout tracker
        const originalSetTimeout = window.setTimeout;
        window.setTimeout = function(fn, delay) {
            const id = originalSetTimeout(fn, delay);
            window.resultatsVerificationTimeouts.push(id);
            return id;
        };
        
        // Animation fluide du switch avec throttling
        const switchElement = document.getElementById('switch-moyennes-ue');
        if (switchElement) {
            const throttledSwitchHandler = throttle(function() {
                const container = this.parentElement;
                if (container) {
                    container.style.transform = 'scale(0.97)';
                    setTimeout(() => {
                        if (container) {
                            container.style.transform = 'scale(1)';
                        }
                    }, 150);
                }
            }, THROTTLE_DELAY);
            
            switchElement.addEventListener('change', throttledSwitchHandler);
        }

        // Optimisation : Lazy loading avec Intersection Observer
        const observerOptions = {
            root: null,
            rootMargin: '50px',
            threshold: 0.1
        };

        const animationObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        if (entry.target) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                            entry.target.classList.add('animate-fade-in-up');
                        }
                    }, index * ANIMATION_DELAY);
                    animationObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Stocker l'observer pour le nettoyage
        window.resultatsVerificationObservers.push(animationObserver);

        // Appliquer le lazy loading optimisé
        const animateElements = document.querySelectorAll('.animate-on-scroll, .btn-enhanced, tbody tr');
        animateElements.forEach((element) => {
            if (element) {
                element.style.opacity = '0';
                element.style.transform = 'translateY(10px)';
                element.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                animationObserver.observe(element);
            }
        });

        // Notification toast optimisée avec debouncing
        const debouncedToast = debounce((message) => {
            if (typeof toastr !== 'undefined') {
                toastr.info(message, '', {
                    timeOut: 3000,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    showMethod: 'slideDown',
                    hideMethod: 'slideUp'
                });
            }
        }, 300);

        // Event listener pour Livewire
        if (typeof Livewire !== 'undefined') {
            Livewire.on('moyennesUEToggled', (isActivated) => {
                const message = isActivated
                    ? 'Mode moyennes UE activé - Calculs UE optimisés'
                    : 'Mode moyennes UE désactivé - Affichage simplifié';
                debouncedToast(message);
            });
        }

        // Monitoring de performance optimisé
        if (window.performance && window.performance.memory) {
            const logMemoryUsage = throttle(() => {
                try {
                    const memory = window.performance.memory;
                    const used = (memory.usedJSHeapSize / 1024 / 1024).toFixed(2);
                    
                    if (used > 100) {
                        console.warn(`⚠️ Utilisation mémoire élevée: ${used}MB`);
                    }
                } catch (e) {
                    // Ignore les erreurs de monitoring
                }
            }, 5000);

            // Activer seulement en mode debug
            if (document.querySelector('[data-debug="true"]') || 
                window.location.search.includes('debug=true') || 
                document.body.classList.contains('debug-mode')) {
                const memoryInterval = setInterval(logMemoryUsage, 10000);
                window.resultatsVerificationTimeouts.push(memoryInterval);
            }
        }

        // Gestion des erreurs réseau
        const networkHandlers = {
            online: () => debouncedToast('Connexion rétablie'),
            offline: () => debouncedToast('Connexion perdue - Les modifications seront synchronisées lors de la reconnexion')
        };

        window.addEventListener('online', networkHandlers.online);
        window.addEventListener('offline', networkHandlers.offline);

        // Stocker les handlers pour le nettoyage
        window.resultatsVerificationNetworkHandlers = networkHandlers;
    });

    // Nettoyage mémoire amélioré pour Livewire
    document.addEventListener('livewire:navigating', () => {
        try {
            // Nettoyer les observateurs
            if (window.resultatsVerificationObservers && window.resultatsVerificationObservers.length > 0) {
                window.resultatsVerificationObservers.forEach(observer => {
                    if (observer && typeof observer.disconnect === 'function') {
                        observer.disconnect();
                    }
                });
                window.resultatsVerificationObservers = [];
            }
            
            // Nettoyer les timeouts/intervals
            if (window.resultatsVerificationTimeouts && window.resultatsVerificationTimeouts.length > 0) {
                window.resultatsVerificationTimeouts.forEach(id => {
                    clearTimeout(id);
                    clearInterval(id);
                });
                window.resultatsVerificationTimeouts = [];
            }

            // Nettoyer les event listeners réseau
            if (window.resultatsVerificationNetworkHandlers) {
                window.removeEventListener('online', window.resultatsVerificationNetworkHandlers.online);
                window.removeEventListener('offline', window.resultatsVerificationNetworkHandlers.offline);
                window.resultatsVerificationNetworkHandlers = null;
            }
            
            // Restaurer setTimeout original
            if (window.originalSetTimeout) {
                window.setTimeout = window.originalSetTimeout;
            }
            
            // Forcer le garbage collection si disponible
            if (window.gc && typeof window.gc === 'function') {
                window.gc();
            }
        } catch (e) {
            console.warn('Erreur lors du nettoyage:', e);
        }
    });

    // Gestion intelligente du focus pour l'accessibilité
    document.addEventListener('livewire:morph-added', () => {
        try {
            const activeElement = document.activeElement;
            if (activeElement && activeElement.hasAttribute && activeElement.hasAttribute('wire:key')) {
                setTimeout(() => {
                    const wireKey = activeElement.getAttribute('wire:key');
                    if (wireKey) {
                        const newElement = document.querySelector(`[wire\\:key="${wireKey}"]`);
                        if (newElement && typeof newElement.focus === 'function') {
                            newElement.focus();
                        }
                    }
                }, 50);
            }
        } catch (e) {
            // Ignore les erreurs de focus
        }
    });

    // Réinitialisation après les mises à jour Livewire
    document.addEventListener('livewire:morph-updated', () => {
        setTimeout(() => {
            // Réappliquer les animations aux nouveaux éléments
            const newElements = document.querySelectorAll('tbody tr:not(.animate-fade-in-up)');
            newElements.forEach((element, index) => {
                if (element) {
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(10px)';
                    element.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    
                    setTimeout(() => {
                        if (element) {
                            element.style.opacity = '1';
                            element.style.transform = 'translateY(0)';
                            element.classList.add('animate-fade-in-up');
                        }
                    }, index * 20);
                }
            });
        }, 50);
    });
    </script>
</div>