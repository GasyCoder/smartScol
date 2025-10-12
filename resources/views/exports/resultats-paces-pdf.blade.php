<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titre_document ?? 'Résultats PACES' }}</title>
    <style>
        @page {
            margin: 10mm 15mm 25mm 15mm;
            size: A4 portrait;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 14px; /* ✅ AUGMENTÉ de 11px à 12px */
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.3; /* ✅ AUGMENTÉ de 1.2 à 1.3 */
            background: #fff;
        }

        /* ===== HEADER ===== */
        .header-officiel {
            margin-bottom: 15px;
            page-break-inside: avoid;
            text-align: center;
        }

        .header-officiel img {
            max-width: 90%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        hr {
            margin: 10px 0 15px 0;
            border: 1px solid #000;
        }

        /* ===== TITRE ===== */
        .titre-principal {
            text-align: center;
            margin: 20px 0 15px 0;
            page-break-inside: avoid;
        }

        .titre-principal h1 {
            font-size: 16px; /* ✅ AUGMENTÉ de 15px à 16px */
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .annee-universitaire {
            font-size: 14px; /* ✅ AUGMENTÉ de 13px à 14px */
            font-weight: bold;
            margin: 8px 0;
            text-transform: uppercase;
        }

        /* ===== TABLEAU ===== */
        .table-container {
            margin: 25px auto;
            width: 100%;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 14px; /* ✅ AUGMENTÉ de 11px à 12px */
        }

        .results-table th {
            background-color: #f0f0f0;
            color: #000;
            padding: 8px 10px; /* ✅ AUGMENTÉ de 6px 8px à 8px 10px */
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 14px; /* ✅ AUGMENTÉ de 10px à 12px */
            text-transform: uppercase;
            vertical-align: middle;
        }

        .results-table td {
            padding: 6px 10px; /* ✅ AUGMENTÉ de 4px 8px à 6px 10px */
            border: 1px solid #000;
            text-align: left;
            font-size: 14px; /* ✅ AUGMENTÉ de 10px à 12px */
            vertical-align: middle;
            color: #000;
        }

        /* ===== COLONNES - TAILLES AUGMENTÉES ===== */
        .col-numero {
            width: 8%;
            text-align: center !important;
            font-size: 14px; /* ✅ Taille cohérente */
        }

        .col-matricule {
            width: 15%;
            text-align: left !important;
            font-size: 14px; /* ✅ AUGMENTÉ */
        }

        .col-nom {
            width: 30%;
            text-align: left !important;
            text-transform: uppercase;
            font-size: 14px; /* ✅ AUGMENTÉ */
        }

        .col-prenoms {
            width: 30%;
            text-align: left !important;
            font-size: 14px; /* ✅ AUGMENTÉ */
        }

        .col-decision {
            width: 17%;
            text-align: center !important;
            font-weight: normal;
            font-size: 14px; /* ✅ AUGMENTÉ */
            color: #000 !important;
        }

        /* ===== INFORMATIONS ===== */
        .info-supplementaire {
            margin: 15px 0;
            font-size: 12px; /* ✅ AUGMENTÉ de 11px à 12px */
            line-height: 1.4; /* ✅ AUGMENTÉ */
        }

        .total-etudiants {
            font-weight: bold;
            margin: 10px 0;
            text-align: justify;
            font-size: 12px; /* ✅ AUGMENTÉ */
        }

        .conditions {
            font-weight: bold;
            margin: 10px 0;
            text-align: justify;
            font-size: 12px; /* ✅ AUGMENTÉ */
        }

        /* ===== SIGNATURE ===== */
        .signature-section {
            margin-top: 30px;
            text-align: right;
            page-break-inside: avoid;
        }

        .lieu-date {
            margin-bottom: 40px;
            font-size: 12px; /* ✅ AUGMENTÉ */
            font-weight: bold;
        }

        .signature-nom {
            font-size: 12px; /* ✅ AUGMENTÉ */
            font-weight: bold;
            margin-top: 30px;
            text-decoration: underline;
        }

        /* ===== PAGINATION FOOTER (VISIBLE) ===== */
        .footer-pagination {
            position: fixed;
            bottom: 5mm; /* ✅ Position fixe en bas */
            left: 0;
            right: 0;
            font-size: 11px; /* ✅ AUGMENTÉ de 10px à 11px */
            font-weight: 600; /* ✅ AJOUTÉ pour visibilité */
            text-align: center;
            color: #333; /* ✅ CHANGÉ de #666 à #333 (plus foncé) */
            padding: 5px 0;
            border-top: 1px solid #ddd; /* ✅ AJOUTÉ bordure */
            background-color: #fff; /* ✅ Fond blanc */
        }

        .small-text {
            font-size: 10px; /* ✅ AUGMENTÉ de 9px à 10px */
            line-height: 1.2;
        }
    </style>
</head>

<body>
    {{-- HEADER OFFICIEL --}}
    <div class="header-officiel">
        @if(!empty($header_image_base64))
            <img src="{{ $header_image_base64 }}" alt="En-tête Faculté de Médecine">
        @endif
        <hr>
    </div>


    {{-- TITRE PRINCIPAL --}}
    <div class="titre-principal" style="text-align:center; margin:20px 0 15px 0; page-break-inside:avoid;">
        <!-- Ligne 1 : RÉSULTATS CONCOURS - PACES -->
        <div style="font-size:16px; font-weight:700; text-transform:uppercase;">
            {{ $titre_document ?? 'RÉSULTATS CONCOURS - PACES' }}
        </div>
        
        <!-- Ligne 2 : LISTE DES ÉTUDIANTS [ADMIS/REDOUBLANTS/EXCLUS] - PARCOURS [NOM] -->
        <div style="font-size:15px; font-weight:700; margin-top:6px; text-transform:uppercase;">
              {!! $titre_special ?? 'LISTE DES ÉTUDIANTS ADMIS - PARCOURS' !!}
        </div>
        
        <!-- Ligne 3 : ANNÉE UNIVERSITAIRE 2024-2025 -->
        <div class="annee-universitaire" style="font-size:13px; font-weight:700; margin-top:6px; text-transform:uppercase;">
            ANNÉE UNIVERSITAIRE {{ $annee_universitaire->libelle ?? '2024-2025' }}
        </div>
    </div>

    {{-- TABLEAU RÉSULTATS --}}
    <div class="table-container">
        @if(!empty($resultats) && count($resultats) > 0)
            <table class="results-table">
                <thead>
                    <tr>
                        @if($colonnes_config['rang'])
                            <th class="col-numero">N°</th>
                        @endif
                        
                        @if($colonnes_config['matricule'])
                            <th class="col-matricule">MATRICULE</th>
                        @endif
                        
                        @if($colonnes_config['nom_complet'])
                            <th class="col-nom">NOM</th>
                            <th class="col-prenoms">PRÉNOMS</th>
                        @endif
                        
                        @if($colonnes_config['decision'])
                            <th class="col-decision">DÉCISION</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultats as $index => $resultat)
                        @php
                            $etudiant = is_array($resultat) ? ($resultat['etudiant'] ?? null) : $resultat->etudiant;
                            
                            if ($etudiant) {
                                $nom = is_array($etudiant) ? ($etudiant['nom'] ?? '') : ($etudiant->nom ?? '');
                                $prenom = is_array($etudiant) ? ($etudiant['prenom'] ?? '') : ($etudiant->prenom ?? '');
                                $matricule = is_array($etudiant) ? ($etudiant['matricule'] ?? '') : ($etudiant->matricule ?? '');
                            } else {
                                $nom = $prenom = $matricule = '';
                            }

                            if (is_array($resultat)) {
                                $decision = $resultat['decision'] ?? 'non_definie';
                                $rang = $resultat['rang'] ?? ($index + 1);
                            } else {
                                $decision = $resultat->decision ?? 'non_definie';
                                $rang = $resultat->rang ?? ($index + 1);
                            }

                            $decisionText = match($decision) {
                                'admis' => 'ADMIS',
                                'redoublant' => 'REDOUBLANT',
                                'exclus' => 'EXCLUS',
                                default => 'NON DÉFINIE'
                            };
                        @endphp

                        <tr>
                            @if($colonnes_config['rang'])
                                <td class="col-numero">{{ $rang }}</td>
                            @endif
                            
                            @if($colonnes_config['matricule'])
                                <td class="col-matricule">{{ $matricule }}</td>
                            @endif
                            
                            @if($colonnes_config['nom_complet'])
                                <td class="col-nom">{{ strtoupper($nom) }}</td>
                                <td class="col-prenoms">{{ ucwords(strtolower($prenom)) }}</td>
                            @endif
                            
                            @if($colonnes_config['decision'])
                                <td class="col-decision">{{ $decisionText }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
        {{-- ✅ AFFICHER NÉANT au lieu de "Aucun résultat" --}}
        <div style="text-align: center; padding: 60px 20px; border: 3px solid #000; margin: 40px 0; background-color: #f9f9f9;">
            <div style="font-size: 48px; font-weight: 900; color: #000; margin-bottom: 15px; letter-spacing: 8px;">
                NÉANT
            </div>
        </div>
        @endif
    </div>

    {{-- INFORMATIONS FINALES --}}
    @if(!empty($resultats) && count($resultats) > 0)
        <div class="info-supplementaire">
            <div class="total-etudiants">
                Arrêtée la présente liste au nombre de <strong>{{ count($resultats) }}</strong>
                @if($type_document === 'admis_seulement')
                    candidat{{ count($resultats) > 1 ? 's' : '' }} admis
                @elseif($type_document === 'redoublant_seulement')
                    candidat{{ count($resultats) > 1 ? 's' : '' }} autorisé{{ count($resultats) > 1 ? 's' : '' }} au redoublement
                @elseif($type_document === 'exclus_seulement')
                    candidat{{ count($resultats) > 1 ? 's' : '' }} exclu{{ count($resultats) > 1 ? 's' : '' }}
                @else
                    candidat{{ count($resultats) > 1 ? 's' : '' }}
                @endif
            </div>
            
            <div class="conditions">
                {{ $conditions ?? 'Sous réserve de validation de Stage Hospitalier et des modules pratiques' }}
            </div>
        </div>
    @endif

    {{-- QR CODE FORMAT CORRECT --}}
    @if(!empty($qrcodeImage))
    <div style="text-align: left; margin-top: 20px; page-break-inside: avoid;">
        {{-- ✅ FORMAT EXACTEMENT COMME L'EXEMPLE QUI FONCTIONNE --}}
        <img 
            src="data:image/png;base64,{{ base64_encode($qrcodeImage) }}" 
            alt="QR Code Statistiques PACES" 
            width="100"
            height="100"/>
        <div style="margin-top: 10px; font-size: 10px; color: #666;">
            Code de vérification - Scannez pour voir les détails
        </div>
    </div>
    @endif


    {{-- SIGNATURE --}}
    <div class="signature-section">
        <div class="lieu-date">
            Mahajanga, le {{ now()->format('d/m/Y') }}
        </div>
        
        <div class="signature-nom">
            {{ $doyen_nom ?? 'Le Doyen' }}
        </div>
    </div>
</body>
</html>