{{-- resources/views/exports/examens-enseignant-excel.blade.php --}}
<table>
    {{-- En-tête principal --}}
    <tr>
        <td colspan="14" style="text-align: center; font-weight: bold; font-size: 16px;">
            PLANNING PERSONNEL - {{ strtoupper($enseignant) }}
        </td>
    </tr>
    
    {{-- Informations générales --}}
    <tr><td colspan="14"></td></tr>
    <tr>
        <td colspan="14">
            <strong>Enseignant :</strong> {{ $enseignant }}
        </td>
    </tr>
    <tr>
        <td colspan="14">
            <strong>Niveau/Parcours :</strong> {{ $niveau['nom'] }} - {{ $parcours['nom'] }}
        </td>
    </tr>
    <tr>
        <td colspan="14">
            <strong>Généré le :</strong> {{ $generated_at }}
        </td>
    </tr>
    <tr>
        <td colspan="14">
            <strong>Résumé :</strong> {{ $total_examens }} examen(s) • {{ $total_heures }} minutes • Durée moyenne: {{ $moyenne_duree }} min
        </td>
    </tr>

    {{-- Dates d'examens --}}
    @if($dates_examens->isNotEmpty())
    <tr>
        <td colspan="14">
            <strong>Dates d'examens :</strong> {{ $dates_examens->implode(', ') }}
        </td>
    </tr>
    @endif

    {{-- Ligne vide --}}
    <tr><td colspan="14"></td></tr>

    {{-- En-têtes des colonnes --}}
    <tr>
        <td><strong>Date</strong></td>
        <td><strong>Heure début</strong></td>
        <td><strong>Heure fin</strong></td>
        <td><strong>Durée (min)</strong></td>
        <td><strong>UE Code.</strong></td>
        <td><strong>UE Nom</strong></td>
        <td><strong>Crédits</strong></td>
        <td><strong>EC Code.</strong></td>
        <td><strong>EC Nom</strong></td>
        <td><strong>Salle</strong></td>
        <td><strong>Code</strong></td>
    </tr>

    {{-- Données --}}
    @foreach($data as $item)
    <tr>
        <td>{{ $item['date'] }}</td>
        <td>{{ $item['heure'] }}</td>
        <td>{{ $item['heure_fin'] }}</td>
        <td>{{ $item['duree'] }}</td>
        <td>{{ $item['ue_abr'] }}</td>
        <td>{{ $item['ue_nom'] }}</td>
        <td style="text-align: center;">{{ $item['ue_credits'] ?? 0 }}</td>
        <td>{{ $item['ec_abr'] }}</td>
        <td>{{ $item['ec_nom'] }}</td>
        <td>{{ $item['salle'] }}</td>
        <td>{{ $item['code_base'] }}</td>
    </tr>
    @endforeach



    {{-- Ligne de résumé final --}}
    <tr>
        <td colspan="4"><strong>TOTAL HEURES :</strong></td>
        <td colspan="3"><strong>{{ $total_heures }} minutes</strong></td>
        <td colspan="3"><strong>{{ round($total_heures / 60, 1) }} heures</strong></td>
    </tr>
</table>