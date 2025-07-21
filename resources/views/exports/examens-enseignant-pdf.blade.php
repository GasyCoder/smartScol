{{-- resources/views/exports/examens-enseignant-pdf.blade.php - VERSION SIMPLE ET COMPACTE --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planning - {{ $enseignant }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 15px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #059669;
            padding-bottom: 10px;
        }
        
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 5px;
        }
        
        .enseignant-name {
            font-size: 12px;
            color: #059669;
            font-weight: bold;
        }
        
        .stats-row {
            background-color: #f0f9ff;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
            border-left: 3px solid #059669;
        }
        
        .stats-inline {
            font-size: 9px;
        }
        
        .stats-number {
            font-weight: bold;
            color: #059669;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 10px;
        }
        
        th {
            background-color: #059669;
            color: white;
            padding: 4px 2px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #047857;
        }
        
        td {
            padding: 3px 2px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-left {
            text-align: left !important;
        }
        
        .footer {
            text-align: center;
            font-size: 8px;
            color: #666;
            margin-top: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
        }
        
        .no-exams {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        .total-row {
            background-color: #f0f9ff;
            font-weight: bold;
            color: #059669;
        }
    </style>
</head>
<body>
    {{-- En-tête simple --}}
    <div class="header">
        <div class="title">PLANNING PERSONNEL</div>
        <div>{{ $niveau['nom'] }} - {{ $parcours['nom'] }}</div>
        <div class="enseignant-name">{{ strtoupper($enseignant) }}</div>
    </div>


    {{-- Tableau simple et compact --}}
    @if($data->isNotEmpty())
    <table>
        <thead>
            <tr>
                <th style="width: 8%">Date</th>
                <th style="width: 10%">Horaire</th>
                <th style="width: 5%">Durée</th>
                <th style="width: 7%">UE</th>
                <th style="width: 20%">UE Nom</th>
                <th style="width: 5%">Crd</th>
                <th style="width: 7%">EC</th>
                <th style="width: 20%">EC Nom</th>
                <th style="width: 8%">Salle</th>
                <th style="width: 6%">Code</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item['date'] ?: '--' }}</td>
                <td>{{ $item['heure'] ? $item['heure'] . '-' . $item['heure_fin'] : '--' }}</td>
                <td>{{ $item['duree'] }}min</td>
                <td class="text-left">{{ $item['ue_abr'] ?: '--' }}</td>
                <td class="text-left">{{ $item['ue_nom'] ?: '--' }}</td>
                <td style="font-weight: bold;">{{ $item['ue_credits'] ?? 0 }}</td>
                <td class="text-left">{{ $item['ec_abr'] ?: '--' }}</td>
                <td class="text-left">{{ $item['ec_nom'] ?: '--' }}</td>
                <td>{{ $item['salle'] ?: '--' }}</td>
                <td><strong>{{ $item['code_base'] ?: '-' }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        
        {{-- Total simple --}}
        <tfoot>
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">TOTAUX :</td>
                <td>{{ $total_credits ?? 0 }}</td>
                <td colspan="2">{{ $total_examens }} examens</td>
                <td colspan="2">{{ $total_heures }}min ({{ round($total_heures / 60, 1) }}h)</td>
            </tr>
        </tfoot>
    </table>
    
    @else
    <div class="no-exams">
        Aucun examen programmé pour cet enseignant.
    </div>
    @endif

    {{-- Pied de page simple --}}
    <div class="footer">
        {{ $enseignant }} • {{ $total_credits ?? 0 }} crédits ECTS • {{ config('app.name') }}
    </div>
</body>
</html>