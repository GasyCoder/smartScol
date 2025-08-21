<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats {{ $session->type }} - {{ $niveau->nom }}</title>
    <style>
        @page {
            margin: 8mm 5mm 12mm 5mm;
            size: A4 landscape;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.2;
            background: #fff;
        }

        /* ===== HEADER ===== */
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            page-break-inside: avoid;
        }

        .header h1 {
            color: #000;
            font-size: 16px;
            margin: 0 0 3px 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header h2 {
            color: #333;
            font-size: 12px;
            margin: 0;
            font-weight: normal;
        }

        /* ===== INFO SECTION ===== */
        .info-section {
            background: #f5f5f5;
            border: 1px solid #ccc;
            padding: 5px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        .info-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .info-row {
            display: table-row;
        }

        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 2px 8px;
        }

        .info-item {
            margin-bottom: 2px;
            font-size: 8px;
        }

        .info-label {
            font-weight: bold;
            color: #000;
        }

        /* ===== STATISTICS ===== */
        .statistics {
            background: #f0f0f0;
            border: 1px solid #ccc;
            padding: 5px;
            margin-bottom: 8px;
            text-align: center;
            page-break-inside: avoid;
        }

        .stats-container {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .stats-row {
            display: table-row;
        }

        .stat-cell {
            display: table-cell;
            text-align: center;
            padding: 2px;
            border-right: 1px solid #ddd;
            vertical-align: middle;
        }

        .stat-cell:last-child {
            border-right: none;
        }

        .stat-number {
            font-size: 11px;
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

        /* ===== TABLE - OPTIMISÉE POUR TOUTES LES DONNÉES ===== */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 6px;
            table-layout: auto;
        }

        .results-table th {
            background-color: #e0e0e0;
            color: #000;
            padding: 2px 1px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 5px;
            line-height: 1.1;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .results-table td {
            padding: 1px 0.5px;
            border: 1px solid #000;
            text-align: center;
            font-size: 6px;
            line-height: 1.1;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .results-table tr {
            page-break-inside: avoid;
        }

        .results-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* ===== COLONNES DYNAMIQUES ===== */
        .col-numero { 
            width: 3%; 
            min-width: 15px;
        }

        .col-matricule { 
            width: 6%; 
            min-width: 40px;
            font-family: monospace;
            font-size: 5px;
        }

        .col-nom { 
            width: 8%; 
            min-width: 50px;
            text-align: left !important;
            font-weight: bold;
            padding-left: 2px !important;
        }

        .col-prenom { 
            width: 7%; 
            min-width: 45px;
            text-align: left !important;
            font-weight: bold;
            padding-left: 2px !important;
        }

        .col-ec { 
            width: 2%; 
            min-width: 20px;
        }

        .col-ue { 
            width: 3%; 
            min-width: 25px;
            background-color: #f5f5f5 !important;
            border-left: 2px solid #000 !important;
            font-weight: bold;
        }

        .col-moyenne { 
            width: 3.5%; 
            min-width: 30px;
            font-weight: bold;
        }

        .col-credits { 
            width: 4%; 
            min-width: 30px;
        }

        .col-decision { 
            width: 5%; 
            min-width: 35px;
        }

        /* ===== NOTES ===== */
        .note {
            font-weight: bold;
            font-size: 6px;
        }

        .ue-header {
            font-size: 4px;
            line-height: 1;
            text-align: center;
        }

        .elimination {
            font-size: 4px;
            color: #666;
            font-style: italic;
        }

        /* ===== DECISIONS ===== */
        .decision {
            font-weight: bold;
            padding: 1px 2px;
            font-size: 5px;
            border-radius: 1px;
            background-color: #e0e0e0;
            color: #000;
        }

        /* ===== PAGINATION ===== */
        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* ===== FOOTER ===== */
        .footer {
            position: fixed;
            bottom: 5mm;
            left: 5mm;
            right: 5mm;
            text-align: center;
            font-size: 6px;
            color: #333;
            border-top: 1px solid #ccc;
            padding-top: 3px;
        }

        /* ===== MODE COMPACT POUR BEAUCOUP DE COLONNES ===== */
        .ultra-compact {
            font-size: 5px;
        }

        .ultra-compact th,
        .ultra-compact td {
            padding: 0.5px;
            font-size: 4px;
        }

        .ultra-compact .note {
            font-size: 5px;
        }

        /* ===== OPTIMISATION POUR NOMBREUSES UE/EC ===== */
        @media print {
            .results-table {
                width: 100%;
                font-size: 5px;
            }
            
            .col-ec {
                width: auto;
                min-width: 18px;
            }
            
            .col-ue {
                width: auto;
                min-width: 22px;
            }
        }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header no-break">
        <h1>RÉSULTATS {{ strtoupper($session->type) }}</h1>
        <h2>{{ $niveau->nom }}{{ $parcours ? ' - ' . $parcours->nom : '' }}</h2>
    </div>

    {{-- INFORMATIONS GÉNÉRALES --}}
    <div class="info-section no-break">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-col">
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
                <div class="info-col">
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
    </div>

    {{-- STATISTIQUES --}}
    <div class="statistics no-break">
        <div class="stats-container">
            <div class="stats-row">
                <div class="stat-cell">
                    <span class="stat-number">{{ $statistics['total_etudiants'] ?? 0 }}</span>
                    <span class="stat-label">Total Étudiants</span>
                </div>
                <div class="stat-cell">
                    <span class="stat-number">{{ $statistics['admis'] ?? 0 }}</span>
                    <span class="stat-label">Admis</span>
                </div>
                <div class="stat-cell">
                    <span class="stat-number">{{ $statistics['rattrapage'] ?? 0 }}</span>
                    <span class="stat-label">Rattrapage</span>
                </div>
                <div class="stat-cell">
                    <span class="stat-number">{{ $statistics['redoublant'] ?? 0 }}</span>
                    <span class="stat-label">Redoublant</span>
                </div>
                @if(($statistics['exclus'] ?? 0) > 0)
                <div class="stat-cell">
                    <span class="stat-number">{{ $statistics['exclus'] ?? 0 }}</span>
                    <span class="stat-label">Exclus</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TABLEAU COMPLET DES RÉSULTATS --}}
    @php
        $totalCols = 4; // N°, Matricule, Nom, Prénom
        foreach($uesStructure as $ue) {
            $totalCols += count($ue['ecs']) + 1; // ECs + Moyenne UE
        }
        $totalCols += 3; // Moyenne Générale, Crédits, Décision
        
        $tableClass = '';
        if ($totalCols > 20) {
            $tableClass = 'ultra-compact';
        }
    @endphp

    <table class="results-table {{ $tableClass }}">
        <thead>
            <tr class="no-break">
                <th class="col-numero">N°</th>
                <th class="col-matricule">Matricule</th>
                <th class="col-nom">Nom</th>
                <th class="col-prenom">Prénom</th>
                @foreach($uesStructure as $ueStructure)
                    {{-- Toutes les colonnes EC de cette UE --}}
                    @foreach($ueStructure['ecs'] as $ecData)
                        <th class="col-ec ue-header" title="{{ $ecData['ec']->nom }}">
                            {{ $ecData['display_name'] }}
                        </th>
                    @endforeach
                    {{-- Colonne moyenne UE --}}
                    <th class="col-ue ue-header">
                        <div>{{ $ueStructure['ue']->abr ?? 'UE' . ($loop->index + 1) }}</div>
                        <div>({{ $ueStructure['ue']->credits }}cr)</div>
                        <div>Moy</div>
                    </th>
                @endforeach
                <th class="col-moyenne">Moy.Gén</th>
                <th class="col-credits">Crédits</th>
                <th class="col-decision">Décision</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultats as $index => $resultat)
                <tr class="no-break">
                    <td class="col-numero note">{{ $index + 1 }}</td>
                    <td class="col-matricule">{{ $resultat['etudiant']->matricule }}</td>
                    <td class="col-nom">{{ $resultat['etudiant']->nom }}</td>
                    <td class="col-prenom">{{ $resultat['etudiant']->prenom }}</td>
                    
                    {{-- TOUTES les UE et EC --}}
                    @foreach($uesStructure as $ueStructure)
                        @php
                            $moyenneUE = 0;
                            $notesUE = [];
                            $hasNoteZeroInUE = false;
                        @endphp
                        
                        {{-- TOUTES les notes EC de cette UE --}}
                        @foreach($ueStructure['ecs'] as $ecData)
                            <td class="col-ec">
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
                        
                        {{-- Moyenne UE complète --}}
                        <td class="col-ue">
                            @php
                                if ($hasNoteZeroInUE) {
                                    $moyenneUE = 0;
                                    $moyenneText = '0.00';
                                    $elimination = '(Élim)';
                                } elseif (!empty($notesUE)) {
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
                                    <br><span class="elimination">{{ $elimination }}</span>
                                @endif
                            </div>
                        </td>
                    @endforeach
                    
                    {{-- Moyenne générale --}}
                    <td class="col-moyenne">
                        <span class="note">{{ number_format($resultat['moyenne_generale'], 2) }}</span>
                    </td>
                    
                    {{-- Crédits --}}
                    <td class="col-credits">{{ $resultat['credits_valides'] }}/{{ $resultat['total_credits'] ?? 60 }}</td>
                    
                    {{-- Décision --}}
                    <td class="col-decision">
                        @php
                            $decision = $resultat['decision'];
                            $decisionLibelle = match($decision) {
                                'admis' => 'Admis',
                                'rattrapage' => 'Rattrapage',
                                'redoublant' => 'Redoublant',
                                'exclus' => 'Exclus',
                                default => 'Non définie'
                            };
                        @endphp
                        <span class="decision">{{ $decisionLibelle }}</span>
                    </td>
                </tr>
                
                {{-- Saut de page tous les 20 étudiants pour tables larges --}}
                @if(($index + 1) % 20 == 0 && !$loop->last)
                    </tbody>
                    </table>
                    <div class="page-break"></div>
                    <table class="results-table {{ $tableClass }}">
                        <thead>
                            <tr class="no-break">
                                <th class="col-numero">N°</th>
                                <th class="col-matricule">Matricule</th>
                                <th class="col-nom">Nom</th>
                                <th class="col-prenom">Prénom</th>
                                @foreach($uesStructure as $ueStructure)
                                    @foreach($ueStructure['ecs'] as $ecData)
                                        <th class="col-ec ue-header" title="{{ $ecData['ec']->nom }}">
                                            {{ $ecData['display_name'] }}
                                        </th>
                                    @endforeach
                                    <th class="col-ue ue-header">
                                        <div>{{ $ueStructure['ue']->abr ?? 'UE' . ($loop->index + 1) }}</div>
                                        <div>({{ $ueStructure['ue']->credits }}cr)</div>
                                        <div>Moy</div>
                                    </th>
                                @endforeach
                                <th class="col-moyenne">Moy.Gén</th>
                                <th class="col-credits">Crédits</th>
                                <th class="col-decision">Décision</th>
                            </tr>
                        </thead>
                        <tbody>
                @endif
            @endforeach
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        Document généré le {{ $dateGeneration->format('d/m/Y à H:i:s') }} -
        {{ $niveau->nom }}{{ $parcours ? ' - ' . $parcours->nom : '' }} -
        Session {{ $session->type }} {{ $anneeUniversitaire->libelle }}
    </div>
</body>
</html>