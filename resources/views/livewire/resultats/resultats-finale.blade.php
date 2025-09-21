{{-- Vue principale simplifiée avec includes conservés --}}
<div class="p-6 bg-white rounded-lg shadow-sm dark:bg-gray-800">
    
    {{-- Header avec filtres (vue partielle) --}}
    @include('livewire.resultats.sessions.header-result')

    {{-- Statistiques compactes - SEULEMENT SI ON A DES RÉSULTATS CHARGÉS --}}
    @if((!empty($resultatsSession1) || !empty($resultatsSession2)))
        @include('livewire.resultats.sessions.statistique-result')
    @endif

    {{-- Onglets modernes - SEULEMENT SI DONNÉES CHARGÉES ET AFFICHABLES --}}
    @if((!empty($resultatsSession1) || (!empty($resultatsSession2) && $showSession2)))
        @include('livewire.resultats.sessions.partials.tabs-result')

        {{-- Contenu des onglets - SEULEMENT SI ON A DES RÉSULTATS --}}
        <div class="tab-content">
            <div class="animate-fadeIn">
                @include('livewire.resultats.sessions.normale')
                @include('livewire.resultats.sessions.rattrapage')
                @include('livewire.resultats.sessions.simulation')
            </div>
        </div>
    @endif

    {{-- ✅ CORRECTION : Message d'information si pas de résultats après chargement --}}
    @if($etape_actuelle === 'resultats' && empty($resultatsSession1) && empty($resultatsSession2))
        <div class="p-8 text-center bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-yellow-100 dark:bg-yellow-900/30 rounded-full">
                <em class="text-2xl text-yellow-600 dark:text-yellow-400 ni ni-alert-circle"></em>
            </div>
            <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2">
                Aucun résultat trouvé
            </h3>
            <p class="text-yellow-700 dark:text-yellow-300 mb-4">
                Aucun résultat d'examen n'a été trouvé pour les critères sélectionnés.
                <br>
                <strong>Niveau :</strong> {{ $nom_niveau_selectionne ?? 'Non défini' }}
                <br>
                <strong>Parcours :</strong> {{ $nom_parcours_selectionne ?? 'Non défini' }}
                <br>
                <strong>Année :</strong> {{ $nom_annee_selectionnee ?? 'Non définie' }}
            </p>
            <div class="flex justify-center space-x-3">
                <button wire:click="selectNiveau(null)" 
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-yellow-800 bg-yellow-100 border border-yellow-300 rounded-lg hover:bg-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-200 dark:border-yellow-700 dark:hover:bg-yellow-900/50 transition-colors">
                    <em class="mr-2 ni ni-reload"></em>
                    Modifier les critères
                </button>
                <button wire:click="chargerResultats" 
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 transition-colors">
                    <em class="mr-2 ni ni-refresh"></em>
                    Recharger
                </button>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    {{-- Scripts pour améliorer l'UX --}}
    <script>
        // Animation de fade-in pour les onglets
        document.addEventListener('livewire:navigated', function () {
            const tabContent = document.querySelector('.tab-content');
            if (tabContent) {
                tabContent.style.opacity = '0';
                setTimeout(() => {
                    tabContent.style.transition = 'opacity 0.3s ease-in-out';
                    tabContent.style.opacity = '1';
                }, 100);
            }
        });

        // Smooth scroll vers les résultats après chargement
        window.addEventListener('resultatsActualises', function() {
            setTimeout(() => {
                const statsSection = document.querySelector('[data-section="statistiques"]');
                if (statsSection) {
                    statsSection.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }
            }, 500);
        });
    </script>
@endpush