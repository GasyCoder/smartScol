<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relevé des notes - {{ $etudiant->matricule }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;  /* ✅ Augmenté de 11px à 12px */
            line-height: 1.3;
            margin: 0;
            padding: 8px 12px;
            color: black;
        }
        
        .content-wrapper {
            max-width: 100%;
            padding: 10px 15px 15px 15px;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
            border-bottom: 1px solid black;
            padding-bottom: 5px;
        }
        
        .titre {
            font-size: 15px;  /* ✅ Augmenté de 14px à 15px */
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        
        .annee {
            font-size: 12px;  /* ✅ Augmenté de 11px à 12px */
            font-weight: normal;
        }
        
        .info {
            margin: 8px 0;
            background: none;
            padding: 0;
            font-size: 14px;
        }
        
        .info p {
            margin: 2px 0;
            font-size: 14px;  /* ✅ Augmenté de 11px à 12px */
        }
        
        /* Styles des tableaux */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 12px;  /* ✅ Gardé à 11px pour le tableau */
        }
        
        th, td {
            border: 1px solid black;
            padding: 4px 6px;  /* ✅ Augmenté de 3px 5px à 4px 6px */
            text-align: left;
        }
        
        .header-row th {
            background: none;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        .ue-header {
            background: none;
            font-weight: bold;
            font-size: 12px;
        }
        
        .ec-row td {
            font-size: 12px;
            padding-left: 15px;
        }
        
        .note-center {
            text-align: center;
        }
        
        .synthese-row {
            font-weight: bold;
            background: none;
            font-size: 12px;  /* ✅ Augmenté de 11px à 12px */
        }
        
        .total-row {
            font-weight: bold;
            border-top: 2px solid black;
        }
        
        .decision {
            text-align: left;
            font-size: 13px;  /* ✅ Augmenté de 12px à 13px */
            font-weight: bold;
            margin: 5px 0;
            padding: 6px;  /* ✅ Augmenté de 5px à 6px */
            border: 1px solid black;
            text-transform: uppercase;
        }
        
        .deliberation-info {
            margin: 3px 0;
            font-size: 10px;  /* ✅ Augmenté de 9px à 10px */
            font-style: italic;
            color: #666;
        }
        
        .deliberation-info p {
            margin: 1px 0;
        }
        
        .note-admission {
            margin: 5px 0;
            font-size: 11px;  /* ✅ Augmenté de 9px à 10px */
            font-style: italic;
        }
        
        .note-admission p {
            margin: 2px 0;
        }
        
        .date-generation {
            text-align: right;
            font-size: 11px;  /* ✅ Augmenté de 10px à 11px */
            margin: 5px 0;
        }

        .signature-space {
            height: 80px;  /* ✅ Réduit de 60px à 50px */
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .footer-info {
            position: fixed;
            left:0px;
            right:0px;
            margin-top: 30px;  /* ✅ Espace avant le footer */
            text-align: center;
            font-size: 10px;  /* ✅ Augmenté de 9px à 10px */
            border-top: 1px solid black;
            padding: 8px 15px;
            background: white;
        }
                
        .footer-info p {
            margin: 2px 0;
        }

        
        @media print {
            body { 
                margin: 0; 
                padding: 8px 12px;
            }
            .content-wrapper { 
                page-break-inside: avoid;
            }
        }

        /* ✅ QR CODE STYLES */
        .footer-qrcode {
            float: left;
            text-align: center;
            margin: 5px 0;
            padding: 5px;
        }
        
        .qr-img {
            width: 100px;
            height: 100px;
            display: block;
            margin: 0 auto;
        }
        
        .qr-legend {
            font-size: 8px;
            color: #666;
            margin-top: 2px;
            font-style: italic;
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
        </div>

        <!-- Tableau des résultats par UE -->
        <table>
            <tr class="header-row">
                <th style="width: 5%;">N°</th>
                <th style="width: 65%;">UNITÉ D'ENSEIGNEMENT</th>
                <th style="width: 15%;">NOTE</th>
                <th style="width: 15%;">CRÉDIT</th>
            </tr>
            
            @php 
                $numeroUE = 1; 
                $totalPoints = 0;
                $totalMaxPoints = 0;
            @endphp
            
            @foreach($ues_data as $ueData)
                @php
                    $totalPoints += $ueData['moyenne_ue'];
                    $totalMaxPoints += 20;
                @endphp
                <tr class="ue-row">
                    <td class="note-center">{{ $numeroUE }}</td>
                    <td>{{ $ueData['ue']->abr ? $ueData['ue']->abr . ' - ' : '' }}{{ strtoupper($ueData['ue']->nom) }}</td>
                    <td class="note-center">{{ number_format($ueData['moyenne_ue'], 2) }}/20</td>
                    <td class="note-center">
                        @if($ueData['validee'])
                            {{ $ueData['credits'] }}/{{ $ueData['credits'] }}
                        @else
                            0/{{ $ueData['credits'] }}
                        @endif
                    </td>
                </tr>
                @php $numeroUE++; @endphp
            @endforeach
            
            <!-- Ligne TOTAL -->
            <tr class="synthese-row">
                <td colspan="2"><strong>TOTAL</strong></td>
                <td class="note-center"><strong>{{ number_format($totalPoints, 2) }}/{{ $totalMaxPoints }}</strong></td>
                <td class="note-center"><strong>{{ $synthese['credits_valides'] }}/{{ $synthese['total_credits'] }}</strong></td>
            </tr>
            
            <!-- Ligne MOYENNE GÉNÉRALE -->
            <tr class="synthese-row">
                <td colspan="2"><strong>MOYENNE GÉNÉRALE</strong></td>
                <td class="note-center" colspan="2"><strong>{{ number_format($synthese['moyenne_generale'], 2) }}/20</strong></td>
            </tr>
        </table>

        <!-- Décision finale -->
        <div class="decision decision-{{ $synthese['decision'] }}">
            RÉSULTAT : 
            @if($synthese['decision'] === 'admis')
                <span style="color: #006400;">ADMIS(E)</span>
            @elseif($synthese['decision'] === 'rattrapage')
                <span style="color: #d97706;">AUTORISÉ(E) AU RATTRAPAGE</span>
            @elseif($synthese['decision'] === 'redoublant')
                <span style="color: #d97706;">AUTORISÉ(E) À REDOUBLER</span>
            @elseif($synthese['decision'] === 'exclus')
                <span style="color: #b91c1c;">EXCLUS(E)</span>
            @else
                {{ strtoupper($synthese['decision']) }}
            @endif
        </div>

        <!-- Note sur les conditions d'admission -->
        <div class="note-admission">
            <p><strong>Note :</strong>
                @if($synthese['has_note_eliminatoire'])
                    Une note éliminatoire (0) a été détectée, rendant certaines UE non validées.
                @endif
                Les règles d'admission varient selon le niveau d'études et le parcours.
            </p>
        </div>

        {{-- FOOTER QR CODE --}}
        @if(!empty($qrcodeImage))
        <div class="footer-qrcode">
            <img class="qr-img" src="data:image/svg+xml;base64,{{ base64_encode($qrcodeImage) }}" alt="QR Code Étudiant">
            <div class="qr-legend">Scannez pour vérifier</div>
        </div>
        @endif
        
        <!-- Date de génération -->
        <div class="date-generation">
            <p>Fait à Mahajanga, le {{ $date_generation }}</p>
        </div>

        <!-- Espace pour le cachet -->
        <div class="signature-space"></div>

        <!-- Footer -->
        <div class="footer-info">
            <p>BP : 652 – Mahajanga 401 – Madagascar | Tél : +261 38 41 930 47</p>
            <p>Mail : facmed.mga@gmail.com | Web : www.medecine.mahajanga-univ.mg</p>
            <p><em>Ce document est officiel et certifie les résultats obtenus par l'étudiant(e) pour la session {{ $session->type }}.</em></p>
        </div>

    </div>
</body>
</html>