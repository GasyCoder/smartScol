{{--  resultats.partials.tab-process --}}
<div id="content-process" class="tab-content" x-show="$wire.activeTab === 'process'" style="{{ $activeTab !== 'process' ? 'display: none;' : '' }}">
    @include('livewire.resultats.partials.etapes')
    <!-- Guide d'étapes -->
    <div class="p-4 mt-6 border rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
        <h4 class="mb-2 text-base font-medium text-gray-900 dark:text-white">Guide d'étapes</h4>
        <div class="text-sm text-gray-600 dark:text-gray-300">
            @if($statut === 'initial')
                <div class="flex items-start">
                    <svg class="w-5 h-5 mt-0.5 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="ml-3">
                        <p><strong>Étape à suivre: Vérification de cohérence</strong><br>
                        Commencez par vérifier la cohérence entre les manchettes et les copies.</p>
                    </div>
                </div>
            @elseif($statut === 'verification')
                <div class="flex items-start">
                    <svg class="w-5 h-5 mt-0.5 text-yellow-500 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="ml-3">
                        <p><strong>Étape actuelle: Vérification de cohérence</strong><br>
                        Vérifiez le rapport de cohérence puis lancez la première fusion.</p>
                    </div>
                </div>
            @elseif($statut === 'fusion')
                <div class="flex items-start">
                    <svg class="w-5 h-5 mt-0.5 text-yellow-500 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                    </svg>
                    <div class="ml-3">
                        <p><strong>Étape actuelle: Fusion des données (étape {{ $etapeFusion }}/3)</strong><br>
                        @if($etapeFusion === 1)
                            Les manchettes et copies ont été associées. Lancez la deuxième fusion pour calculer les moyennes.
                        @elseif($etapeFusion === 2)
                            Les moyennes ont été calculées. Lancez la dernière fusion pour finaliser les résultats.
                        @elseif($etapeFusion === 3)
                            La fusion est complète. Vous pouvez maintenant vérifier et valider les résultats.
                        @else
                            La fusion n'a pas encore commencé. Lancez la première fusion.
                        @endif
                        </p>
                    </div>
                </div>
            @elseif($statut === 'validation')
                <div class="flex items-start">
                    <svg class="w-5 h-5 mt-0.5 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="ml-3">
                        <p><strong>Étape actuelle: Validation des résultats</strong><br>
                        Les résultats ont été validés. Vous pouvez maintenant les publier.
                        @if($estPACES)
                            <br><strong>Note:</strong> PACES 1ère année est considérée comme un concours sans délibération.
                        @endif
                        </p>
                    </div>
                </div>
            @elseif($statut === 'publie')
                <div class="flex items-start">
                    <svg class="w-5 h-5 mt-0.5 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <div class="ml-3">
                        <p><strong>Étape terminée: <span class="text-green-800 dark:text-green-400">Résultats publiés</span></strong><br>
                        Les résultats ont été publiés avec succès et sont maintenant accessibles.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@include('livewire.resultats.partials.modals')
