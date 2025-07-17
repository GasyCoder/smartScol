{{-- resources/views/exports/examens-excel.blade.php --}}
<table>
    {{-- En-tête principal --}}
    <tr>
        <td colspan="19" style="text-align: center; font-weight: bold; font-size: 16px;">
            PLANNING DES EXAMENS - {{ strtoupper($niveau['nom']) }} {{ strtoupper($parcours['nom']) }}
        </td>
    </tr>
    
    {{-- Informations générales --}}
    <tr><td colspan="19"></td></tr>
    <tr>
        <td colspan="19">
            <strong>Niveau :</strong> {{ $niveau['nom'] }} ({{ $niveau['abr'] }})
        </td>
    </tr>
    <tr>
        <td colspan="19">
            <strong>Parcours :</strong> {{ $parcours['nom'] }} ({{ $parcours['abr'] }})
        </td>
    </tr>
    <tr>
        <td colspan="19">
            <strong>Généré le :</strong> {{ $generated_at }}
        </td>
    </tr>

    {{-- Filtres actifs --}}
    @if(!empty($filters))
    <tr>
        <td colspan="19">
            <strong>Filtres appliqués :</strong>
            @foreach($filters as $key => $value)
                {{ $key }}: {{ $value }}@if(!$loop->last), @endif
            @endforeach
        </td>
    </tr>
    @endif

    {{-- Statistiques --}}
    <tr>
        <td colspan="19">
            <strong>Statistiques :</strong> 
            {{ $total_examens }} examen(s) • 
            {{ $total_ecs }} matière(s) • 
            {{ $enseignants_uniques }} enseignant(s)
        </td>
    </tr>

    {{-- Ligne vide --}}
    <tr><td colspan="19"></td></tr>

    {{-- En-têtes des colonnes --}}
    <tr>
        <td><strong>UE Code.</strong></td>
        <td><strong>UE Nom</strong></td>
        <td><strong>Crédits</strong></td>
        <td><strong>EC Code.</strong></td>
        <td><strong>EC Nom</strong></td>
        <td><strong>Enseignant</strong></td>
        <td><strong>Date</strong></td>
        <td><strong>Heure</strong></td>
        <td><strong>Durée (min)</strong></td>
        <td><strong>Salle</strong></td>
        <td><strong>Code</strong></td>
    </tr>

    {{-- Données --}}
    @foreach($data as $item)
    <tr>
        <td>{{ $item['ue_abr'] }}</td>
        <td>{{ $item['ue_nom'] }}</td>
        <td>{{ $item['ue_credits'] }}</td>
        <td>{{ $item['ec_abr'] }}</td>
        <td>{{ $item['ec_nom'] }}</td>
        <td>{{ $item['enseignant'] }}</td>
        <td>{{ $item['date'] }}</td>
        <td>{{ $item['heure'] }}</td>
        <td>{{ $item['duree'] }}</td>
        <td>{{ $item['salle'] }}</td>
        <td>{{ $item['code_base'] }}</td>
    </tr>
    @endforeach

    {{-- Ligne de résumé --}}
    <tr><td colspan="19"></td></tr>
    <tr>
        <td colspan="7"><strong>TOTAL</strong></td>
        <td colspan="3"><strong>{{ $data->sum('duree') }} minutes</strong></td>
    </tr>
</table>