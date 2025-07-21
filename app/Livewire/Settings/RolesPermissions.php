<?php

namespace App\Livewire\Settings;

use App\Models\Permission;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class RolesPermissions extends Component
{
    use WithPagination;

    // Onglets
    public $activeTab = 'roles';

    // Modal states
    public $showRoleModal = false;
    public $showPermissionModal = false;
    public $editMode = false;
    public $confirmDelete = false;

    // Propriétés du formulaire rôle
    public $roleId;
    public $roleName = '';
    public $selectedPermissions = [];

    // Propriétés du formulaire permission
    public $permissionId;
    public $permissionName = '';
    public $permissionLabel = '';
    public $permissionDescription = '';

    // Filtres et recherche
    public $searchRoles = '';
    public $searchPermissions = '';

    protected $rules = [
        'roleName' => 'required|string|max:255|unique:roles,name',
        'selectedPermissions' => 'array',
        'permissionName' => 'required|string|max:255|unique:permissions,name',
        'permissionLabel' => 'required|string|max:255',
        'permissionDescription' => 'nullable|string|max:500',
    ];

    protected function getRulesForRole()
    {
        $rules = [
            'roleName' => 'required|string|max:255|unique:roles,name',
            'selectedPermissions' => 'array',
        ];

        if ($this->editMode && $this->roleId) {
            $rules['roleName'] = 'required|string|max:255|unique:roles,name,' . $this->roleId;
        }

        return $rules;
    }

    protected function getRulesForPermission()
    {
        $rules = [
            'permissionName' => 'required|string|max:255|unique:permissions,name',
            'permissionLabel' => 'required|string|max:255',
            'permissionDescription' => 'nullable|string|max:500',
        ];

        if ($this->editMode && $this->permissionId) {
            $rules['permissionName'] = 'required|string|max:255|unique:permissions,name,' . $this->permissionId;
        }

        return $rules;
    }

    protected $messages = [
        'roleName.required' => 'Le nom du rôle est obligatoire.',
        'roleName.unique' => 'Ce nom de rôle existe déjà.',
        'permissionName.required' => 'Le nom de la permission est obligatoire.',
        'permissionName.unique' => 'Ce nom de permission existe déjà.',
        'permissionLabel.required' => 'Le libellé de la permission est obligatoire.',
    ];

    public function mount()
    {
        $this->resetPage();
    }

    public function updatingSearchRoles()
    {
        $this->resetPage();
    }

    public function updatingSearchPermissions()
    {
        $this->resetPage();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // === GESTION DES RÔLES ===

    public function openRoleModal()
    {
        $this->resetRoleForm();
        $this->showRoleModal = true;
        $this->editMode = false;
    }

    public function closeRoleModal()
    {
        $this->showRoleModal = false;
        $this->resetRoleForm();
        $this->resetValidation();
    }

    public function resetRoleForm()
    {
        $this->roleId = null;
        $this->roleName = '';
        $this->selectedPermissions = [];
    }

    public function refreshPermissions()
    {
        if ($this->roleId) {
            $role = Role::with('permissions')->findOrFail($this->roleId);
            $this->selectedPermissions = $role->permissions->pluck('id')->map(function($id) {
                return (int) $id;
            })->toArray();

            toastr()->info('Permissions rechargées');
        } else {
            toastr()->warning('Aucun rôle sélectionné');
        }
    }

    public function testMethod()
    {
        toastr()->success('Test Livewire réussi !');
    }

    public function saveRole()
    {
        try {
            // Utiliser les règles spécifiques aux rôles
            $this->validate($this->getRulesForRole());

            if ($this->editMode) {
                $role = Role::findOrFail($this->roleId);
                $role->update(['name' => $this->roleName]);
                $message = 'Rôle modifié avec succès.';
            } else {
                $role = Role::create(['name' => $this->roleName]);
                $message = 'Rôle créé avec succès.';
            }

            // Synchroniser les permissions
            $permissionIds = array_map('intval', $this->selectedPermissions);
            $permissions = Permission::whereIn('id', $permissionIds)->get();
            $role->syncPermissions($permissions);

            // Nettoyer le cache des permissions
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            toastr()->success($message);
            $this->closeRoleModal();

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages[] = implode(', ', $messages);
            }
            toastr()->error('Erreur de validation: ' . implode(' | ', $errorMessages));

        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    public function editRole($id)
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);

            $this->roleId = $role->id;
            $this->roleName = $role->name;
            $this->selectedPermissions = $role->permissions->pluck('id')->map(function($id) {
                return (int) $id;
            })->toArray();

            $this->editMode = true;
            $this->showRoleModal = true;

        } catch (\Exception $e) {
            toastr()->error('Erreur lors de l\'ouverture du rôle: ' . $e->getMessage());
        }
    }

    public function confirmDeleteRole($id)
    {
        $this->roleId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRole()
    {
        try {
            $role = Role::findOrFail($this->roleId);

            // Vérifier si le rôle est utilisé par des utilisateurs
            if ($role->users()->count() > 0) {
                toastr()->error('Impossible de supprimer ce rôle car il est assigné à des utilisateurs.');
                $this->confirmDelete = false;
                return;
            }

            $role->delete();
            toastr()->success('Rôle supprimé avec succès.');
        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de la suppression.');
        }

        $this->confirmDelete = false;
        $this->roleId = null;
    }

    // === GESTION DES PERMISSIONS ===

    public function openPermissionModal()
    {
        $this->resetPermissionForm();
        $this->showPermissionModal = true;
        $this->editMode = false;
    }

    public function closePermissionModal()
    {
        $this->showPermissionModal = false;
        $this->resetPermissionForm();
        $this->resetValidation();
    }

    public function resetPermissionForm()
    {
        $this->permissionId = null;
        $this->permissionName = '';
        $this->permissionLabel = '';
        $this->permissionDescription = '';
    }

    public function savePermission()
    {
        // Utiliser les règles spécifiques aux permissions
        $this->validate($this->getRulesForPermission());

        try {
            $data = [
                'name' => $this->permissionName,
                'label' => $this->permissionLabel,
                'description' => $this->permissionDescription,
            ];

            if ($this->editMode) {
                $permission = Permission::findOrFail($this->permissionId);
                $permission->update($data);
                $message = 'Permission modifiée avec succès.';
            } else {
                Permission::create($data);
                $message = 'Permission créée avec succès.';
            }

            toastr()->success($message);
            $this->closePermissionModal();
        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    public function editPermission($id)
    {
        $permission = Permission::findOrFail($id);

        $this->permissionId = $permission->id;
        $this->permissionName = $permission->name;
        $this->permissionLabel = $permission->label;
        $this->permissionDescription = $permission->description;

        $this->editMode = true;
        $this->showPermissionModal = true;
    }

    public function confirmDeletePermission($id)
    {
        $this->permissionId = $id;
        $this->confirmDelete = true;
    }

    public function deletePermission()
    {
        try {
            $permission = Permission::findOrFail($this->permissionId);

            // Détacher la permission de tous les rôles
            $permission->roles()->detach();

            $permission->delete();
            toastr()->success('Permission supprimée avec succès.');
        } catch (\Exception $e) {
            toastr()->error('Une erreur est survenue lors de la suppression.');
        }

        $this->confirmDelete = false;
        $this->permissionId = null;
    }

    public function resetFilters()
    {
        $this->searchRoles = '';
        $this->searchPermissions = '';
        $this->resetPage();
    }

    public function render()
    {
        // Requête pour les rôles
        $rolesQuery = Role::withCount('users', 'permissions');
        if ($this->searchRoles) {
            $rolesQuery->where('name', 'like', '%' . $this->searchRoles . '%');
        }
        $roles = $rolesQuery->paginate(10, ['*'], 'rolesPage');

        // Requête pour les permissions
        $permissionsQuery = Permission::withCount('roles');
        if ($this->searchPermissions) {
            $permissionsQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchPermissions . '%')
                  ->orWhere('label', 'like', '%' . $this->searchPermissions . '%');
            });
        }
        $permissions = $permissionsQuery->paginate(10, ['*'], 'permissionsPage');

        // Toutes les permissions pour le formulaire rôle
        $allPermissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('livewire.settings.roles-permissions', [
            'roles' => $roles,
            'permissions' => $permissions,
            'allPermissions' => $allPermissions
        ]);
    }
}