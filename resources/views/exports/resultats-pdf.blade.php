<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Étudiants {{ $titre_special ?? 'Résultats' }}</title>
    <style>
        @page {
            margin: 15mm 20mm 20mm 20mm;
            size: A4 portrait;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.4;
            background: #fff;
        }

        /* ===== HEADER OFFICIEL ===== */
        .header-officiel {
            text-align: center;
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .republique {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .devise {
            font-size: 11px;
            font-style: italic;
            margin-bottom: 5px;
        }

        .separateur {
            border-bottom: 1px solid #000;
            width: 200px;
            margin: 5px auto;
        }

        .ministere {
            font-size: 12px;
            font-weight: bold;
            margin: 8px 0;
            text-transform: uppercase;
        }

        .universite {
            font-size: 13px;
            font-weight: bold;
            margin: 8px 0;
            text-transform: uppercase;
        }

        .faculte {
            font-size: 12px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }

        .adresse {
            font-size: 10px;
            margin: 5px 0;
        }

        .contact {
            font-size: 10px;
            margin: 10px 0;
        }

        /* ===== TITRE PRINCIPAL ===== */
        .titre-principal {
            text-align: center;
            margin: 40px 0 30px 0;
            page-break-inside: avoid;
        }

        .titre-principal h1 {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }

        .annee-universitaire {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .sous-titre {
            font-size: 12px;
            margin: 5px 0;
            font-style: italic;
        }

        /* ===== TABLEAU RÉSULTATS ===== */
        .table-container {
            margin: 30px auto;
            max-width: 700px;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .results-table th {
            background-color: #f0f0f0;
            color: #000;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            border: 2px solid #000;
            font-size: 13px;
            text-transform: uppercase;
        }

        .results-table td {
            padding: 10px 8px;
            border: 1px solid #000;
            text-align: left;
            font-size: 12px;
            vertical-align: middle;
        }

        .results-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .results-table tbody tr:hover {
            background-color: #f0f0f0;
        }

        /* ===== COLONNES ===== */
        .col-numero {
            width: 8%;
            text-align: center !important;
            font-weight: bold;
        }

        .col-nom {
            width: 46%;
            text-align: left !important;
            font-weight: bold;
            text-transform: uppercase;
        }

        .col-prenoms {
            width: 46%;
            text-align: left !important;
        }

        /* ===== INFORMATIONS SUPPLÉMENTAIRES ===== */
        .info-supplementaire {
            margin: 30px 0;
            font-size: 12px;
            line-height: 1.6;
        }

        .total-etudiants {
            font-weight: bold;
            margin: 15px 0;
        }

        .conditions {
            font-style: italic;
            margin: 10px 0;
            color: #333;
        }

        /* ===== SIGNATURE ===== */
        .signature-section {
            margin-top: 50px;
            text-align: right;
            page-break-inside: avoid;
        }

        .lieu-date {
            margin-bottom: 60px;
            font-size: 12px;
        }

        .signature-titre {
            font-size: 11px;
            margin-bottom: 5px;
            font-style: italic;
        }

        .signature-nom {
            font-size: 12px;
            font-weight: bold;
            margin-top: 40px;
        }

        .signature-fonction {
            font-size: 10px;
            font-style: italic;
            margin-top: 5px;
        }

        /* ===== RESPONSIVE POUR IMPRESSION ===== */
        @media print {
            body {
                font-size: 11px;
            }
            
            .results-table th,
            .results-table td {
                font-size: 11px;
            }
            
            .page-break {
                page-break-before: always;
            }
        }

        /* ===== STYLES SPÉCIAUX POUR DÉCISIONS ===== */
        .decision-admis {
            color: #006600;
            font-weight: bold;
        }

        .decision-rattrapage {
            color: #ff6600;
            font-weight: bold;
        }

        .decision-redoublant {
            color: #cc0000;
            font-weight: bold;
        }

        .moyenne-generale {
            text-align: center !important;
            font-weight: bold;
            font-size: 13px;
        }

        .credits {
            text-align: center !important;
            font-size: 11px;
        }
    </style>
</head>
<body>
    {{-- HEADER OFFICIEL --}}
    <div class="header-officiel">
        <div class="republique">RÉPUBLIQUE DE MADAGASCAR</div>
        <div class="devise">Fitiavana - Tanindrazana - Fandrosoana</div>
        <div class="separateur"></div>
        
        <div class="ministere">
            MINISTÈRE DE L'ENSEIGNEMENT SUPÉRIEUR<br>
            ET DE LA RECHERCHE SCIENTIFIQUE
        </div>
        <div class="separateur"></div>
        
        <div class="universite">UNIVERSITÉ DE MAHAJANGA</div>
        <div class="faculte">{{ $niveau->nom ?? 'FACULTÉ DE MÉDECINE' }}</div>
        
        <div class="adresse">
            BP : 652 – Mahajanga 401 – Madagascar<br>
            Tél : +261 38 41 930 47
        </div>
        
        <div class="contact">
            Mail : facmed.mga@gmail.com - Web www.univ-mahajanga.edu.mg
        </div>
    </div>

    {{-- TITRE PRINCIPAL --}}
    <div class="titre-principal">
        <h1>
            @if(isset($titre_special))
                {{ $titre_special }}
            @else
                RÉSULTATS {{ strtoupper($session->type ?? 'SESSION NORMALE') }}
            @endif
            @if($parcours)
                {{ strtoupper($parcours->nom) }}
            @endif
        </h1>
        
        <div class="annee-universitaire">
            ANNÉE {{ $annee_universitaire->libelle ?? date('Y') . '-' . (date('Y') + 1) }}
        </div>
        
        <div class="sous-titre">(Par ordre de Mérite)</div>
    </div>

    {{-- TABLEAU DES RÉSULTATS --}}
    <div class="table-container">
        <table class="results-table">
            <thead>
                <tr>
                    <th class="col-numero">N°</th>
                    <th class="col-nom">NOM</th>
                    <th class="col-prenoms">PRÉNOMS</th>
                    @if(!isset($titre_special) || $titre_special !== 'LISTE DES ADMIS')
                        <th style="width: 12%; text-align: center;">MOYENNE</th>
                        <th style="width: 12%; text-align: center;">CRÉDITS</th>
                        <th style="width: 15%; text-align: center;">DÉCISION</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($resultats as $index => $resultat)
                <tr>
                    <td class="col-numero">{{ $index + 1 }}</td>
                    <td class="col-nom">{{ strtoupper($resultat['etudiant']->nom) }}</td>
                    <td class="col-prenoms">{{ $resultat['etudiant']->prenom }}</td>
                    
                    @if(!isset($titre_special) || $titre_special !== 'LISTE DES ADMIS')
                        <td class="moyenne-generale">
                            {{ number_format($resultat['moyenne_generale'], 2) }}
                        </td>
                        <td class="credits">
                            {{ $resultat['credits_valides'] }}/{{ $resultat['total_credits'] ?? 60 }}
                        </td>
                        <td class="col-decision">
                            @php
                                $decision = $resultat['decision'];
                                $decisionClass = '';
                                $decisionText = '';
                                
                                switch($decision) {
                                    case 'admis':
                                        $decisionClass = 'decision-admis';
                                        $decisionText = 'ADMIS';
                                        break;
                                    case 'rattrapage':
                                        $decisionClass = 'decision-rattrapage';
                                        $decisionText = 'RATTRAPAGE';
                                        break;
                                    case 'redoublant':
                                        $decisionClass = 'decision-redoublant';
                                        $decisionText = 'REDOUBLANT';
                                        break;
                                    case 'exclus':
                                        $decisionClass = 'decision-redoublant';
                                        $decisionText = 'EXCLUS';
                                        break;
                                    default:
                                        $decisionText = 'NON DÉFINIE';
                                }
                            @endphp
                            <span class="{{ $decisionClass }}">{{ $decisionText }}</span>
                        </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- INFORMATIONS SUPPLÉMENTAIRES --}}
    <div class="info-supplementaire">
        <div class="total-etudiants">
            Arrêtée la présente liste au nombre de 
            @if(isset($titre_special) && $titre_special === 'LISTE DES ADMIS')
                {{ count($resultats) }} ({{ $this->nombreEnLettres(count($resultats)) }}) étudiants
            @else
                {{ count($resultats) }} ({{ $this->nombreEnLettres(count($resultats)) }}) étudiants
            @endif
        </div>
        
        @if($conditions ?? false)
            <div class="conditions">
                {{ $conditions }}
            </div>
        @else
            <div class="conditions">
                Sous réserve de validation de Stage Hospitalier
            </div>
        @endif
    </div>

    {{-- SIGNATURE --}}
    <div class="signature-section">
        <div class="lieu-date">
            Mahajanga, le {{ now()->format('d') }} {{ $this->moisEnFrancais(now()->format('m')) }} {{ now()->format('Y') }}
        </div>
        
        <div class="signature-titre">
            Pour le Doyen de la Faculté
        </div>
        
        <div class="signature-nom">
            Dr {{ $doyen_nom ?? 'RAKOTOMALALA Jules Robert' }}
        </div>
        
        <div class="signature-fonction">
            Enseignant Chercheur
        </div>
    </div>

    {{-- FOOTER AVEC STATISTIQUES (si ce n'est pas la liste des admis uniquement) --}}
    @if(!isset($titre_special) || $titre_special !== 'LISTE DES ADMIS')
        <div style="position: fixed; bottom: 10mm; left: 20mm; right: 20mm; font-size: 10px; text-align: center; border-top: 1px solid #ccc; padding-top: 5px;">
            <strong>Statistiques :</strong>
            Admis: {{ $statistiques['admis'] ?? 0 }} |
            Rattrapage: {{ $statistiques['rattrapage'] ?? 0 }} |
            Redoublant: {{ $statistiques['redoublant'] ?? 0 }}
            @if(($statistiques['exclus'] ?? 0) > 0)
                | Exclus: {{ $statistiques['exclus'] }}
            @endif
            | Taux de réussite: {{ $statistiques['taux_reussite'] ?? 0 }}%
        </div>
    @endif
</body>
</html>