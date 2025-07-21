<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats {{ $session->type }} - {{ $niveau->nom }}</title>
    <style>
        @page {
            margin: 10mm 8mm 15mm 8mm;
            size: A4 landscape;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            page-break-inside: avoid;
        }

        .header h1 {
            color: #000;
            font-size: 16px;
            margin: 0 0 3px 0;
            font-weight: bold;
        }

        .header h2 {
            color: #333;
            font-size: 12px;
            margin: 0;
            font-weight: normal;
        }

        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            background: #f5f5f5;
            padding: 6px;
            border: 1px solid #ccc;
            page-break-inside: avoid;
        }

        .info-row {
            display: table-row;
        }

        .info-left, .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 2px 10px;
        }

        .info-item {
            margin-bottom: 3px;
            font-size: 8px;
        }

        .info-label {
            font-weight: bold;
            color: #000;
        }

        .statistics {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            background: #f0f0f0;
            padding: 6px;
            border: 1px solid #ccc;
            text-align: center;
            page-break-inside: avoid;
        }

        .statistics-row {
            display: table-row;
        }

        .stat-item {
            display: table-cell;
            text-align: center;
            padding: 3px;
            border-right: 1px solid #ddd;
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-number {
            font-size: 12px;
            font-weight: bold;
            color: #000;
            display: block;
        }

        .stat-label {
            font-size: 7px;
            color: #333;
            margin-top: 1px;
            display: block;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 7px;
            page-break-inside: auto;
        }

        .results-table th {
            background-color: #e0e0e0;
            color: #000;
            padding: 3px 2px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 6px;
            line-height: 1.1;
        }

        .results-table td {
            padding: 2px 1px;
            border: 1px solid #000;
            text-align: center;
            font-size: 7px;
            line-height: 1.1;
        }

        .results-table tr {
            page-break-inside: avoid;
        }

        .results-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .student-info {
            text-align: left !important;
            font-weight: bold;
            color: #000;
            padding: 2px 3px !important;
        }

        .matricule {
            font-family: monospace;
            color: #333;
            font-size: 6px;
        }

        .note {
            font-weight: bold;
            font-size: 7px;
        }

        .ue-moyenne {
            background-color: #f5f5f5 !important;
            font-weight: bold;
            border-left: 2px solid #000 !important;
        }

        .decision {
            font-weight: bold;
            padding: 1px 3px;
            font-size: 6px;
            border-radius: 2px;
        }

        .decision-admis {
            background-color: #e0e0e0;
            color: #000;
        }

        .decision-rattrapage {
            background-color: #e0e0e0;
            color: #000;
        }

        .decision-redoublant {
            background-color: #e0e0e0;
            color: #000;
        }

        .decision-exclus {
            background-color: #d0d0d0;
            color: #000;
        }

        .summary-section {
            margin-top: 15px;
            background: #f8f8f8;
            padding: 8px;
            border: 1px solid #ccc;
            page-break-inside: avoid;
        }

        .summary-title {
            font-weight: bold;
            color: #000;
            margin-bottom: 6px;
            font-size: 10px;
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-row {
            display: table-row;
        }

        .summary-column {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding: 0 8px;
            border-right: 1px solid #ddd;
        }

        .summary-column:last-child {
            border-right: none;
        }

        .summary-item {
            margin-bottom: 3px;
            font-size: 7px;
            line-height: 1.2;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            position: fixed;
            bottom: 5mm;
            left: 8mm;
            right: 8mm;
            text-align: center;
            font-size: 6px;
            color: #333;
            border-top: 1px solid #ccc;
            padding-top: 3px;
        }

        /* Éviter les coupures */
        .no-break {
            page-break-inside: avoid;
        }

        /* Style pour l'ordre */
        .ordre {
            font-weight: bold;
            color: #000;
        }

        /* Optimisation pour les petites tables */
        .compact-table {
            font-size: 6px;
        }

        .compact-table th,
        .compact-table td {
            padding: 1px;
        }

        /* Headers UE avec informations complètes */
        .ue-header {
            font-size: 5px;
            line-height: 1;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header no-break">
        <h1>RÉSULTATS {{ strtoupper($session->type) }}</h1>
        <h2>{{ $niveau->nom }}{{ $parcours ? ' - ' . $parcours->nom : '' }}</h2>
    </div>

    <div class="info-section no-break">
        <div class="info-row">
            <div class="info-left">
                <div class="info-item">
                    <span class="info-label">Année Universitaire:</span> {{ $anneeUniversitaire->libelle }}
                </div>
                <div class="info-item">
                    <span class="info-label">Session:</span> {{ $session->type }}
                </div>
                <div class="info-item">
                    <span class="info-label">Niveau:</span> {{ $niveau->nom }}
                </div>
                @if($parcours)
                    <div class="info-item">
                        <span class="info-label">Parcours:</span> {{ $parcours->nom }}
                    </div>
                @endif
            </div>
            <div class="info-right">
                <div class="info-item">
                    <span class="info-label">Date de génération:</span> {{ $dateGeneration->format('d/m/Y H:i:s') }}
                </div>
                <div class="info-item">
                    <span class="info-label">Nombre d'étudiants:</span> {{ count($resultats) }}
                </div>
                <div class="info-item">
                    <span class="info-label">Taux de réussite:</span> {{ $statistics['taux_reussite'] ?? 0 }}%
                </div>
            </div>
        </div>
    </div>

    <div class="statistics no-break">
        <div class="statistics-row">
            <div class="stat-item">
                <span class="stat-number">{{ $statistics['total_etudiants'] ?? 0 }}</span>
                <span class="stat-label">Total Étudiants</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">{{ $statistics['admis'] ?? 0 }}</span>
                <span class="stat-label">Admis</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">{{ $statistics['rattrapage'] ?? 0 }}</span>
                <span class="stat-label">Rattrapage</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">{{ $statistics['redoublant'] ?? 0 }}</span>
                <span class="stat-label">Redoublant</span>
            </div>
            @if(($statistics['exclus'] ?? 0) > 0)
            <div class="stat-item">
                <span class="stat-number">{{ $statistics['exclus'] ?? 0 }}</span>
                <span class="stat-label">Exclus</span>
            </div>
            @endif
        </div>
    </div>

    <table class="results-table {{ count($resultats) > 30 ? 'compact-table' : '' }}">
        <thead>
            <tr class="no-break">
                <th style="width: 4%;">N°</th>
                <th style="width: 8%;">Matricule</th>
                <th style="width: 12%;">Nom</th>
                <th style="width: 10%;">Prénom</th>
                @foreach($uesStructure as $ueStructure)
                    @foreach($ueStructure['ecs'] as $ecData)
                        <th style="width: 2.5%;" class="ue-header" title="{{ $ecData['ec']->nom }}">
                            {{ $ecData['display_name'] }}
                        </th>
                    @endforeach
                    <th style="width: 3.5%;" class="ue-header">
                        <div>{{ $ueStructure['ue']->abr ?? 'UE' . ($loop->index + 1) }}</div>
                        <div>({{ $ueStructure['ue']->credits }})</div>
                        <div>Moy</div>
                    </th>
                @endforeach
                <th style="width: 4%;">Moy.Gén</th>
                <th style="width: 5%;">Crédits</th>
                <th style="width: 6%;">Décision</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultats as $index => $resultat)
                <tr class="no-break">
                    <td class="ordre">{{ $index + 1 }}</td>
                    <td class="matricule">{{ $resultat['etudiant']->matricule }}</td>
                    <td class="student-info">{{ $resultat['etudiant']->nom }}</td>
                    <td class="student-info">{{ $resultat['etudiant']->prenom }}</td>
                    @foreach($uesStructure as $ueStructure)
                        @php
                            $moyenneUE = 0;
                            $notesUE = [];
                            $hasNoteZeroInUE = false;
                        @endphp
                        {{-- Notes EC --}}
                        @foreach($ueStructure['ecs'] as $ecData)
                            <td>
                                @if(isset($resultat['notes'][$ecData['ec']->id]))
                                    @php
                                        $note = $resultat['notes'][$ecData['ec']->id]->note;
                                        $notesUE[] = $note;
                                        if ($note == 0) $hasNoteZeroInUE = true;
                                    @endphp
                                    <span class="note">{{ number_format($note, 2) }}</span>
                                @else
                                    <span style="color: #666;">-</span>
                                @endif
                            </td>
                        @endforeach
                        {{-- CORRECTION : Moyenne UE selon votre logique --}}
                        <td class="ue-moyenne">
                            @php
                                if ($hasNoteZeroInUE) {
                                    // UE éliminée à cause d'une note de 0
                                    $moyenneUE = 0;
                                    $moyenneText = '0.00';
                                    $elimination = '(Élim)';
                                } elseif (!empty($notesUE)) {
                                    // Moyenne UE = somme notes / nombre EC
                                    $moyenneUE = array_sum($notesUE) / count($notesUE);
                                    $moyenneText = number_format($moyenneUE, 2);
                                    $elimination = '';
                                } else {
                                    $moyenneUE = 0;
                                    $moyenneText = '-';
                                    $elimination = '';
                                }
                            @endphp
                            <div>
                                <span class="note">{{ $moyenneText }}</span>
                                @if($elimination)
                                    <br><span style="font-size: 5px; color: #666;">{{ $elimination }}</span>
                                @endif
                            </div>
                        </td>
                    @endforeach
                    <td>
                        <span class="note">{{ number_format($resultat['moyenne_generale'], 2) }}</span>
                    </td>
                    <td>{{ $resultat['credits_valides'] }}/{{ $resultat['total_credits'] ?? 60 }}</td>
                    <td>
                        @php
                            $decision = $resultat['decision'];
                            $decisionClass = match($decision) {
                                'admis' => 'decision-admis',
                                'rattrapage' => 'decision-rattrapage',
                                'redoublant' => 'decision-redoublant',
                                'exclus' => 'decision-exclus',
                                default => 'decision-admis'
                            };
                            $decisionLibelle = match($decision) {
                                'admis' => 'Admis',
                                'rattrapage' => 'Rattrapage',
                                'redoublant' => 'Redoublant',
                                'exclus' => 'Exclus',
                                default => 'Non définie'
                            };
                        @endphp
                        <span class="decision {{ $decisionClass }}">{{ $decisionLibelle }}</span>
                    </td>
                </tr>
                {{-- Saut de page automatique tous les 25 étudiants --}}
                @if(($index + 1) % 25 == 0 && !$loop->last)
                    </tbody>
                    </table>
                    <div class="page-break"></div>
                    <table class="results-table {{ count($resultats) > 30 ? 'compact-table' : '' }}">
                        <thead>
                            <tr class="no-break">
                                <th style="width: 4%;">N°</th>
                                <th style="width: 8%;">Matricule</th>
                                <th style="width: 12%;">Nom</th>
                                <th style="width: 10%;">Prénom</th>
                                @foreach($uesStructure as $ueStructure)
                                    @foreach($ueStructure['ecs'] as $ecData)
                                        <th style="width: 2.5%;" class="ue-header" title="{{ $ecData['ec']->nom }}">
                                            {{ $ecData['display_name'] }}
                                        </th>
                                    @endforeach
                                    <th style="width: 3.5%;" class="ue-header">
                                        <div>{{ $ueStructure['ue']->abr ?? 'UE' . ($loop->index + 1) }}</div>
                                        <div>({{ $ueStructure['ue']->credits }})</div>
                                        <div>Moy</div>
                                    </th>
                                @endforeach
                                <th style="width: 4%;">Moy.Gén</th>
                                <th style="width: 5%;">Crédits</th>
                                <th style="width: 6%;">Décision</th>
                            </tr>
                        </thead>
                        <tbody>
                @endif
            @endforeach
        </tbody>
    </table>

    {{-- Page suivante pour le résumé si nécessaire --}}
    @if(count($resultats) > 20)
        <div class="page-break"></div>
    @endif

    <div class="summary-section no-break">
        <div class="summary-title">RÉSUMÉ ANALYTIQUE</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-column">
                    <div class="summary-item"><strong>Répartition par décision:</strong></div>
                    <div class="summary-item">• Admis: {{ $statistics['admis'] ?? 0 }} ({{ $statistics['total_etudiants'] > 0 ? round(($statistics['admis'] ?? 0) / $statistics['total_etudiants'] * 100, 1) : 0 }}%)</div>
                    <div class="summary-item">• Rattrapage: {{ $statistics['rattrapage'] ?? 0 }} ({{ $statistics['total_etudiants'] > 0 ? round(($statistics['rattrapage'] ?? 0) / $statistics['total_etudiants'] * 100, 1) : 0 }}%)</div>
                    <div class="summary-item">• Redoublant: {{ $statistics['redoublant'] ?? 0 }} ({{ $statistics['total_etudiants'] > 0 ? round(($statistics['redoublant'] ?? 0) / $statistics['total_etudiants'] * 100, 1) : 0 }}%)</div>
                    @if(($statistics['exclus'] ?? 0) > 0)
                    <div class="summary-item">• Exclus: {{ $statistics['exclus'] ?? 0 }} ({{ $statistics['total_etudiants'] > 0 ? round(($statistics['exclus'] ?? 0) / $statistics['total_etudiants'] * 100, 1) : 0 }}%)</div>
                    @endif
                </div>
                <div class="summary-column">
                    <div class="summary-item"><strong>Moyennes de promotion:</strong></div>
                    <div class="summary-item">• Moyenne générale: {{ $statistics['moyenne_promo'] ?? 0 }}/20</div>
                    @php
                        $moyennes = collect($resultats)->pluck('moyenne_generale');
                        $moyenneMin = $moyennes->min();
                        $moyenneMax = $moyennes->max();
                    @endphp
                    <div class="summary-item">• Note la plus basse: {{ number_format($moyenneMin ?? 0, 2) }}/20</div>
                    <div class="summary-item">• Note la plus haute: {{ number_format($moyenneMax ?? 0, 2) }}/20</div>
                    <div class="summary-item">• Crédits moyens validés: {{ $statistics['credits_moyen'] ?? 0 }}/60</div>
                </div>
                <div class="summary-column">
                    <div class="summary-item"><strong>Structure pédagogique:</strong></div>
                    @foreach($uesStructure as $ueStructure)
                        <div class="summary-item">• {{ $ueStructure['ue']->abr ?? 'UE' . ($loop->index + 1) }} - {{ $ueStructure['ue']->nom }} ({{ $ueStructure['ue']->credits }}cr)</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="summary-section no-break" style="margin-top: 10px;">
        <div class="summary-title">LOGIQUE ACADÉMIQUE APPLIQUÉE</div>
        <div style="display: table; width: 100%; font-size: 7px;">
            <div style="display: table-row;">
                <div style="display: table-cell; width: 50%; padding: 0 5px; vertical-align: top;">
                    <div><strong>Calculs et validation:</strong></div>
                    <div>• Moyenne UE = (Somme notes EC) ÷ (Nombre EC)</div>
                    <div>• UE validée si Moyenne ≥ 10 ET aucune note = 0</div>
                    <div>• Note 0 = UE éliminée (0 crédit validé)</div>
                    <div>• Moyenne générale = Moyenne des moyennes UE</div>
                    <div>• Crédits validés = Somme crédits UE validées</div>
                </div>
                <div style="display: table-cell; width: 50%; padding: 0 5px; vertical-align: top;">
                    <div><strong>Décisions selon session:</strong></div>
                    @if($session->type === 'Normale')
                        <div>• 1ère session: 60 crédits validés → Admis</div>
                        <div>• 1ère session: &lt; 60 crédits → Rattrapage</div>
                    @else
                        <div>• 2ème session: Note éliminatoire → Exclu</div>
                        <div>• 2ème session: ≥ 40 crédits → Admis</div>
                        <div>• 2ème session: &lt; 40 crédits → Redoublant</div>
                    @endif
                    <div>• Total crédits année: 60 ECTS</div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Document généré le {{ $dateGeneration->format('d/m/Y à H:i:s') }} -
        {{ $niveau->nom }}{{ $parcours ? ' - ' . $parcours->nom : '' }} -
        Session {{ $session->type }} {{ $anneeUniversitaire->libelle }}
    </div>
</body>
</html>
