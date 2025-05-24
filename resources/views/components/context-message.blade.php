@props([
    'requiresDeliberation' => false,
    'isConcours' => false
])

<div class="p-3 mt-3 border rounded-md {{ $requiresDeliberation ? 'border-purple-200 bg-purple-50 dark:bg-purple-900/20 dark:border-purple-700' : ($isConcours ? 'border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700' : 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700') }}">
    <p class="text-sm {{ $requiresDeliberation ? 'text-purple-800 dark:text-purple-200' : ($isConcours ? 'text-blue-800 dark:text-blue-200' : 'text-green-800 dark:text-green-200') }}">
        <em class="mr-1 icon ni {{ $requiresDeliberation ? 'ni-users' : ($isConcours ? 'ni-target' : 'ni-check') }}"></em>
        <strong>{{ $requiresDeliberation ? 'Délibération requise' : ($isConcours ? 'Concours détecté' : 'Publication directe') }} :</strong>
        @if($requiresDeliberation)
            Cette action déclenchera automatiquement une délibération pour cette session de rattrapage, analysera les performances des étudiants selon les critères de validation des crédits UE, puis publiera les décisions finales.
        @elseif($isConcours)
            Cette action effectuera le classement automatique selon les notes obtenues et publiera immédiatement les résultats. Aucune délibération n'est prévue pour ce type d'évaluation.
        @else
            Cette action analysera automatiquement les performances, déterminera les décisions selon les critères de validation des crédits UE (admis/rattrapage), et publiera immédiatement les résultats.
        @endif
    </p>
    <div class="mt-2 text-xs {{ $requiresDeliberation ? 'text-purple-700 dark:text-purple-300' : ($isConcours ? 'text-blue-700 dark:text-blue-300' : 'text-green-700 dark:text-green-300') }}">
        @if($requiresDeliberation)
            📋 <strong>Processus :</strong> Calcul automatique des moyennes UE → Analyse des crédits validés → Proposition de décisions → Application des décisions → Publication immédiate
        @elseif($isConcours)
            🏆 <strong>Processus de concours :</strong> Calcul des moyennes → Classement selon les notes → Publication directe du classement
        @else
            ⚡ <strong>Processus simplifié :</strong> Calcul des moyennes UE → Validation automatique des crédits → Décision admis/rattrapage → Publication immédiate
        @endif
    </div>
</div>
