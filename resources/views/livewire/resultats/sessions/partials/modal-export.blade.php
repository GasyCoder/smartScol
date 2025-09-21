{{-- ✅ MODAL D'EXPORT AVEC PROTECTION CONTRE LES ERREURS --}}
@if($showExportModal ?? false)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="export-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"
                 wire:click="fermerModalExport"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Content --}}
            <div class="inline-block w-full max-w-4xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full dark:bg-blue-900">
                                @if(($exportType ?? 'pdf') === 'pdf')
                                    <em class="text-red-600 ni ni-file-pdf dark:text-red-400"></em>
                                @else
                                    <em class="text-green-600 ni ni-file-excel dark:text-green-400"></em>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" id="export-modal-title">
                                    Configuration Export {{ strtoupper($exportType ?? 'PDF') }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Source : {{ ucfirst($exportData ?? 'simulation') }}
                                    @if(($exportData ?? '') === 'simulation' && !empty($simulationDeliberation))
                                        ({{ ($simulationDeliberation['total_etudiants'] ?? 0) }} étudiants)
                                    @endif
                                </p>
                            </div>
                        </div>
                        <button wire:click="fermerModalExport"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <em class="text-xl ni ni-times"></em>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 overflow-y-auto max-h-96">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                        {{-- ✅ SECTION 1: Sélection des Colonnes --}}
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                <em class="mr-2 ni ni-list"></em>
                                Colonnes à Exporter
                            </h4>

                            <div class="space-y-3">
                                {{-- Rang --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['rang'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.rang"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Rang</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Numérotation des étudiants par ordre de classement</div>
                                    </div>
                                </label>

                                {{-- Nom et Prénom --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['nom_complet'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.nom_complet"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Nom et Prénom</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Nom complet de l'étudiant</div>
                                    </div>
                                </label>

                                {{-- Matricule --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['matricule'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.matricule"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Matricule</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Numéro d'identification de l'étudiant</div>
                                    </div>
                                </label>

                                {{-- Moyenne --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['moyenne'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.moyenne"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Moyenne Générale</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Moyenne générale de l'étudiant (/20)</div>
                                    </div>
                                </label>

                                {{-- Crédits --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['credits'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.credits"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Crédits</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Crédits validés / Total des crédits</div>
                                    </div>
                                </label>

                                {{-- Décision --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['decision'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.decision"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Décision</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Décision académique (Admis, Rattrapage, etc.)</div>
                                    </div>
                                </label>

                                {{-- Niveau (optionnel) --}}
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ (($exportConfig['colonnes']['niveau'] ?? false)) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="checkbox"
                                           wire:model="exportConfig.colonnes.niveau"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">Niveau</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Niveau d'études (optionnel)</div>
                                    </div>
                                </label>
                            </div>

                            {{-- Actions rapides pour les colonnes --}}
                            <div class="flex gap-2 pt-3 border-t border-gray-200 dark:border-gray-600">
                                <button wire:click="selectionnerToutesColonnes"
                                        class="px-3 py-2 text-xs text-blue-700 bg-blue-100 rounded hover:bg-blue-200 dark:text-blue-300 dark:bg-blue-900/50 dark:hover:bg-blue-900">
                                    Tout sélectionner
                                </button>
                                <button wire:click="deselectionnerToutesColonnes"
                                        class="px-3 py-2 text-xs text-gray-700 bg-gray-100 rounded hover:bg-gray-200 dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600">
                                    Tout désélectionner
                                </button>
                                <button wire:click="resetConfigExport"
                                        class="px-3 py-2 text-xs text-orange-700 bg-orange-100 rounded hover:bg-orange-200 dark:text-orange-300 dark:bg-orange-900/50 dark:hover:bg-orange-900">
                                    Configuration par défaut
                                </button>
                            </div>
                        </div>

                        {{-- ✅ SECTION 2: Filtres et Tri --}}
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                <em class="mr-2 ni ni-filter"></em>
                                Filtres et Tri
                            </h4>

                            {{-- Filtre par décision --}}
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Filtrer par décision
                                </label>
                                <select wire:model="exportConfig.filtres.decision_filter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="tous">Toutes les décisions</option>
                                    <option value="admis">Admis uniquement</option>
                                    <option value="rattrapage">Rattrapage uniquement</option>
                                    <option value="redoublant">Redoublants uniquement</option>
                                    <option value="exclus">Exclus uniquement</option>
                                </select>
                            </div>

                            {{-- Filtre par moyenne --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Moyenne minimum
                                    </label>
                                    <input type="number" step="0.01" min="0" max="20"
                                           wire:model="exportConfig.filtres.moyenne_min"
                                           placeholder="0.00"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Moyenne maximum
                                    </label>
                                    <input type="number" step="0.01" min="0" max="20"
                                           wire:model="exportConfig.filtres.moyenne_max"
                                           placeholder="20.00"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            {{-- Tri --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Trier par
                                    </label>
                                    <select wire:model="exportConfig.tri.champ"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="rang">Rang</option>
                                        <option value="nom_complet">Nom</option>
                                        <option value="moyenne_generale">Moyenne</option>
                                        <option value="credits_valides">Crédits</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Ordre
                                    </label>
                                    <select wire:model="exportConfig.tri.ordre"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="asc">Croissant</option>
                                        <option value="desc">Décroissant</option>
                                    </select>
                                </div>
                            </div>

                            {{-- ✅ APERÇU DES STATISTIQUES AVEC PROTECTION --}}
                            @php
                                // Protéger l'appel à getStatistiquesExportPreview
                                try {
                                    $statsPreview = method_exists($this, 'getStatistiquesExportPreview') ? $this->getStatistiquesExportPreview() : null;
                                } catch (\Exception $e) {
                                    $statsPreview = null;
                                }
                            @endphp
                            @if($statsPreview && is_array($statsPreview))
                                <div class="p-3 bg-gray-100 rounded-lg dark:bg-gray-700">
                                    <h5 class="mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Aperçu des données
                                    </h5>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div>Total initial: {{ ($statsPreview['total_initial'] ?? 0) }}</div>
                                        <div>Après filtres: {{ ($statsPreview['total_filtre'] ?? 0) }}</div>
                                        @if(($statsPreview['total_filtre'] ?? 0) > 0)
                                            <div>Moy. min: {{ number_format(($statsPreview['moyenne_min'] ?? 0), 2) }}</div>
                                            <div>Moy. max: {{ number_format(($statsPreview['moyenne_max'] ?? 0), 2) }}</div>
                                        @endif
                                    </div>
                                    @if(!empty($statsPreview['decisions']) && is_array($statsPreview['decisions']))
                                        <div class="grid grid-cols-2 gap-1 mt-2 text-xs">
                                            @foreach($statsPreview['decisions'] as $decision => $count)
                                                @if($count > 0)
                                                    <div>{{ ucfirst($decision) }}: {{ $count }}</div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Messages d'erreur --}}
                    @if($errors->has('export'))
                        <div class="p-3 mt-4 text-red-700 bg-red-100 border border-red-400 rounded dark:text-red-300 dark:bg-red-900/50 dark:border-red-800">
                            {{ $errors->first('export') }}
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Format: <strong>{{ strtoupper($exportType ?? 'PDF') }}</strong> •
                            Source: <strong>{{ ucfirst($exportData ?? 'simulation') }}</strong>
                            @if(isset($statsPreview) && is_array($statsPreview))
                                • <strong>{{ ($statsPreview['total_filtre'] ?? 0) }}</strong> résultats sélectionnés
                            @endif
                        </div>
                        <div class="flex space-x-3">
                            <button wire:click="fermerModalExport"
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500 dark:hover:bg-gray-500">
                                Annuler
                            </button>
                            <button wire:click="genererExportAvecConfig"
                                    type="button"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                @if(($exportType ?? 'pdf') === 'pdf')
                                    <em class="mr-2 ni ni-file-pdf"></em>
                                @else
                                    <em class="mr-2 ni ni-file-excel"></em>
                                @endif
                                Générer {{ strtoupper($exportType ?? 'PDF') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif