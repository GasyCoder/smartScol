<div>
    @if(empty($resultats) || !isset($uesStructure))
        <div class="text-center py-8 text-slate-500 dark:text-slate-400">
            Aucune donnée à afficher
        </div>
    @else
        <div class="overflow-x-auto shadow-lg rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900/30 dark:to-gray-800/50">
                    <tr>
                        <th rowspan="2" class="px-4 py-3 text-xs font-bold text-gray-900 dark:text-gray-100 uppercase border-b-2 border-r border-gray-300 dark:border-gray-600">
                            Rang
                        </th>
                        <th rowspan="2" class="px-4 py-3 text-xs font-bold text-gray-900 dark:text-gray-100 uppercase border-b-2 border-r border-gray-300 dark:border-gray-600">
                            Matricule
                        </th>
                        {{-- ✅ NOUVEAU : Colonne NOM séparée --}}
                        <th rowspan="2" class="px-4 py-3 text-xs font-bold text-gray-900 dark:text-gray-100 uppercase border-b-2 border-r border-gray-300 dark:border-gray-600">
                            Nom
                        </th>
                        {{-- ✅ NOUVEAU : Colonne PRÉNOM séparée --}}
                        <th rowspan="2" class="px-4 py-3 text-xs font-bold text-gray-900 dark:text-gray-100 uppercase border-b-2 border-r border-gray-300 dark:border-gray-600">
                            Prénom
                        </th>

                        {{-- UEs - ✅ CONSERVÉ : ABR + NOM --}}
                        @foreach($uesStructure as $ueStructure)
                            @php
                                $ue = $ueStructure['ue'];
                                $nbEcs = $ueStructure['ecs']->count() + 1;
                            @endphp
                            <th colspan="{{ $nbEcs }}" 
                                class="px-3 py-2 text-xs font-bold text-center text-primary-700 dark:text-primary-300 uppercase bg-primary-50 dark:bg-primary-900/20 border-b-2 border-r border-primary-300 dark:border-primary-700">
                                <div class="flex flex-col items-center gap-1">
                                    {{-- ✅ CONSERVÉ : Abréviation --}}
                                    <span class="text-sm font-black">{{ $ue->abr }}</span>
                                    {{-- ✅ AJOUTÉ : Nom complet --}}
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $ue->nom }}</span>
                                    {{-- Crédits --}}
                                    <span class="text-xs bg-primary-600 dark:bg-primary-700 text-white px-2 py-0.5 rounded-full font-bold">{{ $ue->credits }}cr</span>
                                </div>
                            </th>
                        @endforeach

                        <th rowspan="2" class="px-4 py-3 text-xs font-bold text-center text-gray-900 dark:text-gray-100 uppercase border-b-2 border-r border-gray-300 dark:border-gray-600">
                            Crédits<br>Validés
                        </th>
                        <th rowspan="2" class="px-4 py-3 text-xs font-bold text-center text-gray-900 dark:text-gray-100 uppercase border-b-2 border-r border-gray-300 dark:border-gray-600">
                            Moyenne<br>Générale
                        </th>
                        <th rowspan="2" class="px-4 py-3 text-xs font-bold text-center text-gray-900 dark:text-gray-100 uppercase border-b-2 border-gray-300 dark:border-gray-600">
                            Décision
                        </th>
                    </tr>
                    <tr>
                        @foreach($uesStructure as $ueStructure)
                            @foreach($ueStructure['ecs'] as $ec)
                                <th class="px-2 py-2 text-xs font-medium text-gray-900 dark:text-gray-100 uppercase border-b-2 border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50"
                                    title="{{ $ec->nom }}{{ $ec->enseignant ? ' - ' . $ec->enseignant : '' }}">
                                    <div class="flex flex-col items-center gap-1">
                                        {{-- Abréviation EC --}}
                                        <span class="font-black text-primary-600 dark:text-primary-400">{{ $ec->abr }}</span>
                                        
                                        {{-- Nom EC --}}
                                        <span class="text-xs font-bold text-gray-600 dark:text-slate-400">
                                            {{ Str::limit($ec->nom, 30) }}
                                        </span>
                                        
                                        {{-- NOM ENSEIGNANT - TOUJOURS VISIBLE --}}
                                        @if(!empty($ec->enseignant))
                                            <span class="text-xs italic text-amber-600 dark:text-amber-400 font-semibold">
                                                {{ Str::limit($ec->enseignant, 30) }}
                                            </span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                            <th class="px-2 py-2 text-xs font-black text-gray-900 dark:text-gray-100 uppercase bg-gray-200 dark:bg-gray-700 border-b-2 border-r border-gray-300 dark:border-gray-600">
                                Moy
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach($resultats as $index => $resultat)
                        @php
                            $decision = $resultat['decision'] ?? 'non_definie';
                            $bgDecision = match($decision) {
                                'admis' => 'bg-green-50 dark:bg-green-900/10',
                                'redoublant' => 'bg-yellow-50 dark:bg-yellow-900/10',
                                'exclus' => 'bg-red-50 dark:bg-red-900/10',
                                default => 'bg-white dark:bg-gray-800'
                            };
                        @endphp
                        <tr class="hover:bg-{{ $couleur }}-50 dark:hover:bg-{{ $couleur }}-900/20 transition-colors {{ $bgDecision }}">
                            
                            {{-- Rang avec indicateur --}}
                            <td class="px-4 py-3 text-center border-r border-gray-200 dark:border-gray-700 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="text-base font-bold text-gray-900 dark:text-gray-100">{{ $index + 1 }}</span>
                                    @if($resultat['est_redoublant'])
                                        <span class="text-xs px-1.5 py-0.5 bg-yellow-500 text-white rounded-md font-bold shadow-sm" title="Redoublant">R</span>
                                    @else
                                        <span class="text-xs px-1.5 py-0.5 bg-cyan-500 text-white rounded-md font-bold shadow-sm" title="Nouveau">N</span>
                                    @endif
                                </div>
                            </td>
                            
                            {{-- Matricule --}}
                            <td class="px-4 py-3 text-center border-r border-gray-200 dark:border-gray-700 font-mono text-sm">
                                <span class="{{ $resultat['est_redoublant'] ? 'text-yellow-600 dark:text-yellow-400 font-black' : 'text-cyan-600 dark:text-cyan-400 font-black' }}">
                                    {!! $this->surlignerTexte($resultat['etudiant']->matricule, $recherche) !!}
                                </span>
                            </td>
                            
                            <td class="px-4 py-3 border-r border-gray-200 dark:border-gray-700">
                                <div class="font-bold text-gray-900 dark:text-gray-100 uppercase">
                                    {!! $this->surlignerTexte($resultat['etudiant']->nom, $recherche) !!}
                                </div>
                            </td>

                            <td class="px-4 py-3 border-r border-gray-200 dark:border-gray-700">
                                <div class="font-semibold text-slate-700 dark:text-slate-300">
                                    {!! $this->surlignerTexte(ucwords(strtolower($resultat['etudiant']->prenom)), $recherche) !!}
                                </div>
                            </td>

                            {{-- Notes par UE --}}
                            @foreach($uesStructure as $ueStructure)
                                @php
                                    $ueId = $ueStructure['ue']->id;
                                    $ueDetails = collect($resultat['resultats_ue'])->firstWhere('ue_id', $ueId);
                                @endphp

                                {{-- Notes ECs --}}
                                @foreach($ueStructure['ecs'] as $ec)
                                    <td class="px-2 py-3 text-center border-r border-gray-200 dark:border-gray-700">
                                        @php
                                            $note = isset($resultat['notes'][$ec->id]) ? $resultat['notes'][$ec->id]->note : null;
                                        @endphp
                                        @if($note !== null)
                                            <span class="font-bold {{ $note >= 10 ? 'text-green-600 dark:text-green-400' : ($note == 0 ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400') }}">
                                                {{ number_format($note, 2) }}
                                                @if($note == 0) 
                                                    <span class="text-base ml-0.5">⚠️</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Moyenne UE --}}
                                <td class="px-2 py-3 text-center border-r border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700/50">
                                    @if($ueDetails)
                                        @php
                                            $moyenneUE = $ueDetails['moyenne_ue'] ?? 0;
                                            $ueValidee = $ueDetails['ue_validee'] ?? $ueDetails['validee'] ?? false;
                                            $creditsUE = $ueStructure['ue']->credits ?? 0;
                                        @endphp
                                        <div class="font-black text-base {{ $moyenneUE >= 10 ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                                            {{ number_format($moyenneUE, 2) }}
                                        </div>
                                        <div class="text-xs mt-1">
                                            @if($ueValidee)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 font-bold">
                                                    {{ $creditsUE }}cr ✓
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 font-bold">
                                                    0cr ✗
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">-</span>
                                    @endif
                                </td>
                            @endforeach

                            {{-- Crédits validés --}}
                            <td class="px-4 py-3 text-center border-r border-gray-200 dark:border-gray-700">
                                <div class="font-black text-lg text-{{ $couleur }}-700 dark:text-{{ $couleur }}-300 mb-2">
                                    {{ $resultat['credits_valides'] ?? 0 }}<span class="text-slate-500 dark:text-slate-400 text-sm">/{{ $resultat['total_credits'] ?? 60 }}</span>
                                </div>
                            </td>

                            {{-- Moyenne générale --}}
                            <td class="px-4 py-3 text-center border-r border-gray-200 dark:border-gray-700">
                                <div class="text-2xl font-black text-{{ $couleur }}-700 dark:text-{{ $couleur }}-300">
                                    {{ number_format($resultat['moyenne_generale'] ?? 0, 2) }}
                                </div>
                                @if($resultat['has_note_eliminatoire'] ?? false)
                                    <div class="text-xs text-red-600 dark:text-red-400 font-bold mt-1 flex items-center justify-center gap-1">
                                        <span>⚠️</span>
                                        <span>Note élim.</span>
                                    </div>
                                @endif
                            </td>

                            {{-- Décision --}}
                            <td class="px-4 py-3 text-center">
                                @php
                                    $badgeConfig = match($decision) {
                                        'admis' => [
                                            'bg' => 'bg-green-100 dark:bg-green-900/30',
                                            'text' => 'text-green-700 dark:text-green-300',
                                            'border' => 'border-green-500',
                                            'label' => 'ADMIS',
                                            'icon' => '✓'
                                        ],
                                        'redoublant' => [
                                            'bg' => 'bg-yellow-100 dark:bg-yellow-900/30',
                                            'text' => 'text-yellow-700 dark:text-yellow-300',
                                            'border' => 'border-yellow-500',
                                            'label' => 'REDOUBLANT',
                                            'icon' => '↻'
                                        ],
                                        'exclus' => [
                                            'bg' => 'bg-red-100 dark:bg-red-900/30',
                                            'text' => 'text-red-700 dark:text-red-300',
                                            'border' => 'border-red-500',
                                            'label' => 'EXCLUS',
                                            'icon' => '✗'
                                        ],
                                        default => [
                                            'bg' => 'bg-slate-100 dark:bg-slate-700',
                                            'text' => 'text-slate-600 dark:text-slate-400',
                                            'border' => 'border-slate-400',
                                            'label' => 'NON DÉFINIE',
                                            'icon' => '?'
                                        ]
                                    };
                                @endphp
                                
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border-2 {{ $badgeConfig['bg'] }} {{ $badgeConfig['text'] }} {{ $badgeConfig['border'] }} font-black text-xs shadow-sm">
                                    <span class="text-lg">{{ $badgeConfig['icon'] }}</span>
                                    <span>{{ $badgeConfig['label'] }}</span>
                                </div>
                                
                                {{-- Indicateur de délibération --}}
                                @if(isset($resultat['is_deliber']) && $resultat['is_deliber'])
                                    <div class="text-xs text-cyan-600 dark:text-cyan-400 mt-1 font-semibold flex items-center justify-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Délibéré</span>
                                    </div>
                                @elseif(isset($resultat['decision_simulee']) && $resultat['decision_simulee'])
                                    <div class="text-xs text-amber-600 dark:text-amber-400 mt-1 font-semibold flex items-center justify-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Simulé</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>