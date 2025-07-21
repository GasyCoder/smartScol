{{-- resources/views/livewire/resultats/verification-resultats.blade.php --}}

<div>
    <div class="container px-4 py-6 mx-auto">
        @if($examen && $sessionActive)
            <div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-sm dark:bg-gray-800 sm:rounded-lg dark:border-gray-700">
                <div class="p-4 sm:p-6">
                    <!-- Filtres de sélection -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-medium text-gray-900 dark:text-white">Filtres de sélection</h3>
                        <div class="flex items-center space-x-3">
                            <button
                                wire:click="resetToExamenValues"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
                            >
                                <em class="mr-2 icon ni ni-reload"></em>
                                Réinitialiser
                            </button>
                            <a
                                href="{{ route('resultats.fusion', ['examenId' => $examenId]) }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
                            >
                                <em class="mr-2 icon ni ni-shuffle"></em>
                                Retour à la Fusion
                            </a>
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

                    <!-- Filtres -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- Niveau -->
                        <div>
                            <label for="niveau_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Niveau</label>
                            <select id="niveau_id" wire:model.live="niveau_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Sélectionner un niveau</option>
                                @foreach($niveaux as $niveau)
                                    <option value="{{ $niveau->id }}">{{ $niveau->abr }} - {{ $niveau->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Parcours -->
                        <div>
                            <label for="parcours_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parcours</label>
                            <select id="parcours_id" wire:model.live="parcours_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" {{ count($parcours) ? '' : 'disabled' }}>
                                <option value="">Sélectionner un parcours</option>
                                @foreach($parcours as $parcour)
                                    <option value="{{ $parcour->id }}">{{ $parcour->abr }} - {{ $parcour->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Matière (EC) -->
                        <div>
                            <label for="ec_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Matière</label>
                            <select id="ec_id" wire:model.live="ec_id" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" {{ count($ecs) ? '' : 'disabled' }}>
                                <option value="">Sélectionner une matière</option>
                                @foreach($ecs as $ec)
                                    <option value="{{ $ec->id }}">{{ isset($ec->abr) ? $ec->abr . ' - ' : '' }}{{ $ec->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- Recherche -->
                    <div class="mt-4">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recherche</label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <em class="text-gray-400 icon ni ni-search"></em>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="search" id="search" class="block w-full py-2 pl-10 pr-3 border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        </div>
                    </div>
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

        <!-- Messages pour les résultats -->
        @if($noExamenFound)
            <div class="p-6 text-center bg-red-100 rounded-lg">
                <em class="mb-4 text-4xl text-red-400 icon ni ni-alert"></em>
                <p class="text-sm text-red-600 dark:text-red-500">
                    Aucun examen trouvé pour le niveau et le parcours sélectionnés dans la session active.
                </p>
            </div>
        @elseif($showVerification)
            <!-- Section principale : Contrôles et tableau -->
            <div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-sm dark:bg-gray-800 sm:rounded-lg dark:border-gray-700">
                <!-- En-tête avec contrôles -->
                <div class="px-4 py-4 border-b border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Vérification des Résultats
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ $totalResultats ?? count($resultats) }} résultat(s)
                                ({{ $resultatsVerifies ?? collect($resultats)->where('is_checked', true)->count() }} vérifiés,
                                {{ $resultatsNonVerifies ?? collect($resultats)->where('is_checked', false)->count() }} en attente)
                            </p>
                        </div>

                        <!-- Contrôles à droite -->
                        <div class="flex items-center space-x-4">
                            <!-- Switch moyennes UE -->
                            <div class="flex items-center px-3 py-2 space-x-3 bg-white border rounded-lg dark:bg-gray-800 dark:border-gray-600">
                                <label for="switch-moyennes-ue" class="text-sm font-medium text-gray-700 cursor-pointer dark:text-gray-300">
                                    Moyennes UE
                                </label>
                                <div class="relative">
                                    <input
                                        type="checkbox"
                                        id="switch-moyennes-ue"
                                        wire:model.live="afficherMoyennesUE"
                                        class="sr-only"
                                    >
                                    <div class="block w-11 h-6 rounded-full cursor-pointer transition-colors duration-200 {{ $afficherMoyennesUE ? 'bg-blue-600' : 'bg-gray-400' }}"
                                        onclick="document.getElementById('switch-moyennes-ue').click()">
                                    </div>
                                    <div class="dot absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition-transform duration-200 {{ $afficherMoyennesUE ? 'transform translate-x-5' : '' }}">
                                    </div>
                                </div>
                                <span class="text-xs font-medium px-2 py-1 rounded {{ $afficherMoyennesUE ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $afficherMoyennesUE ? 'ON' : 'OFF' }}
                                </span>
                            </div>

                            <!-- Boutons d'export -->
                            <div class="flex space-x-2">
                                <button wire:click="exportExcel"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                                        wire:loading.attr="disabled">
                                    <em class="mr-1 icon ni ni-file-xls"></em>
                                    Excel
                                    <span wire:loading wire:target="exportExcel" class="ml-2 animate-spin icon ni ni-loader"></span>
                                </button>
                                <button wire:click="exportPdf('landscape')"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                        wire:loading.attr="disabled">
                                    <em class="mr-1 icon ni ni-file-pdf"></em>
                                    PDF
                                    <span wire:loading wire:target="exportPdf" class="ml-2 animate-spin icon ni ni-loader"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Info sur le mode moyennes UE -->
                    @if($afficherMoyennesUE)
                        <div class="p-2 mt-3 text-xs text-blue-700 bg-blue-100 border border-blue-200 rounded dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800">
                            <em class="mr-1 icon ni ni-info"></em>
                            <strong>Mode AVEC moyennes UE :</strong> Les exports incluront les moyennes par UE, moyenne générale et récapitulatif des crédits.
                        </div>
                    @endif
                </div>

                <!-- Inclusion de la vue partielle du tableau -->
                @if(count($resultats) > 0)
                    @include('livewire.resultats.partials.table-verify-resultat')
                @else
                    <div class="p-6 text-center bg-gray-100 rounded-lg dark:bg-gray-700">
                        <em class="mb-4 text-4xl text-gray-400 icon ni ni-folder-close dark:text-gray-400"></em>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Aucun résultat à vérifier pour les critères sélectionnés.</p>
                    </div>
                @endif

                <!-- Actions en bas -->
                @if(count($resultats) > 0)
                    <div class="px-4 py-4 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 sm:px-6">
                        <div class="flex items-center justify-between">
                            <!-- Progression -->
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                    <em class="icon ni ni-activity"></em>
                                    <span>Progression: {{ $pourcentageVerification ?? (count($resultats) > 0 ? round((collect($resultats)->where('is_checked', true)->count() / count($resultats)) * 100) : 0) }}%</span>
                                </div>
                                <div class="w-32 h-2 bg-gray-200 rounded-full dark:bg-gray-600">
                                    <div class="h-2 transition-all duration-300 bg-blue-600 rounded-full" style="width: {{ $pourcentageVerification ?? (count($resultats) > 0 ? round((collect($resultats)->where('is_checked', true)->count() / count($resultats)) * 100) : 0) }}%"></div>
                                </div>
                            </div>

                            <!-- Bouton de vérification en masse -->
                            @php
                                $nonVerifies = $resultatsNonVerifies ?? collect($resultats)->where('is_checked', false)->count();
                            @endphp
                            @if($nonVerifies > 0)
                                <button wire:click="marquerTousVerifies"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        wire:loading.attr="disabled">
                                    <em class="mr-2 icon ni ni-check-thick"></em>
                                    Marquer tous comme vérifiés ({{ $nonVerifies }})
                                    <span wire:loading wire:target="marquerTousVerifies" class="ml-2 animate-spin icon ni ni-loader"></span>
                                </button>
                            @else
                                <div class="inline-flex items-center px-4 py-2 text-sm font-medium text-green-700 bg-green-100 border border-green-200 rounded-md dark:bg-green-800 dark:text-green-200">
                                    <em class="mr-2 icon ni ni-check-circle-fill"></em>
                                    Tous les résultats sont vérifiés
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="p-6 text-center bg-red-100 rounded-lg">
                <em class="mb-4 text-4xl text-red-400 icon ni ni-alert"></em>
                <p class="text-sm text-red-600 dark:text-red-500">Veuillez d'abord effectuer la première étape de la fusion pour voir les résultats à vérifier.</p>
            </div>
        @endif

        <!-- Indicateur de chargement global -->
        <div wire:loading.flex wire:target="exportExcel,exportPdf,marquerTousVerifies,afficherMoyennesUE" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
                <div class="flex items-center space-x-3">
                    <em class="text-2xl text-blue-600 animate-spin icon ni ni-loader"></em>
                    <span class="text-lg font-medium text-gray-900 dark:text-white">Traitement en cours...</span>
                </div>
            </div>
        </div>
    </div>

<style>
/* CSS pour le switch moyennes UE */
.dot {
    transition: transform 0.2s ease-in-out;
}

#switch-moyennes-ue:focus + div {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Animation des bordures de tableau */
.table-hover tr:hover {
    background-color: rgba(59, 130, 246, 0.05);
}

/* Amélioration de la lisibilité */
.table-striped tbody tr:nth-child(even) {
    background-color: rgba(0, 0, 0, 0.02);
}

.dark .table-striped tbody tr:nth-child(even) {
    background-color: rgba(255, 255, 255, 0.02);
}

/* Animation de la barre de progression */
.progress-bar {
    transition: width 0.6s ease;
}

/* Styles pour les badges de notes */
.note-badge {
    transition: all 0.2s ease;
}

.note-badge:hover {
    transform: scale(1.05);
}
</style>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation fluide du switch
    const switchElement = document.getElementById('switch-moyennes-ue');
    if (switchElement) {
        switchElement.addEventListener('change', function() {
            // Petit feedback visuel
            const container = this.parentElement;
            container.style.transform = 'scale(0.98)';
            setTimeout(() => {
                container.style.transform = 'scale(1)';
            }, 100);
        });
    }

    // Animation des lignes de tableau
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.02}s`;
        row.classList.add('animate-fade-in');
    });

    // Notification toast lors du changement de mode
    Livewire.on('moyennesUEToggled', (isActivated) => {
        const message = isActivated
            ? 'Mode moyennes UE activé - Les exports incluront les calculs UE'
            : 'Mode moyennes UE désactivé - Exports simples sans calculs';

        // Toast notification si disponible
        if (typeof toastr !== 'undefined') {
            toastr.info(message);
        }
    });
});

// CSS pour l'animation fade-in
const style = document.createElement('style');
style.textContent = `
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}
`;
document.head.appendChild(style);
</script>
