<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relevé des notes - {{ $etudiant->matricule }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 15px;
            color: black;
        }
        
        .content-wrapper {
            max-width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 5px;
            border-bottom: 1px solid black;
            padding-bottom: 5px;
        }
        
     
        
        .titre {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        
        .annee {
            font-size: 12px;
            font-weight: normal;
        }
        
        .info {
            margin: 15px 0;
            background: none;
            padding: 0;
        }
        
        .info p {
            margin: 5px 0;
            font-size: 11px;
        }
        
        /* Styles des tableaux */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10px;
        }
        
        th, td {
            border: 1px solid black;
            padding: 4px 6px;
            text-align: left;
        }
        
        .header-row th {
            background: none;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        
        .ue-header {
            background: none;
            font-weight: bold;
            font-size: 11px;
        }
        
        .ec-row td {
            font-size: 10px;
            padding-left: 15px;
        }
        
        .note-center {
            text-align: center;
        }
        
        .synthese-row {
            font-weight: bold;
            background: none;
        }
        
        .total-row {
            font-weight: bold;
            border-top: 2px solid black;
        }
        
        .decision {
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
            padding: 5px;
            border: 1px solid black;
            text-transform: uppercase;
        }
        
        .note-admission {
            margin: 10px 0;
            font-size: 10px;
            font-style: italic;
        }
        
        .footer-info {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            border-top: 1px solid black;
            padding-top: 38px;
        }
        
        .date-generation {
            text-align: right;
            font-size: 10px;
            margin: 10px 0;
        }
        
        @media print {
            body { margin: 0; padding: 10px; }
            .content-wrapper { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <!-- En-tête -->
        <div class="header">
            {{-- HEADER OFFICIEL COMPACT --}}
            <div class="header-officiel">
                @if(!empty($header_image_base64))
                    <img src="{{ $header_image_base64 }}" 
                         alt="En-tête Faculté de Médecine"
                         style="max-width: 100%; height: auto;">
                @endif
                <hr style="border: none; height: 1px; background-color: rgba(0, 0, 0, 0.2); margin: 15px 0;">
            </div>
            <h1 class="titre">RELEVÉ DES NOTES POUR VERIFICATION</h1>
            <span class="annee">Année universitaire: {{ $session->anneeUniversitaire->libelle ?? 'Année universitaire' }} - Session: {{ $session->type }}</span>
        </div>

        <!-- Informations étudiant -->
        <div class="info">
            <p><strong>Nom :</strong> {{ strtoupper($etudiant->nom) }}</p>
            @if($etudiant->prenom)
                <p><strong>Prénoms :</strong> {{ ucfirst($etudiant->prenom) }}</p>
            @endif
            <p><strong>Numéro matricule :</strong> {{ $etudiant->matricule }}</p>
            <p><strong>Parcours :</strong> {{ $etudiant->parcours?->nom ?? 'Tronc Commun' }}</p>
            <p><strong>Année d'études :</strong> {{ $etudiant->niveau?->nom ?? 'N/A' }}</p>
            @if($etudiant->date_naissance)
                <p><strong>Date de naissance :</strong> {{ date('d/m/Y', strtotime($etudiant->date_naissance)) }}</p>
            @endif
        </div>

        <!-- Tableau des résultats par UE -->
        <table>
            <tr class="header-row">
                <th style="width: 3%;">N°</th>
                <th style="width: 50%;">UNITÉ D'ENSEIGNEMENT / ÉLÉMENTS CONSTITUTIFS</th>
                <th style="width: 10%;">NOTE/20</th>
                <th style="width: 8%;">CRÉDIT</th>
                <th style="width: 10%;">STATUT</th>
            </tr>
            
            @php $numeroUE = 1; @endphp
            @foreach($ues_data as $ueData)
                <!-- Ligne UE -->
                <tr class="ue-header">
                    <td class="note-center"><strong>{{ $numeroUE }}</strong></td>
                    <td><strong>
                        {{ $ueData['ue']->abr ? $ueData['ue']->abr . ' - ' : '' }}{{ $ueData['ue']->nom }}
                    </strong></td>
                    <td class="note-center"><strong>{{ number_format($ueData['moyenne_ue'], 2) }}</strong></td>
                    <td class="note-center"><strong>{{ $ueData['credits'] }}</strong></td>
                    <td class="note-center"><strong>
                        @if($ueData['validee'])
                            VALIDÉE
                        @elseif($ueData['eliminees'])
                            ÉLIMINÉE
                        @else
                            NON VALIDÉE
                        @endif
                    </strong></td>
                </tr>
                
                <!-- Lignes des EC de cette UE -->
                @foreach($ueData['notes_ec'] as $noteEC)
                    <tr class="ec-row">
                        <td></td>
                        <td>
                            - {{ $noteEC['ec']->abr ? $noteEC['ec']->abr . ' - ' : '' }}{{ $noteEC['ec']->nom }}
                            @if($noteEC['ec']->enseignant)
                                <br><small>Ens: {{ $noteEC['ec']->enseignant }}</small>
                            @endif
                        </td>
                        <td class="note-center">{{ number_format($noteEC['note'], 2) }}</td>
                        <td class="note-center">-</td>
                        <td class="note-center">
                            @if($noteEC['est_eliminatoire'])
                                ÉLIMINATOIRE
                            @elseif($noteEC['note'] >= 10)
                                VALIDÉ
                            @else
                                NON VALIDÉ
                            @endif
                        </td>
                    </tr>
                @endforeach
                @php $numeroUE++; @endphp
            @endforeach
            
            <!-- Ligne de synthèse -->
            <tr class="synthese-row">
                <td colspan="2"><strong>SYNTHÈSE GÉNÉRALE</strong></td>
                <td class="note-center"><strong>{{ number_format($synthese['moyenne_generale'], 2) }}</strong></td>
                <td class="note-center"><strong>{{ $synthese['credits_valides'] }}/{{ $synthese['total_credits'] }}</strong></td>
                <td class="note-center"><strong>{{ number_format($synthese['pourcentage_credits'], 1) }}%</strong></td>
            </tr>
        </table>

        <!-- Décision finale -->
        <div class="decision decision-{{ $synthese['decision'] }}">
            DÉCISION : 
            @switch($synthese['decision'])
                @case('admis')
                    ADMIS(E)
                    @break
                @case('rattrapage')
                    AUTORISÉ(E) AU RATTRAPAGE
                    @break
                @case('redoublant')
                    AUTORISÉ(E) À REDOUBLER
                    @break
                @case('excluss')
                    EXCLU(E)
                    @break
                @default
                    {{ strtoupper($synthese['decision']) }}
            @endswitch
        </div>
        @if($synthese['session_deliberee'])
        <div class="deliberation-info" style="text-align: left; margin-top: 8px; font-size: 10px; font-style: italic; color: #666;">
            <p><strong>Session délibérée</strong> - Décision appliquée selon les critères de délibération</p>
            @if($session->type === 'Normale')
                <p>Critères : 75% de crédits requis, moyenne >= 10/20, aucune note éliminatoire</p>
            @else
                <p>Critères : 67% de crédits requis (rattrapage), moyenne >= 10/20, aucune note éliminatoire</p>
            @endif
        </div>
        @endif
        <!-- Note sur les conditions d'admission -->
        <div class="note-admission">
            <p><strong>Note :</strong>
                @if($synthese['has_note_eliminatoire'])
                    Une note éliminatoire (0) a été détectée, rendant certaines UE non validées.
                @endif
                Les règles d'admission varient selon le niveau d'études et le type de session.
            </p>
        </div>

        <!-- Date de génération -->
        <div class="date-generation">
            <p>Fait à Mahajanga, le {{ $date_generation }}</p>
        </div>

        <!-- Informations de contact -->
        <div class="footer-info">
            <p>BP : 652 – Mahajanga 401 – Madagascar | Tél : +261 38 41 930 47</p>
            <p>Mail : facmed.mga@gmail.com | Web : www.medecine.mahajanga-univ.mg</p>
            <p><em>Ce document est officiel et certifie les résultats obtenus par l'étudiant(e) pour la session {{ $session->type }}.</em></p>
        </div>
    </div>
</body>
</html>