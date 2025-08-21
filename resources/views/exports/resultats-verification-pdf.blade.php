<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relevé de Notes</title>
<style>
    /* ===== CONFIGURATION AVEC VRAIES MARGES ===== */
    @page {
        size: A4 landscape;
        margin: 8mm 10mm 8mm 10mm; /* ✅ MARGES PAGE RÉDUITES */
    }
    
    body {
        font-family: "Arial", sans-serif;
        font-size: 10px;
        line-height: 1.0;
        margin: 0;
        padding: 0 15mm 0 15mm; /* ✅ PADDING INTERNE 15mm GAUCHE/DROITE */
        color: #000;
    }
    
    /* ===== CONTAINER AVEC MARGES ===== */
    .container-avec-marges {
        width: 100%;
        max-width: 300mm; /* ✅ LARGEUR LIMITÉE */
        margin: 0 auto; /* ✅ CENTRÉ */
        padding: 0 5mm 0 5mm; /* ✅ PADDING SUPPLÉMENTAIRE */
    }
    
    /* ===== HEADER AVEC MARGES ===== */
    .header-compact {
        border: 1px solid #000;
        padding: 2mm;
        margin-bottom: 2mm;
        text-align: center;
        page-break-inside: avoid;
        width: 100%; /* ✅ LARGEUR CONTAINER */
                max-width: 240mm; /* ✅ LARGEUR LIMITÉE */
    }
    
    .header-compact h1 {
        font-size: 11px;
        margin: 0 0 1mm 0;
        font-weight: bold;
    }
    
    .header-compact p {
        font-size: 8px;
        margin: 0;
    }
    
    /* ===== ESPACE 10MM NOUVELLE PAGE ===== */
    .page-spacer {
        height: 10mm;
        width: 100%;
        border-bottom: 1px solid #ddd;
        margin-bottom: 2mm;
        page-break-inside: avoid;
    }
    
    /* ===== TABLEAU AVEC LARGEUR RÉDUITE ===== */
    .tableau-compact {
        width: 100%; /* ✅ RÉDUIT À 95% AU LIEU DE 100% */
        margin: 0 auto; /* ✅ CENTRÉ */
        border-collapse: collapse;
        font-size: 10px;
    }
    
    .tableau-compact th {
        background-color: #f0f0f0;
        border: 0.5px solid #000;
        padding: 1mm;
        text-align: center;
        font-weight: bold;
        font-size: 6px;
        text-transform: uppercase;
    }
    
    .tableau-compact td {
        border: 0.5px solid #666;
        padding: 0.8mm; /* ✅ PADDING LÉGÈREMENT AUGMENTÉ */
        vertical-align: top;
        font-size: 10px;
    }
    
    /* ===== LARGEURS COLONNES AJUSTÉES ===== */
    .c1 { width: 4%; text-align: center; }
    .c2 { width: 10%; text-align: center; font-family: monospace; font-size: 6px; }
    .c3 { width: 13%; font-size: 6px; }
    .c4 { width: 13%; font-size: 6px; }
    .c5 { width: 35%; font-size: 6px; }
    .c6 { width: 17%; font-size: 6px; }
    .c7 { width: 4%; text-align: center; font-weight: bold; }
    .c8 { width: 4%; text-align: center; font-weight: bold; background: #f5f5f5; }
    
    /* ===== STYLES LIGNES ===== */
    .etudiant-premier { 
        background-color: #f8f8f8; 
        font-weight: bold; 
    }
    
    /* ===== PAGINATION ===== */
    .nouvelle-page {
        page-break-before: always;
    }
    
    /* ===== FOOTER AVEC MARGES ===== */
    .footer-minimal {
        position: fixed;
        bottom: 3mm;
        left: 25mm; /* ✅ DÉCALÉ POUR MARGES */
        right: 25mm; /* ✅ DÉCALÉ POUR MARGES */
        text-align: center;
        font-size: 6px;
        border-top: 0.5px solid #000;
        padding-top: 1mm;
    }
    
    /* ===== OPTIMISATIONS ===== */
    * { box-sizing: border-box; }
    tr { page-break-inside: avoid; }
</style>
</head>
<body>
    <!-- ✅ CONTAINER AVEC MARGES -->
    <div class="container-avec-marges">
        
        <!-- HEADER -->
        <div class="header-compact">
            <h1>UNIVERSITÉ - FACULTÉ DE MÉDECINE - RELEVÉ DE NOTES</h1>
            <p>{{ $examen->nom ?? 'Examen' }} - {{ $sessionActive->type ?? 'Session' }} - {{ $examen->niveau->nom ?? 'N/A' }} - {{ $examen->parcours->nom ?? 'N/A' }} - {{ $sessionActive->anneeUniversitaire->nom ?? 'N/A' }}</p>
        </div>

        <!-- TABLEAU -->
        <table class="tableau-compact">
            <thead>
                <tr>
                    <th class="c1">N°</th>
                    <th class="c2">Matricule</th>
                    <th class="c3">Nom</th>
                    <th class="c4">Prénom</th>
                    <th class="c5">Unité d'Enseignement / Élément Constitutif</th>
                    <th class="c6">Enseignant</th>
                    <th class="c7">Note</th>
                    @if($afficherMoyennesUE)
                        <th class="c8">Moy</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $resultatsGroupes = collect($resultats)->groupBy('matricule');
                    $numeroOrdre = 1;
                    $etudiantsParPage = 0;
                    $maxEtudiantsParPage = 8;
                @endphp

                @foreach($resultatsGroupes as $matricule => $resultatsEtudiant)
                    @php
                        $premierResultat = $resultatsEtudiant->first();
                        $resultatsParUE = $resultatsEtudiant->groupBy('ue_nom');
                        $ueIndex = 0;
                        
                        // Pagination avec espace
                        if ($etudiantsParPage >= $maxEtudiantsParPage && $etudiantsParPage > 0) {
                            echo '</tbody></table></div>'; // ✅ FERMER CONTAINER
                            echo '<div class="nouvelle-page">';
                            echo '<div class="container-avec-marges">'; // ✅ NOUVEAU CONTAINER
                            echo '<div class="page-spacer"></div>';
                            echo '<table class="tableau-compact">';
                            echo '<thead><tr><th class="c1">N°</th><th class="c2">Matricule</th><th class="c3">Nom</th><th class="c4">Prénom</th><th class="c5">UE/EC</th><th class="c6">Enseignant</th><th class="c7">Note</th>' . ($afficherMoyennesUE ? '<th class="c8">Moy</th>' : '') . '</tr></thead><tbody>';
                            $etudiantsParPage = 0;
                        }
                        $etudiantsParPage++;
                    @endphp

                    @foreach($resultatsParUE as $ueNom => $resultatsUE)
                        @php 
                            $ueIndex++; 
                            $moyenneUE = $resultatsUE->avg('note');
                            if ($resultatsUE->contains('note', 0)) $moyenneUE = 0;
                        @endphp
                        
                        @foreach($resultatsUE as $indexEC => $resultat)
                            <tr class="{{ $loop->parent->first && $indexEC === 0 ? 'etudiant-premier' : '' }}">
                                <td class="c1">{{ ($loop->parent->first && $indexEC === 0) ? $numeroOrdre : '' }}</td>
                                <td class="c2">{{ ($loop->parent->first && $indexEC === 0) ? $matricule : '' }}</td>
                                <td class="c3">{{ ($loop->parent->first && $indexEC === 0) ? strtoupper($premierResultat['nom']) : '' }}</td>
                                <td class="c4">{{ ($loop->parent->first && $indexEC === 0) ? $premierResultat['prenom'] : '' }}</td>
                                <td class="c5">
                                    @if($indexEC === 0)
                                        <b>UE{{ $ueIndex }}.</b>{{ $ueNom ?? 'N/A' }} ({{ $resultat['ue_credits'] ?? 0 }})<br>
                                    @endif
                                    <span style="margin-left: 4mm;"><b>
                                        EC{{ $indexEC + 1 }}.</b>
                                        {{ $resultat['matiere'] }}
                                    </span>
                                </td>
                                <td class="c6">{{ $resultat['enseignant'] ?? '' }}</td>
                                <td class="c7">{{ number_format($resultat['note'], 2) }}</td>
                                @if($afficherMoyennesUE)
                                    <td class="c8">{{ $indexEC === 0 ? number_format($moyenneUE, 2) : '' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach

                    @php $numeroOrdre++; @endphp
                @endforeach
            </tbody>
        </table>
        
    </div> <!-- ✅ FERMER CONTAINER -->

    <!-- FOOTER -->
    <div class="footer-minimal">
        Document officiel - {{ now()->format('d/m/Y') }}
    </div>
</body>
</html>