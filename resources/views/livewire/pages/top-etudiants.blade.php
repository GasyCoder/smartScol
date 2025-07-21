{{-- resources/views/livewire/pages/top-etudiants.blade.php --}}
<div class="col-span-12 md:col-span-8 lg:col-span-6 2xl:col-span-4">
    <div class="h-full bg-white border border-gray-300 rounded-md dark:bg-gray-950 dark:border-gray-900 shadow-lg">
        {{-- Header avec filtres --}}
        <div class="p-5 sm:p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3 gap-x-3">
                <h6 class="text-base font-bold font-heading -tracking-snug leading-tighter text-slate-700 dark:text-white">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        Majors par niveau & parcours
                    </span>
                </h6>
                
                {{-- Filtres --}}
                <div class="flex items-center space-x-2">
                    {{-- Filtre par niveau --}}
                    <div class="relative">
                        <select wire:model.live="selectedNiveau" 
                                class="text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tous niveaux</option>
                            @foreach($statistiquesNiveaux as $niveau)
                                <option value="{{ $niveau->id }}">{{ $niveau->abr }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Menu dropdown pour actions --}}
                    <div class="relative dropdown">
                        <button class="dropdown-toggle text-xs font-bold tracking-wide text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-all duration-300 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu absolute right-0 min-w-[160px] border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-lg shadow-lg hidden z-[1000]">
                            <div class="py-1">
                                <button wire:click="exportTopEtudiants" class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                                    <svg class="w-3 h-3 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Exporter PDF
                                </button>
                                <button wire:click="refreshTopEtudiants" class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                                    <svg class="w-3 h-3 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistiques rapides --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">{{ $topEtudiants->where('note', '>=', 18)->count() }}</div>
                    <div class="text-xs text-yellow-600/70 dark:text-yellow-400/70">Excellents</div>
                </div>
                <div class="text-center p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $topEtudiants->where('note', '>=', 16)->where('note', '<', 18)->count() }}</div>
                    <div class="text-xs text-green-600/70 dark:text-green-400/70">Très Bien</div>
                </div>
                <div class="text-center p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $topEtudiants->where('note', '>=', 14)->where('note', '<', 16)->count() }}</div>
                    <div class="text-xs text-blue-600/70 dark:text-blue-400/70">Bien</div>
                </div>
            </div>
        </div>

        {{-- Liste des top étudiants --}}
        <div class="p-5 sm:p-6">
            @if($topEtudiants->count() > 0)
                <div class="space-y-4 max-h-[400px] overflow-y-auto">
                    @foreach($topEtudiants->take(10) as $index => $resultat)
                        @php
                            $etudiant = $resultat->etudiant;
                            $note = $resultat->note ?? 0;
                            $mention = '';
                            $couleurMention = '';
                            $badgeSpecial = '';
                            
                            if ($note >= 18) {
                                $mention = 'Excellent';
                                $couleurMention = 'text-yellow-600 dark:text-yellow-400';
                                if ($index === 0) $badgeSpecial = 'Major Général';
                            } elseif ($note >= 16) {
                                $mention = 'Très Bien';
                                $couleurMention = 'text-green-600 dark:text-green-400';
                            } elseif ($note >= 14) {
                                $mention = 'Bien';
                                $couleurMention = 'text-blue-600 dark:text-blue-400';
                            } elseif ($note >= 12) {
                                $mention = 'Assez Bien';
                                $couleurMention = 'text-purple-600 dark:text-purple-400';
                            } else {
                                $mention = 'Passable';
                                $couleurMention = 'text-gray-600 dark:text-gray-400';
                            }
                            
                            // Générer initiales pour avatar
                            $initiales = '';
                            if ($etudiant) {
                                $nom = $etudiant->nom ?? '';
                                $prenom = $etudiant->prenom ?? '';
                                $initiales = strtoupper(substr($nom, 0, 1) . substr($prenom, 0, 1));
                            }
                        @endphp
                        
                        <div class="flex items-center p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200 group relative">
                            {{-- Rang --}}
                            <div class="flex-shrink-0 w-8 text-center">
                                @if($index < 3)
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white {{ $index === 0 ? 'bg-yellow-500' : ($index === 1 ? 'bg-gray-400' : 'bg-orange-500') }}">
                                        {{ $index + 1 }}
                                    </div>
                                @else
                                    <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ $index + 1 }}</span>
                                @endif
                            </div>

                            {{-- Avatar --}}
                            <div class="flex-shrink-0 w-11 ml-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                    {{ $initiales ?: 'ET' }}
                                </div>
                            </div>

                            {{-- Informations étudiant --}}
                            <div class="flex-grow ml-4">
                                <div class="text-base font-semibold text-slate-700 dark:text-white">
                                    {{ $etudiant->nom ?? 'Nom' }} {{ $etudiant->prenom ?? 'Prénom' }}
                                    
                                    {{-- Badge spécial pour le major --}}
                                    @if($badgeSpecial)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300 ml-2">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                            {{ $badgeSpecial }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm text-slate-400 flex items-center">
                                    <span>{{ $etudiant->niveau->nom ?? 'Niveau' }} - {{ $etudiant->parcour->nom ?? 'Parcours' }}</span>
                                    
                                    {{-- Badge niveau/parcours --}}
                                    @if($etudiant->niveau)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 ml-2">
                                            {{ $etudiant->niveau->abr }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Note et mention --}}
                            <div class="text-right ml-4">
                                <div class="text-base font-bold text-slate-700 dark:text-white">
                                    {{ number_format($note, 2) }} / 20
                                </div>
                                <div class="text-sm {{ $couleurMention }} font-medium">
                                    {{ $mention }}
                                </div>
                            </div>

                            {{-- Effet hover --}}
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"></div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer avec pagination --}}
                @if($topEtudiants->count() > 10)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Affichage de 10 sur {{ $topEtudiants->count() }} étudiants
                            </span>
                            <button wire:click="showAllTopEtudiants" 
                                    class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                Voir tous →
                            </button>
                        </div>
                    </div>
                @endif
            @else
                {{-- État vide --}}
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Aucun résultat</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Les meilleurs étudiants apparaîtront ici une fois les résultats publiés.
                    </p>
                    <button wire:click="refreshTopEtudiants" 
                            class="mt-4 px-4 py-2 text-sm bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/50 dark:text-blue-400 dark:hover:bg-blue-900 rounded-lg transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Actualiser
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Loading overlay --}}
<div wire:loading.delay wire:target="selectedNiveau,exportTopEtudiants,refreshTopEtudiants" 
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm mx-auto">
        <div class="flex items-center space-x-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-gray-700 dark:text-gray-300">Chargement...</span>
        </div>
    </div>
</div>

{{-- CSS personnalisé pour les animations --}}
@push('styles')
<style>
.dropdown:hover .dropdown-menu {
    display: block;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.group:hover {
    animation: slideIn 0.2s ease-out;
}
</style>
@endpush