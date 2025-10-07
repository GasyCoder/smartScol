@php
    $loadingMessages = [
        'exporterPDF' => ['message' => 'Génération PDF', 'description' => 'Création du document...'],
        'exporterExcel' => ['message' => 'Génération Excel', 'description' => 'Création du fichier...'],
        'simulerDeliberation' => ['message' => 'Simulation', 'description' => 'Calcul des décisions...'],
        'appliquerDeliberation' => ['message' => 'Application', 'description' => 'Sauvegarde en cours...'],
        'changerFiltre' => ['message' => 'Filtrage', 'description' => 'Mise à jour...'],
        'gotoPage' => ['message' => 'Pagination', 'description' => 'Chargement de la page...'],
        'previousPage' => ['message' => 'Page précédente', 'description' => 'Chargement...'],
        'nextPage' => ['message' => 'Page suivante', 'description' => 'Chargement...'],
    ];
@endphp

@foreach($loadingMessages as $target => $config)
    <x-loading-spinner 
        target="{{ $target }}" 
        message="{{ $config['message'] }}"
        description="{{ $config['description'] }}" />
@endforeach