@extends('layouts.guest')

@section('title', 'Accès Interdit')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="w-full max-w-md text-center">
        <div class="mb-8">
            <!-- Icône d'erreur -->
            <div class="flex items-center justify-center w-24 h-24 mx-auto mb-6 bg-red-100 rounded-full dark:bg-red-900/20">
                <em class="text-4xl text-red-600 dark:text-red-400 ni ni-shield-off"></em>
            </div>

            <!-- Code d'erreur -->
            <h1 class="mb-2 text-6xl font-bold text-gray-900 dark:text-white">403</h1>

            <!-- Message principal -->
            <h2 class="mb-4 text-2xl font-semibold text-gray-700 dark:text-gray-300">
                Accès Interdit
            </h2>

            <!-- Description -->
            <p class="mb-8 text-gray-500 dark:text-gray-400">
                Désolé, vous n'avez pas les permissions nécessaires pour accéder à cette section.
                Contactez votre administrateur si vous pensez que c'est une erreur.
            </p>
        </div>

        <!-- Informations sur les rôles -->
        <div class="p-4 mb-6 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <em class="text-lg text-blue-400 ni ni-info-circle"></em>
                </div>
                <div class="ml-3 text-left">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        Votre profil actuel
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <p><strong>Utilisateur :</strong> {{ auth()->user()->name }}</p>
                        <p><strong>Rôles :</strong>
                            @forelse(auth()->user()->roles as $role)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 mr-1">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @empty
                                <span class="text-gray-500">Aucun rôle assigné</span>
                            @endforelse
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-3">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <em class="mr-2 ni ni-home"></em>
                Retour au tableau de bord
            </a>

            <button onclick="history.back()"
                    class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                <em class="mr-2 ni ni-arrow-left"></em>
                Retour à la page précédente
            </button>
        </div>

        <!-- Contact admin -->
        <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Besoin d'aide ? Contactez votre administrateur système.
            </p>
        </div>
    </div>
</div>
@endsection
