<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $titre_document ?? 'Liste des Résultats' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #000;
            background-color: #fff;
            margin: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: normal;
            margin: 5px 0;
            color: #000;
        }

        .header h3 {
            font-size: 12px;
            font-weight: normal;
            margin: 3px 0;
            color: #000;
        }

        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .meta-left {
            text-align: left;
        }

        .meta-right {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            color: #000;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px 4px;
            font-size: 11px;
            color: #000;
        }

        th {
            background-color: #fff;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
        }

        td {
            border: 1px solid #000;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }

        .rang-col { width: 8%; }
        .nom-col { width: 35%; }
        .matricule-col { width: 15%; }
        .moyenne-col { width: 12%; }
        .credits-col { width: 12%; }
        .decision-col { width: 12%; }
        .niveau-col { width: 15%; }

        .decision-admis {
            font-weight: normal;
            color: #000;
        }

        .decision-rattrapage {
            font-weight: normal;
            color: #000;
        }

        .decision-redoublant {
            font-weight: normal;
            color: #000;
        }

        .decision-exclus {
            font-weight: normal;
            color: #000;
        }

        .moyenne-excellente {
            font-weight: normal;
            color: #000;
        }

        .moyenne-bonne {
            font-weight: normal;
            color: #000;
        }

        .moyenne-passable {
            color: #000;
        }

        .moyenne-insuffisante {
            color: #000;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #000;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 50px;
            margin-bottom: 5px;
        }

        .note-eliminatoire {
            color: #000;
            font-weight: bold;
        }

        .changement-mark {
            color: #000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        // ✅ TRI LOGIQUE : Plus de crédits d'abord, puis plus haute moyenne
        $donneesTriees = collect($donnees ?? [])->sortBy([
            ['credits_valides', 'desc'],
            ['moyenne_generale', 'desc']
        ])->values();

        // ✅ RECALCULER LES RANGS APRÈS TRI
        $donneesAvecRang = $donneesTriees->map(function($item, $index) {
            $item['rang'] = $index + 1;
            return $item;
        });
    @endphp

    <div class="header">
        <h1>{{ $titre_document ?? 'LISTE DES RÉSULTATS' }}</h1>
        <h2>{{ $session_type ?? 'Session' }} - {{ $niveau->nom ?? 'Niveau' }}{{ $parcours_text ?? '' }}</h2>
        <h3>Année Universitaire: {{ $annee_universitaire ?? 'N/A' }}</h3>
    </div>

    <div class="meta-info">
        <div class="meta-left">
            <strong>Établissement:</strong> {{ config('app.etablissement', 'Université de Mahajanga') }}<br>
            <strong>Faculté:</strong> {{ $niveau->faculte ?? 'Faculté de Médecine' }}<br>
            @if($parcours ?? false)
            <strong>Parcours:</strong> {{ $parcours->nom }}
            @endif
        </div>
        <div class="meta-right">
            <strong>Date d'édition:</strong> {{ $date_generation ?? now()->format('d/m/Y H:i:s') }}<br>
            <strong>Nombre de résultats:</strong> {{ count($donnees ?? []) }}<br>
            <strong>Session:</strong> {{ $session_type ?? 'N/A' }}
        </div>
    </div>

    @if(!empty($donnees) && count($donnees) > 0)
        <table>
            <thead>
                <tr>
                    @if(($colonnes_config['rang'] ?? true))
                        <th class="rang-col">Rang</th>
                    @endif
                    @if(($colonnes_config['nom_complet'] ?? true))
                        <th class="nom-col">Nom et Prénom</th>
                    @endif
                    @if(($colonnes_config['matricule'] ?? true))
                        <th class="matricule-col">Matricule</th>
                    @endif
                    @if(($colonnes_config['moyenne'] ?? true))
                        <th class="moyenne-col">Moyenne Générale</th>
                    @endif
                    @if(($colonnes_config['credits'] ?? true))
                        <th class="credits-col">Crédits Validés</th>
                    @endif
                    @if(($colonnes_config['decision'] ?? true))
                        <th class="decision-col">Décision du Jury</th>
                    @endif
                    @if(($colonnes_config['niveau'] ?? false))
                        <th class="niveau-col">Niveau d'Études</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($donneesAvecRang as $item)
                    @php
                        // Récupération sécurisée des données
                        $etudiant = $item['etudiant'] ?? null;
                        $nom = '';
                        $prenom = '';
                        $matricule = '';

                        if ($etudiant) {
                            if (is_array($etudiant)) {
                                $nom = $etudiant['nom'] ?? '';
                                $prenom = $etudiant['prenom'] ?? '';
                                $matricule = $etudiant['matricule'] ?? '';
                            } else {
                                $nom = $etudiant->nom ?? '';
                                $prenom = $etudiant->prenom ?? '';
                                $matricule = $etudiant->matricule ?? '';
                            }
                        }

                        $moyenne = $item['moyenne_generale'] ?? 0;
                        $credits = $item['credits_valides'] ?? 0;
                        $totalCredits = $item['total_credits'] ?? 60;
                        $decision = $item['decision_simulee'] ?? $item['decision_actuelle'] ?? $item['decision'] ?? 'non_definie';
                        $hasNoteEliminatoire = $item['has_note_eliminatoire'] ?? false;
                        $changement = $item['changement'] ?? false;

                        // Classes CSS pour la moyenne
                        $moyenneClass = '';
                        if ($moyenne >= 16) $moyenneClass = 'moyenne-excellente';
                        elseif ($moyenne >= 14) $moyenneClass = 'moyenne-bonne';
                        elseif ($moyenne >= 10) $moyenneClass = 'moyenne-passable';
                        else $moyenneClass = 'moyenne-insuffisante';

                        // Classes CSS pour la décision
                        $decisionClass = 'decision-' . strtolower($decision);
                        $decisionText = strtoupper($decision);
                    @endphp

                    <tr>
                        @if(($colonnes_config['rang'] ?? true))
                            <td class="text-center"><strong>{{ $item['rang'] }}</strong></td>
                        @endif

                        @if(($colonnes_config['nom_complet'] ?? true))
                            <td class="text-left">{{ strtoupper($nom) }} {{ ucwords(strtolower($prenom)) }}</td>
                        @endif

                        @if(($colonnes_config['matricule'] ?? true))
                            <td class="text-center">{{ $matricule }}</td>
                        @endif

                        @if(($colonnes_config['moyenne'] ?? true))
                            <td class="text-center {{ $moyenneClass }}">
                                {{ number_format($moyenne, 2) }}/20
                                @if($hasNoteEliminatoire)
                                    <span class="note-eliminatoire">⚠</span>
                                @endif
                            </td>
                        @endif

                        @if(($colonnes_config['credits'] ?? true))
                            <td class="text-center">{{ $credits }}/{{ $totalCredits }}</td>
                        @endif

                        @if(($colonnes_config['decision'] ?? true))
                            <td class="text-center {{ $decisionClass }}">
                                {{ $decisionText }}
                                @if($changement)
                                    <span class="changement-mark">*</span>
                                @endif
                            </td>
                        @endif

                        @if(($colonnes_config['niveau'] ?? false))
                            <td class="text-center">{{ $niveau->nom ?? 'N/A' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 20px; color: #666; border: 1px solid #ccc;">
            <p><strong>Aucune donnée à afficher</strong></p>
        </div>
    @endif



    <div class="signature-section">
        <div class="signature-box">
            <div><strong>Président de jury</strong></div>
            <div class="signature-line"></div>
            <div>Nom et Signature</div>
        </div>
        <div class="signature-box">
            <div><strong>Le Doyen de la Faculté</strong></div>
            <div class="signature-line"></div>
            <div>Nom et Signature</div>
        </div>
    </div>

    <div class="footer">
        <p>Document généré automatiquement le {{ $date_generation ?? now()->format('d/m/Y à H:i') }}</p>
        <p>Ce document liste les résultats pour l'année universitaire {{ $annee_universitaire ?? 'N/A' }}</p>
        @if($changement ?? false)
            <p style="font-style: italic;">* Indique une modification par rapport à la décision précédente</p>
        @endif
        @if($hasNoteEliminatoire ?? false)
            <p style="font-style: italic;">⚠ Indique la présence d'une note éliminatoire</p>
        @endif
    </div>
</body>
</html>
