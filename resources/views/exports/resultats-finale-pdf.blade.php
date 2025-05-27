<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats {{ $session->type }} - {{ $niveau->nom }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 15px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #4F46E5;
            font-size: 18px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }

        .header h2 {
            color: #6B7280;
            font-size: 14px;
            margin: 0;
            font-weight: normal;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            background: #F9FAFB;
            padding: 10px;
            border-radius: 5px;
        }

        .info-left, .info-right {
            width: 48%;
        }

        .info-item {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            color: #374151;
        }

        .statistics {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            background: #EEF2FF;
            padding: 10px;
            border-radius: 5px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 16px;
            font-weight: bold;
            color: #4F46E5;
        }

        .stat-label {
            font-size: 9px;
            color: #6B7280;
            margin-top: 2px;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8px;
        }

        .results-table th {
            background-color: #4F46E5;
            color: white;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #ddd;
        }

        .results-table td {
            padding: 4px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .results-table tr:nth-child(even) {
            background-color: #F9FAFB;
        }

        .student-name {
            text-align: left !important;
            font-weight: bold;
            color: #374151;
        }

        .matricule {
            font-family: monospace;
            color: #6B7280;
        }

        .note {
            font-weight: bold;
        }

        .note-excellent {
            color: #059669;
            background-color: #D1FAE5;
        }

        .note-good {
            color: #059669;
        }

        .note-average {
            color: #D97706;
        }

        .note-poor {
            color: #DC2626;
        }

        .note-eliminatoire {
            color: #DC2626;
            background-color: #FEE2E2;
            font-weight: bold;
        }

        .decision {
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
        }

        .decision-admis {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .decision-rattrapage {
            background-color: #FED7AA;
            color: #9A3412;
        }

        .decision-redoublant {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .decision-exclus {
            background-color: #991B1B;
            color: white;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #6B7280;
            padding: 10px;
            border-top: 1px solid #E5E7EB;
        }

        .page-break {
            page-break-before: always;
        }

        .summary-section {
            margin-top: 20px;
            background: #F3F4F6;
            padding: 15px;
            border-radius: 5px;
        }

        .summary-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .summary-grid {
            display: flex;
            justify-content: space-between;
        }

        .summary-column {
            width: 30%;
        }

        .summary-item {
            margin-bottom: 5px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RÉSULTATS {{ strtoupper($session->type) }}</h1>
        <h2>{{ $niveau->nom }}{{ $parcours ? ' - ' . $parcours->nom : '' }}</h2>
    </div>

    <div class="info-section">
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

    <div class="statistics">
        <div class="stat-item">
            <div class="stat-number">{{ $statistics['total_etudiants'] ?? 0 }}</div>
            <div class="stat-label">Total Étudiants</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $statistics['admis'] ?? 0 }}</div>
            <div class="stat-label">Admis</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $statistics['rattrapage'] ?? 0 }}</div>
            <div class="stat-label">Rattrapage</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $statistics['redoublant'] ?? 0 }}</div>
            <div class="stat-label">Redoublant</div>
        </div>
        @if(($statistics['exclus'] ?? 0) > 0)
        <div class="stat-item">
            <div class="stat-number">{{ $statistics['exclus'] ?? 0 }}</div>
            <div class="stat-label">Exclus</div>
        </div>
        @endif
    </div>

    <table class="results-table">
        <thead>
            <tr>
                <th width="15%">Nom et Prénom</th>
                <th width="8%">Matricule</th>
                @foreach($ecs as $ueNom => $ecsUE)
                    @foreach($ecsUE as $ec)
                        <th width="4%">{{ $ec->abr }}</th>
                    @endforeach
                @endforeach
                <th width="6%">Moyenne</th>
                <th width="6%">Crédits</th>
                <th width="10%">Décision</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultats as $resultat)
                <tr>
                    <td class="student-name">{{ $resultat['etudiant']->nom }} {{ $resultat['etudiant']->prenom }}</td>
                    <td class="matricule">{{ $resultat['etudiant']->matricule }}</td>
                    @foreach($ecs as $ueNom => $ecsUE)
                        @foreach($ecsUE as $ec)
                            <td>
                                @if(isset($resultat['notes'][$ec->id]))
                                    @php
                                        $note = $resultat['notes'][$ec->id]->note;
                                        $noteClass = '';
                                        if ($note == 0) {
                                            $noteClass = 'note-eliminatoire';
                                        } elseif ($note < 8) {
                                            $noteClass = 'note-poor';
                                        } elseif ($note < 10) {
                                            $noteClass = 'note-average';
                                        } elseif ($note < 16) {
                                            $noteClass = 'note-good';
                                        } else {
                                            $noteClass = 'note-excellent';
                                        }
                                    @endphp
                                    <span class="note {{ $noteClass }}">{{ number_format($note, 2) }}</span>
                                @else
                                    <span style="color: #9CA3AF;">-</span>
                                @endif
                            </td>
                        @endforeach
                    @endforeach
                    <td>
                        @php
                            $moyenne = $resultat['moyenne_generale'];
                            $moyenneClass = '';
                            if ($moyenne < 8) {
                                $moyenneClass = 'note-poor';
                            } elseif ($moyenne < 10) {
                                $moyenneClass = 'note-average';
                            } elseif ($moyenne < 14) {
                                $moyenneClass = 'note-good';
                            } else {
                                $moyenneClass = 'note-excellent';
                            }
                        @endphp
                        <span class="note {{ $moyenneClass }}">{{ number_format($moyenne, 2) }}</span>
                    </td>
                    <td>{{ $resultat['credits_valides'] }}/60</td>
                    <td>
                        @php
                            $decision = $resultat['decision'];
                            $decisionClass = match($decision) {
                                'admis' => 'decision-admis',
                                'rattrapage' => 'decision-rattrapage',
                                'redoublant' => 'decision-redoublant',
                                'exclus' => 'decision-exclus',
                                default => ''
                            };
                        @endphp
                        <span class="decision {{ $decisionClass }}">{{ $resultat['decision_libelle'] }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($resultats) > 25)
        <div class="page-break"></div>
    @endif

    <div class="summary-section">
        <div class="summary-title">RÉSUMÉ ANALYTIQUE</div>
        <div class="summary-grid">
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
                <div class="summary-item"><strong>Moyennes:</strong></div>
                <div class="summary-item">• Moyenne de la promotion: {{ $statistics['moyenne_promo'] ?? 0 }}/20</div>
                @php
                    $moyennes = collect($resultats)->pluck('moyenne_generale');
                    $moyenneMin = $moyennes->min();
                    $moyenneMax = $moyennes->max();
                @endphp
                <div class="summary-item">• Note la plus basse: {{ number_format($moyenneMin ?? 0, 2) }}/20</div>
                <div class="summary-item">• Note la plus haute: {{ number_format($moyenneMax ?? 0, 2) }}/20</div>
            </div>
            <div class="summary-column">
                <div class="summary-item"><strong>Observations:</strong></div>
                @php
                    $notesEliminatoires = 0;
                    $excellents = 0;
                    foreach($resultats as $res) {
                        if($res['moyenne_generale'] >= 16) $excellents++;
                        foreach($res['notes'] as $note) {
                            if($note->note == 0) {
                                $notesEliminatoires++;
                                break;
                            }
                        }
                    }
                @endphp
                <div class="summary-item">• Mentions très bien (≥16): {{ $excellents }}</div>
                <div class="summary-item">• Étudiants avec note(s) éliminatoire(s): {{ $notesEliminatoires }}</div>
                <div class="summary-item">• Taux de réussite global: {{ $statistics['taux_reussite'] ?? 0 }}%</div>
            </div>
        </div>
    </div>

    <div class="summary-section" style="margin-top: 15px;">
        <div class="summary-title">LÉGENDE</div>
        <div style="display: flex; justify-content: space-between; font-size: 8px;">
            <div>
                <div><span class="note note-excellent" style="padding: 2px 4px;">16-20</span> Très bien</div>
                <div><span class="note note-good" style="padding: 2px 4px;">10-15.99</span> Bien/Assez bien</div>
            </div>
            <div>
                <div><span class="note note-average" style="padding: 2px 4px;">8-9.99</span> Passable</div>
                <div><span class="note note-poor" style="padding: 2px 4px;">&lt;8</span> Insuffisant</div>
            </div>
            <div>
                <div><span class="note note-eliminatoire" style="padding: 2px 4px;">0</span> Note éliminatoire</div>
                <div style="margin-top: 5px;"><strong>Session:</strong> {{ $session->type }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div>
            Document généré le {{ $dateGeneration->format('d/m/Y à H:i:s') }} -
            {{ $niveau->nom }}{{ $parcours ? ' - ' . $parcours->nom : '' }} -
            Session {{ $session->type }} {{ $anneeUniversitaire->libelle }} -
            Page {PAGE_NUM} sur {PAGE_COUNT}
        </div>
    </div>
</body>
</html>
