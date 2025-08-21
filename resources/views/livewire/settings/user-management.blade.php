<div class="space-y-4">
    <!-- En-tête slim -->
    <div class="flex flex-col gap-3 p-4 bg-white border border-gray-200 rounded-lg shadow sm:flex-row sm:items-center sm:justify-between dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center space-x-3">
            <div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-lg">
                <em class="text-sm text-white ni ni-users"></em>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Gestion des Utilisateurs</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Gérez les utilisateurs et leurs rôles</p>
            </div>
        </div>
        <button wire:click="createUser"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <em class="mr-2 text-xs ni ni-plus"></em>
            Nouvel Utilisateur
        </button>
    </div>

    <!-- Barre de recherche slim -->
    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <em class="text-sm text-gray-400 ni ni-search"></em>
            </div>
            <input wire:model.live.debounce.300ms="searchUsers"
                   type="text"
                   placeholder="Rechercher par nom, email ou nom d'utilisateur..."
                   class="block w-full py-2 pl-10 pr-3 text-sm border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <!-- Tableau slim -->
    <div class="bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                            <div class="flex items-center space-x-1">
                                <em class="text-xs ni ni-user-circle"></em>
                                <span>Utilisateur</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                            <div class="flex items-center space-x-1">
                                <em class="text-xs ni ni-emails"></em>
                                <span>Contact</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                            <div class="flex items-center space-x-1">
                                <em class="text-xs ni ni-shield-check"></em>
                                <span>Rôles</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                            <div class="flex items-center space-x-1">
                                <em class="text-xs ni ni-calendar"></em>
                                <span>Création</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8">
                                        <div class="flex items-center justify-center w-8 h-8 text-xs font-medium text-white bg-blue-600 rounded-lg">
                                            {{ $user->initials }}
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $user->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $user->username }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $user->email }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @if($role->name === 'superadmin') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                                            @elseif($role->name === 'enseignant') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                            @elseif($role->name === 'secretaire') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400
                                            @endif">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Aucun rôle</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-1">
                                    <!-- Bouton Changer mot de passe -->
                                    <button wire:click="openPasswordModal({{ $user->id }})"
                                            class="inline-flex items-center p-1.5 text-yellow-600 bg-yellow-100 rounded hover:bg-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-400 dark:hover:bg-yellow-900/40"
                                            title="Changer le mot de passe">
                                        <em class="text-sm ni ni-lock"></em>
                                    </button>

                                    <!-- Bouton Modifier -->
                                    <button wire:click="editUser({{ $user->id }})"
                                            class="inline-flex items-center p-1.5 text-blue-600 bg-blue-100 rounded hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40"
                                            title="Modifier">
                                        <em class="text-sm ni ni-edit"></em>
                                    </button>

                                    <!-- Bouton Supprimer -->
                                    @if($user->id !== auth()->id() && !$user->hasRole('superadmin'))
                                        <button wire:click="confirmDeleteUser({{ $user->id }})"
                                                class="inline-flex items-center p-1.5 text-red-600 bg-red-100 rounded hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                                                title="Supprimer">
                                            <em class="text-sm ni ni-trash"></em>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center">
                                    <em class="mb-3 text-2xl text-gray-400 ni ni-users"></em>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Aucun utilisateur trouvé</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-4 py-3 bg-white border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Utilisateur - Taille raisonnable -->
    @if($showUserModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 py-4">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" aria-hidden="true" wire:click="closeUserModal"></div>

                <!-- Modal -->
                <div class="relative z-10 w-full max-w-3xl px-6 py-6 mx-auto overflow-hidden text-left bg-white rounded-lg shadow-xl dark:bg-gray-800">
                    <form wire:submit.prevent="saveUser">
                        <!-- En-tête du modal -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center w-10 h-10 bg-blue-600 rounded-lg">
                                            @if($editMode)
                                                <em class="text-lg text-white ni ni-edit"></em>
                                            @else
                                                <em class="text-lg text-white ni ni-user-add"></em>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                            {{ $editMode ? 'Modifier l\'utilisateur' : 'Créer un nouvel utilisateur' }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $editMode ? 'Modifiez les informations et les rôles de l\'utilisateur.' : 'Créez un nouveau compte utilisateur avec les rôles appropriés.' }}
                                        </p>
                                    </div>
                                </div>
                                <button type="button" wire:click="closeUserModal" class="p-2 text-gray-400 rounded-lg hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <em class="text-lg ni ni-cross"></em>
                                </button>
                            </div>
                        </div>

                        <!-- Corps du modal -->
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <!-- Colonne de gauche - Informations utilisateur -->
                            <div class="space-y-4">
                                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                                    <h4 class="flex items-center mb-4 text-sm font-medium text-gray-900 dark:text-white">
                                        <em class="mr-2 text-blue-500 ni ni-user-circle"></em>
                                        Informations personnelles
                                    </h4>

                                    <div class="space-y-4">
                                        <!-- Nom complet -->
                                        <div>
                                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Nom complet *
                                            </label>
                                            <input wire:model="name"
                                                   type="text"
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                   placeholder="Jean Dupont">
                                            @error('name')
                                                <p class="flex items-center mt-1 text-xs text-red-600 dark:text-red-400">
                                                    <em class="mr-1 ni ni-alert-circle"></em>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <!-- Nom d'utilisateur -->
                                        <div>
                                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Nom d'utilisateur *
                                            </label>
                                            <input wire:model="username"
                                                   type="text"
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                   placeholder="jdupont">
                                            @error('username')
                                                <p class="flex items-center mt-1 text-xs text-red-600 dark:text-red-400">
                                                    <em class="mr-1 ni ni-alert-circle"></em>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <!-- Email -->
                                        <div>
                                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Adresse email *
                                            </label>
                                            <input wire:model="email"
                                                   type="email"
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                   placeholder="jean@example.com">
                                            @error('email')
                                                <p class="flex items-center mt-1 text-xs text-red-600 dark:text-red-400">
                                                    <em class="mr-1 ni ni-alert-circle"></em>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Section mot de passe (seulement pour création) -->
                                @if(!$editMode)
                                    <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-800">
                                        <h4 class="flex items-center mb-4 text-sm font-medium text-gray-900 dark:text-white">
                                            <em class="mr-2 text-yellow-500 ni ni-lock"></em>
                                            Sécurité du compte
                                        </h4>

                                        <div class="space-y-4">
                                            <div>
                                                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Mot de passe *
                                                </label>
                                                <input wire:model="password"
                                                       type="password"
                                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white"
                                                       placeholder="••••••••">
                                                @error('password')
                                                    <p class="flex items-center mt-1 text-xs text-red-600 dark:text-red-400">
                                                        <em class="mr-1 ni ni-alert-circle"></em>
                                                        {{ $message }}
                                                    </p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Confirmer le mot de passe *
                                                </label>
                                                <input wire:model="password_confirmation"
                                                       type="password"
                                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white"
                                                       placeholder="••••••••">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Colonne de droite - Rôles et permissions -->
                            <div class="p-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                                <h4 class="flex items-center mb-4 text-sm font-medium text-gray-900 dark:text-white">
                                    <em class="mr-2 text-blue-500 ni ni-shield-check"></em>
                                    Rôles et autorisations
                                </h4>

                                <p class="mb-4 text-xs text-gray-600 dark:text-gray-400">
                                    Sélectionnez les rôles à attribuer à cet utilisateur.
                                </p>

                                <div class="space-y-2 overflow-y-auto max-h-64">
                                    @foreach($allRoles as $role)
                                        <label class="flex items-start space-x-3 cursor-pointer p-3 rounded-lg border hover:border-blue-200 dark:hover:border-blue-700 hover:bg-white dark:hover:bg-gray-800 {{ in_array($role->id, $selectedRoles) ? 'border-blue-400 bg-white dark:bg-gray-800' : 'border-transparent bg-gray-50 dark:bg-gray-800/50' }}">
                                            <input type="checkbox"
                                                   wire:model.live="selectedRoles"
                                                   value="{{ $role->id }}"
                                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 mt-0.5">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <div class="text-sm font-medium text-gray-900 capitalize dark:text-white">
                                                        {{ $role->name }}
                                                    </div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                        @if($role->name === 'superadmin') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                                                        @elseif($role->name === 'enseignant') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                                        @elseif($role->name === 'secretaire') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                                                        @else bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400
                                                        @endif">
                                                        {{ ucfirst($role->name) }}
                                                    </span>
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $role->permissions->count() }} permissions
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                @error('selectedRoles')
                                    <p class="flex items-center mt-2 text-xs text-red-600 dark:text-red-400">
                                        <em class="mr-1 ni ni-alert-circle"></em>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Pied du modal -->
                        <div class="flex justify-end pt-4 mt-6 space-x-3 border-t border-gray-200 dark:border-gray-700">
                            <button type="button"
                                    wire:click="closeUserModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <em class="mr-2 ni ni-cross"></em>
                                Annuler
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                @if($editMode)
                                    <em class="mr-2 ni ni-check"></em>
                                @else
                                    <em class="mr-2 ni ni-plus"></em>
                                @endif
                                {{ $editMode ? 'Sauvegarder' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Changement de mot de passe - Compact -->
    @if($showPasswordModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 py-4">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" aria-hidden="true" wire:click="closePasswordModal"></div>

                <!-- Modal -->
                <div class="relative z-10 w-full max-w-lg px-6 py-6 mx-auto overflow-hidden text-left bg-white rounded-lg shadow-xl dark:bg-gray-800">
                    <form wire:submit.prevent="changePassword">
                        <!-- En-tête du modal -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center w-10 h-10 bg-yellow-600 rounded-lg">
                                            <em class="text-lg text-white ni ni-lock"></em>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                            Changer le mot de passe
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            @if($userForPasswordChange && $userForPasswordChange->id === auth()->id())
                                                Modifiez votre mot de passe personnel.
                                            @else
                                                Modifiez le mot de passe de {{ $userForPasswordChange->name ?? '' }}.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <button type="button" wire:click="closePasswordModal" class="p-2 text-gray-400 rounded-lg hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <em class="text-lg ni ni-cross"></em>
                                </button>
                            </div>
                        </div>

                        <!-- Corps du modal -->
                        <div class="space-y-4">
                            <!-- Informations utilisateur -->
                            @if($userForPasswordChange)
                                <div class="p-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex items-center justify-center w-8 h-8 text-sm font-medium text-white bg-blue-600 rounded-lg">
                                            {{ $userForPasswordChange->initials }}
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $userForPasswordChange->name }}</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $userForPasswordChange->email }}</p>
                                        </div>
                                        <div class="flex space-x-1">
                                            @foreach($userForPasswordChange->roles as $role)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    @if($role->name === 'superadmin') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                                                    @elseif($role->name === 'enseignant') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                                    @elseif($role->name === 'secretaire') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400
                                                    @endif">
                                                    {{ ucfirst($role->name) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Formulaire de changement de mot de passe -->
                            <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-800">
                                <div class="space-y-4">
                                    <!-- Mot de passe actuel (seulement si l'utilisateur change le sien ET n'est pas superadmin) -->
                                    @if($userForPasswordChange && $userForPasswordChange->id === auth()->id() && !auth()->user()->hasRole('superadmin'))
                                        <div>
                                            <label class="flex items-center block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                <em class="mr-2 text-gray-500 ni ni-lock"></em>
                                                Mot de passe actuel *
                                            </label>
                                            <input wire:model="currentPassword"
                                                   type="password"
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white"
                                                   placeholder="Entrez votre mot de passe actuel">
                                            @error('currentPassword')
                                                <p class="flex items-center mt-1 text-xs text-red-600 dark:text-red-400">
                                                    <em class="mr-1 ni ni-alert-circle"></em>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    @endif

                                    <!-- Nouveau mot de passe -->
                                    <div>
                                        <label class="flex items-center block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <em class="mr-2 text-gray-500 ni ni-lock"></em>
                                            Nouveau mot de passe *
                                        </label>
                                        <input wire:model="newPassword"
                                               type="password"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white"
                                               placeholder="Entrez le nouveau mot de passe">
                                        @error('newPassword')
                                            <p class="flex items-center mt-1 text-xs text-red-600 dark:text-red-400">
                                                <em class="mr-1 ni ni-alert-circle"></em>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <!-- Confirmer le nouveau mot de passe -->
                                    <div>
                                        <label class="flex items-center block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <em class="mr-2 text-gray-500 ni ni-check-circle"></em>
                                            Confirmer le nouveau mot de passe *
                                        </label>
                                        <input wire:model="newPassword_confirmation"
                                               type="password"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white"
                                               placeholder="Confirmez le nouveau mot de passe">
                                    </div>
                                </div>
                            </div>

                            <!-- Conseils de sécurité -->
                            <div class="p-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <em class="text-lg text-blue-400 ni ni-info"></em>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="mb-2 text-sm font-medium text-blue-800 dark:text-blue-200">
                                            Conseils pour un mot de passe sécurisé
                                        </h3>
                                        <div class="grid grid-cols-2 gap-2 text-xs text-blue-700 dark:text-blue-300">
                                            <div class="flex items-center">
                                                <em class="mr-1 text-green-500 ni ni-check"></em>
                                                Au moins 8 caractères
                                            </div>
                                            <div class="flex items-center">
                                                <em class="mr-1 text-green-500 ni ni-check"></em>
                                                Lettres et chiffres
                                            </div>
                                            <div class="flex items-center">
                                                <em class="mr-1 text-green-500 ni ni-check"></em>
                                                Symboles spéciaux
                                            </div>
                                            <div class="flex items-center">
                                                <em class="mr-1 text-green-500 ni ni-check"></em>
                                                Éviter infos personnelles
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pied du modal -->
                        <div class="flex justify-end pt-4 mt-6 space-x-3 border-t border-gray-200 dark:border-gray-700">
                            <button type="button"
                                    wire:click="closePasswordModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                <em class="mr-2 ni ni-cross"></em>
                                Annuler
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                <em class="mr-2 ni ni-lock"></em>
                                Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Confirmation Suppression -->
    @if($confirmDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 py-4">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

                <!-- Modal -->
                <div class="relative z-10 w-full max-w-md mx-auto overflow-hidden text-left bg-white rounded-lg shadow-xl dark:bg-gray-800">
                    <div class="px-6 pt-6 pb-4">
                        <div class="flex items-start">
                            <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg dark:bg-red-900/20">
                                <em class="text-lg text-red-600 dark:text-red-400 ni ni-alert-c"></em>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                    Confirmer la suppression
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible et supprimera définitivement toutes les données associées.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end px-6 py-4 space-x-3 bg-gray-50 dark:bg-gray-700">
                        <button wire:click="$set('confirmDelete', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md dark:text-gray-300 dark:bg-gray-600 dark:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            <em class="mr-2 ni ni-cross"></em>
                            Annuler
                        </button>
                        <button wire:click="deleteUser"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <em class="mr-2 ni ni-trash"></em>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
