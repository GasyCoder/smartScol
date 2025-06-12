{{-- Vue principale - Votre structure exacte avec modifications pour simulation --}}
<div class="p-6 bg-white rounded-lg shadow-sm dark:bg-gray-800">
    <!-- Header result -->
    @include('livewire.resultats.sessons.header-result')

    <!-- Statistiques compactes -->
    @include('livewire.resultats.sessons.statistique-result')

    <!-- Onglets modernes améliorés -->
    <div class="mb-6">
        <div class="inline-flex p-2 space-x-2 border shadow-inner bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800/80 dark:to-gray-700/80 rounded-2xl border-gray-200/50 dark:border-gray-600/50">

            <!-- Onglet Session 1 amélioré -->
            <button wire:click="$set('activeTab', 'session1')"
                    class="group relative flex items-center px-6 py-3.5 text-sm font-medium rounded-xl transition-all duration-300 transform hover:scale-[1.02] {{ $activeTab === 'session1' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-lg shadow-blue-500/20 ring-2 ring-blue-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80' }}">

                <!-- Indicateur actif animé -->
                @if($activeTab === 'session1')
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 rounded-xl"></div>
                @endif

                <div class="relative flex items-center">
                    <div class="mr-3 p-1.5 rounded-lg {{ $activeTab === 'session1' ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-gray-600 group-hover:bg-gray-200 dark:group-hover:bg-gray-500' }} transition-colors">
                        <em class="text-lg ni ni-graduation"></em>
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="font-semibold">Session 1</span>
                        <span class="text-xs opacity-75">Session Normale</span>
                    </div>

                    @if(!empty($resultatsSession1))
                        <span class="ml-3 px-2.5 py-1 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full font-medium border border-blue-200 dark:border-blue-700">
                            {{ count($resultatsSession1) }}
                        </span>
                    @endif

                    <!-- Indicateur de verrouillage stylisé -->
                    <div class="p-1 ml-2 bg-red-100 rounded-full dark:bg-red-900/30" title="Session verrouillée">
                        <em class="text-xs text-red-500 ni ni-lock dark:text-red-400"></em>
                    </div>
                </div>
            </button>

            <!-- Onglet Session 2 amélioré -->
            @if($showSession2)
                <button wire:click="$set('activeTab', 'session2')"
                        class="group relative flex items-center px-6 py-3.5 text-sm font-medium rounded-xl transition-all duration-300 transform hover:scale-[1.02] {{ $activeTab === 'session2' ? 'bg-white dark:bg-gray-700 text-green-600 dark:text-green-400 shadow-lg shadow-green-500/20 ring-2 ring-green-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80' }}">

                    @if($activeTab === 'session2')
                        <div class="absolute inset-0 bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-xl"></div>
                    @endif

                    <div class="relative flex items-center">
                        <div class="mr-3 p-1.5 rounded-lg {{ $activeTab === 'session2' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-gray-100 dark:bg-gray-600 group-hover:bg-gray-200 dark:group-hover:bg-gray-500' }} transition-colors">
                            <em class="text-lg ni ni-repeat"></em>
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="font-semibold">Session 2</span>
                            <span class="text-xs opacity-75">Rattrapage</span>
                        </div>

                        @if(!empty($resultatsSession2))
                            <span class="ml-3 px-2.5 py-1 text-xs bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full font-medium border border-green-200 dark:border-green-700">
                                {{ count($resultatsSession2) }}
                            </span>
                        @endif

                        <!-- Indicateur de verrouillage pour session 2 -->
                        <div class="p-1 ml-2 bg-red-100 rounded-full dark:bg-red-900/30" title="Session verrouillée">
                            <em class="text-xs text-red-500 ni ni-lock dark:text-red-400"></em>
                        </div>
                    </div>
                </button>
            @endif

            <!-- Onglet Simulation amélioré - MAINTENANT DISPONIBLE SI AU MOINS UNE SESSION A DES RÉSULTATS -->
            @if(!empty($resultatsSession1) || !empty($resultatsSession2))
                <button wire:click="$set('activeTab', 'simulation')"
                        class="group relative flex items-center px-6 py-3.5 text-sm font-medium rounded-xl transition-all duration-300 transform hover:scale-[1.02] {{ $activeTab === 'simulation' ? 'bg-white dark:bg-gray-700 text-purple-600 dark:text-purple-400 shadow-lg shadow-purple-500/20 ring-2 ring-purple-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80' }}">

                    @if($activeTab === 'simulation')
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-violet-500/10 rounded-xl"></div>
                    @endif

                    <div class="relative flex items-center">
                        <div class="mr-3 p-1.5 rounded-lg {{ $activeTab === 'simulation' ? 'bg-purple-100 dark:bg-purple-900/30' : 'bg-gray-100 dark:bg-gray-600 group-hover:bg-gray-200 dark:group-hover:bg-gray-500' }} transition-colors">
                            <em class="text-lg ni ni-setting"></em>
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="font-semibold">Simulation</span>
                            <span class="text-xs opacity-75">Délibération</span>
                        </div>

                        <!-- Badge indiquant les sessions disponibles pour simulation -->
                        <span class="ml-3 px-2.5 py-1 text-xs bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded-full font-medium border border-purple-200 dark:border-purple-700">
                            @if(!empty($resultatsSession1) && !empty($resultatsSession2))
                                S1+S2
                            @elseif(!empty($resultatsSession1))
                                S1
                            @else
                                S2
                            @endif
                        </span>
                    </div>
                </button>
            @endif
        </div>

        <!-- Actions contextuelles selon l'onglet actif - Améliorées -->
        <div class="flex items-center justify-between mt-6">
            <div class="flex items-center space-x-3">
                @if($activeTab === 'session1')
                    <div class="flex items-center px-4 py-2.5 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-xl dark:text-blue-300 dark:bg-blue-900/30 dark:border-blue-700 shadow-sm">
                        <div class="w-2 h-2 mr-3 bg-blue-400 rounded-full animate-pulse"></div>
                        <em class="mr-2 text-lg ni ni-eye"></em>
                        <span>Consultation Session Normale (Verrouillée)</span>
                    </div>
                @elseif($activeTab === 'session2')
                    <!-- SESSION RATTRAPAGE - AUSSI VERROUILLÉE -->
                    <div class="flex items-center px-4 py-2.5 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-xl dark:text-red-300 dark:bg-red-900/30 dark:border-red-700 shadow-sm">
                        <div class="w-2 h-2 mr-3 bg-red-400 rounded-full"></div>
                        <em class="mr-2 text-lg ni ni-lock"></em>
                        <span>Consultation Session Rattrapage (Verrouillée)</span>
                    </div>
                @elseif($activeTab === 'simulation')
                    <div class="flex items-center px-4 py-2.5 text-sm font-medium text-purple-700 bg-purple-50 border border-purple-200 rounded-xl dark:text-purple-300 dark:bg-purple-900/30 dark:border-purple-700 shadow-sm">
                        <div class="w-2 h-2 mr-3 bg-purple-400 rounded-full animate-pulse"></div>
                        <em class="mr-2 text-lg ni ni-bulb"></em>
                        <span>Mode Simulation Délibération</span>
                    </div>
                @endif
            </div>

            <!-- Actions rapides selon l'onglet - Améliorées -->
            <div class="flex items-center space-x-3">
                @if($activeTab === 'session2')
                    <!-- SESSION RATTRAPAGE AUSSI EN CONSULTATION SEULE -->
                    <!-- Pas de boutons d'ajout car session verrouillée -->
                @endif

                @if($activeTab === 'simulation')
                    <button wire:click="simulerDeliberation"
                            class="group flex items-center px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-violet-600 rounded-xl hover:from-purple-700 hover:to-violet-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <em class="mr-2 text-lg ni ni-play group-hover:animate-pulse"></em>
                        <span>Lancer Simulation</span>
                    </button>
                @endif

                <button wire:click="refreshData"
                        class="group flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700">
                    <em class="mr-2 text-lg transition-transform duration-300 ni ni-reload group-hover:rotate-180"></em>
                    <span>Actualiser</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Contenu des onglets --}}
    <div class="tab-content">
        <div class="animate-fadeIn">
            @include('livewire.resultats.sessons.normale')
            @include('livewire.resultats.sessons.rattrapage')
            @include('livewire.resultats.sessons.simulation')  <!-- déliberation -->
        </div>
    </div>
</div>


@push('styles')
{{-- Styles additionnels pour les icônes Dashlite et améliorations UI --}}
<style>
    /* Animations et transitions personnalisées */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }

    /* Assurer que les icônes Dashlite s'affichent correctement */
    .ni {
        font-family: 'Nunito', sans-serif;
        font-style: normal;
        font-weight: normal;
        line-height: 1;
        text-decoration: none;
        vertical-align: baseline;
        display: inline-block;
        transition: all 0.2s ease;
    }

    /* Améliorer la visibilité des indicateurs de session */
    .session-indicator {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    /* Styles pour sessions verrouillées */
    .session-locked {
        opacity: 0.8;
        pointer-events: none;
    }

    .session-locked-indicator {
        border: 2px solid #ef4444 !important;
        background: rgba(239, 68, 68, 0.05) !important;
    }

    .session-locked-indicator:hover {
        background: rgba(239, 68, 68, 0.1) !important;
    }

    /* Indicateur visuel pour consultation uniquement */
    .consultation-only {
        position: relative;
    }

    .consultation-only::after {
        content: '🔒';
        position: absolute;
        top: -2px;
        right: -2px;
        font-size: 10px;
    }

    /* Améliorations des ombres et effets */
    .shadow-inner {
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
    }

    .shadow-lg {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .shadow-xl {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Effet hover pour les boutons avec transformation */
    .hover\:scale-\[1\.02\]:hover {
        transform: scale(1.02);
    }

    .hover\:scale-105:hover {
        transform: scale(1.05);
    }

    /* Amélioration des transitions pour tous les éléments */
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 300ms;
    }

    /* Ring focus amélioré */
    .focus\:ring-2:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
        box-shadow: 0 0 0 2px var(--ring-color, rgba(59, 130, 246, 0.5));
    }

    /* Amélioration des gradients */
    .bg-gradient-to-r {
        background-image: linear-gradient(to right, var(--tw-gradient-stops));
    }

    /* Personalisation des scrollbars */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 0, 0, 0.3);
    }

    .dark ::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .dark ::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
    }

    .dark ::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
</style>
@endpush

@push('scripts')
{{-- Scripts avec support dark mode et logique de simulation POUR TOUTES LES SESSIONS --}}
<script>
    document.addEventListener('livewire:init', () => {
        // Gestion du mode sombre
        if (localStorage.getItem('darkMode') === 'true' ||
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }

        // Animation d'entrée pour les onglets
        const animateTabSwitch = () => {
            const tabContent = document.querySelector('.tab-content > div');
            if (tabContent) {
                tabContent.classList.remove('animate-fadeIn');
                void tabContent.offsetWidth; // Force reflow
                tabContent.classList.add('animate-fadeIn');
            }
        };

        // Notifications améliorées avec design moderne
        const showNotification = (message, type = 'info', duration = 4000) => {
            const notification = document.createElement('div');
            const isDark = document.documentElement.classList.contains('dark');

            let config;
            switch(type) {
                case 'success':
                    config = {
                        bg: isDark ? 'bg-green-600' : 'bg-green-500',
                        text: 'text-white',
                        icon: 'ni-check-circle',
                        border: 'border-green-400'
                    };
                    break;
                case 'error':
                    config = {
                        bg: isDark ? 'bg-red-600' : 'bg-red-500',
                        text: 'text-white',
                        icon: 'ni-cross-circle',
                        border: 'border-red-400'
                    };
                    break;
                case 'warning':
                    config = {
                        bg: isDark ? 'bg-orange-600' : 'bg-orange-500',
                        text: 'text-white',
                        icon: 'ni-alert-circle',
                        border: 'border-orange-400'
                    };
                    break;
                case 'simulation':
                    config = {
                        bg: isDark ? 'bg-purple-600' : 'bg-purple-500',
                        text: 'text-white',
                        icon: 'ni-setting',
                        border: 'border-purple-400'
                    };
                    break;
                default:
                    config = {
                        bg: isDark ? 'bg-blue-600' : 'bg-blue-500',
                        text: 'text-white',
                        icon: 'ni-info-circle',
                        border: 'border-blue-400'
                    };
            }

            notification.className = `fixed top-4 right-4 ${config.bg} ${config.text} px-6 py-4 rounded-xl shadow-lg z-50 transform transition-all duration-300 translate-x-full border ${config.border} backdrop-blur-sm`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <em class="mr-3 ni ${config.icon} text-lg"></em>
                    <span class="font-medium">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white/80 hover:text-white">
                        <em class="text-sm ni ni-cross"></em>
                    </button>
                </div>
            `;
            document.body.appendChild(notification);

            // Animation d'entrée
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Suppression automatique
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }, duration);
        };

        // NOUVEAUX ÉVÉNEMENTS : Écouter les événements Livewire spécifiques aux simulations POUR TOUTES LES SESSIONS
        Livewire.on('simulation-complete', (event) => {
            const stats = event[0] || event;
            const sessionType = stats.session_type || 'délibération';
            const changements = stats.changements || 0;
            const total = stats.total || 0;

            showNotification(
                `Simulation ${sessionType} terminée ! ${changements} changements détectés sur ${total} étudiants.`,
                'simulation',
                6000
            );
            animateTabSwitch();
        });

        // Événements existants améliorés
        Livewire.on('note-added', () => {
            showNotification('Note ajoutée avec succès !', 'success');
        });

        Livewire.on('note-updated', () => {
            showNotification('Note modifiée avec succès !', 'success');
        });

        Livewire.on('session-locked', () => {
            showNotification('Session verrouillée - Consultation uniquement', 'warning');
        });

        Livewire.on('note-view-only', () => {
            showNotification('Session verrouillée - Consultation des notes uniquement', 'warning');
        });

        Livewire.on('session-switched', (event) => {
            const sessionType = event.session || event[0]?.session;
            const message = sessionType === 'session2' ?
                'Session de rattrapage - Mode consultation (Verrouillée)' :
                sessionType === 'simulation' ?
                'Mode simulation délibération activé' :
                'Mode consultation - Session normale verrouillée';
            showNotification(message, 'info');
            animateTabSwitch();
        });

        Livewire.on('data-refreshed', () => {
            showNotification('Données actualisées', 'info', 2000);
        });

        // NOUVEAUX ÉVÉNEMENTS : Gestion des erreurs de simulation
        Livewire.on('simulation-error', (event) => {
            const error = event[0] || event;
            showNotification(`Erreur simulation : ${error.message || 'Erreur inconnue'}`, 'error', 8000);
        });

        // Fonction de notification améliorée
        function showBasicNotification(message, type = 'info') {
            showNotification(message, type);
        }

        // Mise à jour automatique du mode sombre selon les préférences système
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('darkMode')) {
                if (e.matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        });

        // Fonctions globales disponibles
        window.showNotification = showNotification;
    });

    // Fonctions globales pour l'interface - SIMULATION POUR TOUTES LES SESSIONS
    window.toggleDarkMode = function() {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
    };

    // Fonctions d'export (à connecter avec Livewire)
    window.exportSession = function(session, format) {
        if (format === 'excel') {
            Livewire.dispatch('exportResults', { session: session });
        } else if (format === 'pdf') {
            Livewire.dispatch('exportPDF', { session: session });
        }
    };

    window.exportComparative = function() {
        Livewire.dispatch('exportComparative');
    };

    // Gestion des onglets avec état
    window.switchTab = function(tab) {
        Livewire.dispatch('switchTab', { tab: tab });
    };

    // Actions contextuelles - SIMULATION AMÉLIORÉE
    window.openAddNoteModal = function() {
        Livewire.dispatch('openNoteModal');
    };

    window.refreshData = function() {
        Livewire.dispatch('refreshData');
    };

    window.runSimulation = function() {
        Livewire.dispatch('simulerDeliberation');
    };

    // NOUVELLES FONCTIONS pour simulation avec choix de session
    window.simulerSession1 = function() {
        if (confirm('Simuler la délibération pour la Session 1 (Normale) ?')) {
            Livewire.dispatch('simulerDeliberationSession', { sessionType: 'session1' });
        }
    };

    window.simulerSession2 = function() {
        if (confirm('Simuler la délibération pour la Session 2 (Rattrapage) ?')) {
            Livewire.dispatch('simulerDeliberationSession', { sessionType: 'session2' });
        }
    };

    // Fonctions spécifiques existantes conservées
    window.forceUnlockSession2 = function() {
        if (confirm('ATTENTION ! Cela va FORCER le déverrouillage de la session rattrapage en ignorant TOUS les statuts de publication. Continuer ?')) {
            Livewire.dispatch('forceUnlockSession2');
        }
    };

    window.resetSession2ToEditable = function() {
        if (confirm('Êtes-vous sûr de vouloir RESET la session 2 en mode éditable ? Cela va ignorer tous les verrouillages.')) {
            Livewire.dispatch('resetSession2ToEditable');
        }
    };

    window.unlockSession2Completely = function() {
        if (confirm('Êtes-vous sûr de vouloir déverrouiller complètement la session de rattrapage ? Cela permettra toutes les modifications.')) {
            Livewire.dispatch('unlockSession2Completely');
        }
    };

    window.addMultipleNotesSession2 = function() {
        Livewire.dispatch('addMultipleNotes');
    };

    window.importNotesSession2 = function() {
        Livewire.dispatch('importNotesSession2');
    };

    // Fonction de confirmation pour les actions critiques
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };
</script>
@endpush
