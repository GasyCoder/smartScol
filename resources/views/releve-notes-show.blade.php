<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <!-- En-tête avec boutons d'action -->
                <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Relevé de Notes - {{ $etudiant->nom }} {{ $etudiant->prenom }}
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $session->anneeUniversitaire->libelle ?? 'Année universitaire' }} - {{ $session->type }}
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('resultats.releve-notes.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Retour à la liste
                        </a>
                        <button onclick="window.print()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V9a2 2 0 00-2-2H9a2 2 0 00-2 2v8.309S9.91 23 10 23h4s.91-6.691 1-15z"></path>
                            </svg>
                            Imprimer
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Informations étudiant -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informations Étudiant</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="font-medium text-gray-900 dark:text-white">Nom et Prénoms</dt>
                                <dd class="mt-1 text-gray-700 dark:text-gray-300">{{ strtoupper($etudiant->nom) }} {{ ucfirst($etudiant->prenom) }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-900 dark:text-white">Matricule</dt>
                                <dd class="mt-1 text-gray-700 dark:text-gray-300">{{ $etudiant->matricule }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-900 dark:text-white">Niveau</dt>
                                <dd class="mt-1 text-gray-700 dark:text-gray-300">{{ $etudiant->niveau?->nom ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-900 dark:text-white">Parcours</dt>
                                <dd class="mt-1 text-gray-700 dark:text-gray-300">{{ $etudiant->parcours?->nom ?? 'Tronc Commun' }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Détail par UE -->
                    @foreach($ues_data as $ueData)
                        <div class="mb-6 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                            <div class="px-4 py-3 {{ $ueData['validee'] ? 'bg-green-50 dark:bg-green-900/20' : ($ueData['eliminees'] ? 'bg-red-50 dark:bg-red-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20') }}">
                                <div class="flex justify-between items-center">
                                    <h4 class="text-lg font-medium {{ $ueData['validee'] ? 'text-green-800 dark:text-green-300' : ($ueData['eliminees'] ? 'text-red-800 dark:text-red-300' : 'text-yellow-800 dark:text-yellow-300') }}">
                                        {{ $ueData['ue']->abr ? $ueData['ue']->abr . ' - ' : '' }}{{ $ueData['ue']->nom }}
                                    </h4>
                                    <div class="text-right">
                                        <span class="text-sm font-medium {{ $ueData['validee'] ? 'text-green-700 dark:text-green-400' : ($ueData['eliminees'] ? 'text-red-700 dark:text-red-400' : 'text-yellow-700 dark:text-yellow-400') }}">
                                            {{ $ueData['credits'] }} crédits - Moyenne: {{ number_format($ueData['moyenne_ue'], 2) }}/20
                                        </span>
                                        <div class="text-xs {{ $ueData['validee'] ? 'text-green-600 dark:text-green-500' : ($ueData['eliminees'] ? 'text-red-600 dark:text-red-500' : 'text-yellow-600 dark:text-yellow-500') }}">
                                            @if($ueData['validee'])
                                                ✓ VALIDÉE
                                            @elseif($ueData['eliminees'])
                                                ✗ ÉLIMINÉE
                                            @else
                                                ⚠ NON VALIDÉE
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Élément Constitutif (EC)
                                            </th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Note /20
                                            </th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Statut
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                        @foreach($ueData['notes_ec'] as $noteEC)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    <div>
                                                        {{ $noteEC['ec']->abr ? $noteEC['ec']->abr . ' - ' : '' }}{{ $noteEC['ec']->nom }}
                                                        @if($noteEC['ec']->enseignant)
                                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                Ens: {{ $noteEC['ec']->enseignant }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium {{ $noteEC['est_eliminatoire'] ? 'text-red-600 bg-red-50 dark:bg-red-900/20' : ($noteEC['note'] >= 10 ? 'text-green-600' : 'text-yellow-600') }}">
                                                    {{ number_format($noteEC['note'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                                    @if($noteEC['est_eliminatoire'])
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                                            ÉLIMINATOIRE
                                                        </span>
                                                    @elseif($noteEC['note'] >= 10)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                            VALIDÉ
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                                            NON VALIDÉ
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    <!-- Synthèse -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mt-6">
                        <h3 class="text-lg font-medium text-blue-900 dark:text-blue-300 text-center mb-4">SYNTHÈSE GÉNÉRALE</h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($synthese['moyenne_generale'], 2) }}/20</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Moyenne Générale</div>
                            </div>
                            <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $synthese['credits_valides'] }}/{{ $synthese['total_credits'] }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Crédits Validés</div>
                            </div>
                            <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($synthese['pourcentage_credits'], 1) }}%</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Pourcentage Crédits</div>
                            </div>
                            <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ count($ues_data) }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Nombre d'UE</div>
                            </div>
                        </div>

                        <!-- Décision -->
                        <div class="text-center p-4 rounded-lg font-bold text-lg 
                            {{ $synthese['decision'] === 'admis' ? 'bg-green-100 text-green-800 border-2 border-green-300 dark:bg-green-900/20 dark:text-green-300 dark:border-green-700' : '' }}
                            {{ $synthese['decision'] === 'rattrapage' ? 'bg-yellow-100 text-yellow-800 border-2 border-yellow-300 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-700' : '' }}
                            {{ $synthese['decision'] === 'redoublant' ? 'bg-orange-100 text-orange-800 border-2 border-orange-300 dark:bg-orange-900/20 dark:text-orange-300 dark:border-orange-700' : '' }}
                            {{ $synthese['decision'] === 'excluss' ? 'bg-red-100 text-red-800 border-2 border-red-300 dark:bg-red-900/20 dark:text-red-300 dark:border-red-700' : '' }}">
                            @switch($synthese['decision'])
                                @case('admis')
                                    🎉 DÉCISION: ADMIS(E)
                                    @break
                                @case('rattrapage')
                                    ⚠️ DÉCISION: AUTORISÉ(E) AU RATTRAPAGE
                                    @break
                                @case('redoublant')
                                    🔄 DÉCISION: AUTORISÉ(E) À REDOUBLER
                                    @break
                                @case('excluss')
                                    🚫 DÉCISION: EXCLU(E)
                                    @break
                                @default
                                    📋 DÉCISION: {{ strtoupper($synthese['decision']) }}
                            @endswitch
                        </div>

                        @if($synthese['has_note_eliminatoire'])
                            <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <p class="text-sm text-red-700 dark:text-red-300 text-center font-medium">
                                    ⚠️ Note éliminatoire détectée
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-600 text-center text-sm text-gray-500 dark:text-gray-400">
                        
                        <p class="mt-1">Ce document certifie les résultats obtenus par l'étudiant(e) pour la session {{ $session->type }}.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>