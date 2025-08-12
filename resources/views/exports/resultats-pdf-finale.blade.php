<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $infos['titre'] ?? 'Résultats Session 1' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 18px;
        }
        
        .header p {
            margin: 5px 0;
            color: #6b7280;
        }
        
        .stats {
            background: #f3f4f6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .stats-row {
            display: inline-block;
            margin: 0 15px;
        }
        
        .stats-label {
            font-weight: bold;
            color: #374151;
        }
        
        .stats-value {
            color: #1f2937;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }
        
        th {
            background-color: #3b82f6;
            color: white;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #2563eb;
        }
        
        td {
            padding: 6px 4px;
            border: 1px solid #d1d5db;
            text-align: center;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        tr:nth-child(odd) {
            background-color: white;
        }
        
        .decision-admis {
            background-color: #dcfce7;
            color: #166534;
            font-weight: bold;
        }
        
        .decision-rattrapage {
            background-color: #fed7aa;
            color: #9a3412;
            font-weight: bold;
        }
        
        .decision-redoublant {
            background-color: #fecaca;
            color: #991b1b;
            font-weight: bold;
        }
        
        .decision-exclus {
            background-color: #fee2e2;
            color: #7f1d1d;
            font-weight: bold;
        }
        
        .moyenne-valide {
            color: #059669;
            font-weight: bold;
        }
        
        .moyenne-non-valide {
            color: #dc2626;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
        }
        
        .note-eliminatoire {
            color: #dc2626;
            font-weight: bold;
        }
        
        .jury-validated {
            color: #2563eb;
            font-size: 10px;
        }
    </style>
</head>
<body>
    {{-- En-tête --}}
    <div class="header">
        <h1>{{ $infos['titre'] ?? 'Résultats Session 1' }}</h1>
        <p><strong>Année Universitaire:</strong> {{ $infos['annee'] ?? date('Y') }}</p>
        <p><strong>Date d'export:</strong> {{ $infos['date_export'] ?? now()->format('d/m/Y à H:i') }}</p>
        @if(isset($infos['filtre']))
            <p><strong>Filtre appliqué:</strong> {{ $infos['filtre'] }}</p>
        @endif
    </div>

    {{-- Statistiques --}}
    @if(isset($infos['statistiques']) && !empty($infos['statistiques']))
        <div class="stats">
            <div class="stats-row">
                <span class="stats-label">Total:</span>
                <span class="stats-value">{{ $infos['total_etudiants'] ?? count($donnees) }}</span>
            </div>
            @if(isset($infos['statistiques']['admis']))
                <div class="stats-row">
                    <span class="stats-label">Admis:</span>
                    <span class="stats-value">{{ $infos['statistiques']['admis'] ?? 0 }}</span>
                </div>
            @endif
            @if(isset($infos['statistiques']['rattrapage']))
                <div class="stats-row">
                    <span class="stats-label">Rattrapage:</span>
                    <span class="stats-value">{{ $infos['statistiques']['rattrapage'] ?? 0 }}</span>
                </div>
            @endif
            @if(isset($infos['statistiques']['redoublant']) && $infos['statistiques']['redoublant'] > 0)
                <div class="stats-row">
                    <span class="stats-label">Redoublants:</span>
                    <span class="stats-value">{{ $infos['statistiques']['redoublant'] }}</span>
                </div>
            @endif
            @if(isset($infos['statistiques']['exclus']) && $infos['statistiques']['exclus'] > 0)
                <div class="stats-row">
                    <span class="stats-label">Exclus:</span>
                    <span class="stats-value">{{ $infos['statistiques']['exclus'] }}</span>
                </div>
            @endif
        </div>
    @endif

    {{-- Tableau des résultats --}}
    <table>
        <thead>
            <tr>
                <th style="width: 6%;">Rang</th>
                <th style="width: 12%;">Matricule</th>
                <th style="width: 20%;">Nom</th>
                <th style="width: 18%;">Prénom</th>
                <th style="width: 10%;">Moyenne</th>
                <th style="width: 12%;">Crédits</th>
                <th style="width: 15%;">Décision</th>
                <th style="width: 7%;">Note 0</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donnees as $row)
                <tr>
                    {{-- Rang --}}
                    <td>{{ $row['rang'] }}</td>
                    
                    {{-- Matricule --}}
                    <td>{{ $row['matricule'] }}</td>
                    
                    {{-- Nom --}}
                    <td style="text-align: left;">{{ $row['nom'] }}</td>
                    
                    {{-- Prénom --}}
                    <td style="text-align: left;">{{ $row['prenom'] }}</td>
                    
                    {{-- Moyenne --}}
                    <td class="{{ floatval($row['moyenne_generale']) >= 10 ? 'moyenne-valide' : 'moyenne-non-valide' }}">
                        {{ $row['moyenne_generale'] }}
                    </td>
                    
                    {{-- Crédits --}}
                    <td>{{ $row['credits_valides'] }}/{{ $row['total_credits'] }}</td>
                    
                    {{-- Décision --}}
                    <td class="decision-{{ strtolower(str_replace(' ', '-', $row['decision'])) }}">
                        {{ $row['decision'] }}
                        @if($row['jury_validated'])
                            <br><span class="jury-validated">Jury</span>
                        @endif
                    </td>
                    
                    {{-- Note éliminatoire --}}
                    <td class="{{ $row['has_note_eliminatoire'] ? 'note-eliminatoire' : '' }}">
                        {{ $row['has_note_eliminatoire'] ? 'OUI' : 'NON' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Pied de page --}}
    <div class="footer">
        <p>Document généré automatiquement le {{ $infos['date_export'] ?? now()->format('d/m/Y à H:i') }}</p>
        <p>Total: {{ count($donnees) }} étudiant(s) • Page 1/1</p>
        @if(isset($infos['filtre']))
            <p><em>Filtre appliqué: {{ $infos['filtre'] }}</em></p>
        @endif
    </div>

    {{-- Légende --}}
    <div style="margin-top: 15px; font-size: 10px; color: #6b7280;">
        <strong>Légende:</strong>
        <span style="color: #059669;">■ Moyenne ≥ 10</span> •
        <span style="color: #dc2626;">■ Moyenne < 10</span> •
        <span style="color: #dc2626;">Note 0 = Note éliminatoire</span> •
        <span style="color: #2563eb;">Jury = Validé par délibération</span>
    </div>
</body>
</html>