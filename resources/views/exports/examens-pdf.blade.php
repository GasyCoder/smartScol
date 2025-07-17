<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Planning des Examens - {{ $niveau['nom'] ?? 'Niveau' }} {{ $parcours['nom'] ?? 'Parcours' }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #2563EB;
            padding-bottom: 15px;
        }
        
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #2563EB;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 12px;
            color: #666;
        }
        
        .info-section {
            background-color: #f8fafc;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .info-row {
            margin-bottom: 5px;
        }
        
        .filters {
            background-color: #dbeafe;
            padding: 8px;
            border-left: 4px solid #2563EB;
            margin-bottom: 15px;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            background-color: #f0f9ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 14px;
            font-weight: bold;
            color: #2563EB;
        }
        
        .stat-label {
            font-size: 8px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8px;
        }
        
        th {
            background-color: #000;
            color: white;
            padding: 8px 4px;
            text-align: center;
            font-weight: normal;
            border: 0px;
        }
        
        td {
            padding: 6px 4px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        

        .text-left {
            text-align: left !important;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        

        
        .enseignant-group {
            margin-bottom: 15px;
            border-left: 3px solid #000;
            padding-left: 10px;
        }
        
        .enseignant-name {
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    {{-- En-tête --}}
    <div class="header">
        <div class="title">PLANNING DES EXAMENS</div>
        <div class="subtitle">{{ strtoupper($niveau['nom'] ?? 'NIVEAU') }} - {{ strtoupper($parcours['nom'] ?? 'PARCOURS') }}</div>
    </div>

    {{-- Informations générales --}}
    <div class="info-section">
        <div class="info-row"><strong>Niveau :</strong> {{ $niveau['nom'] ?? 'N/A' }} ({{ $niveau['abr'] ?? 'N/A' }})</div>
        <div class="info-row"><strong>Parcours :</strong> {{ $parcours['nom'] ?? 'N/A' }} ({{ $parcours['abr'] ?? 'N/A' }})</div>
        <div class="info-row"><strong>Généré le :</strong> {{ $generated_at ?? date('d/m/Y H:i') }}</div>
        <div class="info-row"><strong>Généré par :</strong> {{ $generated_by ?? 'Systeme' }}</div>
    </div>

    {{-- Filtres actifs --}}
    @if(!empty($filters))
    <div class="filters">
        <strong>Filtres appliqués :</strong>
        @foreach($filters as $key => $value)
            {{ $key }}: {{ $value }}@if(!$loop->last), @endif
        @endforeach
    </div>
    @endif


    {{-- Tableau principal --}}
    <table>
        <thead>
            <tr>
                <th style="width: 8%">Date</th>
                <th style="width: 6%">Heure</th>
                <th style="width: 5%">Duree</th>
                <th style="width: 8%">UE CODE</th>
                <th style="width: 20%">UE Nom</th>
                <th style="width: 5%">Crédit</th>
                <th style="width: 8%">EC Code</th>
                <th style="width: 20%">EC Nom</th>
                <th style="width: 15%">Enseignant</th>
                <th style="width: 10%">Salle</th>
                <th style="width: 6%">Code</th>
            </tr>
        </thead>
        <tbody>
            @foreach($examens as $examen)
                @foreach($examen->ecs as $ec)
                    @php
                        // Récupération sécurisée des données
                        $salle_nom = '';
                        if($ec->pivot && $ec->pivot->salle_id) {
                            try {
                                $salle = \App\Models\Salle::find($ec->pivot->salle_id);
                                $salle_nom = $salle ? htmlspecialchars($salle->nom, ENT_QUOTES, 'UTF-8') : '';
                            } catch (\Exception $e) {
                                $salle_nom = 'Erreur salle';
                            }
                        }
                        
                        // Calcul sécurisé des statistiques
                        $copiesCount = 0;
                        $manchettesCount = 0;
                        $totalCodes = 0;
                        
                        try {
                            $copiesCount = $examen->copies()->whereHas('codeAnonymat', function($q) use ($ec, $examen) {
                                $q->where('ec_id', $ec->id)->where('examen_id', $examen->id);
                            })->count();
                            
                            $manchettesCount = $examen->manchettes()->whereHas('codeAnonymat', function($q) use ($ec, $examen) {
                                $q->where('ec_id', $ec->id)->where('examen_id', $examen->id);
                            })->count();
                            
                            $totalCodes = $examen->codesAnonymat()->where('ec_id', $ec->id)->count();
                        } catch (\Exception $e) {
                            // En cas d'erreur, garder les valeurs par défaut à 0
                        }
                        
                        // Détermination du statut
                        if ($totalCodes > 0 && $copiesCount >= $totalCodes && $manchettesCount >= $totalCodes) {
                            $statusClass = 'status-complete';
                            $statusText = 'Complet';
                        } elseif ($copiesCount > 0 || $manchettesCount > 0) {
                            $statusClass = 'status-progress';
                            $statusText = 'En cours';
                        } else {
                            $statusClass = 'status-none';
                            $statusText = $totalCodes > 0 ? 'Non commence' : 'Aucun code';
                        }
                        
                        // Nettoyage des données d'affichage
                        $date = '';
                        $heure = '';
                        if($ec->pivot) {
                            try {
                                $date = $ec->pivot->date_specifique ? \Carbon\Carbon::parse($ec->pivot->date_specifique)->format('d/m/Y') : '';
                                $heure = $ec->pivot->heure_specifique ? \Carbon\Carbon::parse($ec->pivot->heure_specifique)->format('H:i') : '';
                            } catch (\Exception $e) {
                                $date = 'Erreur date';
                                $heure = 'Erreur heure';
                            }
                        }
                    @endphp
                    
                    <tr>
                        <td>{{ $date }}</td>
                        <td>{{ $heure }}</td>
                        <td>{{ $examen->duree ?? 0 }}min</td>
                        <td class="text-left">{{ $ec->ue->abr ?? '' }}</td>
                        <td class="text-left">{{ $ec->ue->nom ?? '' }}</td>
                        <td class="text-center">{{ $ec->ue->credits ?? 0 }}</td>
                        <td class="text-left">{{ $ec->abr ?? '' }}</td>
                        <td class="text-left">{{ $ec->nom ?? '' }}</td>
                        <td class="text-left">{{ $ec->enseignant ?: 'Non assigne' }}</td>
                        <td>{{ $salle_nom }}</td>
                        <td><strong>{{ $ec->pivot->code_base ?? '' }}</strong></td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>



{{-- VERSION 3: FORMAT GRILLE (Plus compact) --}}
@if(isset($examens) && $examens->count() > 0)
<div class="page-break">
    <h2 style="color: #059669; border-bottom: 2px solid #059669; padding-bottom: 5px; margin-bottom: 15px;">Resume par Enseignant</h2>
    
    @php
        $parEnseignant = [];
        try {
            foreach($examens as $examen) {
                foreach($examen->ecs as $ec) {
                    $enseignant = trim($ec->enseignant ?? '');
                    if(!empty($enseignant)) {
                        if(!isset($parEnseignant[$enseignant])) {
                            $parEnseignant[$enseignant] = [];
                        }
                        $parEnseignant[$enseignant][] = [
                            'ec_nom' => $ec->nom ?? '',
                            'date' => $ec->pivot->date_specifique ?? null,
                            'heure' => $ec->pivot->heure_specifique ?? null,
                            'duree' => (int)($examen->duree ?? 0)
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $parEnseignant = [];
        }
    @endphp
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 9px;">
        @foreach($parEnseignant as $enseignant => $items)
            @if(!empty($enseignant) && count($items) > 0)
            <div style="border: 1px solid #d1d5db; padding: 8px; background-color: #fefefe;">
                {{-- En-tête --}}
                <div style="font-weight: bold; color: #059669; margin-bottom: 5px; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px;">
                    {{ $enseignant }}
                </div>
                
                {{-- Statistiques --}}
                <div style="font-size: 8px; color: #666; margin-bottom: 5px;">
                    {{ count($items) }} matières • {{ array_sum(array_column($items, 'duree')) }}min • {{ count(array_unique(array_filter(array_column($items, 'date')))) }} jours
                </div>
                
                {{-- Planning --}}
                @foreach($items as $item)
                    <div style="font-size: 7px; margin-bottom: 2px; line-height: 1.2;">
                        <strong>{{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format('d/m') : '--' }}</strong>
                        {{ $item['heure'] ? \Carbon\Carbon::parse($item['heure'])->format('H:i') : '--' }}
                        - {{ $item['ec_nom'] }} ({{ $item['duree'] }}min)
                    </div>
                @endforeach
            </div>
            @endif
        @endforeach
    </div>
</div>
@endif

    {{-- Pied de page --}}
    <div class="footer">
        <div>Genere le {{ $generated_at ?? date('d/m/Y H:i') }} • {{ config('app.name', 'Systeme de Gestion') }}</div>
    </div>
</body>
</html>