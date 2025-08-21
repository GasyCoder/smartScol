<div class="py-6">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        {{-- En-tête --}}
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Rôles et Permissions
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Gérer les rôles utilisateurs et leurs permissions d'accès
                    </p>
                </div>
                <div class="flex space-x-3">
                    @if($activeTab === 'roles')
                        <button wire:click="openRoleModal"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-blue-600 rounded-lg hover:bg-blue-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Nouveau Rôle
                        </button>
                    @else
                        <button wire:click="openPermissionModal"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-green-600 rounded-lg hover:bg-green-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Nouvelle Permission
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Onglets --}}
        <div class="mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex -mb-px space-x-8">
                    <button wire:click="setActiveTab('roles')"
                            class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'roles' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Rôles
                    </button>
                    <button wire:click="setActiveTab('permissions')"
                            class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'permissions' ? 'border-green-500 text-green-600 dark:text-green-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Permissions
                    </button>
                </nav>
            </div>
        </div>

        {{-- Contenu des onglets --}}
        @if($activeTab === 'roles')
            {{-- Onglet Rôles --}}
            <div class="space-y-6">
                {{-- Filtres Rôles --}}
                <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Rechercher un rôle
                            </label>
                            <div class="relative">
                                <input type="text"
                                       wire:model.live.debounce.300ms="searchRoles"
                                       placeholder="Nom du rôle..."
                                       class="w-full py-2 pl-10 pr-4 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-end">
                            <button wire:click="resetFilters"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 bg-gray-100 rounded-lg dark:text-gray-300 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500">
                                Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tableau des Rôles --}}
                <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Nom du Rôle
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Utilisateurs
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Permissions
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @forelse($roles as $role)
                                    <tr class="transition-colors duration-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex items-center justify-center w-8 h-8 mr-3 bg-blue-100 rounded-full dark:bg-blue-900">
                                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="text-lg font-semibold text-gray-900 capitalize dark:text-white">
                                                        {{ $role->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-800 bg-blue-100 border border-blue-200 rounded-full dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800">
                                                {{ $role->users_count }} utilisateur(s)
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium text-green-800 bg-green-100 border border-green-200 rounded-full dark:bg-green-900/20 dark:text-green-400 dark:border-green-800">
                                                {{ $role->permissions_count }} permission(s)
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <button wire:click="editRole({{ $role->id }})"
                                                        class="inline-flex items-center p-2 text-blue-600 transition-colors duration-200 bg-blue-100 rounded-lg hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40"
                                                        title="Modifier">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                    </svg>
                                                </button>

                                                @if($role->name !== 'superadmin')
                                                    <button wire:click="confirmDeleteRole({{ $role->id }})"
                                                            class="inline-flex items-center p-2 text-red-600 transition-colors duration-200 bg-red-100 rounded-lg hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                                                            title="Supprimer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="text-gray-500 dark:text-gray-400">
                                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                <p class="text-lg font-medium">Aucun rôle trouvé</p>
                                                <p class="mt-1 text-sm">Commencez par créer votre premier rôle</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Rôles --}}
                    @if($roles->hasPages())
                        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                            {{ $roles->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Onglet Permissions --}}
            <div class="space-y-6">
                {{-- Filtres Permissions --}}
                <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Rechercher une permission
                            </label>
                            <div class="relative">
                                <input type="text"
                                       wire:model.live.debounce.300ms="searchPermissions"
                                       placeholder="Nom ou libellé..."
                                       class="w-full py-2 pl-10 pr-4 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-end">
                            <button wire:click="resetFilters"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 bg-gray-100 rounded-lg dark:text-gray-300 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500">
                                Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tableau des Permissions --}}
                <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Permission
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Description
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Utilisée par
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @forelse($permissions as $permission)
                                    <tr class="transition-colors duration-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $permission->label }}
                                                </div>
                                                <div class="font-mono text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $permission->name }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="max-w-xs text-sm text-gray-600 dark:text-gray-400">
                                                {{ $permission->description }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium text-purple-800 bg-purple-100 border border-purple-200 rounded-full dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800">
                                                {{ $permission->roles_count }} rôle(s)
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <button wire:click="editPermission({{ $permission->id }})"
                                                        class="inline-flex items-center p-2 text-green-600 transition-colors duration-200 bg-green-100 rounded-lg hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/40"
                                                        title="Modifier">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                    </svg>
                                                </button>

                                                <button wire:click="confirmDeletePermission({{ $permission->id }})"
                                                        class="inline-flex items-center p-2 text-red-600 transition-colors duration-200 bg-red-100 rounded-lg hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                                                        title="Supprimer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="text-gray-500 dark:text-gray-400">
                                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                </svg>
                                                <p class="text-lg font-medium">Aucune permission trouvée</p>
                                                <p class="mt-1 text-sm">Commencez par créer votre première permission</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Permissions --}}
                    @if($permissions->hasPages())
                        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                            {{ $permissions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Création/Édition Rôle --}}
    @if($showRoleModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="closeRoleModal"></div>

                <div class="relative inline-block w-full max-w-2xl mx-auto overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800">
                    <form wire:submit.prevent="saveRole">
                        <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
                                    <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                        {{ $editMode ? 'Modifier le rôle' : 'Nouveau rôle' }}
                                    </h3>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Nom du rôle *
                                            </label>
                                            <input type="text"
                                                   wire:model="roleName"
                                                   placeholder="Ex: administrateur, gestionnaire..."
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white @error('roleName') border-red-500 @enderror">
                                            @error('roleName')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Permissions assignées
                                            </label>
                                            <div class="p-4 space-y-4 overflow-y-auto border border-gray-300 rounded-lg max-h-96 dark:border-gray-600">
                                                @foreach($allPermissions as $group => $permissions)
                                                    <div class="pb-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                                                        <h4 class="mb-2 text-sm font-semibold text-gray-800 capitalize dark:text-gray-200">
                                                            {{ ucfirst($group) }}
                                                        </h4>
                                                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                                            @foreach($permissions as $permission)
                                                                <label class="flex items-center space-x-2 cursor-pointer">
                                                                    <input type="checkbox"
                                                                           wire:model.live="selectedPermissions"
                                                                           value="{{ $permission->id }}"
                                                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600">
                                                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                                                        {{ $permission->label }}
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('selectedPermissions')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white transition-colors duration-200 bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $editMode ? 'Modifier' : 'Créer' }}
                            </button>
                            <button type="button"
                                    wire:click="closeRoleModal"
                                    class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 transition-colors duration-200 bg-white border border-gray-300 rounded-lg shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Création/Édition Permission --}}
    @if($showPermissionModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="closePermissionModal"></div>

                <div class="relative inline-block w-full max-w-lg mx-auto overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800">
                    <form wire:submit.prevent="savePermission">
                        <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
                                    <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                        {{ $editMode ? 'Modifier la permission' : 'Nouvelle permission' }}
                                    </h3>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Nom technique *
                                            </label>
                                            <input type="text"
                                                   wire:model="permissionName"
                                                   placeholder="Ex: users.view, products.create..."
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white @error('permissionName') border-red-500 @enderror">
                                            @error('permissionName')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Libellé *
                                            </label>
                                            <input type="text"
                                                   wire:model="permissionLabel"
                                                   placeholder="Ex: Voir les utilisateurs, Créer un produit..."
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white @error('permissionLabel') border-red-500 @enderror">
                                            @error('permissionLabel')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Description
                                            </label>
                                            <textarea wire:model="permissionDescription"
                                                      rows="3"
                                                      placeholder="Description détaillée de cette permission..."
                                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white @error('permissionDescription') border-red-500 @enderror"></textarea>
                                            @error('permissionDescription')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white transition-colors duration-200 bg-green-600 border border-transparent rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $editMode ? 'Modifier' : 'Créer' }}
                            </button>
                            <button type="button"
                                    wire:click="closePermissionModal"
                                    class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 transition-colors duration-200 bg-white border border-gray-300 rounded-lg shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Confirmation Suppression --}}
    @if($confirmDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

                <div class="relative inline-block w-full max-w-lg mx-auto overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800">
                    <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                    Confirmer la suppression
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @if($activeTab === 'roles')
                                            Êtes-vous sûr de vouloir supprimer ce rôle ? Cette action est irréversible.
                                        @else
                                            Êtes-vous sûr de vouloir supprimer cette permission ? Elle sera retirée de tous les rôles.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 sm:px-6 sm:flex sm:flex-row-reverse">
                        @if($activeTab === 'roles')
                            <button wire:click="deleteRole"
                                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white transition-colors duration-200 bg-red-600 border border-transparent rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Supprimer le rôle
                            </button>
                        @else
                            <button wire:click="deletePermission"
                                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white transition-colors duration-200 bg-red-600 border border-transparent rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Supprimer la permission
                            </button>
                        @endif
                        <button wire:click="$set('confirmDelete', false)"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 transition-colors duration-200 bg-white border border-gray-300 rounded-lg shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
