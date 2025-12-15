<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relevé des notes - {{ $etudiant->matricule }}</title>
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 11px;
        line-height: 1.2;
        margin: 0;
        padding: 5px 10px;
        color: black;
    }
    
    .content-wrapper {
        max-width: 100%;
        padding: 5px 10px;
        box-sizing: border-box;
    }

    .header {
        text-align: center;
        margin-bottom: 3px;
        border-bottom: 1px solid black;
        padding-bottom: 2px;
    }
    
    .titre {
        font-size: 14px;
        font-weight: bold;
        margin: 3px 0;
        text-transform: uppercase;
    }
    
    /* ✅ MODIFIÉ : Style simple pour la session */
    .session-info {
        font-size: 11px;
        font-weight: bold;
        margin: 2px 0;
        color: black;
    }
    
    .annee {
        font-size: 10px;
        font-weight: normal;
    }
    
    .info {
        margin: 5px 0;
        background: none;
        padding: 0;
        font-size: 11px;
    }
    
    .info p {
        margin: 1px 0;
        font-size: 11px;
    }
    
    /* Styles des tableaux */
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 3px 0;
        font-size: 10px;
    }
    
    th, td {
        border: 1px solid black;
        padding: 2px 4px;
        text-align: left;
    }
    
    .header-row th {
        background: none;
        font-weight: bold;
        text-align: center;
        text-transform: uppercase;
        font-size: 10px;
    }
    
    .ue-header {
        background: none;
        font-weight: bold;
        font-size: 10px;
    }
    
    .ec-row td {
        font-size: 10px;
        padding-left: 10px;
    }
    
    .note-center {
        text-align: center;
    }
    
    .synthese-row {
        font-weight: bold;
        background: none;
        font-size: 10px;
    }
    
    .total-row {
        font-weight: bold;
        border-top: 2px solid black;
    }
    
    .decision {
        text-align: left;
        font-size: 11px;
        font-weight: bold;
        margin: 3px 0;
        padding: 4px;
        border: 1px solid black;
        text-transform: uppercase;
        color: black;
    }
    
    .note-admission {
        margin: 3px 0;
        font-size: 9px;
        font-style: italic;
    }
    
    .note-admission p {
        margin: 1px 0;
    }
    
    .date-generation {
        text-align: right;
        font-size: 9px;
        margin: 3px 0;
    }

    .signature-space {
        height: 50px;
        margin-top: 15px;
        margin-bottom: 10px;
    }

    .footer-info {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 8px;
        border-top: 1px solid black;
        padding: 5px 10px;
        background: white;
    }
            
    .footer-info p {
        margin: 1px 0;
    }

    @media print {
        body { 
            margin: 0; 
            padding: 5px 10px;
        }
        .content-wrapper { 
            page-break-inside: avoid;
        }
    }

    .footer-qrcode {
        float: left;
        text-align: center;
        margin: 3px 0;
        padding: 3px;
    }
    
    .qr-img {
        width: 80px;
        height: 80px;
        display: block;
        margin: 0 auto;
    }
    
    .qr-legend {
        font-size: 7px;
        color: #666;
        margin-top: 1px;
        font-style: italic;
    }
</style>
</head>
<body>
    <div class="content-wrapper">
        <!-- En-tête -->
        <div class="header">
            <div class="header-officiel">
                @if(!empty($header_image_base64))
                    <img src="{{ $header_image_base64 }}" 
                         alt="En-tête Faculté de Médecine"
                         style="max-width: 100%; height: auto;">
                @endif
                <hr style="border: none; height: 1px; background-color: rgba(0, 0, 0, 0.2); margin: 8px 0;">
            </div>
            
            <h1 class="titre">RELEVÉ DE NOTES</h1>
            
            {{-- ✅ MODIFIÉ : Session simple en noir, sans badge ni couleur --}}
            <div class="session-info">
                @if($session->type === 'Normale')
                    SESSION NORMALE (Première Session)
                @elseif($session->type === 'Rattrapage')
                    SESSION DE RATTRAPAGE (Deuxième Session)
                @else
                    {{ strtoupper($session->type) }}
                @endif
            </div>
            
            <span class="annee">Année universitaire: {{ $session->anneeUniversitaire->libelle ?? 'Année universitaire' }}</span>
        </div>

        <!-- Informations étudiant -->
        <div class="info">
            <p><strong>Nom :</strong> {{ strtoupper($etudiant->nom) }}</p>
            @if($etudiant->prenom)
                <p><strong>Prénoms :</strong> {{ ucfirst($etudiant->prenom) }}</p>
            @endif
            <p><strong>Numéro matricule :</strong> {{ $etudiant->matricule }}</p>
            <p><strong>Parcours :</strong> {{ $etudiant->parcours?->nom ?? 'Tronc Commun' }}</p>
            <p><strong>Année d'études :</strong> {{ $etudiant->niveau?->abr ?? 'N/A' }}</p>
        </div>

        <!-- Tableau des résultats par UE -->
        <table>
            <tr class="header-row">
                <th style="width: 5%;">N°</th>
                <th style="width: 65%;">UNITÉS D'ENSEIGNEMENTS</th>
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
                    <td style="text-transform: uppercase;">{{ $ueData['ue']->abr ? $ueData['ue']->abr . ' - ' : '' }}{{ $ueData['ue']->nom }}</td>
                    <td class="note-center">{{ number_format($ueData['moyenne_ue'], 2) }}/20</td>
                    <td class="note-center">
                        {{ $ueData['credits_valides']}}
                    </td>
                </tr>
                @php $numeroUE++; @endphp
            @endforeach
            
            <!-- Ligne TOTAL -->
            <tr class="synthese-row">
                <td colspan="2"><strong>TOTAL</strong></td>
                <td class="note-center"><strong>{{ number_format($totalPoints, 2) }}/{{ $totalMaxPoints }}</strong></td>
                <td class="note-center">
                    <strong>
                        {{ $synthese['credits_valides'] }}/{{ $synthese['total_credits'] }}
                    </strong>
                </td>
            </tr>
            
            <!-- Ligne MOYENNE GÉNÉRALE -->
            <tr class="synthese-row">
                <td colspan="2"><strong>MOYENNE GÉNÉRALE</strong></td>
                <td class="note-center" colspan="2">
                    <strong>{{ number_format($synthese['moyenne_generale'], 2) }}/20</strong>
                </td>
            </tr>
        </table>

        {{-- ✅ MODIFIÉ : Décision finale sans couleur --}}
        <div class="decision">
            RÉSULTAT : 
            @if($synthese['decision'] === 'admis')
                {{ strtoupper($synthese['message_admission'] ?? 'ADMIS(E)') }}
            @elseif($synthese['decision'] === 'rattrapage')
                AUTORISÉ(E) AU RATTRAPAGE
            @elseif($synthese['decision'] === 'redoublant')
                {{ strtoupper($synthese['message_redoublement'] ?? 'AUTORISÉ(E) À REDOUBLER') }}
            @elseif($synthese['decision'] === 'exclus')
                EXCLUS(E)
            @else
                {{ strtoupper($synthese['decision']) }}
            @endif
        </div>

        <!-- Note complémentaire pour admission avec niveau suivant -->
        @if($synthese['decision'] === 'admis' && !empty($synthese['niveau_suivant']))
            <div class="note-admission">
                <p style="font-style: italic; margin-top: 5px;">
                    L'étudiant(e) est autorisé(e) à s'inscrire en {{ $synthese['niveau_suivant'] }} 
                    pour l'année universitaire {{ now()->year }}-{{ now()->year + 1 }}.
                </p>
            </div>
        @endif
        {{-- ✅ NOUVEAU : Note éliminatoire PACES --}}
        @if($etudiant->niveau && $etudiant->niveau->abr === 'PACES' && !empty($synthese['has_note_eliminatoire_paces']) && $synthese['has_note_eliminatoire_paces'])
            <div class="note-admission">
                <p style="color: #d32f2f; font-weight: bold; margin-top: 5px;">
                    ⚠ Vous avez une note éliminatoire (note = 0). 
                    Les crédits des UE concernées ne sont pas validés même si la moyenne est >= 10/20.
                </p>
            </div>
        @endif    
        <!-- Note sur les conditions d'admission -->
        <div class="note-admission">
            @if($synthese['decision'] === 'redoublant')
                <p><strong>Note :</strong>
                    Seuil de Redoublement : {{ $deliberation->credit_min_r ?? 'N/A' }} crédits avec moyenne générale >= {{ number_format($deliberation->moyenne_min_r ?? 0, 2) }}/20.
                </p>
            @endif
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
            <p>Fait à Mahajanga, le.......................</p>
        </div>

        <!-- Espace pour le cachet -->
        <div class="signature-space"></div>

        <!-- Footer -->
        <div class="footer-info">
            <p>BP : 652 – Mahajanga 401 – Madagascar | Tél : +261 38 41 930 47</p>
            <p>Mail : facmed.mga@gmail.com | Web : www.medecine.mahajanga-univ.mg</p>
            <p><em>Document officiel attestant les résultats de l'étudiant(e) via le logiciel SmartScol de la Faculté de Médecine.</em></p>
        </div>

    </div>
</body>
</html>