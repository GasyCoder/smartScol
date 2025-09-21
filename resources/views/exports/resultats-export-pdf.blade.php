{{-- resources/views/exports/admis-deliberation-pdf.blade.php OPTIMISÉ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titre_document ?? 'Liste des Résultats' }}</title>
    <style>
        @page {
            margin: 10mm 15mm 15mm 15mm; /* Marges réduites */
            size: A4 portrait;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px; /* Taille réduite */
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.2; /* Interligne réduit */
            background: #fff;
        }

        /* ===== HEADER OPTIMISÉ ===== */
        .header-officiel {
            margin-bottom: 15px; /* Réduit de 30px à 15px */
            page-break-inside: avoid;
            text-align: center;
        }

        .header-officiel img {
            max-width: 90%; /* Légèrement réduit */
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* ===== TITRE PRINCIPAL COMPACT ===== */
        .titre-principal {
            text-align: center;
            margin: 20px 0 15px 0; /* Réduit */
            page-break-inside: avoid;
        }

        .titre-principal h1 {
            font-size: 15px; /* Réduit */
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 8px 0; /* Réduit */
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .annee-universitaire {
            font-size: 13px; /* Réduit */
            font-weight: bold;
            margin: 8px 0; /* Réduit */
            text-transform: uppercase;
        }

        .sous-titre {
            font-size: 11px;
            margin: 3px 0; /* Réduit */
            font-weight: bold;
        }

        /* ===== TABLEAU OPTIMISÉ ===== */
        .table-container {
            margin: 18px auto; /* Réduit */
            width: 100%;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px; /* Réduit */
            font-size: 11px; /* Réduit */
        }

        .results-table th {
            background-color: #f0f0f0; /* Léger fond gris */
            color: #000;
            padding: 6px 8px; /* Réduit */
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 14px; /* Réduit */
            text-transform: uppercase;
            vertical-align: middle;
        }

        .results-table td {
            padding: 4px 8px; /* Réduit */
            border: 1px solid #000;
            text-align: left;
            font-size: 14px;
            vertical-align: middle;
            background-color: transparent;
        }

        /* ===== COLONNES OPTIMISÉES SANS MATRICULE ===== */
        .col-numero {
            width: 8%; /* Réduit */
            text-align: center !important;
            font-size: 14px;
        }

        .col-nom {
            width: 46%; /* Élargi sans matricule */
            text-align: left !important;
            text-transform: uppercase;
            font-size: 14px;
        }

        .col-prenoms {
            width: 46%; /* Élargi sans matricule */
            text-align: left !important;
            font-size: 14px;
        }

        /* Colonne matricule supprimée */

        .col-moyenne {
            width: 12%;
            text-align: center !important;
            font-size: 11px;
        }

        .col-credits {
            width: 12%;
            text-align: center !important;
            font-size: 11px;
        }

        .col-decision {
            width: 16%;
            text-align: center !important;
            font-size: 10px;
        }

        /* ===== STYLES POUR LISTES ADMIS OPTIMISÉS ===== */
        .liste-admis-only .col-decision,
        .liste-admis-only .col-moyenne,
        .liste-admis-only .col-credits {
            display: none;
        }

        .liste-admis-only .col-nom {
            width: 54%; /* Plus large sans les autres colonnes */
        }

        .liste-admis-only .col-prenoms {
            width: 38%;
        }

        /* ===== INFORMATIONS COMPACTES ===== */
        .info-supplementaire {
            margin: 15px 0; /* Réduit */
            font-size: 11px;
            line-height: 1.3;
        }

        .total-etudiants {
            font-weight: bold;
            margin: 10px 0; /* Réduit */
            text-align: justify;
            font-size: 11px; /* Réduit */
        }

        .conditions {
            font-weight: bold;
            margin: 10px 0; /* Réduit */
           text-align: justify;
           font-size: 11px; /* Réduit */
        }

        /* ===== SIGNATURE COMPACTE ===== */
        .signature-section {
            margin-top: 30px; /* Réduit */
            text-align: right;
            page-break-inside: avoid;
        }

        .lieu-date {
            margin-bottom: 40px; /* Réduit */
            font-size: 11px;
            font-weight: bold;
        }

        .signature-titre {
            font-size: 10px;
            margin-bottom: 4px;
            font-style: italic;
            color: #666;
        }

        .signature-nom {
            font-size: 11px;
            font-weight: bold;
            margin-top: 30px; /* Réduit */
            text-decoration: underline;
        }

        .signature-fonction {
            font-size: 9px;
            font-style: italic;
            margin-top: 4px;
            color: #666;
        }

        /* ===== FOOTER COMPACT ===== */
        .footer-stats {
            position: fixed;
            bottom: 8mm;
            left: 15mm;
            right: 15mm;
            font-size: 9px; /* Réduit */
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 3px;
            background-color: #fff;
        }

        /* ===== OPTIMISATIONS SUPPLÉMENTAIRES ===== */
        hr {
            margin: 10px 0 15px 0; /* Réduit */
            border: 1px solid #000;
        }

        /* Réduire l'espace entre les lignes du tableau */
        .results-table tr {
            line-height: 1.1;
        }

        /* Optimisation pour les petits textes */
        .small-text {
            font-size: 9px;
            line-height: 1.1;
        }
    </style>
</head>
<body>
    {{-- HEADER OFFICIEL COMPACT --}}
    <div class="header-officiel">
        @if(!empty($header_image_base64))
            <img src="{{ $header_image_base64 }}" 
                 alt="En-tête Faculté de Médecine">
        @else
            <div style="text-align: center; padding: 10px; border: 2px solid #000;">
                <div style="font-size: 14px; font-weight: bold;">UNIVERSITÉ DE MAHAJANGA</div>
                <div style="font-size: 16px; font-weight: bold;">FACULTÉ DE MÉDECINE</div>
            </div>
        @endif
        <hr>
    </div>

    {{-- TITRE PRINCIPAL COMPACT --}}
    <div class="titre-principal">
        <h1>{{ $titre_document ?? 'LISTE DES RÉSULTATS' }}</h1>
        
        <div class="annee-universitaire">
            ANNÉE UNIVERSITAIRE {{ $annee_universitaire->libelle ?? date('Y') . '-' . (date('Y') + 1) }}
        </div>
        
        <div class="sous-titre">
            ({{ $session_info['type_complet'] ?? 'Session' }} - Par ordre de Mérite)
        </div>
    </div>

    {{-- TABLEAU OPTIMISÉ SANS MATRICULE --}}
    <div class="table-container">
        @if(!empty($resultats) && count($resultats) > 0)
            @php
                $isAdmisOnly = isset($titre_special) && str_contains($titre_special, 'ADMIS');
                $tableClass = $isAdmisOnly ? 'liste-admis-only' : '';
                $resultatsTriés = collect($resultats)->sortBy('rang')->values();
            @endphp
            
            <table class="results-table {{ $tableClass }}">
                <thead>
                    <tr>
                        @if(($colonnes_config['rang'] ?? true))
                            <th class="col-numero">N°</th>
                        @endif
                        
                        @if(($colonnes_config['nom_complet'] ?? true))
                            <th class="col-nom">NOM</th>
                            <th class="col-prenoms">PRÉNOMS</th>
                        @endif
                        
                        {{-- MATRICULE SUPPRIMÉ --}}
                        
                        @if(($colonnes_config['moyenne'] ?? true) && !$isAdmisOnly)
                            <th class="col-moyenne">MOYENNE</th>
                        @endif
                        
                        @if(($colonnes_config['credits'] ?? true) && !$isAdmisOnly)
                            <th class="col-credits">CRÉDITS</th>
                        @endif
                        
                        @if(($colonnes_config['decision'] ?? true) && !$isAdmisOnly)
                            <th class="col-decision">DÉCISION</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultatsTriés as $index => $resultat)
                        @php
                            $etudiant = $resultat['etudiant'] ?? null;
                            $nom = '';
                            $prenom = '';

                            if ($etudiant) {
                                if (is_array($etudiant)) {
                                    $nom = $etudiant['nom'] ?? '';
                                    $prenom = $etudiant['prenom'] ?? '';
                                } else {
                                    $nom = $etudiant->nom ?? '';
                                    $prenom = $etudiant->prenom ?? '';
                                }
                            }

                            $moyenne = $resultat['moyenne_generale'] ?? 0;
                            $credits = $resultat['credits_valides'] ?? 0;
                            $totalCredits = $resultat['total_credits'] ?? 60;

                            $decision = $resultat['decision_simulee'] ?? 
                                       $resultat['decision_actuelle'] ?? 
                                       $resultat['decision'] ?? 'non_definie';

                            $decisionText = match($decision) {
                                'admis' => 'ADMIS',
                                'rattrapage' => 'RATTRAPAGE',
                                'redoublant' => 'REDOUBLANT',
                                'exclus' => 'EXCLUS',
                                default => 'NON DÉFINIE'
                            };
                        @endphp

                        <tr>
                            @if(($colonnes_config['rang'] ?? true))
                                <td class="col-numero">{{ $resultat['rang'] ?? ($index + 1) }}</td>
                            @endif
                            
                            @if(($colonnes_config['nom_complet'] ?? true))
                                <td class="col-nom">{{ strtoupper($nom) }}</td>
                                <td class="col-prenoms">{{ ucwords(strtolower($prenom)) }}</td>
                            @endif
                            
                            {{-- MATRICULE SUPPRIMÉ --}}
                            
                            @if(($colonnes_config['moyenne'] ?? true) && !$isAdmisOnly)
                                <td class="col-moyenne">{{ number_format($moyenne, 2) }}</td>
                            @endif
                            
                            @if(($colonnes_config['credits'] ?? true) && !$isAdmisOnly)
                                <td class="col-credits">{{ $credits }}/{{ $totalCredits }}</td>
                            @endif
                            
                            @if(($colonnes_config['decision'] ?? true) && !$isAdmisOnly)
                                <td class="col-decision">{{ $decisionText }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="small-text">
                <h3>Aucun résultat à afficher</h3>
                <p>Aucun étudiant ne correspond aux critères sélectionnés.</p>
            </div>
        @endif
    </div>

    {{-- INFORMATIONS COMPACTES --}}
    @if(!empty($resultats) && count($resultats) > 0)
        <div class="info-supplementaire">
            <div class="total-etudiants">
                Arrêtée la présente liste au nombre de <strong>{{ count($resultats ?? []) }}</strong>
                @if(isset($titre_special) && str_contains($titre_special, 'ADMIS'))
                    candidat{{ count($resultats) > 1 ? 's' : '' }} admis
                @else
                    candidat{{ count($resultats) > 1 ? 's' : '' }}
                @endif
            </div>
            
            <div class="conditions">
                {{ $conditions ?? 'Sous réserve de validation de Stage Hospitalier et des modules pratiques' }}
            </div>
        </div>
    @endif

    {{-- SIGNATURE COMPACTE --}}
    <div class="signature-section">
        <div class="lieu-date">
            Mahajanga, le ____________________
        </div>
    </div>
</body>
</html>