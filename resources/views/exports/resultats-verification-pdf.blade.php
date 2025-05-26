<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vérification Résultats - {{ $examen->libelle ?? 'Examen' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            margin: 10mm;
        }
        .header {
            text-align: center;
            margin-bottom: 10mm;
        }
        .header h1 {
            font-size: 14px;
            margin: 0;
        }
        .header p {
            font-size: 10px;
            margin: 2px 0 0 0;
        }
        .info, .stats {
            margin-bottom: 5mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
        }
        th {
            font-weight: bold;
            text-align: center;
        }
        .center {
            text-align: center;
        }
        .footer {
            margin-top: 10mm;
            text-align: center;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>VÉRIFICATION DES RÉSULTATS</h1>
        <p>{{ $examen->libelle ?? 'Examen' }}
           @if($afficherMoyennesUE) - AVEC MOYENNES UE @else - SANS MOYENNES UE @endif
        </p>
    </div>

    <div class="info">
        <strong>Session :</strong> {{ $examen->session->type ?? 'N/A' }} {{ $examen->session->anneeUniversitaire->libelle ?? '' }} |
        <strong>Niveau :</strong> {{ $examen->niveau->nom ?? 'N/A' }} |
        <strong>Parcours :</strong> {{ $examen->parcours->nom ?? 'N/A' }} |
        <strong>Date :</strong> {{ $dateExport }}
    </div>

    <div class="stats">
        <strong>Statistiques :</strong>
        {{ $statistiques['total'] }} résultats -
        {{ $statistiques['verifiees'] }} vérifiés ({{ $statistiques['pourcentage_verification'] }}%) -
        {{ $statistiques['non_verifiees'] }} en attente
        @if($afficherMoyennesUE)
            | Mode moyennes UE activé
        @else
            | Mode moyennes UE désactivé
        @endif
    </div>

    @php
        // Grouper les résultats par étudiant
        $resultatsGroupes = collect($resultats)->groupBy('matricule');
        $numeroOrdre = 1;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">N°</th>
                <th style="width: 10%;">Matricule</th>
                <th style="width: 15%;">Nom</th>
                <th style="width: 15%;">Prénom</th>
                <th style="width: {{ $afficherMoyennesUE ? '25%' : '30%' }};">Unité d'enseignement(UE)</th>
                <th style="width: 15%;">Enseignant</th>
                <th style="width: 8%;">Note/20</th>
                @if($afficherMoyennesUE)
                    <th style="width: 8%;">Moyenne UE</th>
                @endif
                <th style="width: {{ $afficherMoyennesUE ? '10%' : '15%' }};">Commentaire</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultatsGroupes as $matricule => $resultatsEtudiant)
                @php
                    $premierResultat = $resultatsEtudiant->first();
                    $resultatsParUE = $resultatsEtudiant->groupBy('ue_nom');
                    $totalRowsForStudent = 0;

                    // Calculer le nombre total de lignes pour cet étudiant (UE + ECs)
                    foreach ($resultatsParUE as $ueNom => $resultatsUE) {
                        $totalRowsForStudent++; // Une ligne pour l'UE
                        $totalRowsForStudent += $resultatsUE->count(); // Lignes pour les ECs
                    }
                @endphp

                @foreach($resultatsParUE as $ueNom => $resultatsUE)
                    @php
                        $ueAbr = $resultatsUE->first()['ue_abr'] ?? 'UE';
                        $ueCredits = $resultatsUE->first()['ue_credits'] ?? 0;
                        $ueDisplay = $ueAbr . '.' . $ueNom . ($ueCredits ? " ({$ueCredits})" : '');
                        $rowSpanForUE = $resultatsUE->count() + 1; // UE row + EC rows
                    @endphp

                    <!-- Ligne pour l'UE -->
                    <tr>
                        @if($loop->first)
                            <td class="center" rowspan="{{ $totalRowsForStudent }}">{{ $numeroOrdre }}</td>
                            <td class="center" rowspan="{{ $totalRowsForStudent }}">{{ $matricule }}</td>
                            <td rowspan="{{ $totalRowsForStudent }}">{{ $premierResultat['nom'] }}</td>
                            <td rowspan="{{ $totalRowsForStudent }}">{{ $premierResultat['prenom'] }}</td>
                        @endif
                        <td style="font-weight: bold;">{{ $ueDisplay }}</td>
                        <td></td>
                        <td></td>
                        @if($afficherMoyennesUE)
                            <td class="center" rowspan="{{ $rowSpanForUE }}">
                                @php
                                    $moyenneUE = $resultatsUE->first()['moyenne_ue'] ?? null;
                                @endphp
                                {{ $moyenneUE !== null ? number_format((float)$moyenneUE, 2, '.', '') : '' }}
                            </td>
                        @endif
                        <td></td>
                    </tr>

                    <!-- Lignes pour les ECs -->
                    @foreach($resultatsUE as $index => $resultat)
                        <tr>
                            <td style="padding-left: 15px;">- EC{{ $index + 1 }}. {{ $resultat['matiere'] }}</td>
                            <td>{{ $resultat['enseignant'] ?? 'N/A' }}</td>
                            <td class="center">{{ number_format((float)$resultat['note'], 2, '.', '') }}</td>
                            @if($afficherMoyennesUE)
                                <!-- Moyenne UE already displayed in the UE row -->
                            @endif
                            <td>{{ $resultat['commentaire'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @endforeach

                @php $numeroOrdre++; @endphp

                <!-- Ligne de séparation entre étudiants -->
                @if($loop->iteration < $resultatsGroupes->count())
                    <tr style="height: 3px;">
                        <td colspan="{{ $afficherMoyennesUE ? '9' : '8' }}" style="border: none;"></td>
                    </tr>
                @endif
            @endforeach

            @if($resultatsGroupes->isEmpty())
                <tr>
                    <td colspan="{{ $afficherMoyennesUE ? '9' : '8' }}" class="center">
                        Aucun résultat trouvé.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Document généré le {{ $dateExport }}</p>
    </div>
</body>
</html>
